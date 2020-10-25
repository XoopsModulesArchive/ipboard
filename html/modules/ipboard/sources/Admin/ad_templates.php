<?php

/*
+--------------------------------------------------------------------------
|   Invision Power Board v1.1
|   ========================================
|   by Matthew Mecham
|   (c) 2001,2002 Invision Power Services
|   http://www.ibforums.com
|   ========================================
|   Web: http://www.ibforums.com
|   Email: phpboards@ibforums.com
|   Licence Info: phpib-licence@ibforums.com
+---------------------------------------------------------------------------
|
|   > Skin -> Templates functions
|   > Module written by Matt Mecham
|   > Date started: 15th April 2002
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

$idx = new ad_settings();

class ad_settings
{
    public $base_url;

    public function __construct()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        //---------------------------------------

        // Kill globals - globals bad, Homer good.

        //---------------------------------------

        $tmp_in = array_merge($_GET, $_POST, $_COOKIE);

        foreach ($tmp_in as $k => $v) {
            unset($$k);
        }

        switch ($IN['code']) {
            case 'add':
                $this->add_splash();
                break;
            case 'edit':
                $this->show_cats();
                break;
            case 'dedit':
                $this->do_form();
                break;
            case 'doedit':
                $this->do_edit();
                break;
            case 'remove':
                $this->remove();
                break;
            case 'tools':
                $this->tools();
                break;
            case 'editinfo':
                $this->edit_info();
                break;
            case 'export':
                $this->export();
                break;
            case 'edit_bit':
                $this->edit_bit();
                break;
            case 'download':
                $this->download_group();
                break;
            case 'upload':
                $this->upload_form();
                break;
            case 'do_upload':
                $this->upload_single();
                break;
            //-------------------------
            default:
                $this->list_current();
                break;
        }
    }

    //------------------------------------------------------

    public function add_splash()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $HTTP_POST_FILES;

        $FILE_NAME = $HTTP_POST_FILES['FILE_UPLOAD']['name'];

        $FILE_SIZE = $HTTP_POST_FILES['FILE_UPLOAD']['size'];

        $FILE_TYPE = $HTTP_POST_FILES['FILE_UPLOAD']['type'];

        // Naughty Opera adds the filename on the end of the

        // mime type - we don't want this.

        $FILE_TYPE = preg_replace('/^(.+?);.*$/', '\\1', $FILE_TYPE);

        // Naughty Mozilla likes to use "none" to indicate an empty upload field.

        // I love universal languages that aren't universal.

        if ('' == $HTTP_POST_FILES['FILE_UPLOAD']['name'] or !$HTTP_POST_FILES['FILE_UPLOAD']['name'] or ('none' == $HTTP_POST_FILES['FILE_UPLOAD']['name'])) {
            // We're adding new templates based on another set

            $this->add_templates();

            exit();
        }

        if (!is_dir($INFO['upload_dir'])) {
            $ADMIN->error("Could not locate the uploads directory - make sure the 'uploads' path is set correctly");
        }

        //-------------------------------------------------

        // Copy the upload to the uploads directory

        //-------------------------------------------------

        if (!@move_uploaded_file($HTTP_POST_FILES['FILE_UPLOAD']['tmp_name'], $INFO['upload_dir'] . '/' . $FILE_NAME)) {
            $ADMIN->error('The upload failed');
        } else {
            @chmod($INFO['upload_dir'] . '/' . $FILE_NAME, 0777);
        }

        //-------------------------------------------------

        // Attempt to open the file..

        //-------------------------------------------------

        $filename = $INFO['upload_dir'] . '/' . $FILE_NAME;

        if ($FH = @fopen($filename, 'rb')) {
            $data = @fread($FH, filesize($filename));

            @fclose($FH);

            @unlink($filename);
        } else {
            $ADMIN->error('Could not open the uploaded file for reading!');
        }

        //-------------------------------------------------

        // If we're here, we'll assume that we've read the

        // file and the contents are in $data

        // So, lets make sure its the correct template file..

        //-------------------------------------------------

        if (!preg_match("/<!--TEMPLATE_SET\|(.+?)-->/", $data, $matches)) {
            $ADMIN->error('This file does not appear to be a valid Invision Power Board Template Set file');
        }

        [$pack_name, $author, $email, $url] = explode(',', trim($matches[1]));

        //-------------------------------------------------

        // Find the new set ID by inserting the data for the

        // template names, we can always remove it later if

        // we get an error

        //-------------------------------------------------

        $pack_name .= '(Upload ID: ' . mb_substr(time(), -6) . ')';

        $pack_name = str_replace("'", '', $pack_name);

        $author = str_replace("'", '', $author);

        $email = str_replace("'", '', $email);

        $url = str_replace("'", '', $url);

        $DB->query("INSERT INTO ibf_tmpl_names (skname, author, email, url) VALUES('$pack_name', '$author', '$email', '$url')");

        $setid = $DB->get_insert_id();

        //-------------------------------------------------

        // Divide the file up into different sections

        //-------------------------------------------------

        preg_match_all("/<!--IBF_GROUP_START:(\S+?)-->(.+?)<!--IBF_GROUP_END:\S+?-->/s", $data, $match);

        for ($i = 0, $iMax = count($match[0]); $i < $iMax; $i++) {
            $match[1][$i] = trim($match[1][$i]);

            $match[2][$i] = trim($match[2][$i]);

            // Pass it on to our handler..

            $this->process_upload($match[2][$i], $setid, $match[1][$i], 1);
        }

        // Insert this new data into the template names thingy

        $ADMIN->done_screen('Template set import complete', 'Manage Template Sets', 'act=templ');
    }

    //------------------------------------------------------

    public function upload_single()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $HTTP_POST_FILES;

        $FILE_NAME = $HTTP_POST_FILES['FILE_UPLOAD']['name'];

        $FILE_SIZE = $HTTP_POST_FILES['FILE_UPLOAD']['size'];

        $FILE_TYPE = $HTTP_POST_FILES['FILE_UPLOAD']['type'];

        // Naughty Opera adds the filename on the end of the

        // mime type - we don't want this.

        $FILE_TYPE = preg_replace('/^(.+?);.*$/', '\\1', $FILE_TYPE);

        if (!is_dir($INFO['upload_dir'])) {
            $ADMIN->error("Could not locate the uploads directory - make sure the 'uploads' path is set correctly");
        }

        // Naughty Mozilla likes to use "none" to indicate an empty upload field.

        // I love universal languages that aren't universal.

        if ('' == $HTTP_POST_FILES['FILE_UPLOAD']['name'] or !$HTTP_POST_FILES['FILE_UPLOAD']['name'] or ('none' == $HTTP_POST_FILES['FILE_UPLOAD']['name'])) {
            $ADMIN->error('No file was chosen to upload!');
        }

        //-------------------------------------------------

        // Copy the upload to the uploads directory

        //-------------------------------------------------

        if (!@move_uploaded_file($HTTP_POST_FILES['FILE_UPLOAD']['tmp_name'], $INFO['upload_dir'] . '/' . $FILE_NAME)) {
            $ADMIN->error('The upload failed');
        } else {
            @chmod($INFO['upload_dir'] . '/' . $FILE_NAME, 0777);
        }

        //-------------------------------------------------

        // Attempt to open the file..

        //-------------------------------------------------

        $filename = $INFO['upload_dir'] . '/' . $FILE_NAME;

        if ($FH = @fopen($filename, 'rb')) {
            $data = @fread($FH, filesize($filename));

            @fclose($FH);

            @unlink($filename);
        } else {
            $ADMIN->error('Could not open the uploaded file for reading!');
        }

        //-------------------------------------------------

        // If we're here, we'll assume that we've read the

        // file and the contents are in $data

        // So, lets make sure its the correct template file..

        //-------------------------------------------------

        preg_match("/<!--IBF_GROUP_START:(\S+?)-->/", $data, $matches);

        $found_group = trim($matches[1]);

        if ($found_group != $IN['group']) {
            $ADMIN->error("The uploaded file does not appear to be the correct type for this template group. Looking for template group '{$IN['group']}', found '$found_group'");
        }

        //-------------------------------------------------

        // If we're here, then lets proceed, first lets

        // remove the END GROUP statement.

        //-------------------------------------------------

        $data = preg_replace("/<!--IBF_GROUP_END:\S+-->/", '', $data);

        // Pass it on to our handler..

        $this->process_upload($data, $IN['setid'], $IN['group']);

        $ADMIN->done_screen('Template set update complete', 'Manage Template Sets', 'act=templ');
    }

    //------------------------------------------------------

    public function upload_form()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        require __DIR__ . '/sources/Admin/skin_info.php';

        if ('' == $IN['setid']) {
            $ADMIN->error('You must specify an existing template set ID, go back and try again');
        }

        if ('' == $IN['group']) {
            $ADMIN->error('You must specify an existing template set ID, go back and try again');
        }

        //-----------------------------------

        // Get the info from the DB

        //-----------------------------------

        $DB->query("SELECT * FROM ibf_skin_templates WHERE set_id='" . $IN['setid'] . "' AND group_name='" . $IN['group'] . "'");

        if (!$DB->get_num_rows()) {
            $ADMIN->error("Can't query the information from the database");
        }

        $DB->query("SELECT skname FROM ibf_tmpl_names WHERE skid='" . $IN['setid'] . "'");

        $row = $DB->fetch_row();

        //+-------------------------------

        $ADMIN->page_detail = 'Please check all the information carefully before continuing.';

        $ADMIN->page_title = "Upload a template file for template set: {$row['skname']}";

        //+-------------------------------

        $ADMIN->html .= $SKIN->start_form(
            [
                1 => ['code', 'do_upload'],
2 => ['act', 'templ'],
3 => ['MAX_FILE_SIZE', '10000000000'],
4 => ['setid', $IN['setid']],
5 => ['group', $IN['group']],
            ],
            'uploadform',
            " enctype='multipart/form-data'"
        );

        $SKIN->td_header[] = ['&nbsp;', '40%'];

        $SKIN->td_header[] = ['&nbsp;', '60%'];

        $ADMIN->html .= $SKIN->start_table('Upload template file to replace: ' . $skin_names[$IN['group']][0]);

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Choose a file from your computer to upload</b><br>Note: Uploading this file will replace all data currently held, there is no undo!.',
                $SKIN->form_upload(),
            ]
        );

        $ADMIN->html .= $SKIN->end_form('Upload File');

        $ADMIN->html .= $SKIN->end_table();

        $ADMIN->nav[] = ['act=templ', 'Template Control Home'];

        $ADMIN->nav[] = ["act=templ&code=edit&id={$IN['setid']}", $row['skname']];

        $ADMIN->output();
    }

    //------------------------------------------------------

    public function export()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        if ('' == $IN['id']) {
            $ADMIN->error('You must specify an existing template set ID, go back and try again');
        }

        $DB->query("SELECT * FROM ibf_tmpl_names WHERE skid='" . $IN['id'] . "'");

        if (!$set = $DB->fetch_row()) {
            $ADMIN->error('Could not find a template set with that ID in the database, please try again');
        }

        //-----------------------------------

        // Get the info from the DB

        //-----------------------------------

        $groups = $DB->query("SELECT DISTINCT(group_name) FROM ibf_skin_templates WHERE set_id='" . $IN['id'] . "'");

        if (!$DB->get_num_rows($groups)) {
            $ADMIN->error("Can't query the information from the database");
        }

        // Loop and pass it to the download compiler

        $author = str_replace(',', '-', $set['author']);

        $email = str_replace(',', '-', $set['email']);

        $url = str_replace(',', '-', $set['url']);

        $skname = str_replace(',', '-', $set['skname']);

        $output .= "<!--TEMPLATE_SET|$skname,$author,$email,$url-->\n\n";

        while (false !== ($row = $DB->fetch_row($groups))) {
            $output .= $this->download_group(1, $IN['id'], $row['group_name']);
        }

        $name = str_replace(' ', '_', $set['skname']);

        @header('Content-type: unknown/unknown');

        @header("Content-Disposition: attachment; filename={$name}.SET.html");

        print $output;

        exit();
    }

    //------------------------------------------------------

    //------------------------------------------------------

    public function download_group($return = 0, $setid = '', $group = '')
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        if ('' != $setid) {
            $IN['setid'] = $setid;
        }

        if ('' != $group) {
            $IN['group'] = $group;
        }

        if ('' == $IN['setid']) {
            $ADMIN->error('You must specify an existing template set ID, go back and try again');
        }

        if ('' == $IN['group']) {
            $ADMIN->error('You must specify an existing template set ID, go back and try again');
        }

        //-----------------------------------

        // Get the info from the DB

        //-----------------------------------

        $aq = $DB->query("SELECT * FROM ibf_skin_templates WHERE set_id='" . $IN['setid'] . "' AND group_name='" . $IN['group'] . "'");

        if (!$DB->get_num_rows($aq)) {
            $ADMIN->error("Can't query the information from the database");
        }

        $output = "<!-- PLEASE LEAVE ALL 'IBF' COMMENTS IN PLACE, DO NOT REMOVE THEM! -->\n<!--IBF_GROUP_START:{$IN['group']}-->\n\n";

        while (false !== ($row = $DB->fetch_row($aq))) {
            $text = $this->convert_tags($row['section_content']);

            $output .= "<!--IBF_START_FUNC|{$row['func_name']}|{$row['func_data']}-->\n\n";

            $output .= $text . "\n";

            $output .= "<!--IBF_END_FUNC|{$row['func_name']}-->\n\n";
        }

        $output .= "\n<!--IBF_GROUP_END:{$IN['group']}-->\n";

        if (0 == $return) {
            $DB->query("SELECT skname FROM ibf_tmpl_names WHERE skid='" . $IN['setid'] . "'");

            $set = $DB->fetch_row();

            $name = str_replace(' ', '_', $set['skname']);

            @header('Content-type: unknown/unknown');

            @header("Content-Disposition: attachment; filename={$name}.{$IN['group']}.html");

            print $output;

            exit();
        }

        return $output;
    }

    //------------------------------------------------------

    public function show_cats()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        if ('' == $IN['id']) {
            $ADMIN->error('You must specify an existing template set ID, go back and try again');
        }

        //+-------------------------------

        $DB->query("SELECT * from ibf_tmpl_names WHERE skid='" . $IN['id'] . "'");

        if (!$row = $DB->fetch_row()) {
            $ADMIN->error('Could not query the information from the database');
        }

        // Get $skin_names stuff

        require __DIR__ . '/sources/Admin/skin_info.php';

        if ($row['author'] and $row['email']) {
            $author = "<br><br>This template set <b>'{$row['skname']}'</b> was created by <a href='mailto:{$row['email']}' target='_blank'>{$row['author']}</a>";
        } elseif ($row['author']) {
            $author = "<br><br>This template set <b>'{$row['skname']}'</b> was created by {$row['author']}";
        }

        if ($row['url']) {
            $url = " (website: <a href='{$row['url']}' target='_blank'>{$row['url']}</a>)";
        }

        //+-------------------------------

        $ADMIN->page_detail = "Please choose which section you wish to edit below.<br><br><b>Download</b> this HTML section This option allows you to download all of the HTML for this template section for offline editing.<br><b>Upload</b> HTML for this section This option allows you to upload a saved HTML file to replace this template section.$author $url";

        $ADMIN->page_title = 'Edit Template sets';

        //+-------------------------------

        $ADMIN->html .= $SKIN->js_checkdelete();

        $all_cats = $DB->query("select group_name, set_id, suid, count(group_name) as number_secs, group_name FROM ibf_skin_templates WHERE set_id='" . $IN['id'] . "' group by group_name");

        $SKIN->td_header[] = ['Skin Category Title', '40%'];

        $SKIN->td_header[] = ['View Options', '20%'];

        $SKIN->td_header[] = ['Manage', '30%'];

        $SKIN->td_header[] = ['# Bits', '10%'];

        //+-------------------------------

        $ADMIN->html .= "<script language='javascript'>
						 function pop_win(theUrl) {
						 	
						 	window.open('{$SKIN->base_url}&'+theUrl,'Preview','width=400,height=450,resizable=yes,scrollbars=yes');
						 }
						 </script>";

        $ADMIN->html .= $SKIN->start_table('Template: ' . $row['skname']);

        while (false !== ($group = $DB->fetch_row($all_cats))) {
            $name = '<b>' . $group['group_name'] . '</b>';

            $desc = '';

            $expand = 'Expand to Edit';

            $eid = $group['suid'];

            $exp_content = '';

            // If available, change group name to easy name

            if (isset($skin_names[$group['group_name']])) {
                $name = '<b>' . $skin_names[$group['group_name']][0] . '</b>';

                $desc = "<br><span id='description'>" . $skin_names[$group['group_name']][1] . '</span>';
            } else {
                $name .= ' (Non-Default Group)';

                $desc = '<br>This group is not part of the standard Invision Power Board installation and no description is available';
            }

            // Is this section expanded?

            if ($IN['expand'] == $group['suid']) {
                $expand = 'Collapse';

                $eid = '';

                $new_q = $DB->query("SELECT func_name, LENGTH(section_content) as sec_length, suid FROM ibf_skin_templates WHERE set_id='{$IN['id']}' AND group_name='{$group['group_name']}'");

                //----------------------------

                if ($DB->get_num_rows($new_q)) {
                    $exp_content .= $SKIN->add_td_basic(
                        "<a name='anc{$group['suid']}'>
														  <table cellspacing='1' cellpadding='5' width='100%' align='center' bgcolor='#333333'>
														  <tr>
														   <td align='left' id='catrow2'><a style='font-weight:bold;font-size:12px;color:#000033' href='{$SKIN->base_url}&act=templ&code=edit&id={$IN['id']}&expand=' title='Collapse' alt='Collapse'>$name</a></td>
														   <td colspan='3' bgcolor='#FFFFFF'>&nbsp;</td>
														  <tr>
														   <td width='30%' id='catrow2'></td>
														   <td width='20%' id='catrow2' align='center'># Characters</td>
														   <td width='20%' id='catrow2'align='center'>Edit</td>
														   <td width='30%' id='catrow2'align='center'>Preview Options</td>
														  </tr>
														  <!--CONTENT--></table>",
                        'left',
                        'tdrow2'
                    );

                    $temp = '';

                    $sec_arry = [];

                    // Stuff array to sort on name

                    while (false !== ($i = $DB->fetch_row($new_q))) {
                        $sec_arry[$i['suid']] = $i;

                        $sec_arry[$i['suid']]['easy_name'] = $i['func_name'];

                        // If easy name is available, use it

                        if ('' != $bit_names[$group['group_name']][$i['func_name']]) {
                            $sec_arry[$i['suid']]['easy_name'] = $bit_names[$group['group_name']][$i['func_name']];
                        }
                    }

                    // Sort by easy_name

                    usort($sec_arry, ['ad_settings', 'perly_word_sort']);

                    // Loop and print

                    foreach ($sec_arry as $id => $sec) {
                        $sec['easy_name'] = preg_replace("/^(\d+)\:\s+?/", '', $sec['easy_name']);

                        $temp .= "
									<tr>
									 <td width='40%' id='tdrow1'><b>{$sec['easy_name']}</b></td>
									 <td width='10%' id='tdrow1' align='center'>{$sec['sec_length']}</td>
									 <td width='10%' id='tdrow1' align='center'><a href='{$SKIN->base_url}&act=templ&code=edit_bit&suid={$sec['suid']}&expand={$group['suid']}'>Edit</a></td>
									 <td width='40%' id='tdrow1' align='center' nowrap>(<a href='javascript:pop_win(\"act=rtempl&code=preview&suid={$sec['suid']}&type=html\")'>HTML</a> | <a href='javascript:pop_win(\"act=rtempl&code=preview&suid={$sec['suid']}&type=text\")'>Text</a> | <a href='javascript:pop_win(\"act=rtempl&code=preview&suid={$sec['suid']}&type=css\")'>With CSS</a>)</td>
									</tr>
								";
                    }

                    $exp_content = str_replace('<!--CONTENT-->', $temp, $exp_content);

                    $desc = '';
                }

                //----------------------------
            } else {
                $ADMIN->html .= $SKIN->add_td_row(
                    [
                        "<span style='font-weight:bold;font-size:12px;color:#000033'><a href='{$SKIN->base_url}&act=templ&code=edit&id={$IN['id']}&expand=$eid&#anc$eid'>" . $name . '</a></span>' . $desc,
                        "<center><a href='{$SKIN->base_url}&act=templ&code=edit&id={$IN['id']}&expand=$eid'>$expand</a></center>",
                        "<center><a href='{$SKIN->base_url}&act=templ&code=download&setid={$group['set_id']}&group={$group['group_name']}' title='Download a HTML file of this section for offline editing'>Download</a> | <a href='{$SKIN->base_url}&act=templ&code=upload&setid={$group['set_id']}&group={$group['group_name']}' title='Upload a saved HTML file to replace this section'>Upload</a></center>",
                        '<center>' . $group['number_secs'] . '</center>',
                    ]
                );
            }

            $ADMIN->html .= $exp_content;
        }

        $ADMIN->html .= $SKIN->end_table();

        //+-------------------------------

        //+-------------------------------

        $ADMIN->nav[] = ['act=templ', 'Template Control Home'];

        $ADMIN->nav[] = ['', 'Managing Template Set "' . $row['skname'] . '"'];

        $ADMIN->output();
    }

    // Sneaky sorting.

    // We use the format "1: name". without this hack

    // 1: name, 2: other name, 11: other name

    // will sort as 1: name, 11: other name, 2: other name

    // There is natsort and such in PHP, but it causes some

    // problems on older PHP installs, this is hackish but works

    // by simply adding '0' to a number less than 2 characters long.

    // of course, this won't work with three numerics in the hundreds

    // but we don't have to worry about more that 99 bits in a template

    // at this stage.

    public function perly_word_sort($a, $b)
    {
        $nat_a = (int)$a['easy_name'];

        $nat_b = (int)$b['easy_name'];

        if (mb_strlen($nat_a) < 2) {
            $nat_a = '0' . $nat_a;
        }

        if (mb_strlen($nat_b) < 2) {
            $nat_b = '0' . $nat_b;
        }

        return strcmp($nat_a, $nat_b);
    }

    //+--------------------------------------------------------------------------------

    //+--------------------------------------------------------------------------------

    public function edit_info()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $_POST;

        if ('' == $IN['id']) {
            $ADMIN->error('You must specify an existing template set ID, go back and try again');
        }

        //+-------------------------------

        $DB->query("SELECT * from ibf_tmpl_names WHERE skid='" . $IN['id'] . "'");

        if (!$row = $DB->fetch_row()) {
            $ADMIN->error('Could not query the information from the database');
        }

        $final['skname'] = stripslashes($_POST['skname']);

        if (isset($_POST['author'])) {
            $final['author'] = str_replace(',', '', stripslashes($_POST['author']));

            $final['email'] = str_replace(',', '', stripslashes($_POST['email']));

            $final['url'] = str_replace(',', '', stripslashes($_POST['url']));
        }

        $db_string = $DB->compile_db_update_string($final);

        $DB->query("UPDATE ibf_tmpl_names SET $db_string WHERE skid='" . $IN['id'] . "'");

        $ADMIN->done_screen('Template information updated', 'Manage Template sets', 'act=templ');
    }

    //+-------------------------------

    //+-------------------------------

    public function do_edit()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $_POST;

        $text = stripslashes($_POST['template']);

        if ('' == $IN['suid']) {
            $ADMIN->error('You must specify an existing template set ID, go back and try again');
        }

        //+-------------------------------

        // Get the group name, etc

        //+-------------------------------

        $DB->query("SELECT * FROM ibf_skin_templates WHERE suid='" . $IN['suid'] . "'");

        if (!$template = $DB->fetch_row()) {
            $ADMIN->error('You must specify an existing template set ID, go back and try again');
        }

        $real_name = $template['group_name'];

        //+-------------------------------

        // Get the template set info

        //+-------------------------------

        $DB->query("SELECT * from ibf_tmpl_names WHERE skid='" . $template['set_id'] . "'");

        if (!$row = $DB->fetch_row()) {
            $ADMIN->error('Could not query the information from the database');
        }

        //+-------------------------------

        $phpskin = ROOT_PATH . 'Skin/s' . $template['set_id'] . '/' . $real_name . '.php';

        //+-------------------------------

        if (1 != $INFO['safe_mode_skins']) {
            if (SAFE_MODE_ON == 1) {
                $ADMIN->error("Safe mode detected, you will need to change the board configuration to switch 'Safe Mode Skins' on. To do this, click on the 'Board Settings' menu and choose 'Basic Config' when the sub menu appears.");
            }

            if (!is_writable($phpskin)) {
                $ADMIN->error("Cannot write into '$phpskin', please check the CHMOD value, and if needed, CHMOD to 0777 via FTP. IBF cannot do this for you.");
            }
        }

        //+-------------------------------

        // Ok, make sure we actually have

        // some info to parse here.

        //+-------------------------------

        if ('' == $text) {
            $ADMIN->error("You can't delete the template in this manner");
        }

        //+-------------------------------

        // Swop back < and >

        //+-------------------------------

        $text = preg_replace('/&#60;/', '<', $text);

        $text = preg_replace('/&#62;/', '>', $text);

        $text = preg_replace('/&#38;/', '&', $text);

        $text = str_replace('\\n', '\\\\\\n', $text);

        //+-------------------------------

        // Convert \r to nowt

        //+-------------------------------

        $text = preg_replace("/\r/", '', $text);

        $text = $this->unconvert_tags($text);

        //+-------------------------------

        //Update the DB

        //+-------------------------------

        $string = $DB->compile_db_update_string(['section_content' => $text]);

        $DB->query("UPDATE ibf_skin_templates SET $string WHERE suid='" . $IN['suid'] . "'");

        //+-------------------------------

        // Start parsing the php skin file

        //+-------------------------------

        $final = '<' . "?php\n\n" . "class $real_name {\n\n";

        //+------------------------------------------

        // Get all the data from the DB that matches

        // the group name (filename) and set_id

        //+------------------------------------------

        if (1 != $INFO['safe_mode_skins']) {
            if (SAFE_MODE_ON == 1) {
                $ADMIN->error("Safe mode detected, you will need to change the board configuration to switch 'Safe Mode Skins' on. To do this, click on the 'Board Settings' menu and choose 'Basic Config' when the sub menu appears.");
            }

            $DB->query("SELECT * FROM ibf_skin_templates WHERE group_name='$real_name' AND set_id='{$template['set_id']}'");

            while (false !== ($data = $DB->fetch_row())) {
                $final .= "\n\nfunction {$data['func_name']}({$data['func_data']}) {\n" . "global \$ibforums;\n" . "return <<<EOF\n";

                $final .= $data['section_content'];

                $final .= "\nEOF;\n}\n";
            }

            $final .= "\n\n}\n?" . '>';

            if ($fh = fopen((string)$phpskin, 'wb')) {
                fwrite($fh, $final, mb_strlen($final));

                fclose($fh);
            } else {
                $ADMIN->error("Could not save information to $phpskin, please ensure that the CHMOD permissions are correct.");
            }
        }

        $ADMIN->done_screen('Template file updated', "Manage Templates in template set: {$row['skname']}", "act=templ&code=edit&id={$template['set_id']}");
    }

    //------------------------------------------------------------------------------------

    //------------------------------------------------------------------------------------

    public function tools()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $_POST;

        if ('' == $IN['id']) {
            $ADMIN->error('You must choose a valid skin file to perform this operation on');
        }

        if ('tmpl' == $IN['tool']) {
            $this->tools_build_tmpl();
        } else {
            $this->tools_rebuildphp();
        }
    }

    //------------------------------------------------------------------------------------

    public function tools_build_tmpl()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $_POST;

        $insert = 1;

        // Rebuilds the data editable files from the PHP source files

        $skin_dir = ROOT_PATH . 'Skin/s' . $IN['id'];

        /*$curr_groups = array();

        // Are we updating or inserting?

        $DB->query("SELECT group_name, func_name FROM ibf_skin_templates WHERE set_id='".$IN['id']."'");

        if ( $DB->get_num_rows() )
        {
            $insert = 0;

            while ( $gname = $DB->fetch_row() )
            {
                $curr_group[ $gname['group_name'] ][ $gname['func_name'] ] = 1;
            }
        }*/

        $errors = [];

        $flag = 0;

        //------------------------------------------------

        // Is this a safe mode only skinny poos?

        //------------------------------------------------

        if (!file_exists($skin_dir)) {
            $ADMIN->error('This template set is a safe mode only skin and no PHP skin files exist, there is no need to run this tool on this template set.');
        }

        if (!is_readable($skin_dir)) {
            $ADMIN->error("Cannot write into '$skin_dir', please check the CHMOD value, and if needed, CHMOD to 0777 via FTP. IBF cannot do this for you.");
        }

        if (is_dir($skin_dir)) {
            if ($handle = opendir($skin_dir)) {
                while (false !== ($filename = readdir($handle))) {
                    if (('.' != $filename) && ('..' != $filename)) {
                        if (preg_match("/\.php$/", $filename)) {
                            $name = preg_replace("/^(\S+)\.(\S+)$/", '\\1', $filename);

                            if ($FH = fopen($skin_dir . '/' . $filename, 'rb')) {
                                $fdata = fread($FH, filesize($skin_dir . '/' . $filename));

                                fclose($FH);
                            } else {
                                $errors[] = "Could not open $filename for reading, skipping file...";

                                continue;
                            }

                            $fdata = str_replace("\r", "\n", $fdata);

                            $fdata = str_replace("\n\n", "\n", $fdata);

                            if (!preg_match("/\n/", $fdata)) {
                                $errors[] = "Could not find any line endings in $filename, skipping file...";

                                continue;
                            }

                            $farray = explode("\n", $fdata);

                            //----------------------------------------------------

                            $functions = [];

                            foreach ($farray as $f) {
                                // Skip javascript functions...

                                if (preg_match('/<script/i', $f)) {
                                    $script_token = 1;
                                }

                                if (preg_match("/<\/script>/i", $f)) {
                                    $script_token = 0;
                                }

                                //-------------------------------

                                if (0 == $script_token) {
                                    if (preg_match("/^function\s*([\w\_]+)\s*\((.*)\)/i", $f, $matches)) {
                                        $functions[$matches[1]] = '';

                                        $config[$matches[1]] = $matches[2];

                                        $flag = $matches[1];

                                        continue;
                                    }
                                }

                                if ($flag) {
                                    $functions[$flag] .= $f . "\n";

                                    continue;
                                }
                            }

                            //----------------------------------------------------

                            // Remove current templates for this set...

                            //----------------------------------------------------

                            $DB->query("DELETE FROM ibf_skin_templates WHERE set_id='" . $IN['id'] . "' AND group_name='$name'");

                            $final = '';

                            $flag = 0;

                            foreach ($functions as $fname => $ftext) {
                                preg_match('/return <<<(EOF|HTML)(.+?)(EOF|HTML);/s', $ftext, $matches);

                                $matches[2] = str_replace('\\n', '\\\\\\n', $matches[2]);

                                $db_update = $DB->compile_db_update_string(
                                    [
                                        'set_id' => $IN['id'],
'group_name' => $name,
'section_content' => $matches[2],
'func_name' => $fname,
'func_data' => trim($config[$fname]),
'updated' => time(),
                                    ]
                                );

                                $DB->query("INSERT INTO ibf_skin_templates SET $db_update");
                            }

                            $functions = [];

                            //----------------------------------------------------
                        } // if *.php
                    } // if not dir
                } // while loop

                closedir($handle);
            } else {
                $ADMIN->error("Could not open directory $skin_dir for reading!");
            }
        } else {
            $ADMIN->error("$skin_dir is not a directory, please check the " . ROOT_PATH . ' variable in admin.php');
        }

        $ADMIN->done_screen('Editable templates updated from source PHP skin files', 'Manage Template sets', 'act=templ');

        if (count($errors > 0)) {
            $this->html .= $SKIN->start_table('Errors and warnings');

            $this->html .= $SKIN->add_td_basic(implode('<br>', $errors));

            $this->html .= $SKIN->end_table();
        }
    }

    //-------------------------------------------------------------

    // Add templates

    //-------------------------------------------------------------

    public function add_templates()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $_POST;

        if ('' == $IN['id']) {
            $ADMIN->error('You must specify an existing template set ID, go back and try again');
        }

        //-------------------------------------

        if (1 != $INFO['safe_mode_skins']) {
            if (SAFE_MODE_ON == 1) {
                $ADMIN->error("Safe mode detected, you will need to change the board configuration to switch 'Safe Mode Skins' on. To do this, click on the 'Board Settings' menu and choose 'Basic Config' when the sub menu appears.");
            }

            if (!is_writable(ROOT_PATH . 'Skin')) {
                $ADMIN->error("The directory 'Skin' is not writeable by this script. Please check the permissions on that directory. CHMOD to 0777 if in doubt and try again");
            }

            //-------------------------------------

            if (!is_dir(ROOT_PATH . 'Skin/s' . $IN['id'])) {
                $ADMIN->error('Could not locate the original template set to copy, please check and try again');
            }
        }

        //-------------------------------------

        $DB->query("SELECT * FROM ibf_tmpl_names WHERE skid='" . $IN['id'] . "'");

        //-------------------------------------

        if (!$row = $DB->fetch_row()) {
            $ADMIN->error('Could not query that template set from the DB, so there');
        }

        //-------------------------------------

        $row['skname'] .= '.NEW';

        // Insert a new row into the DB...

        $final = [];

        foreach ($row as $k => $v) {
            if ('skid' == $k) {
                continue;
            }

            $final[$k] = $v;
        }

        $db_string = $DB->compile_db_insert_string($final);

        $DB->query('INSERT INTO ibf_tmpl_names (' . $db_string['FIELD_NAMES'] . ') VALUES(' . $db_string['FIELD_VALUES'] . ')');

        $new_id = $DB->get_insert_id();

        //-------------------------------------

        if (1 != $INFO['safe_mode_skins']) {
            if (SAFE_MODE_ON == 1) {
                $ADMIN->error("Safe mode detected, you will need to change the board configuration to switch 'Safe Mode Skins' on. To do this, click on the 'Board Settings' menu and choose 'Basic Config' when the sub menu appears.");
            }

            //-------------------------------------

            if (!$ADMIN->copy_dir($INFO['base_dir'] . 'Skin/s' . $IN['id'], $INFO['base_dir'] . 'Skin/s' . $new_id)) {
                $DB->query("DELETE FROM ibf_tmpl_names WHERE skid='$new_id'");

                $ADMIN->error($ADMIN->errors);
            }
        }

        // Copy over the templates stored inthe database...

        $get = $DB->query("SELECT * FROM ibf_skin_templates WHERE set_id='" . $IN['id'] . "'");

        while (false !== ($r = $DB->fetch_row($get))) {
            $r['section_content'] = str_replace('\\n', '\\\\\\n', $r['section_content']);

            $row = $DB->compile_db_insert_string(
                [
                    'set_id' => $new_id,
'group_name' => $r['group_name'],
'section_content' => $r['section_content'],
'func_name' => $r['func_name'],
'func_data' => $r['func_data'],
'updated' => time(),
'can_remove' => $r['can_remove'],
                ]
            );

            $put = $DB->query("INSERT INTO ibf_skin_templates ({$row['FIELD_NAMES']}) VALUES({$row['FIELD_VALUES']})");
        }

        //-------------------------------------

        // All done, yay!

        //-------------------------------------

        $ADMIN->done_screen('New Template Set', 'Manage Template sets', 'act=templ');
    }

    //-------------------------------------------------------------

    // REMOVE WRAPPERS

    //-------------------------------------------------------------

    public function remove()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $_POST;

        //+-------------------------------

        if ('' == $IN['id']) {
            $ADMIN->error('You must specify an existing template set ID, go back and try again');
        }

        if (1 != $INFO['safe_mode_skins']) {
            if (SAFE_MODE_ON == 1) {
                $ADMIN->error("Safe mode detected, you will need to change the board configuration to switch 'Safe Mode Skins' on. To do this, click on the 'Board Settings' menu and choose 'Basic Config' when the sub menu appears.");
            }

            if (!$ADMIN->rm_dir($INFO['base_dir'] . 'Skin/s' . $IN['id'])) {
                $ADMIN->error('Could not remove the template files, please check the CHMOD permissions to ensure that this script has the correct permissions to allow this');
            }
        }

        $DB->query("DELETE FROM ibf_tmpl_names WHERE skid='" . $IN['id'] . "'");

        $DB->query("DELETE FROM ibf_skin_templates WHERE set_id='" . $IN['id'] . "'");

        $std->boink_it($SKIN->base_url . '&act=templ');

        exit();
    }

    //-------------------------------------------------------------

    // EDIT TEMPLATES, STEP TWO

    //-------------------------------------------------------------

    public function edit_bit()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $HTTP_COOKIE_VARS;

        //-----------------------------------

        // Check for valid input...

        //-----------------------------------

        if ('' == $IN['suid']) {
            $ADMIN->error('You must specify an existing template set ID, go back and try again');
        }

        $DB->query("SELECT * FROM ibf_skin_templates WHERE suid='" . $IN['suid'] . "'");

        //-----------------------------------

        if (!$template = $DB->fetch_row()) {
            $ADMIN->error('You must specify an existing template set ID, go back and try again');
        }

        //-----------------------------------

        if ($cookie = $HTTP_COOKIE_VARS['ad_tempform']) {
            [$rows, $cols] = explode('-', $cookie);
        }

        $cols = $cols ?: 80;

        $rows = $rows ?: 40;

        $wrap = 'soft';

        //+-------------------------------

        $DB->query("SELECT * from ibf_tmpl_names WHERE skid='" . $template['set_id'] . "'");

        if (!$row = $DB->fetch_row()) {
            $ADMIN->error('Could not query the information from the database');
        }

        //+-------------------------------

        // Swop < and > into ascii entities

        // to prevent textarea breaking html

        //+-------------------------------

        $templ = $this->convert_tags($template['section_content']);

        $templ = preg_replace('/&/', '&#38;', $templ);

        $templ = preg_replace('/</', '&#60;', $templ);

        $templ = preg_replace('>/', '&#62;', $templ);

        //+-------------------------------

        $ADMIN->page_detail = 'You may edit the HTML of this template.';

        $ADMIN->page_title = 'Template Editing';

        //+-------------------------------

        //+-------------------------------

        $ADMIN->html .= $SKIN->js_template_tools();

        $ADMIN->html .= $SKIN->start_form(
            [
                1 => ['code', 'doedit'],
2 => ['act', 'templ'],
3 => ['suid', $IN['suid']],
            ],
            'theform'
        );

        //+-------------------------------

        $SKIN->td_header[] = ['&nbsp;', '100%'];

        //+-------------------------------

        $ADMIN->html .= $SKIN->start_table('Template: ' . $template['func_name']);

        $ADMIN->html .= $SKIN->add_td_basic(
            "<input type='button' value='Macro Look-up' id='editbutton' title='View a macro definition' onClick='pop_win(\"code=macro_one&suid={$template['suid']}\", \"MacroWindow\", 400, 200)'>"
            . "&nbsp;<input type='button' value='Compare' id='editbutton' title='Compare the edited version to the original' onClick='pop_win(\"act=rtempl&code=compare&suid={$template['suid']}\", \"CompareWindow\", 500,400)'>"
            . "&nbsp;<input type='button' value='Restore' id='editbutton' title='Restore the original, unedited template bit' onClick='restore(\"{$template['suid']}\",\"{$IN['expand']}\")'>"
            . "&nbsp;<input type='button' value='View Original' id='editbutton' title='View the HTML for the unedited template bit' onClick='pop_win(\"act=rtempl&code=preview&suid={$template['suid']}&type=html\", \"OriginalPreview\", 400,400)'>"
            . "&nbsp;<input type='button' value='Search' id='editbutton' title='Search the templates for a string' onClick='pop_win(\"act=rtempl&code=search&suid={$template['suid']}&type=html\", \"Search\", 500,400)'>"
            . "&nbsp;<input type='button' value='Edit Box Size' id='editbutton' title='Change the size of the edit box below' onClick='edit_box_size(\"$cols\", \"$rows\")'>",
            'center',
            'catrow'
        );

        $ADMIN->html .= $SKIN->add_td_basic(
            '<b>Show me the HTML code for:&nbsp;' . "<select name='htmlcode' onChange=\"document.theform.res.value='&'+document.theform.htmlcode.options[document.theform.htmlcode.selectedIndex].value+';'\" id='multitext'><option value='copy'>&copy;</option>
											 <option value='raquo'>&raquo;</option>
											 <option value='laquo'>&laquo;</option>
											 <option value='#149'>&#149;</option>
											 <option value='reg'>&reg;</option>
											 </select>&nbsp;&nbsp;<input type='text' name='res' size=20 id='multitext'>&nbsp;&nbsp;<input type='button' value='select' id='editbutton' onClick='document.theform.res.focus();document.theform.res.select();'>",
            'center',
            'tdrow1'
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<center>' . $SKIN->form_textarea('template', $templ, $cols, $rows, $wrap) . '</center>',
            ]
        );

        $ADMIN->html .= $SKIN->end_form('Update this file');

        $ADMIN->html .= $SKIN->end_table();

        $ADMIN->nav[] = ['act=templ', 'Template Control Home'];

        $ADMIN->nav[] = ["act=templ&code=edit&id={$template['set_id']}", $row['skname']];

        $ADMIN->nav[] = ["act=templ&code=edit&id={$template['set_id']}&expand={$IN['expand']}", $template['group_name']];

        $ADMIN->nav[] = ['', $template['func_name']];

        $ADMIN->output();
    }

    //-------------------------------------------------------------

    // EDIT TEMPLATES, STEP ONE

    //-------------------------------------------------------------

    public function do_form()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        if ('' == $IN['id']) {
            $ADMIN->error('You must specify an existing template set ID, go back and try again');
        }

        //+-------------------------------

        $DB->query("SELECT * from ibf_tmpl_names WHERE skid='" . $IN['id'] . "'");

        if (!$row = $DB->fetch_row()) {
            $ADMIN->error('Could not query the information from the database');
        }

        $form_array = [];

        //+-------------------------------

        $ADMIN->page_detail = 'Please choose which section you wish to edit below.';

        $ADMIN->page_title = 'Edit Template Set Data';

        //+-------------------------------

        //+-------------------------------

        $ADMIN->html .= $SKIN->js_no_specialchars();

        $ADMIN->html .= $SKIN->start_form(
            [
                1 => ['code', 'editinfo'],
2 => ['act', 'templ'],
3 => ['id', $IN['id']],
            ],
            'theAdminForm',
            "onSubmit=\"return no_specialchars('templates')\""
        );

        //+-------------------------------

        $SKIN->td_header[] = ['&nbsp;', '60%'];

        $SKIN->td_header[] = ['&nbsp;', '40%'];

        //+-------------------------------

        $ADMIN->html .= $SKIN->start_table('Edit template information');

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Template Set Name</b>',
                $SKIN->form_input('skname', $row['skname']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Template set author name:</b>',
                $SKIN->form_input('author', $row['author']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Template set author email:</b>',
                $SKIN->form_input('email', $row['email']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Template set author webpage:</b>',
                $SKIN->form_input('url', $row['url']),
            ]
        );

        $ADMIN->html .= $SKIN->end_form('Edit template set details');

        $ADMIN->html .= $SKIN->end_table();

        $ADMIN->nav[] = ['act=templ', 'Template Control Home'];

        $ADMIN->output();
    }

    //-------------------------------------------------------------

    // SHOW CURRENT TEMPLATE PACKS

    //-------------------------------------------------------------

    public function list_current()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        $form_array = [];

        $ADMIN->page_detail = 'The skin templates contain the all the HTML that is used by the board.
							   <br>
							   This section will allow you to create new template sets or edit HTML in your current template sets.';

        $ADMIN->page_title = 'Manage Template Sets';

        //+-------------------------------

        $SKIN->td_header[] = ['Title', '30%'];

        $SKIN->td_header[] = ['Allocation', '20%'];

        $SKIN->td_header[] = ['Edit&nbsp;Properties', '20%'];

        $SKIN->td_header[] = ['Manage HTML', '20%'];

        $SKIN->td_header[] = ['Remove', '10%'];

        //+-------------------------------

        $DB->query('SELECT DISTINCT(s.set_id), s.sname, t.skid, t.skname FROM ibf_tmpl_names t, ibf_skins s WHERE s.set_id=t.skid ORDER BY t.skname ASC');

        $used_ids = [];

        $show_array = [];

        if ($DB->get_num_rows()) {
            $ADMIN->html .= $SKIN->start_table('Current Template sets In Use');

            while (false !== ($r = $DB->fetch_row())) {
                $show_array[$r['skid']] .= stripslashes($r['sname']) . '<br>';

                if (in_array($r['skid'], $used_ids, true)) {
                    continue;
                }

                $ADMIN->html .= $SKIN->add_td_row(
                    [
                        '<b>' . stripslashes($r['skname']) . "</b><br>[ <a href='{$SKIN->base_url}&act=templ&code=export&id={$r['skid']}' title='Download this complete template set'>Export</a> ]",
                        "<#X-{$r['skid']}#>",
                        //"<center><a href='".$SKIN->base_url."&act=templ&code=export&id={$r['skid']}'>Download</a></center>",
                        "<center><a href='" . $SKIN->base_url . "&act=templ&code=dedit&id={$r['skid']}' title='Edit Template Set Name'>Edit Properties</a></center>",
                        "<center><a href='" . $SKIN->base_url . "&act=templ&code=edit&id={$r['skid']}' title='Edit, upload and download'>Manage HTML</a></center>",
                        '<i>Deallocate before removing</i>',
                    ]
                );

                $used_ids[] = $r['skid'];

                $form_array[] = [$r['skid'], $r['skname']];
            }

            foreach ($show_array as $idx => $string) {
                $string = preg_replace('/<br>$/', '', $string);

                $ADMIN->html = preg_replace("/<#X-$idx#>/", (string)$string, $ADMIN->html);
            }

            $ADMIN->html .= $SKIN->end_table();
        }

        if (count($used_ids) > 0) {
            $DB->query('SELECT skid, skname FROM ibf_tmpl_names WHERE skid NOT IN(' . implode(',', $used_ids) . ')');

            if ($DB->get_num_rows()) {
                $SKIN->td_header[] = ['Title', '50%'];

                $SKIN->td_header[] = ['Edit&nbsp;Properties', '20%'];

                $SKIN->td_header[] = ['Manage HTML', '20%'];

                $SKIN->td_header[] = ['Remove', '10%'];

                $ADMIN->html .= $SKIN->start_table('Current Unallocated Template sets');

                $ADMIN->html .= $SKIN->js_checkdelete();

                while (false !== ($r = $DB->fetch_row())) {
                    $ADMIN->html .= $SKIN->add_td_row(
                        [
                            '<b>' . stripslashes($r['skname']) . '</b>',
                            //"<center><a href='".$SKIN->base_url."&act=templ&code=export&id={$r['skid']}'>Download</a></center>",
                            "<center><a href='" . $SKIN->base_url . "&act=templ&code=dedit&id={$r['skid']}'>Edit Properties</a></center>",
                            "<center><a href='" . $SKIN->base_url . "&act=templ&code=edit&id={$r['skid']}' title='Edit, upload and download'>Manage HTML</a></center>",
                            "<center><a href='javascript:checkdelete(\"act=templ&code=remove&id={$r['skid']}\")'>Remove</a></center>",
                        ]
                    );

                    $form_array[] = [$r['skid'], $r['skname']];
                }

                $ADMIN->html .= $SKIN->end_table();
            }
        }

        //+-------------------------------

        //+-------------------------------

        $ADMIN->html .= $SKIN->start_form(
            [
                1 => ['code', 'add'],
2 => ['act', 'templ'],
3 => ['MAX_FILE_SIZE', '10000000000'],
            ],
            'uploadform',
            " enctype='multipart/form-data'"
        );

        $SKIN->td_header[] = ['&nbsp;', '40%'];

        $SKIN->td_header[] = ['&nbsp;', '60%'];

        $ADMIN->html .= $SKIN->start_table('Create New Template Set');

        //+-------------------------------

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Base new Template set on...</b>',
                $SKIN->form_dropdown('id', $form_array),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b><u>OR</u> Choose a file from your computer to import</b><br>Note: This must be a template group set.',
                $SKIN->form_upload(),
            ]
        );

        $ADMIN->html .= $SKIN->end_form('Create new Template set');

        $ADMIN->html .= $SKIN->end_table();

        //+-------------------------------

        //+-------------------------------

        $ADMIN->html .= $SKIN->start_form(
            [
                1 => ['code', 'tools'],
2 => ['act', 'templ'],
            ]
        );

        $SKIN->td_header[] = ['Tool', '50%'];

        $SKIN->td_header[] = ['run on template set', '50%'];

        $extra = '';

        if (SAFE_MODE_ON == 1) {
            $extra = "<br><span id='detail'>WARNING: Safe mode restrictions detected, some of these tools will not work</span>";
        }

        $ADMIN->html .= $SKIN->start_table('Template Tools' . $extra);

        //+-------------------------------

        $ADMIN->html .= $SKIN->add_td_row(
            [
                $SKIN->form_dropdown(
                    'tool',
                    [
                        1 => ['tmpl', 'Resynchronise the database templates FROM the PHP skin files'],
                    ]
                ),
                $SKIN->form_dropdown('id', $form_array),
            ]
        );

        $ADMIN->html .= $SKIN->end_form('Run Tool');

        $ADMIN->html .= $SKIN->end_table();

        //+-------------------------------

        //+-------------------------------

        $ADMIN->output();
    }

    public function convert_tags($t = '')
    {
        if ('' == $t) {
            return '';
        }

        $t = preg_replace('/{?\\$ibforums->base_url}?/', '{ibf.script_url}', $t);

        $t = preg_replace('/{?\\$ibforums->session_id}?/', '{ibf.session_id}', $t);

        $t = preg_replace("/{?\\\$ibforums->skin\['?(\w+)'?\]}?/", '{ibf.skin.\\1}', $t);

        $t = preg_replace("/{?\\\$ibforums->lang\['?(\w+)'?\]}?/", '{ibf.lang.\\1}', $t);

        $t = preg_replace("/{?\\\$ibforums->vars\['?(\w+)'?\]}?/", '{ibf.vars.\\1}', $t);

        $t = preg_replace("/{?\\\$ibforums->member\['?(\w+)'?\]}?/", '{ibf.member.\\1}', $t);

        // Make some tags safe..

        $t = preg_replace("/\{ibf\.vars\.(sql_driver|sql_host|sql_database|sql_pass|sql_user|sql_port|sql_tbl_prefix|smtp_host|smtp_port|smtp_user|smtp_pass|html_dir|base_dir|upload_dir)\}/", '', $t);

        return $t;
    }

    public function unconvert_tags($t = '')
    {
        if ('' == $t) {
            return '';
        }

        // Make some tags safe..

        $t = preg_replace("/\{ibf\.vars\.(sql_driver|sql_host|sql_database|sql_pass|sql_user|sql_port|sql_tbl_prefix|smtp_host|smtp_port|smtp_user|smtp_pass|html_dir|base_dir|upload_dir)\}/", '', $t);

        $t = preg_replace("/{ibf\.script_url}/i", '{$ibforums->base_url}', $t);

        $t = preg_replace("/{ibf\.session_id}/i", '{$ibforums->session_id}', $t);

        $t = preg_replace("/{ibf\.skin\.(\w+)}/", '{$ibforums->skin[\'' . '\\1' . '\']}', $t);

        $t = preg_replace("/{ibf\.lang\.(\w+)}/", '{$ibforums->lang[\'' . '\\1' . '\']}', $t);

        $t = preg_replace("/{ibf\.vars\.(\w+)}/", '{$ibforums->vars[\'' . '\\1' . '\']}', $t);

        $t = preg_replace("/{ibf\.member\.(\w+)}/", '{$ibforums->member[\'' . '\\1' . '\']}', $t);

        return $t;
    }

    /*
    <!--IBF_START_FUNC|calendar_events|$events = ""-->
        <tr>
           <td id='category' colspan='2'>{ibf.lang.calender_f_title}</td>
        </tr>
        <tr>
          <td id='forum1' width='5%' valign='middle'>{ibf.skin.F_ACTIVE}</td>
          <td id='forum2' width='95%'>$events</td>
        </tr>
    <!--IBF_END_FUNC|calendar_events-->
*/

    public function process_upload($raw, $setid, $group, $isnew = 0)
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        $skin_dir = ROOT_PATH . 'Skin/s' . $setid;

        //-------------------------------------------

        // If we are not using safe mode skins, lets

        // test to make sure we can write to that dir

        //-------------------------------------------

        if (1 != $INFO['safe_mode_skins']) {
            if (SAFE_MODE_ON == 1) {
                if (1 == $isnew) {
                    $DB->query("DELETE FROM ibf_tmpl_names WHERE skid='$setid'");
                }

                $ADMIN->error("Safe mode detected, you will need to change the board configuration to switch 'Safe Mode Skins' on. To do this, click on the 'Board Settings' menu and choose 'Basic Config' when the sub menu appears.");
            }

            // Are we creating a new template set?

            // if so, lets create the directory

            if (1 == $isnew) {
                if (!is_writable(ROOT_PATH . 'Skin')) {
                    $DB->query("DELETE FROM ibf_tmpl_names WHERE skid='$setid'");

                    $ADMIN->error("The directory 'Skin' is not writeable by this script. Please check the permissions on that directory. CHMOD to 0777 if in doubt and try again");
                }

                if (!file_exists($skin_dir)) {
                    // Directory does not exist, lets create it

                    if (!@mkdir($skin_dir, 0777)) {
                        $DB->query("DELETE FROM ibf_tmpl_names WHERE skid='$setid'");

                        $ADMIN->error("Could not create directory '$skin_dir' please check the CHMOD permissions and re-try");
                    } else {
                        @chmod($skin_dir, 0777);
                    }
                }
            } else {
                if (!is_writable($skin_dir)) {
                    $ADMIN->error("Cannot write into '$skin_dir', please check the CHMOD value, and if needed, CHMOD to 0777 via FTP. IBF cannot do this for you.");
                }
            }
        }

        //--------------------------------

        // Remove everything up until the

        // first <!--START tag...

        //--------------------------------

        $raw = preg_replace('/^.*?(<!--IBF_START_FUNC)/s', '\\1', trim($raw));

        $raw = str_replace("\r\n", "\n", $raw);

        //+-------------------------------

        // Convert the tags back to php native

        //+-------------------------------

        $raw = $this->unconvert_tags($raw);

        //+-------------------------------

        // Grab our vars and stuff

        //+-------------------------------

        $DB->query("SELECT func_name, group_name FROM ibf_skin_templates WHERE set_id='$setid'");

        if ($DB->get_num_rows()) {
            while (false !== ($gname = $DB->fetch_row())) {
                $curr_group[$gname['group_name']][$gname['func_name']] = 1;
            }
        }

        $master = [];

        $flag = 0;

        $eachline = explode("\n", $raw);

        foreach ($eachline as $line) {
            if (0 == $flag) {
                // We're not gathering HTML, lets see if we have a new

                // function start..

                if (preg_match("/\s*<!--IBF_START_FUNC\|(\S+?)\|(.*?)-->\s*/", $line, $matches)) {
                    $func = trim($matches[1]);

                    $data = trim($matches[2]);

                    if ('' != $func) {
                        $flag = $func;

                        $master[$func] = [
                            'func_name' => $func,
'func_data' => $data,
'content' => '',
                        ];
                    }

                    continue;
                }
            }

            if (preg_match("/\s*?<!--IBF_END_FUNC\|$flag-->\s*?/", $line)) {
                // We have found the end of the subbie..

                // Reset the flag and feed the next line.

                $flag = 0;

                continue;
            }

            // Carry on feeding the HTML...

            if (isset($master[$flag]['content'])) {
                $master[$flag]['content'] .= $line . "\n";

                continue;
            }
        }

        //+-------------------------------

        // Start parsing the php skin file

        //+-------------------------------

        if (1 != $INFO['safe_mode_skins']) {
            if (SAFE_MODE_ON == 1) {
                $ADMIN->error("Safe mode detected, you will need to change the board configuration to switch 'Safe Mode Skins' on. To do this, click on the 'Board Settings' menu and choose 'Basic Config' when the sub menu appears.");
            }

            $final = '<' . "?php\n\n" . "class $group {\n\n";

            foreach ($master as $func_name => $data) {
                $final .= "\n\nfunction " . trim($data['func_name']) . '(' . trim($data['func_data']) . ") {\n" . "global \$ibforums;\n" . "return <<<EOF\n";

                $final .= trim($data['content']);

                $final .= "\nEOF;\n}\n";
            }

            $final .= "\n\n}\n?" . '>';

            if ($fh = fopen($skin_dir . '/' . $group . '.php', 'wb')) {
                fwrite($fh, $final, mb_strlen($final));

                fclose($fh);
            } else {
                if (1 == $isnew) {
                    $DB->query("DELETE FROM ibf_tmpl_names WHERE skid='$setid'");
                }

                $errors[] = "Could not save information to $phpskin, please ensure that the CHMOD permissions are correct.";
            }
        }

        //+-------------------------------

        // Update the DB

        //+-------------------------------

        foreach ($master as $func_name => $data) {
            if (0 == $isnew) {
                if (1 != $curr_group[$group][$func_name]) {
                    // Not a current group/ func..

                    $isnew = 1;
                }
            }

            if (0 == $isnew) {
                $data['content'] = str_replace('\\n', '\\\\\\n', $data['content']);

                $str = $DB->compile_db_update_string(
                    [
                        'section_content' => trim($data['content']),
'func_data' => trim($data['func_data']),
                    ]
                );

                $DB->query("UPDATE ibf_skin_templates SET $str WHERE set_id='$setid' AND group_name='$group' AND func_name='" . trim($data['func_name']) . "'");
            } else {
                $data['content'] = str_replace('\\n', '\\\\\\n', $data['content']);

                $str = $DB->compile_db_insert_string(
                    [
                        'section_content' => trim($data['content']),
'func_data' => trim($data['func_data']),
'set_id' => $setid,
'group_name' => $group,
'func_name' => trim($data['func_name']),
'can_remove' => 0,
                    ]
                );

                $DB->query("INSERT INTO ibf_skin_templates ({$str['FIELD_NAMES']}) VALUES({$str['FIELD_VALUES']})");
            }
        }

        return true;
    }

    /*foreach($functions as $fname => $ftext)
      {
          preg_match( "/return <<<(EOF|HTML)(.+?)(EOF|HTML);/s", $ftext, $matches );

          // Are we updating a current set, but have a new group to add?
          // Who knows, but it's bloody exciting

          if ($insert == 0)
          {
              if ($curr_group[$name][$fname] != 1)
              {
                  // Not a current group..

                  $insert = 1;
              }
          }

          // Swap fake newlines

          $matches[2] = str_replace( '\n', '\\\n', $matches[2] );

          // Swap real newlines
          //$matches[2] = str_replace( "\r", '\\r', $matches[2] );
          //$matches[2] = str_replace( "\n", '\\n', $matches[2] );

          if ($insert == 0)
          {

              $db_update = $DB->compile_db_update_string( array (
                                                              'section_content' => str_replace( '\n', '\\\n', $matches[2] ),
                                                              'func_data'       => trim($config[$fname]),
                                                              'updated'         => time(),
                                                    )       );

              $DB->query("UPDATE ibf_skin_templates SET $db_update WHERE func_name='$fname' AND set_id='".$IN['id']."' AND group_name='$name'");
          }
          else
          {

              $db_update = $DB->compile_db_update_string( array (
                                                              'set_id'          => $IN['id'],
                                                              'group_name'      => $name,
                                                              'section_content' => str_replace( '\n', '\\\n', $matches[2] ),
                                                              'func_name'       => $fname,
                                                              'func_data'       => trim($config[$fname]),
                                                              'updated'         => time(),
                                                    )       );

              //----------------------------------------------------
              // Remove current templates with this name
              //----------------------------------------------------

              //$DB->query("DELETE FROM ibf_skin_templates WHERE func_name='$fname' AND set_id='".$IN['id']."' AND group_name='$name'");

              $DB->query("INSERT INTO ibf_skin_templates SET $db_update");
          //}


      }*/
}

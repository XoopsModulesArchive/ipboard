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
|   > CSS management functions
|   > Module written by Matt Mecham
|   > Date started: 4th April 2002
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
            case 'wrapper':
                $this->list_sheets();
                break;
            case 'add':
                $this->do_form('add');
                break;
            case 'edit2':
                $this->do_form('edit');
                break;
            case 'edit':
                //$this->edit_splash();
                $this->do_form('edit');
                break;
            //break;

            case 'doadd':
                $this->save_wrapper('add');
                break;
            case 'doedit':
                $this->save_wrapper('edit');
                break;
            case 'remove':
                $this->remove();
                break;
            case 'export':
                $this->export();
                break;
            case 'optimize':
                $this->optimize();
                break;
            case 'css_upload':
                $this->css_upload('new');
                break;
            case 'easyedit':
                $this->easy_edit();
                break;
            case 'editcomments':
                $this->edit_comments();
                break;
            case 'doeditcomments':
                $this->do_edit_comments();
                break;
            //-------------------------
            default:
                $this->list_sheets();
                break;
        }
    }

    //+-------------------------------

    //+-------------------------------

    public function do_edit_comments()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $_POST;

        //+-------------------------------

        if ('' == $IN['id']) {
            $ADMIN->error('You must specify an existing wrapper ID, go back and try again');
        }

        //+-------------------------------

        $DB->query("SELECT cssid, css_text, css_name, css_comments FROM ibf_css WHERE cssid='" . $IN['id'] . "'");

        if (!$cssinfo = $DB->fetch_row()) {
            $ADMIN->error('Could not query the CSS details from the database');
        }

        $DB->query("UPDATE ibf_css SET css_comments='{$IN['comments']}' WHERE cssid='" . $IN['id'] . "'");

        $ADMIN->done_screen('Comments Updated', 'Manage Style Sheets', 'act=style');
    }

    //+-------------------------------

    public function edit_comments()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        //+-------------------------------

        if ('' == $IN['id']) {
            $ADMIN->error('You must specify an existing wrapper ID, go back and try again');
        }

        //+-------------------------------

        $DB->query("SELECT cssid, css_text, css_name, css_comments FROM ibf_css WHERE cssid='" . $IN['id'] . "'");

        if (!$cssinfo = $DB->fetch_row()) {
            $ADMIN->error('Could not query the CSS details from the database');
        }

        //+-------------------------------

        $ADMIN->page_detail = 'Please enter or edit the comments below';

        $ADMIN->page_title = "Edit Comments for Style Sheet '{$cssinfo['css_name']}'";

        //+-------------------------------

        $SKIN->td_header[] = ['CSS', '40%'];

        $SKIN->td_header[] = ['Comment', '60%'];

        //+-------------------------------

        $ADMIN->html .= $SKIN->start_form(
            [
                1 => ['code', 'doeditcomments'],
2 => ['act', 'style'],
3 => ['id', $IN['id']],
            ]
        );

        $ADMIN->html .= $SKIN->start_table('Edit Comments');

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Edit the comments for this stylesheet',
                $SKIN->form_textarea('comments', str_replace('<br>', "\n", $cssinfo['comments'])),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_basic("Back to: <a href='" . $SKIN->base_url . "&act=style'>Manage Style Sheets</a> -&gt; <a href='" . $SKIN->base_url . "&act=style&code=edit&id={$IN['id']}'>Edit Options</a>", 'center', 'title');

        $ADMIN->html .= $SKIN->end_form('Edit the comments');

        $ADMIN->html .= $SKIN->end_table();

        //+-------------------------------

        //+-------------------------------

        $ADMIN->output();
    }

    //-------------------------------------------------------------

    // ADD / EDIT WRAPPERS

    //-------------------------------------------------------------

    public function edit_splash()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        //+-------------------------------

        if ('' == $IN['id']) {
            $ADMIN->error('You must specify an existing wrapper ID, go back and try again');
        }

        //+-------------------------------

        $DB->query("SELECT cssid, css_text, css_name FROM ibf_css WHERE cssid='" . $IN['id'] . "'");

        if (!$cssinfo = $DB->fetch_row()) {
            $ADMIN->error('Could not query the CSS details from the database');
        }

        //+-------------------------------

        $ADMIN->page_detail = 'Please select your editing option.';

        $ADMIN->page_title = "Edit Style Sheet '{$cssinfo['css_name']}'";

        //+-------------------------------

        $SKIN->td_header[] = ['Option', '40%'];

        $SKIN->td_header[] = ['Description', '60%'];

        //+-------------------------------

        $ADMIN->html .= $SKIN->start_table('Choose an option');

        $ADMIN->html .= $SKIN->add_td_row(
            [
                "<a href='" . $SKIN->base_url . "&act=style&code=edit2&id={$IN['id']}'>CSS Editing</a>",
                'Edit the stylesheet as a complete resource in a similar environment as a text editor',
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                "<a href='" . $SKIN->base_url . "&act=style&code=editupload&id={$IN['id']}'>Upload a stylesheet</a>",
                'Upload a stylesheet from your hard drive to replace the one stored in the database',
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                "<a href='" . $SKIN->base_url . "&act=style&code=editcomments&id={$IN['id']}'>Edit Comments</a>",
                'Edit the comments that indicate where the CSS definitions are used in the templates',
            ]
        );

        $ADMIN->html .= $SKIN->add_td_basic("Back to: <a href='" . $SKIN->base_url . "&act=style'>Manage Style Sheets</a>", 'center', 'title');

        $ADMIN->html .= $SKIN->end_table();

        //+-------------------------------

        //+-------------------------------

        $ADMIN->output();
    }

    public function css_upload($type = 'new')
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

        // Move the uploaded file to somewhere we can

        // manipulate it in safe mode

        //-------------------------------------------------

        if (!@move_uploaded_file($HTTP_POST_FILES['FILE_UPLOAD']['tmp_name'], $INFO['upload_dir'] . '/' . $FILE_NAME)) {
            $ADMIN->error('The upload failed');
        }

        // Open the file and copy to the DB

        $real_name = str_replace('_', ' ', preg_replace("/^(.*),\d+\.css$/", '\\1', $FILE_NAME));

        $real_name .= ' [UPLOAD]';

        if ($FH = @fopen($INFO['upload_dir'] . '/' . $FILE_NAME, 'rb')) {
            $data = @fread($FH, @filesize($INFO['upload_dir'] . '/' . $FILE_NAME));

            @fclose($FH);

            @unlink($INFO['upload_dir'] . '/' . $FILE_NAME);
        } else {
            @unlink($INFO['upload_dir'] . '/' . $FILE_NAME);

            $ADMIN->error('Could not open the uploaded file for reading, aborting process');
        }

        [$css, $comments] = explode('<|COMMENTS|>', $data);

        $css = trim($css);

        $comments = trim($css);

        if ('new' == $type) {
            $dbs = $DB->compile_db_insert_string(
                [
                    'css_name' => $real_name,
'css_text' => $css,
'css_comments' => $comments,
                ]
            );

            $DB->query('INSERT INTO ibf_css (' . $dbs['FIELD_NAMES'] . ') VALUES(' . $dbs['FIELD_VALUES'] . ')');

            $ADMIN->done_screen('Stylesheet uploaded', 'Manage Style Sheets', 'act=style');
        }
    }

    //----------------------------------------------------

    public function optimize()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        if ('' == $IN['id']) {
            $ADMIN->error('You must specify an existing CSS ID, go back and try again');
        }

        //+-------------------------------

        $DB->query("SELECT * from ibf_css WHERE cssid='" . $IN['id'] . "'");

        if (!$row = $DB->fetch_row()) {
            $ADMIN->error('Could not query the information from the database');
        }

        //+-------------------------------

        $orig_size = mb_strlen($row['css_text']);

        $orig_text = str_replace("\r\n", "\n", $row['css_text']);

        $orig_text = str_replace("\r", "\n", $orig_text);

        $orig_text = str_replace("\n\n", "\n", $orig_text);

        $parsed = [];

        // Remove comments

        $orig_text = preg_replace("#/\*(.+?)\*/#s", '', $orig_text);

        // Grab all the definitions

        preg_match_all("/(.+?)\{(.+?)\}/s", $orig_text, $match, PREG_PATTERN_ORDER);

        for ($i = 0, $iMax = count($match[0]); $i < $iMax; $i++) {
            $match[1][$i] = trim($match[1][$i]);

            $parsed[$match[1][$i]] = trim($match[2][$i]);
        }

        //------------------

        if (count($parsed) < 1) {
            $ADMIN->error('The stylesheet is in a format that Invision Power Board cannot understand, no optimization done.');
        }

        // Clean them up

        $final = '';

        foreach ($parsed as $name => $p) {
            // Ignore comments

            if (preg_match('#^//#', $name)) {
                continue;
            }

            // Split up the components

            $parts = explode(';', $p);

            $defs = [];

            foreach ($parts as $part) {
                if ('' != $part) {
                    [$definition, $data] = explode(':', $part);

                    $defs[] = trim($definition) . ': ' . trim($data);
                }
            }

            $final .= $name . ' { ' . implode('; ', $defs) . " }\n";
        }

        $final_size = mb_strlen($final);

        if ($final_size < 1000) {
            $ADMIN->error('The stylesheet is in a format that Invision Power Board cannot understand, no optimization done.');
        }

        // Update the DB

        $dbs = $DB->compile_db_update_string(['css_text' => $final]);

        $DB->query("UPDATE ibf_css SET $dbs WHERE cssid='" . $IN['id'] . "'");

        $saved = $orig_size - $final_size;

        $pc_saved = 0;

        if ($saved > 0) {
            $pc_saved = sprintf('%.2f', ($saved / $orig_size) * 100);
        }

        $ADMIN->done_screen("Stylesheet updated<br>Characters Saved: $saved ($pc_saved %)", 'Manage Style Sheets', 'act=style');
    }

    //----------------------------------------------------

    public function export()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        if ('' == $IN['id']) {
            $ADMIN->error('You must specify an existing CSS ID, go back and try again');
        }

        //+-------------------------------

        $DB->query("SELECT * from ibf_css WHERE cssid='" . $IN['id'] . "'");

        if (!$row = $DB->fetch_row()) {
            $ADMIN->error('Could not query the information from the database');
        }

        //+-------------------------------

        $name = str_replace(' ', '_', $row['css_name']);

        @header('Content-type: unknown/unknown');

        @header("Content-Disposition: attachment; filename=$name,{$row['cssid']}.css");

        print $row['css_text'];

        exit();
    }

    //-------------------------------------------------------------

    // REMOVE WRAPPERS

    //-------------------------------------------------------------

    public function remove()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $_POST;

        //+-------------------------------

        if ('' == $IN['id']) {
            $ADMIN->error('You must specify an existing stylesheet ID, go back and try again');
        }

        $DB->query("DELETE FROM ibf_css WHERE cssid='" . $IN['id'] . "'");

        $std->boink_it($SKIN->base_url . '&act=style');

        exit();
    }

    //-------------------------------------------------------------

    // ADD / EDIT WRAPPERS

    //-------------------------------------------------------------

    public function save_wrapper($type = 'add')
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $_POST;

        //+-------------------------------

        if ('edit' == $type) {
            if ('' == $IN['id']) {
                $ADMIN->error('You must specify an existing CSS ID, go back and try again');
            }
        }

        if ('' == $IN['name']) {
            $ADMIN->error('You must specify a name for this stylesheet');
        }

        if ('' == $IN['css']) {
            $ADMIN->error("You can't have an empty stylesheet, can you?");
        }

        $css = stripslashes($_POST['css']);

        $barney = [
            'css_name' => stripslashes($_POST['name']),
'css_text' => $css,
        ];

        if ('add' == $type) {
            $db_string = $DB->compile_db_insert_string($barney);

            $DB->query('INSERT INTO ibf_css (' . $db_string['FIELD_NAMES'] . ') VALUES(' . $db_string['FIELD_VALUES'] . ')');

            $new_id = $DB->get_insert_id();

            $std->boink_it($SKIN->base_url . '&act=style');

            exit();
        }

        $db_string = $DB->compile_db_update_string($barney);

        $DB->query("UPDATE ibf_css SET $db_string WHERE cssid='" . $IN['id'] . "'");

        $ADMIN->nav[] = ['act=style', 'Style Sheet Control Home'];

        $ADMIN->nav[] = ["act=style&code=edit2&id={$IN['id']}", 'Edit Sheet Again'];

        $ADMIN->done_screen('Stylesheet updated', 'Manage Style Sheets', 'act=style');
    }

    //-------------------------------------------------------------

    // ADD / EDIT WRAPPERS

    //-------------------------------------------------------------

    public function do_form($type = 'add')
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        //+-------------------------------

        if ('' == $IN['id']) {
            $ADMIN->error('You must specify an existing wrapper ID, go back and try again');
        }

        //+-------------------------------

        $DB->query("SELECT cssid, css_text, css_name FROM ibf_css WHERE cssid='" . $IN['id'] . "'");

        if (!$cssinfo = $DB->fetch_row()) {
            $ADMIN->error('Could not query the CSS details from the database');
        }

        //+-------------------------------

        $css = $cssinfo['css_text'];

        if ('add' == $type) {
            $code = 'doadd';

            $button = 'Create StyleSheet';

            $cssinfo['css_name'] .= '.2';
        } else {
            $code = 'doedit';

            $button = 'Edit Stylesheet';
        }

        //+-------------------------------

        // Start the CSS matcher thingy

        //+-------------------------------

        //.class { definitions }

        //#id { definitions }

        $css_elements = [];

        preg_match_all("/(\.|\#)(\S+?)\s{0,}\{.+?\}/s", $css, $match);

        for ($i = 0, $iMax = count($match[0]); $i < $iMax; $i++) {
            $type = trim($match[1][$i]);

            $name = trim($match[2][$i]);

            if ('.' == $type) {
                $css_elements[] = ['class|' . $name, $type . $name];
            } else {
                $css_elements[] = ['id|' . $name, $type . $name];
            }
        }

        //+-------------------------------

        $ADMIN->page_detail = 'You may use CSS fully when adding or editing stylesheets.';

        $ADMIN->page_title = 'Manage Style Sheets';

        //+-------------------------------

        $ADMIN->html .= "<script language='javascript'>
		                 <!--
		                 function cssSearch(theID)
		                 {
		                 	cssChosen = document.cssForm.csschoice.options[document.cssForm.csschoice.selectedIndex].value;
		                 	
		                 	window.open('{$SKIN->base_url}&act=rtempl&code=css_search&id='+theID+'&element='+cssChosen,'CSSSEARCH','width=400,height=500,resizable=yes,scrollbars=yes');
		                 }
		                 
		                 function cssPreview(theID)
		                 {
		                 	cssChosen = document.cssForm.csschoice.options[document.cssForm.csschoice.selectedIndex].value;
		                 	
		                 	window.open('{$SKIN->base_url}&act=rtempl&code=css_preview&id='+theID+'&element='+cssChosen,'CSSSEARCH','width=400,height=500,resizable=yes,scrollbars=yes');
		                 }
		                 
		                 //-->
		                 </script>";

        $ADMIN->html .= $SKIN->start_form(
            [
                1 => ['code', 'css_search'],
2 => ['act', 'style'],
3 => ['id', $IN['id']],
            ],
            'cssForm'
        );

        //+-------------------------------

        $SKIN->td_header[] = ['&nbsp;', '20%'];

        $SKIN->td_header[] = ['&nbsp;', '80%'];

        //+-------------------------------

        $ADMIN->html .= $SKIN->start_table('Find CSS Usage');

        $ADMIN->html .= $SKIN->add_td_row(
            [
                'Show me where...',
                $SKIN->form_dropdown('csschoice', $css_elements)
                . ' ... is used within the templates &nbsp;'
                . '<input type="button" value="Go!" onClick="cssSearch(\''
                . $IN['id']
                . '\');" id="editbutton">'
                . '&nbsp;<input type="button" value="Preview CSS Style" onClick="cssPreview(\''
                . $IN['id']
                . '\');" id="editbutton">',
            ]
        );

        $ADMIN->html .= $SKIN->end_form();

        $ADMIN->html .= $SKIN->end_table();

        //+-------------------------------

        //+-------------------------------

        $ADMIN->html .= $SKIN->js_no_specialchars();

        $ADMIN->html .= $SKIN->start_form(
            [
                1 => ['code', $code],
2 => ['act', 'style'],
3 => ['id', $IN['id']],
            ],
            'theAdminForm',
            "onSubmit=\"return no_specialchars('csssheet')\""
        );

        //+-------------------------------

        $SKIN->td_header[] = ['&nbsp;', '20%'];

        $SKIN->td_header[] = ['&nbsp;', '80%'];

        //+-------------------------------

        $ADMIN->html .= $SKIN->start_table($button);

        $ADMIN->html .= $SKIN->add_td_row(
            [
                'Stylesheet Title',
                $SKIN->form_input('name', $cssinfo['css_name']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                "Content<br><br>(<a href='html/sys-img/css.html' target='_blank'>Launch Style Maker</a>)",
                $SKIN->form_textarea('css', $css, '70', '30'),
            ]
        );

        $ADMIN->html .= $SKIN->end_form($button);

        $ADMIN->html .= $SKIN->end_table();

        //+-------------------------------

        //+-------------------------------

        $ADMIN->output();
    }

    //-------------------------------------------------------------

    // SHOW STYLE SHEETS

    //-------------------------------------------------------------

    public function list_sheets()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        $form_array = [];

        $show_array = [];

        $ADMIN->page_detail = 'You may add/edit and remove stylesheets.<br><br>Style Sheets are CSS files. This is where you can change the colours, fonts and font sizes throughout the board.';

        $ADMIN->page_title = 'Manage Stylesheets';

        //+-------------------------------

        $SKIN->td_header[] = ['Title', '40%'];

        $SKIN->td_header[] = ['Allocation', '20%'];

        $SKIN->td_header[] = ['Optimize', '10%'];

        $SKIN->td_header[] = ['Download', '10%'];

        $SKIN->td_header[] = ['Edit', '10%'];

        $SKIN->td_header[] = ['Remove', '10%'];

        //+-------------------------------

        $DB->query('SELECT DISTINCT(c.cssid), c.css_name, s.sname FROM ibf_css c, ibf_skins s WHERE s.css_id=c.cssid ORDER BY c.css_name ASC');

        $used_ids = [];

        if ($DB->get_num_rows()) {
            $ADMIN->html .= $SKIN->start_table('Current Stylesheets In Use');

            while (false !== ($r = $DB->fetch_row())) {
                $show_array[$r['cssid']] .= stripslashes($r['sname']) . '<br>';

                if (in_array($r['cssid'], $used_ids, true)) {
                    continue;
                }

                $ADMIN->html .= $SKIN->add_td_row(
                    [
                        '<b>' . stripslashes($r['css_name']) . '</b>',
                        "<#X-{$r['cssid']}#>",
                        "<center><a href='" . $SKIN->base_url . "&act=style&code=optimize&id={$r['cssid']}'>Optimize</a></center>",
                        "<center><a href='" . $SKIN->base_url . "&act=style&code=export&id={$r['cssid']}'>Download</a></center>",
                        "<center><a href='" . $SKIN->base_url . "&act=style&code=edit&id={$r['cssid']}'>Edit</a></center>",
                        '<i>Deallocate before removing</i>',
                    ]
                );

                $used_ids[] = $r['cssid'];

                $form_array[] = [$r['cssid'], $r['css_name']];
            }

            foreach ($show_array as $idx => $string) {
                $string = preg_replace('/<br>$/', '', $string);

                $ADMIN->html = preg_replace("/<#X-$idx#>/", (string)$string, $ADMIN->html);
            }

            $ADMIN->html .= $SKIN->end_table();
        }

        if (count($used_ids) > 0) {
            $DB->query('SELECT cssid, css_name FROM ibf_css WHERE cssid NOT IN(' . implode(',', $used_ids) . ')');

            if ($DB->get_num_rows()) {
                $SKIN->td_header[] = ['Title', '60%'];

                $SKIN->td_header[] = ['Optimize', '10%'];

                $SKIN->td_header[] = ['Download', '10%'];

                $SKIN->td_header[] = ['Edit', '10%'];

                $SKIN->td_header[] = ['Remove', '10%'];

                $ADMIN->html .= $SKIN->start_table('Current Unallocated Stylesheets');

                $ADMIN->html .= $SKIN->js_checkdelete();

                while (false !== ($r = $DB->fetch_row())) {
                    $ADMIN->html .= $SKIN->add_td_row(
                        [
                            '<b>' . stripslashes($r['css_name']) . '</b>',
                            "<center><a href='" . $SKIN->base_url . "&act=style&code=optimize&id={$r['cssid']}'>Optimize</a></center>",
                            "<center><a href='" . $SKIN->base_url . "&act=style&code=export&id={$r['cssid']}'>Download</a></center>",
                            "<center><a href='" . $SKIN->base_url . "&act=style&code=edit&id={$r['cssid']}'>Edit</a></center>",
                            "<center><a href='javascript:checkdelete(\"act=style&code=remove&id={$r['cssid']}\")'>Remove</a></center>",
                        ]
                    );

                    $form_array[] = [$r['cssid'], $r['css_name']];
                }

                $ADMIN->html .= $SKIN->end_table();
            }
        }

        //+-------------------------------

        //+-------------------------------

        $ADMIN->html .= $SKIN->start_form(
            [
                1 => ['code', 'add'],
2 => ['act', 'style'],
            ]
        );

        $SKIN->td_header[] = ['&nbsp;', '40%'];

        $SKIN->td_header[] = ['&nbsp;', '60%'];

        $ADMIN->html .= $SKIN->start_table('Create New Stylesheet (Copy)');

        //+-------------------------------

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Base new stylesheet on...</b>',
                $SKIN->form_dropdown('id', $form_array),
            ]
        );

        $ADMIN->html .= $SKIN->end_form('Copy to new stylesheet');

        $ADMIN->html .= $SKIN->end_table();

        //+-------------------------------

        //+-------------------------------

        $ADMIN->html .= $SKIN->start_form(
            [
                1 => ['code', 'css_upload'],
2 => ['act', 'style'],
3 => ['MAX_FILE_SIZE', '10000000000'],
            ],
            'uploadform',
            " enctype='multipart/form-data'"
        );

        $SKIN->td_header[] = ['&nbsp;', '40%'];

        $SKIN->td_header[] = ['&nbsp;', '60%'];

        $ADMIN->html .= $SKIN->start_table('Upload new stylesheet');

        //+-------------------------------

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Browse your hard drive</b>',
                $SKIN->form_upload(),
            ]
        );

        $ADMIN->html .= $SKIN->end_form('Upload new stylesheet');

        $ADMIN->html .= $SKIN->end_table();

        $ADMIN->output();
    }
}

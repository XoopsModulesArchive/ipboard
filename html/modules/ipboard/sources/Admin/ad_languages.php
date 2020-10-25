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
|   > Language functions
|   > Module written by Matt Mecham
|   > Date started: 22nd April 2002
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

$idx = new ad_langs();

class ad_langs
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
                $this->add_language();
                break;
            case 'edit':
                $this->do_form('edit');
                break;
            case 'edit2':
                $this->show_file();
                break;
            case 'doadd':
                $this->save_wrapper('add');
                break;
            case 'doedit':
                $this->save_langfile();
                break;
            case 'remove':
                $this->remove();
                break;
            case 'editinfo':
                $this->edit_info();
                break;
            case 'export':
                $this->export();
                break;
            case 'import':
                $this->import();
                break;
            case 'doimport':
                $this->doimport();
                break;
            case 'makedefault':
                $this->make_default();
                break;
            //-------------------------
            default:
                $this->list_current();
                break;
        }
    }

    //--------------------------------------------

    public function make_default()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $_GET;

        $new_dir = stripslashes(urldecode(trim($_GET['id'])));

        if ('' == $new_dir) {
            $ADMIN->error('Could not resolve the new ID for the default lang pack stuff thingy thanks');
        }

        // Update conf file

        $ADMIN->rebuild_config(['default_language' => $new_dir]);

        // Bring it all back to yoooo!

        $std->boink_it($SKIN->base_url . '&act=lang');
    }

    //--------------------------------------------

    public function doimport()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        if ('' == $IN['tarball']) {
            $ADMIN->error('You did not select a tar-chive to import!');
        }

        require ROOT_PATH . 'sources/lib/tar.php';

        $to_dir = $INFO['base_dir'] . 'lang';

        $tarname = $IN['tarball'];

        $from_dir = $INFO['base_dir'] . 'archive_in';

        $tar = new tar();

        //------------------------------

        $real_name = preg_replace("/^lang-(\S+)\.tar$/", '\\1', $IN['tarball']);

        $real_name = str_replace("_", ' ', $real_name);

        //------------------------------

        $tar->new_tar($from_dir, $tarname);

        $files = $tar->list_files();

        if (count($files) > 0) {
            foreach ($files as $giles) {
                if (!preg_match("/^(?:[\.\w\d\+\-\_\/]+)$/", $giles)) {
                    $ADMIN->error("$tarname is possibly corrupted, please re-upload in binary");
                }
            }
        } else {
            $ADMIN->error("$tarname is not a valid tar-chive");
        }

        $DB->query("INSERT INTO ibf_languages (ldir, lname) VALUES('temp', '$real_name (Import)')");

        $new_id = $DB->get_insert_id();

        //-------------------------

        // attempt to make new dir

        //-------------------------

        $dest = $to_dir . '/' . $new_id;

        if (!mkdir($dest, 0777)) {
            $DB->query("DELETE FROM ibf_languages WHERE lid='$new_id'");

            $ADMIN->error("Could not create a new directory in $to_dir, please give sufficient CHMOD permissions to allow this");
        }

        //------------------------------

        // Extract the tarball

        //------------------------------

        $tar->extract_files($dest);

        if ('' != $tar->error) {
            $DB->query("DELETE FROM ibf_languages WHERE lid='$new_id'");

            $ADMIN->error($tar->error);
        }

        $extra = ['lauthor' => '', 'lemail' => ''];

        if (file_exists($dest . '/conf.inc')) {
            require $dest . '/conf.inc';

            $extra['lauthor'] = stripslashes($config['lauthor']);

            $extra['lemail'] = stripslashes($config['lemail']);
        }

        $extra['ldir'] = $new_id;

        $db_string = $DB->compile_db_update_string($extra);

        $DB->query("UPDATE ibf_languages SET $db_string WHERE lid='$new_id'");

        $ADMIN->done_screen('Language Pack Export Imported', 'Manage Language Sets', 'act=lang');
    }

    //--------------------------------------------

    public function import()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        $ADMIN->page_detail = "The tar-chive that you wish to import must reside in 'archive_in' and be a valid tar-chive uploaded in binary format.";

        $ADMIN->page_title = 'Language Pack Import';

        //+-------------------------------

        $ADMIN->html .= $SKIN->start_form(
            [
                1 => ['code', 'doimport'],
2 => ['act', 'lang'],
            ]
        );

        //+-------------------------------

        $SKIN->td_header[] = ['&nbsp;', '50%'];

        $SKIN->td_header[] = ['&nbsp;', '50%'];

        //+-------------------------------

        $ADMIN->html .= $SKIN->start_table('Choose a tar-chive to import');

        $files = $ADMIN->get_tar_names('lang-');

        $form_array = [];

        if (count($files) > 0) {
            foreach ($files as $piles) {
                $form_array[] = [$piles, $piles];
            }
        }

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Tar-chive to import...</b>',
                $SKIN->form_dropdown('tarball', $form_array),
            ]
        );

        $ADMIN->html .= $SKIN->end_form('Import!');

        $ADMIN->html .= $SKIN->end_table();

        $ADMIN->output();
    }

    //--------------------------------------------

    public function export()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        if ('' == $IN['id']) {
            $ADMIN->error('You must specify an existing language set ID, go back and try again');
        }

        //+-------------------------------

        $DB->query("SELECT * from ibf_languages WHERE lid='" . $IN['id'] . "'");

        if (!$row = $DB->fetch_row()) {
            $ADMIN->error('Could not query the information from the database');
        }

        //+-------------------------------

        $archive_dir = $INFO['base_dir'] . '/archive_out';

        $lang_dir = $INFO['base_dir'] . '/lang/' . $row['ldir'];

        require ROOT_PATH . 'sources/lib/tar.php';

        if (!is_dir($archive_dir)) {
            $ADMIN->error("Could not locate $archive_dir, is the directory there?");
        }

        if (!is_writable($archive_dir)) {
            $ADMIN->error("Cannot write in $archive_dir, CHMOD via FTP to 0755 or 0777 to enable this script to write into it. IBF cannot do this for you");
        }

        if (!is_dir($lang_dir)) {
            $ADMIN->error("Could not locate $lang_dir, is the directory there?");
        }

        //+-------------------------------

        // Attempt to copy the files to the

        // working directory...

        //+-------------------------------

        $l_name = preg_replace("/\s{1,}/", '_', $row['lname']);

        $new_dir = 'lang-' . $l_name;

        if (!$ADMIN->copy_dir($lang_dir, $archive_dir . '/' . $new_dir)) {
            $ADMIN->error($ADMIN->errors);
        }

        // Generate the config file..

        $file_content = "<?php\n\n\$config=array('lauthor' => \"" . addslashes($row['lauthor']) . "\", 'lemail'=>\"" . addslashes($row['lemail']) . "\")\n\n?" . '>';

        $FH = fopen($archive_dir . '/' . $new_dir . '/' . 'conf.inc', 'wb');

        fwrite($FH, $file_content, mb_strlen($file_content));

        fclose($FH);

        // Add files and write tarball

        $tar = new tar();

        $tar->new_tar($archive_dir, $new_dir . '.tar');

        $tar->add_directory($archive_dir . '/' . $new_dir);

        $tar->write_tar();

        // Check for errors.

        if ('' != $tar->error) {
            $ADMIN->error($tar->error);
        }

        // remove original unarchived directory

        $ADMIN->rm_dir($archive_dir . '/' . $new_dir);

        $ADMIN->done_screen("Language Pack Export Created<br><br>You can download the tar-chive <a href='archive_out/{$new_dir}.tar'>here</a>", 'Manage Language Sets', 'act=lang');
    }

    public function show_file()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        if ('' == $IN['id']) {
            $ADMIN->error('You must specify an existing language set ID, go back and try again');
        }

        //+-------------------------------

        $DB->query("SELECT * from ibf_languages WHERE lid='" . $IN['id'] . "'");

        if (!$row = $DB->fetch_row()) {
            $ADMIN->error('Could not query the information from the database');
        }

        //+-------------------------------

        $lang_dir = $INFO['base_dir'] . 'lang/' . $row['ldir'];

        $form_array = [];

        $lang_file = $lang_dir . '/' . $IN['lang_file'];

        if (!is_writable($lang_dir)) {
            $ADMIN->error("Cannot write into '$lang_dir', please check the CHMOD value, and if needed, CHMOD to 0777 via FTP. IBF cannot do this for you.");
        }

        if (!file_exists($lang_file)) {
            $ADMIN->error("Cannot locate {$IN['lang_file']} in '$lang_dir', please go back and check the input");
        } else {
            require $lang_file;
        }

        if ('email_content.php' == $IN['lang_file']) {
            $lang = $EMAIL;
        }

        if (!is_writable($lang_file)) {
            $ADMIN->error("Cannot write to '$lang_file', please check the CHMOD value, and if needed, CHMOD to 0777 via FTP. IBF cannot do this for you.");
        }

        $ADMIN->page_detail = 'You may edit any of the language information below.';

        $ADMIN->page_title = 'Edit Language set: ' . $row['lname'];

        //+-------------------------------

        $ADMIN->html .= $SKIN->start_form(
            [
                1 => ['code', 'doedit'],
2 => ['act', 'lang'],
3 => ['id', $IN['id']],
4 => ['lang_file', $IN['lang_file']],
            ]
        );

        //+-------------------------------

        $SKIN->td_header[] = ['Block Name', '20%'];

        $SKIN->td_header[] = ['Content', '80%'];

        //+-------------------------------

        $ADMIN->html .= $SKIN->start_table('Language Text: ' . $IN['lang_file']);

        foreach ($lang as $k => $v) {
            //+-------------------------------

            // Swop < and > into ascii entities

            // to prevent textarea breaking html

            //+-------------------------------

            $v = stripslashes($v);

            $v = preg_replace('/&/', '&#38;', $v);

            $v = preg_replace('/</', '&#60;', $v);

            $v = preg_replace('>/', '&#62;', $v);

            $v = preg_replace("/'/", '&#39;', $v);

            if ('email_content.php' == $IN['lang_file']) {
                $rows = 15;

                $cols = 70;

                $ADMIN->html .= $SKIN->add_td_row(
                    [
                        '&lt;ibf.lang.<b>' . $k . '</b>&gt;',
                        $SKIN->form_textarea('XX_' . $k, $v, $cols, $rows),
                    ]
                );
            } else {
                $ADMIN->html .= $SKIN->add_td_row(
                    [
                        '&lt;ibf.lang.<b>' . $k . '</b>&gt;',
                        $SKIN->form_input('XX_' . $k, $v),
                    ]
                );
            }
        }

        $ADMIN->html .= $SKIN->end_form('Edit this file');

        $ADMIN->html .= $SKIN->end_table();

        //+-------------------------------

        //+-------------------------------

        $ADMIN->output();
    }

    //-------------------------------------------------------------

    // Edit language pack information

    //-------------------------------------------------------------

    public function edit_info()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $_POST;

        if ('' == $IN['id']) {
            $ADMIN->error('You must specify an existing language set ID, go back and try again');
        }

        //+-------------------------------

        $DB->query("SELECT * from ibf_languages WHERE lid='" . $IN['id'] . "'");

        if (!$row = $DB->fetch_row()) {
            $ADMIN->error('Could not query the information from the database');
        }

        $final['lname'] = stripslashes($_POST['lname']);

        if (isset($_POST['lname'])) {
            $final['lauthor'] = stripslashes($_POST['lauthor']);

            $final['lemail'] = stripslashes($_POST['lemail']);
        }

        $db_string = $DB->compile_db_update_string($final);

        $DB->query("UPDATE ibf_languages SET $db_string WHERE lid='" . $IN['id'] . "'");

        $ADMIN->done_screen('Language pack information updated', 'Manage language sets', 'act=lang');
    }

    //-------------------------------------------------------------

    // Add language pack

    //-------------------------------------------------------------

    public function add_language()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $_POST;

        if ('' == $IN['id']) {
            $ADMIN->error('You must specify an existing language set ID, go back and try again');
        }

        $DB->query("SELECT * FROM ibf_languages WHERE lid='" . $IN['id'] . "'");

        //-------------------------------------

        if (!$row = $DB->fetch_row()) {
            $ADMIN->error('Could not query that language set from the DB, so there');
        }

        //-------------------------------------

        //-------------------------------------

        if (!is_writable(ROOT_PATH . 'lang')) {
            $ADMIN->error("The directory 'lang' is not writeable by this script. Please check the permissions on that directory. CHMOD to 0777 if in doubt and try again");
        }

        //-------------------------------------

        if (!is_dir(ROOT_PATH . 'lang/' . $row['ldir'])) {
            $ADMIN->error('Could not locate the original language set to copy, please check and try again');
        }

        //-------------------------------------

        $row['lname'] .= '.2';

        // Insert a new row into the DB...

        $final = [];

        foreach ($row as $k => $v) {
            if ('lid' == $k) {
                continue;
            }

            $final[$k] = $v;
        }

        $db_string = $DB->compile_db_insert_string($final);

        $DB->query('INSERT INTO ibf_languages (' . $db_string['FIELD_NAMES'] . ') VALUES(' . $db_string['FIELD_VALUES'] . ')');

        $new_id = $DB->get_insert_id();

        //-------------------------------------

        if (!$ADMIN->copy_dir($INFO['base_dir'] . 'lang/' . $row['ldir'], $INFO['base_dir'] . 'lang/' . $new_id)) {
            $DB->query("DELETE FROM ibf_languages WHERE lid='$new_id'");

            $ADMIN->error($ADMIN->errors);
        } else {
            $DB->query("UPDATE ibf_languages SET ldir='$new_id' WHERE lid='$new_id'");
        }

        //-------------------------------------

        // Pass to edit / add form...

        //-------------------------------------

        $this->do_form('add', $new_id);
    }

    //-------------------------------------------------------------

    // REMOVE WRAPPERS

    //-------------------------------------------------------------

    public function remove()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $_POST;

        //+-------------------------------

        if ('' == $IN['id']) {
            $ADMIN->error('You must specify an existing image set ID, go back and try again');
        }

        if (1 == $IN['id']) {
            $ADMIN->error('You cannot remove this language pack.');
        }

        $DB->query("SELECT * from ibf_languages WHERE lid='" . $IN['id'] . "'");

        if (!$row = $DB->fetch_row()) {
            $ADMIN->error('Could not query the language information from the database');
        }

        // Is it default??????????????? ok enuff

        if ('' == $INFO['default_language']) {
            $INFO['default_language'] = 'en';
        }

        if ($row['ldir'] == $INFO['default_language']) {
            $ADMIN->error('You cannot remove this language pack while it is the default language directory. Please select another pack to be the default and try again');
        }

        $DB->query("UPDATE xbb_members SET language='{$INFO['default_language']}' WHERE language='{$row['ldir']}'");

        if ($ADMIN->rm_dir($INFO['base_dir'] . 'lang/' . $row['ldir'])) {
            $DB->query("DELETE FROM ibf_languages WHERE lid='" . $IN['id'] . "'");

            $std->boink_it($SKIN->base_url . '&act=lang');

            exit();
        }

        $ADMIN->error('Could not remove the language pack files, please check the CHMOD permissions to ensure that this script has the correct permissions to allow this');
    }

    //-------------------------------------------------------------

    // ADD / EDIT IMAGE SETS

    //-------------------------------------------------------------

    public function save_langfile()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $_POST;

        //+-------------------------------

        if ('' == $IN['id']) {
            $ADMIN->error('You must specify an existing language set ID, go back and try again');
        }

        if ('' == $IN['lang_file']) {
            $ADMIN->error('You must specify an existing language filename, go back and try again');
        }

        $DB->query("SELECT * from ibf_languages WHERE lid='" . $IN['id'] . "'");

        if (!$row = $DB->fetch_row()) {
            $ADMIN->error('Could not query the language information from the database');
        }

        $lang_file = ROOT_PATH . 'lang/' . $row['ldir'] . '/' . $IN['lang_file'];

        if (!file_exists($lang_file)) {
            $ADMIN->error("Could not locate $lang_file, is it there?");
        }

        if (!is_writable($lang_file)) {
            $ADMIN->error("Cannot write to $lang_file, please chmod to 0666 or better and try again");
        }

        $barney = [];

        foreach ($IN as $k => $v) {
            if (preg_match("/^XX_(\S+)$/", $k, $match)) {
                if (isset($IN[$match[0]])) {
                    $v = preg_replace('/&#39;/', "'", stripslashes($_POST[$match[0]]));

                    $v = preg_replace('/&#60;/', '<', $v);

                    $v = preg_replace('/&#62;/', '>', $v);

                    $v = preg_replace('/&#38;/', '&', $v);

                    $v = preg_replace("/\r/", '', $v);

                    $barney[$match[1]] = $v;
                }
            }
        }

        if (count($barney) < 1) {
            $ADMIN->error('Oopsie, something has gone wrong - did you leave all the fields blank?');
        }

        $start = "<?php\n\n";

        if ('email_content.php' == $IN['lang_file']) {
            foreach ($barney as $key => $text) {
                $text = preg_replace('/n{1,}$/', '', $text);

                $start .= "\n" . '$EMAIL[' . "'$key'" . '] = <<<EOF' . "\n" . $text . "\nEOF;\n";
            }
        } else {
            foreach ($barney as $key => $text) {
                $start .= "\n" . '$lang[' . "'$key'" . ']  = "' . addslashes($text) . '";';
            }
        }

        $start .= "\n\n?" . '>';

        if ($fh = fopen($lang_file, 'wb')) {
            fwrite($fh, $start, mb_strlen($start));

            fclose($fh);
        } else {
            $ADMIN->error("Could not write back to $lang_file");
        }

        $ADMIN->done_screen('Set updated', 'Manage Language Sets', 'act=lang');
    }

    //-------------------------------------------------------------

    // EDIT SPLASH

    //-------------------------------------------------------------

    public function do_form($method = 'add', $id = '')
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        //+-------------------------------

        if ('' != $id) {
            $IN['id'] = $id;
        }

        //+-------------------------------

        if ('' == $IN['id']) {
            $ADMIN->error('You must specify an existing language set ID, go back and try again');
        }

        //+-------------------------------

        $DB->query("SELECT * from ibf_languages WHERE lid='" . $IN['id'] . "'");

        if (!$row = $DB->fetch_row()) {
            $ADMIN->error('Could not query the information from the database');
        }

        //+-------------------------------

        $lang_dir = $INFO['base_dir'] . 'lang/' . $row['ldir'];

        $form_array = [];

        if ('add' != $method) {
            if (!is_writable($lang_dir)) {
                $ADMIN->error("Cannot write into '$lang_dir', please check the CHMOD value, and if needed, CHMOD to 0777 via FTP. IBF cannot do this for you.");
            }
        }

        //+-------------------------------

        if (is_dir($lang_dir)) {
            $handle = opendir($lang_dir);

            while (false !== ($filename = readdir($handle))) {
                if (('.' != $filename) && ('..' != $filename)) {
                    if (0 === strpos($filename, "index")) {
                        continue;
                    }

                    if (preg_match("/\.php$/", $filename)) {
                        $form_array[] = [$filename, preg_replace("/\.php$/", '', $filename)];
                    }
                }
            }

            closedir($handle);
        }

        if ($row['lauthor'] and $row['lemail']) {
            $author = "<br><br>This language set <b>'{$row['lname']}'</b> was created by <a href='mailto:{$row['lemail']}' target='_blank'>{$row['lauthor']}</a>";
        } elseif ($row['lauthor']) {
            $author = "<br><br>This language set <b>'{$row['lname']}'</b> was created by {$row['lauthor']}";
        }

        //+-------------------------------

        $ADMIN->page_detail = "Please choose which language section you wish to edit below.$author $url";

        $ADMIN->page_title = 'Edit Language set';

        //+-------------------------------

        //+-------------------------------

        $ADMIN->html .= $SKIN->start_form(
            [
                1 => ['code', 'editinfo'],
2 => ['act', 'lang'],
3 => ['id', $IN['id']],
            ]
        );

        //+-------------------------------

        $SKIN->td_header[] = ['&nbsp;', '60%'];

        $SKIN->td_header[] = ['&nbsp;', '40%'];

        //+-------------------------------

        $ADMIN->html .= $SKIN->start_table('Edit language set information');

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Language Set Name</b>',
                $SKIN->form_input('lname', $row['lname']),
            ]
        );

        if ('add' == $method) {
            $ADMIN->html .= $SKIN->add_td_row(
                [
                    '<b>Language set author name:</b>',
                    $SKIN->form_input('lauthor', $row['lauthor']),
                ]
            );

            $ADMIN->html .= $SKIN->add_td_row(
                [
                    '<b>Language set author email:</b>',
                    $SKIN->form_input('lemail', $row['lemail']),
                ]
            );
        }

        $ADMIN->html .= $SKIN->end_form('Edit language set details');

        $ADMIN->html .= $SKIN->end_table();

        //+-------------------------------

        //+-------------------------------

        $ADMIN->html .= $SKIN->start_form(
            [
                1 => ['code', 'edit2'],
2 => ['act', 'lang'],
3 => ['id', $IN['id']],
            ]
        );

        //+-------------------------------

        $SKIN->td_header[] = ['&nbsp;', '60%'];

        $SKIN->td_header[] = ['&nbsp;', '40%'];

        //+-------------------------------

        $ADMIN->html .= $SKIN->start_table("Edit language files in set '" . $row['lname'] . "'");

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Please select a language file to edit</b>',
                $SKIN->form_dropdown('lang_file', $form_array),
            ]
        );

        $ADMIN->html .= $SKIN->end_form('Edit this language file');

        $ADMIN->html .= $SKIN->end_table();

        //+-------------------------------

        //+-------------------------------

        $ADMIN->output();
    }

    //-------------------------------------------------------------

    // SHOW ALL LANGUAGE PACKS

    //-------------------------------------------------------------

    public function list_current()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        if ('' == $INFO['default_language']) {
            $INFO['default_language'] = 'en';
        }

        $form_array = [];

        $ADMIN->page_detail = 'You can edit, remove and create new language packs from this section';

        $ADMIN->page_title = 'Manage Language Sets';

        //+-------------------------------

        $DB->query("SELECT ibf_languages.*, count(xbb_members.uid) AS mcount FROM ibf_languages LEFT JOIN xbb_members ON(xbb_members.language=ibf_languages.ldir) WHERE (xbb_members.language IS NOT NULL OR xbb_members.language = 'en') GROUP BY ibf_languages.ldir ORDER BY ibf_languages.lname");

        $used_ids = [];

        $show_array = [];

        $ADMIN->html .= $SKIN->js_checkdelete();

        if ($DB->get_num_rows()) {
            $SKIN->td_header[] = ['Title', '40%'];

            $SKIN->td_header[] = ['Members', '30%'];

            $SKIN->td_header[] = ['Export', '10%'];

            $SKIN->td_header[] = ['Edit', '10%'];

            $SKIN->td_header[] = ['Remove', '10%'];

            $ADMIN->html .= $SKIN->start_table('Current Language Packs In Use');

            while (false !== ($r = $DB->fetch_row())) {
                if ($INFO['default_language'] == $r['ldir']) {
                    $root = "<span style='color:red;font-weight:bold'> (Default Language)</span>";
                } else {
                    $root = " ( <a href='{$SKIN->base_url}&act=lang&code=makedefault&id=" . urlencode($r['ldir']) . "'>Make Default Language</a> )";
                }

                $show_array[$r['lid']] .= stripslashes($r['lname']) . '<br>';

                if (in_array($r['lid'], $used_ids, true)) {
                    continue;
                }

                $ADMIN->html .= $SKIN->add_td_row(
                    [
                        '<b>' . stripslashes($r['lname']) . "</b> $root",
                        "<center>{$r['mcount']}</center>",
                        "<center><a href='" . $SKIN->base_url . "&act=lang&code=export&id={$r['lid']}'>Export</a></center>",
                        "<center><a href='" . $SKIN->base_url . "&act=lang&code=edit&id={$r['lid']}'>Edit</a></center>",
                        "<center><a href='javascript:checkdelete(\"act=lang&code=remove&id={$r['lid']}\")'>Remove</a></center>",
                    ]
                );

                $used_ids[] = $r['lid'];

                $form_array[] = [$r['lid'], $r['lname']];
            }

            $ADMIN->html .= $SKIN->end_table();
        }

        if (count($used_ids) < 1) {
            $used_ids[] = '0';
        }

        $DB->query('SELECT lid, ldir, lname FROM ibf_languages WHERE lid NOT IN(' . implode(',', $used_ids) . ')');

        if ($DB->get_num_rows()) {
            $SKIN->td_header[] = ['Title', '40%'];

            $SKIN->td_header[] = ['Export', '10%'];

            $SKIN->td_header[] = ['Edit', '30%'];

            $SKIN->td_header[] = ['Remove', '20%'];

            $ADMIN->html .= $SKIN->start_table('Current Unallocated Language Packs');

            while (false !== ($r = $DB->fetch_row())) {
                if ($INFO['default_language'] == $r['ldir']) {
                    $root = "<span style='color:red;font-weight:bold'> (Default Language)</span>";
                } else {
                    $root = " ( <a href='{$SKIN->base_url}&act=lang&code=makedefault&id=" . urlencode($r['ldir']) . "'>Make Default Language</a> )";
                }

                $ADMIN->html .= $SKIN->add_td_row(
                    [
                        '<b>' . stripslashes($r['lname']) . "</b> $root",
                        "<center><a href='" . $SKIN->base_url . "&act=lang&code=export&id={$r['lid']}'>Export</a></center>",
                        "<center><a href='" . $SKIN->base_url . "&act=lang&code=edit&id={$r['lid']}'>Edit</a></center>",
                        "<center><a href='javascript:checkdelete(\"act=lang&code=remove&id={$r['lid']}\")'>Remove</a></center>",
                    ]
                );

                $form_array[] = [$r['lid'], $r['lname']];
            }

            $ADMIN->html .= $SKIN->end_table();
        }

        //}

        //+-------------------------------

        //+-------------------------------

        $ADMIN->html .= $SKIN->start_form(
            [
                1 => ['code', 'add'],
2 => ['act', 'lang'],
            ]
        );

        $SKIN->td_header[] = ['&nbsp;', '40%'];

        $SKIN->td_header[] = ['&nbsp;', '60%'];

        $ADMIN->html .= $SKIN->start_table('Create Language Set');

        //+-------------------------------

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Base new language set on...</b>',
                $SKIN->form_dropdown('id', $form_array),
            ]
        );

        $ADMIN->html .= $SKIN->end_form('Create new Language set');

        $ADMIN->html .= $SKIN->end_table();

        //+-------------------------------

        //+-------------------------------

        $ADMIN->output();
    }
}

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
|   > Admin functions library
|   > Script written by Matt Mecham
|   > Date started: 1st march 2002
|
+--------------------------------------------------------------------------
*/

class admin_functions
{
    public $img_url;

    public $page_title = 'Welcome to the Invision Power Board Administration CP';

    public $page_detail = 'You can set up and customize your board from within this control panel.<br><br>Clicking on one of the links in the left menu pane will show you the relevant options for that administration category. Each option will contain further information on configuration, etc.';

    public $html;

    public $errors = '';

    public $nav = [];

    public $time_offset = 0;

    public function __construct()
    {
        global $INFO, $IN;

        $this->img_url = $INFO['html_url'] . '/sys-img';

        $this->base_url = $INFO['board_url'] . '/admin.' . $INFO['php_ext'] . '?adsess=' . $IN['AD_SESS'];
    }

    //---------------------------

    // Makes good raw form text

    //----------------------------

    public function make_safe($t)
    {
        $t = stripslashes($t);

        $t = preg_replace("/\\\/", '&#092;', $t);

        return $t;
    }

    //---------------------------

    // Sets up time offset for ACP use

    //----------------------------

    public function get_date($date = '', $method = '')
    {
        global $INFO, $IN, $MEMBER;

        $this->time_options = [
            'JOINED' => $INFO['clock_joined'],
'SHORT' => $INFO['clock_short'],
'LONG' => $INFO['clock_long'],
        ];

        if (!$date) {
            return '--';
        }

        if (empty($method)) {
            $method = 'LONG';
        }

        $this->time_offset = (('' != $MEMBER['timezone_offset']) ? $MEMBER['timezone_offset'] : $INFO['timezone_offset']) * 3600;

        if ('' != $INFO['time_adjust'] and 0 != $INFO['time_adjust']) {
            $this->time_offset += ($INFO['time_adjust'] * 60);
        }

        if ($MEMBER['dst_in_use']) {
            $this->time_offset += 3600;
        }

        return gmdate($this->time_options[$method], ($date + $this->time_offset));
    }

    //**********************************************/

    // save_log

    // Add an entry into the admin logs, yeah.

    //**********************************************/

    public function save_log($action = '')
    {
        global $INFO, $DB, $IN, $MEMBER;

        $str = $DB->compile_db_insert_string(
            [
                'act' => $IN['act'],
'code' => $IN['code'],
'member_id' => $MEMBER['uid'],
'ctime' => time(),
'note' => $action,
'ip_address' => $IN['IP_ADDRESS'],
            ]
        );

        $DB->query("INSERT INTO ibf_admin_logs ({$str['FIELD_NAMES']}) VALUES ({$str['FIELD_VALUES']})");

        return true;  // to anyone that cares..
    }

    //**********************************************/

    // get_tar_names

    // Simply returns a list of tarballs that start

    // with the given filename

    //**********************************************/

    public function get_tar_names($start = 'lang-')
    {
        global $INFO;

        // Remove trailing slashes..

        $files = [];

        $dir = $INFO['base_dir'] . 'archive_in';

        if (is_dir($dir)) {
            $handle = opendir($dir);

            while (false !== ($filename = readdir($handle))) {
                if (('.' != $filename) && ('..' != $filename)) {
                    if (preg_match("/^$start.+?\.tar$/", $filename)) {
                        $files[] = $filename;
                    }
                }
            }

            closedir($handle);
        }

        return $files;
    }

    //**********************************************/

    // copy_dir

    // Copies to contents of a dir to a new dir, creating

    // destination dir if needed.

    //**********************************************/

    public function copy_dir($from_path, $to_path, $mode = 0777)
    {
        global $INFO;

        // Strip off trailing slashes...

        $from_path = preg_replace('#/$#', '', $from_path);

        $to_path = preg_replace('#/$#', '', $to_path);

        if (!is_dir($from_path)) {
            $this->errors = "Could not locate directory '$from_path'";

            return false;
        }

        if (!is_dir($to_path)) {
            if (!@mkdir($to_path, $mode)) {
                $this->errors = "Could not create directory '$to_path' please check the CHMOD permissions and re-try";

                return false;
            }

            @chmod($to_path, $mode);
        }

        $this_path = getcwd();

        if (is_dir($from_path)) {
            chdir($from_path);

            $handle = opendir('.');

            while (false !== ($file = readdir($handle))) {
                if (('.' != $file) && ('..' != $file)) {
                    if (is_dir($file)) {
                        $this->copy_dir($from_path . '/' . $file, $to_path . '/' . $file);

                        chdir($from_path);
                    }

                    if (is_file($file)) {
                        copy($from_path . '/' . $file, $to_path . '/' . $file);

                        @chmod($to_path . '/' . $file, 0777);
                    }
                }
            }

            closedir($handle);
        }

        if ('' == $this->errors) {
            return true;
        }
    }

    //**********************************************/

    // rm_dir

    // Removes directories, if non empty, removes

    // content and directories

    // (Code based on annotations from the php.net

    // manual by pal@degerstrom.com)

    //**********************************************/

    public function rm_dir($file)
    {
        global $INFO;

        $errors = 0;

        // Remove trailing slashes..

        $file = preg_replace('#/$#', '', $file);

        if (file_exists($file)) {
            // Attempt CHMOD

            @chmod($file, 0777);

            if (is_dir($file)) {
                $handle = opendir($file);

                while (false !== ($filename = readdir($handle))) {
                    if (('.' != $filename) && ('..' != $filename)) {
                        $this->rm_dir($file . '/' . $filename);
                    }
                }

                closedir($handle);

                if (!@rmdir($file)) {
                    $errors++;
                }
            } else {
                if (!@unlink($file)) {
                    $errors++;
                }
            }
        }

        if (0 == $errors) {
            return true;
        }

        return false;
    }

    //**********************************************/

    // rebuild_config:

    // Er, rebuilds the config file

    //**********************************************/

    public function rebuild_config($new = '')
    {
        global $IN, $std;

        //-----------------------------------------

        // Check to make sure this is a valid array

        //-----------------------------------------

        if (!is_array($new)) {
            $ADMIN->error('Error whilst attempting to rebuild the board config file, attempt aborted');
        }

        //-----------------------------------------

        // Do we have anything to save out?

        //-----------------------------------------

        if (count($new) < 1) {
            return '';
        }

        //-----------------------------------------

        // Get an up to date copy of the config file

        // (Imports $INFO)

        //-----------------------------------------

        require ROOT_PATH . 'conf_global.php';

        //-----------------------------------------

        // Rebuild the $INFO hash

        //-----------------------------------------

        foreach ($new as $k => $v) {
            // Update the old...

            $v = preg_replace("/'/", "\\'", $v);

            $v = preg_replace("/\r/", '', $v);

            $INFO[$k] = $v;
        }

        //-----------------------------------------

        // Rename the old config file

        //-----------------------------------------

        @rename(ROOT_PATH . 'conf_global.php', ROOT_PATH . 'conf_global-bak.php');

        @chmod(ROOT_PATH . 'conf_global-bak.php', 0777);

        //-----------------------------------------

        // Rebuild the old file

        //-----------------------------------------

        $file_string = "<?php\n";

        foreach ($INFO as $k => $v) {
            if ('skin' == $k or 'languages' == $k) {
                // Protect serailized arrays..

                $v = stripslashes($v);

                $v = addslashes($v);
            }

            $file_string .= '$INFO[' . "'" . $k . "'" . ']' . "\t\t\t=\t'" . $v . "';\n";
        }

        $file_string .= "\n" . '?' . '>';   // Question mark + greater than together break syntax hi-lighting in BBEdit 6 :p

        if ($fh = fopen(ROOT_PATH . 'conf_global.php', 'wb')) {
            fwrite($fh, $file_string, mb_strlen($file_string));

            fclose($fh);
        } else {
            $ADMIN->error('Fatal Error: Could not open conf_global for writing - no changes applied. Try changing the CHMOD to 0777');
        }

        // Pass back the new $INFO array to anyone who cares...

        return $INFO;
    }

    //**********************************************/

    // compile_forum_perms:

    // Returns the READ/REPLY/START DB strings

    //**********************************************/

    public function compile_forum_perms()
    {
        global $DB, $IN;

        $r_array = ['READ' => '', 'REPLY' => '', 'START' => '', 'UPLOAD' => ''];

        if (1 == $IN['READ_ALL']) {
            $r_array['READ'] = '*';
        }

        if (1 == $IN['REPLY_ALL']) {
            $r_array['REPLY'] = '*';
        }

        if (1 == $IN['START_ALL']) {
            $r_array['START'] = '*';
        }

        if (1 == $IN['UPLOAD_ALL']) {
            $r_array['UPLOAD'] = '*';
        }

        $DB->query('SELECT g_id, g_title FROM ibf_groups ORDER BY g_id');

        while (false !== ($data = $DB->fetch_row())) {
            if ('*' != $r_array['READ']) {
                if (1 == $IN['READ_' . $data['g_id']]) {
                    $r_array['READ'] .= $data['g_id'] . ',';
                }
            }

            //+----------------------------

            if ('*' != $r_array['REPLY']) {
                if (1 == $IN['REPLY_' . $data['g_id']]) {
                    $r_array['REPLY'] .= $data['g_id'] . ',';
                }
            }

            //+----------------------------

            if ('*' != $r_array['START']) {
                if (1 == $IN['START_' . $data['g_id']]) {
                    $r_array['START'] .= $data['g_id'] . ',';
                }
            }

            //+----------------------------

            if ('*' != $r_array['UPLOAD']) {
                if (1 == $IN['UPLOAD_' . $data['g_id']]) {
                    $r_array['UPLOAD'] .= $data['g_id'] . ',';
                }
            }
        }

        $r_array['START'] = preg_replace('/,$/', '', $r_array['START']);

        $r_array['REPLY'] = preg_replace('/,$/', '', $r_array['REPLY']);

        $r_array['READ'] = preg_replace('/,$/', '', $r_array['READ']);

        $r_array['UPLOAD'] = preg_replace('/,$/', '', $r_array['UPLOAD']);

        return $r_array;
    }

    //+------------------------------------------------

    //+------------------------------------------------

    // OUTPUT FUNCTIONS

    //+------------------------------------------------

    //+------------------------------------------------

    public function print_popup()
    {
        global $IN, $INFO, $DB, $std, $SKIN, $use_gzip;

        $html = '<html>
		          <head><title>Remote</title>
		          <meta HTTP-EQUIV="Pragma"  CONTENT="no-cache">
				  <meta HTTP-EQUIV="Cache-Control" CONTENT="no-cache">
				  <meta HTTP-EQUIV="Expires" CONTENT="Mon, 06 May 1996 04:57:00 GMT">';

        $html .= $SKIN->get_css();

        $html .= "</head>\n";

        $html .= "</head>
				  <body marginheight='0' marginwidth='0' leftmargin='0' topmargin='0' bgcolor='#E7E7E7'>
				  <table cellspacing='0' cellpadding='2' width='100%' align='center' border='0' bgcolor='#E7E7E7'>
				   <tr>
					<td>
					 <table cellspacing='3' cellpadding='2' width='100%' align='center' height='100%' border='0' bgcolor='#FFFFFF' style='border:thin solid black'>
						<tr>
						 <td valign='top' bgcolor='#FFFFFF'>
						 <table cellspacing='0' cellpadding='2' border='0' align='center' width='100%' height='100%' bgcolor='#FFFFFF'>";

        $html .= $this->html;

        $html .= '</table></td></tr></table></td></tr></table></body></html>';

        print $html;

        exit();
    }

    public function output()
    {
        global $IN, $INFO, $DB, $std, $SKIN, $use_gzip;

        $html = $SKIN->print_top($this->page_title, $this->page_detail);

        $html .= $this->html;

        $html .= $SKIN->print_foot();

        $DB->close_db();

        if (count($this->nav) > 0) {
            $navigation = ["<a href='{$this->base_url}&act=index' target='body'>ACP Home</a>"];

            foreach ($this->nav as $idx => $links) {
                if ('' != $links[0]) {
                    $navigation[] = "<a href='{$this->base_url}&{$links[0]}' target='body'>{$links[1]}</a>";
                } else {
                    $navigation[] = $links[1];
                }
            }

            if (count($navigation) > 0) {
                $html = str_replace('<!--NAV-->', $SKIN->wrap_nav(implode(' -> ', $navigation)), $html);
            }
        }

        if (1 == $use_gzip) {
            $buffer = ob_get_contents();

            ob_end_clean();

            ob_start('ob_gzhandler');

            print $buffer;
        }

        //@header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

        //@header("Cache-Control: no-cache, must-revalidate");

        //@header("Pragma: no-cache");

        print $html;

        exit();
    }

    //**********************************************/

    // Error:

    // Displays an error

    //**********************************************/

    public function error($error = '', $is_popup = 0)
    {
        global $IN, $INFO, $DB, $std, $SKIN, $HTTP_REFERER;

        $this->page_title = 'An Error Occured...';

        $this->page_detail = 'The error message returned is displayed below.';

        $this->html .= "<tr><td><span style='font-size:14px'>$error</span><br><br><center><a href='$HTTP_REFERER'>Go Back</a></center></td></tr>";

        if (0 == $is_popup) {
            $this->output();
        } else {
            $this->print_popup();
        }
    }

    //**********************************************/

    // Done Screen:

    // Displays the "done" screen. Really? Yes.

    //**********************************************/

    public function done_screen($title, $link_text = '', $link_url = '')
    {
        global $IN, $INFO, $DB, $std, $SKIN;

        $this->page_title = $title;

        $this->page_detail = 'The action was executed successfully';

        $SKIN->td_header[] = ['&nbsp;', '100%'];

        $this->html .= $SKIN->start_table('Result');

        $this->html .= $SKIN->add_td_basic("<a href='{$this->base_url}&{$link_url}' target='body'>Go to: $link_text</a>", 'center');

        $this->html .= $SKIN->add_td_basic("<a href='{$this->base_url}&act=index' target='body'>Go to: Administration Home</a>", 'center');

        $this->html .= $SKIN->end_table();

        $this->output();
    }

    public function info_screen($text = '', $title = 'Safe Mode Restriction Warning')
    {
        global $IN, $INFO, $DB, $std, $SKIN;

        $this->page_title = $title;

        $this->page_detail = 'Please note the following:';

        $SKIN->td_header[] = ['&nbsp;', '100%'];

        $this->html .= $SKIN->start_table('Result');

        $this->html .= $SKIN->add_td_basic($text);

        $this->html .= $SKIN->add_td_basic("<a href='{$this->base_url}&act=index' target='body'>Go to: Administration Home</a>", 'center');

        $this->html .= $SKIN->end_table();

        $this->output();
    }

    //**********************************************/

    // MENU:

    // Build the collapsable menu trees

    //**********************************************/

    public function menu()
    {
        global $IN, $std, $PAGES, $CATS, $SKIN;

        $links = $this->build_tree();

        $html = $SKIN->menu_top() . $links . $SKIN->menu_foot();

        print $html;

        exit();
    }

    //+------------------------------------------------

    public function build_tree()
    {
        global $IN, $std, $PAGES, $CATS, $SKIN, $DESC;

        $html = '';

        $links = '';

        foreach ($CATS as $cid => $name) {
            if (preg_match("/(?:^|,)$cid(?:,|$)/", $IN['show'])) {
                foreach ($PAGES[$cid] as $pid => $pdata) {
                    $links .= $SKIN->menu_cat_link($pdata[1], $pdata[0]);
                }

                $html .= $SKIN->menu_cat_expanded($name, $links, $cid);

                unset($links);
            } else {
                $html .= $SKIN->menu_cat_collapsed($name, $cid, $DESC[$cid]);
            }
        }

        return $html;
    }
}

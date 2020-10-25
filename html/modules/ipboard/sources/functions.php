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
|   > Multi function library
|   > Module written by Matt Mecham
|   > Date started: 14th February 2002
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

class FUNC
{
    public $time_formats = [];

    public $time_options = [];

    public $offset = '';

    public $offset_set = 0;

    // Set up some standards to save CPU later

    public function __construct()
    {
        global $INFO;

        $this->time_options = [
            'JOINED' => $INFO['clock_joined'],
'SHORT' => $INFO['clock_short'],
'LONG' => $INFO['clock_long'],
        ];
    }

    /*-------------------------------------------------------------------------*/

    // Load a template file from DB or from PHP file

    /*-------------------------------------------------------------------------*/

    public function load_template($name, $id = '')
    {
        global $ibforums, $DB, $root_path;

        $tags = 1;

        if (0 == $ibforums->vars['safe_mode_skins']) {
            // Simply require and return

            require $root_path . 'Skin/' . $ibforums->skin_id . "/$name.php";

            return new $name();
        }

        // We're using safe mode skins, yippee

        // Load the data from the DB

        $DB->query("SELECT func_name, func_data, section_content FROM ibf_skin_templates WHERE set_id='" . $ibforums->skin_rid . "' AND group_name='$name'");

        if (!$DB->get_num_rows()) {
            fatal_error("Could not fetch the templates from the database. Template $name, ID {$ibforums->skin_rid}");
        } else {
            $new_class = "class $name {\n";

            while (false !== ($row = $DB->fetch_row())) {
                if (1 == $tags) {
                    $comment = "<!--TEMPLATE: $name - Template Part: " . $row['func_name'] . "-->\n";
                }

                $new_class .= 'function ' . $row['func_name'] . '(' . $row['func_data'] . ") {\n";

                $new_class .= "global \$ibforums;\n";

                $new_class .= 'return <<<EOF' . "\n" . $comment . $row['section_content'] . "\nEOF;\n}\n";
            }

            $new_class .= "}\n";

            eval($new_class);

            return new $name();
        }
    }

    /*-------------------------------------------------------------------------*/

    // Creates a profile link if member is a reg. member, else just show name

    /*-------------------------------------------------------------------------*/

    public function make_profile_link($name, $id = '')
    {
        global $ibforums;

        if ($id > 0) {
            return "<a href='{$ibforums->base_url}&act=Profile&MID=$id'>$name</a>";
        }

        return $name;
    }

    /*-------------------------------------------------------------------------*/

    // Redirect using HTTP commands, not a page meta tag.

    /*-------------------------------------------------------------------------*/

    public function boink_it($url)
    {
        global $ibforums;

        if ('refresh' == $ibforums->vars['header_redirect']) {
            @header('Refresh: 0;url=' . $url);
        } elseif ('html' == $ibforums->vars['header_redirect']) {
            @flush();

            echo("<html><head><meta http-equiv='refresh' content='0; url=$url'></head><body></body></html>");

            exit();
        } else {
            @header('Location: ' . $url);
        }

        exit();
    }

    /*-------------------------------------------------------------------------*/

    // Create a random 8 character password

    /*-------------------------------------------------------------------------*/

    public function make_password()
    {
        $pass = '';

        $chars = [
            '1',
            '2',
            '3',
            '4',
            '5',
            '6',
            '7',
            '8',
            '9',
            '0',
            'a',
            'A',
            'b',
            'B',
            'c',
            'C',
            'd',
            'D',
            'e',
            'E',
            'f',
            'F',
            'g',
            'G',
            'h',
            'H',
            'i',
            'I',
            'j',
            'J',
            'k',
            'K',
            'l',
            'L',
            'm',
            'M',
            'n',
            'N',
            'o',
            'O',
            'p',
            'P',
            'q',
            'Q',
            'r',
            'R',
            's',
            'S',
            't',
            'T',
            'u',
            'U',
            'v',
            'V',
            'w',
            'W',
            'x',
            'X',
            'y',
            'Y',
            'z',
            'Z',
        ];

        $count = count($chars) - 1;

        mt_srand((float)microtime() * 1000000);

        for ($i = 0; $i < 8; $i++) {
            $pass .= $chars[random_int(0, $count)];
        }

        return ($pass);
    }

    /*-------------------------------------------------------------------------*/

    // Generate the appropriate folder icon for a forum

    /*-------------------------------------------------------------------------*/

    public function forum_new_posts($forum_data, $sub = 0)
    {
        global $ibforums, $std;

        $rtime = $ibforums->input['last_visit'];

        $fid = '' == $forum_data['fid'] ? $forum_data['id'] : $forum_data['fid'];

        if ($ftime = $std->my_getcookie('fread_' . $fid)) {
            $rtime = $ftime > $rtime ? $ftime : $rtime;
        }

        if (0 == $sub) {
            if (!$forum_data['status']) {
                return '<{C_LOCKED}>';
            }

            $sub_cat_img = '';
        } else {
            $sub_cat_img = '_CAT';
        }

        if ($forum_data['password'] and 0 == $sub) {
            return $forum_data['last_post'] > $rtime ? '<{C_ON_RES}>' : '<{C_OFF_RES}>';
        }

        return $forum_data['last_post'] > $rtime ? '<{C_ON' . $sub_cat_img . '}>' : '<{C_OFF' . $sub_cat_img . '}>';
    }

    /*-------------------------------------------------------------------------*/

    // Generate the appropriate folder icon for a topic

    /*-------------------------------------------------------------------------*/

    public function folder_icon($topic, $dot = '', $last_time = -1)
    {
        global $ibforums;

        $last_time = $last_time > $ibforums->input['last_visit'] ? $last_time : $ibforums->input['last_visit'];

        if ('' != $dot) {
            $dot = '_DOT';
        }

        if ('closed' == $topic['state']) {
            return '<{B_LOCKED}>';
        }

        if ($topic['poll_state']) {
            if (!$ibforums->member['uid']) {
                return '<{B_POLL' . $dot . '}>';
            }

            if ($topic['last_post'] > $topic['last_vote']) {
                $topic['last_vote'] = $topic['last_post'];
            }

            if ($last_time && ($topic['last_vote'] > $last_time)) {
                return '<{B_POLL' . $dot . '}>';
            }

            if ($last_time && ($topic['last_vote'] < $last_time)) {
                return '<{B_POLL_NN' . $dot . '}>';
            }

            return '<{B_POLL}>';
        }

        if ('moved' == $topic['state'] or 'link' == $topic['state']) {
            return '<{B_MOVED}>';
        }

        if (!$ibforums->member['uid']) {
            return '<{B_NORM' . $dot . '}>';
        }

        if (($ibforums->vars['hot_topic'] <= $topic['posts'] + 1) and ((isset($last_time)) && ($topic['last_post'] <= $last_time))) {
            return '<{B_HOT_NN' . $dot . '}>';
        }

        if ($ibforums->vars['hot_topic'] <= $topic['posts'] + 1) {
            return '<{B_HOT' . $dot . '}>';
        }

        if ($last_time && ($topic['last_post'] > $last_time)) {
            return '<{B_NEW' . $dot . '}>';
        }

        return '<{B_NORM' . $dot . '}>';
    }

    /*-------------------------------------------------------------------------*/

    // text_tidy:

    // Takes raw text from the DB and makes it all nice and pretty - which also

    // parses un-HTML'd characters. Use this with caution!

    /*-------------------------------------------------------------------------*/

    public function text_tidy($txt = '')
    {
        $trans = get_html_translation_table(HTML_ENTITIES);

        $trans = array_flip($trans);

        $txt = strtr($txt, $trans);

        $txt = preg_replace("/\s{2}/", '&nbsp; ', $txt);

        $txt = preg_replace("/\r/", "\n", $txt);

        $txt = preg_replace("/\t/", '&nbsp;&nbsp;', $txt);

        //$txt = preg_replace( "/\\n/"   , "&#92;n"       , $txt );

        return $txt;
    }

    /*-------------------------------------------------------------------------*/

    // compile_db_string:

    // Takes an array of keys and values and formats them into a string the DB

    // can use.

    // $array = ( 'THIS' => 'this', 'THAT' => 'that' );

    // will be returned as THIS, THAT  'this', 'that'

    /*-------------------------------------------------------------------------*/

    public function compile_db_string($data)
    {
        $field_names = '';

        $field_values = '';

        foreach ($data as $k => $v) {
            $v = preg_replace("/'/", "\\'", $v);

            $field_names .= "$k,";

            $field_values .= "'$v',";
        }

        $field_names = preg_replace('/,$/', '', $field_names);

        $field_values = preg_replace('/,$/', '', $field_values);

        return [
            'FIELD_NAMES' => $field_names,
'FIELD_VALUES' => $field_values,
        ];
    }

    /*-------------------------------------------------------------------------*/

    // Build up page span links

    /*-------------------------------------------------------------------------*/

    public function build_pagelinks($data)
    {
        global $ibforums;

        $work = [];

        $section = 2;  // Number of pages to show per section( either side of current), IE: 1 ... 4 5 [6] 7 8 ... 10

        $work['pages'] = 1;

        if (($data['TOTAL_POSS'] % $data['PER_PAGE']) == 0) {
            $work['pages'] = $data['TOTAL_POSS'] / $data['PER_PAGE'];
        } else {
            $number = ($data['TOTAL_POSS'] / $data['PER_PAGE']);

            $work['pages'] = ceil($number);
        }

        $work['total_page'] = $work['pages'];

        $work['current_page'] = $data['CUR_ST_VAL'] > 0 ? ($data['CUR_ST_VAL'] / $data['PER_PAGE']) + 1 : 1;

        if ($work['pages'] > 1) {
            $work['first_page'] = "{$data['L_MULTI']} ({$work['pages']})";

            for ($i = 0; $i <= $work['pages'] - 1; ++$i) {
                $RealNo = $i * $data['PER_PAGE'];

                $PageNo = $i + 1;

                if ($RealNo == $data['CUR_ST_VAL']) {
                    $work['page_span'] .= "&nbsp;<b>[{$PageNo}]</b>";
                } else {
                    if ($PageNo < ($work['current_page'] - $section)) {
                        $work['st_dots'] = "&nbsp;<a href='{$data['BASE_URL']}&st=0' title='{$ibforums->lang['ps_page']} 1'>&laquo; {$ibforums->lang['ps_first']}</a>&nbsp;...";

                        continue;
                    }

                    // If the next page is out of our section range, add some dotty dots!

                    if ($PageNo > ($work['current_page'] + $section)) {
                        $work['end_dots'] = "...&nbsp;<a href='{$data['BASE_URL']}&st=" . ($work['pages'] - 1) * $data['PER_PAGE'] . "' title='{$ibforums->lang['ps_page']} {$work['pages']}'>{$ibforums->lang['ps_last']} &raquo;</a>";

                        break;
                    }

                    $work['page_span'] .= "&nbsp;<a href='{$data['BASE_URL']}&st={$RealNo}'>{$PageNo}</a>";
                }
            }

            $work['return'] = $work['first_page'] . $work['st_dots'] . $work['page_span'] . '&nbsp;' . $work['end_dots'];
        } else {
            $work['return'] = $data['L_SINGLE'];
        }

        return $work['return'];
    }

    /*-------------------------------------------------------------------------*/

    // Build the forum jump menu

    /*-------------------------------------------------------------------------*/

    public function build_forum_jump($html = 1, $override = 0)
    {
        global $INFO, $DB, $ibforums;

        // $html = 0 means don't return the select html stuff

        // $html = 1 means return the jump menu with select and option stuff

        $last_cat_id = -1;

        $DB->query(
            'SELECT f.id as forum_id, f.parent_id, f.subwrap, f.sub_can_post, f.name as forum_name, f.position, f.read_perms, c.id as cat_id, c.name
				    FROM ibf_forums f
				     LEFT JOIN ibf_categories c ON (c.id=f.category)
				    ORDER BY c.position, f.position'
        );

        if (1 == $html) {
            $the_html = "<form onSubmit=\"if(document.jumpmenu.f.value == -1){return false;}\" action='{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}&act=SF' method='GET' name='jumpmenu'>"
                        . "<input type='hidden' name='act' value='SF'>\n<input type='hidden' name='s' value='{$ibforums->session_id}'>"
                        . "<select name='f' onChange=\"if(this.options[this.selectedIndex].value != -1){ document.jumpmenu.submit() }\" class='forminput'>"
                        . "<option value='-1'>#Forum Jump#"
                        . "<option value='-1'>------------";
        }

        $forum_keys = [];

        $cat_keys = [];

        $children = [];

        $subs = [];

        // disable short mode if we're compiling a mod form

        if (0 == $html or 1 == $override) {
            $ibforums->vars['short_forum_jump'] = 0;
        }

        while (false !== ($i = $DB->fetch_row())) {
            $selected = '';

            if (1 == $html or 1 == $override) {
                if ($ibforums->input['f'] and $ibforums->input['f'] == $i['forum_id']) {
                    $selected = ' selected';
                }
            }

            if (1 == $i['subwrap'] and 1 != $i['sub_can_post']) {
                $forum_keys[$i['cat_id']][$i['forum_id']] = "<option value=\"{$i['forum_id']}\"" . $selected . ">&nbsp;&nbsp;- {$i['forum_name']}</option>\n";
            } else {
                if ('*' == $i['read_perms']) {
                    if ($i['parent_id'] > 0) {
                        $children[$i['parent_id']][] = "<option value=\"{$i['forum_id']}\"" . $selected . ">&nbsp;&nbsp;---- {$i['forum_name']}</option>\n";
                    } else {
                        $forum_keys[$i['cat_id']][$i['forum_id']] = "<option value=\"{$i['forum_id']}\"" . $selected . ">&nbsp;&nbsp;- {$i['forum_name']}</option><!--fx:{$i['forum_id']}-->\n";
                    }
                } elseif (preg_match('/(^|,)' . $ibforums->member[mgroup] . '(,|$)/', $i['read_perms'])) {
                    if ($i['parent_id'] > 0) {
                        $children[$i['parent_id']][] = "<option value=\"{$i['forum_id']}\"" . $selected . ">&nbsp;&nbsp;---- {$i['forum_name']}</option>\n";
                    } else {
                        $forum_keys[$i['cat_id']][$i['forum_id']] = "<option value=\"{$i['forum_id']}\"" . $selected . ">&nbsp;&nbsp;- {$i['forum_name']}</option><!--fx:{$i['forum_id']}-->\n";
                    }
                } else {
                    continue;
                }
            }

            if ($last_cat_id != $i['cat_id']) {
                // Make sure cats with hidden forums are not shown in forum jump

                $cat_keys[$i['cat_id']] = "<option value='-1'>{$i['name']}</option>\n";

                $last_cat_id = $i['cat_id'];
            }
        }

        foreach ($cat_keys as $cat_id => $cat_text) {
            if (is_array($forum_keys[$cat_id]) && count($forum_keys[$cat_id]) > 0) {
                $the_html .= $cat_text;

                foreach ($forum_keys[$cat_id] as $idx => $forum_text) {
                    $the_html .= $forum_text;

                    if (count($children[$idx]) > 0) {
                        if (1 != $ibforums->vars['short_forum_jump']) {
                            foreach ($children[$idx] as $ii => $tt) {
                                $the_html .= $tt;
                            }
                        } else {
                            $the_html = str_replace("</option><!--fx:$idx-->", ' (+' . count($children[$idx]) . " {$ibforums->lang['fj_subforums']})</option>", $the_html);
                        }
                    }
                }
            }
        }

        if (1 == $html) {
            $the_html .= "</select>&nbsp;<input type='submit' value='{$ibforums->lang['jmp_go']}' class='forminput'></form>";
        }

        return $the_html;
    }

    public function clean_email($email = '')
    {
        $email = preg_replace("#[\n\r\*\'\"<>&\%\!\(\)\{\}\[\]\?\\/]#", '', $email);

        if (preg_match("/^.+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,4}|[0-9]{1,4})(\]?)$/", $email)) {
            return $email;
        }

        return false;
    }

    /*-------------------------------------------------------------------------*/

    // SKIN, sort out the skin stuff

    /*-------------------------------------------------------------------------*/

    public function load_skin()
    {
        global $ibforums, $INFO, $DB;

        $id = -1;

        $skin_set = 0;

        //$ibforums->input['skinid'] = intval($ibforums->input['skinid']);

        //------------------------------------------------

        // Do we have a skin for a particular forum?

        //------------------------------------------------

        if ($ibforums->input['f'] and 'UserCP' != $ibforums->input['act']) {
            if ('' != $ibforums->vars['forum_skin_' . $ibforums->input['f']]) {
                $id = $ibforums->vars['forum_skin_' . $ibforums->input['f']];

                $skin_set = 1;
            }
        }

        //------------------------------------------------

        // Are we allowing user chooseable skins?

        //------------------------------------------------

        $extra = '';

        if (1 != $skin_set and 1 == $ibforums->vars['allow_skins']) {
            if (isset($ibforums->input['skinid'])) {
                $id = (int)$ibforums->input['skinid'];

                $extra = ' AND s.hidden=0';

                $skin_set = 1;
            } elseif ('' != $ibforums->member['skin'] and (int)$ibforums->member['skin'] >= 0) {
                $id = $ibforums->member['skin'];

                if ('Default' == $id) {
                    $id = -1;
                }

                $skin_set = 1;
            }
        }

        //------------------------------------------------

        // Load the info from the database.

        //------------------------------------------------

        if ($id >= 0 and 1 == $skin_set) {
            $DB->query(
                "SELECT s.*, t.template, c.css_text
    					FROM ibf_skins s
    					  LEFT JOIN ibf_templates t ON (t.tmid=s.tmpl_id)
    					  LEFT JOIN ibf_css c ON (c.cssid=s.css_id)
    	           	   WHERE s.sid=$id" . $extra
            );

            // Didn't get a row?

            if (!$DB->get_num_rows()) {
                // Update this members profile

                if ($ibforums->member['uid']) {
                    $DB->query("UPDATE xbb_members SET skin='-1' WHERE uid='" . $ibforums->member['uid'] . "'");
                }

                $DB->query(
                    'SELECT s.*, t.template, c.css_text
    							FROM ibf_skins s
    					  		 LEFT JOIN ibf_templates t ON (t.tmid=s.tmpl_id)
    					 		 LEFT JOIN ibf_css c ON (s.css_id=c.cssid)
    	           	   		    WHERE s.default_set=1'
                );
            }
        } else {
            $DB->query(
                'SELECT s.*, t.template, c.css_text
    					FROM ibf_skins s
    					  LEFT JOIN ibf_templates t ON (t.tmid=s.tmpl_id)
    					  LEFT JOIN ibf_css c ON (s.css_id=c.cssid)
    	           	   WHERE s.default_set=1'
            );
        }

        if (!$row = $DB->fetch_row()) {
            echo('Could not query the skin information!');

            exit();
        }

        return $row;
    }

    /*-------------------------------------------------------------------------*/

    // Require, parse and return an array containing the language stuff

    /*-------------------------------------------------------------------------*/

    public function load_words($current_lang_array, $area, $lang_type)
    {
        require './lang/' . $lang_type . '/' . $area . '.php';

        foreach ($lang as $k => $v) {
            $current_lang_array[$k] = stripslashes($v);
        }

        unset($lang);

        return $current_lang_array;
    }

    /*-------------------------------------------------------------------------*/

    // Return a date or '--' if the date is undef.

    // We use the rather nice gmdate function in PHP to synchronise our times

    // with GMT. This gives us the following choices:

    // If the user has specified a time offset, we use that. If they haven't set

    // a time zone, we use the default board time offset (which should automagically

    // be adjusted to match gmdate.

    /*-------------------------------------------------------------------------*/

    public function get_date($date, $method)
    {
        global $ibforums;

        if (!$date) {
            return '--';
        }

        if (empty($method)) {
            $method = 'LONG';
        }

        if (0 == $this->offset_set) {
            // Save redoing this code for each call, only do once per page load

            $this->offset = (('' != $ibforums->member['timezone_offset']) ? $ibforums->member['timezone_offset'] : $ibforums->vars['time_offset']) * 3600;

            if ('' != $ibforums->vars['time_adjust'] and 0 != $ibforums->vars['time_adjust']) {
                $this->offset += ($ibforums->vars['time_adjust'] * 60);
            }

            if ($ibforums->member['dst_in_use']) {
                $this->offset += 3600;
            }

            $this->offset_set = 1;
        }

        return gmdate($this->time_options[$method], ($date + $this->offset));
    }

    /*-------------------------------------------------------------------------*/

    // Sets a cookie, abstract layer allows us to do some checking, etc

    /*-------------------------------------------------------------------------*/

    public function my_setcookie($name, $value = '', $sticky = 1)
    {
        global $INFO;

        //$expires = "";

        if (1 == $sticky) {
            $expires = time() + 60 * 60 * 24 * 365;
        }

        $INFO['cookie_domain'] = '' == $INFO['cookie_domain'] ? '' : $INFO['cookie_domain'];

        $INFO['cookie_path'] = '' == $INFO['cookie_path'] ? '/' : $INFO['cookie_path'];

        $name = $INFO['cookie_id'] . $name;

        @setcookie($name, $value, $expires, $INFO['cookie_path'], $INFO['cookie_domain']);
    }

    /*-------------------------------------------------------------------------*/

    // Cookies, cookies everywhere and not a byte to eat.

    /*-------------------------------------------------------------------------*/

    public function my_getcookie($name)
    {
        global $INFO, $HTTP_COOKIE_VARS;

        if (isset($HTTP_COOKIE_VARS[$INFO['cookie_id'] . $name])) {
            return urldecode($HTTP_COOKIE_VARS[$INFO['cookie_id'] . $name]);
        }

        return false;
    }

    /*-------------------------------------------------------------------------*/

    // Makes incoming info "safe"

    /*-------------------------------------------------------------------------*/

    public function parse_incoming()
    {
        global $_GET, $_POST, $HTTP_CLIENT_IP, $REQUEST_METHOD, $REMOTE_ADDR, $HTTP_PROXY_USER, $HTTP_X_FORWARDED_FOR;

        $return = [];

        if (is_array($_GET)) {
            while (list($k, $v) = each($_GET)) {
                //$k = $this->clean_key($k);

                if (is_array($_GET[$k])) {
                    while (list($k2, $v2) = each($_GET[$k])) {
                        $return[$k][$this->clean_key($k2)] = $this->clean_value($v2);
                    }
                } else {
                    $return[$k] = $this->clean_value($v);
                }
            }
        }

        // Overwrite GET data with post data

        if (is_array($_POST)) {
            while (list($k, $v) = each($_POST)) {
                //$k = $this->clean_key($k);

                if (is_array($_POST[$k])) {
                    while (list($k2, $v2) = each($_POST[$k])) {
                        $return[$k][$this->clean_key($k2)] = $this->clean_value($v2);
                    }
                } else {
                    $return[$k] = $this->clean_value($v);
                }
            }
        }

        // Sort out the accessing IP

        $return['IP_ADDRESS'] = $this->select_var(
            [
                1 => $_SERVER['REMOTE_ADDR'],
2 => $HTTP_X_FORWARDED_FOR,
3 => $HTTP_PROXY_USER,
4 => $REMOTE_ADDR,
            ]
        );

        // Make sure we take a valid IP address

        $return['IP_ADDRESS'] = preg_replace("/^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})/", '\\1.\\2.\\3.\\4', $return['IP_ADDRESS']);

        $return['request_method'] = ('' != $_SERVER['REQUEST_METHOD']) ? mb_strtolower($_SERVER['REQUEST_METHOD']) : mb_strtolower($REQUEST_METHOD);

        return $return;
    }

    /*-------------------------------------------------------------------------*/

    // Key Cleaner - ensures no funny business with form elements

    /*-------------------------------------------------------------------------*/

    public function clean_key($key)
    {
        if ('' == $key) {
            return '';
        }

        $key = preg_replace("/\.\./", '', $key);

        $key = preg_replace("/\_\_(.+?)\_\_/", '', $key);

        $key = preg_replace("/^([\w\.\-\_]+)$/", '$1', $key);

        return $key;
    }

    public function clean_value($val)
    {
        if ('' == $val) {
            return '';
        }

        $val = str_replace('&#032;', ' ', $val);

        $val = str_replace('&', '&amp;', $val);

        $val = str_replace('<!--', '&#60;&#33;--', $val);

        $val = str_replace('-->', '--&#62;', $val);

        $val = preg_replace('/<script/i', '&#60;script', $val);

        $val = str_replace('>', '&gt;', $val);

        $val = str_replace('<', '&lt;', $val);

        $val = str_replace('"', '&quot;', $val);

        $val = preg_replace("/\|/", '&#124;', $val);

        $val = preg_replace("/\n/", '<br>', $val); // Convert literal newlines

        $val = preg_replace('/\\$/', '&#036;', $val);

        $val = preg_replace("/\r/", '', $val); // Remove literal carriage returns

        $val = str_replace('!', '&#33;', $val);

        $val = str_replace("'", '&#39;', $val); // IMPORTANT: It helps to increase sql query safety.
        $val = stripslashes($val);                                     // Swop PHP added backslashes
        $val = preg_replace("/\\\/", '&#092;', $val); // Swop user inputted backslashes
        return $val;
    }

    public function remove_tags($text = '')
    {
        // Removes < BOARD TAGS > from posted forms

        $text = preg_replace('/(<|&lt;)% (BOARD HEADER|CSS|JAVASCRIPT|TITLE|BOARD|STATS|GENERATOR|COPYRIGHT|NAVIGATION) %(>|&gt;)/i', '&#60;% \\2 %&#62;', $text);

        //$text = str_replace( "<%", "&#60;%", $text );

        return $text;
    }

    public function is_number($number = '')
    {
        if ('' == $number) {
            return -1;
        }

        if (preg_match('/^([0-9]+)$/', $number)) {
            return $number;
        }

        return '';
    }

    /*-------------------------------------------------------------------------*/

    // MEMBER FUNCTIONS

    /*-------------------------------------------------------------------------*/

    public function set_up_guest($name = 'Guest')
    {
        global $INFO;

        return [
            'name' => $name,
'id' => 0,
'password' => '',
'email' => '',
'title' => 'Unregistered',
'mgroup' => $INFO['guest_group'],
'view_sigs' => $INFO['guests_sig'],
'view_img' => $INFO['guests_img'],
'view_avs' => $INFO['guests_ava'],
        ];
    }

    /*-------------------------------------------------------------------------*/

    // GET USER AVATAR

    /*-------------------------------------------------------------------------*/

    public function get_avatar($member_avatar = '', $member_view_avatars = 0, $avatar_dims = 'x')
    {
        global $ibforums;

        if (!$member_avatar or 0 == $member_view_avatars or !$ibforums->vars['avatars_on']) {
            return '';
        }

        if (0 === strpos($member_avatar, "noavatar")) {
            return '';
        }

        if ((preg_match("/\.swf/", $member_avatar)) and (1 != $ibforums->vars['allow_flash'])) {
            return '';
        }

        $davatar_dims = explode('x', $ibforums->vars['avatar_dims']);

        $default_a_dims = explode('x', $ibforums->vars['avatar_def']);

        // Have we enabled URL / Upload avatars?

        $this_dims = explode('x', $avatar_dims);

        if (!$this_dims[0]) {
            $this_dims[0] = $davatar_dims[0];
        }

        if (!$this_dims[1]) {
            $this_dims[1] = $davatar_dims[1];
        }

        if (preg_match("/^http:\/\//", $member_avatar)) {
            // Ok, it's a URL..

            if (preg_match("/\.swf/", $member_avatar)) {
                if ('x' == $avatar_dims) {
                    $member_avatar = rawurlencode($member_avatar);

                    $this_dims = @getimagesize($member_avatar);

                    if ($this_dims[0] > $davatar_dims[0] || $this_dims[1] > $davatar_dims[1]) {
                        if ($this_dims[0] > $this_dims[1]) {
                            $multiplier = $davatar_dims[0] / $this_dims[0];

                            $this_dims[0] = $davatar_dims[0];

                            $this_dims[1] = ceil($this_dims[1] * $multiplier);

                            return "<OBJECT CLASSID=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" WIDTH={$this_dims[0]} HEIGHT={$this_dims[1]}><PARAM NAME=MOVIE VALUE={$member_avatar}><PARAM NAME=PLAY VALUE=TRUE><PARAM NAME=LOOP VALUE=TRUE><PARAM NAME=QUALITY VALUE=HIGH><EMBED SRC={$member_avatar} WIDTH={$this_dims[0]} HEIGHT={$this_dims[1]} PLAY=TRUE LOOP=TRUE QUALITY=HIGH></EMBED></OBJECT>";
                        }

                        if ($this_dims[1] > $this_dims[0]) {
                            $multiplier = $davatar_dims[1] / $this_dims[1];

                            $this_dims[0] = ceil($this_dims[0] * $multiplier);

                            $this_dims[1] = $davatar_dims[1];

                            return "<OBJECT CLASSID=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" WIDTH={$this_dims[0]} HEIGHT={$this_dims[1]}><PARAM NAME=MOVIE VALUE={$member_avatar}><PARAM NAME=PLAY VALUE=TRUE><PARAM NAME=LOOP VALUE=TRUE><PARAM NAME=QUALITY VALUE=HIGH><EMBED SRC={$member_avatar} WIDTH={$this_dims[0]} HEIGHT={$this_dims[1]} PLAY=TRUE LOOP=TRUE QUALITY=HIGH></EMBED></OBJECT>";
                        }

                        return "<OBJECT CLASSID=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" WIDTH={$davatar_dims[0]} HEIGHT={$davatar_dims[1]}><PARAM NAME=MOVIE VALUE={$member_avatar}><PARAM NAME=PLAY VALUE=TRUE><PARAM NAME=LOOP VALUE=TRUE><PARAM NAME=QUALITY VALUE=HIGH><EMBED SRC={$member_avatar} WIDTH={$davatar_dims[0]} HEIGHT={$davatar_dims[1]} PLAY=TRUE LOOP=TRUE QUALITY=HIGH></EMBED></OBJECT>";
                    }

                    if (!$this_dims[0] || '' == $this_dims[0] || !$this_dims[1] || '' == $this_dims[1]) {
                        return "<OBJECT CLASSID=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" WIDTH={$davatar_dims[0]} HEIGHT={$davatar_dims[1]}><PARAM NAME=MOVIE VALUE={$member_avatar}><PARAM NAME=PLAY VALUE=TRUE><PARAM NAME=LOOP VALUE=TRUE><PARAM NAME=QUALITY VALUE=HIGH><EMBED SRC={$member_avatar} WIDTH={$davatar_dims[0]} HEIGHT={$davatar_dims[1]} PLAY=TRUE LOOP=TRUE QUALITY=HIGH></EMBED></OBJECT>";
                    }
                }

                return "<OBJECT CLASSID=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" WIDTH={$this_dims[0]} HEIGHT={$this_dims[1]}><PARAM NAME=MOVIE VALUE={$member_avatar}><PARAM NAME=PLAY VALUE=TRUE><PARAM NAME=LOOP VALUE=TRUE><PARAM NAME=QUALITY VALUE=HIGH><EMBED SRC={$member_avatar} WIDTH={$this_dims[0]} HEIGHT={$this_dims[1]} PLAY=TRUE LOOP=TRUE QUALITY=HIGH></EMBED></OBJECT>";
            }

            if ('x' == $avatar_dims) {
                //					$member_avatar = rawurlencode($member_avatar);

                $this_dims = @getimagesize($member_avatar);

                if ($this_dims[0] > $davatar_dims[0] || $this_dims[1] > $davatar_dims[1]) {
                    if ($this_dims[0] > $this_dims[1]) {
                        $multiplier = $davatar_dims[0] / $this_dims[0];

                        $this_dims[0] = $davatar_dims[0];

                        $this_dims[1] = ceil($this_dims[1] * $multiplier);

                        return "<img src='{$member_avatar}' border='0' width='{$this_dims[0]}' height='{$this_dims[1]}'>";
                    }

                    if ($this_dims[1] > $this_dims[0]) {
                        $multiplier = $davatar_dims[1] / $this_dims[1];

                        $this_dims[0] = ceil($this_dims[0] * $multiplier);

                        $this_dims[1] = $davatar_dims[1];

                        return "<img src='{$member_avatar}' border='0' width='{$this_dims[0]}' height='{$this_dims[1]}'>";
                    }

                    return "<img src='{$member_avatar}' border='0' width='{$davatar_dims[0]}' height='{$davatar_dims[1]}'>";
                }

                if (!$this_dims[0] || '' == $this_dims[0] || !$this_dims[1] || '' == $this_dims[1]) {
                    return "<img src='{$member_avatar}' border='0' width='{$davatar_dims[0]}' height='{$davatar_dims[1]}'>";
                }
            }

            return "<img src='{$member_avatar}' border='0' width='{$this_dims[0]}' height='{$this_dims[1]}'>";
        // Not a URL? Is it an uploaded avatar?
        } elseif (($ibforums->vars['avup_size_max'] > 1) and (0 === strpos($member_avatar, "cavt"))) {
            if ('x' == $avatar_dims) {
                $member_avatar = rawurlencode($member_avatar);

                $this_dims = @getimagesize($ibforums->vars['upload_url'] . '/../../../uploads/' . $member_avatar);

                if ($this_dims[0] > $davatar_dims[0] || $this_dims[1] > $davatar_dims[1]) {
                    if ($this_dims[0] > $this_dims[1]) {
                        $multiplier = $davatar_dims[0] / $this_dims[0];

                        $this_dims[0] = $davatar_dims[0];

                        $this_dims[1] = ceil($this_dims[1] * $multiplier);

                        return "<img src='{$ibforums->vars['upload_url']}/../../../uploads/$member_avatar' border='0' width='{$this_dims[0]}' height='{$this_dims[1]}'>";
                    }

                    if ($this_dims[1] > $this_dims[0]) {
                        $multiplier = $davatar_dims[1] / $this_dims[1];

                        $this_dims[0] = ceil($this_dims[0] * $multiplier);

                        $this_dims[1] = $davatar_dims[1];

                        return "<img src='{$ibforums->vars['upload_url']}/../../../uploads/$member_avatar' border='0' width='{$this_dims[0]}' height='{$this_dims[1]}'>";
                    }

                    return "<img src='{$ibforums->vars['upload_url']}/../../../uploads/$member_avatar' border='0' width='{$davatar_dims[0]}' height='{$davatar_dims[1]}'>";
                }

                if (!$this_dims[0] || '' == $this_dims[0] || !$this_dims[1] || '' == $this_dims[1]) {
                    return "<img src='{$ibforums->vars['upload_url']}/../../../uploads/$member_avatar' border='0' width='{$davatar_dims[0]}' height='{$davatar_dims[1]}'>";
                }
            }

            return "<img src='{$ibforums->vars['upload_url']}/../../../uploads/$member_avatar' border='0' width='{$this_dims[0]}' height='{$this_dims[1]}'>";
        } // No, it's not a URL or an upload, must be a normal avatar then

        elseif ('' != $member_avatar) {
            // Do we have an avatar still ?

            $member_avatar = str_replace('%2F', '/', rawurlencode($member_avatar));

            $default_a_dims = @getimagesize($ibforums->vars['AVATARS_URL'] . '/' . $member_avatar);

            if ($default_a_dims[0] > $davatar_dims[0] || $default_a_dims[1] > $davatar_dims[1]) {
                if ($default_a_dims[0] > $default_a_dims[1]) {
                    $multiplier = $davatar_dims[0] / $default_a_dims[0];

                    $default_a_dims[0] = $davatar_dims[0];

                    $default_a_dims[1] = ceil($default_a_dims[1] * $multiplier);

                    return "<img src='{$ibforums->vars['AVATARS_URL']}/{$member_avatar}' border='0' width='{$default_a_dims[0]}' height='{$default_a_dims[1]}'>";
                }

                if ($default_a_dims[1] > $default_a_dims[0]) {
                    $multiplier = $davatar_dims[1] / $default_a_dims[1];

                    $default_a_dims[0] = ceil($default_a_dims[0] * $multiplier);

                    $default_a_dims[1] = $davatar_dims[1];

                    return "<img src='{$ibforums->vars['AVATARS_URL']}/{$member_avatar}' border='0' width='{$default_a_dims[0]}' height='{$default_a_dims[1]}'>";
                }

                return "<img src='{$ibforums->vars['AVATARS_URL']}/{$member_avatar}' border='0' width='{$davatar_dims[0]}' height='{$davatar_dims[1]}'>";
            }

            if (!$default_a_dims[0] || '' == $default_a_dims[0] || !$default_a_dims[1] || '' == $default_a_dims[1]) {
                return "<img src='{$ibforums->vars['AVATARS_URL']}/{$member_avatar}' border='0' width='{$davatar_dims[0]}' height='{$davatar_dims[1]}'>";
            }

            return "<img src='{$ibforums->vars['AVATARS_URL']}/{$member_avatar}' border='0' width='{$default_a_dims[0]}' height='{$default_a_dims[1]}'>";
        }

        // No, ok - return blank

        return '';
    }

    /*-------------------------------------------------------------------------*/

    // ERROR FUNCTIONS

    /*-------------------------------------------------------------------------*/

    public function Error($error)
    {
        global $DB, $ibforums, $root_path, $skin_universal, $QUERY_STRING, $sid_bb;

        //INIT is passed to the array if we've not yet loaded a skin and stuff

        if (1 == $error['INIT']) {
            $DB->query(
                'SELECT s.*, t.template, c.css_text
    					FROM ibf_skins s
    					  LEFT JOIN ibf_templates t ON (t.tmid=s.tmpl_id)
    					  LEFT JOIN ibf_css c ON (s.css_id=c.cssid)
    	           	   WHERE s.default_set=1'
            );

            $ibforums->skin = $DB->fetch_row();

            $ibforums->session_id = $sid_bb;

            $ibforums->base_url = $ibforums->vars['board_url'] . '/index.' . $ibforums->vars['php_ext'] . '?s=' . $ibforums->session_id;

            $ibforums->skin_rid = $ibforums->skin['set_id'];

            $ibforums->skin_id = 's' . $ibforums->skin['set_id'];

            if ('' == $ibforums->vars['default_language']) {
                $ibforums->vars['default_language'] = 'en';
            }

            $ibforums->lang_id = $ibforums->member['language'] ?: $ibforums->vars['default_language'];

            if (($ibforums->lang_id != $ibforums->vars['default_language']) and (!is_dir($root_path . 'lang/' . $ibforums->lang_id))) {
                $ibforums->lang_id = $ibforums->vars['default_language'];
            }

            $ibforums->vars['img_url'] = 'style_images/' . $ibforums->skin['img_dir'];

            $skin_universal = $this->load_template('skin_global');
        }

        $ibforums->lang = $this->load_words($ibforums->lang, 'lang_error', $ibforums->lang_id);

        [$em_1, $em_2] = explode('@', $ibforums->vars['email_in']);

        $msg = $ibforums->lang[$error['MSG']];

        if ($error['EXTRA']) {
            $msg = preg_replace('/<#EXTRA#>/', $error['EXTRA'], $msg);
        }

        $html = $skin_universal->Error($msg, $em_1, $em_2);

        // If we're a guest, show the log in box..

        if ('' == $ibforums->member['uid'] and 'server_too_busy' != $error['MSG']) {
            $html = preg_replace("/<!-- IBF\.LOG_IN_TABLE -->/e", '$skin_universal->error_log_in($QUERY_STRING)', $html);
        }

        $print = new display();

        $print->add_output($html);

        $print->do_output(
            [
                OVERRIDE => 1,
TITLE => $ibforums->lang['error_title'],
            ]
        );
    }

    public function board_offline()
    {
        global $DB, $ibforums, $root_path, $skin_universal;

        $ibforums->lang = $this->load_words($ibforums->lang, 'lang_error', $ibforums->lang_id);

        $msg = preg_replace("/\n/", '<br>', stripslashes($ibforums->vars['offline_msg']));

        $html = $skin_universal->board_offline($msg);

        $print = new display();

        $print->add_output($html);

        $print->do_output(
            [
                OVERRIDE => 1,
TITLE => $ibforums->lang['offline_title'],
            ]
        );
    }

    /*-------------------------------------------------------------------------*/

    // Variable chooser

    /*-------------------------------------------------------------------------*/

    public function select_var($array)
    {
        if (!is_array($array)) {
            return -1;
        }

        ksort($array);

        $chosen = -1;  // Ensure that we return zero if nothing else is available

        foreach ($array as $k => $v) {
            if (isset($v)) {
                $chosen = $v;

                break;
            }
        }

        return $chosen;
    }
} // end class

//######################################################
// Our "print" class
//######################################################

class display
{
    public $to_print = '';

    //-------------------------------------------

    // Appends the parsed HTML to our class var

    //-------------------------------------------

    public function add_output($to_add)
    {
        $this->to_print .= $to_add;

        //return 'true' on success

        return true;
    }

    //-------------------------------------------

    // Parses all the information and prints it.

    //-------------------------------------------

    public function do_output($output_array)
    {
        global $DB, $Debug, $skin_universal, $ibforums, $xoopsRequestUri, $xoopsModule, $xoopsConfig, $xoopsblock, $xoopsUser, $xoopsLogger, $isbb, $xoopsDB;

        if (1 == $ibforums->input['show_cp_order_number']) {
            // Show the IPS Copyright Removal order number.

            // Note, this is designed to allow IPS validate boards who've purchased copyright removal. The order number

            // is the only thing shown and the order number is unique to the person who paid and is no good to anyone else.

            // Showing the order number poses no risk at all - the information is useless to anyone outside of IPS.

            flush();

            print ('' != $ibforums->vars['ips_cp_purchase']) ? $ibforums->vars['ips_cp_purchase'] : '0';

            exit();
        }

        $TAGS = $DB->query("SELECT macro_value, macro_replace FROM ibf_macro WHERE macro_set='{$ibforums->skin['macro_id']}'");

        $ex_time = sprintf('%.4f', $Debug->endTimer());

        $query_cnt = $DB->get_query_cnt();

        if ($DB->obj['debug']) {
            flush();

            print "<html><head><title>mySQL Debugger</title><body bgcolor='white'><style type='text/css'> TABLE, TD, TR, BODY { font-family: verdana,arial, sans-serif;color:black;font-size:11px }</style>";

            print $ibforums->debug_html;

            print '</body></html>';

            exit();
        }

        $input = '';

        $queries = '';

        $sload = '';

        $gzip_status = 1 == $ibforums->vars['disable_gzip'] ? $ibforums->lang['gzip_off'] : $ibforums->lang['gzip_on'];

        if ($ibforums->server_load > 0) {
            $sload = '&nbsp; [ Server Load: ' . $ibforums->server_load . ' ]';
        }

        //+----------------------------------------------

        if ($ibforums->vars['debug_level'] > 0) {
            $stats = "<br><table width='<{tbl_width}>' cellpadding='4' align='center' cellspacing='0' class='row1'>
					   <tr>
						 <td align='center'>[ Script Execution time: $ex_time ] &nbsp; [ $query_cnt queries used ] &nbsp; [ $gzip_status ] $sload</td>
					   </tr>
					  </table>";
        }

        //+----------------------------------------------

        if ($ibforums->vars['debug_level'] >= 2) {
            $stats .= "<br><table width='<{tbl_width}>' align='center' cellpadding='0' cellspacing='1' bgcolor='<{tbl_border}>'>
       					<tr>
       					 <td>
       					  <table width='100%' align='center' cellpadding='4' cellspacing='1'>
       					<tr>
       					  <td colspan='2' class='titlemedium' align='center'>FORM and GET Input</td>
       					</tr>";

            while (list($k, $v) = each($ibforums->input)) {
                $stats .= "<tr><td width='20%' class='row1'>$k</td><td width='80%' class='row1'>$v</td></tr>";
            }

            $stats .= '</table></td></tr></table>';
        }

        //+----------------------------------------------

        if ($ibforums->vars['debug_level'] >= 3) {
            $stats .= "<br><table width='<{tbl_width}>' align='center' cellpadding='0' cellspacing='1' bgcolor='<{tbl_border}>'>
       					<tr>
       					 <td>
       					  <table width='100%' align='center' cellpadding='4' cellspacing='1'>
       					<tr>
       					  <td colspan='2' class='titlemedium' align='center'>Queries Used</td>
       					</tr>";

            foreach ($DB->obj['cached_queries'] as $q) {
                $q = preg_replace('/^SELECT/i', "<font style='color:red;font-weight:bold'>SELECT</font>", $q);

                $q = preg_replace('/^UPDATE/i', "<font style='color:blue;font-weight:bold'>UPDATE</font>", $q);

                $q = preg_replace('/^DELETE/i', "<font style='color:orange;font-weight:bold'>DELETE</font>", $q);

                $q = preg_replace('/^INSERT/i', "<font style='color:green;font-weight:bold'>INSERT</font>", $q);

                $q = str_replace('LEFT JOIN', "<font style='color:red;font-weight:bold'>LEFT JOIN</font>", $q);

                $q = preg_replace('/(' . $ibforums->vars['sql_tbl_prefix'] . ")(\S+?)([\s\.,]|$)/", "<font style='color:purple;font-weight:bold'>\\1\\2</font>\\3", $q);

                $stats .= "<tr><td class='row1'>$q</td></tr>";
            }

            $stats .= '</table></td></tr></table>';
        }

        /********************************************************/

        // NAVIGATION

        $nav = $skin_universal->start_nav();

        $nav .= "<a href='{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}'>{$ibforums->vars['board_name']}</a>";

        if (empty($output_array['OVERRIDE'])) {
            if (is_array($output_array['NAV'])) {
                foreach ($output_array['NAV'] as $n) {
                    if ($n) {
                        $nav .= '<{F_NAV_SEP}>' . $n;
                    }
                }
            }
        }

        $nav .= $skin_universal->end_nav();

        /********************************************************/

        // CSS

        $css = "\n<style type='text/css'>\n" . $ibforums->skin['css_text'] . "\n</style>";

        // Yes, I realise that this is silly and easy to remove the copyright, but

        // as it's not concealed source, there's no point having a 1337 fancy hashing

        // algorithm if all you have to do is delete a few lines, so..

        // However, be warned: If you remove the copyright and you have not purchased

        // copyright removal, you WILL be spotted and your licence to use Invision Power Board

        // will be terminated, requiring you to remove your board immediately.

        // So, have a nice day.

        $copyright = "<!-- Copyright Information -->\n\n<p><table width='80%' align='center' cellpadding='3' cellspacing='0'><tr><td align='center' valign='middle' class='copyright'>Integrated by <a href='http://koudanshi.net'>Koudanshi</a> v1.1.3 &copy; 2003$b_copy<br>Powered by <a href=\"http://www.invisionboard.com\" target='_blank'>Invision Power Board</a> {$ibforums->version} &copy; 2003 &nbsp;<a href='http://www.invisionpower.com' target='_blank'>IPS, Inc.</a></td></tr></table><p>";

        if ($ibforums->vars['ips_cp_purchase']) {
            $copyright = '';
        }

        // Awww, cmon, don't be mean! Literally thousands of hours have gone into

        // coding Invision Power Board and all we ask in return is one measly little line

        // at the bottom. That's fair isn't it?

        // No? Hmmm...

        // Have you seen how much it costs to remove the copyright from UBB? o_O

        /********************************************************/

        // Build the board header

        $this_header = $skin_universal->BoardHeader();

        // Build the members bar

        if (0 == $ibforums->member['uid']) {
            $output_array['MEMBER_BAR'] = $skin_universal->Guest_bar();
        } else {
            $pm_js = '';

            if (($ibforums->member['g_max_messages'] > 0) and ($ibforums->member['msg_total'] >= $ibforums->member['g_max_messages'])) {
                $msg_data['TEXT'] = $ibforums->lang['msg_full'];
            } else {
                $ibforums->member['new_msg'] = '' == $ibforums->member['new_msg'] ? 0 : $ibforums->member['new_msg'];

                $msg_data['TEXT'] = sprintf($ibforums->lang['msg_new'], $ibforums->member['new_msg']);
            }

            // Do we have a pop up to show?

            if ($ibforums->member['show_popup']) {
                $DB->query("UPDATE xbb_members SET show_popup='0' WHERE uid='{$ibforums->member['uid']}'");

                $pm_js = $skin_universal->PM_popup();
            }

            if (($ibforums->member['is_mod']) or (1 == $ibforums->member['g_is_supmod'])) {
                $mod_link = $skin_universal->mod_link();
            }

            $admin_link = $ibforums->member['g_access_cp'] ? $skin_universal->admin_link() : '';

            if (!$ibforums->member['g_use_pm']) {
                $output_array['MEMBER_BAR'] = $skin_universal->Member_no_usepm_bar($admin_link, $mod_link);
            } else {
                $output_array['MEMBER_BAR'] = $pm_js . $skin_universal->Member_bar($msg_data, $admin_link, $mod_link);
            }
        }

        if (1 == $ibforums->vars['board_offline']) {
            $output_array['TITLE'] = $ibforums->lang['warn_offline'] . ' ' . $output_array['TITLE'];
        }

        // Get the template

        $ibforums->skin['template'] = str_replace('<% CSS %>', $css, $ibforums->skin['template']);

        $ibforums->skin['template'] = str_replace('<% JAVASCRIPT %>', '', $ibforums->skin['template']);

        $ibforums->skin['template'] = str_replace('<% TITLE %>', $output_array['TITLE'], $ibforums->skin['template']);

        $ibforums->skin['template'] = str_replace('<% BOARD %>', $this->to_print, $ibforums->skin['template']);

        $ibforums->skin['template'] = str_replace('<% STATS %>', $stats, $ibforums->skin['template']);

        $ibforums->skin['template'] = str_replace('<% GENERATOR %>', '', $ibforums->skin['template']);

        $ibforums->skin['template'] = str_replace('<% COPYRIGHT %>', $copyright, $ibforums->skin['template']);

        $ibforums->skin['template'] = str_replace('<% BOARD HEADER %>', $this_header, $ibforums->skin['template']);

        $ibforums->skin['template'] = str_replace('<% NAVIGATION %>', $nav, $ibforums->skin['template']);

        if (empty($output_array['OVERRIDE'])) {
            $ibforums->skin['template'] = str_replace('<% MEMBER BAR %>', $output_array['MEMBER_BAR'], $ibforums->skin['template']);
        } else {
            $ibforums->skin['template'] = str_replace('<% MEMBER BAR %>', '<br>', $ibforums->skin['template']);
        }

        //+--------------------------------------------

        // Stick in banner?

        //+--------------------------------------------

        if ($ibforums->vars['ipshosting_credit']) {
            $ibforums->skin['template'] = str_replace('<!--IBF.BANNER-->', $skin_universal->ibf_banner(), $ibforums->skin['template']);
        }

        //+--------------------------------------------

        // Stick in chat link?

        //+--------------------------------------------

        if ($ibforums->vars['chat_account_no']) {
            $ibforums->vars['chat_height'] += 25;

            $ibforums->vars['chat_width'] += 25;

            $chat_link = ('self' == $ibforums->vars['chat_display']) ? $skin_universal->show_chat_link_inline() : $skin_universal->show_chat_link_popup();

            $ibforums->skin['template'] = str_replace('<!--IBF.CHATLINK-->', $chat_link, $ibforums->skin['template']);
        }

        //+--------------------------------------------

        //| Get the macros and replace them

        //+--------------------------------------------

        while (false !== ($row = $DB->fetch_row($TAGS))) {
            if ('' != $row['macro_value']) {
                $ibforums->skin['template'] = str_replace('<{' . $row['macro_value'] . '}>', $row['macro_replace'], $ibforums->skin['template']);
            }
        }

        $ibforums->skin['template'] = str_replace('<#IMG_DIR#>', $ibforums->skin['img_dir'], $ibforums->skin['template']);

        // Close this DB connection

        $DB->close_db();

        // Start GZIP compression

        if (1 != $ibforums->vars['disable_gzip']) {
            $buffer = ob_get_contents();

            ob_end_clean();

            ob_start('ob_gzhandler');

            print $buffer;
        }

        $this->do_headers();

        // XOOPS HEADER

        require './../../header.php';

        print $ibforums->skin['template'];

        // XOOPS footer

        require './../../footer.php';

        exit;
    }

    //-------------------------------------------

    // print the headers

    //-------------------------------------------

    public function do_headers()
    {
        global $ibforums;

        if ($ibforums->vars['print_headers']) {
            @header('HTTP/1.0 200 OK');

            @header('HTTP/1.1 200 OK');

            @header('Content-type: text/html');

            if ($ibforums->vars['nocache']) {
                @header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

                @header('Cache-Control: no-cache, must-revalidate');

                @header('Pragma: no-cache');
            }
        }
    }

    //-------------------------------------------

    // print a pure redirect screen

    //-------------------------------------------

    public function redirect_screen($text = '', $url = '')
    {
        global $ibforums, $skin_universal, $DB;

        if ($ibforums->input['debug']) {
            flush();

            exit();
        }

        $url = $start . "?s={$ibforums->session_id}&" . $url;

        $ibforums->lang['stand_by'] = stripslashes($ibforums->lang['stand_by']);

        $css = "\n<style>\n<!--\n" . str_replace('<#IMG_DIR#>', $ibforums->skin['img_dir'], $ibforums->skin['css_text']) . "\n//-->\n</style>";

        $htm = $skin_universal->Redirect($text, $url, $css);

        $TAGS = $DB->query("SELECT macro_value, macro_replace FROM ibf_macro WHERE macro_set='{$ibforums->skin['macro_id']}'");

        while (false !== ($row = $DB->fetch_row($TAGS))) {
            if ('' != $row['macro_value']) {
                $htm = str_replace('<{' . $row['macro_value'] . '}>', $row['macro_replace'], $htm);
            }
        }

        $htm = str_replace('<#IMG_DIR#>', $ibforums->skin['img_dir'], $htm);

        // Close this DB connection

        $DB->close_db();

        // Start GZIP compression

        if (1 != $ibforums->vars['disable_gzip']) {
            $buffer = ob_get_contents();

            ob_end_clean();

            ob_start('ob_gzhandler');

            print $buffer;
        }

        $this->do_headers();

        echo($htm);

        exit;
    }

    //-------------------------------------------

    // print a minimalist screen suitable for small

    // pop up windows

    //-------------------------------------------

    public function pop_up_window($title = 'Invision Power Board', $text = '')
    {
        global $ibforums, $DB;

        $css = "\n<style>\n<!--\n" . str_replace('<#IMG_DIR#>', $ibforums->skin['img_dir'], $ibforums->skin['css_text']) . "\n//-->\n</style>";

        $html = "<html>
    	           <head>
    	              <title>$title</title>
    	              $css
    	           </head>
    	           <body topmargin='0' leftmargin='0' rightmargin='0' marginwidth='0' marginheight='0' alink='#000000' vlink='#000000'>
    	           $text
    	           </body>
    	         </html>
    	        ";

        $TAGS = $DB->query("SELECT macro_value, macro_replace FROM ibf_macro WHERE macro_set='{$ibforums->skin['macro_id']}'");

        while (false !== ($row = $DB->fetch_row($TAGS))) {
            if ('' != $row['macro_value']) {
                $html = str_replace('<{' . $row['macro_value'] . '}>', $row['macro_replace'], $html);
            }
        }

        $html = str_replace('<#IMG_DIR#>', $ibforums->skin['img_dir'], $html);

        $DB->close_db();

        if (1 != $ibforums->vars['disable_gzip']) {
            $buffer = ob_get_contents();

            ob_end_clean();

            ob_start('ob_gzhandler');

            print $buffer;
        }

        $this->do_headers();

        echo($html);

        exit;
    }
} // END class

//######################################################
// Our "session" class
//######################################################

class session
{
    public $ip_address = 0;

    public $user_agent = '';

    public $time_now = 0;

    public $session_id = 0;

    public $session_dead_id = 0;

    public $session_user_id = 0;

    public $session_user_pass = '';

    public $last_click = 0;

    public $location = '';

    public $member = [];

    // No need for a constructor

    public function authorise()
    {
        global $DB, $INFO, $ibforums, $std, $HTTP_USER_AGENT, $sid_bb, $uid_bb;

        //-------------------------------------------------

        // Before we go any lets check the load settings..

        //-------------------------------------------------

        if ($ibforums->vars['load_limit'] > 0) {
            if (file_exists('/proc/loadavg')) {
                if ($fh = @fopen('/proc/loadavg', 'rb')) {
                    $data = @fread($fh, 6);

                    @fclose($fh);

                    $load_avg = explode(' ', $data);

                    $ibforums->server_load = trim($load_avg[0]);

                    if ($ibforums->server_load > $ibforums->vars['load_limit']) {
                        $std->Error(['LEVEL' => 1, 'MSG' => 'server_too_busy', 'INIT' => 1]);
                    }
                }
            }
        }

        //--------------------------------------------

        // Are they banned?

        //--------------------------------------------

        if ($ibforums->vars['ban_ip']) {
            $ips = explode('|', $ibforums->vars['ban_ip']);

            foreach ($ips as $ip) {
                $ip = str_replace("*", '.*', $ip);

                if (preg_match("/$ip/", $ibforums->input['IP_ADDRESS'])) {
                    $std->Error(['LEVEL' => 1, 'MSG' => 'you_are_banned', 'INIT' => 1]);
                }
            }
        }

        //--------------------------------------------

        $this->member = ['uid' => 0, 'pass' => '', 'uname' => '', 'mgroup' => $INFO['guest_group']];

        //-------------------------------------------------

        // If we are accessing the registration functions,

        // lets not confuse things.

        //-------------------------------------------------

        // We don't want to check if we're registering and we don't want to start

        // any new headers if we're simply viewing an attachment..

        if ('Reg' == $ibforums->input['act'] or 'Attach' == $ibforums->input['act']) {
            return $this->member;
        }

        $this->ip_address = $ibforums->input['IP_ADDRESS'];

        $this->user_agent = mb_substr($HTTP_USER_AGENT, 0, 50);

        $this->time_now = time();

        $cookie = [];

        $cookie['session_id'] = $std->my_getcookie('session_id');

        $cookie['member_id'] = $std->my_getcookie('member_id');

        $cookie['pass_hash'] = $std->my_getcookie('pass_hash');

        if (!empty($sid_bb)) {
            $this->get_session($sid_bb);
        } elseif (!empty($ibforums->input['s'])) {
            $this->get_session($ibforums->input['s']);
        }

        //-------------------------------------------------

        // Finalise the incoming data..

        //-------------------------------------------------

        $ibforums->input['Privacy'] = $std->select_var(
            [
                1 => $ibforums->input['Privacy'],
2 => $std->my_getcookie('anonlogin'),
            ]
        );

        //-------------------------------------------------

        // Do we have a valid session ID?

        //-------------------------------------------------

        if (($this->session_id && !empty($this->session_id)) or ($sid_bb && !empty($sid_bb))) {
            // We've checked the IP addy and browser, so we can assume that this is

            // a valid session.

            if ((0 != $uid_bb && !empty($uid_bb)) or (0 != $this->session_user_id && !empty($this->session_user_id))) {
                // It's a member session, so load the member.

                $this->load_member($uid_bb);

                // Did we get a member?

                if ((!$this->member['uid']) or (0 == $this->member['uid'])) {
                    $this->unload_member();

                    $this->update_guest_session();
                } else {
                    $this->update_member_session();
                }
            } else {
                $this->update_guest_session();
            }
        }

        //-------------------------------------------------

        // Set up a guest if we get here and we don't have a member ID

        //-------------------------------------------------

        if (!$this->member['uid']) {
            $this->member = $std->set_up_guest();

            $DB->query("SELECT * from ibf_groups WHERE g_id='" . $INFO['guest_group'] . "'");

            $group = $DB->fetch_row();

            foreach ($group as $k => $v) {
                $this->member[$k] = $v;
            }
        }

        //------------------------------------------------

        // Synchronise the last visit and activity times if

        // we have some in the member profile

        //-------------------------------------------------

        if ($this->member['uid']) {
            if (!$ibforums->input['last_activity']) {
                if ($this->member['last_activity']) {
                    $ibforums->input['last_activity'] = $this->member['last_activity'];
                } else {
                    $ibforums->input['last_activity'] = $this->time_now;
                }
            }

            //------------

            if (!$ibforums->input['last_visit']) {
                if ($this->member['last_visit']) {
                    $ibforums->input['last_visit'] = $this->member['last_visit'];
                } else {
                    $ibforums->input['last_visit'] = $this->time_now;
                }
            }

            //-------------------------------------------------

            // If there hasn't been a cookie update in 2 hours,

            // we assume that they've gone and come back

            //-------------------------------------------------

            if (!$this->member['last_visit']) {
                // No last visit set, do so now!

                $DB->query("UPDATE xbb_members SET last_visit='" . $this->time_now . "', last_activity='" . $this->time_now . "' WHERE uid='" . $this->member['uid'] . "'");
            } elseif ((time() - $ibforums->input['last_activity']) > 300) {
                // If the last click was longer than 5 mins ago and this is a member

                // Update their profile.

                $DB->query("UPDATE xbb_members SET last_activity='" . $this->time_now . "' WHERE uid='" . $this->member['uid'] . "'");
            }
        }

        //-------------------------------------------------

        // Set a session ID cookie

        //-------------------------------------------------

        // convert skin [id] --> [uid]

        $this->member['name'] = $this->member['uname'];

        $this->member['id'] = $this->member['uid'];

        return $this->member;
    }

    //+-------------------------------------------------

    // Attempt to load a member

    //+-------------------------------------------------

    public function load_member($member_id = 0)
    {
        global $DB, $std, $ibforums;

        if (0 != $member_id) {
            $DB->query(
                "SELECT mod.mid as is_mod, m.uid, m.uname, m.mgroup, m.pass, m.email, m.allow_post, m.view_sigs, m.view_avs, m.view_pop, m.view_img, m.auto_track,
                              m.mod_posts, m.language, m.skin, m.new_msg, m.show_popup, m.msg_total, m.time_offset, m.posts, m.joined, m.last_post,
            				  m.last_visit, m.last_activity, m.dst_in_use, m.view_prefs, g.*
            				  FROM xbb_members m
            				    LEFT JOIN ibf_groups g ON (g.g_id=m.mgroup)
            				    LEFT JOIN ibf_moderators mod ON (mod.member_id=m.uid OR mod.group_id=m.mgroup )
            				  WHERE m.uid='$member_id'"
            );

            if ($DB->get_num_rows()) {
                $this->member = $DB->fetch_row();
            }

            //-------------------------------------------------

            // Unless they have a member id, log 'em in as a guest

            //-------------------------------------------------

            if ((0 == $this->member['uid']) or (empty($this->member['uid']))) {
                $this->unload_member();
            }

            if (time() - $this->member['last_activity'] > 300) {
                // Fix up the last visit/activity times.

                $ibforums->input['last_visit'] = $this->member['last_activity'];

                $ibforums->input['last_activity'] = $this->time_now;
            }
        }

        unset($member_id);
    }

    //+-------------------------------------------------

    // Remove the users cookies

    //+-------------------------------------------------

    public function unload_member()
    {
        global $DB, $std, $ibforums;

        // Boink the cookies

        $this->member['uid'] = 0;

        $this->member['uname'] = '';

        $this->member['pass'] = '';
    }

    //-------------------------------------------

    // Updates a current session.

    //-------------------------------------------

    public function update_member_session()
    {
        global $DB, $ibforums;

        $query = 'UPDATE ibf_sessions SET ' . "member_name='" . $this->member['uname'] . "', " . "member_id='" . $this->member['uid'] . "', " . "member_group='" . $this->member['mgroup'] . "', ";

        // Append the rest of the query

        $query .= "login_type='"
                  . $ibforums->input['Privacy']
                  . "', sess_updated='"
                  . $this->time_now
                  . "', in_forum='"
                  . $ibforums->input['f']
                  . "', in_topic='"
                  . $ibforums->input['t']
                  . "', location='"
                  . $ibforums->input['act']
                  . ','
                  . $ibforums->input['p']
                  . ','
                  . $ibforums->input['CODE']
                  . "' ";

        $query .= "WHERE sess_id='" . $this->session_id . "'";

        // Update the database

        $DB->query($query);
    }

    //--------------------------------------------------------------------

    public function update_guest_session()
    {
        global $DB, $ibforums, $INFO;

        $query = "UPDATE ibf_sessions SET member_name='',member_id='0',member_group='" . $INFO['guest_group'] . "'";

        $query .= ",login_type='0', sess_updated='" . $this->time_now . "', in_forum='" . $ibforums->input['f'] . "', in_topic='" . $ibforums->input['t'] . "', location='" . $ibforums->input['act'] . ',' . $ibforums->input['p'] . ',' . $ibforums->input['CODE'] . "' ";

        $query .= "WHERE sess_id='" . $this->session_id . "'";

        // Update the database

        $DB->query($query);
    }

    //-------------------------------------------

    // Get a session based on the current session ID

    //-------------------------------------------

    public function get_session($session_id = '')
    {
        global $DB, $INFO, $std, $HTTP_SERVER_VARS, $uid_bb, $sid_bb;

        $result = [];

        $query = '';

        $session_id = preg_replace('/([^a-zA-Z0-9])/', '', $session_id);

        if (!empty($session_id)) {
            if (1 == $INFO['match_browser']) {
                $query = " AND browser='" . $this->user_agent . "'";
            }

            $DB->query("SELECT sess_id, member_id, sess_updated, location FROM ibf_sessions WHERE sess_id='" . $session_id . "' and member_id='" . $uid_bb . "'" . $query);

            if (1 != $DB->get_num_rows()) {
                // Either there is no session, or we have more than one session..

                $this->session_dead_id = $session_id;

                $this->session_id = $sid_bb;

                $this->session_user_id = 0;

                return;
            }

            $result = $DB->fetch_row();

            if ('' == $result['sess_id']) {
                $this->session_dead_id = $session_id;

                $this->session_id = 0;

                $this->session_user_id = 0;

                unset($result);

                return;
            }

            $this->session_id = $result['sess_id'];

            $this->session_user_id = $result['member_id'];

            $this->last_click = $result['sess_updated'];

            $this->location = $result['location'];

            unset($result);

            return;
        }
    }
}
?>

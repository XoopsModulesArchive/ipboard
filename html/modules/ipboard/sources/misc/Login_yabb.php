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
|   > Log in / log out module
|   > Module written by Matt Mecham
|   > Date started: 14th February 2002
|
|	> Module Version Number: 1.0.0 (YABB EDITED VERSION)
+--------------------------------------------------------------------------
*/

$idx = new Login();

class Login
{
    public $output = '';

    public $page_title = '';

    public $nav = [];

    public $login_html = '';

    public function __construct()
    {
        global $ibforums, $DB, $std, $print;

        $ibforums->lang = $std->load_words($ibforums->lang, 'lang_login', $ibforums->lang_id);

        $this->login_html = $std->load_template('skin_login');

        // Are we enforcing log ins?

        if (1 == $ibforums->vars['force_login']) {
            $msg = 'admin_force_log_in';
        } else {
            $msg = '';
        }

        // What to do?

        switch ($ibforums->input['CODE']) {
            case '01':
                $this->do_log_in();
                break;
            case '02':
                $this->log_in_form();
                break;
            case '03':
                $this->do_log_out();
                break;
            case '04':
                $this->markforum();
                break;
            case '05':
                $this->markboard();
                break;
            case '06':
                $this->delete_cookies();
                break;
            case 'autologin':
                $this->auto_login();
                break;
            default:
                $this->log_in_form($msg);
                break;
        }

        // If we have any HTML to print, do so...

        $print->add_output((string)$this->output);

        $print->do_output(['TITLE' => $this->page_title, 'JS' => 0, NAV => $this->nav]);
    }

    public function auto_login()
    {
        global $ibforums, $DB, $std, $print, $sess;

        // Universal routine.

        // If we have cookies / session created, simply return to the index screen

        // If not, return to the log in form

        $ibforums->member = $sess->authorise();

        // If there isn't a member ID set, do a quick check ourselves.

        // It's not that important to do the full session check as it'll

        // occur when they next click a link.

        if (!$ibforums->member['uid']) {
            $mid = $std->my_getcookie('member_id');

            $pid = $std->my_getcookie('pass_hash');

            if ($mid and $pid) {
                $DB->query("SELECT * FROM xbb_members WHERE uid=$mid AND pass='$pid'");

                if ($member = $DB->fetch_row()) {
                    $ibforums->member = $member;

                    $ibforums->session_id = '';

                    $std->my_setcookie('session_id', '0', -1);
                }
            }
        }

        $true_words = $ibforums->lang['logged_in'];

        $false_words = $ibforums->lang['not_logged_in'];

        $method = 'no_show';

        if (1 == $ibforums->input['fromreg']) {
            $true_words = $ibforums->lang['reg_log_in'];

            $false_words = $ibforums->lang['reg_not_log_in'];

            $method = 'show';
        }

        if ($ibforums->member['uid']) {
            if ('show' == $method) {
                $print->redirect_screen($true_words, '');
            } else {
                $std->boink_it($ibforums->vars['board_url'] . '/index.' . $ibforums->vars['php_ext']);
            }
        } else {
            if ('show' == $method) {
                $print->redirect_screen($false_words, 'act=Login&CODE=00');
            } else {
                $std->boink_it($ibforums->base_url . '&act=Login&CODE=00');
            }
        }
    }

    public function delete_cookies()
    {
        global $ibforums, $DB, $std, $HTTP_COOKIE_VARS;

        if (is_array($HTTP_COOKIE_VARS)) {
            foreach ($HTTP_COOKIE_VARS as $cookie => $value) {
                if (preg_match('/^(' . $ibforums->vars['cookie_id'] . 'fread.*$)/', $cookie, $match)) {
                    $std->my_setcookie(str_replace($ibforums->vars['cookie_id'], '', $match[0]), '-1', -1);
                }

                if (preg_match('/^(' . $ibforums->vars['cookie_id'] . 'ibforum.*$)/i', $cookie, $match)) {
                    $std->my_setcookie(str_replace($ibforums->vars['cookie_id'], '', $match[0]), '-', -1);
                }
            }
        }

        $std->my_setcookie('pass_hash', '-1');

        $std->my_setcookie('member_id', '-1');

        $std->my_setcookie('session_id', '-1');

        $std->my_setcookie('topicsread', '-1');

        $std->my_setcookie('anonlogin', '-1');

        $std->boink_it($ibforums->base_url);

        exit();
    }

    public function markboard()
    {
        global $ibforums, $DB, $std;

        if (!$ibforums->member['uid']) {
            $std->Error([LEVEL => 1, MSG => 'no_guests']);
        }

        $DB->query("UPDATE xbb_members SET last_visit='" . time() . "', last_activity='" . time() . "' WHERE uid='" . $ibforums->member['uid'] . "'");

        $std->boink_it($ibforums->base_url);

        exit();
    }

    public function markforum()
    {
        global $ibforums, $DB, $std;

        $ibforums->input['f'] = (int)$ibforums->input['f'];

        if ('' == $ibforums->input['f']) {
            $std->Error([LEVEL => 1, MSG => 'missing_files']);
        }

        $DB->query('SELECT id, name, subwrap, parent_id FROM ibf_forums WHERE id=' . $ibforums->input['f']);

        if (!$f = $DB->fetch_row()) {
            $std->Error([LEVEL => 1, MSG => 'missing_files']);
        }

        $std->my_setcookie('fread_' . $ibforums->input['f'], time());

        // Are we getting kicked back to the root forum (if sub forum) or index?

        if ($f['parent_id'] > 0) {
            // Its a sub forum, lets go redirect to parent forum

            $std->boink_it($ibforums->base_url . '&act=SF&f=' . $f['parent_id']);
        } else {
            $std->boink_it($ibforums->base_url);
        }

        exit();
    }

    public function log_in_form($message = '')
    {
        global $ibforums, $DB, $std, $print, $HTTP_REFERER;

        //+--------------------------------------------

        //| Are they banned?

        //+--------------------------------------------

        if ($ibforums->vars['ban_ip']) {
            $ips = explode('|', $ibforums->vars['ban_ip']);

            foreach ($ips as $ip) {
                $ip = str_replace("*", '.*', $ip);

                if (preg_match("/$ip/", $ibforums->input['IP_ADDRESS'])) {
                    $std->Error([LEVEL => 1, MSG => 'you_are_banned']);
                }
            }
        }

        //+--------------------------------------------

        if ('' != $message) {
            $message = $ibforums->lang[$message];

            $message = preg_replace('/<#NAME#>/', "<b>{$ibforums->input[UserName]}</b>", $message);

            $this->output .= $this->login_html->errors($message);
        }

        $this->output .= $this->login_html->ShowForm($ibforums->lang['please_log_in'], $HTTP_REFERER);

        $this->nav = [$ibforums->lang['log_in']];

        $this->page_title = $ibforums->lang['log_in'];

        $print->add_output((string)$this->output);

        $print->do_output(['TITLE' => $this->page_title, 'JS' => 0, NAV => $this->nav]);

        exit();
    }

    //+--------------------------------------------

    public function do_log_in()
    {
        global $DB, $ibforums, $std, $print, $sess, $HTTP_USER_AGENT, $_POST;

        $url = '';

        //-------------------------------------------------

        // Make sure the username and password were entered

        //-------------------------------------------------

        if ('' == $_POST['UserName']) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_username']);
        }

        if ('' == $_POST['PassWord']) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'pass_blank']);
        }

        //-------------------------------------------------

        // Check for input length

        //-------------------------------------------------

        if (mb_strlen($ibforums->input['UserName']) > 32) {
            $std->Error([LEVEL => 1, MSG => 'username_long']);
        }

        if (mb_strlen($ibforums->input['PassWord']) > 32) {
            $std->Error([LEVEL => 1, MSG => 'pass_too_long']);
        }

        $username = mb_strtolower($ibforums->input['UserName']);

        $password = md5($ibforums->input['PassWord']);

        //-------------------------------------------------

        // Check to see if this a YaBB user (uncoverted)

        //-------------------------------------------------

        $DB->query("SELECT uid, uname, pass, misc FROM xbb_members WHERE LOWER(uname)='$username'");

        $yabb_member = $DB->fetch_row();

        // Is it unconverted?

        if ('no' == $yabb_member['misc']) {
            // Unconverted YaBB SE member..

            $in_pass = $ibforums->input['PassWord'];

            if (crypt($in_pass, mb_substr($in_pass, 0, 2)) == $yabb_member['pass']) {
                $DB->query("UPDATE xbb_members SET pass='$password ', misc='' WHERE uid={$yabb_member['uid']}");
            } else {
                $this->log_in_form('wrong_pass');
            }
        }

        //-------------------------------------------------

        // Attempt to get the user details

        //-------------------------------------------------

        $DB->query("SELECT uid, uname, mgroup, pass, new_pass FROM xbb_members WHERE LOWER(uname)='$username'");

        if ($DB->get_num_rows()) {
            $member = $DB->fetch_row();

            if (empty($member['uid']) or ('' == $member['uid'])) {
                $this->log_in_form('wrong_name');
            }

            if ($member['pass'] != $password) {
                $this->log_in_form('wrong_pass');
            }

            //------------------------------

            if ($ibforums->input['CookieDate']) {
                $std->my_setcookie('member_id', $member['uid'], 1);

                $std->my_setcookie('pass_hash', $password, 1);
            }

            //------------------------------

            if ($ibforums->input['s']) {
                $session_id = $ibforums->input['s'];

                // Delete any old sessions with this users IP addy that doesn't match our

                // session ID.

                $DB->query("DELETE FROM ibf_sessions WHERE ip_address='" . $ibforums->input['IP_ADDRESS'] . "' AND id <> '$session_id'");

                $db_string = $DB->compile_db_update_string(
                    [
                        'member_name' => $member['uname'],
'member_id' => $member['uid'],
'running_time' => time(),
'member_group' => $member['mgroup'],
'login_type' => $ibforums->input['Privacy'] ? 1 : 0,
                    ]
                );

                $db_query = "UPDATE ibf_sessions SET $db_string WHERE id='" . $ibforums->input['s'] . "'";
            } else {
                $session_id = md5(uniqid(microtime()));

                // Delete any old sessions with this users IP addy.

                $DB->query("DELETE FROM ibf_sessions WHERE ip_address='" . $ibforums->input['IP_ADDRESS'] . "'");

                $db_string = $DB->compile_db_insert_string(
                    [
                        'id' => $session_id,
'member_name' => $member['uname'],
'member_id' => $member['uid'],
'running_time' => time(),
'member_group' => $member['mgroup'],
'ip_address' => mb_substr($ibforums->input['IP_ADDRESS'], 0, 50),
'browser' => mb_substr($HTTP_USER_AGENT, 0, 50),
'login_type' => $ibforums->input['Privacy'] ? 1 : 0,
                    ]
                );

                $db_query = 'INSERT INTO ibf_sessions (' . $db_string['FIELD_NAMES'] . ') VALUES (' . $db_string['FIELD_VALUES'] . ')';
            }

            $DB->query($db_query);

            //-----------------------------------

            // If a bogus reset passy action occured,

            // and we managed to log in, we'll assume

            // that the user did nothing, so we remove

            // this new pass setting.

            //-----------------------------------

            if ('' != $member['new_pass']) {
                $DB->query("UPDATE xbb_members SET new_pass='' WHERE uid='" . $member['uid'] . "'");
            }

            $ibforums->member = $member;

            $ibforums->session_id = $session_id;

            if ($ibforums->input['referer'] && ('Reg' != $ibforums->input['act'])) {
                $url = $ibforums->input['referer'];

                $url = str_replace("{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}", '', $url);

                $url = preg_replace("!^\?!", '', $url);

                $url = preg_replace("!s=(\w){32}!", '', $url);

                $url = preg_replace('!act=(login|reg|lostpass)!i', '', $url);
            }

            //-----------------------------------

            // set our privacy cookie

            //-----------------------------------

            if (1 == $ibforums->input['Privacy']) {
                $std->my_setcookie('anonlogin', 1);
            }

            //-----------------------------------

            // Redirect them to either the board

            // index, or where they came from

            //-----------------------------------

            $print->redirect_screen("{$ibforums->lang[thanks_for_login]} {$ibforums->member['uname']}", $url);
        } else {
            $this->log_in_form('wrong_name');
        }
    }

    public function do_log_out()
    {
        global $std, $ibforums, $DB, $print, $sess, $HTTP_COOKIE_VARS;

        /*if(! $ibforums->member['id'])
        {
            $std->Error( array( LEVEL => 1, MSG => 'no_guests') );
        }*/

        // Update the DB

        $DB->query(
            'UPDATE ibf_sessions SET ' . "member_name=''," . "member_id='0'," . "login_type='0' " . "WHERE id='" . $sess->session_id . "'"
        );

        $DB->query("UPDATE xbb_members SET last_visit='" . time() . "', last_activity='" . time() . "' WHERE uid='" . $ibforums->member['uid'] . "'");

        // Set some cookies

        $std->my_setcookie('member_id', '0');

        $std->my_setcookie('pass_hash', '0');

        $std->my_setcookie('anonlogin', '-1');

        if (is_array($HTTP_COOKIE_VARS)) {
            foreach ($HTTP_COOKIE_VARS as $cookie => $value) {
                if (preg_match('/^(' . $ibforums->vars['cookie_id'] . 'fread.*$)/', $cookie, $match)) {
                    $std->my_setcookie(str_replace($ibforums->vars['cookie_id'], '', $match[0]), '-1', -1);
                }

                if (preg_match('/^(' . $ibforums->vars['cookie_id'] . 'ibforum.*$)/i', $cookie, $match)) {
                    $std->my_setcookie(str_replace($ibforums->vars['cookie_id'], '', $match[0]), '-', -1);
                }
            }
        }

        // Redirect...

        $print->redirect_screen($ibforums->lang['thanks_for_logout'], '');
    }
}

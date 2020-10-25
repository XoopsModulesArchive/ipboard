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
|   > UserCP functions
|   > Module written by Matt Mecham
|   > Date started: 14th February 2002
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

$idx = new UserCP();

class UserCP
{
    public $output     = '';
    public $page_title = '';
    public $nav        = [];
    public $html       = '';
    public $parser;
    public $member     = [];
    public $m_group    = [];
    public $jump_html  = '';
    public $parser     = '';
    public $links      = [];
    public $bio        = '';
    public $notes      = '';
    public $size       = 'm';
    public $email      = '';
    public $lib;

    public function __construct()
    {
        global $ibforums, $DB, $std, $print;

        require './sources/lib/post_parser.php';

        $this->parser = new post_parser();

        //--------------------------------------------
        // Get the emailer module
        //--------------------------------------------

        require './sources/lib/emailer.php';

        $this->email = new emailer();

        if ($ibforums->input['CODE'] == '') {
            $ibforums->input['CODE'] = 00;
        }

        //--------------------------------------------
        // Require the HTML and language modules
        //--------------------------------------------

        $ibforums->lang = $std->load_words($ibforums->lang, 'lang_post', $ibforums->lang_id);
        $ibforums->lang = $std->load_words($ibforums->lang, 'lang_ucp', $ibforums->lang_id);

        require './sources/lib/usercp_functions.php';

        $this->html = $std->load_template('skin_ucp');

        $this->base_url        = "{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}";
        $this->base_url_nosess = "{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}";

        //--------------------------------------------
        // Check viewing permissions, etc
        //--------------------------------------------

        $this->member = $ibforums->member;

        if (empty($this->member['uid'])) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_guests']);
        }

        // Get more member info..

        $DB->query(
            "SELECT m.*, me.links, me.notes, me.ta_size 
    			   FROM xbb_members m
    			   LEFT JOIN ibf_member_extra me ON(me.id=m.uid)
    			   WHERE m.uid='" . $this->member['uid'] . "'"
        );

        $this->member = $DB->fetch_row();

        $this->links = $this->member['links'];
        $this->notes = $this->member['notes'];
        $this->size  = $this->member['ta_size'] ?: $this->size;

        // We have a single pull down menu for our "Quick Click" stuff
        // The JS uses a simple split command to determine if the link
        // is onsite, or offsite. If it's onsite, it'll have a preceeding
        // element of 0, if it's onsite it'll have an element of 1. If it's
        // offsite, we open a new window via JS.
        // Yes, I feel quite clever. :P

        $links = '';

        if ($this->links) {
            $link_array = unserialize(stripslashes($this->links));

            reset($link_array);

            $links = "<option value='-1'>----------------</option>\n<option value='-1'>{$ibforums->lang['qc_your_links']}</option>\n";
            $links .= "<option value='-1'>----------------</option>\n";

            foreach ($link_array as $k => $v) {
                $url  = $v[0];
                $desc = $v[1];

                if ($url != '' and $desc != '') {
                    if (strlen($desc) > 45) {
                        $desc = substr($desc, 0, 42) . '...';
                    }

                    $links .= "<option value='1|$url'>$desc</option>\n";
                }
            }
        }

        //--------------------------------------------
        // Print the top button menu
        //--------------------------------------------

        $menu_html = $this->html->Menu_bar($this->base_url);

        // If no messenger, remove the links!

        if (!$ibforums->member['g_use_pm']) {
            $menu_html = preg_replace('/<!-- Messenger -->.+?<!-- End Messenger -->/s', '', $menu_html);
        }

        $print->add_output($menu_html);

        $this->lib = new usercp_functions($this);

        //--------------------------------------------
        // What to do?
        //--------------------------------------------

        switch ($ibforums->input['CODE']) {
            case '00':
                $this->splash();
                break;
            case '01':
                $this->personal();
                break;
            //------------------------------
            case '02':
                $this->email_settings();
                break;
            case '03':
                $this->do_email_settings();
                break;
            //------------------------------
            case '04':
                $this->board_prefs();
                break;
            case '05':
                $this->do_board_prefs();
                break;
            //------------------------------
            case '06':
                $this->skin_langs();
                break;
            case '07':
                $this->do_skin_langs();
                break;
            //------------------------------
            case '08':
                $this->email_change();
                break;
            case '09':
                $this->do_email_change();
                break;
            //------------------------------
            case '21':
                $this->do_personal();
                break;
            case '20':
                $this->update_notepad();
                break;
            //------------------------------
            case '22':
                $this->signature();
                break;
            case '23':
                $this->do_signature();
                break;
            //------------------------------
            case '24':
                $this->avatar();
                break;
            case '25':
                $this->do_avatar();
                break;
            //------------------------------
            case '26':
                $this->tracker();
                break;
            case '27':
                $this->do_delete_tracker();
                break;
            //------------------------------
            case '28':
                $this->pass_change();
                break;
            case '29':
                $this->do_pass_change();
                break;
            //------------------------------

            case '50':
                $this->forum_tracker();
                break;
            case '51':
                $this->remove_forum_tracker();
                break;

            //-------------------------------

            case 'show_image':
                $this->show_image();
                break;

            default:
                $this->splash();
                break;
        }

        // If we have any HTML to print, do so...

        $fj = $std->build_forum_jump();
        $fj = preg_replace('!#Forum Jump#!', $ibforums->lang['forum_jump'], $fj);

        $this->output .= $this->html->CP_end();

        $this->output .= $this->html->forum_jump($fj, $links);

        $print->add_output((string)$this->output);
        $print->do_output(['TITLE' => $this->page_title, 'JS' => 1, NAV => $this->nav]);
    }


    //*******************************************************************/
    //| Forum tracker
    //|
    //| What, you need a definition with that title?
    //| What are you doing poking around in the code for anyway?
    //*******************************************************************/

    public function remove_forum_tracker()
    {
        global $ibforums, $std, $DB;

        if ($ibforums->input['f'] == 'all') {
            $DB->query("DELETE FROM ibf_forum_tracker WHERE member_id='" . $this->member['uid'] . "'");
        } else {
            $id = (int)$ibforums->input['f'];

            $DB->query("DELETE FROM ibf_forum_tracker WHERE member_id='" . $this->member['uid'] . "' AND forum_id='$id'");
        }

        $std->boink_it($this->base_url . '&act=UserCP&CODE=50');
    }

    public function forum_tracker()
    {
        global $ibforums, $DB, $std, $print;

        $this->output .= $this->html->forum_subs_header();

        //----------------------------------------------------------
        // Query the DB for the subby toppy-ics - at the same time
        // we get the forum and topic info, 'cos we rule.
        //----------------------------------------------------------

        $DB->query(
            "SELECT t.frid, t.start_date, f.*, c.id as cat_id, c.name as cat_name
 		            FROM ibf_forum_tracker t
 		             LEFT JOIN ibf_forums f ON (t.forum_id=f.id)
 		             LEFT JOIN ibf_categories c ON (c.id=f.category)
 		            WHERE t.member_id='" . $this->member['uid'] . "'
 		            ORDER BY c.position, f.position"
        );

        if ($DB->get_num_rows()) {
            $last_cat_id = -1;

            while (false !== ($forum = $DB->fetch_row())) {
                if ($last_cat_id != $forum['cat_id']) {
                    $last_cat_id = $forum['cat_id'];

                    $this->output .= $this->html->subs_forum_row($forum['cat_id'], $forum['cat_name']);
                }

                $forum['last_post'] = $std->get_date($forum['last_post'], 'LONG');

                $forum['last_topic'] = $ibforums->lang['f_none'];

                $forum['last_title'] = str_replace('&#33;', '!', $forum['last_title']);
                $forum['last_title'] = str_replace('&quot;', '"', $forum['last_title']);

                if (strlen($forum['last_title']) > 30) {
                    $forum['last_title'] = substr($forum['last_title'], 0, 27) . '...';
                    $forum['last_title'] = preg_replace('/&(#(\d+;?)?)?\.\.\.$/', '...', $forum['last_title']);
                }

                if ($forum['password'] != '') {
                    $forum['last_topic'] = $ibforums->lang['f_none'];
                } else {
                    $forum['last_topic'] = "<a href='{$ibforums->base_url}&act=ST&f={$forum['id']}&t={$forum['last_id']}&view=getlastpost'>{$forum['last_title']}</a>";
                }

                if (isset($forum['last_poster_name'])) {
                    $forum['last_poster'] = $forum['last_poster_id'] ? "<a href='{$ibforums->base_url}&act=Profile&CODE=03&MID={$forum['last_poster_id']}'>{$forum['last_poster_name']}</a>" : $forum['last_poster_name'];
                } else {
                    $forum['last_poster'] = $ibforums->lang['f_none'];
                }

                $forum['folder_icon'] = $std->forum_new_posts($forum);

                $this->output .= $this->html->forum_subs_row($forum);
            }
        } else {
            $this->output .= $this->html->forum_subs_none();
        }

        $this->output .= $this->html->forum_subs_end();

        $this->page_title = $ibforums->lang['t_welcome'];
        $this->nav        = ["<a href='" . $this->base_url . "&act=UserCP&CODE=00'>" . $ibforums->lang['t_title'] . '</a>'];
    }


    //*******************************************************************/
    //| pass change:
    //|
    //| Change the users password.
    //*******************************************************************/

    public function pass_change()
    {
        global $ibforums, $DB, $std;

        $this->output     .= $this->html->pass_change();
        $this->page_title = $ibforums->lang['t_welcome'];
        $this->nav        = ["<a href='" . $this->base_url . "&act=UserCP&CODE=00'>" . $ibforums->lang['t_title'] . '</a>'];
    }

    public function do_pass_change()
    {
        global $ibforums, $DB, $std, $_POST, $print;

        if ($_POST['current_pass'] == '' or empty($_POST['current_pass'])) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'complete_form']);
        }

        //--------------------------------------------

        $cur_pass = trim($ibforums->input['current_pass']);
        $new_pass = trim($ibforums->input['new_pass_1']);
        $chk_pass = trim($ibforums->input['new_pass_2']);

        //--------------------------------------------

        if ((empty($new_pass)) or (empty($chk_pass))) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'complete_form']);
        }

        //--------------------------------------------

        if ($new_pass != $chk_pass) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'pass_no_match']);
        }

        //--------------------------------------------

        if (md5($cur_pass) != $this->member['pass']) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'wrong_pass']);
        }

        //--------------------------------------------

        $md5_pass = md5($new_pass);

        //--------------------------------------------
        // Update the DB

        //--------------------------------------------
        $DB->query("UPDATE xbb_members SET pass='$md5_pass' WHERE uid='" . $this->member['uid'] . "'");

        //$DB->query("UPDATE ibf_sessions SET member_pass='$md5_pass' WHERE id='".$this->session_id."' and member_id='".$this->member['id']."'");

        //--------------------------------------------
        // Update the cookie..
        //--------------------------------------------

        $std->my_setcookie('pass_hash', $md5_pass, 1);

        //--------------------------------------------
        // Redirect...
        //--------------------------------------------

        $print->redirect_screen($ibforums->lang['pass_redirect'], 'act=UserCP&CODE=00');
    }


    //*******************************************************************/
    //| email change:
    //|
    //| Change the users email address
    //*******************************************************************/

    public function email_change($msg = '')
    {
        global $ibforums, $DB, $std;

        $txt = $ibforums->lang['ce_current'] . $this->member['email'];

        if ($ibforums->vars['reg_auth_type']) {
            $txt .= $ibforums->lang['ce_auth'];
        }

        //-----------------------------------------------
        // Set up security code
        //-----------------------------------------------

        // Get a time roughly 6 hours ago...

        $r_date = time() - (60 * 60 * 6);

        // Remove old reg requests from the DB

        $DB->query("DELETE FROM ibf_reg_antispam WHERE ctime < '$r_date'");

        // Set a new ID for this reg request...

        $regid = md5(uniqid(microtime()));

        // Set a new 6 character numerical string

        // mt_srand((double)microtime() * 1000000);

        $reg_code = mt_rand(100000, 999999);

        // Insert into the DB

        $str = $DB->compile_db_insert_string(
            [
                'regid'      => $regid,
                'regcode'    => $reg_code,
                'ip_address' => $ibforums->input['IP_ADDRESS'],
                'ctime'      => time(),
            ]
        );

        $DB->query("INSERT INTO ibf_reg_antispam ({$str['FIELD_NAMES']}) VALUES ({$str['FIELD_VALUES']})");

        $this->output     .= $this->html->email_change($txt, $regid, $ibforums->lang[$msg]);
        $this->page_title = $ibforums->lang['t_welcome'];
        $this->nav        = ["<a href='" . $this->base_url . "&act=UserCP&CODE=00'>" . $ibforums->lang['t_title'] . '</a>'];
    }

    public function do_email_change()
    {
        global $ibforums, $DB, $std, $_POST, $print;

        if ($_POST['in_email_1'] == '') {
            $std->Error(['LEVEL' => 1, 'MSG' => 'complete_form']);
        }

        if ($_POST['in_email_2'] == '') {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_guests']);
        }

        //--------------------------------------------

        $email_one = strtolower(trim($ibforums->input['in_email_1']));
        $email_two = strtolower(trim($ibforums->input['in_email_2']));

        if ($email_one != $email_two) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'email_addy_mismatch']);
        }

        //--------------------------------------------

        $email_one = $std->clean_email($email_one);

        if ($email_one == '') {
            $std->Error(['LEVEL' => 1, 'MSG' => 'invalid_email']);
        }

        //--------------------------------------------

        if (!$ibforums->vars['allow_dup_email']) {
            $DB->query("SELECT uid FROM xbb_members WHERE email='" . $email_one . "'");
            $email_check = $DB->fetch_row();
            if ($email_check['id']) {
                $std->Error([LEVEL => 1, MSG => 'email_exists']);
            }
        }

        //---------------------------
        // Check the security code:
        //---------------------------

        if ($ibforums->input['regid'] == '') {
            $this->email_change('err_security_code');
            return '';
        }

        $DB->query("SELECT * FROM ibf_reg_antispam WHERE regid='" . trim(addslashes($ibforums->input['regid'])) . "'");

        if (!$row = $DB->fetch_row()) {
            $this->email_change('err_security_code');
            return '';
        }

        if (trim((int)$ibforums->input['reg_code']) != $row['regcode']) {
            $this->email_change('err_security_code');
            return '';
        }

        //--------------------------

        if ($ibforums->vars['reg_auth_type']) {
            $validate_key = $std->make_password();

            //--------------------------------------------
            // Update the new email, but enter a validation key
            // and put the member in "awaiting authorisation"
            // and send an email..
            //--------------------------------------------

            $DB->query("UPDATE xbb_members SET validate_key='$validate_key', prev_group='" . $this->member['mgroup'] . "', mgroup='" . $ibforums->vars['auth_group'] . "', email='$email_one' WHERE uid='" . $this->member['uid'] . "'");

            // Update their session with the new member group

            if ($ibforums->session_id) {
                $DB->query("UPDATE ibf_sessions SET member_name='', member_id='0', member_group='" . $ibforums->vars['guest_group'] . "' WHERE member_id='" . $this->member['uid'] . "' and id='" . $ibforums->session_id . "'");
            }

            // Kill the cookies to stop auto log in

            $std->my_setcookie('pass_hash', '-1', 0);
            $std->my_setcookie('member_id', '-1', 0);
            $std->my_setcookie('session_id', '-1', 0);

            // Dispatch the mail, and return to the activate form.

            $this->email->get_template('newemail');

            $this->email->build_message(
                [
                    'NAME'     => $this->member['uname'],
                    'MAN_LINK' => $this->base_url_nosess . '?act=Reg&CODE=07',
                    'ID'       => $this->member['uid'],
                    'CODE'     => $validate_key,
                ]
            );

            $this->email->subject = $ibforums->lang['lp_subject'] . ' ' . $ibforums->vars['board_name'];
            $this->email->to      = $email_one;

            $this->email->send_mail();

            $print->redirect_screen($ibforums->lang['ce_redirect'], 'act=Reg&CODE=07');
        } else {
            // No authorisation needed, change email addy and return

            $DB->query("UPDATE xbb_members SET email='$email_one' WHERE uid='" . $this->member['uid'] . "'");

            $print->redirect_screen($ibforums->lang['email_changed_now'], 'act=UserCP&CODE=00');
        }
    }

    //*******************************************************************/
    //| tracker:
    //|
    //| Print the subscribed topics listings
    //*******************************************************************/

    public function tracker()
    {
        global $ibforums, $DB, $std, $print;

        $this->output .= $this->html->subs_header();

        //----------------------------------------------------------
        // Are we checking for auto-prune?
        //----------------------------------------------------------

        $auto_explain = $ibforums->lang['no_auto_prune'];

        if ($ibforums->vars['subs_autoprune'] > 0) {
            if (time() % 2) {
                // Every now and again..

                $time_limit = time() - ($ibforums->vars['subs_autoprune'] * 86400);

                $DB->query("SELECT tr.trid FROM ibf_tracker tr, ibf_topics t WHERE t.tid=tr.topic_id AND t.last_post < '$time_limit'");

                $trids = [];

                while (false !== ($r = $DB->fetch_row())) {
                    $trids[] = $r['trid'];
                }

                if (count($trids) > 0) {
                    $DB->query('DELETE FROM ibf_tracker WHERE trid IN (' . implode(',', $trids) . ')');
                }
            }

            $auto_explain = sprintf($ibforums->lang['auto_prune'], $ibforums->vars['subs_autoprune']);
        }

        //----------------------------------------------------------
        // Do we have an incoming date cut?
        //----------------------------------------------------------

        $date_cut = (int)$ibforums->input['datecut'] != '' ? (int)$ibforums->input['datecut'] : 30;

        $date_query = $date_cut != 1000 ? " AND t.last_post > '" . (time() - ($date_cut * 86400)) . "' " : '';

        //----------------------------------------------------------
        // Query the DB for the subby toppy-ics - at the same time
        // we get the forum and topic info, 'cos we rule.
        //----------------------------------------------------------

        $DB->query(
            'SELECT tr.trid, tr.member_id, tr.topic_id, tr.last_sent, tr.start_date as track_started, t.*, f.id as forum_id, f.name as forum_name, f.read_perms '
            . 'FROM ibf_tracker tr, ibf_topics t, ibf_forums f '
            . "WHERE tr.member_id='"
            . $this->member['uid']
            . "' AND t.tid=tr.topic_id AND f.id=t.forum_id $date_query"
            . 'ORDER BY f.id, t.last_post DESC'
        );

        if ($DB->get_num_rows()) {
            $last_forum_id = -1;

            while (false !== ($topic = $DB->fetch_row())) {
                if ($last_forum_id != $topic['forum_id']) {
                    $last_forum_id = $topic['forum_id'];

                    $this->output .= $this->html->subs_forum_row($topic['forum_id'], $topic['forum_name']);
                }

                $topic['last_poster'] = ($topic['last_poster_id'] != 0) ? "<b><a href='{$this->base_url}&act=Profile&CODE=03&MID={$topic['last_poster_id']}'>{$topic['last_poster_name']}</a></b>" : '-' . $topic['last_poster_name'] . '-';

                $topic['starter'] = ($topic['starter_id'] != 0) ? "<a href='{$this->base_url}&act=Profile&CODE=03&MID={$topic['starter_id']}'>{$topic['starter_name']}</a>" : '-' . $topic['starter_name'] . '-';

                if ($topic['poll_state']) {
                    $topic['prefix'] = $ibforums->vars['pre_polls'] . ' ';
                }

                $topic['folder_icon'] = $std->folder_icon($topic);

                $topic['topic_icon'] = $topic['icon_id'] ? '<img src="' . $ibforums->vars[html_url] . '/icon' . $topic['icon_id'] . '.gif" border="0" alt="">' : '&nbsp;';

                if ($topic['pinned']) {
                    $topic['topic_icon'] = '<{B_PIN}>';
                }

                $topic['start_date'] = $std->get_date($topic['track_started'], 'LONG');

                if ($topic['description']) {
                    $topic['description'] .= '<br>';
                }

                $pages = 1;

                if ($topic['posts']) {
                    if ((($topic['posts'] + 1) % $ibforums->vars['display_max_posts']) == 0) {
                        $pages = ($topic['posts'] + 1) / $ibforums->vars['display_max_posts'];
                    } else {
                        $number = (($topic['posts'] + 1) / $ibforums->vars['display_max_posts']);
                        $pages  = ceil($number);
                    }
                }

                if ($pages > 1) {
                    $topic['PAGES'] = "<span class='small'>({$ibforums->lang['topic_sp_pages']} ";
                    for ($i = 0; $i < $pages; ++$i) {
                        $real_no = $i * $ibforums->vars['display_max_posts'];
                        $page_no = $i + 1;
                        if ($page_no == 4) {
                            $topic['PAGES'] .= "<a href='{$this->base_url}&act=ST&f={$this->forum['id']}&t={$topic['tid']}&st=" . ($pages - 1) * $ibforums->vars['display_max_posts'] . "'>...$pages </a>";
                            break;
                        } else {
                            $topic['PAGES'] .= "<a href='{$this->base_url}&act=ST&f={$this->forum['id']}&t={$topic['tid']}&st=$real_no'>$page_no </a>";
                        }
                    }
                    $topic['PAGES'] .= ')</span>';
                }

                if ($topic['posts'] < 0) {
                    $topic['posts'] = 0;
                }

                // Do the quick goto last page icon stuff

                $topic['last_post_date'] = $std->get_date($topic['last_post'], 'LONG');

                $this->output .= $this->html->subs_row($topic);
            }
        } else {
            $this->output .= $this->html->subs_none();
        }

        // Build date box

        $date_box = "<option value='1'>" . $ibforums->lang['subs_today'] . "</option>\n";

        foreach ([1, 7, 14, 21, 30, 60, 90, 365] as $day) {
            $selected = $day == $date_cut ? ' selected' : '';

            $date_box .= "<option value='$day'$selected>" . sprintf($ibforums->lang['subs_day'], $day) . "</option>\n";
        }

        $date_box .= "<option value='1000'>" . $ibforums->lang['subs_all'] . "</option>\n";

        $this->output .= $this->html->subs_end($auto_explain, $date_box);

        $this->page_title = $ibforums->lang['t_welcome'];
        $this->nav        = ["<a href='" . $this->base_url . "&act=UserCP&CODE=00'>" . $ibforums->lang['t_title'] . '</a>'];
    }

    public function do_delete_tracker()
    {
        global $ibforums, $std, $DB;

        //--------------------------------------
        // Get the ID's to delete
        //--------------------------------------

        if ($ibforums->input['request_method'] != 'post') {
            $std->Error(['LEVEL' => 1, 'MSG' => 'poss_hack_attempt']);
        }

        $ids = [];

        foreach ($ibforums->input as $key => $value) {
            if (preg_match("/^id-(\d+)$/", $key, $match)) {
                if ($ibforums->input[$match[0]]) {
                    $ids[] = $match[1];
                }
            }
        }

        if (count($ids) > 0) {
            $DB->query("DELETE from ibf_tracker WHERE member_id='" . $this->member['uid'] . "' and trid IN (" . implode(',', $ids) . ')');
        }

        $std->boink_it($this->base_url . '&act=UserCP&CODE=26');
    }

    //*******************************************************************/
    //| SKIN LANGS:
    //|
    //| Change skin and languages prefs.
    //*******************************************************************/

    public function skin_langs()
    {
        global $ibforums, $DB, $std, $print;

        // A serialized array holds our langauge settings.
        // The array is: 1 => array( '$dir', '$name'), 2 => ... etc

        $lang_array = [];

        $lang_select = "<select name='u_language' class='forminput'>\n";

        $DB->query('SELECT ldir, lname FROM ibf_languages');

        while (false !== ($l = $DB->fetch_row())) {
            $lang_select .= $l['ldir'] == $this->member['language'] ? "<option value='{$l['ldir']}' selected>{$l['lname']}</option>" : "<option value='{$l['ldir']}'>{$l['lname']}</option>";
        }

        $lang_select .= '</select>';

        $this->output .= $this->html->skin_lang_header($lang_select);

        if ($ibforums->vars['allow_skins'] == 1) {
            $DB->query('SELECT uid, sid, sname FROM ibf_skins WHERE hidden <> 1');

            if ($DB->get_num_rows()) {
                $skin_select = "<select name='u_skin' class='forminput'>\n";

                while (false !== ($s = $DB->fetch_row())) {
                    $skin_select .= $s['sid'] == $this->member['skin'] ? "<option value='{$s['sid']}' selected>{$s['sname']}</option>" : "<option value='{$s['sid']}'>{$s['sname']}</option>";
                }

                $skin_select .= '</select>';
            }

            $this->output .= $this->html->settings_skin($skin_select);
        }

        $this->output .= $this->html->skin_lang_end();

        $this->page_title = $ibforums->lang['t_welcome'];
        $this->nav        = ["<a href='" . $this->base_url . "&act=UserCP&CODE=00'>" . $ibforums->lang['t_title'] . '</a>'];
    }

    public function do_skin_langs()
    {
        $this->lib->do_skin_langs();
    }


    //*******************************************************************/
    //| BOARD PREFS:
    //|
    //| Set up view avatar, sig, time zone, etc.
    //*******************************************************************/

    public function board_prefs()
    {
        global $ibforums, $DB, $std, $print;

        $time = $std->get_date(time(), 'LONG');

        // Do we have a user stored offset, or use the board default:

        $offset = ($ibforums->member['timezone_offset'] != '') ? $ibforums->member['timezone_offset'] : $ibforums->vars['timezone_offset'];

        $time_select = "<select name='u_timezone' class='forminput'>";

        // Loop through the langauge time offsets and names to build our
        // HTML jump box.

        foreach ($ibforums->lang as $off => $words) {
            if (preg_match("/^time_(\S+)$/", $off, $match)) {
                $time_select .= $match[1] == $offset ? "<option value='{$match[1]}' selected>$words</option>" : "<option value='{$match[1]}'>$words</option>";
            }
        }

        $time_select .= '</select>';

        // Print out the header..

        if ($ibforums->member['dst_in_use']) {
            $dst_check = 'checked';
        } else {
            $dst_check = '';
        }

        //---------------------

        if ($ibforums->vars['postpage_contents'] == '') {
            $ibforums->vars['postpage_contents'] = '5,10,15,20,25,30,35,40';
        }

        if ($ibforums->vars['topicpage_contents'] == '') {
            $ibforums->vars['topicpage_contents'] = '5,10,15,20,25,30,35,40';
        }

        [$post_page, $topic_page] = explode('&', $ibforums->member['view_prefs']);

        if ($post_page == '') {
            $post_page = -1;
        }
        if ($topic_page == '') {
            $topic_page = -1;
        }

        $pp_a         = [];
        $tp_a         = [];
        $post_select  = '';
        $topic_select = '';

        $pp_a[] = ['-1', $ibforums->lang['pp_use_default']];
        $tp_a[] = ['-1', $ibforums->lang['pp_use_default']];

        foreach (explode(',', $ibforums->vars['postpage_contents']) as $n) {
            $n      = (int)trim($n);
            $pp_a[] = [$n, $n];
        }

        foreach (explode(',', $ibforums->vars['topicpage_contents']) as $n) {
            $n      = (int)trim($n);
            $tp_a[] = [$n, $n];
        }

        //---------------------

        foreach ($pp_a as $id => $data) {
            $post_select .= ($data[0] == $post_page) ? "<option value='{$data[0]}' selected>{$data[1]}\n" : "<option value='{$data[0]}'>{$data[1]}\n";
        }

        foreach ($tp_a as $id => $data) {
            $topic_select .= ($data[0] == $topic_page) ? "<option value='{$data[0]}' selected>{$data[1]}\n" : "<option value='{$data[0]}'>{$data[1]}\n";
        }

        //---------------------

        $this->output .= $this->html->settings_header($this->member, $time_select, $time, $dst_check);

        $hide_sess = $std->my_getcookie('hide_sess');

        // View avatars, signatures and images..

        $view_ava  = "<select name='VIEW_AVS' class='forminput'>";
        $view_sig  = "<select name='VIEW_SIGS' class='forminput'>";
        $view_img  = "<select name='VIEW_IMG' class='forminput'>";
        $view_pop  = "<select name='DO_POPUP' class='forminput'>";
        $html_sess = "<select name='HIDE_SESS' class='forminput'>";

        $view_ava .= $this->member['view_avs'] ? "<option value='1' selected>" . $ibforums->lang['yes'] . "</option>\n<option value='0'>" . $ibforums->lang['no'] . '</option>' : "<option value='1'>"
                                                                                                                                                                                  . $ibforums->lang['yes']
                                                                                                                                                                                  . "</option>\n<option value='0' selected>"
                                                                                                                                                                                  . $ibforums->lang['no']
                                                                                                                                                                                  . '</option>';

        $view_sig .= $this->member['attachsig'] ? "<option value='1' selected>" . $ibforums->lang['yes'] . "</option>\n<option value='0'>" . $ibforums->lang['no'] . '</option>' : "<option value='1'>"
                                                                                                                                                                                   . $ibforums->lang['yes']
                                                                                                                                                                                   . "</option>\n<option value='0' selected>"
                                                                                                                                                                                   . $ibforums->lang['no']
                                                                                                                                                                                   . '</option>';

        $view_img .= $this->member['view_img'] ? "<option value='1' selected>" . $ibforums->lang['yes'] . "</option>\n<option value='0'>" . $ibforums->lang['no'] . '</option>' : "<option value='1'>"
                                                                                                                                                                                  . $ibforums->lang['yes']
                                                                                                                                                                                  . "</option>\n<option value='0' selected>"
                                                                                                                                                                                  . $ibforums->lang['no']
                                                                                                                                                                                  . '</option>';

        $view_pop .= $this->member['view_pop'] ? "<option value='1' selected>" . $ibforums->lang['yes'] . "</option>\n<option value='0'>" . $ibforums->lang['no'] . '</option>' : "<option value='1'>"
                                                                                                                                                                                  . $ibforums->lang['yes']
                                                                                                                                                                                  . "</option>\n<option value='0' selected>"
                                                                                                                                                                                  . $ibforums->lang['no']
                                                                                                                                                                                  . '</option>';

        $html_sess .= $hide_sess == 1 ? "<option value='1' selected>" . $ibforums->lang['yes'] . "</option>\n<option value='0'>" . $ibforums->lang['no'] . '</option>' : "<option value='1'>" . $ibforums->lang['yes'] . "</option>\n<option value='0' selected>" . $ibforums->lang['no'] . '</option>';

        $this->output .= $this->html->settings_end(
            [
                'IMG'  => $view_img . '</select>',
                'SIG'  => $view_sig . '</select>',
                'AVA'  => $view_ava . '</select>',
                'POP'  => $view_pop . '</select>',
                'SESS' => $html_sess . '</select>',
                'TPS'  => $topic_select,
                'PPS'  => $post_select,
            ]
        );

        $this->page_title = $ibforums->lang['t_welcome'];
        $this->nav        = ["<a href='" . $this->base_url . "&act=UserCP&CODE=00'>" . $ibforums->lang['t_title'] . '</a>'];
    }

    public function do_board_prefs()
    {
        $this->lib->do_board_prefs();
    }

    //*******************************************************************/
    //| EMAIL SETTINGS:
    //|
    //| Set up the email stuff.
    //*******************************************************************/

    public function email_settings()
    {
        global $ibforums, $DB, $std, $print;

        // PM_REMINDER: First byte = Email PM when received new
        //   			Second byte= Show pop-up when new PM received

        $info = [];

        foreach ([allow_admin_mails, email_full, email_pm, auto_track] as $k) {
            if (!empty($this->member[$k])) {
                $info[$k] = 'checked';
            }
        }
        // Add skin convert
        if (empty($this->member[user_viewemail])) {
            $info[hide_email] = 'checked';
        }
        //
        $this->output .= $this->html->email($info);

        $this->page_title = $ibforums->lang['t_welcome'];
        $this->nav        = ["<a href='" . $this->base_url . "&act=UserCP&CODE=00'>" . $ibforums->lang['t_title'] . '</a>'];
    }

    public function do_email_settings()
    {
        $this->lib->do_email_settings();
    }



    //*******************************************************************/
    //| AVATAR:
    //|
    //| Displays the avatar choices
    //*******************************************************************/

    public function avatar()
    {
        global $ibforums, $DB, $std, $print;

        //------------------------------------------
        // Organise the dimensions
        //------------------------------------------

        [$this->member['AVATAR_WIDTH'], $this->member['AVATAR_HEIGHT']] = explode('x', $this->member['avatar_size']);
        [$ibforums->vars['av_width'], $ibforums->vars['av_height']] = explode('x', $ibforums->vars['avatar_dims']);
        [$w, $h] = explode('x', $ibforums->vars['avatar_def']);

        //------------------------------------------
        // Get the users current avatar to display
        //------------------------------------------

        $my_avatar = $std->get_avatar($this->member['user_avatar'], 1, $this->member['avatar_size']);

        $my_avatar = $my_avatar ?: 'noavatar';

        //------------------------------------------
        // Get the avatar gallery
        //------------------------------------------

        $avatar_gallery = [];

        $dh = opendir($ibforums->vars['html_dir'] . '../../../uploads');
        while ($file = readdir($dh)) {
            if (preg_match('/^savt.*|^noavatar|^blank/is', $file)) {
                $avatar_gallery[] = $file;
            }
        }
        closedir($dh);

        //------------------------------------------
        // Get the avatar gallery selected
        //------------------------------------------

        $url_avatar = 'http://';

        $avatar_gall_selected = 'blank.gif';

        $uploaded_avatar = 0;

        if (($this->member['user_avatar'] != '') and ($this->member['user_avatar'] != 'noavatar')) {
            if (0 === strpos($this->member['user_avatar'], "cavt")) {
                $uploaded_avatar = 1;
            } elseif (0 !== stripos($this->member['user_avatar'], "http")) {
                $avatar_gall_selected = $this->member['user_avatar'];
            } else {
                $url_avatar = $this->member['user_avatar'];
            }
        }

        $allowed_ext = '.' . implode(' .', explode('|', $ibforums->vars['avatar_ext']));

        $show_avatar = "<img src='{$ibforums->vars['AVATARS_URL']}/$avatar_gall_selected' name='show_avatar' border='0' hspace='15'>";

        //------------------------------------------
        // Organise the avatar select box
        //------------------------------------------

        $av_html = "<select name='gallery_list' size='10' onchange=\"showavatar('{$ibforums->vars['AVATARS_URL']}/')\" class='forminput'>";
        //avt fix
        asort($avatar_gallery);
        //
        foreach ($avatar_gallery as $v) {
            $view    = preg_replace("/\.(\S+)$/", '', $v);
            $av_html .= $v == $avatar_gall_selected ? "<option value='$v' selected>$view</option>\n" : "<option value='$v'>$view</option>\n";
        }

        $av_html .= '</select>';

        $formextra    = '';
        $hidden_field = '';

        if ($ibforums->member['g_avatar_upload'] == 1) {
            $formextra    = " enctype='multipart/form-data'";
            $hidden_field = "<input type='hidden' name='MAX_FILE_SIZE' value='" . ($ibforums->vars['avup_size_max'] * 1024) . "'>";
        }

        $this->output .= $this->html->personal_avatar(
            [
                'MEMBER'   => $this->member,
                'AVATARS'  => $av_html,
                'SHOW_AVS' => $show_avatar,
                'CUR_AV'   => $my_avatar,
            ],
            $formextra,
            $hidden_field
        );
        if ($ibforums->vars['avatar_url']) {
            $this->output .= $this->html->personal_avatar_URL($this->member, $url_avatar, $allowed_ext);

            $text = $uploaded_avatar == 1 ? $ibforums->lang['avup_yes'] : $ibforums->lang['avup_no'];

            if ($ibforums->member['g_avatar_upload'] == 1) {
                $this->output = preg_replace("/<!-- IBF\.UPLOAD_AVATAR -->/e", '$this->html->avatar_upload_field($text)', $this->output);
            }
        }

        $this->output .= $this->html->personal_avatar_end();

        $this->page_title = $ibforums->lang['t_welcome'];
        $this->nav        = ["<a href='" . $this->base_url . "&act=UserCP&CODE=00'>" . $ibforums->lang['t_title'] . '</a>'];
    }

    public function do_avatar()
    {
        $this->lib->do_avatar();
    }

    //*******************************************************************/
    //| SIGNATURE:
    //|
    //| Displays the signature form
    //*******************************************************************/

    public function signature()
    {
        global $ibforums, $DB, $std, $print;

        $t_sig = $this->parser->unconvert($this->member['signature'], $ibforums->vars['sig_allow_ibc'], $ibforums->vars['sig_allow_html']);

        $ibforums->lang['the_max_length'] = $ibforums->vars['max_sig_length'] ?: 0;

        $this->output .= $this->html->signature($this->member['signature'], $t_sig);

        $this->page_title = $ibforums->lang['t_welcome'];
        $this->nav        = ["<a href='" . $this->base_url . "&act=UserCP&CODE=00'>" . $ibforums->lang['t_title'] . '</a>'];
    }

    public function do_signature()
    {
        $this->lib->do_signature();
    }

    //*******************************************************************/
    //| PERSONAL:
    //|
    //| Displays the personal info form
    //*******************************************************************/

    public function personal()
    {
        global $ibforums, $DB, $std, $print;

        //-----------------------------------------------
        // Check to make sure that we can edit profiles..
        //-----------------------------------------------

        if (empty($ibforums->member['g_edit_profile'])) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'cant_use_feature']);
        }

        //-----------------------------------------------
        // Format the birthday drop boxes..
        //-----------------------------------------------

        $date = getdate();

        $day  = "<option value='0'>--</option>";
        $mon  = "<option value='0'>--</option>";
        $year = "<option value='0'>--</option>";

        for ($i = 1; $i < 32; $i++) {
            $day .= "<option value='$i'";

            $day .= $i == $this->member['bday_day'] ? "selected>$i</option>" : ">$i</option>";
        }

        for ($i = 1; $i < 13; $i++) {
            $mon .= "<option value='$i'";

            $mon .= $i == $this->member['bday_month'] ? "selected>{$ibforums->lang['month'.$i]}</option>" : ">{$ibforums->lang['month'.$i]}</option>";
        }

        $i = $date['year'] - 1;
        $j = $date['year'] - 100;

        for ($i; $j < $i; $i--) {
            $year .= "<option value='$i'";

            $year .= $i == $this->member['bday_year'] ? "selected>$i</option>" : ">$i</option>";
        }

        //-----------------------------------------------
        // Custom profile fields stuff
        //-----------------------------------------------

        $required_output = '';
        $optional_output = '';
        $field_data      = [];

        $DB->query("SELECT * from ibf_pfields_content WHERE member_id='" . $ibforums->member['uid'] . "'");

        while (false !== ($content = $DB->fetch_row())) {
            foreach ($content as $k => $v) {
                if (preg_match("/^field_(\d+)$/", $k, $match)) {
                    $field_data[$match[1]] = $v;
                    //break;
                }
            }
        }

        $DB->query('SELECT * FROM ibf_pfields_data WHERE fedit=1 ORDER BY forder');

        while (false !== ($row = $DB->fetch_row())) {
            $form_element = '';

            if ($row['freq'] == 1) {
                $ftype = 'required_output';
            } else {
                $ftype = 'optional_output';
            }

            if ($row['ftype'] == 'drop') {
                $carray = explode('|', trim($row['fcontent']));

                $d_content = '';

                foreach ($carray as $entry) {
                    $value = explode('=', $entry);

                    $ov = trim($value[0]);
                    $td = trim($value[1]);

                    if ($ov != '' and $td != '') {
                        $d_content .= ($field_data[$row['fid']] == $ov) ? "<option value='$ov' selected>$td</option>\n" : "<option value='$ov'>$td</option>\n";
                    }
                }

                if ($d_content != '') {
                    $form_element = $this->html->field_dropdown('field_' . $row['fid'], $d_content);
                }
            } elseif ($row['ftype'] == 'area') {
                $form_element = $this->html->field_textarea('field_' . $row['fid'], $field_data[$row['fid']]);
            } else {
                $form_element = $this->html->field_textinput('field_' . $row['fid'], $field_data[$row['fid']]);
            }

            ${$ftype} .= $this->html->field_entry($row['ftitle'], $row['fdesc'], $form_element);
        }

        //-----------------------------------------------
        // Format the interest / location boxes
        //-----------------------------------------------

        $this->member['location']  = $this->parser->unconvert($this->member['user_from']);
        $this->member['interests'] = $this->parser->unconvert($this->member['user_intrest']);
        //convert skin compatibility
        $this->member['website']    = $this->parser->unconvert($this->member['url']);
        $this->member['icq_number'] = $this->parser->unconvert($this->member['user_icq']);
        $this->member['aim_name']   = $this->parser->unconvert($this->member['user_aim']);
        $this->member['yahoo']      = $this->parser->unconvert($this->member['user_yim']);
        $this->member['msnname']    = $this->parser->unconvert($this->member['user_msnm']);
        //end convert
        //-----------------------------------------------
        // Suck up the HTML and swop some tags if need be
        //-----------------------------------------------

        $this->output .= $this->html->personal_panel($this->member);

        if (($ibforums->vars['post_titlechange']) and ($this->member['posts'] > $ibforums->vars['post_titlechange'])) {
            $t_html       = $this->html->member_title($this->member['title']);
            $this->output = preg_replace("/<!--\{MEMBERTITLE\}-->/", $t_html, $this->output);
        }

        $t_html = $this->html->birthday($day, $mon, $year);

        $this->output = preg_replace("/<!--\{BIRTHDAY\}-->/", $t_html, $this->output);

        //-----------------------------------------------
        // Add in the custom fields if we need to.
        //-----------------------------------------------

        if ($required_output != '') {
            $this->output = str_replace('<!--{REQUIRED.FIELDS}-->', $this->html->required_title() . "\n" . $required_output, $this->output);
        }

        if ($optional_output != '') {
            $this->output = str_replace('<!--{OPTIONAL.FIELDS}-->', "\n" . $optional_output, $this->output);
        }

        $this->page_title = $ibforums->lang['t_welcome'];
        $this->nav        = ["<a href='" . $this->base_url . "&act=UserCP&CODE=00'>" . $ibforums->lang['t_title'] . '</a>'];
    }

    public function do_personal()
    {
        // Hand it straight to our library to keep this code clean and compact.

        $this->lib->do_profile();
    }




    //*******************************************************************/
    //| SPLASH:
    //|
    //| Displays the intro screen
    //*******************************************************************/

    public function splash()
    {
        global $ibforums, $DB, $std, $print;

        //-----------------------------------------------
        // Format the basic data
        //-----------------------------------------------

        $info['MEMBER_EMAIL']    = $this->member['email'];
        $info['DATE_REGISTERED'] = $std->get_date($this->member['user_regdate'], 'LONG');
        $info['MEMBER_POSTS']    = $this->member['posts'];

        $info['DAILY_AVERAGE'] = $ibforums->lang['no_posts'];

        if ($this->member['posts'] > 0) {
            $diff                  = time() - $this->member['user_regdate'];
            $days                  = ($diff / 3600) / 24;
            $days                  = $days < 1 ? 1 : $days;
            $info['DAILY_AVERAGE'] = sprintf('%.2f', ($this->member['posts'] / $days));
        }

        //---------------------------------------------
        // Get the number of messages we have in total.
        //---------------------------------------------

        $DB->query("SELECT COUNT(*) as msg_total FROM ibf_messages WHERE member_id='" . $this->member['uid'] . "'");
        $total = $DB->fetch_row();

        //---------------------------------------------
        // Make sure we've not exceeded our alloted allowance.
        //---------------------------------------------

        $info['full_messenger'] = '';
        $info['full_percent']   = '';
        $info['space_free']     = 'Unlimited';
        $info['total_messages'] = $total['msg_total'];

        if ($ibforums->member['g_max_messages'] > 0) {
            if ($total['msg_total'] >= $ibforums->member['g_max_messages']) {
                $info['full_messenger'] = "<span class='highlight'>" . $ibforums->lang['folders_full'] . '</span>';
            }

            $info['full_percent'] = $total['msg_total'] ? sprintf('%.0f', (($total['msg_total'] / $ibforums->member['g_max_messages']) * 100)) : 0;
            $info['full_percent'] = '(' . $info['full_percent'] . '% ' . $ibforums->lang['total_capacity'] . ')';
            $info['space_free']   = $ibforums->member['g_max_messages'] - $total['msg_total'];
        }

        //-----------------------------------------------
        // Write the data..
        //-----------------------------------------------

        $s_array = [
            's' => 5,
            'm' => 7,
            'l' => 15,
        ];

        $info['NOTES'] = $this->notes ?: $ibforums->lang['note_pad_empty'];

        $info['SIZE'] = $s_array[$this->size];

        $info['SIZE_CHOICE'] = '';

        //------------------------------------
        // If someone has cheated, fix it now.
        //-------------------------------------

        if (empty($info['SIZE'])) {
            $info['SIZE'] = '5';
        }

        //-------------------------------------
        // Make the choice HTML.
        //-------------------------------------

        foreach ($s_array as $k => $v) {
            if ($v == $info['SIZE']) {
                $info['SIZE_CHOICE'] .= "<option value='$k' selected>{$ibforums->lang['ta_'.$k]}</option>";
            } else {
                $info['SIZE_CHOICE'] .= "<option value='$k'>{$ibforums->lang['ta_'.$k]}</option>";
            }
        }

        $info['NOTES'] = preg_replace('/<br>/', "\n", $info['NOTES']);

        $this->output .= $this->html->splash($info);

        // If no messenger, remove the links!

        if (!$ibforums->member['g_use_pm']) {
            $this->output = preg_replace('/<!-- MSG -->.+?<!-- END MSG -->/s', '', $this->output);
        }

        $this->page_title = $ibforums->lang['t_welcome'];
        $this->nav        = ["<a href='" . $this->base_url . "&act=UserCP&CODE=00'>" . $ibforums->lang['t_title'] . '</a>'];
    }






    //*******************************************************************/
    //| UPDATE_NOTEPAD:
    //|
    //| Displays the intro screen
    //*******************************************************************/

    public function update_notepad()
    {
        global $ibforums, $DB, $std, $_POST;

        // Do we have an entry for this member?

        if ($_POST['act'] == '') {
            $std->Error(['LEVEL' => 1, 'MSG' => 'complete_form']);
        }
        //+----------------------------------------

        $DB->query("SELECT id from ibf_member_extra WHERE id='" . $this->member['uid'] . "'");

        if ($DB->get_num_rows()) {
            $DB->query("UPDATE ibf_member_extra SET notes='" . $ibforums->input['notes'] . "', ta_size='" . $ibforums->input['ta_size'] . "' WHERE id='" . $this->member['uid'] . "'");
        } else {
            $DB->query(
                'INSERT INTO ibf_member_extra (id, notes, ta_size) ' . " VALUES ('" . $this->member['uid'] . "', '" . $ibforums->input['notes'] . "', '" . $ibforums->input['ta_size'] . "')"
            );
        }

        $std->boink_it($this->base_url . '&act=UserCP&CODE=00');
        exit;
    }

    public function show_image()
    {
        global $ibforums, $DB;

        // Init array

        // Must put this into functions.php at some point

        $numbers = [
            0 => 'R0lGODlhCAANAJEAAAAAAP////4BAgAAACH5BAQUAP8ALAAAAAAIAA0AAAIUDH5hiKsOnmqSPjtT1ZdnnjCUqBQAOw==',
            1 => 'R0lGODlhCAANAJEAAAAAAP////4BAgAAACH5BAQUAP8ALAAAAAAIAA0AAAIUjAEWyMqoXIprRkjxtZJWrz3iCBQAOw==',
            2 => 'R0lGODlhCAANAJEAAAAAAP////4BAgAAACH5BAQUAP8ALAAAAAAIAA0AAAIUDH5hiKubnpPzRQvoVbvyrDHiWAAAOw==',
            3 => 'R0lGODlhCAANAJEAAAAAAP////4BAgAAACH5BAQUAP8ALAAAAAAIAA0AAAIVDH5hiKbaHgRyUZtmlPtlfnnMiGUFADs=',
            4 => 'R0lGODlhCAANAJEAAAAAAP////4BAgAAACH5BAQUAP8ALAAAAAAIAA0AAAIVjAN5mLDtjFJMRjpj1Rv6v1SHN0IFADs=',
            5 => 'R0lGODlhCAANAJEAAAAAAP////4BAgAAACH5BAQUAP8ALAAAAAAIAA0AAAIUhA+Bpxn/DITL1SRjnps63l1M9RQAOw==',
            6 => 'R0lGODlhCAANAJEAAAAAAP////4BAgAAACH5BAQUAP8ALAAAAAAIAA0AAAIVjIEYyWwH3lNyrQTbnVh2Tl3N5wQFADs=',
            7 => 'R0lGODlhCAANAJEAAAAAAP////4BAgAAACH5BAQUAP8ALAAAAAAIAA0AAAIUhI9pwbztAAwP1napnFnzbYEYWAAAOw==',
            8 => 'R0lGODlhCAANAJEAAAAAAP////4BAgAAACH5BAQUAP8ALAAAAAAIAA0AAAIVDH5hiKubHgSPWXoxVUxC33FZZCkFADs=',
            9 => 'R0lGODlhCAANAJEAAAAAAP////4BAgAAACH5BAQUAP8ALAAAAAAIAA0AAAIVDA6hyJabnnISnsnybXdS73hcZlUFADs=',
        ];

        if ($ibforums->input['p'] == '') {
            return false;
        }

        if ($ibforums->input['rc'] == '') {
            return false;
        }

        // Get the info from the db

        $DB->query("SELECT * FROM ibf_reg_antispam WHERE regid='" . trim(addslashes($ibforums->input['rc'])) . "'");

        if (!$row = $DB->fetch_row()) {
            return false;
        }

        $p = (int)$ibforums->input['p'] - 1; //substr starts from 0, not 1 :p

        $this_number = substr($row['regcode'], $p, 1);

        flush();
        header('Content-type: image/gif');
        echo base64_decode($numbers[$this_number]);
        exit();
    }
}



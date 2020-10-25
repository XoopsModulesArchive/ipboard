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
|   > ICQ / AIM / EMAIL functions
|   > Module written by Matt Mecham
|   > Date started: 28th February 2002
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

$idx = new Contact();

class Contact
{
    public $output = '';

    public $base_url = '';

    public $html = '';

    public $nav = [];

    public $page_title = '';

    public $email = '';

    public $forum = '';

    public $email = '';

    /***********************************************************************************/

    // Our constructor, load words, load skin

    /***********************************************************************************/

    public function __construct()
    {
        global $ibforums, $DB, $std, $print, $skin_universal;

        // What to do?

        switch ($ibforums->input['act']) {
            case 'Mail':
                $this->mail_member();
                break;
            case 'AOL':
                $this->show_aim();
                break;
            case 'ICQ':
                $this->show_icq();
                break;
            case 'MSN':
                $this->show_msn();
                break;
            case 'YAHOO':
                $this->show_yahoo();
                break;
            case 'Invite':
                $this->invite_member();
                break;
            case 'chat':
                $this->chat_display();
                break;
            case 'report':
                if (1 != $ibforums->input['send']) {
                    $this->report_form();
                } else {
                    $this->send_report();
                }
                break;
            default:
                $std->Error(['LEVEL' => 1, 'MSG' => 'invalid_use']);
                break;
        }

        $print->add_output((string)$this->output);

        $print->do_output(['TITLE' => $this->page_title, 'JS' => 0, NAV => $this->nav]);
    }

    //****************************************************************/

    // IP CHAT:

    //****************************************************************/

    public function chat_display()
    {
        global $ibforums, $DB, $std, $print;

        $ibforums->lang = $std->load_words($ibforums->lang, 'lang_emails', $ibforums->lang_id);

        $this->html = $std->load_template('skin_emails');

        if (!$ibforums->vars['chat_account_no']) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'missing_files']);
        }

        $width = $ibforums->vars['chat_width'] ?: 600;

        $height = $ibforums->vars['chat_height'] ?: 350;

        $lang = $ibforums->vars['chat_language'] ?: 'en';

        if ($ibforums->input['pop']) {
            $html = $this->html->chat_pop($ibforums->vars['chat_account_no'], $lang, $width, $height);

            $print->pop_up_window('CHAT', $html);

            exit();
        }

        $this->output .= $this->html->chat_inline($ibforums->vars['chat_account_no'], $lang, $width, $height);

        $this->nav[] = $ibforums->lang['live_chat'];

        $this->page_title = $ibforums->lang['live_chat'];
    }

    //****************************************************************/

    // REPORT POST FORM:

    //****************************************************************/

    public function report_form()
    {
        global $ibforums, $DB, $std, $print;

        $ibforums->lang = $std->load_words($ibforums->lang, 'lang_emails', $ibforums->lang_id);

        $this->html = $std->load_template('skin_emails');

        $pid = (int)$ibforums->input['p'];

        $tid = (int)$ibforums->input['t'];

        $fid = (int)$ibforums->input['f'];

        $st = (int)$ibforums->input['st'];

        if ((!$pid) and (!$tid) and (!$fid)) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'missing_files']);
        }

        // Do we have permission to do stuff in this forum? Lets hope so eh?!

        $this->check_access($fid, $tid);

        $this->output .= $this->html->report_form($fid, $tid, $pid, $st, $this->forum['topic_title']);

        $this->nav[] = "<a href='" . $ibforums->base_url . "&act=SC&c={$this->forum['cat_id']}'>{$this->forum['cat_name']}</a>";

        $this->nav[] = "<a href='" . $ibforums->base_url . "&act=SF&f={$this->forum['id']}'>{$this->forum['name']}</a>";

        $this->nav[] = $ibforums->lang['report_title'];

        $this->page_title = $ibforums->lang['report_title'];
    }

    public function send_report()
    {
        global $ibforums, $DB, $std, $print, $_POST;

        $ibforums->lang = $std->load_words($ibforums->lang, 'lang_emails', $ibforums->lang_id);

        $this->html = $std->load_template('skin_emails');

        $pid = (int)$ibforums->input['p'];

        $tid = (int)$ibforums->input['t'];

        $fid = (int)$ibforums->input['f'];

        $st = (int)$ibforums->input['st'];

        if ((!$pid) and (!$tid) and (!$fid)) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'missing_files']);
        }

        //--------------------------------------------

        // Make sure we came in via a form.

        //--------------------------------------------

        if ('' == $_POST['message']) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'complete_form']);
        }

        //--------------------------------------------

        // Get the topic title

        //--------------------------------------------

        $DB->query("SELECT title FROM ibf_topics WHERE tid='$tid'");

        $topic = $DB->fetch_row();

        if (!$topic['title']) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'missing_files']);
        }

        //--------------------------------------------

        // Do we have permission to do stuff in this forum? Lets hope so eh?!

        //--------------------------------------------

        $this->check_access($fid, $tid);

        $mods = [];

        // Check for mods in this forum

        $DB->query("SELECT m.uname, m.email, mod.member_id FROM ibf_moderators mod, xbb_members m WHERE mod.forum_id='$fid' and mod.member_id=m.uid");

        if ($DB->get_num_rows()) {
            while (false !== ($r = $DB->fetch_row())) {
                $mods[] = [
                    'name' => $r['uname'],
'email' => $r['email'],
                ];
            }
        } else {
            //--------------------------------------------

            // No mods? Get those with control panel access

            //--------------------------------------------

            $DB->query('SELECT m.uid, m.uname, m.email FROM xbb_members m, ibf_groups g WHERE g.g_access_cp=1 AND m.mgroup=g.g_id');

            while (false !== ($r = $DB->fetch_row())) {
                $mods[] = [
                    'name' => $r['uname'],
'email' => $r['email'],
                ];
            }
        }

        //--------------------------------------------

        // Get the emailer module

        //--------------------------------------------

        require './sources/lib/emailer.php';

        $this->email = new emailer();

        //--------------------------------------------

        // Loop and send the mail

        //--------------------------------------------

        $report = trim(stripslashes($_POST['message']));

        $report = str_replace('<!--', '', $report);

        $report = str_replace('-->', '', $report);

        $report = str_replace('<script', '', $report);

        foreach ($mods as $idx => $data) {
            $this->email->get_template('report_post');

            $this->email->build_message(
                [
                    'MOD_NAME' => $data['uname'],
'USERNAME' => $ibforums->member['uname'],
'TOPIC' => $topic['title'],
'LINK_TO_POST' => "{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}" . "?act=ST&f=$fid&t=$tid&st=$st&#entry$pid",
'REPORT' => $report,
                ]
            );

            $this->email->subject = $ibforums->lang['report_subject'] . ' ' . $ibforums->vars['board_name'];

            $this->email->to = $data['email'];

            $this->email->send_mail();
        }

        $print->redirect_screen($ibforums->lang['report_redirect'], "act=ST&f=$fid&t=$tid&st=$st&#entry$pid");
    }

    //--------------------------------------------

    public function check_access($fid, $tid)
    {
        global $ibforums, $DB, $std, $HTTP_COOKIE_VARS;

        if (!$ibforums->member['uid']) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_permission']);
        }

        //--------------------------------

        $DB->query('SELECT t.title as topic_title, f.*, c.id as cat_id, c.name as cat_name from ibf_forums f, ibf_categories c, ibf_topics t WHERE f.id=' . $fid . " and c.id=f.category and t.tid=$tid");

        $this->forum = $DB->fetch_row();

        $return = 1;

        if ('*' == $this->forum['read_perms']) {
            $return = 0;
        } elseif (preg_match('/(^|,)' . $ibforums->member['mgroup'] . '(,|$)/', $this->forum['read_perms'])) {
            $return = 0;
        }

        if ($this->forum['password']) {
            if ($HTTP_COOKIE_VARS[$ibforums->vars['cookie_id'] . 'iBForum' . $this->forum['id']] == $this->forum['password']) {
                $return = 0;
            }
        }

        if (1 == $return) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_permission']);
        }
    }

    //****************************************************************/

    // MSN CONSOLE:

    //****************************************************************/

    public function show_msn()
    {
        global $ibforums, $DB, $std, $print;

        $this->html = $std->load_template('skin_emails');

        $ibforums->lang = $std->load_words($ibforums->lang, 'lang_emails', $ibforums->lang_id);

        //----------------------------------

        if (empty($ibforums->member['uid'])) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_guests']);
        }

        if (empty($ibforums->input['MID'])) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'invalid_use']);
        }

        //----------------------------------

        if (!preg_match("/^(\d+)$/", $ibforums->input['MID'])) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'invalid_use']);
        }

        //----------------------------------

        $DB->query("SELECT uname, uid, msnname from xbb_members WHERE uid='" . $ibforums->input['MID'] . "'");

        $member = $DB->fetch_row();

        //----------------------------------

        if (!$member['uid']) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_such_user']);
        }

        //----------------------------------

        if (!$member['user_msnm']) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_msn']);
        }

        //----------------------------------

        $html = $this->html->pager_header(['TITLE' => 'MSN']);

        $html .= $this->html->msn_body($member['user_msnm']);

        $html .= $this->html->end_table();

        $print->pop_up_window('MSN CONSOLE', $html);
    }

    //****************************************************************/

    // Yahoo! CONSOLE:

    //****************************************************************/

    public function show_yahoo()
    {
        global $ibforums, $DB, $std, $print;

        $this->html = $std->load_template('skin_emails');

        $ibforums->lang = $std->load_words($ibforums->lang, 'lang_emails', $ibforums->lang_id);

        //----------------------------------

        if (empty($ibforums->member['uid'])) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_guests']);
        }

        if (empty($ibforums->input['MID'])) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'invalid_use']);
        }

        //----------------------------------

        if (!preg_match("/^(\d+)$/", $ibforums->input['MID'])) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'invalid_use']);
        }

        //----------------------------------

        $DB->query("SELECT uname, uid, user_yim from xbb_members WHERE uid='" . $ibforums->input['MID'] . "'");

        $member = $DB->fetch_row();

        //----------------------------------

        if (!$member['uid']) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_such_user']);
        }

        //----------------------------------

        if (!$member['user_yim']) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_yahoo']);
        }

        //----------------------------------

        $html = $this->html->pager_header(['TITLE' => 'Yahoo!']);

        $html .= $this->html->yahoo_body($member['user_yim']);

        $html .= $this->html->end_table();

        $print->pop_up_window('YAHOO! CONSOLE', $html);
    }

    //****************************************************************/

    // AOL CONSOLE:

    //****************************************************************/

    public function show_aim()
    {
        global $ibforums, $DB, $std, $print;

        $ibforums->lang = $std->load_words($ibforums->lang, 'lang_emails', $ibforums->lang_id);

        $this->html = $std->load_template('skin_emails');

        //----------------------------------

        if (empty($ibforums->member['uid'])) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_guests']);
        }

        if (empty($ibforums->input['MID'])) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'invalid_use']);
        }

        //----------------------------------

        if (!preg_match("/^(\d+)$/", $ibforums->input['MID'])) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'invalid_use']);
        }

        //----------------------------------

        $DB->query("SELECT uname, uid, aim_name from xbb_members WHERE uid='" . $ibforums->input['MID'] . "'");

        $member = $DB->fetch_row();

        //----------------------------------

        if (!$member['uid']) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_such_user']);
        }

        //----------------------------------

        if (!$member['user_aim']) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_aol']);
        }

        $member['aim_name'] = str_replace(' ', '', $member['user_aim']);

        //----------------------------------

        $print->pop_up_window('AOL CONSOLE', $this->html->aol_body(['AOLNAME' => $member['user_aim']]));
    }

    //****************************************************************/

    // ICQ CONSOLE:

    //****************************************************************/

    public function show_icq()
    {
        global $ibforums, $DB, $std, $print;

        $ibforums->lang = $std->load_words($ibforums->lang, 'lang_emails', $ibforums->lang_id);

        $this->html = $std->load_template('skin_emails');

        //----------------------------------

        if (empty($ibforums->member['uid'])) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_guests']);
        }

        if (empty($ibforums->input['MID'])) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'invalid_use']);
        }

        //----------------------------------

        if (!preg_match("/^(\d+)$/", $ibforums->input['MID'])) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'invalid_use']);
        }

        //----------------------------------

        $DB->query("SELECT uname, uid, icq_number from xbb_members WHERE uid='" . $ibforums->input['MID'] . "'");

        $member = $DB->fetch_row();

        //----------------------------------

        if (!$member['uid']) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_such_user']);
        }

        //----------------------------------

        if (!$member['user_icq']) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_icq']);
        }

        //----------------------------------

        $html = $this->html->pager_header([$ibforums->lang['icq_title']]);

        $html .= $this->html->icq_body(['UIN' => $member['user_icq']]);

        $html .= $this->html->end_table();

        $print->pop_up_window('ICQ CONSOLE', $html);
    }

    //****************************************************************/

    // MAIL MEMBER:

    // Handles the routines called by clicking on the "email" button when

    // reading topics

    //****************************************************************/

    public function mail_member()
    {
        global $ibforums, $DB, $std, $print;

        require './sources/lib/emailer.php';

        $this->email = new emailer();

        //----------------------------------

        $ibforums->lang = $std->load_words($ibforums->lang, 'lang_emails', $ibforums->lang_id);

        $this->html = $std->load_template('skin_emails');

        //----------------------------------

        if (empty($ibforums->member['uid'])) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_guests']);
        }

        if (!$ibforums->member['g_email_friend']) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_member_mail']);
        }

        //----------------------------------

        if ('01' == $ibforums->input['CODE']) {
            // Send the email, yippee

            if (empty($ibforums->input['to'])) {
                $std->Error(['LEVEL' => 1, 'MSG' => 'invalid_use']);
            }

            //----------------------------------

            if (!preg_match("/^(\d+)$/", $ibforums->input['to'])) {
                $std->Error(['LEVEL' => 1, 'MSG' => 'invalid_use']);
            }

            //----------------------------------

            $DB->query("SELECT uname, uid, hide_email, email  from xbb_members WHERE uid='" . $ibforums->input['to'] . "'");

            $member = $DB->fetch_row();

            //----------------------------------

            if (!$member['uid']) {
                $std->Error(['LEVEL' => 1, 'MSG' => 'no_such_user']);
            }

            //----------------------------------

            $check_array = [
                'message' => 'no_message',
'subject' => 'no_subject',
            ];

            foreach ($check_array as $input => $msg) {
                if (empty($ibforums->input[$input])) {
                    $std->Error([LEVEL => 1, MSG => $msg]);
                }
            }

            $this->email->get_template('email_member');

            $this->email->build_message(
                [
                    'MESSAGE' => str_replace('<br>', "\n", str_replace("\r", '', $ibforums->input['message'])),
'MEMBER_NAME' => $member['uname'],
'FROM_NAME' => $ibforums->member['uname'],
                ]
            );

            $this->email->subject = $ibforums->input['subject'];

            $this->email->to = $member['email'];

            $this->email->from = $ibforums->member['email'];

            $this->email->send_mail();

            $forum_jump = $std->build_forum_jump();

            $forum_jump = preg_replace('!#Forum Jump#!', $ibforums->lang['forum_jump'], $forum_jump);

            $this->output = $this->html->sent_screen($member['uname']);

            $this->output .= $this->html->forum_jump($forum_jump);

            $this->page_title = $ibforums->lang['email_sent'];

            $this->nav = [$ibforums->lang['email_sent']];
        } else {
            // Show the form, booo...

            if (empty($ibforums->input['MID'])) {
                $std->Error(['LEVEL' => 1, 'MSG' => 'invalid_use']);
            }

            //----------------------------------

            if (!preg_match("/^(\d+)$/", $ibforums->input['MID'])) {
                $std->Error(['LEVEL' => 1, 'MSG' => 'invalid_use']);
            }

            //----------------------------------

            $DB->query("SELECT uname, uid, hide_email, email from xbb_members WHERE uid='" . $ibforums->input['MID'] . "'");

            $member = $DB->fetch_row();

            //----------------------------------

            if (!$member['uid']) {
                $std->Error(['LEVEL' => 1, 'MSG' => 'no_such_user']);
            }

            if (0 == $member['user_viewemail']) {
                $std->Error(['LEVEL' => 1, 'MSG' => 'private_email']);
            }

            //----------------------------------

            $this->output = $ibforums->vars['use_mail_form'] ? $this->html->send_form(
                [
                    'NAME' => $member['uname'],
'TO' => $member['uid'],
                ]
            ) : $this->html->show_address(
                [
                    'NAME' => $member['uname'],
'ADDRESS' => $member['email'],
                ]
            );

            $this->page_title = $ibforums->lang['member_address_title'];

            $this->nav = [$ibforums->lang['member_address_title']];
        }
    }
}

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
|   > Messenger functions
|   > Module written by Matt Mecham
|   > Date started: 26th February 2002
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

$idx = new Messenger();

class Messenger
{
    public $output = '';

    public $page_title = '';

    public $nav = [];

    public $html = '';

    public $email = '';

    public $msg_stats = [];

    public $prefs = '';

    public $member = [];

    public $m_group = [];

    public $to_mem = [];

    public $jump_html = '';

    public $vid = 'in';

    public $mem_groups = [];

    public $mem_titles = [];

    public $parser = '';

    public $cp_html = '';

    public function __construct()
    {
        global $ibforums, $DB, $std, $print;

        //--------------------------------------------

        // Make sure our code number is numerical only

        //--------------------------------------------

        if ('' == $ibforums->input['CODE']) {
            $ibforums->input['CODE'] = 00;
        }

        //--------------------------------------------

        // Require the HTML and language modules

        //--------------------------------------------

        $ibforums->lang = $std->load_words($ibforums->lang, 'lang_msg', $ibforums->lang_id);

        $ibforums->lang = $std->load_words($ibforums->lang, 'lang_ucp', $ibforums->lang_id);

        //--------------------------------------------

        $this->html = $std->load_template('skin_msg');

        //--------------------------------------------

        $this->cp_html = $std->load_template('skin_ucp');

        //--------------------------------------------

        $this->base_url = "{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}";

        $this->base_url_nosess = "{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}";

        //--------------------------------------------

        // Check viewing permissions, etc

        //--------------------------------------------

        $this->member = $ibforums->member;

        if (empty($this->member['g_use_pm'])) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_use_messenger']);
        }

        if (empty($this->member['uid'])) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_guests']);
        }

        //--------------------------------------------

        // Get the member stats data

        //--------------------------------------------

        $DB->query("SELECT vdirs, msg_total, new_msg, msg_msg_id FROM xbb_members WHERE uid='" . $this->member['uid'] . "'");

        $this->msg_stats = $DB->fetch_row();

        //--------------------------------------------

        // Do a little set up, do a litle dance, get

        // down tonight! *boogie*

        //--------------------------------------------

        $this->jump_html = "<select name='VID' class='forminput'>\n";

        $this->msg_stats['dir_data'] = [];

        // Do we have VID?

        // No, it's just the way we walk! Haha, etc.

        if ($ibforums->input['VID']) {
            $this->vid = $ibforums->input['VID'];
        }

        if (empty($this->msg_stats['vdirs'])) {
            $this->msg_stats['vdirs'] = 'in:Inbox|sent:Sent Items';
        }

        foreach (explode('|', $this->msg_stats['vdirs']) as $dir) {
            [$id, $real] = explode(':', $dir);

            if (empty($id)) {
                continue;
            }

            $this->msg_stats['dir_data'][] = ['id' => $id, 'real' => $real];

            if ($this->vid == $id) {
                $this->msg_stats['current_dir'] = $real;

                $this->msg_stats['current_id'] = $id;

                $this->jump_html .= "<option value='$id' selected>$real</option>\n";
            } else {
                $this->jump_html .= "<option value='$id'>$real</option>\n";
            }
        }

        $this->jump_html .= "<!--EXTRA--></select>\n\n";

        $print->add_output($this->cp_html->Menu_bar($this->base_url));

        //--------------------------------------------

        // What to do?

        //--------------------------------------------

        switch ($ibforums->input['CODE']) {
            case '01':
                $this->msg_list();
                break;
            case '02':
                $this->contact();
                break;
            case '03':
                $this->view_msg();
                break;
            case '04':
                $this->send();
                break;
            case '05':
                $this->delete();
                break;
            case '06':
                $this->multi_act();
                break;
            case '07':
                $this->prefs();
                break;
            case '08':
                $this->do_prefs();
                break;
            case '09':
                $this->add_member();
                break;
            case '10':
                $this->del_member();
                break;
            case '11':
                $this->edit_member();
                break;
            case '12':
                $this->do_edit();
                break;
            case '14':
                $this->archive();
                break;
            case '15':
                $this->do_archive();
                break;
            case '99':
                $this->pm_popup();
                break;
            case '20':
                $this->view_saved();
                break;
            case '21':
                $this->edit_saved();
                break;
            case '30':
                $this->show_tracking();
                break;
            case '31':
                $this->end_tracking();
                break;
            case '32':
                $this->del_tracked();
                break;
            default:
                $this->msg_list();
                break;
        }

        // If we have any HTML to print, do so...

        $fj = $std->build_forum_jump();

        $fj = preg_replace('!#Forum Jump#!', $ibforums->lang['forum_jump'], $fj);

        $this->output .= $this->cp_html->CP_end();

        $this->output .= $this->cp_html->forum_jump($fj);

        $print->add_output((string)$this->output);

        $print->do_output(['TITLE' => $this->page_title, 'JS' => 0, NAV => $this->nav]);
    }

    /**********************************************************/

    // PM Pop up:

    // Simpy display the pop up window

    /**********************************************************/

    public function pm_popup()
    {
        global $DB, $std, $print, $ibforums;

        // Get the last message stuff

        $DB->query(
            'SELECT m.uname, msg.title, msg.msg_date, msg.from_id FROM xbb_members m, ibf_messages msg ' . "WHERE msg.member_id='" . $ibforums->member['uid'] . "' AND msg_id='" . $this->msg_stats['msg_msg_id'] . "' " . 'AND m.uid=msg.from_id'
        );

        $row = $DB->fetch_row();

        // Fix up the text string...

        $row['msg_date'] = $std->get_date($row['msg_date'], 'LONG');

        $text = preg_replace('/<#NAME#>/', $row['uname'], $ibforums->lang['pmp_string']);

        $text = preg_replace('/<#TITLE#>/', $row['title'], $text);

        $text = preg_replace('/<#DATE#>/', $row['msg_date'], $text);

        $html = $this->html->pm_popup($text, $this->msg_stats['msg_msg_id']);

        $print->pop_up_window('PM', $html);
    }

    /**********************************************************/

    // ARCHIVE:

    // Allows a user to archive and email a HTML file

    /**********************************************************/

    public function archive()
    {
        global $ibforums, $DB, $std, $print;

        $this->jump_html = preg_replace('/<!--EXTRA-->/', "<option value='all'>" . $ibforums->lang['all_folders'] . '</option>', $this->jump_html);

        $this->output .= $this->html->archive_form($this->jump_html);

        $this->page_title = $ibforums->lang['t_welcome'];

        $this->nav = ["<a href='" . $this->base_url . "&act=UserCP&CODE=00'>" . $ibforums->lang['t_title'] . '</a>'];
    }

    public function do_archive()
    {
        global $ibforums, $DB, $std, $print;

        require './sources/lib/emailer.php';

        $this->email = new emailer();

        //----------------------------------------

        // Did we specify a folder, or choose all?

        //----------------------------------------

        $folder_query = '';

        $msg_ids = [];

        if ('all' != $ibforums->input['VID']) {
            $folder_query = " AND vid='" . $ibforums->input['VID'] . "'";
        }

        if ('all' == $ibforums->input['dateline']) {
            $time_cut = 0;
        } else {
            $time_cut = time() - ($ibforums->input['dateline'] * 60 * 60 * 24);
        }

        //----------------------------------------

        // Check the input...

        //----------------------------------------

        $ibforums->input['number'] = preg_replace("/^(\d+)$/", '\\1', $ibforums->input['number']);

        if ($ibforums->input['number'] < 5) {
            $ibforums->input['number'] = 5;
        }

        if ($ibforums->input['number'] > 50) {
            $ibforums->input['number'] = 50;
        }

        $type = 'html';

        $file_name = 'pm_archive.html';

        $ctype = 'text/html';

        if ('xls' == $ibforums->input['type']) {
            $type = 'xls';

            $file_name = 'xls_importable.txt';

            $ctype = 'text/plain';
        }

        $output = '';

        //----------------------------------------

        // Start the datafile..

        //----------------------------------------

        if ('html' == $type) {
            $output .= $this->html->archive_html_header();
        }

        require './sources/lib/post_parser.php';

        $this->parser = new post_parser();

        //----------------------------------------

        // Get the messages...

        //----------------------------------------

        $archive_query = $DB->query("SELECT mg.*, m.uname, m.uid from ibf_messages mg, xbb_members m WHERE mg.member_id=m.uid AND mg.member_id='" . $this->member['uid'] . "' AND mg.msg_date > $time_cut" . $folder_query . ' ORDER BY mg.msg_date LIMIT 0,' . $ibforums->input['number']);

        if ($DB->get_num_rows($archive_query)) {
            while (false !== ($r = $DB->fetch_row($archive_query))) {
                $info = [];

                $msg_ids[] = $r['msg_id'];

                $from_member = $DB->query("SELECT uid, uname FROM xbb_members WHERE uid='" . $r['from_id'] . "'");

                $from_mem = $DB->fetch_row($from_member);

                $info['msg_date'] = $std->get_date($r['msg_date'], 'LONG');

                $info['msg_title'] = $r['title'];

                $info['msg_sender'] = $from_mem['uname'];

                $info['msg_content'] = $this->parser->convert(
                    [
                        'TEXT' => $r['message'],
'SMILIES' => 0,
'CODE' => $ibforums->vars['msg_allow_code'],
'HTML' => $ibforums->vars['msg_allow_html'],
                    ]
                );

                if ('xls' == $type) {
                    $output .= '"' . $this->strip_quotes($info['msg_title']) . '","' . $this->strip_quotes($info['msg_date']) . '","' . $this->strip_quotes($info['msg_sender']) . '","' . $this->strip_quotes($info['msg_content']) . '"' . "\r";
                } else {
                    $output .= $this->html->archive_html_entry($info);
                }
            }

            if ('html' == $type) {
                $output .= $this->html->archive_html_footer();
            }

            $num_msg = count($msg_ids);

            if ('yes' == $ibforums->input['delete']) {
                $msg_str = implode(',', $msg_ids);

                if (!empty($msg_str)) {
                    $DB->query("DELETE FROM ibf_messages WHERE msg_id IN ($msg_str)");

                    $DB->query("UPDATE xbb_members SET msg_total=msg_total-$num_msg WHERE uid ='" . $this->member['uid'] . "'");
                }
            }

            $this->email->get_template('pm_archive');

            $this->email->build_message(
                [
                    'NAME' => $this->member['uname'],
                ]
            );

            $this->email->subject = $ibforums->lang['arc_email_subject'];

            $this->email->to = $this->member['email'];

            $this->email->add_attachment($output, $file_name, $ctype);

            $this->email->send_mail();

            $ibforums->lang['arc_complete'] = preg_replace('/<#NUM#>/', (string)$num_msg, $ibforums->lang['arc_complete']);

            $this->output .= $this->html->archive_complete();

            $this->page_title = $ibforums->lang['t_welcome'];

            $this->nav = ["<a href='" . $this->base_url . "&act=UserCP&CODE=00'>" . $ibforums->lang['t_title'] . '</a>'];
        } else {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_archive_messages']);
        }
    }

    public function strip_quotes($text)
    {
        return preg_replace('/"/', '\\\"', $text);
    }

    /**********************************************************/

    // PREFS:

    // Create/delete/edit messenger folders

    /**********************************************************/

    public function prefs()
    {
        global $ibforums, $DB, $std, $print;

        $this->output .= $this->html->prefs_header();

        $count = 0;

        foreach ($this->msg_stats['dir_data'] as $k => $v) {
            $extra = '';

            if ('in' == $v['id'] or 'sent' == $v['id']) {
                $extra = '&nbsp;&nbsp;( ' . $v['real'] . ' - ' . $ibforums->lang['cannot_remove'] . ' )';
            }

            $this->output .= $this->html->prefs_row(['ID' => $v['id'], 'REAL' => $v['real'], 'EXTRA' => $extra]);

            $count++;
        }

        $this->output .= $this->html->prefs_add_dirs();

        for ($i = $count; $i < $count + 3; $i++) {
            $this->output .= $this->html->prefs_row(['ID' => 'dir_' . $i, 'REAL' => '', 'EXTRA' => '']);
        }

        $this->output .= $this->html->prefs_footer();

        $this->page_title = $ibforums->lang['t_welcome'];

        $this->nav = ["<a href='" . $this->base_url . "&act=UserCP&CODE=00'>" . $ibforums->lang['t_title'] . '</a>'];
    }

    public function do_prefs()
    {
        global $ibforums, $DB, $std, $print;

        // Check to ensure than we've not tried to remove the inbox and sent items directories.

        if (('' == $ibforums->input['sent']) or ('' == $ibforums->input['in'])) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'cannot_remove_dir']);
        }

        $v_dir = 'in:' . $ibforums->input['in'] . '|sent:' . $ibforums->input['sent'];

        // Fetch the rest of the dirs

        $ids = [];

        foreach ($ibforums->input as $key => $value) {
            if (preg_match("/^dir_(\d+)$/", $key, $match)) {
                if ($ibforums->input[$match[0]]) {
                    $v_dir .= '|' . $match[0] . ':' . trim($ibforums->input[$match[0]]);
                }
            }
        }

        $DB->query("UPDATE xbb_members SET vdirs='$v_dir' WHERE uid='" . $this->member['uid'] . "'");

        $std->boink_it($ibforums->base_url . '&act=Msg&CODE=07');

        exit;
    }

    /**********************************************************/

    // DELETE_MEMBER:

    // Removes a member from address book.

    /**********************************************************/

    public function del_member()
    {
        global $ibforums, $DB, $std, $print;

        if (!$ibforums->input['MID']) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_user']);
        }

        if (!preg_match("/^(\d+)$/", $ibforums->input['MID'])) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_user']);
        }

        $DB->query("DELETE FROM ibf_contacts WHERE member_id='" . $this->member['uid'] . "' AND contact_id='" . $ibforums->input['MID'] . "'");

        $std->boink_it($this->base_url . '&act=Msg&CODE=02');

        exit;
    }

    /**********************************************************/

    // EDIT_MEMBER:

    // Edit a member from address book.

    /**********************************************************/

    public function edit_member()
    {
        global $ibforums, $DB, $std, $print;

        if (!$ibforums->input['MID']) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_user']);
        }

        if (!preg_match("/^(\d+)$/", $ibforums->input['MID'])) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_user']);
        }

        $DB->query("SELECT * FROM ibf_contacts WHERE member_id='" . $this->member['uid'] . "' AND contact_id='" . $ibforums->input['MID'] . "'");

        $memb = $DB->fetch_row();

        if (!$memb['contact_id']) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_user']);
        }

        $html = "<select name='allow_msg' class='forminput'>";

        if ($memb['allow_msg']) {
            $html .= "<option value='yes' selected>{$ibforums->lang['yes']}</option><option value='no'>{$ibforums->lang['no']}";
        } else {
            $html .= "<option value='yes'>{$ibforums->lang['yes']}</option><option value='no' selected>{$ibforums->lang['no']}";
        }

        $html .= '</select>';

        $this->output .= $this->html->address_edit(['SELECT' => $html, 'MEMBER' => $memb]);

        $this->page_title = $ibforums->lang['t_welcome'];

        $this->nav = [
            "<a href='" . $this->base_url . "&act=UserCP&CODE=00'>" . $ibforums->lang['t_title'] . '</a>',
            "<a href='" . $this->base_url . "&act=Msg&CODE=02'>" . $ibforums->lang['t_book'] . '</a>',
        ];
    }

    /**********************************************************/

    // DO_EDIT_MEMBER:

    // Edit a member from address book.

    /**********************************************************/

    public function do_edit()
    {
        global $ibforums, $DB, $std, $print;

        if (!$ibforums->input['MID']) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_user']);
        }

        if (!preg_match("/^(\d+)$/", $ibforums->input['MID'])) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_user']);
        }

        $ibforums->input['allow_msg'] = 'yes' == $ibforums->input['allow_msg'] ? 1 : 0;

        $DB->query("SELECT * FROM ibf_contacts WHERE member_id='" . $this->member['uid'] . "' AND contact_id='" . $ibforums->input['MID'] . "'");

        $memb = $DB->fetch_row();

        if (!$memb['contact_id']) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_user']);
        }

        $DB->query("UPDATE ibf_contacts SET contact_desc='" . $ibforums->input['mem_desc'] . "', allow_msg='" . $ibforums->input['allow_msg'] . "' WHERE id='" . $memb['id'] . "'");

        $std->boink_it($this->base_url . '&act=Msg&CODE=02');

        exit;
    }

    /**********************************************************/

    // CONTACT:

    // Shows the address book.

    /**********************************************************/

    public function contact()
    {
        global $ibforums, $DB, $std, $print;

        $this->output .= $this->html->Address_header();

        $DB->query("SELECT * FROM ibf_contacts WHERE member_id='" . $this->member['uid'] . "' ORDER BY contact_name ASC");

        if ($DB->get_num_rows()) {
            $this->output .= $this->html->Address_table_header();

            while (false !== ($row = $DB->fetch_row())) {
                $row['text'] = $row['allow_msg'] ? $ibforums->lang['can_contact'] : $ibforums->lang['cannot_contact'];

                $this->output .= $this->html->render_address_row($row);
            }

            $this->output .= $this->html->end_address_table();
        } else {
            $this->output .= $this->html->Address_none();
        }

        // Do we have a name to enter?

        $name_to_enter = '';

        if ($ibforums->input['MID']) {
            if (preg_match("/^(\d+)$/", $ibforums->input['MID'])) {
                $DB->query("SELECT uname, uid FROM xbb_members WHERE uid='" . $ibforums->input['MID'] . "'");

                $memb = $DB->fetch_row();

                if ($memb['uid']) {
                    $name_to_enter = $memb['uname'];
                }
            }
        }

        $this->output .= $this->html->address_add($name_to_enter);

        $this->page_title = $ibforums->lang['t_welcome'];

        $this->nav = ["<a href='" . $this->base_url . "&act=UserCP&CODE=00'>" . $ibforums->lang['t_title'] . '</a>'];
    }

    /**********************************************************/

    // ADD MEMBER:

    // Adds a member to the addy book.

    /**********************************************************/

    public function add_member()
    {
        global $ibforums, $DB, $std, $print;

        if (!$ibforums->input['mem_name']) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_user']);
        }

        $DB->query("SELECT uname, uid FROM xbb_members WHERE LOWER(uname)='" . $ibforums->input['mem_name'] . "'");

        $memb = $DB->fetch_row();

        if (!$memb['uid']) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_user']);
        }

        //--------------------------------------

        // Do we already have this member in our

        // address book?

        //--------------------------------------

        $DB->query("SELECT contact_id FROM ibf_contacts WHERE member_id='" . $this->member['uid'] . "' AND contact_id='" . $memb['uid'] . "'");

        if ($DB->get_num_rows()) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'member_in_add_book']);
        }

        //--------------------------------------

        // Insert it into the DB

        //--------------------------------------

        $ibforums->input['allow_msg'] = 'yes' == $ibforums->input['allow_msg'] ? 1 : 0;

        $db_string = $std->compile_db_string(
            [
                'member_id' => $this->member['uid'],
'contact_name' => $memb['uname'],
'allow_msg' => $ibforums->input['allow_msg'],
'contact_desc' => $ibforums->input['mem_desc'],
'contact_id' => $memb['uid'],
            ]
        );

        $DB->query('INSERT INTO ibf_contacts (' . $db_string['FIELD_NAMES'] . ') VALUES (' . $db_string['FIELD_VALUES'] . ')');

        unset($db_string);

        // BUH BYE!

        $std->boink_it($this->base_url . '&act=Msg&CODE=02');

        exit;
    }

    /********************************************************************************************************************/

    // Mutli Act:

    // Removes or moves messages.

    /**********************************************************/

    public function multi_act()
    {
        global $ibforums, $DB, $std, $print;

        //--------------------------------------

        // Get the ID's to delete

        //--------------------------------------

        $ids = [];

        foreach ($ibforums->input as $key => $value) {
            if (preg_match("/^msgid_(\d+)$/", $key, $match)) {
                if ($ibforums->input[$match[0]]) {
                    $ids[] = $match[1];
                }
            }
        }

        $affected_ids = count($ids);

        if ($affected_ids > 0) {
            $id_string = implode(',', $ids);

            if ($ibforums->input['delete']) {
                $DB->query("DELETE FROM ibf_messages WHERE member_id='" . $this->member['uid'] . "' AND msg_id IN ($id_string)");

                if ($ibforums->input['saved']) {
                    // Did we delete from the saved folder? If so, don't update the msg stats and

                    // redirect back to the saved folder.

                    $std->boink_it($this->base_url . '&act=Msg&CODE=20');

                    exit;
                }

                $DB->query("UPDATE xbb_members SET msg_total=msg_total-$affected_ids WHERE uid='" . $this->member['uid'] . "'");

                $std->boink_it($this->base_url . "&act=Msg&CODE=01&VID={$this->vid}");

                exit;
            } elseif ($ibforums->input['move']) {
                $DB->query("UPDATE ibf_messages SET vid='" . $this->vid . "' WHERE member_id='" . $this->member['uid'] . "' AND msg_id IN ($id_string)");

                $std->boink_it($this->base_url . "&act=Msg&CODE=01&VID={$this->vid}");

                exit;
            }

            $std->Error(['LEVEL' => 1, 'MSG' => 'no_msg_chosen']);
        } else {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_msg_chosen']);
        }
    }

    /**********************************************************/

    // END TRACKING

    // Removes read tracked messages

    /**********************************************************/

    public function end_tracking()
    {
        global $ibforums, $DB, $std, $print;

        //--------------------------------------

        // Get the ID's to delete

        //--------------------------------------

        $ids = [];

        foreach ($ibforums->input as $key => $value) {
            if (preg_match("/^msgid_(\d+)$/", $key, $match)) {
                if ($ibforums->input[$match[0]]) {
                    $ids[] = $match[1];
                }
            }
        }

        $affected_ids = count($ids);

        if ($affected_ids > 0) {
            $id_string = implode(',', $ids);

            $DB->query("UPDATE ibf_messages SET tracking=0 WHERE tracking=1 AND read_state=1 AND from_id='" . $this->member['uid'] . "' AND msg_id IN ($id_string)");

            $std->boink_it($this->base_url . '&act=Msg&CODE=30');
        } else {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_msg_chosen']);
        }
    }

    public function del_tracked()
    {
        global $ibforums, $DB, $std, $print;

        //--------------------------------------

        // Get the ID's to delete

        //--------------------------------------

        $ids = [];

        foreach ($ibforums->input as $key => $value) {
            if (preg_match("/^msgid_(\d+)$/", $key, $match)) {
                if ($ibforums->input[$match[0]]) {
                    $ids[] = $match[1];
                }
            }
        }

        $affected_ids = count($ids);

        if ($affected_ids > 0) {
            $id_string = implode(',', $ids);

            $DB->query("DELETE FROM ibf_messages WHERE tracking=1 AND read_state=0 AND from_id='" . $this->member['uid'] . "' AND msg_id IN ($id_string)");

            $std->boink_it($this->base_url . '&act=Msg&CODE=30');
        } else {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_msg_chosen']);
        }
    }

    /**********************************************************/

    // DELETE MESSAGE:

    // Removes a message.

    // Yes. there is no small print.

    /**********************************************************/

    public function delete()
    {
        global $ibforums, $DB, $std, $print;

        //--------------------------------------

        // check for a msg ID

        //--------------------------------------

        if (!$ibforums->input['MSID']) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_msg_chosen']);
        }

        if (!preg_match("/^\d+$/", $ibforums->input['MSID'])) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'invalid_use']);
        }

        //--------------------------------------

        // Delete it from the DB

        //--------------------------------------

        $DB->query("DELETE FROM ibf_messages WHERE msg_id='" . $ibforums->input['MSID'] . "' AND member_id='" . $this->member['uid'] . "'");

        $DB->query("UPDATE xbb_members SET msg_total=msg_total-1 WHERE uid='" . $this->member['uid'] . "'");

        // BYE!

        $std->boink_it($this->base_url . "&act=Msg&CODE=01&VID={$this->vid}");

        exit;
    }

    /**********************************************************/

    // VIEW MESSAGE:

    // Views a message, thats it. No, it doesn't do anything else

    // I don't know why. It just does. Accept it and move on dude.

    /**********************************************************/

    public function view_msg()
    {
        global $ibforums, $DB, $std, $print;

        //--------------------------------------

        // check for a msg ID

        //--------------------------------------

        if (!$ibforums->input['MSID']) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_such_msg']);
        }

        if (!preg_match("/^\d+$/", $ibforums->input['MSID'])) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'invalid_use']);
        }

        //--------------------------------------

        // Get the message from the DB

        // Check to make sure it exists

        //--------------------------------------

        $DB->query("SELECT * FROM ibf_messages WHERE msg_id='" . $ibforums->input['MSID'] . "' and member_id='" . $this->member['uid'] . "'");

        $msg = $DB->fetch_row();

        if (!$msg['msg_id']) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_such_msg']);
        }

        //--------------------------------------

        // Did we read this in the pop up?

        // If so, reduce new count by 1 (this msg)

        // 'cos if we went via inbox, we'd have

        // no new msg

        //--------------------------------------

        if ($ibforums->member['new_msg'] >= 1) {
            $DB->query("UPDATE xbb_members SET new_msg=new_msg-1 WHERE uid='" . $this->member['uid'] . "'");
        }

        //--------------------------------------

        // Is this an unread message?

        //--------------------------------------

        if ($msg['read_state'] < 1) {
            $DB->query("UPDATE ibf_messages SET read_state='1', read_date='" . time() . "' WHERE msg_id='" . $ibforums->input['MSID'] . "'");
        }

        //--------------------------------------

        // Start formatting the member and msg

        //--------------------------------------

        require './sources/lib/post_parser.php';

        $this->parser = new post_parser();

        $msg['msg_date'] = $std->get_date($msg['msg_date'], 'LONG');

        $DB->query(
            'SELECT g.g_title as member_group, m.uid, m.joined, m.uname, m.title, m.posts, m.mgroup, m.email, m.signature, m.avatar, m.avatar_size, m.hide_email, m.aim_name, m.icq_number ' . "FROM xbb_members m, ibf_groups g WHERE uid='" . $msg['from_id'] . "' and g.g_id=m.mgroup"
        );

        $member = $DB->fetch_row();

        $member = $this->parse_member($member, $msg);

        $msg['message'] = $this->parser->convert(
            [
                TEXT => $msg['message'],
SMILIES => 1,
CODE => $ibforums->vars['msg_allow_code'],
HTML => $ibforums->vars['msg_allow_html'],
            ]
        );

        if (!$ibforums->vars[SIG_SEP]) {
            $ibforums->vars[SIG_SEP] = '<br><br>--------------------<br>';
        }

        if ($this->member['attachsig']) {
            $member['signature'] = $ibforums->vars[SIG_SEP] . $this->parser->convert(
                [
                        TEXT => $member['signature'],
SMILIES => 0,
CODE => $ibforums->vars['sig_allow_ibc'],
HTML => $ibforums->vars['sig_allow_html'],
SIGNATURE => 1,
                    ]
            );
        } else {
            $member['signature'] = '';
        }

        $member['VID'] = $this->msg_stats['current_id'];

        $this->output .= $this->html->Render_msg(
            [
                'msg' => $msg,
'member' => $member,
'jump' => $this->jump_html,
            ]
        );

        $this->page_title = $ibforums->lang['t_welcome'];

        $this->nav = [
            "<a href='" . $this->base_url . "&act=UserCP&CODE=00'>" . $ibforums->lang['t_title'] . '</a>',
            "<a href='" . $this->base_url . "&act=Msg&CODE=01&VID={$member['VID']}'>" . $this->msg_stats['current_dir'] . '</a>',
        ];
    }

    /**********************************************************/

    // SEND MESSAGE:

    // Sends a message. Yes, it's that simple. Why so much code?

    // Because typing "send a message to member X" doesnt actually

    // do anything.

    /**********************************************************/

    public function send()
    {
        global $ibforums;

        $ibforums->input['MODE'] ? $this->send_msg() : $this->send_form();
    }

    //+-----------------------------------------------------------

    public function send_form($preview = 0, $errors = '')
    {
        global $ibforums, $DB, $std, $print, $_POST;

        //--------------------------------------

        // Get some more HTML and words, oh yes.

        //--------------------------------------

        $errors = preg_replace('/^<br>/', '', $errors);

        $ibforums->lang = $std->load_words($ibforums->lang, 'lang_post', $ibforums->lang_id);

        $this->post_html = $std->load_template('skin_post');

        if (1 == $preview) {
            require './sources/lib/post_parser.php';

            $this->parser = new post_parser();

            $old_msg = $this->parser->convert(
                [
                    TEXT => $std->remove_tags($ibforums->input['Post']),
SMILIES => 1,
CODE => $ibforums->vars['msg_allow_code'],
HTML => $ibforums->vars['msg_allow_html'],
                ]
            );

            $this->output .= $this->html->preview($old_msg);
        }

        if ('' != $errors) {
            $this->output .= $this->html->pm_errors($errors);

            $preview = 1;
        }

        //--------------------------------------

        // Load the contacts

        //--------------------------------------

        $contacts = $this->build_contact_list();

        $name_to_enter = '';

        $old_message = '';

        $old_title = '';

        //--------------------------------------

        // Did we come from a button with a user ID?

        //--------------------------------------

        if (!empty($ibforums->input['MID'])) {
            $DB->query("SELECT uname, uid FROM xbb_members WHERE uid='" . $ibforums->input['MID'] . "'");

            $name = $DB->fetch_row();

            if (1 != $ibforums->input['fwd']) {
                if ($name['uid']) {
                    $name_to_enter = $name['uname'];
                }
            }
        }

        //--------------------------------------

        // Are we quoting an old message?

        //--------------------------------------

        if (1 == $preview) {
            $old_message = htmlspecialchars(stripslashes($_POST['Post']), ENT_QUOTES | ENT_HTML5);

            $old_title = preg_replace("/'/", '&#39;', stripslashes($_POST['msg_title']));
        } elseif (!empty($ibforums->input['MSID'])) {
            $DB->query("SELECT message, title from ibf_messages WHERE msg_id='" . $ibforums->input['MSID'] . "' and member_id='" . $this->member['uid'] . "'");

            $old_msg = $DB->fetch_row();

            if ($old_msg['title']) {
                if (1 == $ibforums->input['fwd']) {
                    $old_title = 'Fwd:' . $old_msg['title'];

                    $old_title = preg_replace("/^(?:Fwd\:){1,}/i", 'Fwd:', $old_title);

                    $old_message = '[QUOTE]' . sprintf($ibforums->lang['vm_forward_text'], $name['uname']) . "\n\n" . $old_msg['message'] . '[/QUOTE]' . "\n";

                    $old_message = str_replace('<br>', "\n", $old_message);
                } else {
                    $old_title = 'Re:' . $old_msg['title'];

                    $old_title = preg_replace("/^(?:Re\:){1,}/i", 'Re:', $old_title);

                    $old_message = '[QUOTE]' . $old_msg['message'] . '[/QUOTE]' . "\n";

                    $old_message = str_replace('<br>', "\n", $old_message);
                }
            }
        }

        //--------------------------------------

        // Build up the HTML for the send form

        //--------------------------------------

        $this->output .= $this->html->Send_form(
            [
                'CONTACTS' => $contacts,
'MEMBER' => $this->member,
'N_ENTER' => $name_to_enter,
'O_TITLE' => $old_title,
'OID' => $ibforums->input['OID'], // Old unsent msg id for restoring saved msg - used to delete saved when sent
            ]
        );

        $ibforums->lang['the_max_length'] = $ibforums->vars['max_post_length'] * 1024;

        $this->output .= $this->post_html->pm_postbox_buttons($old_message);

        $this->output .= $this->html->send_form_footer();

        //--------------------------------------

        // Add in the smilies box

        //--------------------------------------

        $this->html_add_smilie_box();

        $this->page_title = $ibforums->lang['t_welcome'];

        $this->nav = ["<a href='" . $this->base_url . "&act=UserCP&CODE=00'>" . $ibforums->lang['t_title'] . '</a>'];

        //----------------------------------------

        // Do we have permission to mass PM peeps?

        //----------------------------------------

        if ($ibforums->member['g_max_mass_pm'] > 0) {
            $ibforums->lang['carbon_copy_desc'] = sprintf($ibforums->lang['carbon_copy_desc'], $ibforums->member['g_max_mass_pm']);

            if (isset($_POST['carbon_copy'])) {
                $cc_box = preg_replace('#</textarea>#i', '', stripslashes($_POST['carbon_copy']));
            }

            $this->output = str_replace('<!--IBF.MASS_PM_BOX-->', $this->html->mass_pm_box($cc_box), $this->output);
        }
    }

    //+-----------------------------------------------------------

    public function edit_saved()
    {
        global $ibforums, $DB, $std, $print;

        //--------------------------------------

        // Get some more HTML and words, oh yes.

        //--------------------------------------

        $errors = preg_replace('/^<br>/', '', $errors);

        $ibforums->lang = $std->load_words($ibforums->lang, 'lang_post', $ibforums->lang_id);

        $this->post_html = $std->load_template('skin_post');

        //--------------------------------------

        // Load the contacts

        //--------------------------------------

        $contacts = $this->build_contact_list();

        $DB->query("SELECT mg.*, m.uname as to_name, m.uid as to_id from ibf_messages mg, xbb_members m WHERE msg_id='" . $ibforums->input['MSID'] . "' and member_id='" . $this->member['uid'] . "' AND m.uid=mg.recipient_id");

        $msg = $DB->fetch_row();

        if (!$msg['msg_id']) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_such_msg']);
        }

        //--------------------------------------

        // Build up the HTML for the send form

        //--------------------------------------

        $this->output .= $this->html->Send_form(
            [
                'CONTACTS' => $contacts,
'MEMBER' => $this->member,
'N_ENTER' => $msg['to_name'],
'O_TITLE' => $msg['title'],
'OID' => $msg['msg_id'],
            ]
        );

        $ibforums->lang['the_max_length'] = $ibforums->vars['max_post_length'] * 1024;

        $this->output .= $this->post_html->postbox_buttons(str_replace('<br>', "\n", $msg['message']));

        $this->output .= $this->html->send_form_footer();

        //--------------------------------------

        // Add in the smilies box

        //--------------------------------------

        $this->html_add_smilie_box();

        $this->page_title = $ibforums->lang['t_welcome'];

        $this->nav = ["<a href='" . $this->base_url . "&act=UserCP&CODE=00'>" . $ibforums->lang['t_title'] . '</a>'];

        //----------------------------------------

        // Do we have permission to mass PM peeps?

        //----------------------------------------

        if ($ibforums->member['g_max_mass_pm'] > 0) {
            $ibforums->lang['carbon_copy_desc'] = sprintf($ibforums->lang['carbon_copy_desc'], $ibforums->member['g_max_mass_pm']);

            if (isset($msg['cc_users'])) {
                $cc_box = preg_replace('#</textarea>#i', '', $msg['cc_users']);

                $cc_box = str_replace('<br>', "\n", $cc_box);
            }

            $this->output = str_replace('<!--IBF.MASS_PM_BOX-->', $this->html->mass_pm_box($cc_box), $this->output);
        }
    }

    //+-----------------------------------------------------------

    public function send_msg()
    {
        global $ibforums, $DB, $std, $print;

        $ibforums->lang = $std->load_words($ibforums->lang, 'lang_error', $ibforums->lang_id);

        $ibforums->input['from_contact'] = $ibforums->input['from_contact'] ?: '-';

        //----------------------------------------------------------------

        if (mb_strlen($ibforums->input['msg_title']) < 2) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_msg_title']);
        }

        //----------------------------------------------------------------

        if (mb_strlen($ibforums->input['Post']) < 2) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_msg']);
        }

        //----------------------------------------------------------------

        if ('-' == $ibforums->input['from_contact'] and '' == $ibforums->input['entered_name']) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_chosen_member']);
        }

        //----------------------------------------------------------------

        require './sources/lib/emailer.php';

        $this->email = new emailer();

        //--------------------------------------

        // Attempt to get the reciepient details

        //--------------------------------------

        $to_member = [];

        if ('-' == $ibforums->input['from_contact']) {
            $query = "LOWER(uname)='" . $ibforums->input['entered_name'] . "'";
        } else {
            $query = "uid='" . $ibforums->input['from_contact'] . "'";
        }

        $DB->query('SELECT uname, uid, view_pop, mgroup, email_pm, language, email FROM xbb_members WHERE ' . $query);

        $to_member = $DB->fetch_row();

        if (empty($to_member['uid'])) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_to_member']);
        }

        //--------------------------------------

        // Are we simply saving this for later?

        //--------------------------------------

        if ('' != $ibforums->input['save']) {
            $raw = [
                'member_id' => $this->member['uid'],
'msg_date' => time(),
'read_state' => 0,
'title' => $ibforums->input['msg_title'],
'message' => $ibforums->input['Post'],
'from_id' => $this->member['uid'],
'vid' => 'unsent',
'recipient_id' => $to_member['uid'],
'cc_users' => $ibforums->input['carbon_copy'],
            ];

            $saved = 0;

            if ($ibforums->input['OID']) {
                // We have an OID which means that this message

                // is already from the unsent folder, lets check that

                // and if true, update rather than create a new unsent

                // row

                $DB->query("SELECT msg_id from ibf_messages WHERE msg_id='" . $ibforums->input['OID'] . "' AND member_id='" . $ibforums->member['uid'] . "' AND vid='unsent'");

                if ($DB->get_num_rows()) {
                    $saved = 1;

                    $db_string = $DB->compile_db_update_string($raw);

                    $DB->query("UPDATE ibf_messages SET $db_string WHERE msg_id='" . $ibforums->input['OID'] . "'");
                }
            }

            if (0 == $saved) {
                $db_string = $DB->compile_db_insert_string($raw);

                $DB->query('INSERT INTO ibf_messages (' . $db_string['FIELD_NAMES'] . ') VALUES (' . $db_string['FIELD_VALUES'] . ')');
            }

            $print->redirect_screen($ibforums->lang['pms_redirect'], '&act=Msg&CODE=01');
        }

        //--------------------------------------

        // Can the reciepient use the PM system?

        //--------------------------------------

        $DB->query("SELECT m.msg_total, g.g_use_pm, g.g_max_messages FROM ibf_groups g, xbb_members m WHERE m.uid='" . $to_member['uid'] . "' AND g.g_id=m.mgroup");

        $to_msg_stats = $DB->fetch_row();

        if (1 != $to_msg_stats['g_use_pm']) {
            $ibforums->input['MID'] = $to_member['uid'];

            $this->send_form(0, $ibforums->lang['no_usepm_member']);

            return;
        }

        //--------------------------------------

        // Does the target member have enough room

        // in their inbox for a new message?

        //--------------------------------------

        if ((($to_msg_stats['msg_total']) >= $to_msg_stats['g_max_messages']) and ($to_msg_stats['g_max_messages'] > 0)) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'max_message_to']);
        }

        //--------------------------------------

        // Has the reciepient blocked us?

        //--------------------------------------

        $DB->query("SELECT contact_id, allow_msg FROM ibf_contacts WHERE contact_id='" . $this->member['uid'] . "' AND member_id='" . $to_member['uid'] . "'");

        $can_msg = $DB->fetch_row();

        if ((isset($can_msg['contact_id'])) and (1 != $can_msg['allow_msg'])) {
            $ibforums->input['MID'] = $to_member['uid'];

            $this->send_form(0, $ibforums->lang['msg_blocked']);

            return;
        }

        //--------------------------------------

        // Do we have enough room to store a

        // saved copy?

        //--------------------------------------

        if ($ibforums->input['add_sent'] and ($ibforums->member['g_max_messages'] > 0)) {
            if ($ibforums->member['g_max_messages'] <= ($this->msg_stats['msg_total'] + 1)) {
                $std->Error(['LEVEL' => 1, 'MSG' => 'max_message_from']);
            }
        }

        //----------------------------------------------------------------

        // Mass PM stuff

        //----------------------------------------------------------------

        $can_mass_pm = 0;

        $cc_array = [];

        if ($ibforums->member['g_max_mass_pm'] > 0) {
            $can_mass_pm = 1;

            if (isset($ibforums->input['carbon_copy']) and '' != $ibforums->input['carbon_copy']) {
                // Sort out the array

                $ibforums->input['carbon_copy'] = str_replace('<br><br>', '<br>', trim($ibforums->input['carbon_copy']));

                $ibforums->input['carbon_copy'] = preg_replace('/^(<br>){1}/', '', $ibforums->input['carbon_copy']);

                $ibforums->input['carbon_copy'] = preg_replace('/(<br>){1}$/', '', $ibforums->input['carbon_copy']);

                $ibforums->input['carbon_copy'] = preg_replace("/<br>\s+/", ',', $ibforums->input['carbon_copy']);

                $temp_array = explode('<br>', $ibforums->input['carbon_copy']);

                if (is_array($temp_array) and count($temp_array) > 0) {
                    $new_array = [];

                    foreach ($temp_array as $name) {
                        $name = "'" . trim(mb_strtolower($name)) . "'";

                        if (in_array($name, $new_array, true)) {
                            continue;
                        }

                        $new_array[] = $name;
                    }
                }

                if (is_array($new_array) and count($new_array) > 0) {
                    $array_count = count($new_array);

                    $DB->query(
                        'SELECT m.uid, m.uname, m.msg_total, m.view_pop, m.email_pm, m.language, m.email, g.g_max_messages, g.g_use_pm FROM xbb_members m, ibf_groups g ' . 'WHERE LOWER(m.uname) IN (' . implode(',', $new_array) . ') ' . 'AND m.mgroup=g.g_id'
                    );

                    if (!$DB->get_num_rows()) {
                        $ibforums->input['MID'] = $to_member['uid'];

                        $this->send_form(0, $ibforums->lang['pme_no_cc_user']);

                        return;
                    }

                    while (false !== ($r = $DB->fetch_row())) {
                        $cc_array[$r['uid']] = $r;
                    }

                    //--------------------------------------

                    if (count($cc_array) > $ibforums->member['g_max_mass_pm']) {
                        $ibforums->input['MID'] = $to_member['uid'];

                        $this->send_form(0, $ibforums->lang['pme_too_many']);

                        return;
                    }

                    //--------------------------------------

                    $cc_error = '';

                    if (count($cc_array) != $array_count) {
                        foreach ($new_array as $n) {
                            $seen = 0;

                            foreach ($cc_array as $idx => $cc_user) {
                                $tmp = "'" . mb_strtolower($cc_user['uname']) . "'";

                                if ($tmp == $n) {
                                    $seen = 1;
                                }
                            }

                            if (1 != $seen) {
                                $cc_error .= '<br>' . sprintf($ibforums->lang['pme_failed_nomem'], $n, $n);
                            }
                        }
                    }

                    if ('' != $cc_error) {
                        $ibforums->input['MID'] = $to_member['uid'];

                        $this->send_form(0, $cc_error);

                        return;
                    }

                    //--------------------------------------

                    $cc_error = '';

                    $cc_id_array = [];

                    foreach ($cc_array as $idx => $cc_user) {
                        if (1 != $cc_user['g_use_pm']) {
                            $cc_error .= '<br>' . sprintf($ibforums->lang['pme_failed_nopm'], $cc_user['uname'], $cc_user['uname']);
                        }

                        if ($cc_user['g_max_messages'] > 0 and ($cc_user['g_max_messages'] < $cc_user['msg_total'] + 1)) {
                            $cc_error .= '<br>' . sprintf($ibforums->lang['pme_failed_maxed'], $cc_user['uname'], $cc_user['uname']);
                        }

                        $cc_id_array[] = $cc_user['uid'];
                    }

                    if ('' != $cc_error) {
                        $ibforums->input['MID'] = $to_member['uid'];

                        $this->send_form(0, $cc_error);

                        return;
                    }

                    //--------------------------------------

                    // Almost there! now just to check the block list..

                    //--------------------------------------

                    $DB->query("SELECT m.uname, c.allow_msg FROM xbb_members m, ibf_contacts c WHERE contact_id='" . $ibforums->member['uid'] . "' AND member_id IN (" . implode(',', $cc_id_array) . ') AND m.uid=c.member_id');

                    while (false !== ($c = $DB->fetch_row())) {
                        if (1 != $c['allow_msg']) {
                            $cc_error .= '<br>' . sprintf($ibforums->lang['pme_failed_block'], $c['uname'], $c['uname']);
                        }
                    }

                    if ('' != $cc_error) {
                        $ibforums->input['MID'] = $to_member['uid'];

                        $this->send_form(0, $cc_error);

                        return;
                    }

                    //--------------------------------------
                }
            }
        }

        //--------------------------------------

        // Is this a preview?

        //--------------------------------------

        if ('' != $ibforums->input['preview']) {
            $ibforums->input['MID'] = $to_member['uid'];

            $this->send_form(1);

            return;
        }

        //-----------------------------------------

        // Add our original ID to the pool and loop

        //-----------------------------------------

        $cc_array[$to_member['uid']] = $to_member;

        unset($to_member);

        $ibforums->input['add_tracking'] = (1 == $ibforums->input['add_tracking']) ? 1 : 0;

        foreach ($cc_array as $user_id => $to_member) {
            //--------------------------------------

            // Sort out tracking and pop us status

            //--------------------------------------

            $show_popup = $to_member['view_pop'];

            //--------------------------------------

            // Enter the info into the DB

            // Target user side.

            //--------------------------------------

            $db_string = $std->compile_db_string(
                [
                    'member_id' => $to_member['uid'],
'msg_date' => time(),
'read_state' => '0',
'title' => $ibforums->input['msg_title'],
'message' => $std->remove_tags($ibforums->input['Post']),
'from_id' => $this->member['uid'],
'vid' => 'in',
'recipient_id' => $to_member['uid'],
'tracking' => $ibforums->input['add_tracking'],
                ]
            );

            $DB->query('INSERT INTO ibf_messages (' . $db_string['FIELD_NAMES'] . ') VALUES (' . $db_string['FIELD_VALUES'] . ')');

            $new_id = $DB->get_insert_id();

            unset($db_string);

            //-----------------------------------------------------

            $DB->query(
                'UPDATE xbb_members SET ' . 'msg_total = msg_total + 1, ' . 'new_msg = new_msg + 1, ' . "msg_from_id='" . $this->member['uid'] . "', " . "msg_msg_id='" . $new_id . "', " . "show_popup='" . $show_popup . "' " . "WHERE uid='" . $to_member['uid'] . "'"
            );

            //-----------------------------------------------------

            // Has this member requested a PM email nofity?

            //-----------------------------------------------------

            if (1 == $to_member['email_pm']) {
                $to_member['language'] = '' == $to_member['language'] ? 'en' : $to_member['language'];

                $this->email->get_template('pm_notify', $to_member['language']);

                $this->email->build_message(
                    [
                        'NAME' => $to_member['uname'],
'POSTER' => $ibforums->member['uname'],
'TITLE' => $ibforums->input['msg_title'],
'LINK' => "?act=Msg&CODE=03&VID=in&MSID=$new_id",
                    ]
                );

                $this->email->subject = $ibforums->lang['pm_email_subject'];

                $this->email->to = $to_member['email'];

                $this->email->send_mail();
            }
        }

        //-----------------------------------------------------

        // Add the data to the current members DB if we are

        // adding it to our "sent items" folder

        //-----------------------------------------------------

        if ($ibforums->input['add_sent']) {
            $DB->query(
                'UPDATE xbb_members SET ' . 'msg_total = msg_total + 1 ' . "WHERE uid='" . $this->member['uid'] . "'"
            );

            $db_string = $std->compile_db_string(
                [
                    'member_id' => $this->member['uid'],
'msg_date' => time(),
'read_state' => 0,
'title' => $ibforums->lang['saved_sent_msg'] . ' ' . $ibforums->input['msg_title'],
'message' => $ibforums->input['Post'],
'from_id' => $this->member['uid'],
'vid' => 'sent',
'recipient_id' => $to_member['uid'],
                ]
            );

            $DB->query('INSERT INTO ibf_messages (' . $db_string['FIELD_NAMES'] . ') VALUES (' . $db_string['FIELD_VALUES'] . ')');

            unset($db_string);
        }

        if ($ibforums->input['OID']) {
            // We have an OID which means that this message

            // is already from the unsent folder, if true,

            // delete from unsent items.

            $DB->query("DELETE from ibf_messages WHERE msg_id='" . $ibforums->input['OID'] . "' AND member_id='" . $ibforums->member['uid'] . "' AND vid='unsent'");
        }

        $text = preg_replace('/<#FROM_MEMBER#>/', $this->member['uname'], $ibforums->lang['sent_text']);

        $text = preg_replace('/<#TO_MEMBER#>/', $to_member['uname'], $text);

        $text = preg_replace('/<#MESSAGE_TITLE#>/', $ibforums->input['msg_title'], $text);

        $print->redirect_screen($text, '&act=Msg&CODE=01');
    }

    /**********************************************************/

    // MSG LIST:

    // Views the inbox / folder of choice

    /**********************************************************/

    public function msg_list()
    {
        global $ibforums, $DB, $std, $print;

        $sort_key = '';

        switch ($ibforums->input['sort']) {
            case 'rdate':
                $sort_key = 'm.msg_date ASC';
                break;
            case 'title':
                $sort_key = 'm.title ASC';
                break;
            case 'name':
                $sort_key = 'mp.name ASC';
                break;
            default:
                $sort_key = 'm.msg_date DESC';
                break;
        }

        //---------------------------------------------

        // Get the number of messages we have in total.

        //---------------------------------------------

        $DB->query("SELECT COUNT(*) as msg_total FROM ibf_messages WHERE member_id='" . $this->member['uid'] . "' AND vid <> 'unsent'");

        $total = $DB->fetch_row();

        $total['msg_total'] = $total['msg_total'] > 0 ? $total['msg_total'] : 0;

        if ('' == $ibforums->member['msg_total']) {
            $DB->query("UPDATE xbb_members SET msg_total='" . $total['msg_total'] . "' WHERE uid='" . $this->member['uid'] . "'");
        }

        //---------------------------------------------

        // Get the number of messages in our curr folder.

        //---------------------------------------------

        $DB->query("SELECT COUNT(*) as msg_total FROM ibf_messages WHERE member_id='" . $this->member['id'] . "' AND vid='{$this->vid}'");

        $total_current = $DB->fetch_row();

        $total_current['msg_total'] = $total_current['msg_total'] > 0 ? $total_current['msg_total'] : 0;

        //---------------------------------------------

        // Make sure we've not exceeded our alloted allowance.

        //---------------------------------------------

        $info['full_messenger'] = '';

        $info['full_text'] = '';

        $info['total_messages'] = $total['msg_total'];

        $info['img_width'] = 1;

        $info['vid'] = $this->vid;

        $info['date_order'] = 'm.msg_date DESC' == $sort_key ? 'rdate' : 'msg_date';

        $amount_info = sprintf($ibforums->lang['pmpc_info_string'], $total['msg_total'], $ibforums->lang['pmpc_unlimited']);

        if ($ibforums->member['g_max_messages'] > 0) {
            $amount_info = sprintf($ibforums->lang['pmpc_info_string'], $total['msg_total'], $ibforums->member['g_max_messages']);

            $info['full_percent'] = $total['msg_total'] ? sprintf('%.0f', (($total['msg_total'] / $ibforums->member['g_max_messages']) * 100)) : 0;

            $info['img_width'] = $info['full_percent'] > 0 ? (int)$info['full_percent'] * 3 : 1;

            if ($info['img_width'] > 300) {
                $info['img_width'] = 300;
            }

            if ($total['msg_total'] >= $ibforums->member['g_max_messages']) {
                $info['full_messenger'] = "<span class='highlight'>" . $ibforums->lang['folders_full'] . '</span>';
            } else {
                $info['full_messenger'] = str_replace('<#PERCENT#>', $info['full_percent'], $ibforums->lang['pmpc_full_string']);
            }
        }

        //---------------------------------------------

        // Generate Pagination

        //---------------------------------------------

        $start = (int)$ibforums->input['st'] > 0 ? (int)$ibforums->input['st'] : 0;

        $p_end = $ibforums->vars['show_max_msg_list'] > 0 ? $ibforums->vars['show_max_msg_list'] : 50;

        $pages = $std->build_pagelinks(
            [
                'TOTAL_POSS' => $total_current['msg_total'],
'PER_PAGE' => $p_end,
'CUR_ST_VAL' => $start,
'L_SINGLE' => '',
'L_MULTI' => $ibforums->lang['msg_pages'],
'BASE_URL' => $this->base_url . '&act=Msg&CODE=1&VID=' . $this->vid . '&sort=' . $ibforums->input['sort'],
            ]
        );

        //---------------------------------------------

        // Print the header

        //---------------------------------------------

        if ('sent' == $this->vid) {
            $ibforums->lang['message_from'] = $ibforums->lang['message_to'];

            $DB->query("SELECT m.*, mp.uname as from_name FROM ibf_messages m, xbb_members mp WHERE member_id='" . $this->member['uid'] . "' AND vid='" . $this->vid . "' and mp.uid=m.recipient_id ORDER BY $sort_key LIMIT $start, $p_end");
        } else {
            $DB->query("SELECT m.*, mp.uname as from_name FROM ibf_messages m, xbb_members mp WHERE member_id='" . $this->member['uid'] . "' AND vid='" . $this->vid . "' and mp.uid=m.from_id ORDER BY $sort_key LIMIT $start, $p_end");
        }

        $this->output .= $this->html->inbox_table_header($this->msg_stats['current_dir'], $info, $this->jump_html, $pages);

        //---------------------------------------------

        // Get the messages

        //---------------------------------------------

        if ($DB->get_num_rows()) {
            while (false !== ($row = $DB->fetch_row())) {
                if ('sent' == $this->vid) {
                    $row['icon'] = '<{M_READ}>';
                } else {
                    $row['icon'] = 1 == $row['read_state'] ? '<{M_READ}>' : '<{M_UNREAD}>';
                }

                $row['date'] = $std->get_date($row['msg_date'], 'LONG');

                if ('sent' != $this->vid) {
                    $row['add_to_contacts'] = "[ <a href='{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}&act=Msg&CODE=02&MID={$row['from_id']}'>{$ibforums->lang[add_to_book]}</a> ]";
                } else {
                    $row['from_id'] = $row['recipient_id'];
                }

                $d_array = ['msg' => $row, 'member' => $this->member, 'stat' => $this->msg_stats];

                $this->output .= $this->html->inbox_row($d_array);
            }
        } else {
            $this->output .= $this->html->No_msg_inbox();
        }

        $this->output .= $this->html->end_inbox($this->jump_html, $amount_info, $pages);

        //---------------------------------------------

        // Update the message stats if we have to

        //---------------------------------------------

        if ('in' == $this->msg_stats['current_id']) {
            $DB->query(
                'UPDATE xbb_members SET ' . "new_msg='0' " . "WHERE uid='" . $this->member['uid'] . "'"
            );
        }

        $this->page_title = $ibforums->lang['t_welcome'];

        $this->nav = ["<a href='" . $this->base_url . "&act=UserCP&CODE=00'>" . $ibforums->lang['t_title'] . '</a>'];
    }

    //+------------------------------------------------------------------------------------

    /**********************************************************/

    // VIEW SAVED:

    // View the saved folder stuff.

    /**********************************************************/

    public function view_saved()
    {
        global $ibforums, $DB, $std, $print;

        //---------------------------------------------

        // Print the header

        //---------------------------------------------

        $this->output .= $this->html->unsent_table_header();

        $DB->query("SELECT m.*, mp.uname as to_name FROM ibf_messages m, xbb_members mp WHERE member_id='" . $this->member['uid'] . "' AND vid='unsent' and mp.uid=m.recipient_id ORDER BY msg_date DESC");

        //---------------------------------------------

        // Get the messages

        //---------------------------------------------

        if ($DB->get_num_rows()) {
            while (false !== ($row = $DB->fetch_row())) {
                $row['icon'] = '<{M_READ}>';

                $row['date'] = $std->get_date($row['msg_date'], 'LONG');

                $row['cc_users'] = '' == $row['cc_users'] ? $ibforums->lang['no'] : $ibforums->lang['yes'];

                $d_array = ['msg' => $row, 'member' => $this->member];

                $this->output .= $this->html->unsent_row($d_array);
            }
        } else {
            $this->output .= $this->html->No_msg_inbox();
        }

        $this->output .= $this->html->unsent_end();

        $this->page_title = $ibforums->lang['t_welcome'];

        $this->nav = ["<a href='" . $this->base_url . "&act=UserCP&CODE=00'>" . $ibforums->lang['t_title'] . '</a>'];
    }

    public function show_tracking()
    {
        global $ibforums, $DB, $std, $print;

        //---------------------------------------------

        // Get all tracked and read messages

        //---------------------------------------------

        $this->output .= $this->html->trackread_table_header();

        $DB->query("SELECT m.*, mp.uname as to_name, mp.uid as memid FROM ibf_messages m, xbb_members mp WHERE m.tracking=1 AND m.read_state=1 AND m.from_id='" . $this->member['uid'] . "' AND m.member_id=mp.uid ORDER BY msg_date DESC");

        if ($DB->get_num_rows()) {
            while (false !== ($row = $DB->fetch_row())) {
                $row['icon'] = '<{M_READ}>';

                $row['date'] = $std->get_date($row['read_date'], 'LONG');

                $this->output .= $this->html->trackread_row($row);
            }
        } else {
            $this->output .= $this->html->No_msg_inbox();
        }

        $this->output .= $this->html->trackread_end();

        //---------------------------------------------

        // Get all tracked and UNread messages

        //---------------------------------------------

        $this->output .= $this->html->trackUNread_table_header();

        $DB->query("SELECT m.*, mp.uname as to_name, mp.uid as memid FROM ibf_messages m, xbb_members mp WHERE m.tracking=1 AND m.read_state=0 AND m.from_id='" . $this->member['uid'] . "' AND m.member_id=mp.uid ORDER BY msg_date DESC");

        if ($DB->get_num_rows()) {
            while (false !== ($row = $DB->fetch_row())) {
                $row['icon'] = '<{M_UNREAD}>';

                $row['date'] = $std->get_date($row['msg_date'], 'LONG');

                $this->output .= $this->html->trackUNread_row($row);
            }
        } else {
            $this->output .= $this->html->No_msg_inbox();
        }

        $this->output .= $this->html->trackUNread_end();

        $this->page_title = $ibforums->lang['t_welcome'];

        $this->nav = ["<a href='" . $this->base_url . "&act=UserCP&CODE=00'>" . $ibforums->lang['t_title'] . '</a>'];
    }

    /*********************************************************************/

    // Parse the member info

    /*********************************************************************/

    public function parse_member($member = [], $row = [])
    {
        global $ibforums, $std, $DB;

        //convert id --> uid

        $member['id'] = $member['uid'];

        $member['name'] = "<a href='{$this->base_url}&act=Profile&CODE=03&MID={$member['uid']}'>{$member['uname']}</a>";

        $member['avatar'] = $std->get_avatar($member['user_avatar'], $ibforums->member['view_avs'], $member['avatar_size']);

        $member['MEMBER_JOINED'] = $ibforums->lang['m_joined'] . ' ' . $std->get_date($member['user_regdate'], 'JOINED');

        $member['MEMBER_GROUP'] = $ibforums->lang['m_group'] . ' ' . $member['member_group'];

        $member['MEMBER_POSTS'] = $ibforums->lang['m_posts'] . ' ' . $member['posts'];

        $member['PROFILE_ICON'] = "<a href='{$this->base_url}&act=Profile&CODE=03&MID={$member['uid']}'><{P_PROFILE}></a>&nbsp;";

        $member['MESSAGE_ICON'] = "<a href='{$this->base_url}&act=Msg&CODE=04&MID={$member['uid']}'><{P_MSG}></a>&nbsp;";

        if ($member['user_viewemail']) {
            $member['EMAIL_ICON'] = "<a href='{$this->base_url}&act=Mail&CODE=00&MID={$member['uid']}'><{P_EMAIL}></a>&nbsp;";
        }

        if ($member['url'] and $member['url'] = preg_match("/^http:\/\/\S+$/", $member['WEBSITE'])) {
            $member['WEBSITE_ICON'] = "<a href='{$member['url']}' target='_blank'><{P_WEBSITE}></a>&nbsp;";
        }

        if ($member['user_icq']) {
            $member['ICQ_ICON'] = "<a href=\"javascript:PopUp('{$this->base_url}&act=ICQ&MID={$member['uid']}','Pager','450','330','0','1','1','1')\"><{P_ICQ}></a>&nbsp;";
        }

        if ($member['user_aim']) {
            $member['AOL_ICON'] = "<a href=\"javascript:PopUp('{$this->base_url}&act=AOL&MID={$member['uid']}','Pager','450','330','0','1','1','1')\"><{P_AOL}></a>&nbsp;";
        }

        //-----------------------------------------------------

        return $member;
    }

    public function html_add_smilie_box()
    {
        global $ibforums, $std, $DB;

        $show_table = 0;

        $count = 0;

        $smilies = "<tr align='center'>\n";

        // Get the smilies from the DB

        $DB->query("SELECT * FROM xbb_emoticons WHERE clickable='1'");

        while (false !== ($elmo = $DB->fetch_row())) {
            $show_table++;

            $count++;

            // Make single quotes as URL's with html entites in them

            // are parsed by the browser, so ' causes JS error :o

            if (mb_strstr($elmo['code'], '&#39;')) {
                $in_delim = '"';

                $out_delim = "'";
            } else {
                $in_delim = "'";

                $out_delim = '"';
            }

            $smilies .= "<td><a href={$out_delim}javascript:emoticon($in_delim" . $elmo['code'] . "$in_delim){$out_delim}><img src=\"" . $ibforums->vars['EMOTICONS_URL'] . '/' . $elmo['smile_url'] . "\" alt='smilie' border='0'></a>&nbsp;</td>\n";

            if ($count == $ibforums->vars['emo_per_row']) {
                $smilies .= "</tr>\n\n<tr align='center'>";

                $count = 0;
            }
        }

        if ($count != $ibforums->vars['emo_per_row']) {
            for ($i = $count; $i < $ibforums->vars['emo_per_row']; ++$i) {
                $smilies .= "<td>&nbsp;</td>\n";
            }

            $smilies .= '</tr>';
        }

        $table = $this->post_html->smilie_table();

        if (0 != $show_table) {
            $table = preg_replace('/<!--THE SMILIES-->/', $smilies, $table);

            $this->output = preg_replace('/<!--SMILIE TABLE-->/', $table, $this->output);
        }
    }

    public function build_contact_list()
    {
        global $DB, $std;

        $contacts = '';

        $DB->query("SELECT * FROM ibf_contacts WHERE member_id='" . $this->member['uid'] . "' ORDER BY contact_name");

        if ($DB->get_num_rows()) {
            $contacts = "<select name='from_contact' class='forminput'><option value='-'>" . $ibforums->lang['other'] . "</option>\n<option value='-'>--------------------</option>\n";

            while (false !== ($entry = $DB->fetch_row())) {
                $contacts .= "<option value='" . $entry['contact_id'] . "'>" . $entry['contact_name'] . "</option>\n";
            }

            $contacts .= "</select>\n";
        } else {
            $contacts = $ibforums->lang['address_list_empty'];
        }

        return $contacts;
    }
}

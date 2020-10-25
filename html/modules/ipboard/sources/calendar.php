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
|   > Calendar functions library
|   > Module written by Matt Mecham
|   > Date started: 12th June 2002
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

$idx = new calendar();

class calendar
{
    public $output = '';

    public $base_url = '';

    public $html = '';

    public $output = '';

    public $page_title = '';

    public $nav;

    public $chosen_month = '';

    public $chosen_year = '';

    public $now_date = '';

    public $our_datestamp = '';

    public $offset = '';

    public $start_date = '';

    public $first_day_array = '';

    public $month_words = [];

    public $day_words = [];

    public function __construct()
    {
        global $ibforums, $DB, $std, $print, $skin_universal;

        $ibforums->lang = $std->load_words($ibforums->lang, 'lang_calendar', $ibforums->lang_id);

        $ibforums->lang = $std->load_words($ibforums->lang, 'lang_post', $ibforums->lang_id);

        $this->html = $std->load_template('skin_calendar');

        $this->post_html = $std->load_template('skin_post');

        //-----------------------------------------

        // Do some set up

        //-----------------------------------------

        $this->offset = (('' != $ibforums->member['timezone_offset']) ? $ibforums->member['timezone_offset'] : $ibforums->vars['time_offset']) * 3600;

        if ('' != $ibforums->vars['time_adjust'] and 0 != $ibforums->vars['time_adjust']) {
            $this->offset += ($ibforums->vars['time_adjust'] * 60);
        }

        /*if ($ibforums->member['dst_in_use'])
        {
            $this->offset += 3600;
        }*/

        //-----------------------------------------

        // Prep our chosen dates

        //-----------------------------------------

        $this->now_date = getdate(time() + $this->offset);

        $this->chosen_month = ('' == (int)$ibforums->input['m']) ? $this->now_date['mon'] : $ibforums->input['m'];

        $this->chosen_year = ('' == (int)$ibforums->input['y']) ? $this->now_date['year'] : $ibforums->input['y'];

        //-----------------------------------------

        // Make sure the date is in range.

        //-----------------------------------------

        if (!checkdate($this->chosen_month, 1, $this->chosen_year)) {
            $this->chosen_month = $this->now_date['mon'];

            $this->chosen_year = $this->now_date['year'];
        }

        //-----------------------------------------

        // Get the timestamp for our chosen date

        //-----------------------------------------

        $this->our_datestamp = mktime(0, 0, 0, $this->chosen_month, 1, $this->chosen_year);

        $this->first_day_array = getdate($this->our_datestamp);

        //-----------------------------------------

        // Finally, build up the lang arrays

        //-----------------------------------------

        $this->month_words = [
            $ibforums->lang['M_1'],
            $ibforums->lang['M_2'],
            $ibforums->lang['M_3'],
            $ibforums->lang['M_4'],
            $ibforums->lang['M_5'],
            $ibforums->lang['M_6'],
            $ibforums->lang['M_7'],
            $ibforums->lang['M_8'],
            $ibforums->lang['M_9'],
            $ibforums->lang['M_10'],
            $ibforums->lang['M_11'],
            $ibforums->lang['M_12'],
        ];

        $this->day_words = [
            $ibforums->lang['D_0'],
            $ibforums->lang['D_1'],
            $ibforums->lang['D_2'],
            $ibforums->lang['D_3'],
            $ibforums->lang['D_4'],
            $ibforums->lang['D_5'],
            $ibforums->lang['D_6'],
        ];

        //-----------------------------------------

        switch ($ibforums->input['code']) {
            case 'newevent':
                $this->new_event();
                break;
            case 'showday':
                $this->show_day();
                break;
            case 'showevent':
                $this->show_event();
                break;
            case 'addnewevent':
                $this->add_new_event();
                break;
            case 'birthdays':
                $this->show_birthdays();
                break;
            case 'edit':
                $this->edit();
                break;
            case 'doedit':
                $this->do_edit();
                break;
            default:
                $this->show_month();
                break;
        }

        //-----------------------------------------

        if ('' == $this->page_title) {
            $this->page_title = $ibforums->vars['board_name'] . ' ' . $ibforums->lang['page_title'];
        }

        if (!is_array($this->nav)) {
            $this->nav[] = "<a href='{$ibforums->base_url}&act=calendar'>{$ibforums->lang['page_title']}</a>";
        }

        $print->add_output((string)$this->output);

        $print->do_output(['TITLE' => $this->page_title, 'JS' => 0, NAV => $this->nav]);
    }

    //--------------------------------------

    // Display Edit / Delete boxes

    //--------------------------------------

    public function edit()
    {
        global $ibforums, $DB, $std, $print;

        if (1 != $ibforums->member['g_calendar_post']) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_permission']);
        }

        if (!$ibforums->member['uid']) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_permission']);
        }

        //-----------------------------------------

        $eventid = (int)$ibforums->input['eventid'];

        if ('' == $eventid) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'cal_no_events']);
        }

        $DB->query("SELECT * FROM ibf_calendar_events WHERE eventid='$eventid'");

        if (!$event = $DB->fetch_row()) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'cal_no_events']);
        }

        //-----------------------------------------

        $can_edit = 0;

        //-----------------------------------------

        // Do we have permission to edit this event?

        //-----------------------------------------

        if ($ibforums->member['uid'] == $event['userid']) {
            $can_edit = 1;
        } elseif (1 == $ibforums->member['g_is_supmod']) {
            $can_edit = 1;
        } else {
            $can_edit = 0;
        }

        if (1 != $can_edit) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'cal_no_events']);
        }

        //-----------------------------------------

        // Do we have permission to see the event?

        //-----------------------------------------

        if ('*' != $event['read_perms']) {
            if (!preg_match('/(^|,)' . $ibforums->member['mgroup'] . '(,|$)/', $event['read_perms'])) {
                $std->Error(['LEVEL' => 1, 'MSG' => 'cal_no_events']);
            }
        }

        //-----------------------------------------

        $ibforums->lang['the_max_length'] = $ibforums->vars['max_post_length'] ? $ibforums->vars['max_post_length'] * 1024 : 2140000;

        $this->nav[] = "<a href='{$ibforums->base_url}&act=calendar'>{$ibforums->lang['page_title']}</a>";

        $this->nav[] = $ibforums->lang['edit_event'] . ' ' . $event['title'];

        //-----------------------------------------

        $this->output .= $this->post_html->calendar_start_edit_form($event['eventid']);

        $this->output .= $this->post_html->table_top($ibforums->lang['edit_event']);

        $this->output .= $this->post_html->calendar_delete_box();

        $this->output .= $this->post_html->calendar_event_title($event['title']);

        $this->output .= $this->post_html->calendar_choose_date(
            $this->get_day_dropdown($event['mday']),
            $this->get_month_dropdown($event['month']),
            $this->get_year_dropdown($event['year'])
        );

        $public = '';

        $private = '';

        if (1 == $event['priv_event']) {
            $private = ' selected';
        } else {
            $public = ' selected';
        }

        $this->output .= $this->post_html->calendar_event_type($public, $private);

        if ($ibforums->member['mgroup'] == $ibforums->vars['admin_group']) {
            // Get all the group ID's and names from the DB and build the selection box

            $group_choices = '';

            $DB->query('SELECT g_id, g_title FROM ibf_groups ORDER BY g_title');

            while (false !== ($r = $DB->fetch_row())) {
                $selected = '';

                if (preg_match('/(^|,)' . $r['g_id'] . '(,|$)/', $event['read_perms'])) {
                    $selected = ' selected';
                }

                $group_choices .= "<option value='" . $r['g_id'] . "'" . $selected . '>' . $r['g_title'] . "</option>\n";
            }

            $this->output .= $this->post_html->calendar_admin_group_box($group_choices);
        }

        $this->output .= $this->post_html->postbox_buttons(str_replace('<br>', "\n", $event['event_text']));

        $this->output .= $this->post_html->calendar_end_form($ibforums->lang['calendar_edit_submit']);

        //--------------------------------------

        // Add in the smilies box

        //--------------------------------------

        $show_table = 0;

        $count = 0;

        $smilies = "<tr align='center'>\n";

        // Get the smilies from the DB

        $DB->query("SELECT * FROM xbb_emoticons WHERE clickable='1'");

        while (false !== ($elmo = $DB->fetch_row())) {
            $show_table++;

            $count++;

            $smilies .= "<td><a href=\"javascript:emoticon('" . $elmo['code'] . "')\"><img src=\"" . $ibforums->vars['EMOTICONS_URL'] . '/' . $elmo['smile_url'] . "\" alt='smilie' border='0'></a>&nbsp;</td>\n";

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

        // Remove the "add sig" box <!--IBF.END_SIG_CLICK-->

        $this->output = preg_replace("/<!--IBF\.SIG_CLICK-->.+?<!--IBF\.END_SIG_CLICK-->/s", '', $this->output);
    }

    //-----------------------------------------

    // Do the edit

    //-----------------------------------------

    public function do_edit()
    {
        global $ibforums, $DB, $std, $print, $_POST;

        if (1 != $ibforums->member['g_calendar_post']) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_permission']);
        }

        if (!$ibforums->member['uid']) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_permission']);
        }

        //-----------------------------------------

        $eventid = (int)$ibforums->input['eventid'];

        if ('' == $eventid) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'cal_no_events']);
        }

        $DB->query("SELECT * FROM ibf_calendar_events WHERE eventid='$eventid'");

        if (!$event = $DB->fetch_row()) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'cal_no_events']);
        }

        //-----------------------------------------

        $can_edit = 0;

        //-----------------------------------------

        // Do we have permission to edit this event?

        //-----------------------------------------

        if ($ibforums->member['uid'] == $event['userid']) {
            $can_edit = 1;
        } elseif (1 == $ibforums->member['g_is_supmod']) {
            $can_edit = 1;
        } else {
            $can_edit = 0;
        }

        if (1 != $can_edit) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'cal_no_events']);
        }

        //-----------------------------------------

        // Are we deleting this event?

        //-----------------------------------------

        if (1 == $_POST['event_delete']) {
            $DB->query("DELETE FROM ibf_calendar_events WHERE eventid='$eventid'");

            $print->redirect_screen($ibforums->lang['delete_event_redirect'], '&act=calendar');
        } else {
            $ibforums->vars['max_post_length'] = $ibforums->vars['max_post_length'] ?: 2140000;

            //-----------------------------------------

            // Sort out some of the form data, check for

            // posting length, etc.

            //-----------------------------------------

            $allow_emoticons = 'yes' == $ibforums->input['enableemo'] ? 1 : 0;

            $private_event = 'private' == $ibforums->input['e_type'] ? 1 : 0;

            //-----------------------------------------

            // Do we have a valid post?

            //-----------------------------------------

            if (mb_strlen(trim($_POST['Post'])) < 1) {
                $std->Error(['LEVE' => 1, 'MSG' => 'no_post']);
            }

            if (mb_strlen($_POST['Post']) > ($ibforums->vars['max_post_length'] * 1024)) {
                $std->Error(['LEVEL' => 1, 'MSG' => 'post_too_long']);
            }

            //-----------------------------------------

            // Fix up the Event Title

            //-----------------------------------------

            $ibforums->input['event_title'] = str_replace('<br>', '', $ibforums->input['event_title']);

            $ibforums->input['event_title'] = trim(stripslashes($ibforums->input['event_title']));

            if ((mb_strlen($ibforums->input['event_title']) < 2) or (!$ibforums->input['event_title'])) {
                $std->Error(['LEVEL' => 1, 'MSG' => 'cal_title_none']);
            }

            if (mb_strlen($ibforums->input['event_title']) > 64) {
                $std->Error(['LEVEL' => 1, 'MSG' => 'cal_title_long']);
            }

            //-----------------------------------------

            // Are we an admin, and have we set w/groups

            // can see?

            //-----------------------------------------

            $read_perms = '*';

            if ($ibforums->member['mgroup'] == $ibforums->vars['admin_group']) {
                if (is_array($_POST['e_groups'])) {
                    $read_perms = implode(',', $_POST['e_groups']);

                    $read_perms .= ',' . $ibforums->vars['admin_group'];
                }

                if ('' == $read_perms) {
                    $read_perms = '*';
                }
            }

            $day = (int)$ibforums->input['e_day'];

            $month = (int)$ibforums->input['e_month'];

            $year = (int)$ibforums->input['e_year'];

            //-----------------------------------------

            // Do we have a sensible date?

            //-----------------------------------------

            if (!checkdate($month, $day, $year)) {
                $std->Error(['LEVEL' => 1, 'MSG' => 'cal_date_oor']);
            }

            //-----------------------------------------

            // Add it to the DB

            //-----------------------------------------

            $db_string = $DB->compile_db_update_string(
                [
                    'year' => $year,
'month' => $month,
'mday' => $day,
'title' => $ibforums->input['event_title'],
'event_text' => $ibforums->input['Post'],
'read_perms' => $read_perms,
'priv_event' => $private_event,
'unix_stamp' => mktime(23, 59, 59, $month, $day, $year),
'show_emoticons' => $allow_emoticons,
                ]
            );

            $DB->query("UPDATE ibf_calendar_events SET $db_string WHERE eventid='$eventid'");

            $print->redirect_screen($ibforums->lang['edit_event_redirect'], "&act=calendar&code=showevent&eventid=$eventid");
        }
    }

    //-----------------------------------------

    public function show_month()
    {
        global $ibforums, $DB, $std, $print;

        //-----------------------------------------

        // Get the birthdays from the database

        //-----------------------------------------

        $birthdays = [];

        $DB->query("SELECT bday_day from xbb_members WHERE bday_month='" . $this->chosen_month . "'");

        while (false !== ($user = $DB->fetch_row())) {
            $birthdays[$user['bday_day']]++;
        }

        //-----------------------------------------

        // Get the events

        //-----------------------------------------

        $events = [];

        $DB->query("SELECT eventid, title, userid, priv_event, read_perms, mday from ibf_calendar_events WHERE month='" . $this->chosen_month . "' AND year='" . $this->chosen_year . "'");

        while (false !== ($event = $DB->fetch_row())) {
            if (1 == $event['priv_event']) {
                if ($ibforums->member['uid'] != $event['userid']) {
                    continue;
                }
            }

            if ('*' != $event['read_perms']) {
                if (!preg_match('/(^|,)' . $ibforums->member['mgroup'] . '(,|$)/', $event['read_perms'])) {
                    continue;
                }
            }

            $events[$event['mday']][] = $event;
        }

        //-----------------------------------------

        // Figure out the next / previous links

        //-----------------------------------------

        $prev_month = [];

        $next_month = [];

        $prev_month['year_id'] = $this->chosen_year;

        $next_month['year_id'] = $this->chosen_year;

        $prev_month['month_id'] = $this->chosen_month - 1;

        $prev_month['month_name'] = $this->month_words[$this->chosen_month - 2];

        $next_month['month_name'] = $this->month_words[$this->chosen_month];

        $next_month['month_id'] = $this->chosen_month + 1;

        if (1 == $this->chosen_month) {
            $prev_month['month_name'] = $this->month_words[11];

            $prev_month['month_id'] = 12;

            $prev_month['year_id'] = $this->chosen_year - 1;
        } elseif (12 == $this->chosen_month) {
            $next_month['month_name'] = $this->month_words[0];

            $next_month['month_id'] = 1;

            $next_month['year_id'] = $this->chosen_year + 1;
        }

        $this->output .= $this->html->cal_main_content($this->month_words[$this->chosen_month - 1], $this->chosen_year, $prev_month, $next_month);

        //-----------------------------------------

        // Print the days table top row

        //-----------------------------------------

        $day_output = '';

        $cal_output = '';

        foreach ($this->day_words as $day) {
            $day_output .= $this->html->cal_day_bit($day);
        }

        //-----------------------------------------

        // Print the main calendar body

        //-----------------------------------------

        $seen_days = []; // Holds yday

        for ($c = 0; $c < 42; $c++) {
            $day_array = getdate($this->our_datestamp);

            //echo(">>".$day_array['weekday']." -{$day_array['wday']}, {$day_array['yday']}=$c- ". $day_array['mday'].",".$day_array['mon']."<br>");

            if (0 == (($c) % 7)) {
                //-----------------------------------------

                // Kill the loop if we are no longer on our month

                //-----------------------------------------

                if ($day_array['mon'] != $this->chosen_month) {
                    break;
                }

                $cal_output .= $this->html->cal_new_row();
            }

            //-----------------------------------------

            // Run out of legal days for this month?

            // Or have we yet to get to the first day?

            //-----------------------------------------

            if (($c < $this->first_day_array['wday']) or ($day_array['mon'] != $this->chosen_month)) {
                $cal_output .= $this->html->cal_blank_cell();
            } else {
                $this->our_datestamp += 86400;

                if (1 == $seen_days[$day_array['yday']]) {
                    $c--;

                    continue;
                }

                $seen_days[$day_array['yday']] = 1;

                $this_day_events = '';

                $cal_date = $day_array['mday'];

                if (isset($events[$day_array['mday']]) and is_array($events[$day_array['mday']])) {
                    if (count($events[$day_array['mday']]) > 0) {
                        foreach ($events[$day_array['mday']] as $idx => $data) {
                            $this_day_events .= $this->html->cal_events_wrap(
                                'code=showevent&eventid=' . $data['eventid'],
                                $data['title']
                            );
                        }
                    }
                }

                if (isset($birthdays[$day_array['mday']]) and $birthdays[$day_array['mday']] > 0) {
                    $this_day_events .= $this->html->cal_events_wrap(
                        'code=birthdays&y=' . $this->chosen_year . '&m=' . $this->chosen_month . '&d=' . $day_array['mday'],
                        sprintf($ibforums->lang['entry_birthdays'], $birthdays[$day_array['mday']])
                    );
                }

                if ('' != $this_day_events) {
                    $cal_date = "<a href='{$ibforums->base_url}&act=calendar&code=showday&y=" . $this->chosen_year . '&m=' . $this->chosen_month . '&d=' . $day_array['mday'] . "'>{$day_array['mday']}</a>";

                    $this_day_events = $this->html->cal_events_start() . $this_day_events . $this->html->cal_events_end();
                }

                if (($day_array['mday'] == $this->now_date['mday']) and ($this->now_date['mon'] == $day_array['mon']) and ($this->now_date['year'] == $day_array['year'])) {
                    $cal_output .= $this->html->cal_date_cell_today($cal_date, $this_day_events);
                } else {
                    $cal_output .= $this->html->cal_date_cell($cal_date, $this_day_events);
                }

                unset($this_day_events);
            }
        }

        //-----------------------------------------

        // Switch the HTML tags...

        //-----------------------------------------

        $this->output = str_replace('<!--IBF.DAYS_TITLE_ROW-->', $day_output, $this->output);

        $this->output = str_replace('<!--IBF.DAYS_CONTENT-->', $cal_output, $this->output);

        $this->output = str_replace('<!--IBF.MONTH_BOX-->', $this->get_month_dropdown(), $this->output);

        $this->output = str_replace('<!--IBF.YEAR_BOX-->', $this->get_year_dropdown(), $this->output);

        $this->nav[] = "<a href='{$ibforums->base_url}&act=calendar'>{$ibforums->lang['page_title']}</a>";

        $this->nav[] = $this->month_words[$this->chosen_month - 1] . ' ' . $this->chosen_year;
    }

    //-----------------------------------------

    // POST NEW CALENDAR EVENT

    //-----------------------------------------

    public function new_event()
    {
        global $ibforums, $DB, $std, $print;

        if (1 != $ibforums->member['g_calendar_post']) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_permission']);
        }

        if (!$ibforums->member['uid']) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_permission']);
        }

        $ibforums->lang['the_max_length'] = $ibforums->vars['max_post_length'] ? $ibforums->vars['max_post_length'] * 1024 : 2140000;

        $this->nav[] = "<a href='{$ibforums->base_url}&act=calendar'>{$ibforums->lang['page_title']}</a>";

        $this->nav[] = $ibforums->lang['post_new_event'];

        $this->output .= $this->post_html->calendar_start_form();

        $this->output .= $this->post_html->table_top($ibforums->lang['post_new_event']);

        $this->output .= $this->post_html->calendar_event_title();

        $this->output .= $this->post_html->calendar_choose_date(
            $this->get_day_dropdown(),
            $this->get_month_dropdown(),
            $this->get_year_dropdown()
        );

        $this->output .= $this->post_html->calendar_event_type();

        if ($ibforums->member['mgroup'] == $ibforums->vars['admin_group']) {
            // Get all the group ID's and names from the DB and build the selection box

            $group_choices = '';

            $DB->query('SELECT g_id, g_title FROM ibf_groups ORDER BY g_title');

            while (false !== ($r = $DB->fetch_row())) {
                $group_choices .= "<option value='" . $r['g_id'] . "'>" . $r['g_title'] . "</option>\n";
            }

            $this->output .= $this->post_html->calendar_admin_group_box($group_choices);
        }

        $this->output .= $this->post_html->postbox_buttons('');

        $this->output .= $this->post_html->calendar_end_form($ibforums->lang['calendar_submit']);

        //--------------------------------------

        // Add in the smilies box

        //--------------------------------------

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

        // Remove the "add sig" box <!--IBF.END_SIG_CLICK-->

        $this->output = preg_replace("/<!--IBF\.SIG_CLICK-->.+?<!--IBF\.END_SIG_CLICK-->/s", '', $this->output);
    }

    //-----------------------------------------

    // ADD NEW CALENDAR EVENT TO THE DB

    //-----------------------------------------

    public function add_new_event()
    {
        global $ibforums, $DB, $std, $print, $_POST;

        if (1 != $ibforums->member['g_calendar_post']) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_permission']);
        }

        if (!$ibforums->member['uid']) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_permission']);
        }

        $ibforums->vars['max_post_length'] = $ibforums->vars['max_post_length'] ?: 2140000;

        //-----------------------------------------

        // Sort out some of the form data, check for

        // posting length, etc.

        //-----------------------------------------

        $allow_emoticons = 'yes' == $ibforums->input['enableemo'] ? 1 : 0;

        $private_event = 'private' == $ibforums->input['e_type'] ? 1 : 0;

        //-----------------------------------------

        // Do we have a valid post?

        //-----------------------------------------

        if (mb_strlen(trim($_POST['Post'])) < 1) {
            $std->Error(['LEVE' => 1, 'MSG' => 'no_post']);
        }

        if (mb_strlen($_POST['Post']) > ($ibforums->vars['max_post_length'] * 1024)) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'post_too_long']);
        }

        //-----------------------------------------

        // Fix up the Event Title

        //-----------------------------------------

        $ibforums->input['event_title'] = str_replace('<br>', '', $ibforums->input['event_title']);

        $ibforums->input['event_title'] = trim(stripslashes($ibforums->input['event_title']));

        if ((mb_strlen($ibforums->input['event_title']) < 2) or (!$ibforums->input['event_title'])) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'cal_title_none']);
        }

        if (mb_strlen($ibforums->input['event_title']) > 64) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'cal_title_long']);
        }

        //-----------------------------------------

        // Are we an admin, and have we set w/groups

        // can see?

        //-----------------------------------------

        $read_perms = '*';

        if ($ibforums->member['mgroup'] == $ibforums->vars['admin_group']) {
            if (is_array($_POST['e_groups'])) {
                $read_perms = implode(',', $_POST['e_groups']);

                $read_perms .= ',' . $ibforums->vars['admin_group'];
            }

            if ('' == $read_perms) {
                $read_perms = '*';
            }
        }

        $day = (int)$ibforums->input['e_day'];

        $month = (int)$ibforums->input['e_month'];

        $year = (int)$ibforums->input['e_year'];

        //-----------------------------------------

        // Do we have a sensible date?

        //-----------------------------------------

        if (!checkdate($month, $day, $year)) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'cal_date_oor']);
        }

        //-----------------------------------------

        // Add it to the DB

        //-----------------------------------------

        $db_string = $DB->compile_db_insert_string(
            [
                'userid' => $ibforums->member['uid'],
'year' => $year,
'month' => $month,
'mday' => $day,
'title' => $ibforums->input['event_title'],
'event_text' => $ibforums->input['Post'],
'read_perms' => $read_perms,
'unix_stamp' => mktime(23, 59, 59, $month, $day, $year),
'priv_event' => $private_event,
'show_emoticons' => $allow_emoticons,
            ]
        );

        $DB->query('INSERT INTO ibf_calendar_events (' . $db_string['FIELD_NAMES'] . ') VALUES (' . $db_string['FIELD_VALUES'] . ')');

        $print->redirect_screen($ibforums->lang['new_event_redirect'], '&act=calendar');
    }

    //-----------------------------------------

    // SHOW DAYS EVENTS

    //-----------------------------------------

    public function show_day()
    {
        global $ibforums, $DB, $std, $print;

        $day = (int)$ibforums->input['d'];

        $month = (int)$ibforums->input['m'];

        $year = (int)$ibforums->input['y'];

        //-----------------------------------------

        // Do we have a sensible date?

        //-----------------------------------------

        if (!checkdate($month, $day, $year)) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'cal_date_oor']);
        }

        require './sources/lib/post_parser.php';

        $this->parser = new post_parser();

        //-----------------------------------------

        $this->output .= $this->html->cal_page_events_start();

        $eq = $DB->query("SELECT * FROM ibf_calendar_events WHERE month='$month' AND mday='$day' AND year='$year'");

        $printed = 0;

        if ($DB->get_num_rows($eq)) {
            while (false !== ($event = $DB->fetch_row($eq))) {
                //-----------------------------------------

                // Is it a private event?

                //-----------------------------------------

                if (1 == $event['priv_event'] and $ibforums->member['uid'] != $event['userid']) {
                    continue;
                }

                //-----------------------------------------

                // Do we have permission to see the event?

                //-----------------------------------------

                if ('*' != $event['read_perms']) {
                    if (!preg_match('/(^|,)' . $ibforums->member['mgroup'] . '(,|$)/', $event['read_perms'])) {
                        continue;
                    }
                }

                $this->output .= $this->make_event_html($event);

                $printed++;
            }
        }

        //-----------------------------------------

        // Do we have any printed events?

        //-----------------------------------------

        if ($printed > 0) {
            $switch = 1;
        } else {
            // Error if no birthdays

            $switch = 0;
        }

        $this->output .= $this->make_birthday_html($month, $day, $switch);

        $this->nav[] = "<a href='{$ibforums->base_url}&act=calendar'>{$ibforums->lang['page_title']}</a>";

        $this->nav[] = $day . ' ' . $this->month_words[$this->chosen_month - 1] . ' ' . $this->chosen_year;
    }

    //-----------------------------------------

    // SHOW A SINGLE EVENT BASED ON eventid

    //-----------------------------------------

    public function show_event()
    {
        global $ibforums, $DB, $std, $print;

        $eventid = (int)$ibforums->input['eventid'];

        if ('' == $eventid) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'cal_no_events']);
        }

        $DB->query("SELECT * FROM ibf_calendar_events WHERE eventid='$eventid'");

        if (!$event = $DB->fetch_row()) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'cal_no_events']);
        }

        //-----------------------------------------

        // Is it a private event?

        //-----------------------------------------

        if (1 == $event['priv_event'] and $ibforums->member['uid'] != $event['userid']) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'cal_no_events']);
        }

        //-----------------------------------------

        // Do we have permission to see the event?

        //-----------------------------------------

        if ('*' != $event['read_perms']) {
            if (!preg_match('/(^|,)' . $ibforums->member['mgroup'] . '(,|$)/', $event['read_perms'])) {
                $std->Error(['LEVEL' => 1, 'MSG' => 'cal_no_events']);
            }
        }

        //-----------------------------------------

        // Get the pre parsed event HTML

        //-----------------------------------------

        require './sources/lib/post_parser.php';

        $this->parser = new post_parser();

        //-----------------------------------------

        $this->output .= $this->html->cal_page_events_start() . $this->make_event_html($event);

        $this->nav[] = "<a href='{$ibforums->base_url}&act=calendar'>{$ibforums->lang['page_title']}</a>";

        $this->nav[] = $event['title'];
    }

    //-----------------------------------------

    // MAKE EVENT HTML (return HTML for bdays)

    //-----------------------------------------

    public function make_event_html($event)
    {
        global $ibforums, $DB, $std, $print;

        //-----------------------------------------

        // Parse the text

        //-----------------------------------------

        $event['event_text'] = $this->parser->convert(
            [
                'TEXT' => $event['event_text'],
'SMILIES' => $event['show_emoticons'],
'CODE' => 1,
'HTML' => 0,
            ]
        );

        //-----------------------------------------

        // What kind of event is it?

        //-----------------------------------------

        $event_type = $ibforums->lang['public_event'];

        if (1 == $event['priv_event']) {
            $event_type = $ibforums->lang['private_event'];
        } elseif ('*' != $event['read_perms']) {
            $event_type = $ibforums->lang['restricted_event'];
        }

        //-----------------------------------------

        // Do we have an edit button?

        //-----------------------------------------

        $edit_button = '';

        // Are we a super dooper moderator?

        if (1 == $ibforums->member['g_is_supmod']) {
            $edit_button = $this->html->cal_edit_button($event['eventid']);
        } // Are we the OP of this event?

        elseif ($ibforums->member['uid'] == $event['userid']) {
            $edit_button = $this->html->cal_edit_button($event['eventid']);
        }

        //-----------------------------------------

        // Get the member details and stuff

        //-----------------------------------------

        $DB->query(
            'SELECT m.uid, m.uname, m.mgroup, m.posts, m.joined, m.avatar, m.avatar_size, ' . "g.g_id, g.g_title FROM xbb_members m, ibf_groups g WHERE m.uid='" . $event['userid'] . "' AND g.g_id=m.mgroup"
        );

        $member = $DB->fetch_row();

        $member['joined'] = $std->get_date($member['user_regdate'], 'JOINED');

        $member['avatar'] = $std->get_avatar($member['user_avatar'], $ibforums->member['view_avs'], $member['avatar_size']);

        $event['month_text'] = $this->month_words[$event['month'] - 1];

        return $this->html->cal_show_event($event, $member, $event_type, $edit_button);
    }

    //-----------------------------------------

    // SHOW BIRTHDAYS

    //-----------------------------------------

    public function show_birthdays()
    {
        global $ibforums, $DB, $std, $print;

        $day = (int)$ibforums->input['d'];

        $month = (int)$ibforums->input['m'];

        $year = (int)$ibforums->input['y'];

        //-----------------------------------------

        // Do we have a sensible date?

        //-----------------------------------------

        if (!checkdate($month, $day, $year)) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'cal_date_oor']);
        }

        $this->output .= $this->html->cal_page_events_start() . $this->make_birthday_html($month, $day);

        $this->nav[] = "<a href='{$ibforums->base_url}&act=calendar'>{$ibforums->lang['page_title']}</a>";

        $this->nav[] = $ibforums->lang['cal_birthdays'] . ' ' . $this->month_words[$this->chosen_month - 1] . ' ' . $this->chosen_year;
    }

    //-----------------------------------------

    // MAKE BIRTHDAY HTML (return HTML for bdays)

    //-----------------------------------------

    public function make_birthday_html($month, $day, $switch = 0)
    {
        global $ibforums, $DB, $std, $print;

        //-----------------------------------------

        // Get the birthdays from the database

        //-----------------------------------------

        $birthdays = [];

        $output = '';

        $DB->query("SELECT uid, uname, bday_year from xbb_members WHERE bday_month='" . $month . "' and bday_day='$day'");

        if (!$DB->get_num_rows()) {
            if (1 == $switch) {
                return;
            }

            $std->Error(['LEVEL' => 1, 'MSG' => 'cal_no_events']);
        } else {
            $output .= $this->html->cal_birthday_start();

            while (false !== ($r = $DB->fetch_row())) {
                $age = $this->chosen_year - $r['bday_year'];

                $output .= $this->html->cal_birthday_entry($r['uid'], $r['uname'], $age);
            }

            $output .= $this->html->cal_birthday_end();
        }

        return $output;
    }

    //-----------------------------------------

    public function get_month_dropdown($month = '')
    {
        global $ibforums;

        $return = '';

        if ('' == $month) {
            $month = $this->chosen_month;
        }

        for ($x = 1; $x <= 12; $x++) {
            $return .= "\t<option value='$x'";

            $return .= ($x == $month) ? ' selected' : '';

            $return .= '>' . $this->month_words[$x - 1] . "\n";
        }

        return $return;
    }

    //-----------------------------------------

    public function get_year_dropdown($year = '')
    {
        global $ibforums;

        $return = '';

        $ibforums->vars['start_year'] = $ibforums->vars['start_year'] ?? 2001;

        $ibforums->vars['year_limit'] = $ibforums->vars['year_limit'] ?? 5;

        if ('' == $year) {
            $year = $this->chosen_year;
        }

        for ($x = $ibforums->vars['start_year']; $x <= $this->now_date['year'] + $ibforums->vars['year_limit']; $x++) {
            $return .= "\t<option value='$x'";

            $return .= ($x == $year) ? ' selected' : '';

            $return .= '>' . $x . "\n";
        }

        return $return;
    }

    //-----------------------------------------

    public function get_day_dropdown($day = '')
    {
        global $ibforums;

        if ('' == $day) {
            $day = $this->now_date['mday'];
        }

        $return = '';

        for ($x = 1; $x <= 31; $x++) {
            $return .= "\t<option value='$x'";

            $return .= ($x == $day) ? ' selected' : '';

            $return .= '>' . $x . "\n";
        }

        return $return;
    }
}

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
|   > Board index module
|   > Module written by Matt Mecham
|   > Date started: 17th February 2002
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

$idx = new Boards();

class Boards
{
    public $output = '';

    public $base_url = '';

    public $html = '';

    public $output = '';

    public $forums = [];

    public $mods = [];

    public $cats = [];

    public $children = [];

    public $nav;

    public $news_topic_id = '';

    public $news_forum_id = '';

    public $news_title = '';

    public function __construct()
    {
        global $ibforums, $DB, $std, $print, $skin_universal;

        $this->base_url = "{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}";

        // Get more words for this invocation!

        $ibforums->lang = $std->load_words($ibforums->lang, 'lang_boards', $ibforums->lang_id);

        $this->html = $std->load_template('skin_boards');

        if (!$ibforums->member['uid']) {
            $ibforums->input['last_visit'] = time();
        }

        $this->output .= $this->html->PageTop($std->get_date($ibforums->input['last_visit'], 'LONG'));

        // Get the forums and category info from the DB

        $last_c_id = -1;

        $DB->query(
            'SELECT f.*, c.id as cat_id, c.position as cat_position, c.state as cat_state, c.name as cat_name, c.description as cat_desc,
        		   c.image, c.url, m.member_name as mod_name, m.member_id as mod_id, m.is_group, m.group_id, m.group_name, m.mid
        		   from ibf_forums f, ibf_categories c
        		   LEFT JOIN ibf_moderators m ON (f.id=m.forum_id)
			   WHERE f.category=c.id
        		   order by c.position, f.position'
        );

        while (false !== ($r = $DB->fetch_row())) {
            if ($last_c_id != $r['cat_id']) {
                $this->cats[$r['cat_id']] = [
                    'id' => $r['cat_id'],
'position' => $r['cat_position'],
'state' => $r['cat_state'],
'name' => $r['cat_name'],
'description' => $r['cat_desc'],
'image' => $r['image'],
'url' => $r['url'],
                ];

                $last_c_id = $r['cat_id'];
            }

            if ($r['parent_id'] > 0) {
                $this->children[$r['parent_id']][$r['id']] = $r;
            } else {
                $this->forums[$r['id']] = $r;
            }

            if ('' != $r['mod_id']) {
                $this->mods[$r['id']][$r['mid']] = [
                    'name' => $r['mod_name'],
'id' => $r['mod_id'],
'isg' => $r['is_group'],
'gname' => $r['group_name'],
'gid' => $r['group_id'],
                ];
            }
        }

        //-----------------------------------

        // What are we doing?

        //-----------------------------------

        if ('' != $ibforums->input['c']) {
            $this->show_single_cat();

            $this->nav[] = $this->cats[$ibforums->input['c']]['name'];
        } else {
            $this->process_all_cats();
        }

        //*********************************************/

        // Add in show online users

        //*********************************************/

        $active = [
            'TOTAL' => 0,
'NAMES' => '',
'GUESTS' => 0,
'MEMBERS' => 0,
'ANON' => 0,
        ];

        $stats_html = '';

        if ($ibforums->vars['show_active']) {
            if ('' == $ibforums->vars['au_cutoff']) {
                $ibforums->vars['au_cutoff'] = 15;
            }

            // Get the users from the DB

            $cut_off = $ibforums->vars['au_cutoff'] * 60;

            $time = time() - $cut_off;

            $DB->query(
                "SELECT s.member_id, s.member_name, s.login_type, g.suffix, g.prefix
			            FROM ibf_sessions s
			              LEFT JOIN ibf_groups g ON (g.g_id=s.member_group)
			            WHERE running_time > '$time'
			            ORDER BY s.running_time DESC"
            );

            // cache all printed members so we don't double print them

            $cached = [];

            while (false !== ($result = $DB->fetch_row())) {
                if (0 == $result['member_id']) {
                    $active['GUESTS']++;
                } else {
                    if (empty($cached[$result['member_id']])) {
                        $cached[$result['member_id']] = 1;

                        if (1 == $result['login_type']) {
                            if (($ibforums->member['mgroup'] == $ibforums->vars['admin_group']) and (1 != $ibforums->vars['disable_admin_anon'])) {
                                $active['NAMES'] .= "<span class='highlight'>&gt;</span><a href='{$ibforums->base_url}&act=Profile&MID={$result['member_id']}'>{$result['prefix']}{$result['member_name']}{$result['suffix']}</a>* \n";

                                $active['ANON']++;
                            } else {
                                $active['ANON']++;
                            }
                        } else {
                            $active['MEMBERS']++;

                            $active['NAMES'] .= "<span class='highlight'>&gt;</span><a href='{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}&act=Profile&MID={$result['member_id']}'>{$result['prefix']}{$result['member_name']}{$result['suffix']}</a> \n";
                        }
                    }
                }
            }

            $active['TOTAL'] = $active['MEMBERS'] + $active['GUESTS'] + $active['ANON'];

            // Show a link?

            if ($ibforums->vars['allow_online_list']) {
                $active['LINK'] = '[ ' . "<a href='" . $this->base_url . "&act=Online&CODE=listall'>" . $ibforums->lang['browser_user_list'] . '</a>' . ' ]';
            }

            $ibforums->lang['active_users'] = sprintf($ibforums->lang['active_users'], $ibforums->vars['au_cutoff']);

            $stats_html .= $this->html->ActiveUsers($active, $ibforums->vars['au_cutoff']);

            //-----------------------------------------------

            // Are we viewing the calendar?

            //-----------------------------------------------

            if ($ibforums->vars['show_birthdays']) {
                $user_time = time() + ($ibforums->member['timezone_offset'] * 3600);

                $date = getdate($user_time);

                $day = $date['mday'];

                $month = $date['mon'];

                $year = $date['year'];

                $birthstring = '';

                $count = 0;

                $DB->query("SELECT uid, uname, bday_day as DAY, bday_month as MONTH, bday_year as YEAR from xbb_members WHERE bday_day='$day' and bday_month='$month'");

                while (false !== ($user = $DB->fetch_row())) {
                    $birthstring .= "<span class='highlight'>&gt;</span><a href='{$this->base_url}&act=Profile&CODE=03&MID={$user['uid']}'>{$user['uname']}</a> ";

                    if ($user['YEAR']) {
                        $pyear = $year - $user['YEAR'];  // $year = 2002 and $user['YEAR'] = 1976

                        $birthstring .= "(<b>$pyear</b>) ";
                    }

                    $count++;
                }

                $lang = $ibforums->lang['no_birth_users'];

                if ($count > 0) {
                    $lang = ($count > 1) ? $ibforums->lang['birth_users'] : $ibforums->lang['birth_user'];
                } else {
                    $count = '';
                }

                $stats_html .= $this->html->birthdays($birthstring, $count, $lang);
            }
        }

        //-----------------------------------------------

        // Are we viewing the calendar?

        //-----------------------------------------------

        if ($ibforums->vars['show_calendar']) {
            if ($ibforums->vars['calendar_limit'] < 2) {
                $ibforums->vars['calendar_limit'] = 2;
            }

            $our_unix = time() + ($ibforums->member['timezone_offset'] * 3600);

            $max_date = $our_unix + ($ibforums->vars['calendar_limit'] * 86400);

            $DB->query("SELECT eventid, title, read_perms, priv_event, userid, unix_stamp FROM ibf_calendar_events WHERE unix_stamp > '$our_unix' and unix_stamp < '$max_date' ORDER BY unix_stamp ASC");

            $show_events = [];

            while (false !== ($event = $DB->fetch_row())) {
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

                $c_time = $std->get_date($event['unix_stamp'] - 86000, 'JOINED');

                $show_events[] = "<a href='{$ibforums->base_url}&act=calendar&code=showevent&eventid={$event['eventid']}' title='$c_time'>" . $event['title'] . '</a>';
            }

            if (count($show_events) > 0) {
                $event_string = implode(', ', $show_events);
            } else {
                $event_string = $ibforums->lang['no_calendar_events'];
            }

            $ibforums->lang['calender_f_title'] = sprintf($ibforums->lang['calender_f_title'], $ibforums->vars['calendar_limit']);

            $stats_html .= $this->html->calendar_events($event_string);
        }

        //*********************************************/

        // Add in show stats

        //*********************************************/

        if ($ibforums->vars['show_totals']) {
            $DB->query('SELECT * FROM ibf_stats');

            $stats = $DB->fetch_row();

            // Update the most active count if needed

            if ($active['TOTAL'] > $stats['MOST_COUNT']) {
                $DB->query("UPDATE ibf_stats SET MOST_DATE='" . time() . "', MOST_COUNT='" . $active[TOTAL] . "'");

                $stats['MOST_COUNT'] = $active[TOTAL];

                $stats['MOST_DATE'] = time();
            }

            $most_time = $std->get_date($stats['MOST_DATE'], 'LONG');

            $ibforums->lang['most_online'] = str_replace('<#NUM#>', $stats['MOST_COUNT'], $ibforums->lang['most_online']);

            $ibforums->lang['most_online'] = str_replace('<#DATE#>', $most_time, $ibforums->lang['most_online']);

            $total_posts = $stats['TOTAL_REPLIES'] + $stats['TOTAL_TOPICS'];

            if ('none' != $ibforums->vars['number_format']) {
                $ibforums->vars['number_format'] = ('space' == $ibforums->vars['number_format']) ? ' ' : $ibforums->vars['number_format'];

                $total_posts = number_format($total_posts, 0, '', $ibforums->vars['number_format']);

                $stats['MEM_COUNT'] = number_format($stats['MEM_COUNT'], 0, '', $ibforums->vars['number_format']);
            }

            $link = $ibforums->base_url . '&act=Profile&MID=' . $stats['LAST_MEM_ID'];

            $ibforums->lang['total_word_string'] = str_replace('<#posts#>', (string)$total_posts, $ibforums->lang['total_word_string']);

            $ibforums->lang['total_word_string'] = str_replace('<#reg#>', $stats['MEM_COUNT'], $ibforums->lang['total_word_string']);

            $ibforums->lang['total_word_string'] = str_replace('<#mem#>', $stats['LAST_MEM_NAME'], $ibforums->lang['total_word_string']);

            $ibforums->lang['total_word_string'] = str_replace('<#link#>', $link, $ibforums->lang['total_word_string']);

            $stats_html .= $this->html->ShowStats($ibforums->lang['total_word_string']);
        }

        if ('' != $stats_html) {
            $this->output .= $this->html->stats_header();

            $this->output .= $stats_html;

            $this->output .= $this->html->stats_footer();
        }

        // Add in board info footer

        $this->output .= $this->html->BoardInformation();

        // Check for news forum.

        if ($this->news_title and $this->news_topic_id and $this->news_forum_id) {
            $t_html = $this->html->newslink($this->news_forum_id, stripslashes($this->news_title), $this->news_topic_id);

            $this->output = str_replace('<!-- IBF.NEWSLINK -->', (string)$t_html, $this->output);
        }

        // Display quick log in if we're not a member

        if ($ibforums->member['uid'] < 1) {
            $this->output = str_replace('<!--IBF.QUICK_LOG_IN-->', $this->html->quick_log_in(), $this->output);
        }

        $print->add_output((string)$this->output);

        $cp = ' (Powered by Invision Power Board)';

        if ($ibforums->vars['ips_cp_purchase']) {
            $cp = '';
        }

        $print->do_output(['TITLE' => $ibforums->vars['board_name'] . $cp, 'JS' => 0, 'NAV' => $this->nav]);
    }

    //*********************************************/

    // SHOW A SUB FORUM

    //*********************************************/

    //*********************************************/

    // PROCESS ALL CATEGORIES

    //*********************************************/

    public function process_all_cats()
    {
        global $std, $DB, $ibforums;

        foreach ($this->cats as $cat_id => $cat_data) {
            //----------------------------

            // Is this category turned on?

            //----------------------------

            if (!$cat_data['state']) {
                continue;
            }

            foreach ($this->forums as $forum_id => $forum_data) {
                if ($forum_data['category'] == $cat_id) {
                    //-----------------------------------

                    // We store the HTML in a temp var so

                    // we can make sure we have cats for

                    // this forum, or hidden forums with a

                    // cat will show the cat strip - we don't

                    // want that, no - we don't.

                    //-----------------------------------

                    $temp_html .= $this->process_forum($forum_id, $forum_data);
                }
            }

            if ('' != $temp_html) {
                $this->output .= $this->html->CatHeader_Expanded($cat_data);

                $this->output .= $temp_html;

                $this->output .= $this->html->end_this_cat();
            }

            unset($temp_html);
        }

        $this->output .= $this->html->end_all_cats();
    }

    //*********************************************/

    // SHOW A SINGLE CATEGORY

    //*********************************************/

    public function show_single_cat()
    {
        global $std, $DB, $ibforums;

        $cat_id = $ibforums->input['c'];

        if (!is_array($this->cats[$cat_id])) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'missing_files']);
        }

        $cat_data = $this->cats[$cat_id];

        //----------------------------

        // Is this category turned on?

        //----------------------------

        if (!$cat_data['state']) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'missing_files']);
        }

        foreach ($this->forums as $forum_id => $forum_data) {
            if ($forum_data['category'] == $cat_id) {
                //-----------------------------------

                // We store the HTML in a temp var so

                // we can make sure we have cats for

                // this forum, or hidden forums with a

                // cat will show the cat strip - we don't

                // want that, no - we don't.

                //-----------------------------------

                $temp_html .= $this->process_forum($forum_id, $forum_data);
            }
        }

        if ('' != $temp_html) {
            $this->output .= $this->html->CatHeader_Expanded($cat_data);

            $this->output .= $temp_html;

            $this->output .= $this->html->end_this_cat();
        } else {
            $std->Error(['LEVEL' => 1, 'MSG' => 'missing_files']);
        }

        unset($temp_html);

        $this->output .= $this->html->end_all_cats();
    }

    //*********************************************/

    // RENDER A FORUM

    //*********************************************/

    public function process_forum($forum_id = '', $forum_data = '')
    {
        global $std, $ibforums;

        if (1 == $forum_data['subwrap']) {
            $printed_children = 0;

            $can_see_root = false;

            //--------------------------------------

            // This is a sub cat forum...

            //--------------------------------------

            // Do we have any sub forums here?

            if ((isset($this->children[$forum_data['id']])) and (count($this->children[$forum_data['id']]) > 0)) {
                // Are we allowed to see the postable forum stuff?

                if (1 == $forum_data['sub_can_post']) {
                    if ('*' == $forum_data['read_perms']) {
                        $forum_data['fid'] = $forum_data['id'];

                        $newest = $forum_data;

                        $can_see_root = true;
                    } elseif (preg_match('/(^|,)' . $ibforums->member['mgroup'] . '(,|$)/', $forum_data['read_perms'])) {
                        $forum_data['fid'] = $forum_data['id'];

                        $newest = $forum_data;

                        $can_see_root = true;
                    } else {
                        $newest = [];
                    }
                }

                foreach ($this->children[$forum_data['id']] as $idx => $data) {
                    //--------------------------------------

                    // Check permissions...

                    //--------------------------------------

                    if ('*' != $data['read_perms']) {
                        if (!preg_match('/(^|,)' . $ibforums->member['mgroup'] . '(,|$)/', $data['read_perms'])) {
                            continue;
                        }
                    }

                    // Do the news stuff first

                    if (isset($data['last_title']) and '' != $data['last_id']) {
                        if ((1 == $ibforums->vars['index_news_link']) and (!empty($ibforums->vars['news_forum_id'])) and ($ibforums->vars['news_forum_id'] == $data['id'])) {
                            $this->news_topic_id = $data['last_id'];

                            $this->news_forum_id = $data['id'];

                            $this->news_title = $data['last_title'];
                        }
                    }

                    if ($data['last_post'] > $newest['last_post']) {
                        $newest['last_post'] = $data['last_post'];

                        $newest['fid'] = $data['id'];

                        //$newest['id']               = $data['id'];

                        $newest['last_id'] = $data['last_id'];

                        $newest['last_title'] = $data['last_title'];

                        $newest['password'] = $data['password'];

                        $newest['last_poster_id'] = $data['last_poster_id'];

                        $newest['last_poster_name'] = $data['last_poster_name'];

                        $newest['status'] = $data['status'];
                    }

                    $newest['posts'] += $data['posts'];

                    $newest['topics'] += $data['topics'];

                    $printed_children++;
                }

                if (($printed_children < 1) && (true !== $can_see_root)) {
                    // If we don't have permission to view any forums

                    // and we can't post in this root forum

                    // then simply return and the row won't be printed

                    return '';
                }

                // Fix up the last of the data

                if (mb_strlen($newest['last_title']) > 30) {
                    $newest['last_title'] = str_replace('&#33;', '!', $newest['last_title']);

                    $newest['last_title'] = str_replace('&quot;', '"', $newest['last_title']);

                    $newest['last_title'] = mb_substr($newest['last_title'], 0, 27) . '...';

                    $newest['last_title'] = preg_replace('/&(#(\d+;?)?)?\.\.\.$/', '...', $newest['last_title']);
                }

                if ('' != $newest['password']) {
                    $newest['last_topic'] = $ibforums->lang['f_protected'];
                } elseif ('' != $newest['last_title']) {
                    $newest['last_topic'] = "<a href='{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}&act=ST&f={$newest['fid']}&t={$newest['last_id']}&view=getlastpost'>{$newest['last_title']}</a>";
                } else {
                    $newest['last_topic'] = $ibforums->lang['f_none'];
                }

                if (isset($newest['last_poster_name'])) {
                    $newest['last_poster'] = $newest['last_poster_id'] ? "<a href='{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}&act=Profile&CODE=03&MID={$newest['last_poster_id']}'>{$newest['last_poster_name']}</a>" : $newest['last_poster_name'];
                } else {
                    $newest['last_poster'] = $ibforums->lang['f_none'];
                }

                $newest['img_new_post'] = $std->forum_new_posts($newest, 1);

                $newest['last_post'] = $std->get_date($newest['last_post'], 'LONG');

                if ('none' != $ibforums->vars['number_format']) {
                    $ibforums->vars['number_format'] = ('space' == $ibforums->vars['number_format']) ? ' ' : $ibforums->vars['number_format'];

                    $newest['posts'] = number_format($newest['posts'], 0, '', $ibforums->vars['number_format']);

                    $newest['topics'] = number_format($newest['topics'], 0, '', $ibforums->vars['number_format']);
                }

                foreach ($newest as $k => $v) {
                    if ('id' == $k) {
                        continue;
                    }

                    $forum_data[$k] = $v;
                }

                $forum_data['moderator'] = $this->get_moderators($forum_id);

                return $this->html->ForumRow($forum_data);
            }

            return '';
        }

        //--------------------------------------

        // Check permissions...

        //--------------------------------------

        if ('*' != $forum_data['read_perms']) {
            if (!preg_match('/(^|,)' . $ibforums->member['mgroup'] . '(,|$)/', $forum_data['read_perms'])) {
                return '';
            }
        }

        $forum_data['img_new_post'] = $std->forum_new_posts($forum_data);

        $forum_data['last_post'] = $std->get_date($forum_data['last_post'], 'LONG');

        $forum_data['last_topic'] = $ibforums->lang['f_none'];

        if (isset($forum_data['last_title']) and '' != $forum_data['last_id']) {
            if ((1 == $ibforums->vars['index_news_link']) and (!empty($ibforums->vars['news_forum_id'])) and ($ibforums->vars['news_forum_id'] == $forum_data['id'])) {
                $this->news_topic_id = $forum_data['last_id'];

                $this->news_forum_id = $forum_data['id'];

                $this->news_title = $forum_data['last_title'];
            }

            $forum_data['last_title'] = str_replace('&#33;', '!', $forum_data['last_title']);

            $forum_data['last_title'] = str_replace('&quot;', '"', $forum_data['last_title']);

            if (mb_strlen($forum_data['last_title']) > 30) {
                $forum_data['last_title'] = mb_substr($forum_data['last_title'], 0, 27) . '...';

                $forum_data['last_title'] = preg_replace('/&(#(\d+;?)?)?\.\.\.$/', '...', $forum_data['last_title']);
            }

            if ('' != $forum_data['password']) {
                $forum_data['last_topic'] = $ibforums->lang['f_protected'];
            } else {
                $forum_data['last_topic'] = "<a href='{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}&act=ST&f={$forum_data['id']}&t={$forum_data['last_id']}&view=getlastpost'>{$forum_data['last_title']}</a>";
            }
        }

        if (isset($forum_data['last_poster_name'])) {
            $forum_data['last_poster'] = $forum_data['last_poster_id'] ? "<a href='{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}&act=Profile&CODE=03&MID={$forum_data['last_poster_id']}'>{$forum_data['last_poster_name']}</a>" : $forum_data['last_poster_name'];
        } else {
            $forum_data['last_poster'] = $ibforums->lang['f_none'];
        }

        //---------------------------------

        // Moderators

        //---------------------------------

        $forum_data['moderator'] = $this->get_moderators($forum_data['id']);

        if ('none' != $ibforums->vars['number_format']) {
            $ibforums->vars['number_format'] = ('space' == $ibforums->vars['number_format']) ? ' ' : $ibforums->vars['number_format'];

            $forum_data['posts'] = number_format($forum_data['posts'], 0, '', $ibforums->vars['number_format']);

            $forum_data['topics'] = number_format($forum_data['topics'], 0, '', $ibforums->vars['number_format']);
        }

        return $this->html->ForumRow($forum_data);
    }

    //-------------------------------------

    // Return mods for this forum in a

    // HTML formatted string

    //-------------------------------------

    public function get_moderators($forum_id = '')
    {
        global $ibforums, $std, $DB;

        $mod_string = '';

        if ('' == $forum_id) {
            return '';
        }

        if (isset($this->mods[$forum_id])) {
            $mod_string = $ibforums->lang['forum_leader'] . ' ';

            if (is_array($this->mods[$forum_id])) {
                foreach ($this->mods[$forum_id] as $moderator) {
                    if (1 == $moderator['isg']) {
                        $mod_string .= "<a href='{$ibforums->base_url}&act=Members&max_results=30&filter={$moderator['gid']}&sort_order=asc&sort_key=name&st=0'>{$moderator['gname']}</a>, ";
                    } else {
                        $mod_string .= "<a href='{$ibforums->base_url}&act=Profile&CODE=03&MID={$moderator['id']}'>{$moderator['name']}</a>, ";
                    }
                }

                $mod_string = preg_replace("!,\s+$!", '', $mod_string);
            } else {
                if (1 == $moderator['isg']) {
                    $mod_string .= "<a href='{$ibforums->base_url}&act=Members&max_results=30&filter={$this->mods[$forum_id]['gid']}&sort_order=asc&sort_key=name&st=0'>{$this->mods[$forum_id]['gname']}</a>, ";
                } else {
                    $mod_string .= "<a href='{$ibforums->base_url}&act=Profile&CODE=03&MID={$this->mods[$forum_id]['id']}'>{$this->mods[$forum_id]['name']}</a>";
                }
            }
        }

        return $mod_string;
    }
}

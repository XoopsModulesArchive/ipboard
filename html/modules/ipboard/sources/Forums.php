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
|   > Forum topic index module
|   > Module written by Matt Mecham
|   > Date started: 14th February 2002
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

$idx = new Forums();

class Forums
{
    public $output = '';

    public $base_url = '';

    public $html = '';

    public $moderator = [];

    public $forum = [];

    public $mods = [];

    public $show_dots = '';

    public $nav_extra = '';

    public $read_array = [];

    public $board_html = '';

    public $sub_output = '';

    public $pinned_print = 0;

    //+----------------------------------------------------------------

    // Our constructor, load words, load skin, get DB forum/cat data

    //+----------------------------------------------------------------

    public function __construct()
    {
        global $ibforums, $DB, $std, $print, $skin_universal;

        $ibforums->input['f'] = (int)$ibforums->input['f'];

        $ibforums->lang = $std->load_words($ibforums->lang, 'lang_forum', $ibforums->lang_id);

        $this->html = $std->load_template('skin_forum');

        // Get the forum info based on the forum ID, and get the category name, ID, etc.

        $DB->query(
            'SELECT f.*, c.id as cat_id, c.name as cat_name
        			FROM ibf_forums f
        			  LEFT JOIN ibf_categories c ON (c.id=f.category)
        			WHERE f.id=' . $ibforums->input['f']
        );

        $this->forum = $DB->fetch_row();

        //----------------------------------------

        // Error out if we can not find the forum

        //----------------------------------------

        if (!$this->forum['id']) {
            $std->Error([LEVEL => 1, MSG => 'missing_files']);
        }

        //----------------------------------------

        // If this is a sub forum, we need to get

        // the cat details, and parent details

        //----------------------------------------

        if ($this->forum['parent_id'] > 0) {
            $DB->query("SELECT f.id as forum_id, f.name as forum_name, c.id, c.name FROM ibf_forums f, ibf_categories c WHERE f.id='" . $this->forum['parent_id'] . "' AND c.id=f.category");

            $row = $DB->fetch_row();

            $this->forum['cat_id'] = $row['id'];

            $this->forum['cat_name'] = $row['name'];

            $this->nav_extra = "<a href='" . $ibforums->base_url . "&act=SF&f={$row['forum_id']}'>{$row['forum_name']}</a>";
        }

        //--------------------------------------------------------------------------------

        //--------------------------------------------------------------------------------

        $this->base_url = "{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}";

        $this->forum['FORUM_JUMP'] = $std->build_forum_jump();

        $this->forum['FORUM_JUMP'] = preg_replace('!#Forum Jump#!', $ibforums->lang['forum_jump'], $this->forum['FORUM_JUMP']);

        // Are we viewing the forum, or viewing the forum rules?

        if ('SR' == $ibforums->input['act']) {
            $this->show_rules();
        } else {
            if (1 == $this->forum['subwrap']) {
                $this->show_subforums();

                if ($this->forum['sub_can_post']) {
                    $this->show_forum();
                } else {
                    // No forum to show, just use the HTML in $this->sub_output

                    // or there will be no HTML to use in the str_replace!

                    $this->output = $this->sub_output;

                    $this->sub_output = '';
                }
            } else {
                $this->show_forum();
            }
        }

        //+----------------------------------------------------------------

        // Print it

        //+----------------------------------------------------------------

        if ('' != $this->sub_output) {
            $this->output = str_replace('<!--IBF.SUBFORUMS-->', $this->sub_output, $this->output);
        }

        if ($ibforums->member['uid'] > 0) {
            $this->output = str_replace('<!--IBF.SUB_FORUM_LINK-->', $this->html->show_sub_link($this->forum['id']), $this->output);
        }

        $print->add_output($this->output);

        $print->do_output(
            [
                'TITLE' => $ibforums->vars['board_name'] . ' -> ' . $this->forum['name'],
'JS' => 0,
'NAV' => [
                    "<a href='" . $this->base_url . "&act=SC&c={$this->forum['cat_id']}'>{$this->forum['cat_name']}</a>",
                    $this->nav_extra,
                    "<a href='" . $this->base_url . "&act=SF&f={$this->forum['id']}'>{$this->forum['name']}</a>",
                ],
            ]
        );
    }

    //+----------------------------------------------------------------

    // Display any sub forums

    //+----------------------------------------------------------------

    public function show_subforums()
    {
        global $std, $DB, $ibforums;

        $ibforums->lang = $std->load_words($ibforums->lang, 'lang_boards', $ibforums->lang_id);

        $this->board_html = $std->load_template('skin_boards');

        $fid = $ibforums->input['f'];

        $DB->query(
            "SELECT f.*, m.member_name as mod_name, m.member_id as mod_id, m.is_group, m.group_id, m.group_name, m.mid
        			FROM ibf_forums f
        			 LEFT JOIN ibf_moderators m ON (f.id=m.forum_id)
        			WHERE parent_id='$fid'
        			ORDER BY position"
        );

        if (!$DB->get_num_rows()) {
            return '';
        }

        while (false !== ($r = $DB->fetch_row())) {
            $this->forums[$r['id']] = $r;

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

        foreach ($this->forums as $data) {
            $temp_html .= $this->process_forum($data['id'], $data);
        }

        if ('' != $temp_html) {
            $this->sub_output .= $this->board_html->subheader();

            $this->sub_output .= $temp_html;

            $this->sub_output .= $this->board_html->end_this_cat();
        } else {
            return $this->sub_output;
        }

        unset($temp_html);

        $this->sub_output .= $this->board_html->end_all_cats();
    }

    public function process_forum($forum_id = '', $forum_data = '')
    {
        global $std, $ibforums;

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

        $forum_data['moderator'] = '';

        if (isset($this->mods[$forum_data['id']])) {
            $forum_data['moderator'] = $ibforums->lang['forum_leader'] . ' ';

            if (is_array($this->mods[$forum_data['id']])) {
                foreach ($this->mods[$forum_data['id']] as $moderator) {
                    if (1 == $moderator['isg']) {
                        $forum_data['moderator'] .= "<a href='{$ibforums->base_url}&act=Members&max_results=30&filter={$moderator['gid']}&sort_order=asc&sort_key=name&st=0'>{$moderator['gname']}</a>, ";
                    } else {
                        $forum_data['moderator'] .= "<a href='{$ibforums->base_url}&act=Profile&CODE=03&MID={$moderator['id']}'>{$moderator['name']}</a>, ";
                    }
                }

                $forum_data['moderator'] = preg_replace("!,\s+$!", '', $forum_data['moderator']);
            } else {
                if (1 == $moderator['isg']) {
                    $forum_data['moderator'] .= "<a href='{$ibforums->base_url}&act=Members&max_results=30&filter={$this->mods[$forum_data['id']]['gid']}&sort_order=asc&sort_key=name&st=0'>{$this->mods[$forum_data['id']]['gname']}</a>, ";
                } else {
                    $forum_data['moderator'] .= "<a href='{$ibforums->base_url}&act=Profile&CODE=03&MID={$this->mods[$forum_data['id']]['id']}'>{$this->mods[$forum_data['id']]['name']}</a>";
                }
            }
        }

        return $this->board_html->ForumRow($forum_data);
    }

    //+----------------------------------------------------------------

    // Show the forum rules on a seperate page

    //+----------------------------------------------------------------

    public function show_rules()
    {
        global $DB, $ibforums, $std, $print;

        //+--------------------------------------------

        // Do we have permission to view these rules?

        //+--------------------------------------------

        $bad_entry = $this->check_access();

        if (1 == $bad_entry) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_view_topic']);
        }

        //+--------------------------------------------

        // Get the rules from the DB

        //+--------------------------------------------

        $DB->query("SELECT * FROM ibf_rules WHERE fid='" . $ibforums->input['f'] . "'");

        if ($rules = $DB->fetch_row()) {
            $rules['title'] = stripslashes($rules['title']);

            $rules['body'] = stripslashes($rules['body']);

            $this->output .= $this->html->show_rules($rules);

            $print->add_output((string)$this->output);

            $print->do_output(
                [
                    'TITLE' => $ibforums->vars['board_name'] . ' -> ' . $this->forum['name'],
'JS' => 0,
'NAV' => [
                        "<a href='" . $this->base_url . "&act=SC&c={$this->forum['cat_id']}'>{$this->forum['cat_name']}</a>",
                    ],
                ]
            );
        } else {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_view_topic']);
        }
    }

    //+----------------------------------------------------------------

    // Forum view check for authentication

    //+----------------------------------------------------------------

    public function show_forum()
    {
        global $ibforums;

        // are we checking for user authentication via the log in form

        // for a private forum w/password protection?

        1 == $ibforums->input['L'] ? $this->authenticate_user() : $this->render_forum();
    }

    //+----------------------------------------------------------------

    // Authenicate the log in for a password protected forum

    //+----------------------------------------------------------------

    public function authenticate_user()
    {
        global $std, $ibforums, $print;

        if ('' == $ibforums->input['f_password']) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'pass_blank']);
        }

        if ($ibforums->input['f_password'] != $this->forum['password']) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'wrong_pass']);
        }

        $std->my_setcookie('iBForum' . $this->forum['id'], $ibforums->input['f_password']);

        $print->redirect_screen($ibforums->lang['logged_in'], 'act=SF&f=' . $this->forum['id']);
    }

    //+----------------------------------------------------------------------------------

    public function check_access()
    {
        global $ibforums, $HTTP_COOKIE_VARS;

        $return = 1;

        if ('*' == $this->forum['read_perms']) {
            $return = 0;
        } elseif (preg_match('/(^|,)' . $ibforums->member['mgroup'] . '(,|$)/', $this->forum['read_perms'])) {
            $return = 0;
        }

        // Do we have permission to even see the password page?

        if (0 == $return) {
            if ($this->forum['password']) {
                if ($HTTP_COOKIE_VARS[$ibforums->vars['cookie_id'] . 'iBForum' . $this->forum['id']] == $this->forum['password']) {
                    $return = 0;
                } else {
                    $this->forum_login();
                }
            }
        }

        return $return;
    }

    //+----------------------------------------------------------------------------------

    public function forum_login()
    {
        global $ibforums, $std, $DB, $HTTP_COOKIE_VARS, $print;

        if (empty($ibforums->member['uid'])) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_guests']);
        }

        $this->output = $this->html->Forum_log_in($this->forum['id']);

        $print->add_output((string)$this->output);

        $print->do_output(
            [
                'TITLE' => $ibforums->vars['board_name'] . ' -> ' . $this->forum['name'],
'JS' => 0,
'NAV' => [
                    "<a href='" . $this->base_url . "&act=SC&c={$this->forum['cat_id']}'>{$this->forum['cat_name']}</a>",
                    "<a href='" . $this->base_url . "&act=SF&f={$this->forum['id']}'>{$this->forum['name']}</a>",
                ],
            ]
        );
    }

    //+----------------------------------------------------------------

    // Main render forum engine

    //+----------------------------------------------------------------

    public function render_forum()
    {
        global $ibforums, $DB, $std, $print, $HTTP_COOKIE_VARS;

        $bad_entry = $this->check_access();

        if (1 == $bad_entry) {
            if (1 == $this->forum['subwrap']) {
                // Dont' show an error as we may have sub forums up top

                // Instead, copy the sub forum ouput to the main output

                // and return gracefully

                $this->output = $this->sub_output;

                $this->sub_output = '';

                return true;
            }

            $std->Error([LEVEL => 1, MSG => 'no_permission']);
        }

        if ($read = $std->my_getcookie('topicsread')) {
            $this->read_array = unserialize(stripslashes($read));
        }

        if ($forum = $std->my_getcookie('fread_' . $ibforums->input['f'])) {
            $ibforums->input['last_visit'] = $forum > $ibforums->input['last_visit'] ? $forum : $ibforums->input['last_visit'];
        }

        $prune_value = $std->select_var(
            [
                1 => $ibforums->input['prune_day'],
2 => $this->forum['prune'],
3 => '100',
            ]
        );

        $sort_key = $std->select_var(
            [
                1 => $ibforums->input['sort_key'],
2 => $this->forum['sort_key'],
3 => 'last_post',
            ]
        );

        $sort_by = $std->select_var(
            [
                1 => $ibforums->input['sort_by'],
2 => $this->forum['sort_order'],
3 => 'Z-A',
            ]
        );

        $First = $std->select_var(
            [
                1 => (int)$ibforums->input['st'],
                2 => 0,
            ]
        );

        // Figure out sort order, day cut off, etc

        $Prune = 100 != $prune_value ? (time() - ($prune_value * 60 * 60 * 24)) : 0;

        $sort_keys = [
            'last_post' => 'sort_by_date',
'title' => 'sort_by_topic',
'starter_name' => 'sort_by_poster',
'posts' => 'sort_by_replies',
'views' => 'sort_by_views',
'start_date' => 'sort_by_start',
'last_poster_name' => 'sort_by_last_poster',
        ];

        $prune_by_day = [
            '1' => 'show_today',
'5' => 'show_5_days',
'7' => 'show_7_days',
'10' => 'show_10_days',
'15' => 'show_15_days',
'20' => 'show_20_days',
'25' => 'show_25_days',
'30' => 'show_30_days',
'60' => 'show_60_days',
'90' => 'show_90_days',
'100' => 'show_all',
        ];

        $sort_by_keys = [
            'Z-A' => 'descending_order',
'A-Z' => 'ascending_order',
        ];

        //+----------------------------------------------------------------

        // check for any form funny business by wanna-be hackers

        //+----------------------------------------------------------------

        if ((!isset($sort_keys[$sort_key])) and (!isset($prune_by_day[$prune_value])) and (!isset($sort_by_keys[$sort_by]))) {
            $std->Error([LEVEL => 5, MSG => 'incorrect_use']);
        }

        $r_sort_by = 'A-Z' == $sort_by ? 'ASC' : 'DESC';

        //+----------------------------------------------------------------

        // Query the database to see how many topics there are in the forum

        //+----------------------------------------------------------------

        $DB->query("SELECT COUNT(tid) as max FROM ibf_topics WHERE forum_id='" . $this->forum['id'] . "' and approved='1' and (last_post > $Prune or pinned=1)");

        $total_possible = $DB->fetch_row();

        //+----------------------------------------------------------------

        // Generate the forum page span links

        //+----------------------------------------------------------------

        $this->forum['SHOW_PAGES'] = $std->build_pagelinks(
            [
                'TOTAL_POSS' => $total_possible[max],
'PER_PAGE' => $ibforums->vars['display_max_topics'],
'CUR_ST_VAL' => $ibforums->input['st'],
'L_SINGLE' => $ibforums->lang['single_page_forum'],
'L_MULTI' => $ibforums->lang['multi_page_forum'],
'BASE_URL' => $this->base_url . '&act=SF&f=' . $this->forum['id'] . "&prune_day=$prune_value&sort_by=$sort_by&sort_key=$sort_key",
            ]
        );

        //+----------------------------------------------------------------

        // Do we have any rules to show?

        //+----------------------------------------------------------------

        if ($this->forum['show_rules']) {
            $DB->query("SELECT * from ibf_rules WHERE fid='" . $this->forum['id'] . "'");

            if ($rules = $DB->fetch_row()) {
                $rules['title'] = stripslashes($rules['title']);

                $rules['body'] = stripslashes($rules['body']);

                $this->output .= $rules['show_all'] ? $this->html->show_rules_full($rules) : $this->html->show_rules_link($rules);
            }
        }

        //+----------------------------------------------------------------

        // Generate the poll button

        //+----------------------------------------------------------------

        $this->forum['POLL_BUTTON'] = $this->forum['allow_poll'] ? "<a href='" . $this->base_url . '&act=Post&CODE=10&f=' . $this->forum['id'] . "'><{A_POLL}></a>" : '';

        //+----------------------------------------------------------------

        // Start printing the page

        //+----------------------------------------------------------------

        $this->output .= $this->html->PageTop($this->forum);

        //+----------------------------------------------------------------

        // Do we have any topics to show?

        //+----------------------------------------------------------------

        if ($total_possible['max'] < 1) {
            $this->output .= $this->html->show_no_matches();
        }

        $total_topics_printed = 0;

        if ((1 == $ibforums->vars['show_user_posted']) and ($ibforums->member['uid'])) {
            $query = "SELECT DISTINCT ibf_posts.author_id, ibf_topics.* from ibf_topics LEFT JOIN ibf_posts ON (ibf_topics.tid = ibf_posts.topic_id AND ibf_posts.author_id = '"
                     . $ibforums->member['uid']
                     . "') WHERE ibf_topics.forum_id='"
                     . $this->forum['id']
                     . "' and (ibf_topics.last_post > '$Prune' OR ibf_topics.pinned='1') and ibf_topics.approved='1'";
        } else {
            $query = "SELECT * from ibf_topics WHERE forum_id='" . $this->forum['id'] . "' and approved='1' and (last_post > '$Prune' OR pinned='1')";
        }

        //+----------------------------------------------------------------

        // Do we have permission to view other posters topics?

        //+----------------------------------------------------------------

        if (!$ibforums->member['g_other_topics']) {
            $query .= " and starter_id='" . $ibforums->member['uid'] . "'";
        }

        //+----------------------------------------------------------------

        // Finish off the query

        //+----------------------------------------------------------------

        $First = $First ?: 0;

        $query .= " ORDER BY pinned DESC, $sort_key $r_sort_by LIMIT $First," . $ibforums->vars['display_max_topics'];

        $DB->query($query);

        //+----------------------------------------------------------------

        // Grab the rest of the topics and print them

        //+----------------------------------------------------------------

        while (false !== ($topic = $DB->fetch_row())) {
            $this->output .= $this->render_entry($topic);

            $total_topics_printed++;
        }

        //+----------------------------------------------------------------

        // Finish off the rest of the page

        //+----------------------------------------------------------------

        $ibforums->lang['showing_text'] = preg_replace('/<#MATCHED_TOPICS#>/', $total_topics_printed, $ibforums->lang['showing_text']);

        $ibforums->lang['showing_text'] = preg_replace('/<#TOTAL_TOPICS#>/', $total_possible['max'], $ibforums->lang['showing_text']);

        $sort_key_html = "<select name='sort_key'  class='forminput'>\n";

        $prune_day_html = "<select name='prune_day' class='forminput'>\n";

        $sort_by_html = "<select name='sort_by'   class='forminput'>\n";

        foreach ($sort_by_keys as $k => $v) {
            $sort_by_html .= $k == $sort_by ? "<option value='$k' selected>" . $ibforums->lang[$sort_by_keys[$k]] . "\n" : "<option value='$k'>" . $ibforums->lang[$sort_by_keys[$k]] . "\n";
        }

        foreach ($sort_keys as $k => $v) {
            $sort_key_html .= $k == $sort_key ? "<option value='$k' selected>" . $ibforums->lang[$sort_keys[$k]] . "\n" : "<option value='$k'>" . $ibforums->lang[$sort_keys[$k]] . "\n";
        }

        foreach ($prune_by_day as $k => $v) {
            $prune_day_html .= $k == $prune_value ? "<option value='$k' selected>" . $ibforums->lang[$prune_by_day[$k]] . "\n" : "<option value='$k'>" . $ibforums->lang[$prune_by_day[$k]] . "\n";
        }

        $ibforums->lang['sort_text'] = preg_replace('!<#SORT_KEY_HTML#>!', "$sort_key_html</select>", $ibforums->lang['sort_text']);

        $ibforums->lang['sort_text'] = preg_replace('!<#ORDER_HTML#>!', "$sort_by_html</select>", $ibforums->lang['sort_text']);

        $ibforums->lang['sort_text'] = preg_replace('!<#PRUNE_HTML#>!', "$prune_day_html</select>", $ibforums->lang['sort_text']);

        $this->output .= $this->html->TableEnd($this->forum);

        //+----------------------------------------------------------------

        // Process users active in this forum

        //+----------------------------------------------------------------

        if (1 != $ibforums->vars['no_au_forum']) {
            //+-----------------------------------------

            // Is this forum restricted, or global?

            //+-----------------------------------------

            if ('*' != $this->forum['read_perms']) {
                $q_extra = ' AND s.member_group IN (-2,' . $this->forum['read_perms'] . ') ';
            }

            //+-----------------------------------------

            // Get the users

            //+-----------------------------------------

            $cut_off = ('' != $ibforums->vars['au_cutoff']) ? $ibforums->vars['au_cutoff'] * 60 : 900;

            $time = time() - $cut_off;

            $DB->query(
                "SELECT s.member_id, s.member_name, s.login_type, s.location, g.suffix, g.prefix
					    FROM ibf_sessions s
					     LEFT JOIN ibf_groups g ON (g.g_id=s.member_group)
					    WHERE s.in_forum='{$this->forum['id']}'
					    AND s.running_time > '$time'" . $q_extra . 'ORDER BY s.running_time DESC'
            );

            //+-----------------------------------------

            // Cache all printed members so we don't double print them

            //+-----------------------------------------

            $cached = [];

            $active = ['guests' => 0, 'anon' => 0, 'members' => 0, 'names' => ''];

            while (false !== ($result = $DB->fetch_row())) {
                if (0 == $result['member_id']) {
                    $active['guests']++;
                } else {
                    if (empty($cached[$result['member_id']])) {
                        $cached[$result['member_id']] = 1;

                        if (1 == $result['login_type']) {
                            if (($ibforums->member['mgroup'] == $ibforums->vars['admin_group']) and (1 != $ibforums->vars['disable_admin_anon'])) {
                                $active['names'] .= "<a href='{$ibforums->base_url}&act=Profile&MID={$result['member_id']}'>{$result['prefix']}{$result['member_name']}{$result['suffix']}</a>*, ";

                                $active['anon']++;
                            } else {
                                $active['anon']++;
                            }
                        } else {
                            $active['members']++;

                            $active['names'] .= "<a href='{$ibforums->base_url}&act=Profile&MID={$result['member_id']}'>{$result['prefix']}{$result['member_name']}{$result['suffix']}</a>, ";
                        }
                    }
                }
            }

            $active['names'] = preg_replace("/,\s+$/", '', $active['names']);

            $ibforums->lang['active_users_title'] = sprintf($ibforums->lang['active_users_title'], ($active['members'] + $active['guests'] + $active['anon']));

            $ibforums->lang['active_users_detail'] = sprintf($ibforums->lang['active_users_detail'], $active['guests'], $active['anon']);

            $ibforums->lang['active_users_members'] = sprintf($ibforums->lang['active_users_members'], $active['members']);

            $this->output = str_replace('<!--IBF.FORUM_ACTIVE-->', $this->html->forum_active_users($active), $this->output);
        }

        return true;
    }

    //+----------------------------------------------------------------

    // Crunches the data into pwetty html

    //+----------------------------------------------------------------

    public function render_entry($topic)
    {
        global $DB, $std, $ibforums;

        $topic['last_text'] = $ibforums->lang['last_post_by'];

        $topic['last_poster'] = (0 != $topic['last_poster_id']) ? "<b><a href='{$this->base_url}&act=Profile&CODE=03&MID={$topic['last_poster_id']}'>{$topic['last_poster_name']}</a></b>" : '-' . $topic['last_poster_name'] . '-';

        $topic['starter'] = (0 != $topic['starter_id']) ? "<a href='{$this->base_url}&act=Profile&CODE=03&MID={$topic['starter_id']}'>{$topic['starter_name']}</a>" : '-' . $topic['starter_name'] . '-';

        if ($topic['poll_state']) {
            $topic['prefix'] = $ibforums->vars['pre_polls'] . ' ';
        }

        if (($ibforums->member['uid']) and ($topic['author_id'])) {
            $show_dots = 1;
        }

        $topic['folder_img'] = $std->folder_icon($topic, $show_dots, $this->read_array[$topic['tid']]);

        $topic['topic_icon'] = $topic['icon_id'] ? '<img src="' . $ibforums->vars['img_url'] . '/icon' . $topic['icon_id'] . '.gif" border="0" alt="">' : '&nbsp;';

        $topic['start_date'] = $std->get_date($topic['start_date'], 'LONG');

        $pages = 1;

        if ($topic['posts']) {
            if (0 == (($topic['posts'] + 1) % $ibforums->vars['display_max_posts'])) {
                $pages = ($topic['posts'] + 1) / $ibforums->vars['display_max_posts'];
            } else {
                $number = (($topic['posts'] + 1) / $ibforums->vars['display_max_posts']);

                $pages = ceil($number);
            }
        }

        if ($pages > 1) {
            $topic['PAGES'] = "<span class='small'>({$ibforums->lang['topic_sp_pages']} ";

            for ($i = 0; $i < $pages; ++$i) {
                $real_no = $i * $ibforums->vars['display_max_posts'];

                $page_no = $i + 1;

                if (4 == $page_no) {
                    $topic['PAGES'] .= "<a href='{$this->base_url}&act=ST&f={$this->forum['id']}&t={$topic['tid']}&st=" . ($pages - 1) * $ibforums->vars['display_max_posts'] . "'>...$pages </a>";

                    break;
                }

                $topic['PAGES'] .= "<a href='{$this->base_url}&act=ST&f={$this->forum['id']}&t={$topic['tid']}&st=$real_no'>$page_no </a>";
            }

            $topic['PAGES'] .= ')</span>';
        }

        if ($topic['posts'] < 0) {
            $topic['posts'] = 0;
        }

        $last_time = $this->read_array[$topic['tid']] > $ibforums->input['last_visit'] ? $this->read_array[$topic['tid']] : $ibforums->input['last_visit'];

        if ($last_time && ($topic['last_post'] > $last_time)) {
            $topic['go_last_page'] = "<a href='{$this->base_url}&act=ST&f={$this->forum['id']}&t={$topic['tid']}&view=getlastpost'><{GO_LAST_ON}></a>";

            $topic['go_new_post'] = "<a href='{$this->base_url}&act=ST&f={$this->forum['id']}&t={$topic['tid']}&view=getnewpost'><{NEW_POST}></a>";
        } else {
            $topic['go_last_page'] = "<a href='{$this->base_url}&act=ST&f={$this->forum['id']}&t={$topic['tid']}&view=getlastpost'><{GO_LAST_OFF}></a>";

            $topic['go_new_post'] = '';
        }

        $topic['last_post'] = $std->get_date($topic['last_post'], 'SHORT');

        //+----------------------------------------------------------------

        if ('link' == $topic['state']) {
            $t_array = explode('&', $topic['moved_to']);

            $topic['tid'] = $t_array[0];

            $topic['forum_id'] = $t_array[1];

            $topic['title'] = $topic['title'];

            $topic['views'] = '--';

            $topic['posts'] = '--';

            $topic['prefix'] = $ibforums->vars['pre_moved'] . ' ';

            $topic['go_new_post'] = '';
        } else {
            $topic['posts'] = $this->html->who_link($topic['tid'], $topic['posts']);
        }

        $p_start = '';

        $p_end = '';

        if (1 == $topic['pinned']) {
            $topic['prefix'] = $ibforums->vars['pre_pinned'];

            $topic['topic_icon'] = '<{B_PIN}>';

            if (0 == $this->pinned_print) {
                // we've a pinned topic, but we've not printed the pinned

                // starter row, so..

                $p_start = $this->html->render_pinned_start();

                $this->pinned_print = 1;
            }

            return $p_start . $this->html->render_pinned_row($topic);
        }

        // This is not a pinned topic, so lets check to see if we've

        // printed the footer yet.

        if (1 == $this->pinned_print) {
            // Nope, so..

            $p_end = $this->html->render_pinned_end();

            $this->pinned_print = 0;
        }

        return $p_end . $this->html->RenderRow($topic);
    }

    //+----------------------------------------------------------------

    // Returns the last action date

    //+----------------------------------------------------------------

    public function get_last_date($topic)
    {
        global $ibforums, $std;

        return $std->get_date($topic['last_post'], 'SHORT');
    }
}

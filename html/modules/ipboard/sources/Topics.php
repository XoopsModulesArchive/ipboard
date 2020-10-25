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
|   > Topic display module
|   > Module written by Matt Mecham
|   > Date started: 18th February 2002
|
|	> Module Version Number: 1.1.0
+--------------------------------------------------------------------------
*/

$idx = new Topics();

class Topics
{
    public $output         = '';
    public $base_url       = '';
    public $html           = '';
    public $moderator      = [];
    public $forum          = [];
    public $topic          = [];
    public $category       = [];
    public $mem_titles     = [];
    public $mod_action     = [];
    public $poll_html      = '';
    public $colspan        = 0;
    public $parser         = '';
    public $mimetypes      = '';
    public $nav_extra      = '';
    public $read_array     = [];
    public $mod_panel_html = '';
    /***********************************************************************************/
    //
    // Our constructor, load words, load skin, print the topic listing
    //
    /***********************************************************************************/

    public function __construct()
    {
        global $ibforums, $DB, $std, $print, $skin_universal;

        //-------------------------------------
        // Compile the language file
        //-------------------------------------

        $ibforums->lang = $std->load_words($ibforums->lang, 'lang_topic', $ibforums->lang_id);

        $this->html = $std->load_template('skin_topic');

        require './sources/lib/post_parser.php';

        $this->parser = new post_parser();

        //-------------------------------------
        // Check the input
        //-------------------------------------

        $ibforums->input['t'] = $std->is_number($ibforums->input['t']);
        $ibforums->input['f'] = $std->is_number($ibforums->input['f']);

        if (($ibforums->input['t'] < 0 or $ibforums->input['f'] < 0)) {
            $std->Error([LEVEL => 1, MSG => 'missing_files']);
        }

        //-------------------------------------
        // Get the forum info based on the forum ID, get the category name, ID, and get the topic details
        //-------------------------------------

        $DB->query(
            "SELECT t.*, f.name as forum_name, f.id as forum_id, f.read_perms, f.reply_perms, f.parent_id, f.start_perms, f.allow_poll, f.password, f.posts as forum_posts, f.topics as forum_topics, f.upload_perms, c.name as cat_name, c.id as cat_id FROM ibf_topics t, ibf_forums f , ibf_categories c where t.tid='"
            . $ibforums->input[t]
            . "' and f.id = t.forum_id and f.category=c.id"
        );

        $this->topic = $DB->fetch_row();

        $this->forum = [
            'id'           => $this->topic['forum_id'],
            'name'         => $this->topic['forum_name'],
            'posts'        => $this->topic['forum_posts'],
            'topics'       => $this->topic['forum_topics'],
            'read_perms'   => $this->topic['read_perms'],
            'allow_poll'   => $this->topic['allow_poll'],
            'upload_perms' => $this->topic['upload_perms'],
            'parent_id'    => $this->topic['parent_id'],
            'password'     => $this->topic['password'],
        ];

        $this->category = [
            'name' => $this->topic['cat_name'],
            'id'   => $this->topic['cat_id'],
        ];

        //-------------------------------------
        // Error out if we can not find the forum
        //-------------------------------------

        if (!$this->forum['id']) {
            $std->Error([LEVEL => 1, MSG => 'missing_files']);
        }

        //-------------------------------------
        // Error out if we can not find the topic
        //-------------------------------------

        if (!$this->topic['tid']) {
            $std->Error([LEVEL => 1, MSG => 'missing_files']);
        }

        //-------------------------------------
        // Error out if the topic is not approved
        //-------------------------------------

        if ($this->topic['approved'] != 1) {
            $std->Error([LEVEL => 1, MSG => 'missing_files']);
        }

        //-------------------------------------        
        // If this forum is a link, then 
        // redirect them to the new location
        //-------------------------------------

        if ($this->topic['state'] == 'link') {
            $f_stuff = explode('&', $this->topic['moved_to']);
            $print->redirect_screen($ibforums->lang['topic_moved'], "act=ST&f={$f_stuff[1]}&t={$f_stuff[0]}");
        }

        //-------------------------------------
        // Unserialize the read array and parse into
        // array
        //-------------------------------------

        if ($read = $std->my_getcookie('topicsread')) {
            $this->read_array = unserialize(stripslashes($read));

            if (!is_array($this->read_array)) {
                $this->read_array = [];
            }
        }

        //--------------------------------------------------------------------
        // Are we looking for an older / newer topic?
        //--------------------------------------------------------------------

        if (isset($ibforums->input['view'])) {
            if ($ibforums->input['view'] == 'new') {
                $DB->query(
                    "SELECT * from ibf_topics WHERE forum_id='" . $this->forum['id'] . "' AND approved=1 AND state <> 'link' AND last_post > " . $this->topic['last_post'] . ' ' . 'ORDER BY last_post ASC LIMIT 0,1'
                );

                if ($DB->get_num_rows()) {
                    $this->topic = $DB->fetch_row();
                    $std->boink_it($ibforums->base_url . '&act=ST&f=' . $this->topic['forum_id'] . '&t=' . $this->topic['tid']);
                    //$ibforums->input['t'] = $this->topic['tid'];
                } else {
                    $std->Error([LEVEL => 1, MSG => 'no_newer']);
                }
            } elseif ($ibforums->input['view'] == 'old') {
                $DB->query(
                    "SELECT * from ibf_topics WHERE forum_id='" . $this->forum['id'] . "' AND approved=1 AND state <> 'link' AND last_post < " . $this->topic['last_post'] . ' ' . 'ORDER BY last_post DESC LIMIT 0,1'
                );

                if ($DB->get_num_rows()) {
                    $this->topic = $DB->fetch_row();
                    $std->boink_it($ibforums->base_url . '&act=ST&f=' . $this->topic['forum_id'] . '&t=' . $this->topic['tid']);
                    //$ibforums->input['t'] = $this->topic['tid'];
                } else {
                    $std->Error([LEVEL => 1, MSG => 'no_older']);
                }
            } elseif ($ibforums->input['view'] == 'getlastpost') {
                $this->return_last_post();
            } elseif ($ibforums->input['view'] == 'getnewpost') {
                $st  = 0;
                $pid = '';

                $last_time = $this->read_array[$this->topic['tid']] ?? $ibforums->input['last_visit'];

                $DB->query("SELECT pid, post_date FROM ibf_posts WHERE queued <> 1 AND topic_id='" . $this->topic['tid'] . "' AND post_date > '" . $last_time . "' ORDER BY post_date LIMIT 1");

                if ($post = $DB->fetch_row()) {
                    $pid = '&#entry' . $post['pid'];

                    $DB->query("SELECT COUNT(pid) as posts FROM ibf_posts WHERE topic_id='" . $this->topic['tid'] . "' AND pid <= '" . $post['pid'] . "'");

                    $cposts = $DB->fetch_row();

                    if ((($cposts['posts']) % $ibforums->vars['display_max_posts']) == 0) {
                        $pages = ($cposts['posts']) / $ibforums->vars['display_max_posts'];
                    } else {
                        $number = (($cposts['posts']) / $ibforums->vars['display_max_posts']);
                        $pages  = ceil($number);
                    }

                    $st = ($pages - 1) * $ibforums->vars['display_max_posts'];

                    $std->boink_it($ibforums->base_url . '&act=ST&f=' . $this->topic['forum_id'] . '&t=' . $this->topic['tid'] . "&st=$st" . $pid);
                    exit();
                } else {
                    $this->return_last_post();
                }
            } elseif ($ibforums->input['view'] == 'findpost') {
                $pid = (int)$ibforums->input['p'];

                if ($pid > 0) {
                    $DB->query("SELECT COUNT(pid) as posts FROM ibf_posts WHERE topic_id='" . $this->topic['tid'] . "' AND pid <= '" . $pid . "'");

                    $cposts = $DB->fetch_row();

                    if ((($cposts['posts']) % $ibforums->vars['display_max_posts']) == 0) {
                        $pages = ($cposts['posts']) / $ibforums->vars['display_max_posts'];
                    } else {
                        $number = (($cposts['posts']) / $ibforums->vars['display_max_posts']);
                        $pages  = ceil($number);
                    }

                    $st = ($pages - 1) * $ibforums->vars['display_max_posts'];

                    $std->boink_it($ibforums->base_url . '&act=ST&f=' . $this->topic['forum_id'] . '&t=' . $this->topic['tid'] . "&st=$st" . '&#entry' . $pid);
                    exit();
                } else {
                    $this->return_last_post();
                }
            }
        }

        $this->base_url = "{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}";

        $this->forum['JUMP'] = $std->build_forum_jump();
        $this->forum['JUMP'] = preg_replace('!#Forum Jump#!', $ibforums->lang['forum_jump'], $this->forum['JUMP']);

        //-------------------------------------
        // Check viewing permissions, private forums,
        // password forums, etc
        //-------------------------------------

        if ((!$this->topic['pinned']) and (!$ibforums->member['g_other_topics'])) {
            $std->Error([LEVEL => 1, MSG => 'no_view_topic']);
        }

        $bad_entry = $this->check_access();

        if ($bad_entry == 1) {
            $std->Error([LEVEL => 1, MSG => 'no_view_topic']);
        }

        //-------------------------------------
        // Update the topic views counter
        //-------------------------------------

        $DB->query("UPDATE ibf_topics SET views=views+1 WHERE tid='" . $this->topic['tid'] . "'");

        //-------------------------------------
        // Update the topic read cookie
        //-------------------------------------

        if ($ibforums->member['uid']) {
            $this->read_array[$this->topic['tid']] = time();

            $std->my_setcookie('topicsread', serialize($this->read_array), -1);
        }

        //----------------------------------------
        // If this is a sub forum, we need to get
        // the cat details, and parent details
        //----------------------------------------

        if ($this->forum['parent_id'] > 0) {
            $DB->query("SELECT f.id as forum_id, f.name as forum_name, c.id, c.name FROM ibf_forums f, ibf_categories c WHERE f.id='" . $this->forum['parent_id'] . "' AND c.id=f.category");

            $row = $DB->fetch_row();

            $this->category['id']   = $row['id'];
            $this->category['name'] = $row['name'];

            $this->nav_extra = "<a href='" . $this->base_url . "&act=SF&f={$row['forum_id']}'>{$row['forum_name']}</a>";
        }

        //-------------------------------------
        // Get all the member groups and
        // member title info
        //-------------------------------------

        $DB->query('SELECT id, title, pips, posts FROM ibf_titles ORDER BY posts DESC');
        while (false !== ($i = $DB->fetch_row())) {
            $this->mem_titles[$i['id']] = [
                'TITLE' => $i['title'],
                'PIPS'  => $i['pips'],
                'POSTS' => $i['posts'],
            ];
        }

        //-------------------------------------
        // Are we a moderator?
        //-------------------------------------

        if (($ibforums->member['uid']) and ($ibforums->member['g_is_supmod'] != 1)) {
            $DB->query("SELECT * FROM ibf_moderators WHERE forum_id='" . $this->forum['id'] . "' AND (member_id='" . $ibforums->member['uid'] . "' OR (is_group=1 AND group_id='" . $ibforums->member['mgroup'] . "'))");
            $this->moderator = $DB->fetch_row();
        }

        $this->mod_action = [
            'CLOSE_TOPIC'   => '00',
            'OPEN_TOPIC'    => '01',
            'MOVE_TOPIC'    => '02',
            'DELETE_TOPIC'  => '03',
            'EDIT_TOPIC'    => '05',
            'PIN_TOPIC'     => '15',
            'UNPIN_TOPIC'   => '16',
            'UNSUBBIT'      => '30',
            'SPLIT_TOPIC'   => '50',
            'MERGE_TOPIC'   => '60',
            'TOPIC_HISTORY' => '90',
        ];

        //-------------------------------------
        // Get the reply, and posting buttons
        //------------------------------------- 

        $this->topic['POLL_BUTTON'] = $this->forum['allow_poll'] ? "<a href='" . $this->base_url . '&act=Post&CODE=10&f=' . $this->forum['id'] . "'><{A_POLL}></a>" : '';

        $this->topic['REPLY_BUTTON'] = $this->reply_button();

        //-------------------------------------
        // Generate the forum page span links
        //-------------------------------------

        if ($ibforums->input['hl']) {
            $hl = '&hl=' . $ibforums->input['hl'];
        }

        $this->topic['SHOW_PAGES'] = $std->build_pagelinks(
            [
                'TOTAL_POSS' => ($this->topic['posts'] + 1),
                'PER_PAGE'   => $ibforums->vars[display_max_posts],
                'CUR_ST_VAL' => $ibforums->input['st'],
                'L_SINGLE'   => '',
                'L_MULTI'    => $ibforums->lang['multi_page_topic'],
                'BASE_URL'   => $this->base_url . '&act=ST&f=' . $this->forum['id'] . '&t=' . $this->topic['tid'] . $hl,
            ]
        );

        if (($this->topic['posts'] + 1) > $ibforums->vars['display_max_posts']) {
            $this->topic['go_new'] = $this->html->golastpost_link($this->forum['id'], $this->topic['tid']);
        }

        //-------------------------------------
        // Fix up some of the words
        //-------------------------------------

        $this->topic['TOPIC_START_DATE'] = $std->get_date($this->topic['start_date'], 'LONG');

        $ibforums->lang['topic_stats'] = preg_replace('/<#START#>/', $this->topic['TOPIC_START_DATE'], $ibforums->lang['topic_stats']);
        $ibforums->lang['topic_stats'] = preg_replace('/<#POSTS#>/', $this->topic['posts'], $ibforums->lang['topic_stats']);

        if ($this->topic['description']) {
            $this->topic['description'] = ', ' . $this->topic['description'];
        }

        //-------------------------------------
        // Render the page top
        //-------------------------------------

        $this->output .= $this->html->PageTop(['TOPIC' => $this->topic, 'FORUM' => $this->forum]);

        //-------------------------------------
        // Do we have a poll?
        //-------------------------------------

        if ($this->topic['poll_state']) {
            $this->output = str_replace('<!--{IBF.POLL}-->', $this->parse_poll(), $this->output);
        }

        /*******************************************************************************************/ // Grab the posts we'll need
        /*******************************************************************************************/

        $first = (int)$ibforums->input['st'];
        if (!$first) {
            $first = 0;
        }

        // Optimized query?
        // mySQL.com insists that forcing LEFT JOIN or STRAIGHT JOIN helps the query optimizer, so..

        $DB->query(
            "SELECT p.*,
				    m.uid,m.uname,m.mgroup,m.email,m.joined,m.avatar,m.avatar_size,m.posts,m.aim_name,m.icq_number,
				    m.signature, m.website,m.yahoo,m.title,m.hide_email,m.msnname,
				    g.g_id, g.g_title, g.g_icon
				    FROM ibf_posts p
				      LEFT JOIN xbb_members m ON (p.author_id=m.uid)
				      LEFT JOIN ibf_groups g ON (g.g_id=m.mgroup)
				    WHERE p.topic_id='" . $this->topic['tid'] . "' and p.queued !='1'
				    ORDER BY p.pid LIMIT $first, " . $ibforums->vars['display_max_posts']
        );

        if (!$DB->get_num_rows()) {
            if ($first >= $ibforums->vars['display_max_posts']) {
                // Get the correct number of replies...

                $newq   = $DB->query("SELECT COUNT(pid) as pcount FROM ibf_posts p, xbb_members m WHERE p.topic_id='" . $this->topic['tid'] . "' and p.queued !='1' AND p.author_id=m.uid");
                $pcount = $DB->fetch_row($newq);

                $pcount['pcount'] = $pcount['pcount'] > 0 ? $pcount['pcount'] - 1 : 0;

                // Update the post table...

                if ($pcount['pcount'] > 1) {
                    $DB->query("UPDATE ibf_topics SET posts='" . $pcount['pcount'] . "' WHERE tid='" . $this->topic['tid'] . "'");
                }

                $std->boink_it($ibforums->base_url . "&act=ST&f={$this->forum['id']}&t={$this->topic['tid']}&view=getlastpost");
                exit();
            }
        }

        $cached_members = [];

        //-------------------------------------
        // Format and print out the topic list
        //-------------------------------------

        $post_count = 0;  // Use this as our master bater, er... I mean counter.

        while (false !== ($row = $DB->fetch_row())) {
            $poster = [];

            // Get the member info. We parse the data and cache it.
            // It's likely that the same member posts several times in
            // one page, so it's not efficient to keep parsing the same
            // data

            if ($row['author_id'] != 0) {
                // Is it in the hash?
                if (isset($cached_members[$row['author_id']])) {
                    // Ok, it's already cached, read from it
                    $poster          = $cached_members[$row['author_id']];
                    $row['name_css'] = 'normalname';
                } else {
                    $row['name_css'] = 'normalname';
                    $poster          = $this->parse_member($row);
                    // Add it to the cached list
                    $cached_members[$row['author_id']] = $poster;
                }
            } else {
                // It's definately a guest...
                $poster          = $std->set_up_guest($row['author_name']);
                $row['name_css'] = 'unreg';
            }

            //--------------------------------------------------------------

            $row['post_css'] = $post_count % 2 ? 'post1' : 'post2';

            //--------------------------------------------------------------

            if (($row['append_edit'] == 1) and ($row['edit_time'] != '') and ($row['edit_name'] != '')) {
                $e_time = $std->get_date($row['edit_time'], 'LONG');

                $row['post'] .= "<br><br><span class='edit'>" . sprintf($ibforums->lang['edited_by'], $row['edit_name'], $e_time) . '</span>';
            }

            //--------------------------------------------------------------

            if (!$ibforums->member['view_img']) {
                // unconvert smilies first, or it looks a bit crap.

                $row['post'] = preg_replace('#<!--emo&(.+?)-->.+?<!--endemo-->#', "\\1", $row['post']);

                $row['post'] = preg_replace("/<img src=[\"'](.+?)[\"'].+?" . '>/', "(IMG:<a href='\\1' target='_blank'>\\1</a>)", $row['post']);
            }

            //--------------------------------------------------------------

            if ($ibforums->input['hl']) {
                $keywords = str_replace('+', ' ', $ibforums->input['hl']);

                if (preg_match('/,(and|or),/i', $keywords)) {
                    while (preg_match('/,(and|or),/i', $keywords, $match)) {
                        $word_array = explode(',' . $match[1] . ',', $keywords);

                        if (is_array($word_array)) {
                            foreach ($word_array as $keywords) {
                                $row['post'] = preg_replace("/(^|\s)($keywords)(\s|$)/i", "\\1<span class='highlight'>\\2</span>\\3", $row['post']);
                            }
                        }
                    }
                } else {
                    $row['post'] = preg_replace("/(^|\s)($keywords)(\s|$)/i", "\\1<span class='highlight'>\\2</span>\\3", $row['post']);
                }
            }

            //--------------------------------------------------------------

            if (($post_count != 0 and $first == 0) or ($first > 0)) {
                $row['delete_button'] = $this->delete_button($row['pid'], $poster);
            }

            $row['edit_button'] = $this->edit_button($row['pid'], $poster, $row['post_date']);
            $row['post_date']   = $std->get_date($row['post_date'], 'LONG');
            $row['post_icon']   = $row['icon_id'] ? "<img src='" . $ibforums->vars['img_url'] . "/icon{$row['icon_id']}.gif' alt=''>&nbsp;&nbsp;" : '';

            $row['ip_address'] = $this->view_ip($row, $poster);

            $row['report_link'] = (($ibforums->vars['disable_reportpost'] != 1) and ($ibforums->member['uid'])) ? $this->html->report_link($row) : '';

            //--------------------------------------------------------------

            if ($row['attach_id']) {
                // If we've not already done so, lets grab our mime-types

                if (!is_array($this->mimetypes)) {
                    require './conf_mime_types.php';
                    $this->mimetypes = $mime_types;
                    unset($mime_types);
                }

                // Is it an image, and are we viewing the image in the post?
                if (($ibforums->vars['show_img_upload']) and ($row['attach_type'] == 'image/gif' or $row['attach_type'] == 'image/jpeg' or $row['attach_type'] == 'image/pjpeg' or $row['attach_type'] == 'image/x-png' or $row['attach_type'] == 'image/png')) {
                    $row['attachment'] = $this->html->Show_attachments_img(['file_name' => $row['attach_id']]);
                } else {
                    $row['attachment'] = $this->html->Show_attachments(
                        [
                            'hits'  => $row['attach_hits'],
                            'image' => $this->mimetypes[$row['attach_type']][1],
                            'name'  => $row['attach_file'],
                            'pid'   => $row['pid'],
                        ]
                    );
                }
            }

            //--------------------------------------------------------------
            // Siggie stuff
            //--------------------------------------------------------------

            if (!$ibforums->vars[SIG_SEP]) {
                $ibforums->vars[SIG_SEP] = '<br><br>--------------------<br>';
            }

            if ($poster['signature'] and $ibforums->member['attachsig']) {
                if ($row['use_sig'] == 1) {
                    $row['signature'] = "<!--Signature-->{$ibforums->vars[SIG_SEP]}<span class='signature'>{$poster['signature']}</span><!--E-Signature-->";
                } else {
                    $row['signature'] = '';
                }
            } else {
                $row['signature'] = '';
            }

            // Fix up the membername so it links to the members profile

            if ($poster['uid']) {
                $poster['name'] = "<a href='{$this->base_url}&act=Profile&CODE=03&MID={$poster['uid']}'>{$poster['uname']}</a>";
            }

            $this->output .= $this->html->RenderRow(['POST' => $row, 'POSTER' => $poster]);

            $post_count++;
        }

        //-------------------------------------
        // Print the footer
        //-------------------------------------

        $this->output .= $this->html->TableFooter(['TOPIC' => $this->topic, 'FORUM' => $this->forum]);

        //+----------------------------------------------------------------
        // Process users active in this forum
        //+----------------------------------------------------------------

        if ($ibforums->vars['no_au_topic'] != 1) {
            //+-----------------------------------------
            // Is this forum restricted, or global?
            //+-----------------------------------------

            if ($this->forum['read_perms'] != '*') {
                $q_extra = ' AND s.member_group IN (0,' . $this->forum['read_perms'] . ') ';
            }

            //+-----------------------------------------
            // Get the users
            //+-----------------------------------------

            $cut_off = ($ibforums->vars['au_cutoff'] != '') ? $ibforums->vars['au_cutoff'] * 60 : 900;

            $time = time() - $cut_off;

            $DB->query(
                "SELECT s.member_id, s.member_name, s.login_type, s.location, g.suffix, g.prefix
					    FROM ibf_sessions s
					     LEFT JOIN ibf_groups g ON (g.g_id=s.member_group)
					    WHERE s.in_topic='{$this->topic['tid']}'
					    AND s.running_time > '$time'" . $q_extra . 'ORDER BY s.running_time DESC'
            );

            //+-----------------------------------------
            // Cache all printed members so we don't double print them
            //+-----------------------------------------

            $cached = [];
            $active = ['guests' => 0, 'anon' => 0, 'members' => 0, 'names' => ''];

            while (false !== ($result = $DB->fetch_row())) {
                if ($result['member_id'] == 0) {
                    $active['guests']++;
                } else {
                    if (empty($cached[$result['member_id']])) {
                        $cached[$result['member_id']] = 1;

                        if ($result['login_type'] == 1) {
                            if (($ibforums->member['mgroup'] == $ibforums->vars['admin_group']) and ($ibforums->vars['disable_admin_anon'] != 1)) {
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

            $ibforums->lang['active_users_title']   = sprintf($ibforums->lang['active_users_title'], ($active['members'] + $active['guests'] + $active['anon']));
            $ibforums->lang['active_users_detail']  = sprintf($ibforums->lang['active_users_detail'], $active['guests'], $active['anon']);
            $ibforums->lang['active_users_members'] = sprintf($ibforums->lang['active_users_members'], $active['members']);

            $this->output = str_replace('<!--IBF.TOPIC_ACTIVE-->', $this->html->topic_active_users($active), $this->output);
        }

        //+----------------------------------------------------------------
        // Print it
        //+----------------------------------------------------------------

        $this->output = str_replace('<!--IBF.MOD_PANEL-->', $this->moderation_panel(), $this->output);

        if ($ibforums->member['uid'] > 0) {
            $this->output = str_replace('<!--IBF.EMAIL_OPTIONS-->', $this->html->email_options($this->topic['tid'], $this->forum['id']), $this->output);
        }

        // Pass it to our print routine

        $print->add_output((string)$this->output);
        $print->do_output(
            [
                'TITLE' => $ibforums->vars['board_name'] . " -> {$this->topic['title']}",
                'JS'    => 1,
                'NAV'   => [
                    "<a href='" . $this->base_url . "&act=SC&c={$this->category['id']}'>{$this->category['name']}</a>",
                    $this->nav_extra,
                    "<a href='" . $this->base_url . "&act=SF&f={$this->forum['id']}'>{$this->forum['name']}</a>",
                ],
            ]
        );
    }

    /*********************************************************************/
    // Parse the member info
    /*********************************************************************/

    public function parse_member($member = [])
    {
        global $ibforums, $std, $DB;

        $member['avatar'] = $std->get_avatar($member['user_avatar'], $ibforums->member['view_avs'], $member['avatar_size']);

        $pips = 0;

        foreach ($this->mem_titles as $k => $v) {
            if ($member['posts'] >= $v['POSTS']) {
                if (!$member['title']) {
                    $member['title'] = $this->mem_titles[$k]['TITLE'];
                }
                $pips = $v['PIPS'];
                break;
            }
        }

        if ($member['g_icon']) {
            $member['member_rank_img'] = "<img src='{$ibforums->vars[TEAM_ICON_URL]}/{$member['g_icon']}' border='0'>";
        } else {
            if ($pips) {
                if (preg_match("/^\d+$/", $pips)) {
                    for ($i = 1; $i <= $pips; ++$i) {
                        $member['member_rank_img'] .= '<{A_STAR}>';
                    }
                } else {
                    $member['member_rank_img'] = "<img src='{$ibforums->vars['TEAM_ICON_URL']}/$pips' border='0'>";
                }
            }
        }

        $member['member_joined'] = $ibforums->lang['m_joined'] . ' ' . $std->get_date($member['user_regdate'], 'JOINED');

        $member['member_group'] = $ibforums->lang['m_group'] . ' ' . $member['g_title'];

        $member['member_posts'] = $ibforums->lang['m_posts'] . ' ' . $member['posts'];

        $member['member_number'] = $ibforums->lang['member_no'] . ' ' . $member['uid'];

        $member['profile_icon'] = "<a href='{$this->base_url}&act=Profile&CODE=03&MID={$member['uid']}'><{P_PROFILE}></a>&nbsp;";

        $member['message_icon'] = "<a href='{$this->base_url}&act=Msg&CODE=04&MID={$member['uid']}'><{P_MSG}></a>&nbsp;";

        if ($member['user_viewemail']) {
            $member['email_icon'] = "<a href='{$this->base_url}&act=Mail&CODE=00&MID={$member['uid']}'><{P_EMAIL}></a>&nbsp;";
        }

        if ($member['url'] and preg_match("/^http:\/\/\S+$/", $member['url'])) {
            $member['website_icon'] = "<a href='{$member['url']}' target='_blank'><{P_WEBSITE}></a>&nbsp;";
        }

        if ($member['user_icq']) {
            $member['icq_icon'] = "<a href=\"javascript:PopUp('{$this->base_url}&act=ICQ&MID={$member['uid']}','Pager','450','330','0','1','1','1')\"><{P_ICQ}></a>&nbsp;";
        }

        if ($member['user_aim']) {
            $member['aol_icon'] = "<a href=\"javascript:PopUp('{$this->base_url}&act=AOL&MID={$member['uid']}','Pager','450','330','0','1','1','1')\"><{P_AOL}></a>&nbsp;";
        }

        if ($member['user_yim']) {
            $member['yahoo_icon'] = "<a href=\"javascript:PopUp('{$this->base_url}&act=YAHOO&MID={$member['uid']}','Pager','450','330','0','1','1','1')\"><{P_YIM}></a>&nbsp;";
        }

        if ($member['user_msnm']) {
            $member['msn_icon'] = "<a href=\"javascript:PopUp('{$this->base_url}&act=MSN&MID={$member['uid']}','Pager','450','330','0','1','1','1')\"><{P_MSN}></a>&nbsp;";
        }

        //-----------------------------------------------------

        return $member;
    }

    /*********************************************************************/
    // Render the delete button
    /*********************************************************************/

    public function delete_button($post_id, $poster)
    {
        global $ibforums;

        if ($ibforums->member['uid'] == '' or $ibforums->member['uid'] == 0) {
            return '';
        }

        $button = "<a href=\"javascript:delete_post('{$this->base_url}&act=Mod&CODE=04&f={$this->forum['id']}&t={$this->topic['tid']}&p={$post_id}&st={$ibforums->input[st]}')\"><{P_DELETE}></a>";

        if ($ibforums->member['g_is_supmod']) {
            return $button;
        }
        if ($this->moderator['delete_post']) {
            return $button;
        }
        if ($poster['id'] == $ibforums->member['uid'] and ($ibforums->member['g_delete_own_posts'])) {
            return $button;
        }
        return '';
    }

    /*********************************************************************/
    // Render the edit button
    /*********************************************************************/

    public function edit_button($post_id, $poster, $post_date)
    {
        global $ibforums;

        if ($ibforums->member['uid'] == '' or $ibforums->member['uid'] == 0) {
            return '';
        }

        $button = "<a href=\"{$this->base_url}&act=Post&CODE=08&f={$this->forum['id']}&t={$this->topic['tid']}&p={$post_id}&st={$ibforums->input[st]}\"><{P_EDIT}></a>";

        if ($ibforums->member['g_is_supmod']) {
            return $button;
        }

        if ($this->moderator['edit_post']) {
            return $button;
        }

        if ($poster['uid'] == $ibforums->member['uid'] and ($ibforums->member['g_edit_posts'])) {
            // Have we set a time limit?

            if ($ibforums->member['g_edit_cutoff'] > 0) {
                if ($post_date > (time() - ((int)$ibforums->member['g_edit_cutoff'] * 60))) {
                    return $button;
                } else {
                    return '';
                }
            } else {
                return $button;
            }
        }

        return '';
    }


    /*********************************************************************/
    // Render the IP address
    /*********************************************************************/

    public function view_ip($row, $poster)
    {
        global $ibforums;

        if ($ibforums->member['g_is_supmod'] != 1 && $this->moderator['view_ip'] != 1) {
            return '';
        } else {
            $row['ip_address'] = $poster['mgroup'] == $ibforums->vars['admin_group'] ? '[ ---------- ]' : "[ <a href='{$ibforums->base_url}&act=modcp&CODE=ip&incoming={$row['ip_address']}' target='_blank'>{$row['ip_address']}</a> ]";
            return $this->html->ip_show($row['ip_address']);
        }
    }


    /*********************************************************************/
    // Render the moderator links
    /*********************************************************************/

    public function moderation_panel()
    {
        global $ibforums;

        $mod_links = '';

        if (!isset($ibforums->member['uid'])) {
            return '';
        }

        $skcusgej = 0;

        if ($ibforums->member['uid'] == $this->topic['starter_id']) {
            $skcusgej = 1;
        }

        if ($ibforums->member['g_is_supmod'] == 1) {
            $skcusgej = 1;
        }

        if ($this->moderator['mid'] != '') {
            $skcusgej = 1;
        }

        if ($skcusgej == 0) {
            return '';
        }

        $actions = ['MOVE_TOPIC', 'CLOSE_TOPIC', 'OPEN_TOPIC', 'DELETE_TOPIC', 'EDIT_TOPIC', 'PIN_TOPIC', 'UNPIN_TOPIC', 'UNSUBBIT', 'MERGE_TOPIC', 'SPLIT_TOPIC'];

        foreach ($actions as $key) {
            if ($ibforums->member['g_is_supmod']) {
                $mod_links .= $this->append_link($key);
            } elseif ($this->moderator['mid']) {
                if ($key == 'MERGE_TOPIC' or $key == 'SPLIT_TOPIC') {
                    if ($this->moderator['split_merge'] == 1) {
                        $mod_links .= $this->append_link($key);
                    }
                } else {
                    if ($this->moderator[strtolower($key)]) {
                        $mod_links .= $this->append_link($key);
                    }
                }
            } elseif ($key == 'OPEN_TOPIC' or $key == 'CLOSE_TOPIC') {
                if ($ibforums->member['g_open_close_posts']) {
                    $mod_links .= $this->append_link($key);
                }
            } /*elseif ($key == 'EDIT_TOPIC')
			{
				if ($ibforums->member['g_delete_own_topics'])
				{
					$mod_links .= $this->append_link($key);
				}
			}*/ elseif ($key == 'DELETE_TOPIC') {
                if ($ibforums->member['g_delete_own_topics']) {
                    $mod_links .= $this->append_link($key);
                }
            }
        }

        if ($ibforums->member['g_access_cp'] == 1) {
            $mod_links .= $this->append_link('TOPIC_HISTORY');
        }

        if ($mod_links != '') {
            return $this->html->Mod_Panel($mod_links, $this->forum['id'], $this->topic['tid']);
        }
    }

    public function append_link($key = '')
    {
        global $ibforums;

        if ($key == '') {
            return '';
        }

        if ($this->topic['state'] == 'open' and $key == 'OPEN_TOPIC') {
            return '';
        }
        if ($this->topic['state'] == 'closed' and $key == 'CLOSE_TOPIC') {
            return '';
        }
        if ($this->topic['state'] == 'moved' and ($key == 'CLOSE_TOPIC' or $key == 'MOVE_TOPIC')) {
            return '';
        }
        if ($this->topic['pinned'] == 1 and $key == 'PIN_TOPIC') {
            return '';
        }
        if ($this->topic['pinned'] == 0 and $key == 'UNPIN_TOPIC') {
            return '';
        }

        ++$this->colspan;

        return $this->html->mod_wrapper($this->mod_action[$key], $ibforums->lang[$key]);
    }

    /*********************************************************************/
    // Render the reply button
    /*********************************************************************/

    public function reply_button()
    {
        global $ibforums;

        if ($this->topic['state'] == 'closed') {
            // Do we have the ability to post in
            // closed topics?

            if ($ibforums->member['g_post_closed'] == 1) {
                return "<a href='{$this->base_url}&act=Post&CODE=02&f=" . $this->forum['id'] . '&t=' . $this->topic['tid'] . "'><{A_LOCKED_B}></a>";
            } else {
                return '<{A_LOCKED_B}>';
            }
        }

        if ($this->topic['state'] == 'moved') {
            return '<{A_MOVED_B}>';
        }

        if ($this->topic['poll_state'] == 'closed') {
            return '<{A_POLLONLY_B}>';
        }

        return "<a href='{$this->base_url}&act=Post&CODE=02&f=" . $this->forum['id'] . '&t=' . $this->topic['tid'] . "'><{A_REPLY}></a>";
    }

    public function check_access()
    {
        global $ibforums, $std, $HTTP_COOKIE_VARS;

        $return = 1;

        $this->m_group = $ibforums->member['mgroup'];

        if ($this->forum['read_perms'] == '*') {
            $return = 0;
        } elseif (preg_match("/(^|,)$this->m_group(,|$)/", $this->forum['read_perms'])) {
            $return = 0;
        }

        if ($this->forum['password'] != '') {
            if (!$c_pass = $std->my_getcookie('iBForum' . $this->forum['id'])) {
                return 1;
            }

            if ($c_pass == $this->forum['password']) {
                return 0;
            } else {
                return 1;
            }
        }

        return $return;
    }

    /*********************************************************************/
    // Process and parse the poll
    /*********************************************************************/

    public function parse_poll()
    {
        global $ibforums, $DB, $std;

        $html        = '';
        $check       = 0;
        $poll_footer = '';

        $ibforums->lang = $std->load_words($ibforums->lang, 'lang_post', $ibforums->lang_id);

        $this->poll_html = $std->load_template('skin_poll');

        // Get the poll information...

        $DB->query("SELECT * FROM ibf_polls WHERE tid='" . $this->topic['tid'] . "'");
        $poll_data = $DB->fetch_row();

        if (!$poll_data['pid']) {
            return;
        }

        if (!$poll_data['poll_question']) {
            $poll_data['poll_question'] = $this->topic['title'];
        }

        //----------------------------------

        $delete_link = '';
        $edit_link   = '';
        $can_edit    = 0;
        $can_delete  = 0;

        if ($this->moderator['edit_post']) {
            $can_edit = 1;
        }
        if ($this->moderator['delete_post']) {
            $can_delete = 1;
        }

        if ($ibforums->member['g_is_supmod'] == 1) {
            $can_edit   = 1;
            $can_delete = 1;
        }

        if ($can_edit == 1) {
            $edit_link = $this->poll_html->edit_link($this->topic['tid'], $this->forum['id']);
        }

        if ($can_delete == 1) {
            $delete_link = $this->poll_html->delete_link($this->topic['tid'], $this->forum['id']);
        }

        //----------------------------------

        $voter = ['id' => 0];

        // Have we voted in this poll?

        $DB->query("SELECT member_id from ibf_voters WHERE member_id='" . $ibforums->member['uid'] . "' and tid='" . $this->topic['tid'] . "'");
        $voter = $DB->fetch_row();

        if ($voter['member_id'] != 0) {
            $check       = 1;
            $poll_footer = $ibforums->lang['poll_you_voted'];
        }

        if (($poll_data['starter_id'] == $ibforums->member['uid']) and ($ibforums->vars['allow_creator_vote'] != 1)) {
            $check       = 1;
            $poll_footer = $ibforums->lang['poll_you_created'];
        }

        if (!$ibforums->member['uid']) {
            $check       = 1;
            $poll_footer = $ibforums->lang['poll_no_guests'];
        }

        // is the topic locked?

        if ($this->topic['state'] == 'closed') {
            $check       = 1;
            $poll_footer = '&nbsp;';
        }

        if ($check == 1) {
            // Show the results

            $html = $this->poll_html->ShowPoll_header($this->topic['tid'], $poll_data['poll_question'], $edit_link, $delete_link);

            $poll_answers = unserialize(stripslashes($poll_data['choices']));
            reset($poll_answers);
            foreach ($poll_answers as $entry) {
                $id     = $entry[0];
                $choice = $entry[1];
                $votes  = $entry[2];

                if (!$choice) {
                    continue;
                }

                if ($ibforums->vars['poll_tags']) {
                    $choice = $this->parser->parse_poll_tags($choice);
                }

                $percent = $votes == 0 ? 0 : $votes / $poll_data['votes'] * 100;
                $percent = sprintf('%.2f', $percent);
                $width   = $percent > 0 ? (int)$percent * 2 : 0;
                $html    .= $this->poll_html->Render_row_results($votes, $id, $choice, $percent, $width);
            }
        } else {
            $poll_answers = unserialize(stripslashes($poll_data['choices']));
            reset($poll_answers);

            // Show poll form

            $html = $this->poll_html->ShowPoll_Form_header($this->topic['tid'], $poll_data['poll_question'], $edit_link, $delete_link);

            foreach ($poll_answers as $entry) {
                $id     = $entry[0];
                $choice = $entry[1];
                $votes  = $entry[2];

                if (!$choice) {
                    continue;
                }

                if ($ibforums->vars['poll_tags']) {
                    $choice = $this->parser->parse_poll_tags($choice);
                }

                $html .= $this->poll_html->Render_row_form($votes, $id, $choice);
            }
            $poll_footer = "<input type='submit' name='submit'   value='{$ibforums->lang['poll_add_vote']}' class='forminput'>&nbsp;" . "<input type='submit' name='nullvote' value='{$ibforums->lang['poll_null_vote']}' class='forminput'>";
        }

        $html .= $this->poll_html->ShowPoll_footer($poll_footer);

        return $html;
    }

    public function return_last_post()
    {
        global $ibforums, $DB, $std;

        $st = 0;

        if ($this->topic['posts']) {
            if ((($this->topic['posts'] + 1) % $ibforums->vars['display_max_posts']) == 0) {
                $pages = ($this->topic['posts'] + 1) / $ibforums->vars['display_max_posts'];
            } else {
                $number = (($this->topic['posts'] + 1) / $ibforums->vars['display_max_posts']);
                $pages  = ceil($number);
            }

            $st = ($pages - 1) * $ibforums->vars['display_max_posts'];
        }

        $DB->query("SELECT pid FROM ibf_posts WHERE queued <> 1 AND topic_id='" . $this->topic['tid'] . "' ORDER BY pid DESC LIMIT 1");
        $post = $DB->fetch_row();

        $std->boink_it($ibforums->base_url . '&act=ST&f=' . $this->topic['forum_id'] . '&t=' . $this->topic['tid'] . "&st=$st&" . '#entry' . $post['pid']);
        exit();
    }
}



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
|   > Moderation core module
|   > Module written by Matt Mecham
|   > Date started: 19th February 2002
|
|   > Module Version 1.0.0
+--------------------------------------------------------------------------
*/

$idx = new Moderate();

class Moderate
{
    public $output = '';

    public $base_url = '';

    public $html = '';

    public $moderator = '';

    public $forum = [];

    public $topic = [];

    public $upload_dir = '';

    /***********************************************************************************/

    // Our constructor, load words, load skin, print the topic listing

    /***********************************************************************************/

    public function __construct()
    {
        global $ibforums, $DB, $std, $print, $skin_universal, $_POST;

        // Make sure this is a POST request, not a naughty IMG redirect

        if ('04' != $ibforums->input['CODE'] && '02' != $ibforums->input['CODE'] && '20' != $ibforums->input['CODE'] && '22' != $ibforums->input['CODE']) {
            if ('' == $_POST['act']) {
                $std->Error(['LEVEL' => 1, 'MSG' => 'incorrect_use']);
            }
        }

        //-------------------------------------

        // Compile the language file

        //-------------------------------------

        $ibforums->lang = $std->load_words($ibforums->lang, 'lang_mod', $ibforums->lang_id);

        $this->html = $std->load_template('skin_mod');

        //-------------------------------------

        // Check the input

        //-------------------------------------

        if ($ibforums->input['t']) {
            $ibforums->input['t'] = (int)$ibforums->input['t'];

            if (!$ibforums->input['t']) {
                $std->Error(['LEVEL' => 1, 'MSG' => 'missing_files']);
            } else {
                $DB->query("SELECT tid, title, description, posts, state, starter_id, pinned, forum_id, last_post from ibf_topics WHERE tid='" . $ibforums->input['t'] . "'");

                $this->topic = $DB->fetch_row();

                if (empty($this->topic['tid'])) {
                    $std->Error(['LEVEL' => 1, 'MSG' => 'missing_files']);
                }
            }
        }

        if ($ibforums->input['p']) {
            $ibforums->input['p'] = (int)$ibforums->input['p'];

            if (!$ibforums->input['p']) {
                $std->Error(['LEVEL' => 1, 'MSG' => 'missing_files']);
            }
        }

        $ibforums->input['f'] = (int)$ibforums->input['f'];

        if (!$ibforums->input['f']) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'missing_files']);
        }

        $ibforums->input['st'] = $ibforums->input['st'] ? $std->is_number($ibforums->input['st']) : 0;

        //-------------------------------------

        // Get the forum info based on the forum ID, get the category name, ID, and get the topic details

        //-------------------------------------

        $DB->query('SELECT f.*, c.name as cat_name, c.id as cat_id from ibf_forums f, ibf_categories c WHERE f.id=' . $ibforums->input['f'] . ' and c.id=f.category');

        $this->forum = $DB->fetch_row();

        //-------------------------------------

        // Error out if we can not find the forum

        //-------------------------------------

        if (!$this->forum['id']) {
            $std->Error([LEVEL => 1, MSG => 'missing_files']);
        }

        $this->base_url = "{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}";

        if ($ibforums->member['uid']) {
            if (1 != $ibforums->member['g_is_supmod']) {
                $DB->query("SELECT * FROM ibf_moderators WHERE forum_id='" . $this->forum['id'] . "' AND (member_id='" . $ibforums->member['uid'] . "' OR (is_group=1 AND group_id='" . $ibforums->member['mgroup'] . "'))");

                $this->moderator = $DB->fetch_row();
            }
        }

        $this->upload_dir = $ibforums->vars['upload_dir'];

        //-------------------------------------

        // Convert the code ID's into something

        // use mere mortals can understand....

        //-------------------------------------

        switch ($ibforums->input['CODE']) {
            case '02':
                $this->move_form();
                break;
            case '03':
                $this->delete_form();
                break;
            case '04':
                $this->delete_post();
                break;
            case '05':
                $this->edit_form();
                break;
            case '00':
                $this->close_topic();
                break;
            case '01':
                $this->open_topic();
                break;
            case '08':
                $this->delete_topic();
                break;
            case '12':
                $this->do_edit();
                break;
            case '14':
                $this->do_move();
                break;
            case '15':
                $this->pin_topic();
                break;
            case '16':
                $this->unpin_topic();
                break;
            case '17':
                $this->rebuild_topic();
                break;
            //-------------------------
            case '20':
                $this->poll_edit_form();
                break;
            case '21':
                $this->poll_edit_do();
                break;
            //-------------------------
            case '22':
                $this->poll_delete_form();
                break;
            case '23':
                $this->poll_delete_do();
                break;
            //-------------------------
            case '30':
                $this->unsubscribe_all_form();
                break;
            case '31':
                $this->unsubscribe_all();
                break;
            //-------------------------

            case '50':
                $this->split_start();
                break;
            case '51':
                $this->split_complete();
                break;
            //-------------------------

            case '60':
                $this->merge_start();
                break;
            case '61':
                $this->merge_complete();
                break;
            //-------------------------

            case '90':
                $this->topic_history();
                break;
            default:
                $this->moderate_error();
                break;
        }

        // If we have any HTML to print, do so...

        $print->add_output((string)$this->output);

        $print->do_output(['TITLE' => $this->page_title, 'JS' => 0, NAV => $this->nav]);
    }

    /*************************************************/

    // TOPIC HISTORY:

    // ---------------

    /*************************************************/

    public function topic_history()
    {
        global $std, $ibforums, $DB, $print;

        $passed = 0;

        if (1 == $ibforums->member['g_access_cp']) {
            $passed = 1;
        } else {
            $passed = 0;
        }

        if (1 != $passed) {
            $this->moderate_error();
        }

        if (empty($this->topic['tid'])) {
            $this->moderate_error();
        }

        $tid = (int)$ibforums->input['t'];

        //-----------------------------------------

        // Get all info for this topic-y-poos

        //-----------------------------------------

        $DB->query("SELECT * FROM ibf_topics WHERE tid='$tid'");

        $topic = $DB->fetch_row();

        if ($topic['last_post'] == $topic['start_date']) {
            $avg_posts = 1;
        } else {
            $avg_posts = round(($topic['posts'] + 1) / ((($topic['last_post'] - $topic['start_date']) / 86400)), 1);
        }

        if ($avg_posts < 0) {
            $avg_posts = 1;
        }

        $data = [
            'th_topic' => $topic['title'],
'th_desc' => $topic['description'],
'th_start_date' => $std->get_date($topic['start_date'], 'LONG'),
'th_start_name' => $std->make_profile_link($topic['starter_name'], $topic['starter_id']),
'th_last_date' => $std->get_date($topic['last_post'], 'LONG'),
'th_last_name' => $std->make_profile_link($topic['last_poster_name'], $topic['last_poster_id']),
'th_avg_post' => $avg_posts,
        ];

        $this->output .= $this->html->topic_history($data);

        $this->output .= $this->html->mod_log_start();

        // Do we have any logs in the mod-logs DB about this topic? eh? well?

        $DB->query("SELECT * FROM ibf_moderator_logs WHERE topic_id='$tid' ORDER BY ctime DESC");

        if (!$DB->get_num_rows()) {
            $this->output .= $this->html->mod_log_none();
        } else {
            while (false !== ($row = $DB->fetch_row())) {
                $row['member'] = $std->make_profile_link($row['member_name'], $row['member_id']);

                $row['date'] = $std->get_date($row['ctime'], 'LONG');

                $this->output .= $this->html->mod_log_row($row);
            }
        }

        $this->output .= $this->html->mod_log_end();

        $this->page_title = $this->topic['title'];

        $this->nav = [
            "<a href='{$this->base_url}&act=SF&f={$this->forum['id']}'>{$this->forum['name']}</a>",
            "<a href='{$this->base_url}&act=ST&f={$this->forum['id']}&t={$this->topic['tid']}'>{$this->topic['title']}</a>",
        ];
    }

    //=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+

    /*************************************************/

    // SPLIT TOPICS:

    // ---------------

    /*************************************************/

    public function split_start()
    {
        global $std, $ibforums, $DB, $print;

        $passed = 0;

        if (1 == $ibforums->member['g_is_supmod']) {
            $passed = 1;
        } elseif (1 == $this->moderator['split_merge']) {
            $passed = 1;
        } else {
            $passed = 0;
        }

        if (1 != $passed) {
            $this->moderate_error();
        }

        if (empty($this->topic['tid'])) {
            $this->moderate_error();
        }

        //-----------------------------------------

        require './sources/lib/post_parser.php';

        $this->parser = new post_parser();

        //-----------------------------------------

        $jump_html = $std->build_forum_jump(0, 1);

        $this->output = $this->html_start_form(
            [
                1 => ['CODE', '51'],
2 => ['t', $this->topic['tid']],
3 => ['f', $this->forum['id']],
            ]
        );

        $this->output .= $this->html->table_top($ibforums->lang['st_top'] . ' ' . $this->forum['name'] . ' &gt; ' . $this->topic['title']);

        $this->output .= $this->html->mod_exp($ibforums->lang['st_explain']);

        $this->output .= $this->html->split_body($jump_html);

        //-----------------------------------------

        // Display the posty wosty's

        //-----------------------------------------

        $post_query = $DB->query(
            "SELECT post, pid, post_date, author_id, author_name
		                          FROM ibf_posts
		                          WHERE topic_id='{$this->topic['tid']}'
		                           AND queued <> 1
		                          ORDER BY post_date"
        );

        $post_count = 0;

        while (false !== ($row = $DB->fetch_row($post_query))) {
            // Limit posts to 200 chars to stop shite loads of pages

            if (mb_strlen($row['post']) > 800) {
                $row['post'] = $this->parser->unconvert($row['post']);

                $row['post'] = mb_substr($row['post'], 0, 800) . '...';
            }

            $row['date'] = $std->get_date($row['post_date'], 'LONG');

            $row['st_top_bit'] = sprintf($ibforums->lang['st_top_bit'], $row['author_name'], $row['date']);

            $row['post_css'] = $post_count % 2 ? 'row1' : 'row2';

            $this->output .= $this->html->split_row($row);

            $post_count++;
        }

        //-----------------------------------------

        // print my bottom, er, the bottom

        //-----------------------------------------

        $this->output .= $this->html->split_end_form($ibforums->lang['st_submit']);

        $this->page_title = $ibforums->lang['st_top'] . ' ' . $this->topic['title'];

        $this->nav = [
            "<a href='{$this->base_url}&act=SF&f={$this->forum['id']}'>{$this->forum['name']}</a>",
            "<a href='{$this->base_url}&act=ST&f={$this->forum['id']}&t={$this->topic['tid']}'>{$this->topic['title']}</a>",
        ];
    }

    //=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+

    public function split_complete()
    {
        global $std, $ibforums, $DB, $print;

        $passed = 0;

        if (1 == $ibforums->member['g_is_supmod']) {
            $passed = 1;
        } elseif (1 == $this->moderator['split_merge']) {
            $passed = 1;
        } else {
            $passed = 0;
        }

        if (1 != $passed) {
            $this->moderate_error();
        }

        if (empty($this->topic['tid'])) {
            $this->moderate_error();
        }

        //------------------------------------------

        // Check the input

        //------------------------------------------

        if ('' == $ibforums->input['title']) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'complete_form']);
        }

        //------------------------------------------

        // Get the post ID's to split

        //------------------------------------------

        $ids = [];

        foreach ($ibforums->input as $key => $value) {
            if (preg_match("/^post_(\d+)$/", $key, $match)) {
                if ($ibforums->input[$match[0]]) {
                    $ids[] = $match[1];
                }
            }
        }

        $affected_ids = count($ids);

        // Do we have enough?

        if ($affected_ids < 1) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'split_not_enough']);
        }

        // Complete the PID string

        $pid_string = implode(',', $ids);

        //----------------------------------------------------

        // Check the forum we're moving this too

        //----------------------------------------------------

        $ibforums->input['fid'] = (int)$ibforums->input['fid'];

        if ($ibforums->input['fid'] != $this->forum['id']) {
            $DB->query("SELECT id, subwrap, sub_can_post FROM ibf_forums WHERE id='" . $ibforums->input['fid'] . "'");

            if (!$f = $DB->fetch_row()) {
                $std->Error(['LEVEL' => 1, 'MSG' => 'move_no_forum']);
            }

            if (1 == $f['subwrap'] and 1 != $f['sub_can_post']) {
                $std->Error(['LEVEL' => 1, 'MSG' => 'forum_no_post_allowed']);
            }
        }

        //----------------------------------------------------

        // Complete a new dummy topic

        //----------------------------------------------------

        $new_topic = [
            'title' => $ibforums->input['title'],
'description' => $ibforums->input['desc'],
'state' => 'open',
'posts' => 0,
'starter_id' => 0,
'starter_name' => 0,
'start_date' => time(),
'last_poster_id' => 0,
'last_poster_name' => 0,
'last_post' => time(),
'icon_id' => 0,
'author_mode' => 1,
'poll_state' => 0,
'last_vote' => 0,
'views' => 0,
'forum_id' => $ibforums->input['fid'],
'approved' => 1,
'pinned' => 0,
        ];

        $db_string = $DB->compile_db_insert_string($new_topic);

        $DB->query('INSERT INTO ibf_topics (' . $db_string['FIELD_NAMES'] . ') VALUES (' . $db_string['FIELD_VALUES'] . ')');

        $new_topic_id = $DB->get_insert_id();

        //----------------------------------------------------

        // Move the posts

        //----------------------------------------------------

        $DB->query("UPDATE ibf_posts SET forum_id='" . $ibforums->input['fid'] . "', topic_id='$new_topic_id' WHERE pid IN($pid_string)");

        //----------------------------------------------------

        // NEW TOPIC: Get the last / first post in the "new" topic

        //----------------------------------------------------

        $DB->query("SELECT author_id, author_name, post_date FROM ibf_posts WHERE topic_id='$new_topic_id' ORDER BY post_date DESC LIMIT 1");

        $last_post = $DB->fetch_row();

        $DB->query("SELECT pid, author_id, author_name, post_date FROM ibf_posts WHERE topic_id='$new_topic_id' ORDER BY post_date ASC LIMIT 1");

        $first_post = $DB->fetch_row();

        // Get the number of posts in this "new" topic

        $DB->query("SELECT count(pid) as posts FROM ibf_posts WHERE queued <> 1 AND topic_id='$new_topic_id'");

        $post_count = $DB->fetch_row();

        // Remove 1 from the count as we don't count the first post

        $post_count['posts']--;

        //----------------------------------------------------

        // NEW TOPIC: Update new topic entry in DB

        //----------------------------------------------------

        $new_topic = [
            'posts' => $post_count['posts'],
'starter_id' => $first_post['author_id'],
'starter_name' => $first_post['author_name'],
'start_date' => $first_post['post_date'],
'last_poster_id' => $last_post['author_id'],
'last_poster_name' => $last_post['author_name'],
'last_post' => $last_post['post_date'],
'author_mode' => $first_post['author_id'] ? 1 : 0,
        ];

        $db_string = $DB->compile_db_update_string($new_topic);

        $DB->query("UPDATE ibf_topics SET $db_string WHERE tid='$new_topic_id'");

        //----------------------------------------------------

        // NEW TOPIC: Reset the new_topic bit

        //----------------------------------------------------

        $DB->query("UPDATE ibf_posts SET new_topic=0 WHERE topic_id='$new_topic_id'");

        $DB->query("UPDATE ibf_posts SET new_topic=1 WHERE topic_id='$new_topic_id' AND pid='" . $first_post['pid'] . "'");

        unset($last_post);

        unset($first_post);

        unset($post_count);

        unset($new_topic);

        //----------------------------------------------------

        // OLD TOPIC: Get the last / first post in the "old" topic

        //----------------------------------------------------

        $DB->query("SELECT author_id, author_name, post_date FROM ibf_posts WHERE topic_id='" . $this->topic['tid'] . "' ORDER BY post_date DESC LIMIT 1");

        $last_post = $DB->fetch_row();

        $DB->query("SELECT pid, author_id, author_name, post_date FROM ibf_posts WHERE topic_id='" . $this->topic['tid'] . "' ORDER BY post_date ASC LIMIT 1");

        $first_post = $DB->fetch_row();

        // Get the number of posts in this "new" topic

        $DB->query("SELECT count(pid) as posts FROM ibf_posts WHERE queued <> 1 AND topic_id='" . $this->topic['tid'] . "'");

        $post_count = $DB->fetch_row();

        // Remove 1 from the count as we don't count the first post

        $post_count['posts']--;

        //----------------------------------------------------

        // OLD TOPIC: Update new topic entry in DB

        //----------------------------------------------------

        $new_topic = [
            'posts' => $post_count['posts'],
'starter_id' => $first_post['author_id'],
'starter_name' => $first_post['author_name'],
'start_date' => $first_post['post_date'],
'last_poster_id' => $last_post['author_id'],
'last_poster_name' => $last_post['author_name'],
'last_post' => $last_post['post_date'],
'author_mode' => $first_post['author_id'] ? 1 : 0,
        ];

        $db_string = $DB->compile_db_update_string($new_topic);

        $DB->query("UPDATE ibf_topics SET $db_string WHERE tid='" . $this->topic['tid'] . "'");

        //----------------------------------------------------

        // OLD TOPIC: Reset the new_topic bit

        //----------------------------------------------------

        $DB->query("UPDATE ibf_posts SET new_topic=0 WHERE topic_id='" . $this->topic['tid'] . "'");

        $DB->query("UPDATE ibf_posts SET new_topic=1 WHERE topic_id='" . $this->topic['tid'] . "' AND pid='" . $first_post['pid'] . "'");

        //----------------------------------------------------

        // Update the forum(s)

        //----------------------------------------------------

        $this->recount($this->topic['forum_id']);

        if ($this->topic['forum_id'] != $ibforums->input['fid']) {
            $this->recount($ibforums->input['fid']);
        }

        $this->moderate_log("Split topic '{$this->topic['title']}'");

        $print->redirect_screen($ibforums->lang['st_redirect'], 'act=SF&f=' . $this->forum['id']);
    }

    /*************************************************/

    // MERGE TOPICS:

    // ---------------

    /*************************************************/

    public function merge_start()
    {
        global $std, $ibforums, $DB, $print;

        $passed = 0;

        if (1 == $ibforums->member['g_is_supmod']) {
            $passed = 1;
        } elseif (1 == $this->moderator['split_merge']) {
            $passed = 1;
        } else {
            $passed = 0;
        }

        if (1 != $passed) {
            $this->moderate_error();
        }

        if (empty($this->topic['tid'])) {
            $this->moderate_error();
        }

        $this->output = $this->html_start_form(
            [
                1 => ['CODE', '61'],
2 => ['t', $this->topic['tid']],
3 => ['f', $this->forum['id']],
            ]
        );

        $this->output .= $this->html->table_top($ibforums->lang['mt_top'] . ' ' . $this->forum['name'] . ' &gt; ' . $this->topic['title']);

        $this->output .= $this->html->mod_exp($ibforums->lang['mt_explain']);

        $this->output .= $this->html->merge_body($this->topic['title'], $this->topic['description']);

        $this->output .= $this->html->end_form($ibforums->lang['mt_submit']);

        $this->page_title = $ibforums->lang['mt_top'] . ' ' . $this->topic['title'];

        $this->nav = [
            "<a href='{$this->base_url}&act=SF&f={$this->forum['id']}'>{$this->forum['name']}</a>",
            "<a href='{$this->base_url}&act=ST&f={$this->forum['id']}&t={$this->topic['tid']}'>{$this->topic['title']}</a>",
        ];
    }

    //=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+

    public function merge_complete()
    {
        global $std, $ibforums, $DB, $print;

        $passed = 0;

        if (1 == $ibforums->member['g_is_supmod']) {
            $passed = 1;
        } elseif (1 == $this->moderator['split_merge']) {
            $passed = 1;
        } else {
            $passed = 0;
        }

        if (1 != $passed) {
            $this->moderate_error();
        }

        if (empty($this->topic['tid'])) {
            $this->moderate_error();
        }

        //------------------------------------------

        // Check the input

        //------------------------------------------

        if ('' == $ibforums->input['topic_url'] or '' == $ibforums->input['title']) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'complete_form']);
        }

        //------------------------------------------

        // Get the topic ID of the entered URL

        //------------------------------------------

        preg_match("/(\?|&amp;)t=(\d+)($|&amp;)/", $ibforums->input['topic_url'], $match);

        $old_id = (int)trim($match[2]);

        if ('' == $old_id) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'mt_no_topic']);
        }

        //------------------------------------------

        // Get the topic from the DB

        //------------------------------------------

        $DB->query("SELECT tid, title, forum_id, last_post, last_poster_id, last_poster_name, posts, views FROM ibf_topics WHERE tid='$old_id'");

        if (!$old_topic = $DB->fetch_row()) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'mt_no_topic']);
        }

        //------------------------------------------

        // Did we try and merge the same topic?

        //------------------------------------------

        if ($old_id == $this->topic['tid']) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'mt_same_topic']);
        }

        //------------------------------------------

        // Do we have moderator permissions for this

        // topic (ie: in the forum the topic is in)

        //------------------------------------------

        $pass = false;

        if ($this->topic['forum_id'] == $old_topic['forum_id']) {
            $pass = true;
        } else {
            if (1 == $ibforums->member['g_is_supmod']) {
                $pass = true;
            } else {
                $DB->query('SELECT mid FROM ibf_moderators WHERE forum_id=' . $old_topic['forum_id'] . " AND (member_id='" . $ibforums->member['uid'] . "' OR (is_group=1 AND group_id='" . $ibforums->member['mgroup'] . "'))");

                if ($DB->get_num_rows()) {
                    $pass = true;
                }
            }
        }

        if (false === $pass) {
            // No, we don't have permission

            $this->moderate_error();
        }

        //----------------------------------------------------

        // Update the posts, remove old polls, subs and topic

        //----------------------------------------------------

        $DB->query("UPDATE ibf_posts SET forum_id='" . $this->topic['forum_id'] . "', topic_id='" . $this->topic['tid'] . "' WHERE topic_id='" . $old_topic['tid'] . "'");

        $DB->query("DELETE FROM ibf_polls WHERE tid='" . $old_topic['tid'] . "'");

        $DB->query("DELETE FROM ibf_voters WHERE tid='" . $old_topic['tid'] . "'");

        $DB->query("DELETE FROM ibf_tracker WHERE topic_id='" . $old_topic['tid'] . "'");

        $DB->query("DELETE FROM ibf_topics WHERE tid='" . $old_topic['tid'] . "'");

        //----------------------------------------------------

        // Update the newly merged topic

        //----------------------------------------------------

        $updater = [
            'title' => $ibforums->input['title'],
'description' => $ibforums->input['desc'],
        ];

        if ($old_topic['last_post'] > $this->topic['last_post']) {
            $updater['last_post'] = $old_topic['last_post'];

            $updater['last_poster_name'] = $old_topic['last_poster_name'];

            $updater['last_poster_id'] = $old_topic['last_poster_id'];
        }

        // We need to now count the original post, which isn't in the "posts" field 'cos it was a new topic

        $old_topic['posts']++;

        $str = $DB->compile_db_update_string($updater);

        $DB->query("UPDATE ibf_topics SET $str,views=views+{$old_topic['views']} WHERE tid='" . $this->topic['tid'] . "'");

        //----------------------------------------------------

        // Fix up the "new_topic" attribute.

        //----------------------------------------------------

        $DB->query("UPDATE ibf_posts SET new_topic=0 WHERE topic_id='" . $this->topic['tid'] . "'");

        $DB->query("SELECT pid, author_name, author_id, post_date FROM ibf_posts WHERE topic_id='" . $this->topic['tid'] . "' ORDER BY post_date ASC LIMIT 1");

        if ($first_post = $DB->fetch_row()) {
            $DB->query("UPDATE ibf_posts SET new_topic=1 WHERE pid='" . $first_post['pid'] . "'");
        }

        //----------------------------------------------------

        // Reset the post count for this topic

        //----------------------------------------------------

        $amode = $first_post['author_id'] ? 1 : 0;

        $DB->query("SELECT count(pid) as posts FROM ibf_posts WHERE queued <> 1 AND topic_id='" . $this->topic['tid'] . "'");

        if ($post_count = $DB->fetch_row()) {
            $post_count['posts']--; //Remove first post

            $DB->query(
                'UPDATE ibf_topics
			           SET posts=' . $post_count['posts'] . ",
			           starter_name='" . $first_post['author_name'] . "',
					   starter_id='" . $first_post['author_id'] . "',
					   start_date='" . $first_post['post_date'] . "',
					   author_mode=$amode
			           WHERE tid='" . $this->topic['tid'] . "'"
            );
        }

        //----------------------------------------------------

        // Update the forum(s)

        //----------------------------------------------------

        $this->recount($this->topic['forum_id']);

        if ($this->topic['forum_id'] != $old_topic['forum_id']) {
            $this->recount($old_topic['forum_id']);
        }

        $this->moderate_log("Merged topic '{$old_topic['title']}' with '{$this->topic['title']}'");

        $print->redirect_screen($ibforums->lang['mt_redirect'], 'act=ST&f=' . $this->forum['id'] . '&t=' . $this->topic['tid']);
    }

    /*************************************************/

    // UNSUBSCRIBE ALL FORM:

    // ---------------------

    /*************************************************/

    public function unsubscribe_all_form()
    {
        global $std, $ibforums, $DB, $print;

        $passed = 0;

        if (1 == $ibforums->member['g_is_supmod']) {
            $passed = 1;
        }

        if (1 != $passed) {
            $this->moderate_error();
        }

        if (empty($this->topic['tid'])) {
            $this->moderate_error();
        }

        $DB->query("SELECT COUNT(trid) as subbed FROM ibf_tracker WHERE topic_id='" . $this->topic['tid'] . "'");

        $tracker = $DB->fetch_row();

        /*if (! $tracker = $DB->fetch_row() )
        {
        	$this->moderate_error();
        }*/

        if ($tracker['subbed'] < 1) {
            $text = $ibforums->lang['ts_none'];
        } else {
            $text = sprintf($ibforums->lang['ts_count'], $tracker['subbed']);
        }

        $this->output = $this->html_start_form(
            [
                1 => ['CODE', '31'],
2 => ['t', $this->topic['tid']],
3 => ['f', $this->forum['id']],
            ]
        );

        $this->output .= $this->html->table_top($ibforums->lang['ts_title'] . ' &gt; ' . $this->forum['name'] . ' &gt; ' . $this->topic['title']);

        $this->output .= $this->html->mod_exp($text);

        $this->output .= $this->html->end_form($ibforums->lang['ts_submit']);

        $this->page_title = $ibforums->lang['ts_title'] . ' &gt; ' . $this->topic['title'];

        $this->nav = [
            "<a href='{$this->base_url}&act=SF&f={$this->forum['id']}'>{$this->forum['name']}</a>",
            "<a href='{$this->base_url}&act=ST&f={$this->forum['id']}&t={$this->topic['tid']}'>{$this->topic['title']}</a>",
        ];
    }

    //---------------------------------

    public function unsubscribe_all()
    {
        global $std, $ibforums, $DB, $print;

        $passed = 0;

        if (1 == $ibforums->member['g_is_supmod']) {
            $passed = 1;
        }

        if (1 != $passed) {
            $this->moderate_error();
        }

        if (empty($this->topic['tid'])) {
            $this->moderate_error();
        }

        // Delete the subbies based on this topic ID

        $DB->query("DELETE FROM ibf_tracker WHERE topic_id='" . $this->topic['tid'] . "'");

        $print->redirect_screen($ibforums->lang['ts_redirect'], 'act=ST&f=' . $this->forum['id'] . '&t=' . $this->topic['tid'] . '&st=' . $ibforums->input['st']);
    }

    /*************************************************/

    // EDIT POLL FORM:

    // ---------------

    /*************************************************/

    public function poll_delete_form()
    {
        global $std, $ibforums, $DB, $print;

        $passed = 0;

        if (1 == $ibforums->member['g_is_supmod']) {
            $passed = 1;
        } elseif (1 == $this->moderator['delete_topic']) {
            $passed = 1;
        } else {
            $passed = 0;
        }

        if (1 != $passed) {
            $this->moderate_error();
        }

        if (empty($this->topic['tid'])) {
            $this->moderate_error();
        }

        $DB->query("SELECT * FROM ibf_polls WHERE tid='" . $this->topic['tid'] . "'");

        $poll_data = $DB->fetch_row();

        if (!$poll_data['pid']) {
            $this->moderate_error();
        }

        $this->output = $this->html_start_form(
            [
                1 => ['CODE', '23'],
2 => ['t', $this->topic['tid']],
3 => ['f', $this->forum['id']],
            ]
        );

        $this->output .= $this->html->table_top($ibforums->lang['pd_top'] . ' ' . $this->forum['name'] . ' &gt; ' . $this->topic['title']);

        $this->output .= $this->html->mod_exp($ibforums->lang['pd_text']);

        $this->output .= $this->html->end_form($ibforums->lang['pd_submit']);

        $this->page_title = $ibforums->lang['pd_top'] . $this->topic['title'];

        $this->nav = [
            "<a href='{$this->base_url}&act=SF&f={$this->forum['id']}'>{$this->forum['name']}</a>",
            "<a href='{$this->base_url}&act=ST&f={$this->forum['id']}&t={$this->topic['tid']}'>{$this->topic['title']}</a>",
        ];
    }

    public function poll_delete_do()
    {
        global $std, $ibforums, $DB, $print;

        $passed = 0;

        if (1 == $ibforums->member['g_is_supmod']) {
            $passed = 1;
        } elseif (1 == $this->moderator['delete_topic']) {
            $passed = 1;
        } else {
            $passed = 0;
        }

        if (1 != $passed) {
            $this->moderate_error();
        }

        if (empty($this->topic['tid'])) {
            $this->moderate_error();
        }

        // Remove the poll

        $DB->query("DELETE FROM ibf_polls WHERE tid='" . $this->topic['tid'] . "'");

        // Remove from poll votes

        $DB->query("DELETE FROM ibf_voters WHERE tid='" . $this->topic['tid'] . "'");

        // Update topic

        $DB->query("UPDATE ibf_topics SET poll_state='', last_vote='', total_votes='' WHERE tid='" . $this->topic['tid'] . "'");

        // Boing!

        $print->redirect_screen($ibforums->lang['pd_redirect'], 'act=ST&f=' . $this->forum['id'] . '&t=' . $this->topic['tid'] . '&st=' . $ibforums->input['st']);
    }

    public function poll_edit_do()
    {
        global $std, $ibforums, $DB, $print;

        $passed = 0;

        if (1 == $ibforums->member['g_is_supmod']) {
            $passed = 1;
        } elseif (1 == $this->moderator['edit_post']) {
            $passed = 1;
        } else {
            $passed = 0;
        }

        if (1 != $passed) {
            $this->moderate_error();
        }

        if (empty($this->topic['tid'])) {
            $this->moderate_error();
        }

        $DB->query("SELECT * FROM ibf_polls WHERE tid='" . $this->topic['tid'] . "'");

        $poll_data = $DB->fetch_row();

        if (!$poll_data['pid']) {
            $this->moderate_error();
        }

        $poll_answers = unserialize(stripslashes($poll_data['choices']));

        reset($poll_answers);

        $new_poll_array = [];

        foreach ($poll_answers as $entry) {
            $id = $entry[0];

            $choice = $ibforums->input['POLL_' . $id];

            $votes = $entry[2];

            $new_poll_array[] = [$id, $choice, $votes];
        }

        $poll_data['choices'] = addslashes(serialize($new_poll_array));

        $DB->query(
            'UPDATE ibf_polls SET ' . "choices='" . $poll_data['choices'] . "', poll_question='" . $ibforums->input['poll_question'] . "' " . "WHERE tid='" . $this->topic['tid'] . "'"
        );

        //------------------------

        // Update the topic table to change the poll_only value.

        $poll_state = 1 == $ibforums->input['pollonly'] ? 'closed' : 'open';

        $DB->query("UPDATE ibf_topics SET poll_state='$poll_state' WHERE tid='" . $this->topic['tid'] . "'");

        $this->moderate_log('Edited a Poll');

        $print->redirect_screen($ibforums->lang['pe_done'], 'act=ST&f=' . $this->forum['id'] . '&t=' . $this->topic['tid'] . '&st=' . $ibforums->input['st']);
    }

    //--------------------------------------

    public function poll_edit_form()
    {
        global $std, $ibforums, $DB, $print;

        $passed = 0;

        if (1 == $ibforums->member['g_is_supmod']) {
            $passed = 1;
        } elseif (1 == $this->moderator['edit_post']) {
            $passed = 1;
        } else {
            $passed = 0;
        }

        if (1 != $passed) {
            $this->moderate_error();
        }

        if (empty($this->topic['tid'])) {
            $this->moderate_error();
        }

        $DB->query("SELECT * FROM ibf_polls WHERE tid='" . $this->topic['tid'] . "'");

        $poll_data = $DB->fetch_row();

        if (!$poll_data['pid']) {
            $this->moderate_error();
        }

        $this->output = $this->html_start_form(
            [
                1 => ['CODE', '21'],
2 => ['t', $this->topic['tid']],
3 => ['f', $this->forum['id']],
            ]
        );

        $this->output .= $this->html->table_top($ibforums->lang['pe_top'] . ' ' . $this->forum['name'] . ' &gt; ' . $this->topic['title']);

        $poll_answers = unserialize(stripslashes($poll_data['choices']));

        reset($poll_answers);

        foreach ($poll_answers as $entry) {
            $id = $entry[0];

            $choice = $entry[1];

            $votes = $entry[2];

            $this->output .= $this->html->poll_entry($id, $choice);
        }

        $this->output .= $this->html->poll_select_form($poll_data['poll_question']);

        $this->output .= $this->html->end_form($ibforums->lang['pe_submit']);

        $this->page_title = $ibforums->lang['pe_top'] . $this->topic['title'];

        $this->nav = [
            "<a href='{$this->base_url}&act=SF&f={$this->forum['id']}'>{$this->forum['name']}</a>",
            "<a href='{$this->base_url}&act=ST&f={$this->forum['id']}&t={$this->topic['tid']}'>{$this->topic['title']}</a>",
        ];
    }

    /*************************************************/

    // MOVE FORM:

    // ---------------

    /*************************************************/

    public function move_form()
    {
        global $std, $ibforums, $DB, $print;

        $passed = 0;

        if (1 == $ibforums->member['g_is_supmod']) {
            $passed = 1;
        } elseif (1 == $this->moderator['move_topic']) {
            $passed = 1;
        } else {
            $passed = 0;
        }

        if (1 != $passed) {
            $this->moderate_error();
        }

        if (empty($this->topic['tid'])) {
            $this->moderate_error();
        }

        $this->output = $this->html_start_form(
            [
                1 => ['CODE', '14'],
2 => ['tid', $this->topic['tid']],
3 => ['sf', $this->forum['id']],
            ]
        );

        $jump_html = $std->build_forum_jump('no_html');

        $this->output .= $this->html->table_top($ibforums->lang['top_move'] . ' ' . $this->forum['name'] . ' &gt; ' . $this->topic['title']);

        $this->output .= $this->html->mod_exp($ibforums->lang['move_exp']);

        $this->output .= $this->html->move_form($jump_html, $this->forum['name']);

        $this->output .= $this->html->end_form($ibforums->lang['submit_move']);

        $this->page_title = $ibforums->lang['t_move'] . ': ' . $this->topic['title'];

        $this->nav = [
            "<a href='{$this->base_url}&act=SF&f={$this->forum['id']}'>{$this->forum['name']}</a>",
            "<a href='{$this->base_url}&act=ST&f={$this->forum['id']}&t={$this->topic['tid']}'>{$this->topic['title']}</a>",
        ];
    }

    /*************************************************/

    public function do_move()
    {
        global $std, $ibforums, $DB, $print;

        $passed = 0;

        if (1 == $ibforums->member['g_is_supmod']) {
            $passed = 1;
        } elseif (1 == $this->moderator['move_topic']) {
            $passed = 1;
        } else {
            $passed = 0;
        }

        if (1 != $passed) {
            $this->moderate_error();
        }

        //----------------------------------

        // Check for input..

        //----------------------------------

        if ('' == $ibforums->input['sf']) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'move_no_source']);
        }

        //----------------------------------

        if ('' == $ibforums->input['move_id'] or -1 == $ibforums->input['move_id']) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'move_no_forum']);
        }

        //----------------------------------

        if ($ibforums->input['move_id'] == $ibforums->input['sf']) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'move_same_forum']);
        }

        //----------------------------------

        $DB->query('SELECT id, subwrap, sub_can_post, name FROM ibf_forums WHERE id IN(' . $ibforums->input['sf'] . ',' . $ibforums->input['move_id'] . ')');

        if (2 != $DB->get_num_rows()) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'move_no_forum']);
        }

        $source_name = '';

        $dest_name = '';

        //-----------------------------------

        // Check for an attempt to move into a subwrap forum

        //-----------------------------------

        while (false !== ($f = $DB->fetch_row())) {
            if ($f['id'] == $ibforums->input['sf']) {
                $source_name = $f['name'];
            } else {
                $dest_name = $f['name'];
            }

            if (1 == $f['subwrap'] and 1 != $f['sub_can_post']) {
                $std->Error(['LEVEL' => 1, 'MSG' => 'forum_no_post_allowed']);
            }
        }

        $DB->query("SELECT * FROM ibf_topics WHERE tid='" . $ibforums->input['tid'] . "'");

        if (!$this->topic = $DB->fetch_row()) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'move_no_forum']);
        }

        //----------------------------------

        // We know that $this->topic is set..

        //----------------------------------

        $source = $ibforums->input['sf'];

        $moveto = $ibforums->input['move_id'];

        $tid = $ibforums->input['tid'];

        //----------------------------------

        // Update the topic

        //----------------------------------

        $DB->query("UPDATE ibf_topics SET forum_id='$moveto' WHERE forum_id='$source' AND tid='$tid'");

        //----------------------------------

        // Update the posts

        //----------------------------------

        $DB->query("UPDATE ibf_posts SET forum_id='$moveto' WHERE forum_id='$source' AND topic_id='$tid'");

        //----------------------------------

        // Update the polls

        //----------------------------------

        $DB->query("UPDATE ibf_polls SET forum_id='$moveto' WHERE forum_id='$source' AND tid='$tid'");

        //----------------------------------

        // Are we leaving a link?

        //----------------------------------

        if ('y' == $ibforums->input['leave']) {
            //----------------------------------

            // Insert a new "link" topic...

            //----------------------------------

            $db_string = $DB->compile_db_insert_string(
                [
                    'title' => $this->topic['title'],
'description' => $this->topic['description'],
'state' => 'link',
'posts' => 0,
'views' => 0,
'starter_id' => $this->topic['starter_id'],
'start_date' => $this->topic['start_date'],
'starter_name' => $this->topic['starter_name'],
'last_post' => $this->topic['last_post'],
'forum_id' => $source,
'approved' => 1,
'pinned' => 0,
'moved_to' => $this->topic['tid'] . '&' . $moveto,
'last_poster_id' => $this->topic['last_poster_id'],
'last_poster_name' => $this->topic['last_poster_name'],
                ]
            );

            $DB->query('INSERT INTO ibf_topics (' . $db_string['FIELD_NAMES'] . ') VALUES (' . $db_string['FIELD_VALUES'] . ')');
        }

        $this->moderate_log("Moved a topic from $source_name to $dest_name");

        // Resync the forums..

        $this->recount($source);

        $this->recount($moveto);

        $print->redirect_screen($ibforums->lang['p_moved'], 'act=SF&f=' . $this->forum['id'] . '&st=' . $ibforums->input['st']);
    }

    /*************************************************/

    public function delete_post()
    {
        global $std, $ibforums, $DB, $print;

        // Get this post id.

        $DB->query("SELECT pid,attach_file, author_id, attach_id, post_date, new_topic from ibf_posts WHERE forum_id='" . $this->forum['id'] . "' AND topic_id='" . $this->topic['tid'] . "' and pid='" . $ibforums->input['p'] . "'");

        if (!$post = $DB->fetch_row()) {
            $this->moderate_error();
        }

        $passed = 0;

        if (1 == $ibforums->member['g_is_supmod']) {
            $passed = 1;
        } elseif (1 == $this->moderator['delete_post']) {
            $passed = 1;
        } elseif ((1 == $ibforums->member['g_delete_own_posts']) and ($ibforums->member['id'] == $post['author_id'])) {
            $passed = 1;
        } else {
            $passed = 0;
        }

        if (1 != $passed) {
            $this->moderate_error();
        }

        // Check to make sure that this isn't the first post in the topic..

        if (1 == $post['new_topic']) {
            $this->moderate_error('no_delete_post');
        }

        //---------------------------------------

        // Is there an attachment to this post?

        //---------------------------------------

        if ('' != $post['attach_id']) {
            if (is_file($this->upload_dir . '/' . $post['attach_id'])) {
                unlink($this->upload_dir . '/' . $post['attach_id']);
            }
        }

        //---------------------------------------

        // delete the post

        //---------------------------------------

        $DB->query("DELETE from ibf_posts WHERE topic_id='" . $this->topic['tid'] . "' and pid='" . $post['pid'] . "'");

        //---------------------------------------

        // Update the stats

        //---------------------------------------

        $DB->query('UPDATE ibf_stats SET TOTAL_REPLIES=TOTAL_REPLIES-1');

        //---------------------------------------

        // Decrease the users post count

        //---------------------------------------

        if ($this->forum['inc_postcount']) {
            $DB->query("UPDATE xbb_members SET posts=posts-1 WHERE uid='" . $post['author_id'] . "'");
        }

        //---------------------------------------

        // Get the latest post details

        //---------------------------------------

        $DB->query("SELECT post_date, author_id, author_name from ibf_posts WHERE topic_id='" . $this->topic['tid'] . "' and queued <> 1 ORDER BY pid DESC");

        $last_post = $DB->fetch_row();

        $DB->query(
            "UPDATE ibf_topics SET last_post='" . $last_post['post_date'] . "', " . "last_poster_id='" . $last_post['author_id'] . "', " . "last_poster_name='" . $last_post['author_name'] . "', " . "posts=posts-1 WHERE tid='" . $this->topic['tid'] . "'"
        );

        //---------------------------------------

        // If we deleted the last post in a topic that was

        // the last post in a forum, best update that :D

        //---------------------------------------

        if ($this->forum['last_id'] == $this->topic['tid']) {
            $DB->query(
                'SELECT title, tid, last_post, last_poster_id, last_poster_name ' . "FROM ibf_topics WHERE forum_id='" . $this->forum['id'] . "' AND approved=1 " . 'ORDER BY last_post DESC LIMIT 0,1'
            );

            $tt = $DB->fetch_row();

            $db_string = $DB->compile_db_update_string(
                [
                    last_title => $tt['title'] ?: '',
                    last_id => $tt['tid'] ?: '',
                    last_post => $tt['last_post'] ?: '',
                    last_poster_name => $tt['last_poster_name'] ?: '',
                    last_poster_id => $tt['last_poster_id'] ?: '',
                ]
            );

            $DB->query('UPDATE ibf_forums SET ' . $db_string . ",posts=posts-1 WHERE id='" . $this->forum['id'] . "'");
        }

        $this->moderate_log('Deleted a post');

        $print->redirect_screen($ibforums->lang['post_deleted'], 'act=ST&f=' . $this->forum['id'] . '&t=' . $this->topic['tid'] . '&st=' . $ibforums->input['st']);
    }

    public function rebuild_topic()
    {
    }

    /*************************************************/

    // DELETE TOPIC:

    // ---------------

    /*************************************************/

    public function delete_form()
    {
        global $std, $ibforums, $DB, $print;

        $passed = 0;

        if (1 == $ibforums->member['g_is_supmod']) {
            $passed = 1;
        } elseif (1 == $this->moderator['delete_topic']) {
            $passed = 1;
        } elseif ($this->topic['starter_id'] == $ibforums->member['uid']) {
            if (1 == $ibforums->member['g_delete_own_topics']) {
                $passed = 1;
            }
        } else {
            $passed = 0;
        }

        if (1 != $passed) {
            $this->moderate_error();
        }

        if (empty($this->topic['tid'])) {
            $this->moderate_error();
        }

        $this->output = $this->html->delete_js();

        $this->output .= $this->html_start_form(
            [
                1 => ['CODE', '08'],
2 => ['t', $this->topic['tid']],
            ]
        );

        $this->output .= $this->html->table_top($ibforums->lang['top_delete'] . ' ' . $this->forum['name'] . ' &gt; ' . $this->topic['title']);

        $this->output .= $this->html->mod_exp($ibforums->lang['delete_topic']);

        $this->output .= $this->html->end_form($ibforums->lang['submit_delete']);

        $this->page_title = $ibforums->lang['t_delete'] . ': ' . $this->topic['title'];

        $this->nav = [
            "<a href='{$this->base_url}&act=SF&f={$this->forum['id']}'>{$this->forum['name']}</a>",
            "<a href='{$this->base_url}&act=ST&f={$this->forum['id']}&t={$this->topic['tid']}'>{$this->topic['title']}</a>",
        ];
    }

    public function delete_topic()
    {
        global $std, $ibforums, $DB, $print;

        $passed = 0;

        if (1 == $ibforums->member['g_is_supmod']) {
            $passed = 1;
        } elseif (1 == $this->moderator['delete_topic']) {
            $passed = 1;
        } elseif ($this->topic['starter_id'] == $ibforums->member['uid']) {
            if (1 == $ibforums->member['g_delete_own_topics']) {
                $passed = 1;
            }
        } else {
            $passed = 0;
        }

        if (1 != $passed) {
            $this->moderate_error();
        }

        if (empty($this->topic['tid'])) {
            $this->moderate_error();
        }

        // Do we have a linked topic to remove?

        $DB->query("SELECT tid FROM ibf_topics WHERE state='link' AND moved_to='" . $this->topic['tid'] . '&' . $this->forum['id'] . "'");

        if ($linked_topic = $DB->fetch_row()) {
            $DB->query("DELETE FROM ibf_topics WHERE tid='" . $linked_topic['tid'] . "'");
        }

        // Remove polls assigned to this topic

        $DB->query("DELETE FROM ibf_polls WHERE tid='" . $this->topic['tid'] . "'");

        // Remove poll voters

        $DB->query("DELETE FROM ibf_voters WHERE tid='" . $this->topic['tid'] . "'");

        // Remove the topic itself

        $DB->query("DELETE FROM ibf_topics WHERE tid='" . $this->topic['tid'] . "'");

        // Get the attach ID's and filenames

        $DB->query("SELECT attach_id, attach_hits, attach_file FROM ibf_posts WHERE attach_id <> '' AND topic_id='" . $this->topic['tid'] . "'");

        // Remove the attachments

        if ($DB->get_num_rows()) {
            while (false !== ($r = $DB->fetch_row())) {
                if (is_file($this->upload_dir . '/' . $r['attach_id'])) {
                    @unlink($this->upload_dir . '/' . $r['attach_id']);
                }
            }
        }

        // Remove the posts

        $DB->query("DELETE FROM ibf_posts WHERE topic_id='" . $this->topic['tid'] . "'");

        //------------------------------------------------

        // Update the forum topic/post and stat counters.

        // We also need to make sure the last forum id and

        // title is correct.

        //------------------------------------------------

        $DB->query('SELECT COUNT(tid) as tcount from ibf_topics WHERE approved=1');

        $topics = $DB->fetch_row();

        $DB->query('SELECT COUNT(pid) as pcount from ibf_posts WHERE queued <> 1');

        $posts = $DB->fetch_row();

        $DB->query("SELECT COUNT(tid) as tcount from ibf_topics WHERE approved=1 and forum_id='" . $this->forum['id'] . "'");

        $f_topics = $DB->fetch_row();

        $DB->query("SELECT COUNT(pid) as pcount from ibf_posts WHERE queued <> 1 and forum_id='" . $this->forum['id'] . "'");

        $f_posts = $DB->fetch_row();

        $this->forum['topics'] = $f_topics['tcount'];

        $this->forum['posts'] = $f_posts['pcount'] - $f_topics['tcount'];

        $DB->query(
            'SELECT title, tid, last_post, last_poster_id, last_poster_name ' . "FROM ibf_topics WHERE forum_id='" . $this->forum['id'] . "' AND approved=1 " . 'ORDER BY last_post DESC LIMIT 0,1'
        );

        $tt = $DB->fetch_row();

        $db_string = $DB->compile_db_update_string(
            [
                last_title => $tt['title'] ?: '',
                last_id => $tt['tid'] ?: '',
                last_post => $tt['last_post'] ?: '',
                last_poster_name => $tt['last_poster_name'] ?: '',
                last_poster_id => $tt['last_poster_id'] ?: '',
                topics => $this->forum['topics'],
                posts => $this->forum['posts'],
            ]
        );

        $DB->query("UPDATE ibf_forums SET $db_string WHERE id='" . $this->forum['id'] . "'");

        // Update the main board stats.

        $posts = $posts['pcount'] - $topics['tcount'];

        $DB->query("UPDATE ibf_stats SET TOTAL_TOPICS='" . $topics['tcount'] . "', TOTAL_REPLIES='" . $posts . "'");

        $this->moderate_log('Deleted a topic');

        $print->redirect_screen($ibforums->lang['p_deleted'], 'act=SF&f=' . $this->forum['id']);
    }

    /*************************************************/

    // EDIT TOPIC:

    // ---------------

    /*************************************************/

    public function edit_form()
    {
        global $std, $ibforums, $DB, $print;

        $passed = 0;

        if (1 == $ibforums->member['g_is_supmod']) {
            $passed = 1;
        } elseif (1 == $this->moderator['edit_topic']) {
            $passed = 1;
        } else {
            $passed = 0;
        }

        if (1 != $passed) {
            $this->moderate_error();
        }

        if (empty($this->topic['tid'])) {
            $this->moderate_error();
        }

        $this->output = $this->html_start_form(
            [
                1 => ['CODE', '12'],
2 => ['t', $this->topic['tid']],
            ]
        );

        $this->output .= $this->html->table_top($ibforums->lang['top_edit'] . ' ' . $this->forum['name'] . ' &gt; ' . $this->topic['title']);

        $this->output .= $this->html->mod_exp($ibforums->lang['edit_topic']);

        $this->output .= $this->html->topictitle_fields($this->topic['title'], $this->topic['description']);

        $this->output .= $this->html->end_form($ibforums->lang['submit_edit']);

        $this->page_title = $ibforums->lang['t_edit'] . ': ' . $this->topic['title'];

        $this->nav = [
            "<a href='{$this->base_url}&act=SF&f={$this->forum['id']}'>{$this->forum['name']}</a>",
            "<a href='{$this->base_url}&act=ST&f={$this->forum['id']}&t={$this->topic['tid']}'>{$this->topic['title']}</a>",
        ];
    }

    public function do_edit()
    {
        global $std, $ibforums, $DB, $print;

        $passed = 0;

        if (1 == $ibforums->member['g_is_supmod']) {
            $passed = 1;
        } elseif (1 == $this->moderator['edit_topic']) {
            $passed = 1;
        } else {
            $passed = 0;
        }

        if (1 != $passed) {
            $this->moderate_error();
        }

        if (empty($this->topic['tid'])) {
            $this->moderate_error();
        }

        if ('' == $ibforums->input['TopicTitle']) {
            $std->Error(['LEVEL' => 2, 'MSG' => 'no_topic_title']);
        }

        $topic_title = preg_replace("/'/", "/\\'/", $ibforums->input['TopicTitle']);

        $topic_desc = preg_replace("/'/", "/\\'/", $ibforums->input['TopicDesc']);

        $DB->query("UPDATE ibf_topics SET title='$topic_title', description='$topic_desc' WHERE tid='" . $this->topic['tid'] . "'");

        if ($this->topic['tid'] == $this->forum['last_id']) {
            $DB->query("UPDATE ibf_forums SET last_title='$topic_title' WHERE id='" . $this->forum['id'] . "'");
        }

        $this->moderate_log('Edited a topic title');

        $print->redirect_screen($ibforums->lang['p_edited'], 'act=SF&f=' . $this->forum['id']);
    }

    /*************************************************/

    // OPEN TOPIC:

    // ---------------

    /*************************************************/

    public function open_topic()
    {
        global $std, $ibforums, $DB, $print;

        if ('open' == $this->topic['state']) {
            $this->moderate_error();
        }

        $passed = 0;

        if (1 == $ibforums->member['g_is_supmod']) {
            $passed = 1;
        } elseif ($this->topic['starter_id'] == $ibforums->member['uid']) {
            if (1 == $ibforums->member['g_open_close_posts']) {
                $passed = 1;
            }
        } else {
            $passed = 0;
        }

        if (1 == $this->moderator['open_topic']) {
            $passed = 1;
        }

        if (1 != $passed) {
            $this->moderate_error();
        }

        $this->alter_topic(['TOPIC' => $this->topic['tid'], 'FIELD' => 'state', 'VALUE' => 'open']);

        $this->moderate_log('Opened Topic');

        $print->redirect_screen($ibforums->lang['p_opened'], 'act=ST&f=' . $this->forum['id'] . '&t=' . $this->topic['tid'] . '&st=' . $ibforums->input['st']);
    }

    /*************************************************/

    // CLOSE TOPIC:

    // ---------------

    /*************************************************/

    public function close_topic()
    {
        global $std, $ibforums, $DB, $print;

        $passed = 0;

        if (1 == $ibforums->member['g_is_supmod']) {
            $passed = 1;
        } elseif ($this->topic['starter_id'] == $ibforums->member['uid']) {
            if (1 == $ibforums->member['g_open_close_posts']) {
                $passed = 1;
            }
        } else {
            $passed = 0;
        }

        if (1 == $this->moderator['close_topic']) {
            $passed = 1;
        }

        if (1 != $passed) {
            $this->moderate_error();
        }

        $this->alter_topic(['TOPIC' => $this->topic['tid'], 'FIELD' => 'state', 'VALUE' => 'closed']);

        $this->moderate_log('Locked Topic');

        $print->redirect_screen($ibforums->lang['p_closed'], 'act=SF&f=' . $this->forum['id']);
    }

    /*************************************************/

    // PIN TOPIC:

    // ---------------

    /*************************************************/

    public function pin_topic()
    {
        global $std, $ibforums, $DB, $print;

        if (1 == $this->topic['PIN_STATE']) {
            $this->moderate_error();
        }

        $passed = 0;

        if (1 == $ibforums->member['g_is_supmod']) {
            $passed = 1;
        } elseif (1 == $this->moderator['pin_topic']) {
            $passed = 1;
        } else {
            $passed = 0;
        }

        if (1 != $passed) {
            $this->moderate_error();
        }

        $this->alter_topic(['TOPIC' => $this->topic['tid'], 'FIELD' => 'pinned', 'VALUE' => '1']);

        $this->moderate_log('Pinned Topic');

        $print->redirect_screen($ibforums->lang['p_pinned'], 'act=ST&f=' . $this->forum['id'] . '&t=' . $this->topic['tid'] . '&st=' . $ibforums->input['st']);
    }

    /*************************************************/

    // UNPIN TOPIC:

    // ---------------

    /*************************************************/

    public function unpin_topic()
    {
        global $std, $ibforums, $DB, $print;

        if (0 == $this->topic['pinned']) {
            $this->moderate_error();
        }

        $passed = 0;

        if (1 == $ibforums->member['g_is_supmod']) {
            $passed = 1;
        } elseif (1 == $this->moderator['unpin_topic']) {
            $passed = 1;
        } else {
            $passed = 0;
        }

        if (1 != $passed) {
            $this->moderate_error();
        }

        $this->alter_topic(['TOPIC' => $this->topic['tid'], 'FIELD' => 'pinned', 'VALUE' => '0']);

        $this->moderate_log('Unpinned Topic');

        $print->redirect_screen($ibforums->lang['p_unpinned'], 'act=ST&f=' . $this->forum['id'] . '&t=' . $this->topic['tid'] . '&st=' . $ibforums->input['st']);
    }

    //+---------------------------------------------------------------------------------------------

    /*************************************************/

    // MODERATE ERROR:

    // ---------------

    // Function for error messages in this script

    /*************************************************/

    public function moderate_error($msg = 'moderate_no_permission')
    {
        global $std;

        $std->Error(['LEVEL' => 2, 'MSG' => $msg]);

        // Make sure we exit..

        exit();
    }

    /*************************************************/

    // MODERATE LOG:

    // ---------------

    // Function for adding the mod action to the DB

    /*************************************************/

    public function moderate_log($title = 'unknown')
    {
        global $std, $ibforums, $DB, $HTTP_REFERER, $QUERY_STRING;

        $db_string = $std->compile_db_string(
            [
                'forum_id' => $ibforums->input['f'],
'topic_id' => $ibforums->input['t'],
'post_id' => $ibforums->input['p'],
'member_id' => $ibforums->member['uid'],
'member_name' => $ibforums->member['uname'],
'ip_address' => $ibforums->input['IP_ADDRESS'],
'http_referer' => $HTTP_REFERER,
'ctime' => time(),
'topic_title' => $this->topic['title'],
'action' => $title,
'query_string' => $QUERY_STRING,
            ]
        );

        $DB->query('INSERT INTO ibf_moderator_logs (' . $db_string['FIELD_NAMES'] . ') VALUES (' . $db_string['FIELD_VALUES'] . ')');
    }

    /*************************************************/

    // Re Count topics for the forums:

    // ---------------

    // Handles simple moderation functions, saves on

    // writing the same code over and over.

    // ASS_U_ME's that the requesting user has been

    // authenticated by this stage.

    /*************************************************/

    public function recount($fid = '')
    {
        global $ibforums, $root_path, $DB, $std;

        if ('' == $fid) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'move_no_source']);
        }

        // Get the topics..

        $DB->query("SELECT COUNT(tid) as count FROM ibf_topics WHERE approved=1 and forum_id='" . $fid . "'");

        $topics = $DB->fetch_row();

        // Get the posts..

        $DB->query("SELECT COUNT(pid) as count FROM ibf_posts WHERE queued <> 1 and forum_id='" . $fid . "'");

        $posts = $DB->fetch_row();

        // Get the forum last poster..

        $DB->query("SELECT tid, title, last_poster_id, last_poster_name, last_post FROM ibf_topics WHERE approved=1 and forum_id='" . $fid . "' ORDER BY last_post DESC LIMIT 0,1");

        $last_post = $DB->fetch_row();

        // Get real post count by removing topic starting posts from the count

        $real_posts = $posts['count'] - $topics['count'];

        // Reset this forums stats

        $db_string = $DB->compile_db_update_string(
            [
                'last_poster_id' => $last_post['last_poster_id'],
'last_poster_name' => $last_post['last_poster_name'],
'last_post' => $last_post['last_post'],
'last_title' => $last_post['title'],
'last_id' => $last_post['tid'],
'topics' => $topics['count'],
'posts' => $real_posts,
            ]
        );

        $DB->query("UPDATE ibf_forums SET $db_string WHERE id='" . $fid . "'");
    }

    /*************************************************/

    // ALTER TOPIC:

    // ---------------

    // Handles simple moderation functions, saves on

    // writing the same code over and over.

    // ASS_U_ME's that the requesting user has been

    // authenticated by this stage.

    /*************************************************/

    public function alter_topic($data = [])
    {
        global $ibforums, $DB;

        if ('' == $data['FIELD']) {
            return -1;
        }

        if ('' == $data['VALUE']) {
            return -1;
        }

        if ('' == $data['TOPIC']) {
            return -1;
        }

        if ($data['TOPIC'] < 1) {
            return -1;
        }

        $data['VALUE'] = preg_replace("/'/", "\\'", $data['VALUE']);

        $DB->query('UPDATE ibf_topics SET ' . $data['FIELD'] . "='" . $data['VALUE'] . "' WHERE tid='" . $data['TOPIC'] . "'");
    }

    /*****************************************************/

    // HTML: start form.

    // ------------------

    // Returns the HTML for the <FORM> opening tag

    /*****************************************************/

    public function html_start_form($additional_tags = [])
    {
        global $ibforums;

        $form = "<form action='{$this->base_url}' method='POST' name='REPLIER'>"
                . "<input type='hidden' name='st' value='"
                . $ibforums->input[st]
                . "'>"
                . "<input type='hidden' name='act' value='Mod'>"
                . "<input type='hidden' name='s' value='"
                . $ibforums->session_id
                . "'>"
                . "<input type='hidden' name='f' value='"
                . $this->forum['id']
                . "'>";

        // Any other tags to add?

        if (isset($additional_tags)) {
            foreach ($additional_tags as $k => $v) {
                $form .= "\n<input type='hidden' name='{$v[0]}' value='{$v[1]}'>";
            }
        }

        return $form;
    }
}

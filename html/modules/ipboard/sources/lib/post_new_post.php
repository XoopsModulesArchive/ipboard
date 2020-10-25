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
|   > New Post module
|   > Module written by Matt Mecham
|   > Date started: 17th February 2002
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

class post_functions extends Post
{
    public $nav = [];

    public $title = '';

    public $post = [];

    public $topic = [];

    public $upload = [];

    public $mod_topic = [];

    public $m_group = '';

    public function __construct($class)
    {
        global $ibforums, $std, $DB;

        // Lets do some tests to make sure that we are allowed to start a new topic

        $this->m_group = $ibforums->member['mgroup'];

        if (!$ibforums->member['g_post_new_topics']) {
            $std->Error([LEVEL => 1, MSG => 'no_starting']);
        }

        if ('*' != $class->forum['start_perms']) {
            if (!preg_match("/(^|,)$this->m_group(,|$)/", $class->forum['start_perms'])) {
                $std->Error([LEVEL => 1, MSG => 'no_starting']);
            }
        }
    }

    public function process($class)
    {
        global $ibforums, $std, $DB, $print, $_POST;

        //-------------------------------------------------

        // Parse the post, and check for any errors.

        //-------------------------------------------------

        $this->post = $class->compile_post();

        //-------------------------------------------------

        // check to make sure we have a valid topic title

        //-------------------------------------------------

        $ibforums->input['TopicTitle'] = str_replace('<br>', '', $ibforums->input['TopicTitle']);

        $ibforums->input['TopicTitle'] = trim(stripslashes($ibforums->input['TopicTitle']));

        if ((mb_strlen($ibforums->input['TopicTitle']) < 2) or (!$ibforums->input['TopicTitle'])) {
            $class->obj['post_errors'] = 'no_topic_title';
        }

        if (mb_strlen($_POST['TopicTitle']) > 64) {
            $class->obj['post_errors'] = 'topic_title_long';
        }

        //-------------------------------------------------

        // If we don't have any errors yet, parse the upload

        //-------------------------------------------------

        if ('' == $class->obj['post_errors']) {
            $this->upload = $class->process_upload();
        }

        if (('' != $class->obj['post_errors']) or ('' != $class->obj['preview_post'])) {
            // Show the form again

            $this->show_form($class);
        } else {
            $this->add_new_topic($class);
        }
    }

    public function add_new_topic($class)
    {
        global $ibforums, $std, $DB, $print;

        //-------------------------------------------------

        // Fix up the topic title

        //-------------------------------------------------

        if ($ibforums->vars['etfilter_punct']) {
            $ibforums->input['TopicTitle'] = preg_replace("/\?{1,}/", '?', $ibforums->input['TopicTitle']);

            $ibforums->input['TopicTitle'] = preg_replace('/(&#33;){1,}/', '&#33;', $ibforums->input['TopicTitle']);
        }

        if ($ibforums->vars['etfilter_shout']) {
            $ibforums->input['TopicTitle'] = ucwords(mb_strtolower($ibforums->input['TopicTitle']));
        }

        $ibforums->input['TopicTitle'] = $class->parser->bad_words($ibforums->input['TopicTitle']);

        $ibforums->input['TopicDesc'] = $class->parser->bad_words($ibforums->input['TopicDesc']);

        $pinned = 0;

        $state = 'open';

        if (('' != $ibforums->input['mod_options']) or ('nowt' != $ibforums->input['mod_options'])) {
            if ('pin' == $ibforums->input['mod_options']) {
                if (1 == $ibforums->member['g_is_supmod'] or 1 == $class->moderator['pin_topic']) {
                    $pinned = 1;

                    $class->moderate_log('Pinned topic from post form', $ibforums->input['TopicTitle']);
                }
            } elseif ('close' == $ibforums->input['mod_options']) {
                if (1 == $ibforums->member['g_is_supmod'] or 1 == $class->moderator['close_topic']) {
                    $state = 'closed';

                    $class->moderate_log('Closed topic from post form', $ibforums->input['TopicTitle']);
                }
            }
        }

        //-------------------------------------------------

        // Build the master array

        //-------------------------------------------------

        $this->topic = [
            'title' => $ibforums->input['TopicTitle'],
'description' => $ibforums->input['TopicDesc'],
'state' => $state,
'posts' => 0,
'starter_id' => $ibforums->member['uid'],
'starter_name' => $ibforums->member['uid'] ? $ibforums->member['uname'] : $ibforums->input['UserName'],
'start_date' => time(),
'last_poster_id' => $ibforums->member['uid'],
'last_poster_name' => $ibforums->member['uid'] ? $ibforums->member['uname'] : $ibforums->input['UserName'],
'last_post' => time(),
'icon_id' => $ibforums->input['iconid'],
'author_mode' => $ibforums->member['uid'] ? 1 : 0,
'poll_state' => 0,
'last_vote' => 0,
'views' => 0,
'forum_id' => $class->forum['id'],
'approved' => $class->obj['moderate'] ? 0 : 1,
'pinned' => $pinned,
        ];

        //-------------------------------------------------

        // Insert the topic into the database to get the

        // last inserted value of the auto_increment field

        // follow suit with the post

        //-------------------------------------------------

        $db_string = $DB->compile_db_insert_string($this->topic);

        $DB->query('INSERT INTO ibf_topics (' . $db_string['FIELD_NAMES'] . ') VALUES (' . $db_string['FIELD_VALUES'] . ')');

        $this->post['topic_id'] = $DB->get_insert_id();

        $this->topic['tid'] = $this->post['topic_id'];

        /*---------------------------------------------------*/

        // Update the post info with the upload array info

        $this->post['attach_id'] = $this->upload['attach_id'];

        $this->post['attach_type'] = $this->upload['attach_type'];

        $this->post['attach_hits'] = $this->upload['attach_hits'];

        $this->post['attach_file'] = $this->upload['attach_file'];

        $this->post['new_topic'] = 1;

        $db_string = $DB->compile_db_insert_string($this->post);

        $DB->query('INSERT INTO ibf_posts (' . $db_string['FIELD_NAMES'] . ') VALUES (' . $db_string['FIELD_VALUES'] . ')');

        $this->post['pid'] = $DB->get_insert_id();

        if ($class->obj['moderate']) {
            // Redirect them with a message telling them the post has to be previewed first

            $print->redirect_screen($ibforums->lang['moderate_topic'], "act=SF&f={$class->forum['id']}");
        }

        //-------------------------------------------------

        // If we are still here, lets update the

        // board/forum stats

        //-------------------------------------------------

        $class->forum['last_title'] = $this->topic['title'];

        $class->forum['last_id'] = $this->topic['tid'];

        $class->forum['last_post'] = time();

        $class->forum['last_poster_name'] = $ibforums->member['uid'] ? $ibforums->member['uname'] : $ibforums->input['UserName'];

        $class->forum['last_poster_id'] = $ibforums->member['uid'];

        $class->forum['topics']++;

        // Update the database

        $DB->query(
            "UPDATE ibf_forums    SET last_title='"
            . $class->forum['last_title']
            . "', "
            . "last_id='"
            . $class->forum['last_id']
            . "', "
            . "last_post='"
            . $class->forum['last_post']
            . "', "
            . "last_poster_name='"
            . $class->forum['last_poster_name']
            . "', "
            . "last_poster_id='"
            . $class->forum['last_poster_id']
            . "', "
            . "topics='"
            . $class->forum['topics']
            . "' "
            . "WHERE id='"
            . $class->forum['id']
            . "'"
        );

        $DB->query('UPDATE ibf_stats SET TOTAL_TOPICS=TOTAL_TOPICS+1');

        //-------------------------------------------------

        // Are we tracking new topics we start 'auto_track'?

        //-------------------------------------------------

        if (1 == $ibforums->member['auto_track']) {
            $db_string = $DB->compile_db_insert_string(
                [
                    'member_id' => $ibforums->member['uid'],
'topic_id' => $this->topic['tid'],
'start_date' => time(),
                ]
            );

            $DB->query("INSERT INTO ibf_tracker ({$db_string['FIELD_NAMES']}) VALUES ({$db_string['FIELD_VALUES']})");
        }

        //---------------------------------------------------------------

        // Are we tracking this forum? If so generate some mailies - yay!

        //---------------------------------------------------------------

        $class->forum_tracker($class->forum['id'], $this->topic['tid'], $this->topic['title'], $class->forum['name']);

        //-------------------------------------------------

        // If we are a member, lets update thier last post

        // date and increment their post count.

        //-------------------------------------------------

        $pcount = '';

        if ($ibforums->member['uid']) {
            if ($class->forum['inc_postcount']) {
                // Increment the users post count

                $pcount = 'posts=posts+1, ';
            }

            // Are we checking for auto promotion?

            if ('-1&-1' != $ibforums->member['g_promotion']) {
                [$gid, $gposts] = explode('&', $ibforums->member['g_promotion']);

                if ($gid > 0 and $gposts > 0) {
                    if ($gposts <= $ibforums->member['posts'] + 1) {
                        $mgroup = "mgroup='$gid', ";
                    }
                }
            }

            $ibforums->member['last_post'] = time();

            $DB->query(
                'UPDATE xbb_members SET ' . $pcount . $mgroup . "last_post='" . $ibforums->member['last_post'] . "'" . "WHERE uid='" . $ibforums->member['uid'] . "'"
            );
        }

        //-------------------------------------------------

        // Redirect them back to the topic

        //-------------------------------------------------

        $std->boink_it($class->base_url . "&act=ST&f={$class->forum['id']}&t={$this->topic['tid']}");
    }

    public function show_form($class)
    {
        global $ibforums, $std, $DB, $print, $_POST;

        // Sort out the "raw" textarea input and make it safe incase

        // we have a <textarea> tag in the raw post var.

        $raw_post = $_POST['Post'] ?? '';

        $topic_title = isset($_POST['TopicTitle']) ? $ibforums->input['TopicTitle'] : '';

        $topic_desc = isset($_POST['TopicDesc']) ? $ibforums->input['TopicDesc'] : '';

        if (isset($raw_post)) {
            $raw_post = str_replace('$', '&#036;', htmlspecialchars($raw_post, ENT_QUOTES | ENT_HTML5));

            $raw_post = stripslashes($raw_post);
        }

        // Do we have any posting errors?

        if ($class->obj['post_errors']) {
            $class->output .= $class->html->errors($ibforums->lang[$class->obj['post_errors']]);
        }

        if ($class->obj['preview_post']) {
            $class->output .= $class->html->preview($class->parser->convert(['TEXT' => $this->post['post'], 'CODE' => $class->forum['use_ibc'], 'SMILIES' => $ibforums->input['enableemo'], 'HTML' => $class->forum['use_html']]));
        }

        $class->check_upload_ability();

        $class->output .= $class->html_start_form([1 => ['CODE', '01']]);

        //---------------------------------------

        // START TABLE

        //---------------------------------------

        $class->output .= $class->html->table_structure();

        //---------------------------------------

        $topic_title = $class->html->topictitle_fields([TITLE => $topic_title, DESC => $topic_desc]);

        $start_table = $class->html->table_top("{$ibforums->lang['top_txt_new']} {$class->forum['name']}");

        $name_fields = $class->html_name_field();

        $post_box = $class->html_post_body($raw_post);

        $mod_options = $class->mod_options();

        $end_form = $class->html->EndForm($ibforums->lang['submit_new']);

        $post_icons = $class->html_post_icons();

        if ($class->obj['can_upload']) {
            $upload_field = $class->html->Upload_field($ibforums->member['g_attach_max'] * 1024);
        }

        //---------------------------------------

        $class->output = preg_replace('/<!--START TABLE-->/', (string)$start_table, $class->output);

        $class->output = preg_replace('/<!--NAME FIELDS-->/', (string)$name_fields, $class->output);

        $class->output = preg_replace('/<!--POST BOX-->/', (string)$post_box, $class->output);

        $class->output = preg_replace('/<!--POST ICONS-->/', (string)$post_icons, $class->output);

        $class->output = preg_replace('/<!--UPLOAD FIELD-->/', (string)$upload_field, $class->output);

        $class->output = preg_replace('/<!--MOD OPTIONS-->/', (string)$mod_options, $class->output);

        $class->output = preg_replace('/<!--END TABLE-->/', (string)$end_form, $class->output);

        $class->output = preg_replace('/<!--TOPIC TITLE-->/', (string)$topic_title, $class->output);

        //---------------------------------------

        $class->html_add_smilie_box();

        $this->nav = [
            "<a href='{$class->base_url}&act=SC&c={$class->forum['cat_id']}'>{$class->forum['cat_name']}</a>",
            "<a href='{$class->base_url}&act=SF&f={$class->forum['id']}'>{$class->forum['name']}</a>",
        ];

        $this->title = $ibforums->lang['posting_new_topic'];

        $print->add_output((string)$class->output);

        $print->do_output(
            [
                'TITLE' => $ibforums->vars['board_name'] . ' -> ' . $this->title,
'JS' => 1,
'NAV' => $this->nav,
            ]
        );
    }
}

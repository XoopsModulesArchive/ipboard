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
|
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

    public $poll_count = 0;

    public $poll_choices = '';

    public $m_group = '';

    public function __construct($class)
    {
        global $ibforums, $std, $DB;

        // Lets do some tests to make sure that we are allowed to start a new topic

        if (!$ibforums->member['g_post_polls']) {
            $std->Error([LEVEL => 1, MSG => 'no_start_polls']);
        }

        if (!$class->forum['allow_poll']) {
            $std->Error([LEVEL => 1, MSG => 'no_start_polls']);
        }

        $this->m_group = $ibforums->member['mgroup'];

        if ('*' != $class->forum['start_perms']) {
            if (!preg_match("/(^|,)$this->m_group(,|$)/", $class->forum['start_perms'])) {
                $std->Error([LEVEL => 1, MSG => 'no_start_polls']);
            }
        }
    }

    public function process($class)
    {
        global $ibforums, $std, $DB, $print;

        //-------------------------------------------------

        // Parse the post, and check for any errors.

        //-------------------------------------------------

        $this->post = $class->compile_post();

        //-------------------------------------------------

        // check to make sure we have a valid topic title

        //-------------------------------------------------

        if ((mb_strlen($ibforums->input['TopicTitle']) < 2) or (!$ibforums->input['TopicTitle'])) {
            $class->obj['post_errors'] = 'no_topic_title';
        }

        if (mb_strlen($ibforums->input['TopicTitle']) > 64) {
            $class->obj['post_errors'] = 'topic_title_long';
        }

        //-------------------------------------------------

        // check to make sure we have a correct # of choices

        //-------------------------------------------------

        $this->poll_choices = $ibforums->input['PollAnswers'];

        $this->poll_choices = preg_replace('/<br><br>/', '', $this->poll_choices);

        $this->poll_choices = preg_replace('/<br>/e', '$this->regex_count_choices()', $this->poll_choices);

        if ($this->poll_count > 10) {
            $class->obj['post_errors'] = 'poll_to_many';
        }

        if ($this->poll_count < 1) {
            $class->obj['post_errors'] = 'poll_not_enough';
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
            $this->add_new_poll($class);
        }
    }

    public function add_new_poll($class)
    {
        global $ibforums, $std, $DB, $print;

        //-------------------------------------------------

        // Sort out the poll stuff

        // This is somewhat contrived, but it has to be

        // compatible with the current perl version.

        //-------------------------------------------------

        $poll_array = [];

        $count = 0;

        $polls = explode('<br>', $this->poll_choices);

        foreach ($polls as $polling) {
            if ('' == $polling) {
                continue;
            }

            $poll_array[] = [$count, $polling, 0];

            $count++;
        }

        //-------------------------------------------------

        // Fix up the topic title

        //-------------------------------------------------

        if ($ibforums->vars['etfilter_punct']) {
            $ibforums->input['TopicTitle'] = preg_replace("/\?{1,}/", '?', $ibforums->input['TopicTitle']);

            $ibforums->input['TopicTitle'] = preg_replace('/(&#33;){1,}/', '&#33;', $ibforums->input['TopicTitle']);
        }

        if ($ibforums->vars['etfilter_shout']) {
            $ibforums->input['TopicTitle'] = ucwords($ibforums->input['TopicTitle']);
        }

        $ibforums->input['TopicTitle'] = $class->parser->bad_words($ibforums->input['TopicTitle']);

        $ibforums->input['TopicDesc'] = $class->parser->bad_words($ibforums->input['TopicDesc']);

        //-------------------------------------------------

        // Build the master array

        //-------------------------------------------------

        $this->topic = [
            'title' => $ibforums->input['TopicTitle'],
'description' => $ibforums->input['TopicDesc'],
'state' => 'open',
'posts' => 0,
'starter_id' => $ibforums->member['uid'],
'starter_name' => $ibforums->member['uid'] ? $ibforums->member['uname'] : $ibforums->input['UserName'],
'start_date' => time(),
'last_poster_id' => $ibforums->member['uid'],
'last_poster_name' => $ibforums->member['uid'] ? $ibforums->member['uname'] : $ibforums->input['UserName'],
'last_post' => time(),
'icon_id' => $ibforums->input['iconid'],
'author_mode' => $ibforums->member['uid'] ? 1 : 0,
'poll_state' => 0 == $ibforums->input['allow_disc'] ? 'open' : 'closed',
'last_vote' => 0,
'views' => 0,
'forum_id' => $class->forum['id'],
'approved' => $class->obj['moderate'] ? 0 : 1,
'pinned' => 0,
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

        $this->post['new_topic'] = 1;

        $db_string = $DB->compile_db_insert_string($this->post);

        $DB->query('INSERT INTO ibf_posts (' . $db_string['FIELD_NAMES'] . ') VALUES (' . $db_string['FIELD_VALUES'] . ')');

        $this->post['pid'] = $DB->get_insert_id();

        //-------------------------------------------------

        // Add the poll to the forum_polls table

        // if we are moderating this post

        //-------------------------------------------------

        $db_string = $std->compile_db_string(
            [
                'tid' => $this->topic['tid'],
'forum_id' => $class->forum['id'],
'start_date' => time(),
'choices' => addslashes(serialize($poll_array)),
'starter_id' => $ibforums->member['uid'],
'votes' => 0,
'poll_question' => $class->parser->bad_words($ibforums->input['pollq']),
            ]
        );

        $DB->query('INSERT INTO ibf_polls (' . $db_string['FIELD_NAMES'] . ') VALUES (' . $db_string['FIELD_VALUES'] . ')');

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

        // At this point in time, we'll scan the database for the correct number

        // of topics - if this proves to database intensive, we'll simply increment

        // the value stored in the ibf_stats database.

        $DB->query('SELECT COUNT(tid) AS topic_cnt FROM ibf_topics');

        $stats = $DB->fetch_row();

        $DB->query("UPDATE ibf_stats SET TOTAL_TOPICS='" . $stats['topic_cnt'] . "'");

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

        // Set a last post time cookie

        //-------------------------------------------------

        $std->my_setcookie('LPid', time());

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

        $topic_title = isset($_POST['TopicTitle']) ? str_replace("'", '&#39;', stripslashes($_POST['TopicTitle'])) : '';

        $topic_desc = isset($_POST['TopicDesc']) ? str_replace("'", '&#39;', stripslashes($_POST['TopicDesc'])) : '';

        $poll = isset($_POST['PollAnswers']) ? str_replace("'", '&#39;', stripslashes($_POST['PollAnswers'])) : '';

        if (isset($raw_post)) {
            $raw_post = preg_replace('/<textarea>/', '&lt;textarea>', $raw_post);

            $raw_post = str_replace('$', '&#036;', $raw_post);

            $raw_post = str_replace('<%', '&lt;%', $raw_post);

            $raw_post = stripslashes($raw_post);
        }

        // Do we have any posting errors?

        if ($class->obj['post_errors']) {
            $class->output .= $class->html->errors($ibforums->lang[$class->obj['post_errors']]);
        }

        if ($class->obj['preview_post']) {
            $class->output .= $class->html->preview($class->parser->convert(['TEXT' => $this->post['post'], 'CODE' => $class->forum['use_ibc'], 'SMILIES' => $ibforums->input['enableemo'], 'HTML' => $class->forum['use_html']]));
        }

        $extra = '';

        if ($ibforums->vars['poll_tags']) {
            $extra = $ibforums->lang['poll_tag_allowed'];
        }

        $class->output .= $class->html_start_form([1 => ['CODE', '11'], 2 => ['f', $class->forum['id']]]);

        //---------------------------------------

        // START TABLE

        //---------------------------------------

        $class->output .= $class->html->table_structure();

        //---------------------------------------

        $topic_title = $class->html->topictitle_fields([TITLE => $topic_title, DESC => $topic_desc]);

        $start_table = $class->html->table_top("{$ibforums->lang['top_txt_poll']}: {$class->forum['name']}");

        $name_fields = $class->html_name_field();

        $post_box = $class->html_post_body($raw_post);

        $mod_options = $class->mod_options();

        $poll_box = $class->html->poll_box($poll, $extra);

        $end_form = $class->html->EndForm($ibforums->lang['submit_poll']);

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

        //$class->output = preg_replace( "/<!--MOD OPTIONS-->/" , "$mod_options"  , $class->output );

        $class->output = preg_replace('/<!--END TABLE-->/', (string)$end_form, $class->output);

        $class->output = preg_replace('/<!--TOPIC TITLE-->/', (string)$topic_title, $class->output);

        $class->output = preg_replace('/<!--POLL BOX-->/', (string)$poll_box, $class->output);

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

    public function regex_count_choices()
    {
        ++$this->poll_count;

        return '<br>';
    }
}

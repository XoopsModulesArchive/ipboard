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
|   > Reply post module
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

    public $m_group = '';

    public function __construct($class)
    {
        global $ibforums, $std, $DB;

        // Lets load the topic from the database before we do anything else.

        $DB->query("SELECT * FROM ibf_topics WHERE forum_id='" . $class->forum['id'] . "' AND tid='" . $ibforums->input['t'] . "'");

        $this->topic = $DB->fetch_row();

        // Is it legitimate?

        if (!$this->topic['tid']) {
            $std->Error([LEVEL => 1, MSG => 'missing_files']);
        }

        //-------------------------------------------------

        // Lets do some tests to make sure that we are

        // allowed to reply to this topic

        //-------------------------------------------------

        if ('closed' == $this->topic['poll_state'] and 1 != $ibforums->member['g_is_supadmin']) {
            $std->Error([LEVEL => 1, MSG => 'no_replies']);
        }

        if ($this->topic['starter_id'] == $ibforums->member['uid']) {
            if (!$ibforums->member['g_reply_own_topics']) {
                $std->Error([LEVEL => 1, MSG => 'no_replies']);
            }
        }

        if ($this->topic['starter_id'] != $ibforums->member['uid']) {
            if (!$ibforums->member['g_reply_own_topics']) {
                $std->Error([LEVEL => 1, MSG => 'no_replies']);
            }
        }

        $this->m_group = $ibforums->member['mgroup'];

        if ('*' != $class->forum['reply_perms']) {
            if (!preg_match("/(^|,)$this->m_group(,|$)/", $class->forum['reply_perms'])) {
                $std->Error([LEVEL => 1, MSG => 'no_replies']);
            }
        }

        // Is the topic locked?

        if ('open' != $this->topic['state']) {
            if (1 != $ibforums->member['g_post_closed']) {
                $std->Error([LEVEL => 1, MSG => 'locked_topic']);
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

        if ('' == $class->obj['post_errors']) {
            $this->upload = $class->process_upload();
        }

        if (('' != $class->obj['post_errors']) or ('' != $class->obj['preview_post'])) {
            // Show the form again

            $this->show_form($class);
        } else {
            $this->add_reply($class);
        }
    }

    public function add_reply($class)
    {
        global $ibforums, $std, $DB, $print;

        //-------------------------------------------------

        // Update the post info with the upload array info

        //-------------------------------------------------

        $this->post['attach_id'] = $this->upload['attach_id'];

        $this->post['attach_type'] = $this->upload['attach_type'];

        $this->post['attach_hits'] = $this->upload['attach_hits'];

        $this->post['attach_file'] = $this->upload['attach_file'];

        //-------------------------------------------------

        // Insert the post into the database to get the

        // last inserted value of the auto_increment field

        //-------------------------------------------------

        $this->post['topic_id'] = $this->topic['tid'];

        //-------------------------------------------------

        // Are we a mod, and can we change the topic state?

        //-------------------------------------------------

        $return_to_move = 0;

        if (('' != $ibforums->input['mod_options']) or ('nowt' != $ibforums->input['mod_options'])) {
            if ('pin' == $ibforums->input['mod_options']) {
                if (1 == $ibforums->member['g_is_supmod'] or 1 == $class->moderator['pin_topic']) {
                    $this->topic['pinned'] = 1;

                    $class->moderate_log('Pinned topic from post form', $this->topic['title']);
                }
            } elseif ('close' == $ibforums->input['mod_options']) {
                if (1 == $ibforums->member['g_is_supmod'] or 1 == $class->moderator['close_topic']) {
                    $this->topic['state'] = 'closed';

                    $class->moderate_log('Closed topic from post form', $this->topic['title']);
                }
            } elseif ('move' == $ibforums->input['mod_options']) {
                if (1 == $ibforums->member['g_is_supmod'] or 1 == $class->moderator['move_topic']) {
                    $return_to_move = 1;
                }
            }
        }

        $db_string = $std->compile_db_string($this->post);

        $DB->query('INSERT INTO ibf_posts (' . $db_string['FIELD_NAMES'] . ') VALUES (' . $db_string['FIELD_VALUES'] . ')');

        $this->post['pid'] = $DB->get_insert_id();

        if ($class->obj['moderate']) {
            $print->redirect_screen($ibforums->lang['moderate_post'], "act=ST&f={$class->forum['id']}&t={$this->topic['tid']}");
        }

        //-------------------------------------------------

        // If we are still here, lets update the

        // board/forum/topic stats

        //-------------------------------------------------

        $class->forum['last_title'] = str_replace("'", '&#39;', $this->topic['title']);

        $class->forum['last_id'] = $this->topic['tid'];

        $class->forum['last_post'] = time();

        $class->forum['last_poster_name'] = $ibforums->member['uid'] ? $ibforums->member['uname'] : $ibforums->input['UserName'];

        $class->forum['last_poster_id'] = $ibforums->member['uid'];

        $class->forum['posts']++;

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
            . "posts='"
            . $class->forum['posts']
            . "' "
            . "WHERE id='"
            . $class->forum['id']
            . "'"
        );

        // Update the database

        //+------------------------------------------------------------------------------------------------------

        $DB->query(
            "UPDATE ibf_topics     SET last_poster_id='"
            . $class->forum['last_poster_id']
            . "', "
            . "last_poster_name='"
            . $class->forum['last_poster_name']
            . "', "
            . "last_post='"
            . $class->forum['last_post']
            . "', "
            . "pinned='"
            . $this->topic['pinned']
            . "', "
            . "state='"
            . $this->topic['state']
            . "', "
            . 'posts=posts+1 '
            . "WHERE tid='"
            . $this->topic['tid']
            . "'"
        );

        //+------------------------------------------------------------------------------------------------------

        $DB->query('UPDATE ibf_stats SET TOTAL_REPLIES=TOTAL_REPLIES+1');

        //-------------------------------------------------

        // If we are a member, lets update thier last post

        // date and increment their post count.

        //-------------------------------------------------

        $pcount = '';

        $mgroup = '';

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

        // Are we tracking topics we reply in 'auto_track'?

        //-------------------------------------------------

        if (1 == $ibforums->member['auto_track']) {
            $DB->query("SELECT trid FROM ibf_tracker WHERE topic_id='" . $this->topic['tid'] . "' AND member_id='" . $ibforums->member['uid'] . "'");

            if (!$DB->get_num_rows()) {
                $db_string = $DB->compile_db_insert_string(
                    [
                        'member_id' => $ibforums->member['uid'],
'topic_id' => $this->topic['tid'],
'start_date' => time(),
                    ]
                );

                $DB->query("INSERT INTO ibf_tracker ({$db_string['FIELD_NAMES']}) VALUES ({$db_string['FIELD_VALUES']})");
            }
        }

        //-------------------------------------------------

        // Check for subscribed topics

        // Pass on the previous last post time of the topic

        // to see if we need to send emails out

        //-------------------------------------------------

        $class->topic_tracker($this->topic['tid'], $this->post['post'], $class->forum['last_poster_name'], $this->topic['last_post']);

        //-------------------------------------------------

        // Redirect them back to the topic

        //-------------------------------------------------

        if (1 == $return_to_move) {
            $std->boink_it($class->base_url . "&act=Mod&CODE=02&f={$class->forum['id']}&t={$this->topic['tid']}");
        } else {
            $page = floor(($this->topic['posts'] + 1) / $ibforums->vars['display_max_posts']);

            $page *= $ibforums->vars['display_max_posts'];

            $std->boink_it($class->base_url . "&act=ST&f={$class->forum['id']}&t={$this->topic['tid']}&st=$page&#entry{$this->post['pid']}");
        }
    }

    public function show_form($class)
    {
        global $ibforums, $std, $DB, $print, $_POST;

        // Sort out the "raw" textarea input and make it safe incase

        // we have a <textarea> tag in the raw post var.

        $raw_post = $_POST['Post'] ?? '';

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

        $class->output .= $class->html_start_form(
            [
                1 => ['CODE', '03'],
2 => ['t', $this->topic['tid']],
            ]
        );

        //---------------------------------------

        // START TABLE

        //---------------------------------------

        $class->output .= $class->html->table_structure();

        //---------------------------------------

        $start_table = $class->html->table_top("{$ibforums->lang['top_txt_reply']} {$this->topic['title']}");

        $name_fields = $class->html_name_field();

        $post_box = $class->html_post_body($raw_post);

        $mod_options = $class->mod_options(1);

        $end_form = $class->html->EndForm($ibforums->lang['submit_reply']);

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

        //---------------------------------------

        $class->html_add_smilie_box();

        $class->html_topic_summary($this->topic['tid']);

        $this->nav = [
            "<a href='{$class->base_url}&act=SC&c={$class->forum[cat_id]}'>{$class->forum['cat_name']}</a>",
            "<a href='{$class->base_url}&act=SF&f={$class->forum['id']}'>{$class->forum['name']}</a>",
            "<a href='{$class->base_url}&act=ST&f={$class->forum['id']}&t={$this->topic['tid']}'>{$this->topic['title']}</a>",
        ];

        $this->title = $ibforums->lang['replying_in'] . ' ' . $this->topic['title'];

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

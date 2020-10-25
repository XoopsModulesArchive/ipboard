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
|   > Edit post library
|   > Module written by Matt Mecham
|   > Date started: 19th February 2002
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

    public $moderator = ['member_id' => 0, 'member_name' => '', 'edit_post' => 0];

    public $orig_post = [];

    public function __construct($class)
    {
        global $ibforums, $std, $DB;

        // Lets load the topic from the database before we do anything else.

        $DB->query("SELECT * FROM ibf_topics WHERE tid='" . $ibforums->input['t'] . "'");

        $this->topic = $DB->fetch_row();

        // Is it legitimate?

        if (!$this->topic['tid']) {
            $std->Error([LEVEL => 1, MSG => 'missing_files']);
        }

        // Load the old post

        $DB->query("SELECT * FROM ibf_posts WHERE pid='" . $ibforums->input['p'] . "'");

        $this->orig_post = $DB->fetch_row();

        if (!$this->orig_post['pid']) {
            $std->Error([LEVEL => 1, MSG => 'missing_files']);
        }

        // Load the moderator

        if ($ibforums->member['uid']) {
            $DB->query("SELECT member_id, member_name, mid, edit_post FROM ibf_moderators WHERE forum_id='" . $class->forum['id'] . "' AND (member_id='" . $ibforums->member['uid'] . "' OR (is_group=1 AND group_id='" . $ibforums->member['mgroup'] . "'))");

            $this->moderator = $DB->fetch_row();
        }

        //-------------------------------------------------

        // Lets do some tests to make sure that we are

        // allowed to edit this topic

        //-------------------------------------------------

        $can_edit = 0;

        if ($ibforums->member['g_is_supmod']) {
            $can_edit = 1;
        }

        if ($this->moderator['edit_post']) {
            $can_edit = 1;
        }

        if (($this->orig_post['author_id'] == $ibforums->member['uid']) and ($ibforums->member['g_edit_posts'])) {
            // Have we set a time limit?

            if ($ibforums->member['g_edit_cutoff'] > 0) {
                if ($this->orig_post['post_date'] > (time() - ((int)$ibforums->member['g_edit_cutoff'] * 60))) {
                    $can_edit = 1;
                }
            } else {
                $can_edit = 1;
            }
        }

        if (1 != $can_edit) {
            $std->Error([LEVEL => 1, MSG => 'not_op']);
        }

        // Is the topic locked?

        if (('open' != $this->topic['state']) and (!$ibforums->member['g_is_supmod'])) {
            $std->Error([LEVEL => 1, MSG => 'locked_topic']);
        }
    }

    public function process($class)
    {
        global $ibforums, $std, $DB, $print;

        //-------------------------------------------------

        // Parse the post, and check for any errors.

        // overwrites saved post intentionally

        //-------------------------------------------------

        $this->post = $class->compile_post();

        if (('' != $class->obj['post_errors']) or ('' != $class->obj['preview_post'])) {
            // Show the form again

            $this->show_form($class);
        } else {
            $this->complete_edit($class);
        }
    }

    public function complete_edit($class)
    {
        global $ibforums, $std, $DB, $print, $HTTP_POST_FILES;

        $time = $std->get_date(time(), 'LONG');

        //-------------------------------------------------

        // Reset some data

        //-------------------------------------------------

        // Do we have to adjust the attachments?

        if ($this->orig_post['attach_id']) {
            if ('keep' != $ibforums->input['editupload']) {
                // We're either uploading a new attachment, or deleting one, so lets

                // remove the old attachment first eh?

                if (is_file($ibforums->vars['upload_dir'] . '/' . $this->orig_post['attach_id'])) {
                    @unlink($ibforums->vars['upload_dir'] . '/' . $this->orig_post['attach_id']);
                }

                if ('new' == $ibforums->input['editupload']) {
                    // w00t, we're uploading a new ..um.. upload

                    $new_upload = $class->process_upload();

                    if ('' != $class->obj['post_errors']) {
                        $this->show_form($class);
                    }

                    $this->post['attach_id'] = $new_upload['attach_id'];

                    $this->post['attach_type'] = $new_upload['attach_type'];

                    $this->post['attach_hits'] = $new_upload['attach_hits'];

                    $this->post['attach_file'] = $new_upload['attach_file'];
                } elseif ('delete' == $ibforums->input['editupload']) {
                    // Simply remove the DB data as we've already removed the file

                    $this->post['attach_id'] = '';

                    $this->post['attach_type'] = '';

                    $this->post['attach_hits'] = '';

                    $this->post['attach_file'] = '';
                }
            } else {
                // We are keeping the old attachment

                $this->post['attach_id'] = $this->orig_post['attach_id'];

                $this->post['attach_type'] = $this->orig_post['attach_type'];

                $this->post['attach_hits'] = $this->orig_post['attach_hits'];

                $this->post['attach_file'] = $this->orig_post['attach_file'];
            }
        }

        $this->post['ip_address'] = $this->orig_post['ip_address'];

        $this->post['topic_id'] = $this->orig_post['topic_id'];

        $this->post['author_id'] = $this->orig_post['author_id'];

        $this->post['pid'] = $this->orig_post['pid'];

        $this->post['post_date'] = $this->orig_post['post_date'];

        $this->post['author_name'] = $this->orig_post['author_name'];

        $this->post['queued'] = $this->orig_post['queued'];

        $this->post['edit_time'] = time();

        $this->post['edit_name'] = $ibforums->member['uname'];

        // If the post icon has changed, update the topic post icon

        if (1 == $this->orig_post['new_topic']) {
            if ($this->post['icon_id'] != $this->orig_post['icon_id']) {
                $DB->query("UPDATE ibf_topics SET icon_id='" . $this->post['icon_id'] . "' WHERE tid='" . $this->topic['tid'] . "'");
            }
        }

        //-------------------------------------------------

        // Update the database (ib_forum_post)

        //-------------------------------------------------

        $this->post['append_edit'] = 1;

        if ($ibforums->member['g_append_edit']) {
            if (1 != $ibforums->input['add_edit']) {
                $this->post['append_edit'] = 0;
            }
        }

        $db_string = $DB->compile_db_update_string($this->post);

        $DB->query("UPDATE ibf_posts SET $db_string WHERE pid='" . $this->post['pid'] . "'");

        //-------------------------------------------------

        // Redirect them back to the topic

        //-------------------------------------------------

        $print->redirect_screen($ibforums->lang['post_edited'], "act=ST&f={$class->forum['id']}&t={$this->topic['tid']}&st={$ibforums->input['st']}#entry{$this->post['pid']}");
    }

    public function show_form($class)
    {
        global $ibforums, $std, $DB, $print, $_POST;

        //-------------------------------------------------

        // Sort out the "raw" textarea input and make it safe incase

        // we have a <textarea> tag in the raw post var.

        //-------------------------------------------------

        $raw_post = isset($_POST['Post']) ? htmlspecialchars($_POST['Post'], ENT_QUOTES | ENT_HTML5) : $class->parser->unconvert($this->orig_post['post'], $class->forum['use_ibc'], $class->forum['use_html']);

        if (isset($raw_post)) {
            $raw_post = str_replace('$', '&#036;', $raw_post);

            $raw_post = stripslashes($raw_post);
        }

        //-------------------------------------------------

        // Do we have any posting errors?

        //-------------------------------------------------

        if ($class->obj['post_errors']) {
            $class->output .= $class->html->errors($ibforums->lang[$class->obj['post_errors']]);
        }

        if ($class->obj['preview_post']) {
            $class->output .= $class->html->preview($class->parser->convert(['TEXT' => $this->post['post'], 'CODE' => $class->forum['use_ibc'], 'SMILIES' => $ibforums->input['enableemo'], 'HTML' => $class->forum['use_html']]));
        }

        $class->check_upload_ability();

        $class->output .= $class->html_start_form(
            [
                1 => ['CODE', '09'],
2 => ['t', $this->topic['tid']],
3 => ['p', $ibforums->input['p']],
4 => ['st', $ibforums->input['st']],
            ]
        );

        //---------------------------------------

        // START TABLE

        //---------------------------------------

        $class->output .= $class->html->table_structure();

        //---------------------------------------

        $start_table = $class->html->table_top("{$ibforums->lang['top_txt_edit']} {$this->topic['title']}");

        $name_fields = $class->html_name_field();

        $post_box = $class->html_post_body($raw_post);

        $end_form = $class->html->EndForm($ibforums->lang['submit_edit']);

        $post_icons = $class->html_post_icons($this->orig_post['icon_id']);

        if (($class->obj['can_upload']) and ('' != $this->orig_post['attach_id'])) {
            $upload_field = $class->html->edit_upload_field($ibforums->member['g_attach_max'] * 1024, $this->orig_post['attach_file']);
        }

        if ($ibforums->member['g_append_edit']) {
            $checked = '';

            if ($this->orig_post['append_edit']) {
                $checked = 'checked';
            }

            $edit_option = $class->html->add_edit_box($checked);
        }

        //---------------------------------------

        $class->output = str_replace('<!--START TABLE-->', $start_table, $class->output);

        $class->output = str_replace('<!--NAME FIELDS-->', $name_fields, $class->output);

        $class->output = str_replace('<!--POST BOX-->', $post_box, $class->output);

        $class->output = str_replace('<!--POST ICONS-->', $post_icons, $class->output);

        $class->output = str_replace('<!--END TABLE-->', $end_form, $class->output);

        $class->output = str_replace('<!--UPLOAD FIELD-->', $upload_field, $class->output);

        $class->output = str_replace('<!--MOD OPTIONS-->', $edit_option, $class->output);

        //---------------------------------------

        $class->html_add_smilie_box();

        $class->html_topic_summary($this->topic['tid']);

        $this->nav = [
            "<a href='{$class->base_url}&act=SC&c={$class->forum['cat_id']}'>{$class->forum['cat_name']}</a>",
            "<a href='{$class->base_url}&act=SF&f={$class->forum['id']}'>{$class->forum['name']}</a>",
            "<a href='{$class->base_url}&act=ST&f={$class->forum['id']}&t={$this->topic['tid']}'>{$this->topic['title']}</a>",
        ];

        $this->title = $ibforums->lang['editing_post'] . ' ' . $this->topic['title'];

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

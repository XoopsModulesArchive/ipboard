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
|   > Forward topic to a friend module
|   > Module written by Matt Mecham
|   > Date started: 21st March 2002
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

$idx = new Forward();

class Forward
{
    public $output = '';

    public $base_url = '';

    public $html = '';

    public $forum = [];

    public $topic = [];

    public $category = [];

    /***********************************************************************************/

    // Our constructor, load words, load skin, print the topic listing

    /***********************************************************************************/

    public function __construct()
    {
        global $ibforums, $DB, $std, $print, $skin_universal;

        //-------------------------------------

        // Compile the language file

        //-------------------------------------

        $ibforums->lang = $std->load_words($ibforums->lang, 'lang_emails', $ibforums->lang_id);

        $this->html = $std->load_template('skin_emails');

        //-------------------------------------

        // Check the input

        //-------------------------------------

        $ibforums->input['t'] = $std->is_number($ibforums->input['t']);

        $ibforums->input['f'] = $std->is_number($ibforums->input['f']);

        if ($ibforums->input['t'] < 0 or $ibforums->input['f'] < 0) {
            $std->Error([LEVEL => 1, MSG => 'missing_files']);
        }

        //-------------------------------------

        // Get the forum info based on the forum ID, get the category name, ID, and get the topic details

        //-------------------------------------

        $DB->query(
            "SELECT t.*, f.name as forum_name, f.id as forum_id, f.read_perms, f.reply_perms, f.start_perms, f.allow_poll, f.posts as forum_posts, f.topics as forum_topics, c.name as cat_name, c.id as cat_id FROM ibf_topics t, ibf_forums f , ibf_categories c where t.tid='"
            . $ibforums->input[t]
            . "' and f.id = t.forum_id and f.category=c.id"
        );

        $this->topic = $DB->fetch_row();

        $this->forum = [
            'id' => $this->topic['forum_id'],
'name' => $this->topic['forum_name'],
'posts' => $this->topic['forum_posts'],
'topics' => $this->topic['forum_topics'],
'read_perms' => $this->topic['read_perms'],
'allow_poll' => $this->topic['allow_poll'],
        ];

        $this->category = [
            'name' => $this->topic['cat_name'],
'id' => $this->topic['cat_id'],
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

        $this->base_url = "{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}";

        $this->base_url_NS = "{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}";

        //-------------------------------------

        // Check viewing permissions, private forums,

        // password forums, etc

        //-------------------------------------

        if (!$ibforums->member['uid']) {
            $std->Error([LEVEL => 1, MSG => 'no_guests']);
        }

        $bad_entry = $this->check_access();

        if (1 == $bad_entry) {
            $std->Error([LEVEL => 1, MSG => 'no_view_topic']);
        }

        // What to do?

        if ('01' == $ibforums->input['CODE']) {
            $this->send_email();
        } else {
            $this->show_form();
        }
    }

    public function send_email()
    {
        global $std, $ibforums, $DB, $print;

        require './sources/lib/emailer.php';

        $this->email = new emailer();

        $lang_to_use = 'en';

        $DB->query('SELECT lid, ldir, lname FROM ibf_languages');

        while (false !== ($l = $DB->fetch_row())) {
            if ($ibforums->input['lang'] == $l['ldir']) {
                $lang_to_use = $l['ldir'];
            }
        }

        $check_array = [
            'to_name' => 'stf_no_name',
'to_email' => 'stf_no_email',
'message' => 'stf_no_msg',
'subject' => 'stf_no_subject',
        ];

        foreach ($check_array as $input => $msg) {
            if (empty($ibforums->input[$input])) {
                $std->Error(['LEVEL' => 1, 'MSG' => $msg]);
            }
        }

        $to_email = $std->clean_email($ibforums->input['to_email']);

        if (!$to_email) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'invalid_email']);
        }

        $this->email->get_template('forward_page', $lang_to_use);

        $this->email->build_message(
            [
                'THE_MESSAGE' => str_replace('<br>', "\n", $ibforums->input['message']),
'TO_NAME' => $ibforums->input['to_name'],
'FROM_NAME' => $ibforums->member['uname'],
            ]
        );

        $this->email->subject = $ibforums->input['subject'];

        $this->email->to = $ibforums->input['to_email'];

        $this->email->from = $ibforums->member['email'];

        $this->email->send_mail();

        $print->redirect_screen($ibforums->lang['redirect'], 'act=ST&f=' . $this->forum['id'] . '&t=' . $this->topic['tid'] . '&st=' . $ibforums->input['st']);
    }

    public function show_form()
    {
        global $std, $ibforums, $DB, $print, $root_path;

        require $root_path . 'lang/' . $ibforums->lang_id . '/email_content.php';

        $ibforums->lang['send_text'] = $EMAIL['send_text'];

        $lang_array = unserialize(stripslashes($ibforums->vars['languages']));

        $lang_select = "<select name='lang' class='forminput'>\n";

        $DB->query('SELECT lid, ldir, lname FROM ibf_languages');

        while (false !== ($l = $DB->fetch_row())) {
            $lang_select .= $l['ldir'] == $ibforums->member['language'] ? "<option value='{$l['ldir']}' selected>{$l['lname']}</option>" : "<option value='{$l['ldir']}'>{$l['lname']}</option>";
        }

        $lang_select .= '</select>';

        $ibforums->lang['send_text'] = preg_replace('/<#THE LINK#>/', $this->base_url_NS . '?act=ST&f=' . $this->forum['id'] . '&t=' . $this->topic['tid'], $ibforums->lang['send_text']);

        $ibforums->lang['send_text'] = preg_replace('/<#USER NAME#>/', $ibforums->member['uname'], $ibforums->lang['send_text']);

        $this->output = $this->html->forward_form($this->topic['title'], $ibforums->lang['send_text'], $lang_select);

        $this->page_title = $ibforums->lang['title'];

        $this->nav = ["<a href='{$this->base_url}&act=SF&f={$this->forum['id']}'>{$this->forum['name']}</a>", "<a href='" . $this->base_url . "&act=ST&f={$this->forum['id']}&t={$this->topic['tid']}'>{$this->topic['title']}</a>", $ibforums->lang['title']];

        $print->add_output((string)$this->output);

        $print->do_output(['TITLE' => $this->page_title, 'JS' => 0, NAV => $this->nav]);
    }

    //+----------------------------------------------------------------------------------

    public function check_access()
    {
        global $ibforums, $HTTP_COOKIE_VARS;

        $return = 1;

        $this->m_group = $ibforums->member['mgroup'];

        if ('*' == $this->forum['read_perms']) {
            $return = 0;
        } elseif (preg_match("/(^|,)$this->m_group(,|$)/", $this->forum['read_perms'])) {
            $return = 0;
        }

        if ($this->forum['password']) {
            if ($HTTP_COOKIE_VARS[$ibforums->vars['cookie_id'] . 'iBForum' . $this->forum['id']] == $this->forum['password']) {
                $return = 0;
            } else {
                $return = 1;
            }
        }

        return $return;
    }
}








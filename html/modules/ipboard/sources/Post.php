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
|   > Post core module
|   > Module written by Matt Mecham
|   > Date started: 14th February 2002
|
|   > Module Version 1.0.0
+--------------------------------------------------------------------------
*/

$idx = new Post();

class Post
{
    public $output     = '';
    public $base_url   = '';
    public $html       = '';
    public $parser     = '';
    public $moderator  = [];
    public $forum      = [];
    public $topic      = [];
    public $category   = [];
    public $mem_groups = [];
    public $mem_titles = [];
    public $obj        = [];
    public $email      = '';
    public $can_upload = 0;
    /***********************************************************************************/
    //
    // Our constructor, load words, load skin, print the topic listing
    //
    /***********************************************************************************/

    public function __construct()
    {
        global $ibforums, $DB, $std, $print, $skin_universal;

        require './sources/lib/post_parser.php';

        $this->parser = new post_parser(1);

        //--------------------------------------
        // Compile the language file
        //--------------------------------------

        $ibforums->lang = $std->load_words($ibforums->lang, 'lang_post', $ibforums->lang_id);

        $this->html = $std->load_template('skin_post');

        //--------------------------------------
        // Check the input
        //--------------------------------------

        if ($ibforums->input['t']) {
            $ibforums->input['t'] = $std->is_number($ibforums->input['t']);
            if (!$ibforums->input['t']) {
                $std->Error([LEVEL => 1, MSG => 'missing_files']);
            }
        }

        if ($ibforums->input['p']) {
            $ibforums->input['p'] = $std->is_number($ibforums->input['p']);
            if (!$ibforums->input['p']) {
                $std->Error([LEVEL => 1, MSG => 'missing_files']);
            }
        }

        $ibforums->input['f'] = $std->is_number($ibforums->input['f']);
        if (!$ibforums->input['f']) {
            $std->Error([LEVEL => 1, MSG => 'missing_files']);
        }

        $ibforums->input['st'] = $ibforums->input['st'] ? $std->is_number($ibforums->input['st']) : 0;

        // Did the user press the "preview" button?

        $this->obj['preview_post'] = $ibforums->input['preview'];

        //--------------------------------------
        // Get the forum info based on the forum ID, get the category name, ID, and get the topic details
        //--------------------------------------

        $DB->query('SELECT f.*, c.id as cat_id, c.name as cat_name from ibf_forums f, ibf_categories c WHERE f.id=' . $ibforums->input[f] . ' and c.id=f.category');

        $this->forum = $DB->fetch_row();

        if ($this->forum['read_perms'] != '*') {
            if (!preg_match('/(^|,)' . $ibforums->member['mgroup'] . '(,|$)/', $this->forum['read_perms'])) {
                $std->Error([LEVEL => 1, MSG => 'no_view_topic']);
            }
        }

        // Can we upload stuff?

        if ($this->forum['upload_perms'] == '*') {
            $this->can_upload = 1;
        } elseif (preg_match('/(^|,)' . $ibforums->member['mgroup'] . '(,|$)/', $this->forum['upload_perms'])) {
            $this->can_upload = 1;
        }

        // Is this forum switched off?

        if (!$this->forum['status']) {
            $std->Error([LEVEL => 1, MSG => 'forum_read_only']);
        }

        //--------------------------------------
        // Is this a password protected forum?
        //--------------------------------------

        $pass = 0;

        if ($this->forum['password'] != '') {
            if (!$c_pass = $std->my_getcookie('iBForum' . $this->forum['id'])) {
                $pass = 0;
            }

            if ($c_pass == $this->forum['password']) {
                $pass = 1;
            } else {
                $pass = 0;
            }
        } else {
            $pass = 1;
        }

        if ($pass == 0) {
            $std->Error([LEVEL => 1, MSG => 'no_view_topic']);
        }

        //--------------------------------------

        if ($this->forum['parent_id'] > 0) {
            $DB->query("SELECT f.id as forum_id, f.name as forum_name, c.id, c.name FROM ibf_forums f, ibf_categories c WHERE f.id='" . $this->forum['parent_id'] . "' AND c.id=f.category");

            $row = $DB->fetch_row();

            $this->forum['cat_id']   = $row['id'];
            $this->forum['cat_name'] = $row['name'];
        }

        //--------------------------------------
        // Error out if we can not find the forum
        //--------------------------------------

        if (!$this->forum['id']) {
            $std->Error([LEVEL => 1, MSG => 'missing_files']);
        }

        $this->base_url = "{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}";

        //--------------------------------------
        // Is this forum moderated?
        //--------------------------------------

        $this->obj['moderate'] = $this->forum['preview_posts'] ? 1 : 0;
        // Can we bypass it?
        if ($ibforums->member['g_avoid_q']) {
            $this->obj['moderate'] = 0;
        }

        // Does this member have mod_posts enabled?

        if ($ibforums->member['mod_posts'] == 1) {
            $this->obj['moderate'] = 1;
        }

        //--------------------------------------
        // Are we allowed to post at all?
        //--------------------------------------

        if ($ibforums->member['uid']) {
            if (!$ibforums->member['allow_post']) {
                $std->Error([LEVEL => 1, MSG => 'posting_off']);
            }

            // Flood check..

            if ($ibforums->input['CODE'] != '08' and $ibforums->input['CODE'] != '09') {
                if ($ibforums->vars['flood_control'] > 0) {
                    if ($ibforums->member['g_avoid_flood'] != 1) {
                        if (time() - $ibforums->member['last_post'] < $ibforums->vars['flood_control']) {
                            $std->Error(['LEVEL' => 1, 'MSG' => 'flood_control', 'EXTRA' => $ibforums->vars['flood_control']]);
                        }
                    }
                }
            }
        }

        if ($ibforums->member['uid'] != 0 and $ibforums->member['g_is_supmod'] == 0) {
            $DB->query("SELECT * from ibf_moderators WHERE forum_id='" . $this->forum['id'] . "' AND (member_id='" . $ibforums->member['uid'] . "' OR (is_group=1 AND group_id='" . $ibforums->member['mgroup'] . "'))");
            $this->moderator = $DB->fetch_row();
        }

        //--------------------------------------
        // Convert the code ID's into something
        // use mere mortals can understand....
        //--------------------------------------

        $this->obj['action_codes'] = [
            '00' => ['0', 'new_post'],
            '01' => ['1', 'new_post'],
            '02' => ['0', 'reply_post'],
            '03' => ['1', 'reply_post'],
            '06' => ['0', 'q_reply_post'],
            '07' => ['1', 'q_reply_post'],
            '08' => ['0', 'edit_post'],
            '09' => ['1', 'edit_post'],
            '10' => ['0', 'poll'],
            '11' => ['1', 'poll'],
        ];

        // Make sure our input CODE element is legal.

        if (!isset($this->obj['action_codes'][$ibforums->input['CODE']])) {
            $std->Error([LEVEL => 1, MSG => 'missing_files']);
        }

        // Require and run our associated library file for this action.
        // this imports an extended class for this Post class.

        require './sources/lib/post_' . $this->obj['action_codes'][$ibforums->input['CODE']][1] . '.php';

        $post_functions = new post_functions($this);

        // If the first CODE array bit is set to "0" - show the relevant form.
        // If it's set to "1" process the input.

        // We pass a reference to this classes object so we can manipulate this classes
        // data from our sub class.

        if ($this->obj['action_codes'][$ibforums->input['CODE']][0]) {
            // Make sure we have a "Guest" Name..

            if (!$ibforums->member['uid']) {
                $ibforums->input['UserName'] = trim($ibforums->input['UserName']);
                $ibforums->input['UserName'] = str_replace('<br>', '', $ibforums->input['UserName']);
                $ibforums->input['UserName'] = $ibforums->input['UserName'] ?: 'Guest';

                if ($ibforums->input['UserName'] != 'Guest') {
                    $DB->query("SELECT uid FROM xbb_members WHERE LOWER(uname)='" . trim(strtolower($ibforums->input['UserName'])) . "'");

                    if ($DB->get_num_rows()) {
                        $ibforums->input['UserName'] = $ibforums->vars['guest_name_pre'] . $ibforums->input['UserName'] . $ibforums->vars['guest_name_suf'];
                    }
                }
            }

            //-------------------------------------------------------------------------
            // Stop the user hitting the submit button in the hope that multiple topics
            // or replies will be added. Or if the user accidently hits the button
            // twice.
            //-------------------------------------------------------------------------

            if ($this->obj['preview_post'] == '') {
                if (preg_match('/Post,.*,(01|03|07|11)$/', $ibforums->location)) {
                    if (time() - $ibforums->lastclick < 2) {
                        if ($ibforums->input['CODE'] == '01' or $ibforums->input['CODE'] == '11') {
                            // Redirect to the newest topic in the forum

                            $DB->query(
                                "SELECT tid from ibf_topics WHERE forum_id='" . $this->forum['id'] . "' AND approved=1 " . 'ORDER BY last_post DESC LIMIT 0,1'
                            );

                            $topic = $DB->fetch_row();

                            $std->boink_it($ibforums->base_url . '&act=ST&f=' . $this->forum['id'] . '&t=' . $topic['tid']);
                            exit();
                        } else {
                            // It's a reply, so simply show the topic...

                            $std->boink_it($ibforums->base_url . '&act=ST&f=' . $this->forum['id'] . '&t=' . $ibforums->input['t'] . '&view=getlastpost');
                            exit();
                        }
                    }
                }
            }

            //----------------------------------

            $post_functions->process($this);
        } else {
            $post_functions->show_form($this);
        }
    }

    /*****************************************************/
    // topic tracker
    // ------------------
    // Checks and sends out the emails as needed.
    /*****************************************************/

    public function topic_tracker($tid = '', $post = '', $poster = '', $last_post = '')
    {
        global $ibforums, $DB, $std;

        require './sources/lib/emailer.php';

        $this->email = new emailer();

        //-------------------------

        if ($tid == '') {
            return true;
        }

        // Get the email addy's, topic ids and email_full stuff - oh yeah.
        // We only return rows that have a member last_activity of greater than the post itself

        $DB->query(
            "SELECT tr.trid, tr.topic_id, m.uname, m.email, m.uid, m.email_full, m.language, m.last_activity, t.title, t.forum_id
				    FROM ibf_tracker tr, ibf_topics t,xbb_members m
				    WHERE tr.topic_id='$tid'
				    AND tr.member_id=m.uid
				    AND m.uid <> '{$ibforums->member['uid']}'
				    AND t.tid=tr.topic_id
				    AND m.last_activity > '$last_post'"
        );

        if ($DB->get_num_rows()) {
            $trids = [];

            while (false !== ($r = $DB->fetch_row())) {
                $r['language'] = $r['language'] ?: 'en';

                if ($r['email_full'] == 1) {
                    $this->email->get_template('subs_with_post', $r['language']);

                    $this->email->build_message(
                        [
                            'TOPIC_ID' => $r['topic_id'],
                            'FORUM_ID' => $r['forum_id'],
                            'TITLE'    => $r['title'],
                            'NAME'     => $r['uname'],
                            'POSTER'   => $poster,
                            'POST'     => $post,
                        ]
                    );

                    $this->email->subject = $ibforums->lang['tt_subject'];
                    $this->email->to      = $r['email'];
                    $this->email->send_mail();
                } else {
                    $this->email->get_template('subs_no_post', $r['language']);

                    $this->email->build_message(
                        [
                            'TOPIC_ID' => $r['topic_id'],
                            'FORUM_ID' => $r['forum_id'],
                            'TITLE'    => $r['title'],
                            'NAME'     => $r['uname'],
                            'POSTER'   => $poster,
                        ]
                    );

                    $this->email->subject = $ibforums->lang['tt_subject'];
                    $this->email->to      = $r['email'];

                    $this->email->send_mail();
                }

                $trids[] = $r['trid'];
            }
        }
        return true;
    }



    /*****************************************************/
    // Forum tracker
    // ------------------
    // Checks and sends out the new topic notification if
    // needed
    /*****************************************************/

    public function forum_tracker($fid = '', $this_tid = '', $title = '', $forum_name = '')
    {
        global $ibforums, $DB, $std;

        require './sources/lib/emailer.php';

        $this->email = new emailer();

        //-------------------------

        if ($this_tid == '') {
            return true;
        }

        if ($fid == '') {
            return true;
        }

        // Work out the time stamp needed to "guess" if the user is still active on the board
        // We will base this guess on a period of non activity of time_now - 30 minutes.

        $time_limit = time() - (30 * 60);

        // There is the chance that an admin makes a forum, leaves it. Some one subscribes to the forum
        // and then the admin decides to make it hidden from the public via member groups. If this happened,
        // this forum will still send all notifications - this is not desirable, so we check for permissions
        // in the SQL query - just in case.

        $allowed_groups = '';

        if ($this->forum['read_perms'] != '*') {
            if (strlen($this->forum['read_perms'] > 0)) {
                $allowed_groups = ' AND m.mgroup IN (0,' . $this->forum['read_perms'] . ') ';
            }
        }

        // Get the email addy's, topic ids and email_full stuff - oh yeah.
        // We only return rows that have a member last_activity of greater than the post itself

        $DB->query(
            "SELECT tr.frid, m.uname, m.email, m.uid, m.language, m.last_activity
				    FROM ibf_forum_tracker tr,xbb_members m
				    WHERE tr.forum_id='$fid'
				    AND tr.member_id=m.uid
				    AND m.uid <> '{$ibforums->member['uid']}'
				    $allowed_groups
				    AND m.last_activity < '$time_limit'"
        );

        if ($DB->get_num_rows()) {
            while (false !== ($r = $DB->fetch_row())) {
                $r['language'] = $r['language'] ?: 'en';

                $this->email->get_template('subs_new_topic', $r['language']);

                $this->email->build_message(
                    [
                        'TOPIC_ID' => $this_tid,
                        'FORUM_ID' => $fid,
                        'TITLE'    => $title,
                        'NAME'     => $r['uname'],
                        'POSTER'   => $ibforums->member['uname'],
                        'FORUM'    => $forum_name,
                    ]
                );

                $this->email->subject = $ibforums->lang['ft_subject'];
                $this->email->to      = $r['email'];

                $this->email->send_mail();
            }
        }
        return true;
    }


    /*****************************************************/
    // compile post
    // ------------------
    // Compiles all the incoming information into an array
    // which is returned to the accessor
    /*****************************************************/

    public function compile_post()
    {
        global $ibforums, $std, $REQUEST_METHOD, $_POST;

        $ibforums->vars['max_post_length'] = $ibforums->vars['max_post_length'] ?: 2140000;

        // sort out some of the form data, check for posting length, etc.
        // THIS MUST BE CALLED BEFORE CHECKING ATTACHMENTS

        $ibforums->input['enablesig'] = $ibforums->input['enablesig'] == 'yes' ? 1 : 0;
        $ibforums->input['enableemo'] = $ibforums->input['enableemo'] == 'yes' ? 1 : 0;

        // Do we have a valid post?

        if (strlen(trim($_POST['Post'])) < 1) {
            $std->Error([LEVEL => 1, MSG => 'no_post']);
        }

        if (strlen($_POST['Post']) > ($ibforums->vars['max_post_length'] * 1024)) {
            $std->Error([LEVEL => 1, MSG => 'post_too_long']);
        }

        $post = [
            'author_id'   => $ibforums->member['uid'] ?: 0,
            'use_sig'     => $ibforums->input['enablesig'],
            'use_emo'     => $ibforums->input['enableemo'],
            'ip_address'  => $ibforums->input['IP_ADDRESS'],
            'post_date'   => time(),
            'icon_id'     => $ibforums->input['iconid'],
            'post'        => $this->parser->convert(
                [
                    TEXT    => $ibforums->input['Post'],
                    SMILIES => $ibforums->input['enableemo'],
                    CODE    => $this->forum['use_ibc'],
                    HTML    => $this->forum['use_html'],
                ]
            ),
            'author_name' => $ibforums->member['uid'] ? $ibforums->member['uname'] : $ibforums->input['UserName'],
            'forum_id'    => $this->forum['id'],
            'topic_id'    => '',
            'queued'      => $this->obj['moderate'],
            'attach_id'   => '',
            'attach_hits' => '',
            'attach_type' => '',
        ];

        // If we had any errors, parse them back to this class
        // so we can track them later.

        $this->obj['post_errors'] = $this->parser->error;

        return $post;
    }

    /*****************************************************/
    // process upload
    // ------------------
    // checks for an entry in the upload field, and uploads
    // the file if it meets our criteria. This also inserts
    // a new row into the attachments database if successful
    /*****************************************************/

    public function process_upload()
    {
        global $ibforums, $std, $HTTP_POST_FILES, $DB, $FILE_UPLOAD;

        //-------------------------------------------------
        // Set up some variables to stop carpals developing
        //-------------------------------------------------

        $FILE_NAME = $HTTP_POST_FILES['FILE_UPLOAD']['name'];
        $FILE_SIZE = $HTTP_POST_FILES['FILE_UPLOAD']['size'];
        $FILE_TYPE = $HTTP_POST_FILES['FILE_UPLOAD']['type'];

        // Naughty Opera adds the filename on the end of the
        // mime type - we don't want this.

        $FILE_TYPE = preg_replace('/^(.+?);.*$/', "\\1", $FILE_TYPE);

        $attach_data = [
            'attach_id'   => '',
            'attach_hits' => '',
            'attach_type' => '',
            'attach_file' => '',
        ];

        //-------------------------------------------------
        // Return if we don't have a file to upload
        //-------------------------------------------------

        // Naughty Mozilla likes to use "none" to indicate an empty upload field.
        // I love universal languages that aren't universal.

        if ($HTTP_POST_FILES['FILE_UPLOAD']['name'] == '' or !$HTTP_POST_FILES['FILE_UPLOAD']['name'] or ($HTTP_POST_FILES['FILE_UPLOAD']['name'] == 'none')) {
            return $attach_data;
        }

        //-------------------------------------------------
        // Return empty handed if we don't have permission to use
        // uploads
        //-------------------------------------------------

        if (($this->can_upload != 1) and ($ibforums->member['g_attach_max'] < 1)) {
            return $attach_data;
        }

        //-------------------------------------------------
        // Load our mime types config file.
        //-------------------------------------------------

        require './conf_mime_types.php';

        //-------------------------------------------------
        // Are we allowing this type of file?
        //-------------------------------------------------

        if ($mime_types[$FILE_TYPE][0] != 1) {
            $this->obj['post_errors'] = 'invalid_mime_type';
            return $attach_data;
        }

        //-------------------------------------------------
        // Check the file size
        //-------------------------------------------------

        if ($FILE_SIZE > ($ibforums->member['g_attach_max'] * 1024)) {
            $std->Error([LEVEL => 1, MSG => 'upload_to_big']);
        }

        //-------------------------------------------------
        // Make the uploaded file safe
        //-------------------------------------------------

        $FILE_NAME = preg_replace("/[^\w\.]/", '_', $FILE_NAME);

        $real_file_name = 'post-' . $this->forum['id'] . '-' . time();  // Note the lack of extension!

        if (preg_match("/\.(cgi|pl|js|asp|php|html|htm|jsp|jar)/", $FILE_NAME)) {
            $FILE_TYPE = 'text/plain';
        }

        //-------------------------------------------------
        // Add on the extension...
        //-------------------------------------------------

        $ext = '.ibf';

        switch ($FILE_TYPE) {
            case 'image/gif':
                $ext = '.gif';
                break;
            case 'image/jpeg':
                $ext = '.jpg';
                break;
            case 'image/pjpeg':
                $ext = '.jpg';
                break;
            case 'image/x-png':
                $ext = '.png';
                break;
            default:
                $ext = '.ibf';
                break;
        }

        $real_file_name .= $ext;

        //-------------------------------------------------
        // If we are previewing the post, we don't want to
        // add the attachment to the database, so we return
        // the array with the filename. We would have returned
        // earlier if there was an error
        //-------------------------------------------------

        if ($this->obj['preview_post']) {
            return ['FILE_NAME' => $FILE_NAME];
        }

        //-------------------------------------------------
        // Copy the upload to the uploads directory
        //-------------------------------------------------

        if (!@move_uploaded_file($HTTP_POST_FILES['FILE_UPLOAD']['tmp_name'], $ibforums->vars['upload_dir'] . '/' . $real_file_name)) {
            $this->obj['post_errors'] = 'upload_failed';
            return $attach_data;
        } else {
            @chmod($ibforums->vars['upload_dir'] . '/' . $real_file_name, 0777);
        }

        //-------------------------------------------------
        // set the array, and enter the info into the DB
        // We don't have an extension on the file in the
        // hope that it make it more difficult to execute
        // a script on our server.
        //-------------------------------------------------

        $attach_data['attach_id']   = $real_file_name;
        $attach_data['attach_hits'] = 0;
        $attach_data['attach_type'] = $FILE_TYPE;
        $attach_data['attach_file'] = $FILE_NAME;

        return $attach_data;
    }



    /*****************************************************/
    // check_upload_ability
    // ------------------
    // checks to make sure the requesting browser can accept
    // file uploads, also checks if the member group can
    // accept uploads and returns accordingly.
    /*****************************************************/

    public function check_upload_ability()
    {
        global $ibforums;

        if (($this->can_upload == 1) and $ibforums->member['g_attach_max'] > 0) {
            $this->obj['can_upload']   = 1;
            $this->obj['form_extra']   = " enctype='multipart/form-data'";
            $this->obj['hidden_field'] = "<input type='hidden' name='MAX_FILE_SIZE' value='" . ($ibforums->member['g_attach_max'] * 1024) . "'>";
        }
    }

    /*****************************************************/
    // HTML: mod_options.
    // ------------------
    // Returns the HTML for the mod options drop down box
    /*****************************************************/

    public function mod_options($is_reply = 0)
    {
        global $ibforums, $DB;

        $can_close = 0;
        $can_pin   = 0;
        $can_move  = 0;

        $html = "<select id='forminput' name='mod_options' class='forminput'>\n<option value='nowt'>" . $ibforums->lang['mod_nowt'] . "</option>\n";

        if ($ibforums->member['g_is_supmod']) {
            $can_close = 1;
            $can_pin   = 1;
            $can_move  = 1;
        } elseif ($ibforums->member['uid'] != 0) {
            if ($this->moderator['mid'] != '') {
                if ($this->moderator['close_topic']) {
                    $can_close = 1;
                }
                if ($this->moderator['pin_topic']) {
                    $can_pin = 1;
                }
                if ($this->moderator['move_topic']) {
                    $can_move = 1;
                }
            }
        } else {
            return '';
        }

        if ($can_pin == 0 and $can_close == 0 and $can_move == 0) {
            return '';
        }

        if ($can_pin) {
            $html .= "<option value='pin'>" . $ibforums->lang['mod_pin'] . '</option>';
        }
        if ($can_close) {
            $html .= "<option value='close'>" . $ibforums->lang['mod_close'] . '</option>';
        }

        if ($can_move and $is_reply) {
            $html .= "<option value='move'>" . $ibforums->lang['mod_move'] . '</option>';
        }

        return $this->html->mod_options($html);
    }

    /*****************************************************/
    // HTML: start form.
    // ------------------
    // Returns the HTML for the <FORM> opening tag
    /*****************************************************/

    public function html_start_form($additional_tags = [])
    {
        global $ibforums;

        $form = "<form action='{$this->base_url}' method='POST' name='REPLIER' onSubmit='return ValidateForm()'"
                . $this->obj['form_extra']
                . '>'
                . "<input type='hidden' name='st' value='"
                . $ibforums->input[st]
                . "'>"
                . "<input type='hidden' name='act' value='Post'>"
                . "<input type='hidden' name='s' value='"
                . $ibforums->session_id
                . "'>"
                . "<input type='hidden' name='f' value='"
                . $this->forum['id']
                . "'>"
                . $this->obj['hidden_field'];

        // Any other tags to add?

        if (isset($additional_tags)) {
            foreach ($additional_tags as $k => $v) {
                $form .= "\n<input type='hidden' name='{$v[0]}' value='{$v[1]}'>";
            }
        }

        return $form;
    }

    /*****************************************************/
    // HTML: name fields.
    // ------------------
    // Returns the HTML for either text inputs or membername
    // depending if the member is a guest.
    /*****************************************************/

    public function html_name_field()
    {
        global $ibforums;

        return $ibforums->member['uid'] ? $this->html->nameField_reg() : $this->html->nameField_unreg($ibforums->input[UserName]);
    }

    /*****************************************************/
    // HTML: Post body.
    // ------------------
    // Returns the HTML for post area, code buttons and
    // post icons
    /*****************************************************/

    public function html_post_body($raw_post = '')
    {
        global $ibforums;

        $ibforums->lang['the_max_length'] = $ibforums->vars['max_post_length'] * 1024;

        return $this->html->postbox_buttons($raw_post);
    }

    /*****************************************************/
    // HTML: Post Icons
    // ------------------
    // Returns the HTML for post area, code buttons and
    // post icons
    /*****************************************************/

    public function html_post_icons($post_icon = '')
    {
        global $ibforums;

        if ($ibforums->input['iconid']) {
            $post_icon = $ibforums->input['iconid'];
        }

        $ibforums->lang['the_max_length'] = $ibforums->vars['max_post_length'] * 1024;

        $html = $this->html->PostIcons();

        if ($post_icon) {
            $html = preg_replace("/name=[\"']iconid[\"']\s*value=[\"']$post_icon\s?[\"']/", "name='iconid' value='$post_icon' checked", $html);
            $html = preg_replace("/name=[\"']iconid[\"']\s*value=[\"']0[\"']\s*checked/i", "name='iconid' value='0'", $html);
        }
        return $html;
    }

    /*****************************************************/
    // HTML: add smilie box.
    // ------------------
    // Inserts the clickable smilies box
    /*****************************************************/

    public function html_add_smilie_box()
    {
        global $ibforums, $DB;

        $show_table = 0;
        $count      = 0;
        $smilies    = "<tr align='center'>\n";

        // Get the smilies from the DB

        $DB->query("SELECT * FROM xbb_emoticons WHERE clickable='1'");

        while (false !== ($elmo = $DB->fetch_row())) {
            $show_table++;
            $count++;

            // Make single quotes as URL's with html entites in them
            // are parsed by the browser, so ' causes JS error :o

            if (false !== strpos($elmo['code'], '&#39;')) {
                $in_delim  = '"';
                $out_delim = "'";
            } else {
                $in_delim  = "'";
                $out_delim = '"';
            }

            $smilies .= "<td><a href={$out_delim}javascript:emoticon($in_delim" . $elmo['code'] . "$in_delim){$out_delim}><img src=\"" . $ibforums->vars['EMOTICONS_URL'] . '/' . $elmo['smile_url'] . "\" alt='smilie' border='0'></a>&nbsp;</td>\n";

            if ($count == $ibforums->vars['emo_per_row']) {
                $smilies .= "</tr>\n\n<tr align='center'>";
                $count   = 0;
            }
        }

        if ($count != $ibforums->vars['emo_per_row']) {
            for ($i = $count; $i < $ibforums->vars['emo_per_row']; ++$i) {
                $smilies .= "<td>&nbsp;</td>\n";
            }
            $smilies .= '</tr>';
        }

        $table = $this->html->smilie_table();

        if ($show_table != 0) {
            $table        = preg_replace('/<!--THE SMILIES-->/', $smilies, $table);
            $this->output = preg_replace('/<!--SMILIE TABLE-->/', $table, $this->output);
        }
    }

    /*****************************************************/
    // HTML: topic summary.
    // ------------------
    // displays the last 10 replies to the topic we're
    // replying in.
    /*****************************************************/

    public function html_topic_summary($topic_id)
    {
        global $ibforums, $std, $DB;

        if (!$topic_id) {
            return;
        }

        $cached_members = [];

        $this->output .= $this->html->TopicSummary_top();

        //--------------------------------------------------------------
        // Get the posts
        // This section will probably change at some point
        //--------------------------------------------------------------

        $post_query = $DB->query("SELECT post, pid, post_date, author_id, author_name FROM ibf_posts WHERE topic_id='$topic_id' and queued <> 1 ORDER BY pid DESC LIMIT 0,10");

        while (false !== ($row = $DB->fetch_row($post_query))) {
            $row['author'] = $row['author_name'];

            $row['date'] = $std->get_date($row['post_date'], 'LONG');

            $this->output .= $this->html->TopicSummary_body($row);
        }

        $this->output .= $this->html->TopicSummary_bottom();
    }

    /*****************************************************/
    // Moderators log
    // ------------------
    // Simply adds the last action to the mod logs
    /*****************************************************/

    public function moderate_log($title = 'unknown', $topic_title)
    {
        global $std, $ibforums, $DB, $HTTP_REFERER, $QUERY_STRING;

        $db_string = $std->compile_db_string(
            [
                'forum_id'     => $ibforums->input['f'],
                'topic_id'     => $ibforums->input['t'],
                'post_id'      => $ibforums->input['p'],
                'member_id'    => $ibforums->member['uid'],
                'member_name'  => $ibforums->member['uname'],
                'ip_address'   => $ibforums->input['IP_ADDRESS'],
                'http_referer' => $HTTP_REFERER,
                'ctime'        => time(),
                'topic_title'  => $topic_title,
                'action'       => $title,
                'query_string' => $QUERY_STRING,
            ]
        );

        $DB->query('INSERT INTO ibf_moderator_logs (' . $db_string['FIELD_NAMES'] . ') VALUES (' . $db_string['FIELD_VALUES'] . ')');
    }
}



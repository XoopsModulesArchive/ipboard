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
|   > Admin Setting functions
|   > Module written by Matt Mecham
|   > Date started: 20th March 2002
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

$idx = new ad_settings();

class ad_settings
{
    public $base_url;

    public function __construct()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        //---------------------------------------

        // Kill globals - globals bad, Homer good.

        //---------------------------------------

        $tmp_in = array_merge($_GET, $_POST, $_COOKIE);

        foreach ($tmp_in as $k => $v) {
            unset($$k);
        }

        switch ($IN['code']) {
            case 'phpinfo':
                phpinfo();
                exit;

            case 'cookie':
                $this->cookie();
                break;
            case 'docookie':
                $this->save_config(['cookie_domain', 'cookie_id', 'cookie_path']);
                break;
            //-------------------------
            case 'secure':
                $this->secure();
                break;
            case 'dosecure':
                $this->save_config(
                    [
                        'reg_antispam',
                        'disable_admin_anon',
                        'disable_online_ip',
                        'disable_reportpost',
                        'allow_dynamic_img',
                        'session_expiration',
                        'match_browser',
                        'allow_dup_email',
                        'allow_images',
                        'force_login',
                        'no_reg',
                        'allow_flash',
                        'new_reg_notify',
                        'use_mail_form',
                        'flood_control',
                        'allow_online_list',
                        'reg_auth_type',
                    ]
                );
                break;
            //-------------------------
            case 'post':
                $this->post();
                break;
            case 'dopost':
                $this->save_config(
                    [
                        'poll_tags',
                        'guest_name_pre',
                        'guest_name_suf',
                        'max_w_flash',
                        'max_h_flash',
                        'hot_topic',
                        'display_max_topics',
                        'display_max_posts',
                        'max_emos',
                        'max_images',
                        'emo_per_row',
                        'etfilter_punct',
                        'etfilter_shout',
                        'strip_quotes',
                        'max_post_length',
                        'show_img_upload',
                        'pre_polls',
                        'pre_moved',
                        'pre_pinned',
                        'img_ext',
                    ]
                );
                break;
            //-------------------------
            case 'avatars':
                $this->avatars();
                break;
            case 'doavatars':
                $this->save_config(
                    [
                        'subs_autoprune',
                        'topicpage_contents',
                        'postpage_contents',
                        'allow_skins',
                        'max_sig_length',
                        'sig_allow_ibc',
                        'sig_allow_html',
                        'avatar_ext',
                        'avatar_url',
                        'avup_size_max',
                        'avatars_on',
                        'avatar_dims',
                        'avatar_def',
                        'max_location_length',
                        'max_interest_length',
                        'post_titlechange',
                        'guests_ava',
                        'guests_img',
                        'guests_sig',
                    ]
                );
                break;
            //-------------------------
            case 'dates':
                $this->dates();
                break;
            case 'dodates':
                $this->save_config(['time_offset', 'clock_short', 'clock_joined', 'clock_long', 'time_adjust']);
                break;
            //-------------------------

            case 'calendar':
                $this->calendar();
                break;
            case 'docalendar':
                $this->save_config(['show_calendar', 'calendar_limit', 'year_limit', 'start_year']);
                break;
            //-------------------------

            case 'cpu':
                $this->cpu();
                break;
            case 'docpu':
                $this->save_config(['min_search_word', 'short_forum_jump', 'no_au_forum', 'no_au_topic', 'au_cutoff', 'load_limit', 'show_active', 'show_birthdays', 'show_totals', 'allow_search', 'search_post_cut', 'show_user_posted', 'nocache']);
                break;
            //-------------------------
            case 'email':
                $this->email();
                break;
            case 'doemail':
                $this->save_config(['email_in', 'email_out', 'mail_method', 'smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass']);
                break;
            //-------------------------
            case 'url':
                $this->url();
                break;
            case 'dourl':
                $this->save_config(
                    [
                        'number_format',
                        'html_dir',
                        'safe_mode_skins',
                        'board_name',
                        'board_url',
                        'home_name',
                        'home_url',
                        'disable_zip',
                        'html_url',
                        'upload_url',
                        'upload_dir',
                        'print_headers',
                        'header_redirect',
                        'debug_level',
                        'sql_debug',
                    ]
                );
                break;
            //-------------------------
            case 'pm':
                $this->pm();
                break;
            case 'dopm':
                $this->save_config(['show_max_msg_list', 'msg_allow_code', 'msg_allow_html']);
                break;
            //-------------------------
            case 'news':
                $this->news();
                break;
            case 'donews':
                $this->save_config(['news_forum_id', 'index_news_link']);
                break;
            //-------------------------
            case 'coppa':
                $this->coppa();
                break;
            case 'docoppa':
                $this->save_config(['use_coppa', 'coppa_fax', 'coppa_address']);
                break;
            //-------------------------
            case 'board':
                $this->board();
                break;
            case 'doboard':
                $this->save_config(['board_offline', 'offline_msg']);
                break;
            //-------------------------
            case 'bw':
                $this->badword();
                break;
            case 'bw_add':
                $this->add_badword();
                break;
            case 'bw_remove':
                $this->remove_badword();
                break;
            case 'bw_edit':
                $this->edit_badword();
                break;
            case 'bw_doedit':
                $this->doedit_badword();
                break;
            //-------------------------
            case 'emo':
                $this->emoticons();
                break;
            case 'emo_add':
                $this->add_emoticons();
                break;
            case 'emo_remove':
                $this->remove_emoticons();
                break;
            case 'emo_edit':
                $this->edit_emoticons();
                break;
            case 'emo_doedit':
                $this->doedit_emoticons();
                break;
            case 'emo_upload':
                $this->upload_emoticon();
            //-------------------------
            // no break
            case 'count':
                $this->countstats();
                break;
            case 'docount':
                $this->docount();
                break;
            default:
                $this->cookie();
                break;
        }
    }

    //=====================================================

    //-------------------------------------------------------------

    // NEWS

    //--------------------------------------------------------------

    public function coppa()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        $this->common_header(
            'docoppa',
            'COPPA Set-Up',
            'You may change the configuration below. Note, enabling <a href="http://www.ftc.gov/opa/1999/9910/childfinal.htm" target="_blank">COPPA</a> on your board will require children under the age of 13 to get parental consent via a faxed or mailed form.'
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Use COPPA registration system?</b>',
                $SKIN->form_yes_no('use_coppa', $INFO['use_coppa']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Fax number to receive COPPA forms</b>',
                $SKIN->form_input('coppa_fax', $form_array, $INFO['coppa_fax']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Mail address to receive COPPA forms</b>',
                $SKIN->form_textarea('coppa_address', $INFO['coppa_address']),
            ]
        );

        $this->common_footer();
    }

    //=====================================================

    public function docount()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        if ((!$IN['posts']) and (!$IN['members']) and (!$IN['lastreg'])) {
            $ADMIN->error('Nothing to recount!');
        }

        $stats = [];

        if ($IN['posts']) {
            $DB->query('SELECT COUNT(pid) as posts FROM ibf_posts WHERE queued <> 1');

            $r = $DB->fetch_row();

            $stats['TOTAL_REPLIES'] = $r['posts'];

            $stats['TOTAL_REPLIES'] < 1 ? 0 : $stats['TOTAL_REPLIES'];

            $DB->query('SELECT COUNT(tid) as topics FROM ibf_topics WHERE approved = 1');

            $r = $DB->fetch_row();

            $stats['TOTAL_TOPICS'] = $r['topics'];

            $stats['TOTAL_TOPICS'] < 1 ? 0 : $stats['TOTAL_TOPICS'];

            $stats['TOTAL_REPLIES'] -= $stats['TOTAL_TOPICS'];
        }

        if ($IN['members']) {
            $DB->query("SELECT COUNT(uid) as members from xbb_members WHERE mgroup <> '" . $INFO['auth_group'] . "'");

            $r = $DB->fetch_row();

            $stats['MEM_COUNT'] = $r['members'];

            // Remove "guest" account...

            $stats['MEM_COUNT']--;

            $stats['MEM_COUNT'] < 1 ? 0 : $stats['MEM_COUNT'];
        }

        if ($IN['lastreg']) {
            $DB->query("SELECT uid, uname FROM xbb_members WHERE mgroup <> '" . $INFO['auth_group'] . "' ORDER BY uid DESC LIMIT 0,1");

            $r = $DB->fetch_row();

            $stats['LAST_MEM_NAME'] = $r['uname'];

            $stats['LAST_MEM_ID'] = $r['uid'];
        }

        if ($IN['online']) {
            $stats['MOST_DATE'] = time();

            $stats['MOST_COUNT'] = 1;
        }

        if (count($stats) > 0) {
            $db_string = $DB->compile_db_update_string($stats);

            $DB->query("UPDATE ibf_stats SET $db_string");
        } else {
            $ADMIN->error('Nothing to recount!');
        }

        $ADMIN->done_screen('Statistics Recounted', 'Administration CP Home', 'act=index');
    }

    public function countstats()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        $ADMIN->page_detail = 'Please choose which statistics to recount.';

        $ADMIN->page_title = 'Recount Statistics Control';

        $ADMIN->html .= $SKIN->start_form(
            [
                1 => ['code', 'docount'],
2 => ['act', 'op'],
            ]
        );

        //+-------------------------------

        $SKIN->td_header[] = ['Statistic', '70%'];

        $SKIN->td_header[] = ['Option', '30%'];

        //+-------------------------------

        $ADMIN->html .= $SKIN->start_table('Recount Statistics');

        $ADMIN->html .= $SKIN->add_td_row(
            [
                'Recount total topics and posts',
                $SKIN->form_dropdown('posts', [0 => [1, 'Yes'], 1 => [0, 'No']]),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                'Recount Members',
                $SKIN->form_dropdown('members', [0 => [1, 'Yes'], 1 => [0, 'No']]),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                'Reset last registered member',
                $SKIN->form_dropdown('lastreg', [0 => [1, 'Yes'], 1 => [0, 'No']]),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                "Reset 'Most online' statistic?",
                $SKIN->form_dropdown('online', [0 => [0, 'No'], 1 => [1, 'Yes']]),
            ]
        );

        $ADMIN->html .= $SKIN->end_form('Reset these statistics');

        $ADMIN->html .= $SKIN->end_table();

        $ADMIN->output();
    }

    //-------------------------------------------------------------

    // CALENDAR

    //--------------------------------------------------------------

    public function calendar()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        $this->common_header('docalendar', 'Calendar Set Up', 'You may change the configuration below');

        $INFO['start_year'] = $INFO['start_year'] ?? 2001;

        $INFO['year_limit'] = $INFO['year_limit'] ?? 5;

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Show forthcoming events?</b><br>This will show calendar events on the board index page in the stats section.',
                $SKIN->form_yes_no('show_calendar', $INFO['show_calendar']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Show forthcoming events from today to [x] days ahead</b><br>This applies to the above option.',
                $SKIN->form_input('calendar_limit', $INFO['calendar_limit']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                "<b>Starting year for calendar 'Year' drop down box</b><br>This applies to view calendar / post event.",
                $SKIN->form_input('start_year', $INFO['start_year']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                "<b>Year end limit for 'Year' drop down box</b><br>This applies to view calendar / post event.<br>Example: current year is 2002, you enter 5 - last choosable year = 2007",
                $SKIN->form_input('year_limit', $INFO['year_limit']),
            ]
        );

        $this->common_footer();
    }

    //-------------------------------------------------------------

    // URLs and ADDRESSES

    //--------------------------------------------------------------

    public function board()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        $this->common_header('doboard', 'Board offline/online', 'You may change the configuration below');

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Turn the board offline?</b><br>The board will still be accessable by those who have permission',
                $SKIN->form_yes_no('board_offline', $INFO['board_offline']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>The offline message to display</b>',
                $SKIN->form_textarea('offline_msg', $INFO['offline_msg']),
            ]
        );

        $this->common_footer();
    }

    //-------------------------------------------------------------

    // EMOTICON FUNCTIONS

    //-------------------------------------------------------------

    public function doedit_emoticons()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        if ('' == $IN['before']) {
            $ADMIN->error('You must enter text to replace, silly!');
        }

        if ('' == $IN['id']) {
            $ADMIN->error('You must pass a valid emoticon id, silly!');
        }

        $IN['clickable'] = $IN['clickable'] ? 1 : 0;

        $db_string = $DB->compile_db_update_string(
            [
                'code' => $IN['before'],
'smile_url' => $IN['after'],
'clickable' => $IN['click'],
            ]
        );

        $DB->query("UPDATE xbb_emoticons SET $db_string WHERE id='" . $IN['id'] . "'");

        $std->boink_it($SKIN->base_url . '&act=op&code=emo');

        exit();
    }

    //=====================================================

    public function edit_emoticons()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        $ADMIN->page_detail = 'You may edit the emoticon filter below';

        $ADMIN->page_title = 'Edit Emoticon';

        //+-------------------------------

        if ('' == $IN['id']) {
            $ADMIN->error('You must pass a valid filter id, silly!');
        }

        //+-------------------------------

        $DB->query("SELECT * FROM xbb_emoticons WHERE id='" . $IN['id'] . "'");

        if (!$r = $DB->fetch_row()) {
            $ADMIN->error('We could not find that emoticon in the database');
        }

        //+-------------------------------

        $ADMIN->html .= $SKIN->start_form(
            [
                1 => ['code', 'emo_doedit'],
2 => ['act', 'op'],
3 => ['id', $IN['id']],
            ]
        );

        $SKIN->td_header[] = ['Before', '40%'];

        $SKIN->td_header[] = ['After', '40%'];

        $SKIN->td_header[] = ['+ Clickable', '20%'];

        //+-------------------------------

        $emos = [];

        if (!is_dir($INFO['html_dir'] . '/../../../uploads')) {
            $ADMIN->error("Could not locate the emoticons directory - make sure the 'html_dir' path is set correctly");
        }

        //+-------------------------------

        $dh = opendir($INFO['html_dir'] . '/../../../uploads') || die('Could not open the emoticons directory for reading, check paths and permissions');

        while ($file = readdir($dh)) {
            if (preg_match('/^smil.*/i', $file)) {
                $emos[] = [$file, $file];
            }
        }

        closedir($dh);

        //+-------------------------------

        $ADMIN->html .= $SKIN->start_table('Edit an Emoticon');

        $ADMIN->html .= "<script language='javascript'>
						 <!--
						 	function show_emo() {
						 	
						 		var emo_url = '{$INFO['html_url']}/../../../uploads/' + document.theAdminForm.after.options[document.theAdminForm.after.selectedIndex].value;
						 		
						 		document.images.emopreview.src = emo_url;
							}
						//-->
						</script>
						";

        $ADMIN->html .= $SKIN->add_td_row(
            [
                $SKIN->form_input('before', stripslashes($r['code'])),
                $SKIN->form_dropdown('after', $emos, $r['smile_url'], "onChange='show_emo()'") . "&nbsp;&nbsp;<img src='../../uploads/{$r['smile_url']}' name='emopreview' border='0'>",
                $SKIN->form_dropdown('click', [0 => [1, 'Yes'], 1 => [0, 'No']], $r['clickable']),
            ]
        );

        $ADMIN->html .= $SKIN->end_form('Edit Emoticon');

        $ADMIN->html .= $SKIN->end_table();

        $ADMIN->output();
    }

    //=====================================================

    public function remove_emoticons()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        if ('' == $IN['id']) {
            $ADMIN->error('You must pass a valid emoticon id, silly!');
        }

        $DB->query("DELETE FROM xbb_emoticons WHERE id='" . $IN['id'] . "'");

        $std->boink_it($SKIN->base_url . '&act=op&code=emo');

        exit();
    }

    //=====================================================

    public function add_emoticons()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        if ('' == $IN['before']) {
            $ADMIN->error('You must enter an emoticon text to replace, silly!');
        }

        $IN['click'] = $IN['click'] ? 1 : 0;

        $db_string = $DB->compile_db_insert_string(
            [
                'code' => $IN['before'],
'smile_url' => $IN['after'],
'clickable' => $IN['click'],
            ]
        );

        $DB->query('INSERT INTO xbb_emoticons (' . $db_string['FIELD_NAMES'] . ') VALUES(' . $db_string['FIELD_VALUES'] . ')');

        $std->boink_it($SKIN->base_url . '&act=op&code=emo');

        exit();
    }

    public function perly_length_sort($a, $b)
    {
        if (mb_strlen($a['code']) == mb_strlen($b['code'])) {
            return 0;
        }

        return (mb_strlen($a['code']) > mb_strlen($b['code'])) ? -1 : 1;
    }

    public function perly_word_sort($a, $b)
    {
        if (mb_strlen($a['code']) == mb_strlen($b['code'])) {
            return 0;
        }

        return (mb_strlen($a['code']) > mb_strlen($b['code'])) ? -1 : 1;
    }

    //=====================================================

    public function upload_emoticon()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $HTTP_POST_FILES;

        $FILE_NAME = $HTTP_POST_FILES['FILE_UPLOAD']['name'];

        $FILE_SIZE = $HTTP_POST_FILES['FILE_UPLOAD']['size'];

        $FILE_TYPE = $HTTP_POST_FILES['FILE_UPLOAD']['type'];

        // Naughty Opera adds the filename on the end of the

        // mime type - we don't want this.

        $FILE_TYPE = preg_replace('/^(.+?);.*$/', '\\1', $FILE_TYPE);

        if (!is_dir($INFO['html_dir'] . '/../../../uploads')) {
            $ADMIN->error("Could not locate the emoticons directory - make sure the 'html_dir' path is set correctly");
        }

        // Naughty Mozilla likes to use "none" to indicate an empty upload field.

        // I love universal languages that aren't universal.

        if ('' == $HTTP_POST_FILES['FILE_UPLOAD']['name'] or !$HTTP_POST_FILES['FILE_UPLOAD']['name'] or ('none' == $HTTP_POST_FILES['FILE_UPLOAD']['name'])) {
            $ADMIN->error('No file was chosen to upload!');
        }

        //-------------------------------------------------

        // Copy the upload to the uploads directory

        //-------------------------------------------------

        if (!@move_uploaded_file($HTTP_POST_FILES['FILE_UPLOAD']['tmp_name'], $INFO['html_dir'] . '/../../../uploads' . '/' . 'smiles-' . $FILE_NAME)) {
            $ADMIN->error('The upload failed');
        } else {
            @chmod($INFO['html_dir'] . '/../../../uploads' . '/' . $FILE_NAME, 0777);
        }

        $std->boink_it($SKIN->base_url . '&act=op&code=emo');

        exit();
    }

    public function emoticons()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        $ADMIN->page_detail = "You may add/edit or remove emoticons in this section.<br>You can only choose emoticons that have been uploaded into the ,<font color=blue> 'xoops/uploads' </font>directory.<br><br>Clickable refers to emoticons that are in the posting screens 'Clickable Emoticons' table.";

        $ADMIN->page_title = 'Emoticon Control';

        //+-------------------------------

        $SKIN->td_header[] = ['Before', '30%'];

        $SKIN->td_header[] = ['After', '30%'];

        $SKIN->td_header[] = ['+ Clickable', '20%'];

        $SKIN->td_header[] = ['Edit', '10%'];

        $SKIN->td_header[] = ['Remove', '10%'];

        //+-------------------------------

        $ADMIN->html .= $SKIN->start_table('Current Emoticons');

        $DB->query('SELECT * from xbb_emoticons');

        $emo_url = $INFO['html_url'] . '/../../../uploads';

        $smilies = [];

        if ($DB->get_num_rows()) {
            while (false !== ($r = $DB->fetch_row())) {
                $smilies[] = $r;
            }

            usort($smilies, ['ad_settings', 'perly_length_sort']);

            foreach ($smilies as $array_idx => $r) {
                $click = $r['clickable'] ? 'Yes' : 'No';

                $ADMIN->html .= $SKIN->add_td_row(
                    [
                        stripslashes($r['code']),
                        "<center><img src='$emo_url/{$r['smile_url']}'></center>",
                        "<center>$click</center>",
                        "<center><a href='" . $SKIN->base_url . "&act=op&code=emo_edit&id={$r['id']}'>Edit</a></center>",
                        "<center><a href='" . $SKIN->base_url . "&act=op&code=emo_remove&id={$r['id']}'>Remove</a></center>",
                    ]
                );
            }
        }

        $ADMIN->html .= $SKIN->end_table();

        //+-------------------------------

        $emos = [];

        if (!is_dir($INFO['html_dir'] . '/../../../uploads')) {
            $ADMIN->error("Could not locate the emoticons directory - make sure the 'html_dir' path is set correctly");
        }

        //+-------------------------------

        $cnt = 0;

        $start = '';

        $dh = opendir($INFO['html_dir'] . '/../../../uploads') || die('Could not open the emoticons directory for reading, check paths and permissions');

        while ($file = readdir($dh)) {
            if (preg_match('/^smil.*/i', $file)) {
                $emos[] = [$file, $file];

                if (0 == $cnt) {
                    $cnt = 1;

                    $start = $file;
                }
            }
        }

        closedir($dh);

        //+-------------------------------

        $ADMIN->html .= $SKIN->start_form(
            [
                1 => ['code', 'emo_add'],
2 => ['act', 'op'],
            ]
        );

        $SKIN->td_header[] = ['Before', '40%'];

        $SKIN->td_header[] = ['After', '40%'];

        $SKIN->td_header[] = ['+ Clickable', '20%'];

        //+-------------------------------

        $ADMIN->html .= "<script language='javascript'>
						 <!--
						 	function show_emo() {
						 	
						 		var emo_url = '{$INFO['html_url']}/../../../uploads/' + document.theAdminForm.after.options[document.theAdminForm.after.selectedIndex].value;
						 		
						 		document.images.emopreview.src = emo_url;
							}
						//-->
						</script>
						";

        $ADMIN->html .= $SKIN->start_table('Add a new Emoticon');

        $ADMIN->html .= $SKIN->add_td_row(
            [
                $SKIN->form_input('before'),
                $SKIN->form_dropdown('after', $emos, '', "onChange='show_emo()'") . "&nbsp;&nbsp;<img src='../../uploads/$start' name='emopreview' border='0'>",
                $SKIN->form_dropdown('click', [0 => [1, 'Yes'], 1 => [0, 'No']]),
            ]
        );

        $ADMIN->html .= $SKIN->end_form('Add Emoticon');

        $ADMIN->html .= $SKIN->end_table();

        //+-------------------------------

        //+-------------------------------

        $ADMIN->html .= $SKIN->start_form(
            [
                1 => ['code', 'emo_upload'],
2 => ['act', 'op'],
3 => ['MAX_FILE_SIZE', '10000000000'],
            ],
            'uploadform',
            " enctype='multipart/form-data'"
        );

        $SKIN->td_header[] = ['&nbsp;', '40%'];

        $SKIN->td_header[] = ['&nbsp;', '60%'];

        $ADMIN->html .= $SKIN->start_table('Upload an Emoticon to the emoticons directory');

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Choose a file from your computer to upload</b><br>After uploading, the emoticon will be selectable from the form above.',
                $SKIN->form_upload(),
            ]
        );

        $ADMIN->html .= $SKIN->end_form('Upload Emoticon');

        $ADMIN->html .= $SKIN->end_table();

        $ADMIN->output();
    }

    //-------------------------------------------------------------

    // BADWORD FUNCTIONS

    //--------------------------------------------------------------

    public function doedit_badword()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        if ('' == $IN['before']) {
            $ADMIN->error('You must enter a word to replace, silly!');
        }

        if ('' == $IN['id']) {
            $ADMIN->error('You must pass a valid filter id, silly!');
        }

        $IN['match'] = $IN['match'] ? 1 : 0;

        mb_strlen($IN['swop']) > 1 ? $IN['swop'] : '';

        $db_string = $DB->compile_db_update_string(
            [
                'type' => $IN['before'],
'swop' => $IN['after'],
'm_exact' => $IN['match'],
            ]
        );

        $DB->query("UPDATE ibf_badwords SET $db_string WHERE wid='" . $IN['id'] . "'");

        $std->boink_it($SKIN->base_url . '&act=op&code=bw');

        exit();
    }

    //=====================================================

    public function edit_badword()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        $ADMIN->page_detail = 'You may edit the chosen filter below';

        $ADMIN->page_title = 'Bad Word Filter';

        //+-------------------------------

        if ('' == $IN['id']) {
            $ADMIN->error('You must pass a valid filter id, silly!');
        }

        //+-------------------------------

        $DB->query("SELECT * FROM ibf_badwords WHERE wid='" . $IN['id'] . "'");

        if (!$r = $DB->fetch_row()) {
            $ADMIN->error('We could not find that filter in the database');
        }

        //+-------------------------------

        $ADMIN->html .= $SKIN->start_form(
            [
                1 => ['code', 'bw_doedit'],
2 => ['act', 'op'],
3 => ['id', $IN['id']],
            ]
        );

        $SKIN->td_header[] = ['Before', '40%'];

        $SKIN->td_header[] = ['After', '40%'];

        $SKIN->td_header[] = ['Method', '20%'];

        //+-------------------------------

        $ADMIN->html .= $SKIN->start_table('Edit a filter');

        $ADMIN->html .= $SKIN->add_td_row(
            [
                $SKIN->form_input('before', stripslashes($r['type'])),
                $SKIN->form_input('after', stripslashes($r['swop'])),
                $SKIN->form_dropdown('match', [0 => [1, 'Exact'], 1 => [0, 'Loose']], $r['m_exact']),
            ]
        );

        $ADMIN->html .= $SKIN->end_form('Edit Filter');

        $ADMIN->html .= $SKIN->end_table();

        $ADMIN->output();
    }

    //=====================================================

    public function remove_badword()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        if ('' == $IN['id']) {
            $ADMIN->error('You must pass a valid filter id, silly!');
        }

        $DB->query("DELETE FROM ibf_badwords WHERE wid='" . $IN['id'] . "'");

        $std->boink_it($SKIN->base_url . '&act=op&code=bw');

        exit();
    }

    //=====================================================

    public function add_badword()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        if ('' == $IN['before']) {
            $ADMIN->error('You must enter a word to replace, silly!');
        }

        $IN['match'] = $IN['match'] ? 1 : 0;

        mb_strlen($IN['swop']) > 1 ? $IN['swop'] : '';

        $db_string = $DB->compile_db_insert_string(
            [
                'type' => $IN['before'],
'swop' => $IN['after'],
'm_exact' => $IN['match'],
            ]
        );

        $DB->query('INSERT INTO ibf_badwords (' . $db_string['FIELD_NAMES'] . ') VALUES(' . $db_string['FIELD_VALUES'] . ')');

        $std->boink_it($SKIN->base_url . '&act=op&code=bw');

        exit();
    }

    //=====================================================

    public function badword()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        $ADMIN->page_detail = "You can add/edit and remove bad word filters in this section.<br>The badword filter allows you to globally replace words from a members post, signature and topic title.<br><br><b>Loose matching</b>: If you entered 'hell' as a bad word, it will replace 'hell' and 'hello' with either your replacement if entered or 6 hashes (case insensitive)<br><br><b>Exact matching</b>: If you entered 'hell' as a bad word, it will replace 'hell' only with either your replacement if entered or 6 hashes (case insensitive)";

        $ADMIN->page_title = 'Bad Word Filter';

        //+-------------------------------

        $ADMIN->html .= $SKIN->start_form(
            [
                1 => ['code', 'bw_add'],
2 => ['act', 'op'],
            ]
        );

        //+-------------------------------

        $SKIN->td_header[] = ['Before', '30%'];

        $SKIN->td_header[] = ['After', '30%'];

        $SKIN->td_header[] = ['Method', '20%'];

        $SKIN->td_header[] = ['Edit', '10%'];

        $SKIN->td_header[] = ['Remove', '10%'];

        //+-------------------------------

        $ADMIN->html .= $SKIN->start_table('Current Filters');

        $DB->query('SELECT * from ibf_badwords');

        if ($DB->get_num_rows()) {
            while (false !== ($r = $DB->fetch_row())) {
                $words[] = $r;
            }

            usort($words, ['ad_settings', 'perly_word_sort']);

            foreach ($words as $idx => $r) {
                $replace = $r['swop'] ? stripslashes($r['swop']) : '######';

                $method = $r['m_exact'] ? 'Exact' : 'Loose';

                $ADMIN->html .= $SKIN->add_td_row(
                    [
                        stripslashes($r['type']),
                        $replace,
                        $method,
                        "<center><a href='" . $SKIN->base_url . "&act=op&code=bw_edit&id={$r['wid']}'>Edit</a></center>",
                        "<center><a href='" . $SKIN->base_url . "&act=op&code=bw_remove&id={$r['wid']}'>Remove</a></center>",
                    ]
                );
            }
        }

        $ADMIN->html .= $SKIN->end_table();

        $SKIN->td_header[] = ['Before', '40%'];

        $SKIN->td_header[] = ['After', '40%'];

        $SKIN->td_header[] = ['Method', '20%'];

        //+-------------------------------

        $ADMIN->html .= $SKIN->start_table('Add a new filter');

        $ADMIN->html .= $SKIN->add_td_row(
            [
                $SKIN->form_input('before'),
                $SKIN->form_input('after'),
                $SKIN->form_dropdown('match', [0 => [1, 'Exact'], 1 => [0, 'Loose']]),
            ]
        );

        $ADMIN->html .= $SKIN->end_form('Add Filter');

        $ADMIN->html .= $SKIN->end_table();

        $ADMIN->output();
    }

    //-------------------------------------------------------------

    // NEWS

    //--------------------------------------------------------------

    public function news()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        $this->common_header('donews', 'News Export Set-Up', 'You may change the configuration below');

        $DB->query('SELECT id, name FROM ibf_forums WHERE subwrap = 0');

        $form_array = [];

        while (false !== ($r = $DB->fetch_row())) {
            $form_array[] = [$r['id'], $r['name']];
        }

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Export news topics from which forum?</b>',
                $SKIN->form_dropdown('news_forum_id', $form_array, $INFO['news_forum_id']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                "<b>Show a 'Latest News' link on the board index?</b>",
                $SKIN->form_yes_no('index_news_link', $INFO['index_news_link']),
            ]
        );

        $this->common_footer();
    }

    //-------------------------------------------------------------

    // PM

    //--------------------------------------------------------------

    public function pm()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        $this->common_header('dopm', 'Messenger Set up', 'You may change the configuration below');

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Allow IBF Code in messages?</b>',
                $SKIN->form_yes_no('msg_allow_code', $INFO['msg_allow_code']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Allow HTML in messages?</b>',
                $SKIN->form_yes_no('msg_allow_html', $INFO['msg_allow_html']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Max. number of messages to show per page when viewing message list</b><br>Default is 50',
                $SKIN->form_input('show_max_msg_list', $INFO['show_max_msg_list']),
            ]
        );

        $this->common_footer();
    }

    //-------------------------------------------------------------

    // EMAIL functions

    //--------------------------------------------------------------

    public function email()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        $this->common_header('doemail', 'Email Set Up', 'You may change the configuration below');

        $ADMIN->html .= $SKIN->add_td_basic('Email Addresses', 'left', 'catrow2');

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Board incoming email address</b>',
                $SKIN->form_input('email_in', $INFO['email_in']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Board outgoing email address</b>',
                $SKIN->form_input('email_out', $INFO['email_out']),
            ]
        );

        //-----------------------------------------------------------------------------------------------------------

        $ADMIN->html .= $SKIN->add_td_basic('Mail Method', 'left', 'catrow2');

        //-----------------------------------------------------------------------------------------------------------

        $ADMIN->html .= $SKIN->add_td_row(
            [
                "<b>Mail Method</b><br>If PHP's mail() isn't available, choose SMTP",
                $SKIN->form_dropdown(
                    'mail_method',
                    [
                        0 => ['mail', 'PHP mail()'],
1 => ['smtp', 'SMTP'],
                    ],
                    $INFO['mail_method']
                ),
            ]
        );

        //-----------------------------------------------------------------------------------------------------------

        $ADMIN->html .= $SKIN->add_td_basic('SMTP Options', 'left', 'catrow2');

        //-----------------------------------------------------------------------------------------------------------

        $ADMIN->html .= $SKIN->add_td_row(
            [
                "<b>Over-ride SMTP Host?</b><br>Default is 'localhost'",
                $SKIN->form_input('smtp_host', $INFO['smtp_host']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Over-ride SMTP Port?</b><br>Default is 25',
                $SKIN->form_input('smtp_port', $INFO['smtp_port']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                "<b>SMTP UserName</b><br>Not required in most cases when using 'localhost'",
                $SKIN->form_input('smtp_user', $INFO['smtp_user']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                "<b>SMTP Password</b><br>Not required in most cases when using 'localhost'",
                $SKIN->form_input('smtp_pass', $INFO['smtp_pass']),
            ]
        );

        $this->common_footer();
    }

    //-------------------------------------------------------------

    // URLs and ADDRESSES

    //--------------------------------------------------------------

    public function url()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        $this->common_header('dourl', 'Global Set Up', 'You may change the configuration below');

        //-----------------------------------------------------------------------------------------------------------

        $ADMIN->html .= $SKIN->add_td_basic('Board Name and HTTP addresses', 'left', 'catrow2');

        //-----------------------------------------------------------------------------------------------------------

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Board Name</b>',
                $SKIN->form_input('board_name', $INFO['board_name']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Board Address</b>',
                $SKIN->form_input('board_url', $INFO['board_url']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Website Name</b><br>(Not currently used in Invision Power Board)',
                $SKIN->form_input('home_name', $INFO['home_name']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Website Address</b><br>(Not currently used in Invision Power Board)',
                $SKIN->form_input('home_url', $INFO['home_url']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>HTML URL</b><br>For images, etc',
                $SKIN->form_input('html_url', $INFO['html_url']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Upload URL</b>',
                $SKIN->form_input('upload_url', $INFO['upload_url']),
            ]
        );

        //-----------------------------------------------------------------------------------------------------------

        $ADMIN->html .= $SKIN->add_td_basic('Board Server Paths', 'left', 'catrow2');

        //-----------------------------------------------------------------------------------------------------------

        $ADMIN->html .= $SKIN->add_td_row(
            [
                "<b>Path to 'html' directory</b><br>Note: this is a path, not a URL",
                $SKIN->form_input('html_dir', $INFO['html_dir']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Upload Directory</b>',
                $SKIN->form_input('upload_dir', $INFO['upload_dir']),
            ]
        );

        //-----------------------------------------------------------------------------------------------------------

        $ADMIN->html .= $SKIN->add_td_basic('HTTP Environment', 'left', 'catrow2');

        //-----------------------------------------------------------------------------------------------------------

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Print HTTP headers?</b><br>(Some NT installs require this off)',
                $SKIN->form_yes_no('print_headers', $INFO['print_headers']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b><i>DISABLE</I> GZIP encoding?</b><br>(GZIP enables faster page transfer and lower bandwidth use)',
                $SKIN->form_yes_no('disable_gzip', $INFO['disable_gzip']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Type of auto-redirect?</b><br>(This is for quick no page redirects)',
                $SKIN->form_dropdown(
                    'header_redirect',
                    [
                        0 => ['location', 'Location type (*nix savvy)'],
1 => ['refresh', 'Refresh (Windows savvy)'],
2 => ['html', 'HTML META redirect (If all else fails...)'],
                    ],
                    $INFO['header_redirect']
                ),
            ]
        );

        //-----------------------------------------------------------------------------------------------------------

        $ADMIN->html .= $SKIN->add_td_basic('Debugging', 'left', 'catrow2');

        //-----------------------------------------------------------------------------------------------------------

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Debug level</b>',
                $SKIN->form_dropdown(
                    'debug_level',
                    [
                        0 => [0, '0: None - Don\'t show any debug information'],
1 => [1, '1: Show server load, page generation times and query count'],
2 => [2, '2: Show level 1 (above) and GET and POST information'],
3 => [3, '3: Show level 1 + 2 and database queries'],
                    ],
                    $INFO['debug_level']
                ),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                "<b><i>ENABLE</I> SQL Debug Mode?</b><br>(If yes, add '&debug=1' to any page to view mySQL debug info)",
                $SKIN->form_yes_no('sql_debug', $INFO['sql_debug']),
            ]
        );

        //-----------------------------------------------------------------------------------------------------------

        $ADMIN->html .= $SKIN->add_td_basic('Global Skin Settings', 'left', 'catrow2');

        //-----------------------------------------------------------------------------------------------------------

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Use safe mode skins?</b><br>(Note: You may need to resynchronise your template sets after changing this if you have custom/edited skins)',
                $SKIN->form_dropdown(
                    'safe_mode_skins',
                    [
                        0 => ['0', 'No'],
1 => ['1', 'Yes'],
                    ],
                    $INFO['safe_mode_skins']
                ),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Number Formatting</b><br>You may choose which character to separate thousands from hundreds<br>(EG: USA & UK use a comma)',
                $SKIN->form_dropdown(
                    'number_format',
                    [
                        0 => ['none', 'Don\'t format'],
1 => ['space', 'Space'],
2 => [',', ','],
3 => ['.', '.'],
                    ],
                    $INFO['number_format']
                ),
            ]
        );

        $this->common_footer();
    }

    //-------------------------------------------------------------

    // CPU SAVING

    //--------------------------------------------------------------

    public function cpu()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        $this->common_header('docpu', 'CPU Saving', 'You can opt to turn some features off to minimize the resource footprint');

        if ('' == $INFO['au_cutoff']) {
            $INFO['au_cutoff'] = 15;
        }

        //-----------------------------------------------------------------------------------------------------------

        $ADMIN->html .= $SKIN->add_td_basic('SQL Savings', 'left', 'catrow2');

        //-----------------------------------------------------------------------------------------------------------

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Show Active Users?</b>',
                $SKIN->form_yes_no('show_active', $INFO['show_active']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Cut off for active user display in minutes</b>',
                $SKIN->form_input('au_cutoff', $INFO['au_cutoff']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Show Birthdays?</b>',
                $SKIN->form_yes_no('show_birthdays', $INFO['show_birthdays']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Show Board Totals?</b>',
                $SKIN->form_yes_no('show_totals', $INFO['show_totals']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Mark topics a user has posted when displaying a forum?</b>',
                $SKIN->form_yes_no('show_user_posted', $INFO['show_user_posted']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                "<b>Remove 'Users Browsing this <u>forum</u>' feature?</b><br>(This save 1 query per forum view)",
                $SKIN->form_yes_no('no_au_forum', $INFO['no_au_forum']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                "<b>Remove 'Users Browsing this <u>topic</u>' feature?</b><br>(This save 1 query per topic view)",
                $SKIN->form_yes_no('no_au_topic', $INFO['no_au_topic']),
            ]
        );

        //-----------------------------------------------------------------------------------------------------------

        $ADMIN->html .= $SKIN->add_td_basic('CPU Savings', 'left', 'catrow2');

        //-----------------------------------------------------------------------------------------------------------

        $ADMIN->html .= $SKIN->add_td_row(
            [
                "<b>Server Load Limit</b><br>Will display 'busy' message when limit hit<br>Can be left blank for no limit",
                $SKIN->form_input('load_limit', $INFO['load_limit']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Allow users (where allowed) to use search?</b>',
                $SKIN->form_yes_no('allow_search', $INFO['allow_search']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Cut search post to [x] characters</b><br>Refers to when returning search results as posts',
                $SKIN->form_input('search_post_cut', $INFO['search_post_cut']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                "<b>Minimum search word length</b><br>Allowing shorter search words can return more results, such as 'if', 'at', etc",
                $SKIN->form_input('min_search_word', $INFO['min_search_word']),
            ]
        );

        //-----------------------------------------------------------------------------------------------------------

        $ADMIN->html .= $SKIN->add_td_basic('Bandwidth Savings', 'left', 'catrow2');

        //-----------------------------------------------------------------------------------------------------------

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Print HTTP no-cache headers?</b><br>(This will stop browsers caching pages)',
                $SKIN->form_yes_no('nocache', $INFO['nocache']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Show short forum jump list?</b><br>This will remove sub-forums from the drop down list - useful if you have many',
                $SKIN->form_yes_no('short_forum_jump', $INFO['short_forum_jump']),
            ]
        );

        $this->common_footer();
    }

    //-------------------------------------------------------------

    // DATES

    //--------------------------------------------------------------

    public function dates()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        $this->common_header('dodates', 'Dates', 'Define date formats');

        $time_array = [];

        require ROOT_PATH . 'lang/en/lang_ucp.php';

        foreach ($lang as $off => $words) {
            if (preg_match("/^time_(\S+)$/", $off, $match)) {
                $time_select[] = [$match[1], $words];
            }
        }

        $d_date = $std->get_date(time(), 'LONG');

        $ADMIN->html .= $SKIN->add_td_row(
            [
                "<b>Native Server Time Zone</b><br><span style='color:red'>If you have chosen the correct timezone and the clock is an hour out, this is because of daylight savings time and your members can correct this by editing their 'Board settings' via their User Control Panel.</span>",
                $SKIN->form_dropdown('time_offset', $time_select, $INFO['time_offset']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                "<b>Server Time Adjustment (in minutes)</b><br>You fine tune the server time. If you need to subtract minutes from the server time, start the number with a '-' (no quotes).",
                $SKIN->form_input('time_adjust', $INFO['time_adjust']) . "<br>Board time (inc. above time zone and current adj.) is now: $d_date",
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                "<b>Short time format</b><br>Same configuration as <a href='http://www.php.net/date' target='_blank'>PHP Date</a>",
                $SKIN->form_input('clock_short', $INFO['clock_short']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                "<b>Join date time format</b><br>Same configuration as <a href='http://www.php.net/date' target='_blank'>PHP Date</a>",
                $SKIN->form_input('clock_joined', $INFO['clock_joined']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                "<b>Long time format</b><br>Same configuration as <a href='http://www.php.net/date' target='_blank'>PHP Date</a>",
                $SKIN->form_input('clock_long', $INFO['clock_long']),
            ]
        );

        $this->common_footer();
    }

    //-------------------------------------------------------------

    // AVATARS

    //--------------------------------------------------------------

    public function avatars()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        $this->common_header('doavatars', 'User Profiles', 'Define user profile permissions');

        $INFO['avatar_ext'] = preg_replace("/\|/", ',', $INFO['avatar_ext']);

        //-----------------------------------------------------------------------------------------------------------

        $ADMIN->html .= $SKIN->add_td_basic('User Profiles & Options', 'left', 'catrow2');

        //-----------------------------------------------------------------------------------------------------------

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Allow members to choose skins?</b>',
                $SKIN->form_yes_no('allow_skins', $INFO['allow_skins']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Number of posts a member must have over before allowing them to change their member title?</b><br>Leave blank to disable completely',
                $SKIN->form_input('post_titlechange', $INFO['post_titlechange']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Maximum length (in bytes) for the location field entry</b>',
                $SKIN->form_input('max_location_length', $INFO['max_location_length']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Maximum length (in bytes) for the interests field entry</b>',
                $SKIN->form_input('max_interest_length', $INFO['max_interest_length']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Maximum length (in bytes) for user signatures</b>',
                $SKIN->form_input('max_sig_length', $INFO['max_sig_length']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Allow HTML in signatures?</b>',
                $SKIN->form_yes_no('sig_allow_html', $INFO['sig_allow_html']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Allow IBF Code in signatures?</b>',
                $SKIN->form_yes_no('sig_allow_ibc', $INFO['sig_allow_ibc']),
            ]
        );

        if ('' == $INFO['postpage_contents']) {
            $INFO['postpage_contents'] = '5,10,15,20,25,30,35,40';
        }

        if ('' == $INFO['topicpage_contents']) {
            $INFO['topicpage_contents'] = '5,10,15,20,25,30,35,40';
        }

        $ADMIN->html .= $SKIN->add_td_row(
            [
                "<b>User selectable posts per page dropdown contents</b><br>Separate with a comma, 'Use forum default' added automatically<br>Example: 5,15,20,25,30",
                $SKIN->form_input('postpage_contents', $INFO['postpage_contents']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                "<b>User selectable topics per forum page dropdown contents</b><br>Separate with a comma, 'Use forum default' added automatically<br>Example: 5,15,20,25,30",
                $SKIN->form_input('topicpage_contents', $INFO['topicpage_contents']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Auto prune all topic subscriptions if the topic has no replies over [x] days</b><br>Leave blank for no auto prune limit',
                $SKIN->form_input('subs_autoprune', $INFO['subs_autoprune']),
            ]
        );

        //-----------------------------------------------------------------------------------------------------------

        $ADMIN->html .= $SKIN->add_td_basic('Avatars', 'left', 'catrow2');

        //-----------------------------------------------------------------------------------------------------------

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Allow the use of avatars?</b>',
                $SKIN->form_yes_no('avatars_on', $INFO['avatars_on']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Allowed image extensions</b><br>Seperate with comma (gif,png,jpeg) etc',
                $SKIN->form_input('avatar_ext', $INFO['avatar_ext']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Allow users to use remote URL avatars?</b>',
                $SKIN->form_yes_no('avatar_url', $INFO['avatar_url']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Max. file size for avatar uploads? (K)</b>',
                $SKIN->form_input('avup_size_max', $INFO['avup_size_max']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Maximum avatar dimensions</b><br>(WIDTH<b>x</b>HEIGHT)',
                $SKIN->form_input('avatar_dims', $INFO['avatar_dims']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Default sizes for gallery avatars</b><br>(WIDTH<b>x</b>HEIGHT)',
                $SKIN->form_input('avatar_def', $INFO['avatar_def']),
            ]
        );

        //-----------------------------------------------------------------------------------------------------------

        $ADMIN->html .= $SKIN->add_td_basic('Guest Permissions', 'left', 'catrow2');

        //-----------------------------------------------------------------------------------------------------------

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Allow GUESTS to view signatures?</b>',
                $SKIN->form_yes_no('guests_sig', $INFO['guests_sig']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Allow GUESTS to view posted images?</b>',
                $SKIN->form_yes_no('guests_img', $INFO['guests_img']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Allow GUESTS to view user avatars?</b>',
                $SKIN->form_yes_no('guests_ava', $INFO['guests_ava']),
            ]
        );

        $this->common_footer();
    }

    //-------------------------------------------------------------

    // TOPICS and POSTS

    //--------------------------------------------------------------

    public function post()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        $INFO['img_ext'] = preg_replace("/\|/", ',', $INFO['img_ext']);

        $this->common_header('dopost', 'Topics, Posts and Posting', 'Configure the viewable post elements and limits.');

        //-----------------------------------------------------------------------------------------------------------

        $ADMIN->html .= $SKIN->add_td_basic('Topics', 'left', 'catrow2');

        //-----------------------------------------------------------------------------------------------------------

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Number of topics per forum page</b>',
                $SKIN->form_input('display_max_topics', $INFO['display_max_topics']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                "<b>Number of posts needed to make a 'hot topic'?</b>",
                $SKIN->form_input('hot_topic', $INFO['hot_topic']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Topic prefix for PINNED topics</b>',
                $SKIN->form_input('pre_pinned', $INFO['pre_pinned']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Topic prefix for MOVED topics</b>',
                $SKIN->form_input('pre_moved', $INFO['pre_moved']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Topic prefix for POLLS</b>',
                $SKIN->form_input('pre_polls', $INFO['pre_polls']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Stop shouting in topic titles?</b><br>(Will turn: CLICK HERE into Click Here)',
                $SKIN->form_yes_no('etfilter_shout', $INFO['etfilter_shout']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Remove excess exclamation/question marks in topic titles?</b><br>(Will turn: This!!!!! into This!)',
                $SKIN->form_yes_no('etfilter_punct', $INFO['etfilter_punct']),
            ]
        );

        //-----------------------------------------------------------------------------------------------------------

        $ADMIN->html .= $SKIN->add_td_basic('Posts & Posting', 'left', 'catrow2');

        //-----------------------------------------------------------------------------------------------------------

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Number of posts per topic page</b>',
                $SKIN->form_input('display_max_posts', $INFO['display_max_posts']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>No. emoticons per clickable table row</b>',
                $SKIN->form_input('emo_per_row', $INFO['emo_per_row']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Max. no. emoticons per post</b>',
                $SKIN->form_input('max_emos', $INFO['max_emos']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Max. no. images per post</b>',
                $SKIN->form_input('max_images', $INFO['max_images']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Max. size of post (in kilobytes [k])</b>',
                $SKIN->form_input('max_post_length', $INFO['max_post_length']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Max. width of posted Flash movies (in pixels)</b>',
                $SKIN->form_input('max_w_flash', $INFO['max_w_flash']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Max. height of posted Flash movies (in pixels)</b>',
                $SKIN->form_input('max_h_flash', $INFO['max_h_flash']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Valid postable image extensions</b><br>(Seperate with comma (gif,jpeg,jpg) etc',
                $SKIN->form_input('img_ext', $INFO['img_ext']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Show uploaded images in post?</b>',
                $SKIN->form_yes_no('show_img_upload', $INFO['show_img_upload']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                "<b>Stop Quote Embedding?</b><br>This will remove any quoted text when quoting a post that contains quotes<br><a href='#' title='and if that made any sense, then you have a larger IQ than me.'>..</a>",
                $SKIN->form_yes_no('strip_quotes', $INFO['strip_quotes']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Guest names <i>prefix</i></b><br>(This is for when a guest posts with a members name, it allows for a visual difference to prevent confusion)',
                $SKIN->form_input('guest_name_pre', $INFO['guest_name_pre']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Guest names <i>suffix</i></b><br>(This is for when a guest posts with a members name, it allows for a visual difference to prevent confusion)',
                $SKIN->form_input('guest_name_suf', $INFO['guest_name_suf']),
            ]
        );

        //-----------------------------------------------------------------------------------------------------------

        $ADMIN->html .= $SKIN->add_td_basic('Polls', 'left', 'catrow2');

        //-----------------------------------------------------------------------------------------------------------

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Allow [IMG] and [URL] tags in polls?</b>',
                $SKIN->form_yes_no('poll_tags', $INFO['poll_tags']),
            ]
        );

        $this->common_footer();
    }

    //-------------------------------------------------------------

    // SECURITY

    //--------------------------------------------------------------

    public function secure()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        $this->common_header('dosecure', 'Security', 'Define the level of security your board possess by using the configurations below');

        //-----------------------------------------------------------------------------------------------------------

        $ADMIN->html .= $SKIN->add_td_basic('Security (High)', 'left', 'catrow2');

        //-----------------------------------------------------------------------------------------------------------

        $ADMIN->html .= $SKIN->add_td_row(
            [
                "<b>Allow dynamic images?</b><br>If 'yes' users can post scripted image generators",
                $SKIN->form_yes_no('allow_dynamic_img', $INFO['allow_dynamic_img']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Session Expiration (in seconds)</b><br>Removes inactive sessions over the limit you specify',
                $SKIN->form_input('session_expiration', $INFO['session_expiration']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Match users browsers while validating?</b>',
                $SKIN->form_yes_no('match_browser', $INFO['match_browser']),
            ]
        );

        //-----------------------------------------------------------------------------------------------------------

        $ADMIN->html .= $SKIN->add_td_basic('Security (Medium)', 'left', 'catrow2');

        //-----------------------------------------------------------------------------------------------------------

        $ADMIN->html .= $SKIN->add_td_row(
            [
                "<b>Enable Registration Bot Flood Control?</b><br>Forces users to input a random code to prevent bot's from spamming the registration." . $SKIN->js_help_link('s_reg_antispam'),
                $SKIN->form_yes_no('reg_antispam', $INFO['reg_antispam']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Use secure mail form for member to member mails?</b><br>Hides users email addresses',
                $SKIN->form_yes_no('use_mail_form', $INFO['use_mail_form']),
            ]
        );

        //-----------------------------------------------------------------------------------------------------------

        $ADMIN->html .= $SKIN->add_td_basic('Security (Low)', 'left', 'catrow2');

        //-----------------------------------------------------------------------------------------------------------

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Allow images to be posted?</b><br>Advanced programmers can force images to run as scripts. IBF limits damage by this method however.',
                $SKIN->form_yes_no('allow_images', $INFO['allow_images']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Allow flash movies in posts and avatars?</b><br>Flash has a built in scripting language which may or may not compromise security',
                $SKIN->form_yes_no('allow_flash', $INFO['allow_flash']),
            ]
        );

        //-----------------------------------------------------------------------------------------------------------

        $ADMIN->html .= $SKIN->add_td_basic('Security (Troublesome Users)', 'left', 'catrow2');

        //-----------------------------------------------------------------------------------------------------------

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Allow duplicate emails when user registers?</b><br>Will not check for existing email address',
                $SKIN->form_yes_no('allow_dup_email', $INFO['allow_dup_email']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>New registration email validation?</b><br>Make admin manually preview all new accounts or make new users validate their email address',
                $SKIN->form_dropdown(
                    'reg_auth_type',
                    [
                        0 => ['user', 'User Email Validation'],
1 => ['admin', 'Admin Validation'],
2 => ['0', 'None'],
                    ],
                    $INFO['reg_auth_type']
                ),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Get notified when a new user registers via email?</b>',
                $SKIN->form_dropdown(
                    'new_reg_notify',
                    [
                        0 => ['1', 'Yes'],
1 => ['0', 'No'],
                    ],
                    $INFO['new_reg_notify']
                ),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Force guests to log in before allowing access to the board?</b>',
                $SKIN->form_yes_no('force_login', $INFO['force_login']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Disable new registrations?</b>',
                $SKIN->form_yes_no('no_reg', $INFO['no_reg']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                "<b>Disable 'Report this post to a moderator' link?</b>",
                $SKIN->form_yes_no('disable_reportpost', $INFO['disable_reportpost']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Flood control delay (in seconds)</b><br>Make users wait before posting again<br>Can be left blank for no flood control',
                $SKIN->form_input('flood_control', $INFO['flood_control']),
            ]
        );

        //-----------------------------------------------------------------------------------------------------------

        $ADMIN->html .= $SKIN->add_td_basic('Privacy', 'left', 'catrow2');

        //-----------------------------------------------------------------------------------------------------------

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Allow users to browse the Active Users list?</b>',
                $SKIN->form_yes_no('allow_online_list', $INFO['allow_online_list']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Disable root admin group viewing anonymous online users?</b><br>Anonymous users have an asterisk after their name',
                $SKIN->form_yes_no('disable_admin_anon', $INFO['disable_admin_anon']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Disable root admin group viewing online users IP address in online user list?</b>',
                $SKIN->form_yes_no('disable_online_ip', $INFO['disable_online_ip']),
            ]
        );

        $this->common_footer();
    }

    //-------------------------------------------------------------

    // COOKIES: Yum Yum!

    //--------------------------------------------------------------

    public function cookie()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        $this->common_header('docookie', 'Cookies', 'All of these fields can be left blank. Experiment to find the correct settings for your host');

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Cookie Domain</b><br>Hint: use <b>.your-domain.com</b> for global cookies',
                $SKIN->form_input('cookie_domain', $INFO['cookie_domain']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Cookie Name Prefix</b><br>Allows multiple boards on one host.',
                $SKIN->form_input('cookie_id', $INFO['cookie_id']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Cookie Path</b><br>Relative path from domain to root IBF dir',
                $SKIN->form_input('cookie_path', $INFO['cookie_path']),
            ]
        );

        $this->common_footer();
    }

    //-------------------------------------------------------------

    // Save config. Does the hard work, so you don't have to.

    //--------------------------------------------------------------

    public function save_config($new)
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $_POST;

        $master = [];

        if (is_array($new)) {
            if (count($new) > 0) {
                foreach ($new as $field) {
                    // Handle special..

                    if ('img_ext' == $field or 'avatar_ext' == $field) {
                        $_POST[$field] = preg_replace("/[\.\s]/", '', $_POST[$field]);

                        $_POST[$field] = preg_replace('/,/', '|', $_POST[$field]);
                    } elseif ('coppa_address' == $field) {
                        $_POST[$field] = nl2br($_POST[$field]);
                    }

                    $_POST[$field] = preg_replace("/'/", '&#39;', stripslashes($_POST[$field]));

                    $master[$field] = stripslashes($_POST[$field]);
                }

                $ADMIN->rebuild_config($master);
            }
        }

        $ADMIN->save_log('Board Settings Updated, Back Up Written');

        $ADMIN->done_screen('Forum Configurations updated', 'Administration CP Home', 'act=index');
    }

    //-------------------------------------------------------------

    // Common header: Saves writing the same stuff out over and over

    //--------------------------------------------------------------

    public function common_header($formcode = '', $section = '', $extra = '')
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        $extra = $extra ? $extra . '<br>' : $extra;

        $ADMIN->page_detail = $extra . 'Please check the data you are entering before submitting the changes';

        $ADMIN->page_title = "Board Settings ($section)";

        //+-------------------------------

        $ADMIN->html .= $SKIN->start_form(
            [
                1 => ['code', $formcode],
2 => ['act', 'op'],
            ]
        );

        //+-------------------------------

        $SKIN->td_header[] = ['&nbsp;', '40%'];

        $SKIN->td_header[] = ['&nbsp;', '60%'];

        //+-------------------------------

        $ADMIN->html .= $SKIN->start_table('Settings');
    }

    //-------------------------------------------------------------

    // Common footer: Saves writing the same stuff out over and over

    //--------------------------------------------------------------

    public function common_footer($button = 'Submit Changes')
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        $ADMIN->html .= $SKIN->end_form($button);

        $ADMIN->html .= $SKIN->end_table();

        $ADMIN->output();
    }
}

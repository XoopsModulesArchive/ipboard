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
|   > Admin Framework for IPS Services
|   > Module written by Matt Mecham
|   > Date started: 17 February 2003
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

$idx = new ad_plugins();

class ad_plugins
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

        // Make sure we're a root admin, or else!

        if ($MEMBER['mgroup'] != $INFO['admin_group']) {
            $ADMIN->error('Sorry, these functions are for the root admin group only');
        }

        switch ($IN['code']) {
            case 'ipchat':
                $this->chat_splash();
                break;
            case 'chatframe':
                $this->chat_frame();
                break;
            case 'chatsave':
                $this->chat_save();
                break;
            case 'dochat':
                $this->chat_config_save();
                break;
            default:
                exit();
                break;
        }
    }

    //-------------------------------------------------------------

    // CHAT SPLASH

    //--------------------------------------------------------------

    public function chat_splash()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        //---------------------------------------

        // Do we have an order number

        //---------------------------------------

        if ($INFO['chat_account_no']) {
            $this->chat_config();
        } else {
            $frames = "<html>
		   			 <head><title>Invision Power Board: Chat Set up</title></head>
					   <frameset rows='*,50' frameborder='yes' border='1' framespacing='0'>
					   	<frame name='chat_top'   scrolling='auto' src='http://www.invisionchat.com/?acp++acp'>
					   	<frame name='chat_bottom'  scrolling='auto' src='{$SKIN->base_url}&act=pin&code=chatframe'>
					   </frameset>
				   </html>";

            print $frames;

            exit();
        }
    }

    //---------------------------------------------------------------

    public function chat_frame()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        $css = $SKIN->get_css();

        $html = "<html>
				  <head>
				   <title>Invision Power Board Order Box</title>
				   $css
				  </head>
				  <body marginheight='0' marginwidth='0' leftmargin='0' topmargin='0' bgcolor='#4C77B6'>
				  <table cellpadding=4 cellspacing=0 border=0 align='center'>
				  <form action='{$SKIN->base_url}&act=pin&code=chatsave' method='POST' target='body'>
				  <tr>
				   <td valign='middle' align='left'><b style='color:white'>Ordered IP Chat?</b></td>
				   <td valign='middle' align='left'><input type='text' size=35 name='account_no' value='enter your account number here...' onClick=\"this.value='';\"></td>
				   <td valign='middle' align='left'><input type='submit' value='Continue...'></td>
				  </tr>
				  </table>
				  </form>
				  </body>
				 </html>";

        echo $html;

        exit();
    }

    //---------------------------------------------------------------

    public function chat_save()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        $acc_number = (int)$IN['account_no'];

        if ('' == $acc_number) {
            $ADMIN->error('Sorry, that is not a valid IP Chat account number');
        }

        $ADMIN->rebuild_config(['chat_account_no' => $acc_number]);

        $this->chat_config();
    }

    //---------------------------------------------------------------

    public function chat_config_save()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        $acc_number = (int)$IN['chat_account_no'];

        //if ( $acc_number == "" )

        //{

        //	$ADMIN->error("Sorry, that is not a valid IP Chat account number");

        //}

        $new = [
            'chat_account_no' => $acc_number,
'chat_allow_guest' => $IN['chat_allow_guest'],
'chat_width' => $IN['chat_width'],
'chat_height' => $IN['chat_height'],
'chat_language' => $IN['chat_language'],
'chat_display' => $IN['chat_display'],
        ];

        // Get the ID's of the groups we're emailing.

        $ids = [];

        foreach ($IN as $key => $value) {
            if (preg_match("/^sg_(\d+)$/", $key, $match)) {
                if ($IN[$match[0]]) {
                    $ids[] = $match[1];
                }
            }
        }

        $new['chat_admin_groups'] = implode(',', $ids);

        $ADMIN->rebuild_config($new);

        $ADMIN->done_screen('IP Chat Configurations Updated', 'IP Chat Configuration', 'act=pin&code=ipchat');
    }

    //--------------------------------------------------------------

    public function chat_config()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        $ADMIN->page_detail = 'You may edit the configuration below to suit';

        $ADMIN->page_title = 'IP Chat Configuration';

        //+-------------------------------

        // SET UP SOME DEFAULTS

        //+-------------------------------

        $language = '' == $INFO['chat_language'] ? 'en' : $INFO['chat_language'];

        $larray = [
            0 => ['en', 'English'],
1 => ['ar', 'Arabic'],
2 => ['de', 'German'],
3 => ['es', 'Spanish'],
4 => ['fr', 'French'],
5 => ['hr', 'Croation'],
6 => ['it', 'Italian'],
7 => ['iw', 'Hebrew'],
8 => ['nl', 'Dutch'],
9 => ['pl', 'Polish'],
10 => ['pt', 'Portuguese'],
        ];

        $display = [
            0 => ['self', 'Normal IPB Page'],
1 => ['new', 'New Pop Up Window'],
        ];

        //+-------------------------------

        // START THE FORM

        //+-------------------------------

        $ADMIN->html .= $SKIN->start_form(
            [
                1 => ['code', 'dochat'],
2 => ['act', 'pin'],
            ]
        );

        //+-------------------------------

        $SKIN->td_header[] = ['&nbsp;', '60%'];

        $SKIN->td_header[] = ['&nbsp;', '40%'];

        //+-------------------------------

        $ADMIN->html .= $SKIN->start_table('Basic Configuration');

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Chat Room Account Number?</b><br>Removing this number will remove all links / chat functionality within IPB.',
                $SKIN->form_input('chat_account_no', $INFO['chat_account_no']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                "<b>Allow guests access to the chat room?</b><br>Choosing 'no' will require all chat users to log into chat.",
                $SKIN->form_yes_no('chat_allow_guest', $INFO['chat_allow_guest']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Chat Room Dimensions (WIDTH)?</b>',
                $SKIN->form_input('chat_width', $INFO['chat_width'] ?: 600),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Chat Room Dimensions (HEIGHT)?</b>',
                $SKIN->form_input('chat_height', $INFO['chat_height'] ?: 350),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Default Chat Room Interface Language?</b>',
                $SKIN->form_dropdown('chat_language', $larray, $language),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Load the chat room in...?</b>',
                $SKIN->form_dropdown('chat_display', $display, $INFO['chat_display']),
            ]
        );

        $ADMIN->html .= $SKIN->end_table();

        //+-------------------------------

        $SKIN->td_header[] = ['&nbsp;', '60%'];

        $SKIN->td_header[] = ['&nbsp;', '40%'];

        //+-------------------------------

        $ADMIN->html .= $SKIN->start_table('Admin Access Permission');

        //-------------------------------

        // Break up our line of admins

        //-------------------------------

        $allowed = [];

        foreach (explode(',', $INFO['chat_admin_groups']) as $i) {
            $allowed[$i] = 1;
        }

        $DB->query('SELECT g_id, g_title FROM ibf_groups WHERE g_id <> ' . $INFO['guest_group'] . ' ORDER BY g_title');

        while (false !== ($r = $DB->fetch_row())) {
            $mode = $r['g_id'] == $INFO['admin_group'] ? 'green' : 'red';

            $ADMIN->html .= $SKIN->add_td_row(
                [
                    "<b>Allow Group '<span style='color:$mode'>{$r['g_title']}</span>' IP Chat Admin Access?</b>",
                    $SKIN->form_yes_no("sg_{$r['g_id']}", $allowed[$r['g_id']] ? 1 : 0),
                ]
            );
        }

        $ADMIN->html .= $SKIN->end_form('Save this configuration');

        $ADMIN->html .= $SKIN->end_table();

        $ADMIN->output();
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
    }

    //-------------------------------------------------------------

    // Common header: Saves writing the same stuff out over and over

    //--------------------------------------------------------------

    public function common_header($formcode = '', $section = '', $extra = '')
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        $extra = $extra ? $extra . '<br>' : $extra;

        $ADMIN->page_detail = $extra . 'Please check the data you are entering before submitting the changes';

        $ADMIN->page_title = "Plug In Configuration [ $section ]";

        //+-------------------------------

        $ADMIN->html .= $SKIN->start_form(
            [
                1 => ['code', $formcode],
2 => ['act', 'pin'],
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
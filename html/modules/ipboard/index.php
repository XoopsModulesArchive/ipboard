<?php

/*
+--------------------------------------------------------------------------
|   Invision Power Board v1.1
|   ========================================
|   by Matthew Mecham
|   (c) 2001,2002 Invision Power Services, Inc
|   http://www.ibforums.com
|   ========================================
|   Web: http://www.ibforums.com
|   Email: phpboards@ibforums.com
|   Licence Info: phpib-licence@ibforums.com
+---------------------------------------------------------------------------
|
|   > Wrapper script
|   > Script written by Matt Mecham
|   > Date started: 14th February 2002
|
+--------------------------------------------------------------------------
*/

//-----------------------------------------------
// USER CONFIGURABLE ELEMENTS
//-----------------------------------------------

//XOOPS
include './../../mainfile.php';
//if(!file_exists("./install.lock")) header ("location: ./sm_install.php");
if (!file_exists('./install.lock')) {
    if (!file_exists('./sm_install.php')) {
        echo '<center><font color=red>Please! Copying file <b>sm_install.php</b> from <b>"ipboard/setup"</b> to <b>"ipboard"</b> directory and try again!!!</font></center>';

        exit;
    }

    header('location: ./sm_install.php');
}

// Root path
$root_path = './';

//-----------------------------------------------
// NO USER EDITABLE SECTIONS BELOW
//-----------------------------------------------

error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_magic_quotes_runtime(0);

class Debug
{
    public function startTimer()
    {
        global $starttime;

        $mtime = microtime();

        $mtime = explode(' ', $mtime);

        $mtime = $mtime[1] + $mtime[0];

        $starttime = $mtime;
    }

    public function endTimer()
    {
        global $starttime;

        $mtime = microtime();

        $mtime = explode(' ', $mtime);

        $mtime = $mtime[1] + $mtime[0];

        $endtime = $mtime;

        $totaltime = round(($endtime - $starttime), 5);

        return $totaltime;
    }
}

class info
{
    public $member = [];

    public $input = [];

    public $session_id = '';

    public $base_url = '';

    public $vars = '';

    public $skin_id = '0';     // Skin Dir name
    public $skin_rid = '';      // Real skin id (numerical only)
    public $lang_id = 'en';

    public $skin = '';

    public $lang = '';

    public $server_load = 0;

    public $version = 'v1.1.2';

    public $lastclick = '';

    public $location = '';

    public $debug_html = '';

    public function __construct()
    {
        global $sess, $std, $DB, $root_path, $INFO;

        $this->vars = &$INFO;

        $this->vars['TEAM_ICON_URL'] = $INFO['html_url'] . '/team_icons';

        $this->vars['AVATARS_URL'] = $INFO['html_url'] . '/../../../uploads';

        $this->vars['EMOTICONS_URL'] = $INFO['html_url'] . '/../../../uploads';

        $this->vars['mime_img'] = $INFO['html_url'] . '/mime_types';
    }
}

//--------------------------------
// Import $INFO, now!
//--------------------------------

require $root_path . 'conf_global.php';

//--------------------------------
// The clocks a' tickin'
//--------------------------------

$Debug = new Debug();
$Debug->startTimer();

//--------------------------------
// Require our global functions
//--------------------------------

require $root_path . 'sources/functions.php';

$std = new FUNC();
$print = new display();
$sess = new session();

//--------------------------------
// Load the DB driver and such
//--------------------------------

$INFO['sql_driver'] = !$INFO['sql_driver'] ? 'mySQL' : $INFO['sql_driver'];

$to_require = $root_path . 'sources/Drivers/' . $INFO['sql_driver'] . '.php';
require $to_require;

$DB = new db_driver();

$DB->obj['sql_database'] = $INFO['sql_database'];
$DB->obj['sql_user'] = $INFO['sql_user'];
$DB->obj['sql_pass'] = $INFO['sql_pass'];
$DB->obj['sql_host'] = $INFO['sql_host'];
$DB->obj['sql_tbl_prefix'] = $INFO['sql_tbl_prefix'];

$DB->obj['debug'] = (1 == $INFO['sql_debug']) ? $_GET['debug'] : 0;

// Get a DB connection

$DB->connect();

//--------------------------------
// Wrap it all up in a nice easy to
// transport super class
//--------------------------------

$ibforums = new info();

//--------------------------------
//  Set up our vars
//--------------------------------

$ibforums->input = $std->parse_incoming();
$ibforums->member = $sess->authorise();
$ibforums->skin = $std->load_skin();
$ibforums->lastclick = $sess->last_click;
$ibforums->location = $sess->location;
$ibforums->session_id = $sess->session_id;

[$ppu, $tpu] = explode('&', $ibforums->member['view_prefs']);

$ibforums->vars['display_max_topics'] = ($tpu > 0) ? $tpu : $ibforums->vars['display_max_topics'];
$ibforums->vars['display_max_posts'] = ($ppu > 0) ? $ppu : $ibforums->vars['display_max_posts'];

if ($ibforums->member['uid'] and ($std->my_getcookie('hide_sess'))) {
    $ibforums->session_id = '';
}

$ibforums->base_url = $ibforums->vars['board_url'] . '/index.' . $ibforums->vars['php_ext'] . '?s=' . $ibforums->session_id;

$ibforums->skin_rid = $ibforums->skin['set_id'];
$ibforums->skin_id = 's' . $ibforums->skin['set_id'];

$ibforums->vars['img_url'] = 'style_images/' . $ibforums->skin['img_dir'];

//--------------------------------
//  Set up our language choice
//--------------------------------

if ('' == $ibforums->vars['default_language']) {
    $ibforums->vars['default_language'] = 'en';
}

$ibforums->lang_id = $ibforums->member['language'] ?: $ibforums->vars['default_language'];

if (($ibforums->lang_id != $ibforums->vars['default_language']) and (!is_dir($root_path . 'lang/' . $ibforums->lang_id))) {
    $ibforums->lang_id = $ibforums->vars['default_language'];
}

$ibforums->lang = $std->load_words($ibforums->lang, 'lang_global', $ibforums->lang_id);

//--------------------------------

$skin_universal = $std->load_template('skin_global');

//--------------------------------

if ('Login' != $ibforums->input['act'] and 'Reg' != $ibforums->input['act'] and 'Attach' != $ibforums->input['act']) {
    //--------------------------------

    //  Do we have permission to view

    //  the board?

    //--------------------------------

    if (1 != $ibforums->member['g_view_board']) {
        $std->Error(['LEVEL' => 1, 'MSG' => 'no_view_board']);
    }

    //--------------------------------

    //  Is the board offline?

    //--------------------------------

    if (1 == $ibforums->vars['board_offline']) {
        if (1 != $ibforums->member['g_access_offline']) {
            $std->board_offline();
        }
    }

    //--------------------------------

    //  Is log in enforced?

    //--------------------------------

    if ((!$ibforums->member['uid']) and (1 == $ibforums->vars['force_login'])) {
        require $root_path . 'sources/Login.php';
    }
}

//--------------------------------
// Decide what to do
//--------------------------------

$choice = [
    'idx' => 'Boards',
'SF' => 'Forums',
'SR' => 'Forums',
'ST' => 'Topics',
'Login' => 'Login',
'Post' => 'Post',
'Poll' => 'lib/add_poll',
'Reg' => 'Register',
'Online' => 'Online',
'Members' => 'Memberlist',
'Help' => 'Help',
'Search' => 'Search',
'Mod' => 'Moderate',
'Print' => 'misc/print_page',
'Forward' => 'misc/forward_page',
'Mail' => 'misc/contact_member',
'Invite' => 'misc/contact_member',
'ICQ' => 'misc/contact_member',
'AOL' => 'misc/contact_member',
'YAHOO' => 'misc/contact_member',
'MSN' => 'misc/contact_member',
'report' => 'misc/contact_member',
'chat' => 'misc/contact_member',
'Msg' => 'Messenger',
'UserCP' => 'Usercp',
'Profile' => 'Profile',
'Track' => 'misc/tracker',
'Stats' => 'misc/stats',
'Attach' => 'misc/attach',
'ib3' => 'misc/ib3',
'legends' => 'misc/legends',
'modcp' => 'mod_cp',
'calendar' => 'calendar',
'buddy' => 'browsebuddy',
];

/***************************************************/

$ibforums->input['act'] = '' == $ibforums->input['act'] ? 'idx' : $ibforums->input['act'];

// Check to make sure the array key exits..

if (!isset($choice[$ibforums->input['act']])) {
    $ibforums->input['act'] = 'idx';
}

// Require and run

require $root_path . 'sources/' . $choice[$ibforums->input['act']] . '.php';

//+-------------------------------------------------
// GLOBAL ROUTINES
//+-------------------------------------------------

function fatal_error($message = '', $help = '')
{
    echo("$message<br><br>$help");

    exit;
}

<?php

/*
+--------------------------------------------------------------------------
|   IBFORUMS v1
|   ========================================
|   by Matthew Mecham and David Baxter
|   (c) 2001,2002 IBForums
|   http://www.ibforums.com
|   ========================================
|   Web: http://www.ibforums.com
|   Email: phpboards@ibforums.com
|   Licence Info: phpib-licence@ibforums.com
+---------------------------------------------------------------------------
|
|   > SSI script
|   > Script written by Matt Mecham
|   > Date started: 29th April 2002
|
+--------------------------------------------------------------------------
*/

/* USAGE:
   ------

   Simply call this script via PHP includes, or SSI .shtml tags to generate content
   on the fly, streamed into your own webpage.

   To show the last 10 topics and posts in the news forums...

   include "http://domain.com/forums/ssi.php?a=news&show=10";

   You can adjust the "show" attribute to display a different amount of topics.

   To show the board statistics

   include "http://domain.com/forums/ssi.php?a=stats";

   To show the active users stats (x Members, X Guests, etc)

   include "http://domain.com/forums/ssi.php?a=active";

*/

//-----------------------------------------------
// USER CONFIGURABLE ELEMENTS
//-----------------------------------------------

// Root path

$root_path = './';

$templates_dir = './ssi_templates';

//-----------------------------------------------
// NO USER EDITABLE SECTIONS BELOW
//-----------------------------------------------

error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_magic_quotes_runtime(0);

class info
{
    public $input = [];

    public $base_url = '';

    public $vars = '';

    public function __construct()
    {
        global $sess, $std, $DB, $root_path, $INFO;

        $this->vars = &$INFO;
    }
}

//--------------------------------
// Import $INFO, now!
//--------------------------------

require $root_path . 'conf_global.php';

//--------------------------------
// Require our global functions
//--------------------------------

require $root_path . 'sources/functions.php';

$std = new FUNC();

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
$ibforums->base_url = $ibforums->vars['board_url'] . '/index.' . $ibforums->vars['php_ext'];

//--------------------------------
// What to do?
//--------------------------------

switch ($ibforums->input['a']) {
    case 'news':
        do_news();
        break;
    case 'active':
        do_active();
        break;
    case 'stats':
        do_stats();
        break;
    default:
        echo('An error occured whilst processing this directive');
        exit();
        break;
}

//+-------------------------------------------------
// Import the stats! WOOHOO
//+-------------------------------------------------

function do_stats()
{
    global $DB, $ibforums, $root_path, $templates_dir, $std;

    // Load the template...

    $template = load_template('stats.html');

    $to_echo = '';

    // Get the topics, member info and other stuff

    $time = time() - 900;

    $DB->query('SELECT * FROM ibf_stats');

    $stats = $DB->fetch_row();

    $total_posts = $stats['TOTAL_REPLIES'] + $stats['TOTAL_TOPICS'];

    $to_echo = parse_template(
        $template,
        [
            'total_posts' => $total_posts,
'topics' => $stats['TOTAL_TOPICS'],
'replies' => $stats['TOTAL_REPLIES'],
'members' => $stats['MEM_COUNT'],
        ]
    );

    echo $to_echo;

    exit();
}

function do_news()
{
    global $DB, $ibforums, $root_path, $templates_dir, $std;

    if ((!$ibforums->vars['news_forum_id']) or ('' == $ibforums->vars['news_forum_id'])) {
        fatal_error('No news forum assigned');
    }

    $perpage = $ibforums->input['show'] ?: 10;

    // Load the template...

    $template = load_template('news.html');

    $to_echo = '';

    // Get the topics, member info and other stuff

    $DB->query(
        'SELECT m.uname as member_name, m.uid as member_id, m.title as member_title, m.avatar, m.avatar_size, m.posts, t.*, p.* FROM xbb_members m, ibf_posts p, ibf_topics t '
        . "WHERE t.forum_id = '"
        . $ibforums->vars['news_forum_id']
        . "' AND p.topic_id=t.tid AND p.new_topic=1 AND m.uid=t.starter_id "
        . "AND t.approved=1 ORDER BY t.tid DESC LIMIT 0, $perpage"
    );

    if (!$DB->get_num_rows()) {
        fatal_error('Could not get the information from the database');
    }

    while (false !== ($row = $DB->fetch_row())) {
        $to_echo .= parse_template(
            $template,
            [
                'profile_link' => $ibforums->base_url . '?act=Profile&CODE=03&MID=' . $row['member_id'],
'member_name' => $row['member_name'],
'post_date' => $std->get_date($row['start_date'], 'LONG'),
'topic_title' => $row['title'],
'post' => $row['post'],
'comments' => $row['posts'],
'view_all_link' => $ibforums->base_url . "?act=ST&f={$row['forum_id']}&t={$row['tid']}",
            ]
        );
    }

    echo $to_echo;

    exit();
}

function do_active()
{
    global $DB, $ibforums, $root_path, $templates_dir, $std;

    // Load the template...

    $template = load_template('active.html');

    $to_echo = '';

    // Get the topics, member info and other stuff

    $time = time() - 900;

    $DB->query("SELECT s.member_id, s.member_name, s.login_type, g.suffix, g.prefix FROM ibf_sessions s, ibf_groups g WHERE running_time > '$time' AND g.g_id=s.member_group ORDER BY running_time DESC");

    // cache all printed members so we don't double print them

    $cached = [];

    $active = [];

    while (false !== ($result = $DB->fetch_row())) {
        if (0 == $result['member_id']) {
            $active['GUESTS']++;
        } else {
            if (empty($cached[$result['member_id']])) {
                $cached[$result['member_id']] = 1;

                if (1 == $result['login_type']) {
                    $active['ANON']++;
                } else {
                    $active['MEMBERS']++;
                }
            }
        }
    }

    $active['TOTAL'] = $active['MEMBERS'] + $active['GUESTS'] + $active['ANON'];

    $to_echo = parse_template(
        $template,
        [
            'total' => $active['TOTAL'] ?: 0,
            'members' => $active['MEMBERS'] ?: 0,
            'guests' => $active['GUESTS'] ?: 0,
            'anon' => $active['ANON'] ?: 0,
        ]
    );

    echo $to_echo;

    exit();
}

//+-------------------------------------------------
// GLOBAL ROUTINES
//+-------------------------------------------------

function parse_template($template, $assigned = [])
{
    foreach ($assigned as $word => $replace) {
        $template = preg_replace("/\{$word\}/i", (string)$replace, $template);
    }

    return $template;
}

function load_template($template = '')
{
    global $templates_dir;

    $filename = $templates_dir . '/' . $template;

    if (file_exists($filename)) {
        if ($FH = fopen($filename, 'rb')) {
            $template = fread($FH, filesize($filename));

            fclose($FH);
        } else {
            fatal_error("Couldn't open the template file");
        }
    } else {
        fatal_error('Template file does not exist');
    }

    return $template;
}

function fatal_error($message = '')
{
    echo('An error occured whilst processing this directive');

    if ($message) {
        echo("<br>$message");
    }

    exit();
}

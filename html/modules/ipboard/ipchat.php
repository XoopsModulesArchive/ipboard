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
|   > IP Chat => IPB Bridge Script
|   > Script written by Matt Mecham
|   > Date started: 17th February 2003
|
+--------------------------------------------------------------------------
*/

//----------------------------------------------
// END OF USER EDITABLE COMPONENTS
//---------------------------------------------

define('ROOT_PATH', './');
require ROOT_PATH . 'conf_global.php';

define('DENIED', 0);
define('ACCESS', 1);
define('ADMIN', 2);

$db_info['host'] = $INFO['sql_host'];
$db_info['user'] = $INFO['sql_user'];
$db_info['pass'] = $INFO['sql_pass'];
$db_info['database'] = $INFO['sql_database'];
$db_info['tbl_prefix'] = $INFO['sql_tbl_prefix'];

$allowed_groups = $INFO['chat_admin_groups'];

$allow_guest_access = 1 == $INFO['chat_allow_guest'] ? ACCESS : DENIED;

// Stupid PHP changing it's mind on HTTP args

$username = '' != $_GET['username'] ? $_GET['username'] : $_GET['username'];
$password = '' != $_GET['password'] ? $_GET['password'] : $_GET['password'];
$ip = '' != $_GET['ip'] ? $_GET['ip'] : $_GET['ip'];

// Remove URL encoding (%20, etc)

$username = urldecode(trim($username));
$password = urldecode(trim($password));
$ip = urldecode(trim($ip));

//----------------------------------------------
// Main code
//----------------------------------------------

// Start off with the lowest accessibility

$output_int = $allow_guest_access;
$output_name = '';

$DB = @mysql_connect($db_info['host'], $db_info['user'], $db_info['pass']);

if (!@mysqli_select_db($GLOBALS['xoopsDB']->conn, $db_info['database'])) {
    die_nice();

    //-- script exits --//
}

//------------------------------
// Attempt to find the user
//------------------------------

$md5_password = md5($password);

$query_id = @$GLOBALS['xoopsDB']->queryF(
    "SELECT m.mgroup, m.pass, m.uname, m.uid  FROM {$db_info['tbl_prefix']}users m
						  WHERE m.uname='$username' LIMIT 1",
    $DB
);

if (!$query_id) {
    die_nice();

    //-- script exits --//
}

if (!$member = @$GLOBALS['xoopsDB']->fetchBoth($query_id, MYSQL_ASSOC)) {
    // No member found - allow guest access?

    die_nice($allow_guest_access);

    //-- script exits --//
}

@$GLOBALS['xoopsDB']->close();

//------------------------------
// Check password - member exists
//------------------------------

if ('' != $password) {
    // Password was entered..

    if ($md5_password != $member['pass']) {
        // Password incorrect..

        die_nice();

        //-- script exits --//
    }
} else {
    // No password entered - die!

    // Do not allow guest access on reg. name

    die_nice();

    //-- script exits --//
}

//------------------------------
// Do we have admin access?
//------------------------------

$output_int = ACCESS;

if (preg_match('/(^|,)' . $member['mgroup'] . '(,|$)/', $allowed_groups)) {
    $output_int = ADMIN;
}

//------------------------------
// Spill the beans
//------------------------------

print $output_int;

exit();

function die_nice($access = 0)
{
    // Simply error out silently, showing guest access only for the user

    @$GLOBALS['xoopsDB']->close();

    print $access;

    exit();
}

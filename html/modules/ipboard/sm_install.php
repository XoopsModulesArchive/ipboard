<?php

/*
+--------------------------------------------------------------------------
|   INVISION POWER BOARD SAFE MODE INSTALL SCRIPT v1.1
|   ========================================
|   by Matthew Mecham and David Baxter
|   (c) 2001, 2002 IBForums
|   http://www.ibforums.com
|   ========================================
|   Web: http://www.ibforums.com
|   Email: phpboards@ibforums.com
|   Licence Info: phpib-licence@ibforums.com
+---------------------------------------------------------------------------
|
|   > Script written by Matthew Mecham
|   > Date started: 30th March 2002
|   > Update started: 17th October 2002
|
+--------------------------------------------------------------------------
*/

error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_magic_quotes_runtime(0);

//+---------------------------------------
// ENTER YOUR PATH TO THE DIRECTORY THIS SCRIPT
// IS IN.
//
// Tips:
//
// If you are using Windows and Apache, do not
// use backslashes, use normal forward slashes.
// You may need to remove the drive letter also
// Example: C:\apache\htdocs\ibforums\ will need
// to be: /apache/htdocs/ibforums/
//
// If you are using Windows and IIS, then you will
// need to enter double backslashes.
//
// In all cases, please enter a trailing slash (or
// trailing backslashes...)
//+---------------------------------------

include './../../mainfile.php';
$root = './';

//+---------------------------------------

$template = new template();
$std = new installer();

$VARS = $std->parse_incoming();

if (isset($_REQUEST['mode'])) {
    $mode = (int)$_REQUEST['mode'];
} else {
    $mode = 0;
}

//+---------------------------------------
// What are we doing then? Eh? I'm talking to you!
//+---------------------------------------

if (file_exists($root . 'install.lock')) {
    install_error("This installer is locked!<br>Please (via FTP) remove the 'install.lock' file in this directory");

    exit();
}

switch ($VARS['a']) {
    case '1':
        do_setup_form();
        break;
    case '2':
        do_install();
        break;
    case 'templates':
        do_templates();
        break;
    case '3':
        do_finish();
        break;
    default:
        do_intro();
        break;
}

function do_finish()
{
    global $std, $template, $root, $VARS, $SQL;

    // Attempt to lock the install..

    $file = $root . 'sm_install.php';

    if (file_exists($file)) {
        unlink($file);
    }

    if ($FH = @fopen($root . 'install.lock', 'wb')) {
        @fwrite($FH, 'bleh', 4);

        @fclose($FH);

        @chmod($root . 'install.lock', 0666);

        $template->print_top('Success!');

        if (file_exists($file)) {
            $template->contents .= "<tr>
								  <td id='subtitle'>&#149;&nbsp;Success!</td>
								<tr>
								<td>
								  <table cellpadding='8' cellspacing='0' width='100%' align='center' border='0' id='tablewrap'>
								  <tr>
									<td>
								  <table width='100%' cellspacing='1' cellpadding='0' align='center' border='0' id='table1'>
								   <tr>
									<td>
									 <table width='100%' cellspacing='2' cellpadding='3' align='center' border='0'>
									 <tr>
									   <td>
											<b>The installation is now complete!</b>
											<br><br>
											Although the installer is now locked (to re-install, remove the file 'install.lock'), for added
											security, please remove the 'sm_install.php' program before continuing.
											<br>
											Please CHMOD <font color=blue> conf_global.php</font> --> <font color=red>644 </font>for security.
											<br><br>
											<center><b><a href='index.php'>CLICK HERE TO LOG IN!</a></center>
										</td>
									 </tr>
									</table>
								  </td>
								 </tr>
								</table>
							   </td>
							  </tr>
							 </table>";
        } else {
            $template->contents .= "<tr>
								  <td id='subtitle'>&#149;&nbsp;Success!</td>
								<tr>
								<td>
								  <table cellpadding='8' cellspacing='0' width='100%' align='center' border='0' id='tablewrap'>
								  <tr>
									<td>
								  <table width='100%' cellspacing='1' cellpadding='0' align='center' border='0' id='table1'>
								   <tr>
									<td>
									 <table width='100%' cellspacing='2' cellpadding='3' align='center' border='0'>
									 <tr>
									   <td>
											<b>The installation is now complete!</b>											
											<br>
											Please CHMOD <font color=blue> conf_global.php</font> --> <font color=red>644 </font>for security.
											<br><br>
											<center><b><a href='index.php'>CLICK HERE TO LOG IN!</a></center>
										</td>
									 </tr>
									</table>
								  </td>
								 </tr>
								</table>
							   </td>
							  </tr>
							 </table>";
        }
    } else {
        $template->print_top('Success!');

        $template->contents .= "<tr>
							  <td id='warning'>&#149;&nbsp;WARNING!</td>
							<tr>
							<td>
							  <table cellpadding='8' cellspacing='0' width='100%' align='center' border='0' id='tablewrap'>
							  <tr>
								<td>
							  <table width='100%' cellspacing='1' cellpadding='0' align='center' border='0' id='table1'>
							   <tr>
								<td>
								 <table width='100%' cellspacing='2' cellpadding='3' align='center' border='0'>
								 <tr>
								   <td>
										<b>The installation is now complete!</b>
										<br><br>
										PLEASE REMOVE THE INSTALLER ('sm_install.php') BEFORE CONTINUING!
										<br>
										Failure to do so will enable ANYONE to delete your board at any time!
										<br><br>
										<center><b><a href='index.php'>CLICK HERE TO LOG IN!</a></center>
									</td>
								 </tr>
								</table>
							  </td>
							 </tr>
							</table>
						   </td>
						  </tr>
						 </table>";
    }

    $template->output();
}

//+---------------------------------------
// Install the template files, woohoo and stuff
//+---------------------------------------

function do_templates()
{
    global $std, $template, $root, $VARS, $_POST;

    //-----------------------------------

    // IMPORT $INFO!

    //-----------------------------------

    if ('./' == $root) {
        $root = str_replace('\\', '/', getcwd()) . '/';
    }

    $require = $root . 'conf_global.php';

    if (!file_exists($require)) {
        install_error("Could not locate '$require'. You may need to enter a value for the root path in this installer script, to do this, simply open up this script in a text editor and enter a value in \$root - remember to add a trailing slash. NT users will need to use double backslashes");
    }

    include $require;

    //-----------------------------------

    // Attempt a DB connection..

    //-----------------------------------

    if (!$connect_id = mysql_connect($INFO['sql_host'], $INFO['sql_user'], $INFO['sql_pass'])) {
        install_error("Could not create a mySQL connection, please check that the file 'conf_global.php' exists in the same directory as this file and that the installer has updated the file properly.");
    }

    if (!mysqli_select_db($GLOBALS['xoopsDB']->conn, $INFO['sql_database'], $connect_id)) {
        install_error("mySQL could not locate a database called '{$VARS['sql_database']}'. Please contact our technical support if a re-install does not solve this problem");
    }

    //-----------------------------------

    // Lets open the style file

    //-----------------------------------

    $style_file = $root . 'install_templates.txt';

    if (!file_exists($style_file)) {
        install_error(
            "Could not locate '$style_file'. <br><br>Check to ensure that this file exists in the same location as this script.<br><br>You may need to enter a value for the root path in this installer script, to do this, simply open up this script in a text editor and enter a value in \$root - remember to add a trailing slash. NT users will need to use double backslashes"
        );
    }

    if ($fh = fopen($style_file, 'rb')) {
        $data = fread($fh, filesize($style_file));

        fclose($fh);
    } else {
        install_error("Could open '$style_file'");
    }

    if (mb_strlen($data) < 100) {
        install_error("Err 1:'$style_file' is incomplete, please re-upload a fresh copy over the existing copy on the server'");
    }

    // Chop up the data file.

    $template_rows = explode('||~&~||', $data);

    $crows = count($template_rows);

    if ($crows < 100) {
        install_error("Err2: (Found $crows rows) '$style_file' is incomplete, please re-upload a fresh copy over the existing copy on the server'");
    }

    //-----------------------------------

    // Lets populate the database!

    //-----------------------------------

    foreach ($template_rows as $q) {
        $q = trim($q);

        if (mb_strlen($q) < 5) {
            continue;
        }

        $query = 'INSERT INTO ' . $INFO['sql_tbl_prefix'] . "ipb_skin_templates (set_id, group_name, section_content, func_name, func_data, updated, can_remove) VALUES $q";

        if (!$GLOBALS['xoopsDB']->queryF($query, $connect_id)) {
            install_error('mySQL Error: ' . $GLOBALS['xoopsDB']->error());
        }
    }

    // ARE WE DONE? REALLY? COOL!!

    $template->print_top('Success!');

    $template->contents .= "<tr>
							 <td id='subtitle'>&#149;&nbsp;Success!</td>
						   <tr>
						   <td>
							 <table cellpadding='8' cellspacing='0' width='100%' align='center' border='0' id='tablewrap'>
							 <tr>
							   <td>
							 <table width='100%' cellspacing='1' cellpadding='0' align='center' border='0' id='table1'>
							  <tr>
							   <td>
								<table width='100%' cellspacing='2' cellpadding='3' align='center' border='0'>
								<tr>
								  <td>
									   <b>Template files installed!</b>
									   <br><br>
									   The installation process is now complete.
									   <br>
									   Click the link below to clean up the installer and related files
									   <br><br>
									   <center><b><a href='sm_install.php?a=3'>CLICK HERE TO FINISH</a></center>
								   </td>
								</tr>
							   </table>
							 </td>
							</tr>
						   </table>
						  </td>
						 </tr>
						</table>";

    $template->output();
}

//+---------------------------------------

function do_install()
{
    global $std, $template, $root, $VARS, $_POST, $mode, $re_inst;

    // Ok, lets check for blankies...

    $NEW_INFO = [];

    $need = ['board_url', 'sql_host', 'sql_database', 'sql_user', 'adminname', 'adminpassword', 'adminpassword2', 'email'];

    //-----------------------------------

    /*
        foreach($need as $greed)
        {
            if ($VARS[ $greed ] == "")
            {
                install_error("You must complete all of the form with the sole exception of 'SQL Table prefix'");
            }
        }
    */

    //-----------------------------------

    $VARS['board_url'] = preg_replace('#$#', '', $VARS['board_url']);

    if ('' == $VARS['sql_tbl_prefix']) {
        $VARS['sql_tbl_prefix'] = 'ibf_';
    }

    //-----------------------------------

    // Did the admin passy and passy2 match?

    //-----------------------------------

    if ($VARS['adminpassword2'] != $VARS['adminpassword']) {
        install_error('Your passwords did not match');
    }

    /*if ( ! preg_match( "!^http://!", $VARS['board_url'] ) )
    {
        install_error("The board URL must start with 'http://'");
    }*/

    //-----------------------------------

    // IMPORT $INFO!

    //-----------------------------------

    if ('./' == $root) {
        $root = str_replace('\\', '/', getcwd()) . '/';
    }

    $require = $root . 'conf_global.php';

    if (!file_exists($require)) {
        install_error("Could not locate '$require'. You may need to enter a value for the root path in this installer script, to do this, simply open up this script in a text editor and enter a value in \$root - remember to add a trailing slash. NT users will need to use double backslashes");
    }

    //@chmod( "conf_global.php", 0666 );

    include $require;

    //echo("here");

    //exit();

    if (count($INFO) < 1) {
        install_error("Possible corruption in 'conf_global.php' ({$VARS['base_dir']}conf_global.php), please re-upload in ASCII format");
    }

    //-----------------------------------

    // Attempt a DB connection..

    //-----------------------------------

    if (!$connect_id = mysql_connect($VARS['sql_host'], $_POST['sql_user'], $_POST['sql_pass'])) {
        install_error('Could not create a mySQL connection, please check the SQL values entered');
    }

    if (!mysqli_select_db($GLOBALS['xoopsDB']->conn, $VARS['sql_database'], $connect_id)) {
        install_error("mySQL could not locate a database called '{$VARS['sql_database']}' please check the value entered for this");
    }

    //-----------------------------------

    // Attempt to write the config file.

    //-----------------------------------

    $new = [
        'base_dir' => $root,
'board_url' => $VARS['board_url'],
'sql_host' => $VARS['sql_host'],
'sql_database' => $VARS['sql_database'],
'sql_user' => $_POST['sql_user'],
'sql_pass' => $_POST['sql_pass'],
'sql_tbl_prefix' => $VARS['sql_tbl_prefix'],

        'html_dir' => $root . 'html/',
'html_url' => $VARS['board_url'] . '/html',
'upload_dir' => $root . 'uploads',
'upload_url' => $VARS['board_url'] . '/uploads',
'email_in' => $VARS['email'],
'email_out' => $VARS['email'],
'ban_names' => '',
'ban_email' => '',
'ban_ip' => '',
'force_login' => 0,
'load_limit' => '',
'board_start' => time(),
'installed' => 1,
'guests_ava' => 1,
'guests_img' => 1,
'guests_sig' => 1,
'disable_gzip' => 1,
'safe_mode_skins' => 1,
'print_headers' => 0,
'guest_name_pre' => 'Guest_',
    ];

    foreach ($new as $k => $v) {
        // Update the old...

        $v = preg_replace("/'/", "\\'", $v);

        $v = preg_replace("/\r/", '', $v);

        $INFO[$k] = $v;
    }

    $file_string = "<?php\n";

    foreach ($INFO as $k => $v) {
        if ('skin' == $k or 'languages' == $k) {
            continue;
        }

        $file_string .= '$INFO[' . "'" . $k . "'" . ']' . "\t\t\t=\t'" . $v . "';\n";
    }

    $file_string .= "\n" . '?' . '>';   // Question mark + greater than together break syntax hi-lighting in BBEdit 6 :p

    if ($fh = fopen($require, 'wb')) {
        fwrite($fh, $file_string, mb_strlen($file_string));

        fclose($fh);
    } else {
        install_error("Could not write to 'conf_global.php'");
    }

    //-----------------------------------

    // Lets populate the database!

    //-----------------------------------

    $sql_reinst = 'SELECT uid from ' . $VARS['sql_tbl_prefix'] . "users WHERE uid = '1' AND mgroup='4'";

    if ($GLOBALS['xoopsDB']->queryF($sql_reinst, $connect_id)) {
        $re_inst = 0;
    } else {
        $re_inst = 1;
    }

    if (1 == $mode) {
        $SQL = get_sql_inst();
    }

    if (2 == $mode) {
        $SQL = get_sql_upg();
    }

    foreach ($SQL as $q) {
        if ('ibf_' != $VARS['sql_tbl_prefix']) {
            $q = preg_replace("/ibf_(\S+?)([\s\.,]|$)/", $VARS['sql_tbl_prefix'] . 'ipb_\\1\\2', $q);

            $q = preg_replace("/xbb_(\S+?)([\s\.,]|$)/", $VARS['sql_tbl_prefix'] . '\\1\\2', $q);
        }

        $q = str_replace('<%time%>', time(), $q);

        if (preg_match("/CREATE TABLE (\S+) \(/", $q, $match)) {
            if ($match[1]) {
                $the_query = 'DROP TABLE if exists ' . $match[1];

                if (!$GLOBALS['xoopsDB']->queryF($the_query, $connect_id)) {
                    install_error('mySQL Error: ' . $GLOBALS['xoopsDB']->error());
                }
            }
        }

        if (!$GLOBALS['xoopsDB']->queryF($q, $connect_id)) {
            install_error('mySQL Error: ' . $GLOBALS['xoopsDB']->error());
        }
    }

    // Insert the admin...

    $time = time();

    $query = 'UPDATE ' . $VARS['sql_tbl_prefix'] . "users SET user_avatar='blank.gif', attachsig='1', mgroup='4', ip_address='127.0.0.1',title='Administrator',last_visit='$time', last_activity='$time' WHERE uid='1'";

    if (!$GLOBALS['xoopsDB']->queryF($query, $connect_id)) {
        install_error('mySQL Error: ' . $GLOBALS['xoopsDB']->error());
    }

    // ARE WE DONE? REALLY? COOL!!

    $template->print_top('Success!');

    $template->contents .= "<tr>
							  <td id='subtitle'>&#149;&nbsp;Success!</td>
							<tr>
							<td>
							  <table cellpadding='8' cellspacing='0' width='100%' align='center' border='0' id='tablewrap'>
							  <tr>
								<td>
							  <table width='100%' cellspacing='1' cellpadding='0' align='center' border='0' id='table1'>
							   <tr>
								<td>
								 <table width='100%' cellspacing='2' cellpadding='3' align='center' border='0'>
								 <tr>
								   <td>
								   		<b>Your board has now been installed!</b>
								   		<br><br>
								   		The installation process is almost complete.
								   		<br>
								   		The next and final step will install the template files into your database
								   		<br><br>
								   		<center><b><a href='sm_install.php?a=templates'>CLICK HERE TO CONTINUE</a></center>
								   	</td>
								 </tr>
								</table>
							  </td>
							 </tr>
							</table>
						   </td>
						  </tr>
						 </table>";

    $template->output();
}

function do_setup_form()
{
    global $std, $template, $root, $HTTP_SERVER_VARS, $mode;

    $template->print_top('Set Up form');

    //--------------------------------------------------

    $this_url = str_replace('/sm_install.php', '', $HTTP_SERVER_VARS['HTTP_REFERER']);

    $this_url = str_replace('?mode=0', '', $this_url);

    if (!$this_url) {
        $this_url = mb_substr($HTTP_SERVER_VARS['SCRIPT_NAME'], 0, -15);

        if ('' == $this_url) {
            '/' == $this_url;
        }

        $this_url = 'http://' . $HTTP_SERVER_VARS['SERVER_NAME'] . $this_url;
    }

    //--------------------------------------------------

    $template->contents .= "<tr>
							  <td id='subtitle'>&#149;&nbsp;Your Server Environment</td>
							<tr>
							<td>
							  <form action='sm_install.php?mode=$mode' method='POST'>
							  <input type='hidden' name='a' value='2'>
							  <table cellpadding='8' cellspacing='0' width='100%' align='center' border='0' id='tablewrap'>
							  <tr>
								<td>
							  <table width='100%' cellspacing='1' cellpadding='0' align='center' border='0' id='table1'>
							   <tr>
								<td>
								 <table width='100%' cellspacing='2' cellpadding='3' align='center' border='0'>
								 <tr>
								   <td colspan='2' id='tdrow1'>
								   		This section requires you to enter the paths and URL's for the board. If in doubt, please
								   		check with your webhost before asking for support.
								   	</td>
								 </tr>								 
								  <tr>
								   <td width='40%' id='tdrow1'><b>The script URL</b><br>This is the URL (must start with http://) to the directory that this script is in</td>
								   <td width='60%' id='tdrow2'><input type='text' id='textinput' name='board_url' value='$this_url'></td>
								  </tr>								 
								 </table>
								</td>
							   </tr>
							  </table>
						   </td>
						  </tr>
						 </table>						 
						 </td>
						 </tr>
						 <tr>
								   <input type='hidden' name='sql_host' value=" . XOOPS_DB_HOST . ">														 								 
								   <input type='hidden' name='sql_database' value=" . XOOPS_DB_NAME . ">
								   <input type='hidden' name='sql_user' value=" . XOOPS_DB_USER . ">
								   <input type='hidden' name='sql_pass' value=" . XOOPS_DB_PASS . ">
								   <input type='hidden' name='sql_tbl_prefix' value=" . XOOPS_DB_PREFIX . "_>
						  </tr>
 
								 <tr>
								 	<td colspan='2' id='tdrow1' align='center'><input type='submit' value='Process'></td>
								 </tr>

								</table>
							  </td>
							 </tr>
							</table>
						   </td>
						  </tr>
						 </table>
						 </form>
						 </td>
						 </tr>
						 
						 ";

    $template->output();
}

//+---------------------------------------

function do_intro()
{
    global $std, $template, $root, $mode;

    $phpversion = (int)(str_replace('.', '', phpversion()));

    $template->print_top('Welcome');

    $template->contents .= "<tr>
							  <td id='subtitle'>&#149;&nbsp;Welcome!</td>
							<tr>
							<td>
							  <table cellpadding='8' cellspacing='0' width='100%' align='center' border='0' id='tablewrap'>
							  <tr>
								<td>
							  <table width='100%' cellspacing='1' cellpadding='0' align='center' border='0' id='table1'>
							   <tr>
								<td>
								 <table width='100%' cellspacing='2' cellpadding='3' align='center' border='0'>
								 <tr>
								   <td>
								   		<b>Welcome to the Invision Board Safe Mode Installer</b>
								   		<br><br>
								   		Before we go any further, please ensure that all the files have been uploaded!<p>
								   		File &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <font color = blue>conf_global.php </font>--> <font color = red>0777 </font><br>
								   		Directory: <font color = blue>ipboard </font>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;--> <font color = red>0777 </font><br>
								   		Directory: <font color = blue>uploads </font>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;--> <font color = red>0777 </font>
								   		<br><br>
								   		<form type='post' action='sm_install.php'>
								   		   <select name='mode' >
										    <option value='0' >Please choose the type of installation:</option>
										    <option value='1' >New installation</option>
										    <option value='2' >Update from IBF 1.01</option>
										   </select>
										   <input type=\"submit\" value=\"Proceed\" >										   
											</form>
								   		Once you have clicked on proceed, you will be taken to a form to enter information the installer needs to set up your board.
								   		<br><br>
								   		<b><U>PLEASE NOTE:</U><BR> --> PLEASE <font color=red>BACKUP</font> YOUR XOOPS DATABASE BEFORE CONTINUING THIS INSTALLER.</b><br>
								   		<B>--> As always, make a backup before messing with anything. All code
										 release by me is considered sample code only. It may be fully
										 functual, but you use it at your own risk, if you break it,
										 you get to fix it too. No waranty is given or implied. </B>
								   	</td>
								 </tr>
								</table>
							  </td>
							 </tr>
							</table>
						   </td>
						  </tr>
						 </table>";

    // Check to make sure that the config file is there and it's got suitable permissions to write to:

    if (1 == $mode or 2 == $mode) {
        header("Location: sm_install.php?a=1&mode=$mode");

        exit();
    }

    $file = $root . 'conf_global.php';

    $style_file = $root . 'install_templates.txt';

    $warnings = [];

    if (!file_exists($style_file)) {
        $warnings[] = "Cannot locate the file 'install_templates.txt'. This should be uploaded into the same directory as this script!";
    }

    if (!file_exists($file)) {
        $warnings[] = "Cannot locate the file 'conf_global.php'. This should be uploaded into the same directory as this script!";
    }

    if (!is_writable($file)) {
        $warnings[] = "Cannot write to 'conf_global.php'. Please adjust the permissions to allow this script to write to the file. if in doubt, CHMOD via FTP to 0777";
    }

    $phpversion = phpversion();

    if ($phpversion < '4.0.0') {
        $warnings[] = 'You cannot install Invision Power Board. Invision Power Board requires PHP Version 4.0.0 or better.';
    }

    if (count($warnings) > 0) {
        $err_string = '<ul><li>' . implode('<li>', $warnings) . '</ul>';

        $template->contents .= "<tr>
							  <td id='warning'>&#149;&nbsp;WARNING!</td>
							<tr>
							<td>
							  <table cellpadding='8' cellspacing='0' width='100%' align='center' border='0' id='tablewrap'>
							  <tr>
								<td>
							  <table width='100%' cellspacing='1' cellpadding='0' align='center' border='0' id='table1'>
							   <tr>
								<td>
								 <table width='100%' cellspacing='2' cellpadding='3' align='center' border='0'>
								 <tr>
								   <td>
								   		<b>The following errors must be rectified before continuing!</b>
								   		<br><br>
								   		$err_string
								   	</td>
								 </tr>
								</table>
							  </td>
							 </tr>
							</table>
						   </td>
						  </tr>
						 </table>";
    }

    $template->output();
}

function install_error($msg = '')
{
    global $std, $template, $root;

    $template->print_top('Warning!');

    $template->contents .= "<tr>
						  <td id='warning'>&#149;&nbsp;WARNING!</td>
						<tr>
						<td>
						  <table cellpadding='8' cellspacing='0' width='100%' align='center' border='0' id='tablewrap'>
						  <tr>
							<td>
						  <table width='100%' cellspacing='1' cellpadding='0' align='center' border='0' id='table1'>
						   <tr>
							<td>
							 <table width='100%' cellspacing='2' cellpadding='3' align='center' border='0'>
							 <tr>
							   <td>
									<b>The following errors must be rectified before continuing!</b><br>Please go back and try again!
									<br><br>
									$msg
								</td>
							 </tr>
							</table>
						  </td>
						 </tr>
						</table>
					   </td>
					  </tr>
					 </table>";

    $template->output();
}

//+--------------------------------------------------------------------------
// CLASSES
//+--------------------------------------------------------------------------

class template
{
    public $contents = '';

    public function output()
    {
        echo $this->contents;

        echo "   
				 </table>
				 <br><br><center><span id='copy'>Integrated into XOOPS by <a href ='http://Koudanshi.net'>Koudanshi</a> &copy 2003 <br> &copy 2003 Invision Power Board (www.invisionboard.com)</span></center>
				 
				 </body>
				 </html>";

        exit();
    }

    //--------------------------------------

    public function print_top($title = '')
    {
        $this->contents = "<html>
		          <head><title>Invision Power Board Set Up :: $title </title>
		          <style type='text/css'>
		          	TABLE, TR, TD     { font-family:Verdana, Arial;font-size: 11px; color:#333333 }
					BODY      { font: 11px Verdana; color:#333333 }
					a:link, a:visited, a:active  { color:#000055 }
					a:hover                      { color:#333377;text-decoration:underline }
					
					#title  { font-size:10px; font-weight:bold; line-height:150%; color:#FFFFFF; height: 24px; background-image: url(html/sys-img/top_cell.gif); }
					#title  a:link, #title  a:visited, #title  a:active { text-decoration: underline; color : #FFFFFF; font-size:11px }
					
					#detail { font-family: Arial; font-size:11px; color: #333333 }
					
 					#large { font-family: verdana, arial; font-size:20px; color:#4C77B6; font-weight:bold; letter-spacing:-1px }
 					
					#subtitle { font-family: Verdana; font-size:22px; color:#4C77B6; font-weight:bold }
					
					#warning { font-family: Verdana; font-size:22px; color:#FF0000; font-weight:bold }
					
					#table1 {  background-color:#F1F1F1; width:100%; align:center; border:1px solid black }
					
					#tdrow1 { background-color:#F3F3EE }
					
					#tdrow2 { background-color:#EBEBE4 }
					
					#catrow  { font-size:10px; font-weight:bold; line-height:150%; color:#4C77B6; background-color:#C2CFDF; }
					
					#tablewrap {  border:1px dashed #777777; background-color:#EFEFEF }
					
					#copy { color:#555555; font-size:9px }
					
					#tdtop  { font-weight:bold; height:20px; line-height:150%; color:#FFFFFF; background-image: url(html/sys-img/top_cell.gif); }
					
					#green    { background-color: #caf2d9 }
					#red      { background-color: #f5cdcd }
					
					#button   { background-color: #4C77B6; color: #FFFFFF; font-family:Verdana, Arial; font-size:11px }
					
					#textinput { background-color: #EEEEEE; color:Ê#000000; font-family:Verdana, Arial; font-size:10px; width:100% }
					
					#dropdown { background-color: #EEEEEE; color:Ê#000000; font-family:Verdana, Arial; font-size:10px }
					
					#multitext { background-color: #EEEEEE; color:Ê#000000; font-family:Courier, Verdana, Arial; font-size:10px }
					
				  </style>
				  </head>
				 <body marginheight='0' marginwidth='0' leftmargin='0' topmargin='0' bgcolor='#FFFFFF'>
				 
				 <table width='100%' height='70' cellpadding='0' cellspacing='0' border='0'>
					<tr bgcolor='#4C77B6'>
						<td width='370' align='left' bgcolor='#4C77B6'><img src='html/sys-img/title.gif' width='370' height='70'></td>
					</tr>
				</table>
				<br>
				<table width='90%' cellpadding='0' cellspacing='0' border='0' align='center'>
				 ";
    }
}

class installer
{
    public function parse_incoming()
    {
        global $_GET, $_POST, $HTTP_CLIENT_IP, $REQUEST_METHOD, $REMOTE_ADDR, $HTTP_PROXY_USER, $HTTP_X_FORWARDED_FOR;

        $return = [];

        if (is_array($_GET)) {
            while (list($k, $v) = each($_GET)) {
                //$k = $this->clean_key($k);

                if (is_array($_GET[$k])) {
                    while (list($k2, $v2) = each($_GET[$k])) {
                        $return[$k][$this->clean_key($k2)] = $this->clean_value($v2);
                    }
                } else {
                    $return[$k] = $this->clean_value($v);
                }
            }
        }

        // Overwrite GET data with post data

        if (is_array($_POST)) {
            while (list($k, $v) = each($_POST)) {
                //$k = $this->clean_key($k);

                if (is_array($_POST[$k])) {
                    while (list($k2, $v2) = each($_POST[$k])) {
                        $return[$k][$this->clean_key($k2)] = $this->clean_value($v2);
                    }
                } else {
                    $return[$k] = $this->clean_value($v);
                }
            }
        }

        return $return;
    }

    public function clean_key($key)
    {
        if ('' == $key) {
            return '';
        }

        $key = preg_replace("/\.\./", '', $key);

        $key = preg_replace("/\_\_(.+?)\_\_/", '', $key);

        $key = preg_replace("/^([\w\.\-\_]+)$/", '$1', $key);

        return $key;
    }

    public function clean_value($val)
    {
        if ('' == $val) {
            return '';
        }

        $val = preg_replace('/&/', '&amp;', $val);

        $val = preg_replace('/<!--/', '&#60;&#33;--', $val);

        $val = preg_replace('/-->/', '--&#62;', $val);

        $val = preg_replace('/<script/i', '&#60;script', $val);

        $val = preg_replace('>/', '&gt;', $val);

        $val = preg_replace('/</', '&lt;', $val);

        $val = preg_replace('/"/', '&quot;', $val);

        $val = preg_replace("/\|/", '&#124;', $val);

        $val = preg_replace("/\n/", '<br>', $val); // Convert literal newlines

        $val = preg_replace('/\\$/', '&#036;', $val);

        $val = preg_replace("/\r/", '', $val); // Remove literal carriage returns

        $val = preg_replace('/!/', '&#33;', $val);

        $val = preg_replace("/'/", '&#39;', $val); // IMPORTANT: It helps to increase sql query safety.
        $val = stripslashes($val);                                     // Swop PHP added backslashes
        $val = preg_replace("/\\\/", '&#092;', $val); // Swop user inputted backslashes
        return $val;
    }
}

// DATA AND STUFF, ETC

function get_sql_inst()
{
    global $re_inst;

    $SQL = [];

    $SQL[] = 'CREATE TABLE ibf_admin_logs (
  id bigint(20) NOT NULL auto_increment,
  act varchar(255) default NULL,
  code varchar(255) default NULL,
  member_id int(10) default NULL,
  ctime int(10) default NULL,
  note text,
  ip_address varchar(255) default NULL,
  PRIMARY KEY  (id)
);';

    $SQL[] = "CREATE TABLE ibf_admin_sessions (
  ID varchar(32) NOT NULL default '',
  IP_ADDRESS varchar(32) NOT NULL default '',
  MEMBER_NAME varchar(32) NOT NULL default '',
  MEMBER_ID varchar(32) NOT NULL default '',
  SESSION_KEY varchar(32) NOT NULL default '',
  LOCATION varchar(64) default 'index',
  LOG_IN_TIME int(10) NOT NULL default '0',
  RUNNING_TIME int(10) NOT NULL default '0',
  PRIMARY KEY  (ID)
);";

    $SQL[] = 'CREATE TABLE ibf_attachments (
  id bigint(20) NOT NULL auto_increment,
  mime_type varchar(128) default NULL,
  file_name varchar(64) default NULL,
  PRIMARY KEY  (id)
);';

    $SQL[] = "CREATE TABLE ibf_badwords (
  wid int(3) NOT NULL auto_increment,
  type varchar(250) NOT NULL default '',
  swop varchar(250) default NULL,
  m_exact tinyint(1) default '0',
  PRIMARY KEY  (wid)
);";

    $SQL[] = "CREATE TABLE ibf_calendar_events (
  eventid bigint(20) NOT NULL auto_increment,
  userid bigint(20) NOT NULL default '0',
  year int(4) NOT NULL default '2002',
  month int(2) NOT NULL default '1',
  mday int(2) NOT NULL default '1',
  title varchar(254) NOT NULL default 'no title',
  event_text text NOT NULL,
  read_perms varchar(254) NOT NULL default '*',
  unix_stamp int(10) NOT NULL default '0',
  priv_event tinyint(1) NOT NULL default '0',
  show_emoticons tinyint(1) NOT NULL default '1',
  rating smallint(2) NOT NULL default '1',
  PRIMARY KEY  (eventid),
  KEY unix_stamp (unix_stamp)
);";

    $SQL[] = "CREATE TABLE ibf_categories (
  id smallint(5) NOT NULL default '0',
  position tinyint(3) default NULL,
  state varchar(10) default NULL,
  name varchar(128) NOT NULL default '',
  description text,
  image varchar(128) default NULL,
  url varchar(128) default NULL,
  PRIMARY KEY  (id),
  KEY id (id)
);";

    $SQL[] = "CREATE TABLE ibf_contacts (
  id bigint(20) NOT NULL auto_increment,
  contact_id varchar(32) NOT NULL default '',
  member_id varchar(32) NOT NULL default '',
  contact_name varchar(32) NOT NULL default '',
  allow_msg tinyint(1) default NULL,
  contact_desc varchar(50) default NULL,
  PRIMARY KEY  (id)
);";

    $SQL[] = "CREATE TABLE ibf_css (
  cssid int(10) NOT NULL auto_increment,
  css_name varchar(128) NOT NULL default '',
  css_text text,
  css_comments text,
  PRIMARY KEY  (cssid)
);";

    $SQL[] = "CREATE TABLE ibf_faq (
  id bigint(20) NOT NULL auto_increment,
  title varchar(128) NOT NULL default '',
  text text,
  description text NOT NULL,
  PRIMARY KEY  (id)
);";

    $SQL[] = "CREATE TABLE ibf_forum_tracker (
  frid bigint(20) NOT NULL auto_increment,
  member_id varchar(32) NOT NULL default '',
  forum_id int(10) NOT NULL default '0',
  start_date int(10) default NULL,
  last_sent int(10) NOT NULL default '0',
  PRIMARY KEY  (frid)
);";

    $SQL[] = "CREATE TABLE ibf_forums (
  id smallint(5) NOT NULL default '0',
  topics mediumint(6) default NULL,
  posts mediumint(6) default NULL,
  last_post int(10) default NULL,
  last_poster_id int(10) default NULL,
  last_poster_name varchar(32) default NULL,
  name varchar(128) NOT NULL default '',
  description text,
  position tinyint(2) default NULL,
  use_ibc tinyint(1) default NULL,
  use_html tinyint(1) default NULL,
  status varchar(10) default NULL,
  start_perms varchar(255) default NULL,
  reply_perms varchar(255) default NULL,
  read_perms varchar(255) default NULL,
  password varchar(32) default NULL,
  category smallint(5) NOT NULL default '0',
  last_title varchar(128) default NULL,
  last_id int(10) default NULL,
  sort_key varchar(32) default NULL,
  sort_order varchar(32) default NULL,
  prune tinyint(3) default NULL,
  show_rules tinyint(1) default NULL,
  upload_perms varchar(255) default NULL,
  preview_posts tinyint(1) default NULL,
  allow_poll tinyint(1) NOT NULL default '1',
  allow_pollbump tinyint(1) NOT NULL default '0',
  inc_postcount tinyint(1) NOT NULL default '1',
  skin_id int(10) default NULL,
  parent_id mediumint(5) default '-1',
  subwrap tinyint(1) default '0',
  sub_can_post tinyint(1) default '1',
  PRIMARY KEY  (id),
  KEY category (category),
  KEY id (id)
);";

    $SQL[] = "CREATE TABLE ibf_groups (
  g_id int(3) unsigned NOT NULL auto_increment,
  g_view_board tinyint(1) default NULL,
  g_mem_info tinyint(1) default NULL,
  g_other_topics tinyint(1) default NULL,
  g_use_search tinyint(1) default NULL,
  g_email_friend tinyint(1) default NULL,
  g_invite_friend tinyint(1) default NULL,
  g_edit_profile tinyint(1) default NULL,
  g_post_new_topics tinyint(1) default NULL,
  g_reply_own_topics tinyint(1) default NULL,
  g_reply_other_topics tinyint(1) default NULL,
  g_edit_posts tinyint(1) default NULL,
  g_delete_own_posts tinyint(1) default NULL,
  g_open_close_posts tinyint(1) default NULL,
  g_delete_own_topics tinyint(1) default NULL,
  g_post_polls tinyint(1) default NULL,
  g_vote_polls tinyint(1) default NULL,
  g_use_pm tinyint(1) default NULL,
  g_is_supmod tinyint(1) default NULL,
  g_access_cp tinyint(1) default NULL,
  g_title varchar(32) NOT NULL default '',
  g_can_remove tinyint(1) default NULL,
  g_append_edit tinyint(1) default NULL,
  g_access_offline tinyint(1) default NULL,
  g_avoid_q tinyint(1) default NULL,
  g_avoid_flood tinyint(1) default NULL,
  g_icon varchar(64) default NULL,
  g_attach_max bigint(20) default NULL,
  g_avatar_upload tinyint(1) default '0',
  g_calendar_post tinyint(1) default '0',
  prefix varchar(250) default NULL,
  suffix varchar(250) default NULL,
  g_max_messages int(5) default '50',
  g_max_mass_pm int(5) default '0',
  g_search_flood mediumint(6) default '20',
  g_edit_cutoff int(10) default '0',
  g_promotion varchar(10) default '-1&-1',
  g_hide_from_list tinyint(1) default '0',
  g_post_closed tinyint(1) default '0',
  PRIMARY KEY  (g_id)
);";

    $SQL[] = "CREATE TABLE ibf_languages (
  lid bigint(20) NOT NULL auto_increment,
  ldir varchar(64) NOT NULL default '',
  lname varchar(250) NOT NULL default '',
  lauthor varchar(250) default NULL,
  lemail varchar(250) default NULL,
  PRIMARY KEY  (lid)
);";

    $SQL[] = "CREATE TABLE ibf_macro (
  macro_id smallint(3) NOT NULL auto_increment,
  macro_value varchar(200) default NULL,
  macro_replace text,
  can_remove tinyint(1) default '0',
  macro_set smallint(3) NOT NULL,
  PRIMARY KEY  (macro_id),
  KEY macro_set (macro_set)
);";

    $SQL[] = "CREATE TABLE ibf_macro_name (
  set_id smallint(3) NOT NULL default '0',
  set_name varchar(200) default NULL,
  PRIMARY KEY  (set_id)
);";

    $SQL[] = "CREATE TABLE ibf_member_extra (
  id bigint(20) NOT NULL default '0',
  notes text,
  links text,
  bio text,
  ta_size char(3) default NULL,
  PRIMARY KEY  (id)
);";

    if ($re_inst) {
        $SQL[] = "ALTER TABLE xbb_session
ADD member_name varchar(64) default NULL,
ADD member_id varchar(32) default NULL,
ADD browser varchar(64) default NULL,
ADD login_type tinyint(1) default NULL,
ADD location varchar(40) default NULL,
ADD member_group smallint(3) default NULL,
ADD in_forum int(10) NOT NULL default '0',
ADD in_topic int(10) NOT NULL default '0',
ADD KEY in_topic (in_topic),
ADD KEY in_forum (in_forum)
";

        $SQL[] = "ALTER TABLE xbb_users
CHANGE uid uid bigint(10) unsigned NOT NULL auto_increment default '0',
CHANGE uname uname varchar(32) NOT NULL default '',
CHANGE user_avatar user_avatar varchar(128),
CHANGE user_aim user_aim varchar(40) default NULL,
CHANGE user_icq user_icq varchar(40) default NULL,
CHANGE user_from user_from varchar(128) default NULL,
CHANGE user_yim user_yim varchar(32) default NULL,
CHANGE user_viewemail user_viewemail varchar(8) default NULL,
CHANGE user_msnm user_msnm varchar(64) default NULL,
CHANGE user_intrest user_intrest text,
CHANGE attachsig attachsig tinyint(1) default '1',
CHANGE actkey actkey varchar(8) NULL default '' ,
CHANGE user_occ user_occ varchar(100) NULL default '',

ADD signature text,
ADD avatar_size varchar(9) default NULL,
ADD mgroup tinyint(2) NOT NULL default '3',
ADD ip_address varchar(16) NOT NULL default '',
ADD title varchar(64) default NULL,
ADD allow_admin_mails tinyint(1) default NULL,
ADD email_pm tinyint(1) default NULL,
ADD email_full tinyint(1) default NULL,
ADD skin smallint(5) default NULL,
ADD warn_level int(10) default NULL,
ADD language varchar(32) default NULL,
ADD last_post int(10) default NULL,
ADD allow_post tinyint(1) default '1',
ADD view_img tinyint(1) default '1',
ADD view_avs tinyint(1) default '1',
ADD view_pop tinyint(1) default '1',
ADD bday_day int(2) default NULL,
ADD bday_month int(2) default NULL,
ADD bday_year int(4) default NULL,
ADD new_msg tinyint(2) default NULL,
ADD msg_from_id varchar(32) default NULL,
ADD msg_msg_id int(10) default NULL,
ADD msg_total smallint(5) default NULL,
ADD vdirs text,
ADD show_popup tinyint(1) default NULL,
ADD validate_key varchar(32) default NULL,
ADD prev_group smallint(3) default '0',
ADD new_pass varchar(32) default NULL,
ADD misc varchar(128) default NULL,
ADD last_visit int(10) default '0',
ADD last_activity int(10) default '0',
ADD dst_in_use tinyint(1) default '0',
ADD view_prefs varchar(64) default '-1&-1',
ADD coppa_user tinyint(1) default '0',
ADD mod_posts tinyint(1) default '0',
ADD auto_track tinyint(1) default '0',
ADD INDEX mgroup (mgroup),
ADD INDEX bday_day (bday_day),
ADD INDEX bday_month (bday_month)
";

        $SQL[] = "INSERT INTO xbb_users (uid, uname, mgroup, pass, email, user_regdate, ip_address, allow_post, attachsig, view_img, view_avs, view_pop, new_pass, misc, last_visit, last_activity, dst_in_use, view_prefs, coppa_user, mod_posts, auto_track) VALUES (0, 'Guest', 2, '', 'guest@ibforums.com', 0, '', 1, 1, 1, 1, 1, 0,'0', 0, 0, 0, '-1&-1', 0, 0, 0)";

        $SQL[] = "UPDATE xbb_users SET uid='0', level='0' WHERE uname='Guest' AND user_regdate='0'";

        $SQL[] = "ALTER TABLE xbb_smiles
ADD clickable smallint(2) NOT NULL default '1'  
";
    }

    $SQL[] = "CREATE TABLE ibf_messages (
  msg_id bigint(20) NOT NULL auto_increment,
  msg_date int(10) default NULL,
  read_state tinyint(1) default NULL,
  title varchar(128) default NULL,
  message text,
  from_id varchar(32) default NULL,
  vid varchar(32) default NULL,
  member_id varchar(32) NOT NULL default '0',
  recipient_id varchar(32) default NULL,
  attach_type tinyint(128) default NULL,
  attach_file tinyint(128) default NULL,
  cc_users text,
  tracking tinyint(1) default '0',
  read_date int(10) default NULL,
  PRIMARY KEY  (msg_id),
  KEY member_id (member_id),
  KEY vid (vid)
);";

    $SQL[] = "CREATE TABLE ibf_moderator_logs (
  id bigint(20) NOT NULL auto_increment,
  forum_id int(5) default '0',
  topic_id bigint(20) default '0',
  post_id bigint(20) default '0',
  member_id varchar(32) NOT NULL default '',
  member_name varchar(32) NOT NULL default '',
  ip_address varchar(32) default NULL,
  http_referer varchar(255) default NULL,
  ctime int(10) default NULL,
  topic_title varchar(128) default NULL,
  action varchar(128) default NULL,
  query_string varchar(128) default NULL,
  PRIMARY KEY  (id)
);";

    $SQL[] = "CREATE TABLE ibf_moderators (
  mid bigint(20) NOT NULL auto_increment,
  forum_id int(5) NOT NULL default '0',
  member_name varchar(32) NOT NULL default '',
  member_id varchar(32) NOT NULL default '0',
  edit_post tinyint(1) default NULL,
  edit_topic tinyint(1) default NULL,
  delete_post tinyint(1) default NULL,
  delete_topic tinyint(1) default NULL,
  view_ip tinyint(1) default NULL,
  open_topic tinyint(1) default NULL,
  close_topic tinyint(1) default NULL,
  mass_move tinyint(1) default NULL,
  mass_prune tinyint(1) default NULL,
  move_topic tinyint(1) default NULL,
  pin_topic tinyint(1) default NULL,
  unpin_topic tinyint(1) default NULL,
  post_q tinyint(1) default NULL,
  topic_q tinyint(1) default NULL,
  allow_warn tinyint(1) default NULL,
  edit_user tinyint(1) NOT NULL default '0',
  is_group tinyint(1) default '0',
  group_id smallint(3) NOT NULL default '',
  group_name varchar(200) default NULL,
  split_merge tinyint(1) default '0',
  PRIMARY KEY  (mid),
  KEY forum_id (forum_id),
  KEY group_id (group_id),
  KEY member_id (member_id)
);";

    $SQL[] = "CREATE TABLE ibf_pfields_content (
  member_id bigint(20) NOT NULL default '0',
  updated int(10) default '0',
  PRIMARY KEY  (member_id)
);";

    $SQL[] = "CREATE TABLE ibf_pfields_data (
  fid smallint(5) NOT NULL auto_increment,
  ftitle varchar(200) NOT NULL default '',
  fdesc varchar(250) default '',
  fcontent text,
  ftype varchar(250) default 'text',
  freq tinyint(1) default '0',
  fhide tinyint(1) default '0',
  fmaxinput smallint(6) default '250',
  fedit tinyint(1) default '1',
  forder smallint(6) default '1',
  fshowreg tinyint(1) default '0',
  PRIMARY KEY  (fid)
);";

    $SQL[] = "CREATE TABLE ibf_polls (
  pid bigint(20) NOT NULL auto_increment,
  tid bigint(20) NOT NULL default '0',
  start_date int(10) default NULL,
  choices text,
  starter_id varchar(32) default NULL,
  votes bigint(20) default NULL,
  forum_id bigint(20) default NULL,
  poll_question varchar(255) default NULL,
  PRIMARY KEY  (pid)
);";

    $SQL[] = "CREATE TABLE ibf_posts (
  append_edit tinyint(1) default '0',
  edit_time int(10) default NULL,
  pid bigint(20) NOT NULL auto_increment,
  author_id int(10) NOT NULL default '0',
  author_name varchar(32) default NULL,
  use_sig varchar(8) default NULL,
  use_emo varchar(8) default NULL,
  ip_address varchar(32) default NULL,
  post_date int(10) NOT NULL default '',
  icon_id smallint(3) default NULL,
  post text,
  queued tinyint(1) default NULL,
  topic_id bigint(20) NOT NULL default '0',
  forum_id int(10) NOT NULL default '0',
  attach_id varchar(64) default NULL,
  attach_hits int(10) default NULL,
  attach_type varchar(128) default NULL,
  attach_file varchar(255) default NULL,
  post_title varchar(255) default NULL,
  new_topic tinyint(1) default '0',
  edit_name varchar(255) default NULL,
  PRIMARY KEY  (pid),
  KEY topic_id (topic_id,author_id),
  KEY author_id (author_id),
  KEY forum_id (forum_id,post_date)
);";

    $SQL[] = "CREATE TABLE ibf_reg_antispam (
  regid varchar(32) NOT NULL default '',
  regcode varchar(8) NOT NULL default '',
  ip_address varchar(32) default NULL,
  ctime int(10) default NULL,
  PRIMARY KEY  (regid)
);";

    $SQL[] = "CREATE TABLE ibf_rules (
  fid mediumint(6) NOT NULL default '0',
  title varchar(128) NOT NULL default '',
  body text,
  updated int(10) default NULL,
  show_all tinyint(1) default NULL,
  PRIMARY KEY  (fid)
);";

    $SQL[] = "CREATE TABLE ibf_search_results (
  id varchar(32) NOT NULL default '',
  topic_id text NOT NULL,
  search_date int(12) NOT NULL default '0',
  topic_max int(3) NOT NULL default '0',
  sort_key varchar(32) NOT NULL default 'last_post',
  sort_order varchar(4) NOT NULL default 'desc',
  member_id mediumint(10) default '0',
  ip_address varchar(64) default NULL,
  post_id text,
  post_max int(10) NOT NULL default '0'
);";

    $SQL[] = "CREATE TABLE ibf_skin_templates (
  suid int(10) NOT NULL auto_increment,
  set_id int(10) NOT NULL default '0',
  group_name varchar(255) NOT NULL default '',
  section_content mediumtext,
  func_name varchar(255) default NULL,
  func_data text,
  updated int(10) default NULL,
  can_remove tinyint(4) default '0',
  PRIMARY KEY  (suid)
);";

    $SQL[] = "CREATE TABLE ibf_skins (
  uid int(10) NOT NULL auto_increment,
  sname varchar(100) NOT NULL default '',
  sid int(10) NOT NULL default '0',
  set_id int(5) NOT NULL default '0',
  tmpl_id int(10) NOT NULL default '0',
  macro_id int(10) NOT NULL default '1',
  css_id int(10) NOT NULL default '1',
  img_dir varchar(200) default '1',
  tbl_width varchar(250) default NULL,
  tbl_border varchar(250) default NULL,
  hidden tinyint(1) NOT NULL default '0',
  default_set tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (uid),
  KEY tmpl_id (tmpl_id),
  KEY css_id (css_id)
);";

    $SQL[] = "CREATE TABLE ibf_stats (
  TOTAL_REPLIES bigint(20) NOT NULL default '0',
  TOTAL_TOPICS bigint(20) NOT NULL default '0',
  LAST_MEM_NAME varchar(32) default NULL,
  LAST_MEM_ID varchar(32) default NULL,
  MOST_DATE int(10) default NULL,
  MOST_COUNT int(10) default '0',
  MEM_COUNT bigint(20) default '0'
);";

    $SQL[] = 'CREATE TABLE ibf_templates (
  tmid int(10) NOT NULL auto_increment,
  template mediumtext,
  name varchar(128) default NULL,
  PRIMARY KEY  (tmid)
);';

    $SQL[] = 'CREATE TABLE ibf_titles (
  id smallint(5) NOT NULL auto_increment,
  posts int(10) default NULL,
  title varchar(128) default NULL,
  pips varchar(128) default NULL,
  PRIMARY KEY  (id)
);';

    $SQL[] = "CREATE TABLE ibf_tmpl_names (
  skid int(10) NOT NULL auto_increment,
  skname varchar(60) NOT NULL default 'Invision P Board',
  author varchar(250) default '',
  email varchar(250) default '',
  url varchar(250) default '',
  PRIMARY KEY  (skid)
);";

    $SQL[] = "CREATE TABLE ibf_topics (
  tid bigint(20) NOT NULL auto_increment,
  title varchar(70) NOT NULL default '',
  description varchar(70) default NULL,
  state varchar(8) default NULL,
  posts smallint(4) default NULL,
  starter_id varchar(32) default NULL,
  start_date int(10) default NULL,
  last_poster_id varchar(32) default NULL,
  last_post int(10) default '0' NOT NULL,
  icon_id tinyint(2) default NULL,
  starter_name varchar(32) default NULL,
  last_poster_name varchar(32) default NULL,
  poll_state varchar(8) default NULL,
  last_vote int(10) default NULL,
  views smallint(5) default NULL,
  forum_id smallint(5) NOT NULL default '0',
  approved tinyint(1) NOT NULL default '0',
  author_mode tinyint(1) default NULL,
  pinned tinyint(1) NOT NULL default '0',
  moved_to varchar(64) default NULL,
  rating text,
  total_votes int(5) NOT NULL default '0',
  PRIMARY KEY  (tid),
  KEY forum_id (forum_id,approved,pinned,last_post)
);";

    $SQL[] = "CREATE TABLE ibf_tracker (
  trid bigint(20) NOT NULL auto_increment,
  member_id varchar(32) NOT NULL default '',
  topic_id bigint(20) NOT NULL default '0',
  start_date int(10) default NULL,
  last_sent int(10) NOT NULL default '0',
  PRIMARY KEY  (trid)
);";

    $SQL[] = "CREATE TABLE ibf_voters (
  vid bigint(20) NOT NULL auto_increment,
  ip_address varchar(16) NOT NULL default '',
  vote_date int(10) NOT NULL default '0',
  tid bigint(20) NOT NULL default '0',
  member_id varchar(32) default NULL,
  forum_id bigint(20) default NULL,
  PRIMARY KEY  (vid)
);";

    $SQL[] = "INSERT INTO ibf_categories (id, position, state, name, description, image, url) VALUES (-1, NULL, NULL, '-', NULL, NULL, NULL)";

    $SQL[] = "INSERT INTO ibf_categories (id, position, state, name, description, image, url) VALUES (1, 1, '1', 'A Test Category', '', '', '')";

    $SQL[] = "INSERT INTO ibf_css (cssid, css_name, css_text, css_comments) VALUES (1, 'Invision Style Sheet', 'form { display:inline }\r\nTABLE, TR, TD { font-family: Verdana, Tahoma, Arial; font-size: 8.5pt; color: #000000 }\r\na:link, a:visited, a:active { text-decoration: underline; color: #000000 }\r\na:hover { color: #465584 }\r\n.hlight { background-color: #DFE6EF }\r\n.dlight { background-color: #EEF2F7 }\r\n.mainbg { background-color: #FFFFFF }\r\n.mainfoot { background-color: #BCD0ED }\r\n.forum1 { background-color: #DFE6EF }\r\n.forum2 { background-color: #E4EAF2 }\r\n.post1 { background-color: #F5F9FD }\r\n.post2 { background-color: #EEF2F7 }\r\n.posthead { background-color: #E4EAF2 }\r\n\r\n.postbak { background-color: #D2D2D0 }\r\n.title { background-color: #C4DCF7 }\r\n.row1 { background-color: #EEF2F7 }\r\n.row2 { background-color: #F5F9FD }\r\n.postsep { background-color: #C7D2E0; height: 1px }\r\n\r\n.signature { font-size: 7.5pt; color: #333399 }\r\n.postdetails { font-size: 7.5pt }\r\n.postcolor, #postcolor { font-size: 9pt; line-height: 160% }\r\n.membertitle { font-size: 10px; line-height: 150%; color: #000000 }\r\n.normalname { font-size: 12px; font-weight: bold; color: #000033; padding-bottom: 2px }\r\n.normalname a:link, .normalname a:visited, .normalname a:active { text-decoration: underline; color: #000033; padding-bottom: 2px }\r\n.unreg { font-size: 11px; font-weight: bold; color: #990000 }\r\n.highlight { color: #FF0000 }\r\n.highlight a:link, .highlight a:visited, .highlight a:active { text-decoration: underline; color: #FF0000 }\r\n.highlight a:hover { text-decoration: underline }\r\n.desc { font-size: 8.0pt; color: #434951 }\r\n.copyright { font-family: Verdana, Tahoma, Arial; font-size: 7.5pt; line-height: 12px }\r\n.category { font-weight: bold; line-height: 150%; color: #4C77B6; background-color: #C2CFDF }\r\n.category   a:link, #category   a:visited, #category   a:active { text-decoration: none; color: #4C77B6 }\r\n.postfoot         {\r\n\r\n    font-weight:bold;\r\n\r\n    color:#3A4F6C;\r\n\r\n    height: 24px;\r\n\r\n    background-color: #D1DCEB;\r\n\r\n}\r\n.titlefoot { font-weight: bold; color: #3A4F6C; height: 24px; background-color: #BCD0ED }\r\n.titlemedium         {\r\n\r\n    font-weight:bold;\r\n    color:#3A4F6C;\r\n\r\n    height: 30px;\r\n\r\n    background-color: #9FBCE3;\r\n    \r\n    background-image: url(style_images/<#IMG_DIR#>/tile_sub.gif);\r\n\r\n}\r\n.titlemedium  a:link,  .titlefoot  a:link, .titlemedium  a:visited, .titlefoot  a:visited, .titlemedium  a:active, .titlefoot  a:active { text-decoration: underline; color: #3A4F6C }\r\n.titlemedium a:hover, .subtitle a:hover, .titlefoot a:hover { text-decoration: underline; color: #000000 }\r\n.maintitle         {\r\n\r\n    color:#FFFFFF;\r\n\r\n    font-size: 9.5pt;\r\n    \r\n    height: 26px;\r\n    \r\n    background-image: url(style_images/<#IMG_DIR#>/tile_back.gif);\r\n\r\n}\r\n.edit { font-size: 9px }\r\n.fancyborder { border: 1px dashed #999999 }\r\n.solidborder { border: 1px solid #999999 }\r\n.maintitle  a:link, .maintitle  a:visited, .maintitle  a:active { text-decoration: none; color: #FFFFFF }\r\n.maintitle a:hover { text-decoration: underline }\r\n.nav { font-weight: bold; color: #000000; font-size: 8.5pt }\r\n.pagetitle { color: #4C77B6; font-size: 18px; font-weight: bold; letter-spacing: -1px; line-height: 120% }\r\n.useroptions { background-color: #598CC3; height: 25px; font-weight: bold; color: #FFFFFF }\r\n.useroptions a:link, .useroptions a:visited,.useroptions a:active { text-decoration: none; color: #FFFFFF }\r\n.bottomborder { border-bottom: 1px dashed #D2D2D0 }\r\n.linkthru { color: #000000; font-size:8.5pt }\r\n.linkthru  a:link, .linkthru  a:active { text-decoration: underline; color: #000000 }\r\n.linkthru  a:visited { text-decoration: underline; color: #000000 }\r\n.linkthru  a:hover { text-decoration: underline; color: #465584 }\r\n#QUOTE { font-family: Verdana, Arial; font-size: 8pt; color: #333333; background-color: #FAFCFE; border: 1px solid Black; padding-top: 2px; padding-right: 2px; padding-bottom: 2px; padding-left: 2px }\r\n#CODE { font-family: Verdana, Arial; font-size: 8pt; color: #333333; background-color: #FAFCFE; border: 1px solid Black; padding-top: 2px; padding-right: 2px; padding-bottom: 2px; padding-left: 2px }\r\n.codebuttons { font-size: 8.5pt; font-family: verdana, helvetica, sans-serif; vertical-align: middle }\r\n.forminput { font-size: 9pt; font-family: verdana, helvetica, sans-serif; vertical-align: middle }\r\n.textinput { font-size: 9pt; font-family: verdana, helvetica, sans-serif; vertical-align: middle }\r\n.input { font-size: 9pt; font-family: verdana, helvetica, sans-serif; vertical-align: middle }\r\n', NULL)";

    $SQL[] = "INSERT INTO ibf_faq (id, title, text, description) VALUES (1, 'Registration benefits', 'To be able to use all the features on this board, the administrator will probably require that you register for a member account. Registration is free and only takes a moment to complete.\r<br>\r<br>During registration, the adminstrator requires that you supply a valid email address. This is important as the administrator may require that you validate your registration via an email. If this is the case, you will be notified when registering.\r<br>In some cases, the administrator will need to approve your regsitration before you can use your member account fully. If this is the case you will be notified during registration.\r<br>\r<br>Once you have registered and logged in, you will have access to your private messenger and your control panel.\r<br>\r<br>For more information on these items, please see the relevant sections in this documentation.', 'How to register and the added benefits of being a registered member.')";

    $SQL[] = "INSERT INTO ibf_faq (id, title, text, description) VALUES (2, 'Cookies and cookie usage', 'Using cookies is optional, but strongly recommended. Cookies are used to track topics, showing you which topics have new replies since your last visit and to automatically log you in when you return.\r<br>\r<br>This board will also allow you to remove the long session id\'s found in the URL\'s. You can do this by entering your control panel (click the link at the top of the board) and entering the \'Board Settings\' section. It is recommended that you enable this feature because you may experience problems if your internet connection shares a proxy and you share a link with another user. If in doubt, choose to hide the session id.\r<br>\r<br><b>Clearing Cookies</b>\r<br>\r<br>You can clear the cookies at any time by clicking on the link found at the bottom of the main board page (the first page you see when returning to the board). If this does not work for you, you may need to remove the cookies manually.\r<br>\r<br><u>Removing Cookies in Internet Explorer for Windows</u>\r<br>\r<br><ul>\r<br><li> Close all open Internet Explorer Windows\r<br><li> Click on the \'start\' button\r<br><li> Move up to \'Find\' and click on \'Files and Folders\'\r<br><li> When the new window appears, type in the domain name of the board you are using into the \'containing text\' field. (If the boards address was \'http://www.invisionboard.com/forums/index.php\' you would enter \'invisionboard.com\' without the quotes)\r<br><li> In the \'look in\' box, type in <b>C:&#92;Windows&#92;Cookies</b> and press \'Find Now\'\r<br><li> After it has finished searching, highlight all files (click on a file then press CTRL+A) and delete them.\r<br></ul>\r<br>\r<br><u>Removing Cookies in Internet Explorer for Macintosh</u>\r<br>\r<br><ul>\r<br><li> With Internet Explorer active, choose \'Edit\' and then \'Preferences\' from the Macintosh menu bar at the top of the screen\r<br><li> When the preferences panel opens, choose \'Cookies\' found in the \'Receiving Files\' section.\r<br><li> When the cookie pane loads, look for the domain name of the board (If the boards address was \'http://www.invisionboard.com/forums/index.php\' look for \'invisionboard.com\' or \'www.invisionboard.com\'\r<br><li> For each cookie, click on the entry and press the delete button.\r<br></ul>\r<br>\r<br>Your cookies should now be removed. In some cases you may need to restart your computer for the changes to take effect.', 'The benefits of using cookies and how to remove cookies set by this board.')";

    $SQL[] = "INSERT INTO ibf_faq (id, title, text, description) VALUES (3, 'Recovering lost or forgotten passwords', 'Security is a big feature on this board, and to that end, all passwords are encrypted when you register.\r<br>This means that we cannot email your password to you as we hold no record of your \'uncrypted\' password. You can however, apply to have your password reset.\r<br>\r<br>To do this, click on the <a href=\'index.php?act=Reg&CODE=10\'>Lost Password link</a> found on the log in page.\r<br>\r<br>Further instruction is available from there.', 'How to reset your password if you\'ve forgotton it.')";

    $SQL[] = "INSERT INTO ibf_faq (id, title, text, description) VALUES (4, 'Your Control Panel', 'Your control panel is your own private board console. You can change how the board looks and feels as well as your own information from here.\r<br>\r<br><b>Edit Profile Info</b>\r<br>\r<br>This section allows you to add or edit your contact information and enter some personal information if you choose. All of this information is optional and can be omitted.\r<br>\r<br><b>Edit Signature</b>\r<br>\r<br>A board \'signature\' is very similar to an email signature. This signature is attached to the foot of every message you post unless you choose to check the box that allows you to ommit the signature in the message you are posting. You may use BB Code if available and in some cases, pure HTML (if the board administrator allows it).\r<br>\r<br><b>Edit Avatar Settings</b>\r<br>\r<br>An avatar is a little image that appears under your username when you view a topic or post you authored. If the administrator allows, you may either choose from the board gallery, enter a URL to an avatar stored on your server or upload an avatar to use. You may also set the width of the avatar to ensure that it\'s sized in proportion.\r<br>\r<br><b>Email Settings</b>\r<br>\r<br><u>Hide my email address</u> allows you to deny the ability for other users to send you an email from the board.\r<br><u>Send me updates sent by the board administrator</u> will allow the administrator to include your email address in any mailings they send out - this is used mostly for important updates and community information.\r<br><u>Include a copy of the post when emailing me from a subscribed topic</u>, this allows you to have the new post included in any reply to topic notifications.\r<br>\r<br><b>Board Settings</b>\r<br>\r<br>From this section, you can set your timezone, choose to not see users signatures, avatars and posted images.\r<br>You can choose to get a pop up window informing you when you have a new message and hide the long session ID in links (see the \'Cookies\' help file for more infomation on this).\r<br>\r<br><b>Skins and Languages</b>\r<br>\r<br>If available, you can choose a skin style and language choice. This affects how the board is displayed so you may wish to preview the skin before submitting the form.\r<br>\r<br><b>Change Email Address</b>\r<br>\r<br>At any time, you can change the email address that is registered to your account. In some cases, you will need to revalidate your account after changing your email address. If this is the case, you will be notified before your email address change is processed.\r<br>\r<br><b>Change Password</b>\r<br>\r<br>You may change your password from this section. Please note that you will need to know your current password before you can change your password.\r<br>\r<br><b>View Subsciptions</b>\r<br>\r<br>This is where you manage your topic subscriptions. Please see the help file \'Email Notification of new messages\' for more information on how to subscribe to topics.', 'Editing contact information, personal information, avatars, signatures, board settings, languages and style choices.')";

    $SQL[] = "INSERT INTO ibf_faq (id, title, text, description) VALUES (5, 'Email Notification of new messages', 'This board can notify you when a new reply is added to a topic. Many users find this useful to keep up to date on topics without the need to view the board to check for new messages.\r<br>\r<br>To do this, simply click on the \'Track This Topic\' link found at the top and bottom of any topic. When you click this link, the topic will be added to your subscriptions that are managed from your control panel.\r<br>\r<br>Please note that to avoid multiple emails being sent to your email address, you will only get notified once per day of new replies.', 'How to get emailed when a new reply is added to a topic.')";

    $SQL[] = "INSERT INTO ibf_faq (id, title, text, description) VALUES (6, 'Your Personal Messenger', 'Your personal messenger acts much like an email account in that you can send and receive messages and store messages in folders.\r<br>\r<br><b>Send a new PM</b>\r<br>\r<br>This will allow you to send a message to another member. If you have names in your contact list, you can choose a name from it - or you may choose to enter a name in the relevant form field. This will be automatically filled in if you clicked a \'PM\' button on the board (from the memberlist or a post).\r<br>If the administrator allows, you may use BB Code and HTML in your private message. If you choose to check the \'Add a copy of this message to you sent items folder\' box, a copy of the message will be saved for you for later reference. If you check the \'Get notified when this message is read\' box, you will receive a PM informing you when the message was read for your records.\r<br>\r<br><b>Contact List</b>\r<br>\r<br>You may add in users names in this section, or edit any saved entries. You can also use this as a ban list, denying the named member the ability to message you.\r<br>Names entered in this section will appear in the drop down list when sending a new PM, allowing you to quickly choose the members name when sending a message.\r<br>\r<br><b>Edit Folders</b>\r<br>\r<br>You may rename, add or remove folders to store messages is, allowing you to organise your messages to your preference. You cannot remove \'Sent Items\' or \'Inbox\'.\r<br>\r<br><b>Go to Inbox</b>\r<br>\r<br>Your inbox is where all new messages are sent to. Clicking on the message title will show you the message in a similar format to the board topic view. You can also delete or move messages from your inbox.\r<br>\r<br><b>Archive Messages</b>\r<br>\r<br>If your messenger folders are full and you are unable to receive new messages, you can archive them off. This compiles the messages into a single HTML page or Microsoft &copy; Excel Format. This page is then emailed to your registered email address for your convenience.\r<br>\r<br>', 'How to send personal messages, edit your messenger folders and archive stored messages.')";

    $SQL[] = "INSERT INTO ibf_faq (id, title, text, description) VALUES (7, 'Contacting the moderating team', 'If you need to contact a moderator or simply wish to view the complete administration team, you can click the link \'The moderating team\' found at the top of the main board page (the first page you see when visiting the board).\r<br>\r<br>This list will show you administrators (those who have administration control panel access), global moderators (those who can moderate in all forums) and the moderators of the individual forums.\r<br>\r<br>If you wish to contact someone about your member account, then contact an administrator - if you wish to contact someone about a post or topic, contact either a global moderator or the forum moderator.', 'Where to find a list of the board moderators and administrators.')";

    $SQL[] = "INSERT INTO ibf_faq (id, title, text, description) VALUES (8, 'Viewing members profile information', 'You can view a members profile at any time by clicking on their name when it is underlined (as a link) or by clicking on their name in a post within a topic.\r<br>\r<br>This will show you their profile page which contains their contact information (if they have entered some) and their \'active stats\'.', 'How to view members contact information.')";

    $SQL[] = "INSERT INTO ibf_faq (id, title, text, description) VALUES (9, 'Viewing todays active topics', 'You can view which new topics have new replies today by clicking on the \'Todays Active Topics\' link found on the main board page (the first page you see when visiting the board).\r<br>\r<br>You can set your own date critera, choosing to view all topics  with new replies during several date choices.', 'How to view all the topics which have a new reply today')";

    $SQL[] = "INSERT INTO ibf_faq (id, title, text, description) VALUES (10, 'Searching Topics and Posts', 'The search feature is designed to allow you to quickly find topics and posts that contain the keywords you enter.\r<br>\r<br>The \'Simple Search\' option from the drop down box allows you to enter a single keyword or phrase to search by - the advanced option allows you to join keywords with \'AND\', \'OR\' to fine tune the search. Using this option will make the search slightly slower.\r<br>\r<br>The bottom section of the form allows you to further fine tune the search. You can choose a category to search in, or a forum - or choose all forums to search in.\r<br>\r<br>If you enter a name into the \'Search by member name\' section, all posts started or replied in by this member are returned.', 'How to use the search feature.')";

    $SQL[] = "INSERT INTO ibf_faq (id, title, text, description) VALUES (11, 'Logging in and out', 'If you have chosen not to remember your log in details in cookies, or you are accessing the board on another computer, you will need to log into the board to access your member profile and post with your registered name.\r<br>\r<br>When you log in, you have the choice to save cookies that will log you in automatically when you return. Do not use this option on a shared computer for security.\r<br>\r<br>You can also choose to hide - this will keep your name from appearing in the active users list.\r<br>\r<br>Logging out is simply a matter of clicking on the \'Log Out\' link that is displayed when you are logged in. If you find that you are not logged out, you may need to manually remove your cookies. See the \'Cookies\' help file for more information.', 'How to log in and out from the board and how to remain anonymous and not be shown on the active users list.')";

    $SQL[] = "INSERT INTO ibf_forums (id, topics, posts, last_post, last_poster_id, last_poster_name, name, description, position, use_ibc, use_html, status, start_perms, reply_perms, read_perms, password, category, last_title, last_id, sort_key, sort_order, prune, show_rules, upload_perms, preview_posts, allow_poll, allow_pollbump, inc_postcount, skin_id, parent_id, subwrap, sub_can_post) VALUES (1, 1, 1, <%time%>, 1, 'Invision Power Board Team', 'A Test Forum', 'A test forum that may be removed at any time', 1, 1, 0, '1', '*', '*', '*', '', 1, 'Welcome', 1, 'last_post', 'Z-A', 30, 0, '', 0, 0, 1, 1, NULL, -1, 0, 1)";

    $SQL[] = "INSERT INTO ibf_groups (g_id, g_view_board, g_mem_info, g_other_topics, g_use_search, g_email_friend, g_invite_friend, g_edit_profile, g_post_new_topics, g_reply_own_topics, g_reply_other_topics, g_edit_posts, g_delete_own_posts, g_open_close_posts, g_delete_own_topics, g_post_polls, g_vote_polls, g_use_pm, g_is_supmod, g_access_cp, g_title, g_can_remove, g_append_edit, g_access_offline, g_avoid_q, g_avoid_flood, g_icon, g_attach_max, g_avatar_upload, g_calendar_post, prefix, suffix, g_max_messages, g_max_mass_pm, g_search_flood, g_edit_cutoff, g_promotion, g_hide_from_list, g_post_closed) VALUES (4, 1, 1, 1, 1, 1, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 'Admin', 0, 1, 1, 1, 1, '', 50000, 1, 1, '', '', 50, 6, 0, 5, '-1&-1', 0, 1)";

    $SQL[] = "INSERT INTO ibf_groups (g_id, g_view_board, g_mem_info, g_other_topics, g_use_search, g_email_friend, g_invite_friend, g_edit_profile, g_post_new_topics, g_reply_own_topics, g_reply_other_topics, g_edit_posts, g_delete_own_posts, g_open_close_posts, g_delete_own_topics, g_post_polls, g_vote_polls, g_use_pm, g_is_supmod, g_access_cp, g_title, g_can_remove, g_append_edit, g_access_offline, g_avoid_q, g_avoid_flood, g_icon, g_attach_max, g_avatar_upload, g_calendar_post, prefix, suffix, g_max_messages, g_max_mass_pm, g_search_flood, g_edit_cutoff, g_promotion, g_hide_from_list) VALUES (2, 1, 1, 1, 1, 0, 0, 0, 1, 1, 1, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, 'Guests', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 50, 0, 20, 0, '-1&-1', 0)";

    $SQL[] = "INSERT INTO ibf_groups (g_id, g_view_board, g_mem_info, g_other_topics, g_use_search, g_email_friend, g_invite_friend, g_edit_profile, g_post_new_topics, g_reply_own_topics, g_reply_other_topics, g_edit_posts, g_delete_own_posts, g_open_close_posts, g_delete_own_topics, g_post_polls, g_vote_polls, g_use_pm, g_is_supmod, g_access_cp, g_title, g_can_remove, g_append_edit, g_access_offline, g_avoid_q, g_avoid_flood, g_icon, g_attach_max, g_avatar_upload, g_calendar_post, prefix, suffix, g_max_messages, g_max_mass_pm, g_search_flood, g_edit_cutoff, g_promotion, g_hide_from_list) VALUES (3, 1, 1, 1, 1, 1, 0, 1, 1, 1, 1, 1, 0, 0, 0, 1, 1, 1, 0, 0, 'Members', 0, 1, 0, 0, 0, '', 0, 1, 0, '', '', 50, 0, 20, 0, '-1&-1', 0)";

    $SQL[] = "INSERT INTO ibf_groups (g_id, g_view_board, g_mem_info, g_other_topics, g_use_search, g_email_friend, g_invite_friend, g_edit_profile, g_post_new_topics, g_reply_own_topics, g_reply_other_topics, g_edit_posts, g_delete_own_posts, g_open_close_posts, g_delete_own_topics, g_post_polls, g_vote_polls, g_use_pm, g_is_supmod, g_access_cp, g_title, g_can_remove, g_append_edit, g_access_offline, g_avoid_q, g_avoid_flood, g_icon, g_attach_max, g_avatar_upload, g_calendar_post, prefix, suffix, g_max_messages, g_max_mass_pm, g_search_flood, g_edit_cutoff, g_promotion, g_hide_from_list) VALUES (1, 1, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 'Validating', 0, 1, 0, 0, 0, NULL, 0, 0, 0, NULL, NULL, 50, 0, 20, 0, '-1&-1', 0)";

    $SQL[] = "INSERT INTO ibf_groups (g_id, g_view_board, g_mem_info, g_other_topics, g_use_search, g_email_friend, g_invite_friend, g_edit_profile, g_post_new_topics, g_reply_own_topics, g_reply_other_topics, g_edit_posts, g_delete_own_posts, g_open_close_posts, g_delete_own_topics, g_post_polls, g_vote_polls, g_use_pm, g_is_supmod, g_access_cp, g_title, g_can_remove, g_append_edit, g_access_offline, g_avoid_q, g_avoid_flood, g_icon, g_attach_max, g_avatar_upload, g_calendar_post, prefix, suffix, g_max_messages, g_max_mass_pm, g_search_flood, g_edit_cutoff, g_promotion, g_hide_from_list) VALUES (5, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 'Banned', 0, 0, 0, 0, 0, NULL, NULL, 0, 0, NULL, NULL, 50, 0, 20, 0, '-1&-1', 1)";

    $SQL[] = "INSERT INTO ibf_languages (lid, ldir, lname, lauthor, lemail) VALUES (1, 'en', 'English', 'Invision Power Board', 'languages@invisionboard.com')";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (1, 'A_LOCKED_B', '<img src=\'style_images/<#IMG_DIR#>/t_closed.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (2, 'A_MOVED_B', '<img src=\'style_images/<#IMG_DIR#>/t_moved.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (3, 'A_POLL', '<img src=\'style_images/<#IMG_DIR#>/t_poll.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (4, 'A_POLLONLY_B', '<img src=\'style_images/<#IMG_DIR#>/t_closed.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (5, 'A_POST', '<img src=\'style_images/<#IMG_DIR#>/t_new.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (6, 'A_REPLY', '<img src=\'style_images/<#IMG_DIR#>/t_reply.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (7, 'A_STAR', '<img src=\'style_images/<#IMG_DIR#>/pip.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (8, 'B_HOT', '<img src=\'style_images/<#IMG_DIR#>/f_hot.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (9, 'B_HOT_NN', '<img src=\'style_images/<#IMG_DIR#>/f_hot_no.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (10, 'B_LOCKED', '<img src=\'style_images/<#IMG_DIR#>/f_closed.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (11, 'B_MOVED', '<img src=\'style_images/<#IMG_DIR#>/f_moved.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (12, 'B_NEW', '<img src=\'style_images/<#IMG_DIR#>/f_norm.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (13, 'B_NORM', '<img src=\'style_images/<#IMG_DIR#>/f_norm_no.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (14, 'B_PIN', '<img src=\'style_images/<#IMG_DIR#>/f_pinned.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (15, 'B_POLL', '<img src=\'style_images/<#IMG_DIR#>/f_poll.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (16, 'B_POLL_NN', '<img src=\'style_images/<#IMG_DIR#>/f_poll_no.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (17, 'C_LOCKED', '<img src=\'style_images/<#IMG_DIR#>/bf_readonly.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (18, 'C_OFF', '<img src=\'style_images/<#IMG_DIR#>/bf_nonew.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (19, 'C_OFF_CAT', '<img src=\'style_images/<#IMG_DIR#>/bc_nonew.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (20, 'C_OFF_RES', '<img src=\'style_images/<#IMG_DIR#>/br_nonew.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (21, 'C_ON', '<img src=\'style_images/<#IMG_DIR#>/bf_new.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (22, 'C_ON_CAT', '<img src=\'style_images/<#IMG_DIR#>/bc_new.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (23, 'C_ON_RES', '<img src=\'style_images/<#IMG_DIR#>/br_new.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (24, 'F_ACTIVE', '<img src=\'style_images/<#IMG_DIR#>/user.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (25, 'F_NAV_SEP', ' ->', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (26, 'F_NAV', '<img src=\'style_images/<#IMG_DIR#>/nav.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (27, 'F_STATS', '<img src=\'style_images/<#IMG_DIR#>/stats.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (28, 'GO_LAST_ON', '', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (29, 'GO_LAST_OFF', '', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (30, 'M_ADDMEM', '<img src=\'style_images/<#IMG_DIR#>/msg_l_addmem.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (31, 'M_DELETE', '<img src=\'style_images/<#IMG_DIR#>/msg_l_delete.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (32, 'M_READ', '<img src=\'style_images/<#IMG_DIR#>/f_norm_no.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (33, 'M_REPLY', '<img src=\'style_images/<#IMG_DIR#>/msg_l_reply.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (34, 'M_UNREAD', '<img src=\'style_images/<#IMG_DIR#>/f_norm.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (35, 'P_AOL', '<img src=\'style_images/<#IMG_DIR#>/p_aim.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (36, 'P_DELETE', '<img src=\'style_images/<#IMG_DIR#>/p_delete.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (37, 'P_EDIT', '<img src=\'style_images/<#IMG_DIR#>/p_edit.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (38, 'P_EMAIL', '<img src=\'style_images/<#IMG_DIR#>/p_email.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (39, 'P_ICQ', '<img src=\'style_images/<#IMG_DIR#>/p_icq.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (40, 'P_MSG', '<img src=\'style_images/<#IMG_DIR#>/p_pm.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (41, 'P_PROFILE', '[ Profile ]', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (42, 'P_QUOTE', '<img src=\'style_images/<#IMG_DIR#>/p_quote.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (43, 'P_WEBSITE', '<img src=\'style_images/<#IMG_DIR#>/p_www.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (44, 'CAT_IMG', '<img src=\'style_images/<#IMG_DIR#>/nav_m.gif\' border=\'0\'  alt=\'\' width=\'8\' height=\'8\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (45, 'B_HOT_DOT', '<img src=\'style_images/<#IMG_DIR#>/f_hot_dot.gif\' border=\'0\' alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (46, 'B_NEW_DOT', '<img src=\'style_images/<#IMG_DIR#>/f_norm_dot.gif\' border=\'0\' alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (47, 'B_HOT_NN_DOT', '<img src=\'style_images/<#IMG_DIR#>/f_hot_no_dot.gif\' border=\'0\' alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (48, 'B_NORM_DOT', '<img src=\'style_images/<#IMG_DIR#>/f_norm_no_dot.gif\' border=\'0\' alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (49, 'B_POLL_DOT', '<img src=\'style_images/<#IMG_DIR#>/f_poll_dot.gif\' border=\'0\' alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (50, 'B_POLL_NN_DOT', '<img src=\'style_images/<#IMG_DIR#>/f_poll_no_dot.gif\' border=\'0\' alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (51, 'NEW_POST', '<img src=\'style_images/<#IMG_DIR#>/newpost.gif\' border=\'0\'  alt=\'Goto last unread\' title=\'Goto last unread\' hspace=2>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (52, 'tbl_width', '95%', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (53, 'tbl_border', '#345487', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (55, 'P_YIM', '<img src=\'style_images/<#IMG_DIR#>/p_yim.gif\' border=\'0\' alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (56, 'P_MSN', '<img src=\'style_images/<#IMG_DIR#>/p_msn.gif\' border=\'0\' alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro_name (set_id, set_name) VALUES (1, 'IBF Default Macro Set')";

    $SQL[] = "INSERT INTO ibf_posts (append_edit, edit_time, pid, author_id, author_name, use_sig, use_emo, ip_address, post_date, icon_id, post, queued, topic_id, forum_id, attach_id, attach_hits, attach_type, attach_file, post_title, new_topic, edit_name) VALUES (0, NULL, 1, 1, 'Invision Power Board Team', '0', '1', '127.0.0.1', <%time%>, 0, 'Welcome to your new Invision Power Board!<br>This is simply a test message confirming that the installation was successful.<br>You can remove this message, topic, forum or even category at any time.', 0, 1, 1, '', 0, '', '', NULL, 1, NULL)";

    $SQL[] = "INSERT INTO ibf_skins (uid, sname, sid, set_id, tmpl_id, macro_id, css_id, img_dir, tbl_width, tbl_border, hidden, default_set) VALUES (1, 'Invision Board', 0, 1, 1, 1, 1, '1', '95%', '#999999', 0, 1)";

    $SQL[] = "INSERT INTO ibf_stats (TOTAL_REPLIES, TOTAL_TOPICS, LAST_MEM_NAME, LAST_MEM_ID, MOST_DATE, MOST_COUNT, MEM_COUNT) VALUES (0, 1, '', '1', <%time%>, 1, 1)";

    $SQL[] = "INSERT INTO ibf_templates (tmid, template, name) VALUES (1, '<html>    <head>      <title>                <% TITLE %>      </title>      <% GENERATOR %>      <% CSS %>      <% JAVASCRIPT %>          </head><body bgcolor=\'#FFFFFF\' leftmargin=\"0\" topmargin=\"0\" marginwidth=\"0\" marginheight=\"0\" alink=\'#000000\' vlink=\'#000000\'><% BOARD HEADER %><% NAVIGATION %><% BOARD %><% STATS %><% COPYRIGHT %></body></html>', 'Invision Power Board Standard')";

    $SQL[] = "INSERT INTO ibf_titles (id, posts, title, pips) VALUES (1, 0, 'Newbie', '1')";

    $SQL[] = "INSERT INTO ibf_titles (id, posts, title, pips) VALUES (2, 10, 'Member', '2')";

    $SQL[] = "INSERT INTO ibf_titles (id, posts, title, pips) VALUES (4, 30, 'Advanced Member', '3')";

    $SQL[] = "INSERT INTO ibf_tmpl_names (skid, skname, author, email, url) VALUES (1, 'Invision Power Board Template Set', 'Invision Power Board', 'skins@invisionboard.com', 'http://www.invisionboard.com')";

    $SQL[] = "INSERT INTO ibf_topics (tid, title, description, state, posts, starter_id, start_date, last_poster_id, last_post, icon_id, starter_name, last_poster_name, poll_state, last_vote, views, forum_id, approved, author_mode, pinned, moved_to, rating, total_votes) VALUES (1, 'Welcome', '', 'open', 0, '-1', <%time%>, '0', <%time%>, 0, 'Invision Power Board Team', 'Invision Power Board Team', '0', 0, 0, 1, 1, 0, 0, NULL, NULL, 0)";

    return $SQL;
}

//+---------------------------

function get_sql_upg()
{
    $SQL = [];

    $SQL[] = 'CREATE TABLE ibf_admin_logs (
  id bigint(20) NOT NULL auto_increment,
  act varchar(255) default NULL,
  code varchar(255) default NULL,
  member_id int(10) default NULL,
  ctime int(10) default NULL,
  note text,
  ip_address varchar(255) default NULL,
  PRIMARY KEY  (id)
);';

    $SQL[] = "CREATE TABLE ibf_admin_sessions (
  ID varchar(32) NOT NULL default '',
  IP_ADDRESS varchar(32) NOT NULL default '',
  MEMBER_NAME varchar(32) NOT NULL default '',
  MEMBER_ID varchar(32) NOT NULL default '',
  SESSION_KEY varchar(32) NOT NULL default '',
  LOCATION varchar(64) default 'index',
  LOG_IN_TIME int(10) NOT NULL default '0',
  RUNNING_TIME int(10) NOT NULL default '0',
  PRIMARY KEY  (ID)
);";

    $SQL[] = 'CREATE TABLE ibf_attachments (
  id bigint(20) NOT NULL auto_increment,
  mime_type varchar(128) default NULL,
  file_name varchar(64) default NULL,
  PRIMARY KEY  (id)
);';

    $SQL[] = "CREATE TABLE ibf_badwords (
  wid int(3) NOT NULL auto_increment,
  type varchar(250) NOT NULL default '',
  swop varchar(250) default NULL,
  m_exact tinyint(1) default '0',
  PRIMARY KEY  (wid)
);";

    $SQL[] = "CREATE TABLE ibf_calendar_events (
  eventid bigint(20) NOT NULL auto_increment,
  userid bigint(20) NOT NULL default '0',
  year int(4) NOT NULL default '2002',
  month int(2) NOT NULL default '1',
  mday int(2) NOT NULL default '1',
  title varchar(254) NOT NULL default 'no title',
  event_text text NOT NULL,
  read_perms varchar(254) NOT NULL default '*',
  unix_stamp int(10) NOT NULL default '0',
  priv_event tinyint(1) NOT NULL default '0',
  show_emoticons tinyint(1) NOT NULL default '1',
  rating smallint(2) NOT NULL default '1',
  PRIMARY KEY  (eventid),
  KEY unix_stamp (unix_stamp)
);";

    $SQL[] = "CREATE TABLE ibf_categories (
  id smallint(5) NOT NULL default '0',
  position tinyint(3) default NULL,
  state varchar(10) default NULL,
  name varchar(128) NOT NULL default '',
  description text,
  image varchar(128) default NULL,
  url varchar(128) default NULL,
  PRIMARY KEY  (id),
  KEY id (id)
);";

    $SQL[] = "CREATE TABLE ibf_contacts (
  id bigint(20) NOT NULL auto_increment,
  contact_id varchar(32) NOT NULL default '',
  member_id varchar(32) NOT NULL default '',
  contact_name varchar(32) NOT NULL default '',
  allow_msg tinyint(1) default NULL,
  contact_desc varchar(50) default NULL,
  PRIMARY KEY  (id)
);";

    $SQL[] = "CREATE TABLE ibf_css (
  cssid int(10) NOT NULL auto_increment,
  css_name varchar(128) NOT NULL default '',
  css_text text,
  css_comments text,
  PRIMARY KEY  (cssid)
);";

    $SQL[] = "CREATE TABLE ibf_faq (
  id bigint(20) NOT NULL auto_increment,
  title varchar(128) NOT NULL default '',
  text text,
  description text NOT NULL,
  PRIMARY KEY  (id)
);";

    $SQL[] = "CREATE TABLE ibf_forum_tracker (
  frid bigint(20) NOT NULL auto_increment,
  member_id varchar(32) NOT NULL default '',
  forum_id int(10) NOT NULL default '0',
  start_date int(10) default NULL,
  last_sent int(10) NOT NULL default '0',
  PRIMARY KEY  (frid)
);";

    $SQL[] = "CREATE TABLE ibf_forums (
  id smallint(5) NOT NULL default '0',
  topics mediumint(6) default NULL,
  posts mediumint(6) default NULL,
  last_post int(10) default NULL,
  last_poster_id int(10) default NULL,
  last_poster_name varchar(32) default NULL,
  name varchar(128) NOT NULL default '',
  description text,
  position tinyint(2) default NULL,
  use_ibc tinyint(1) default NULL,
  use_html tinyint(1) default NULL,
  status varchar(10) default NULL,
  start_perms varchar(255) default NULL,
  reply_perms varchar(255) default NULL,
  read_perms varchar(255) default NULL,
  password varchar(32) default NULL,
  category smallint(5) NOT NULL default '0',
  last_title varchar(128) default NULL,
  last_id int(10) default NULL,
  sort_key varchar(32) default NULL,
  sort_order varchar(32) default NULL,
  prune tinyint(3) default NULL,
  show_rules tinyint(1) default NULL,
  upload_perms varchar(255) default NULL,
  preview_posts tinyint(1) default NULL,
  allow_poll tinyint(1) NOT NULL default '1',
  allow_pollbump tinyint(1) NOT NULL default '0',
  inc_postcount tinyint(1) NOT NULL default '1',
  skin_id int(10) default NULL,
  parent_id mediumint(5) default '-1',
  subwrap tinyint(1) default '0',
  sub_can_post tinyint(1) default '1',
  PRIMARY KEY  (id),
  KEY category (category),
  KEY id (id)
);";

    $SQL[] = "CREATE TABLE ibf_groups (
  g_id int(3) unsigned NOT NULL auto_increment,
  g_view_board tinyint(1) default NULL,
  g_mem_info tinyint(1) default NULL,
  g_other_topics tinyint(1) default NULL,
  g_use_search tinyint(1) default NULL,
  g_email_friend tinyint(1) default NULL,
  g_invite_friend tinyint(1) default NULL,
  g_edit_profile tinyint(1) default NULL,
  g_post_new_topics tinyint(1) default NULL,
  g_reply_own_topics tinyint(1) default NULL,
  g_reply_other_topics tinyint(1) default NULL,
  g_edit_posts tinyint(1) default NULL,
  g_delete_own_posts tinyint(1) default NULL,
  g_open_close_posts tinyint(1) default NULL,
  g_delete_own_topics tinyint(1) default NULL,
  g_post_polls tinyint(1) default NULL,
  g_vote_polls tinyint(1) default NULL,
  g_use_pm tinyint(1) default NULL,
  g_is_supmod tinyint(1) default NULL,
  g_access_cp tinyint(1) default NULL,
  g_title varchar(32) NOT NULL default '',
  g_can_remove tinyint(1) default NULL,
  g_append_edit tinyint(1) default NULL,
  g_access_offline tinyint(1) default NULL,
  g_avoid_q tinyint(1) default NULL,
  g_avoid_flood tinyint(1) default NULL,
  g_icon varchar(64) default NULL,
  g_attach_max bigint(20) default NULL,
  g_avatar_upload tinyint(1) default '0',
  g_calendar_post tinyint(1) default '0',
  prefix varchar(250) default NULL,
  suffix varchar(250) default NULL,
  g_max_messages int(5) default '50',
  g_max_mass_pm int(5) default '0',
  g_search_flood mediumint(6) default '20',
  g_edit_cutoff int(10) default '0',
  g_promotion varchar(10) default '-1&-1',
  g_hide_from_list tinyint(1) default '0',
  g_post_closed tinyint(1) default '0',
  PRIMARY KEY  (g_id)
);";

    $SQL[] = "CREATE TABLE ibf_languages (
  lid bigint(20) NOT NULL auto_increment,
  ldir varchar(64) NOT NULL default '',
  lname varchar(250) NOT NULL default '',
  lauthor varchar(250) default NULL,
  lemail varchar(250) default NULL,
  PRIMARY KEY  (lid)
);";

    $SQL[] = "CREATE TABLE ibf_macro (
  macro_id smallint(3) NOT NULL auto_increment,
  macro_value varchar(200) default NULL,
  macro_replace text,
  can_remove tinyint(1) default '0',
  macro_set smallint(3) NOT NULL,
  PRIMARY KEY  (macro_id),
  KEY macro_set (macro_set)
);";

    $SQL[] = "CREATE TABLE ibf_macro_name (
  set_id smallint(3) NOT NULL default '0',
  set_name varchar(200) default NULL,
  PRIMARY KEY  (set_id)
);";

    $SQL[] = "CREATE TABLE ibf_member_extra (
  id bigint(20) NOT NULL default '0',
  notes text,
  links text,
  bio text,
  ta_size char(3) default NULL,
  PRIMARY KEY  (id)
);";

    $SQL[] = "CREATE TABLE ibf_messages (
  msg_id bigint(20) NOT NULL auto_increment,
  msg_date int(10) default NULL,
  read_state tinyint(1) default NULL,
  title varchar(128) default NULL,
  message text,
  from_id varchar(32) default NULL,
  vid varchar(32) default NULL,
  member_id varchar(32) NOT NULL default '0',
  recipient_id varchar(32) default NULL,
  attach_type tinyint(128) default NULL,
  attach_file tinyint(128) default NULL,
  cc_users text,
  tracking tinyint(1) default '0',
  read_date int(10) default NULL,
  PRIMARY KEY  (msg_id),
  KEY member_id (member_id),
  KEY vid (vid)
);";

    $SQL[] = "CREATE TABLE ibf_moderator_logs (
  id bigint(20) NOT NULL auto_increment,
  forum_id int(5) default '0',
  topic_id bigint(20) default '0',
  post_id bigint(20) default '0',
  member_id varchar(32) NOT NULL default '',
  member_name varchar(32) NOT NULL default '',
  ip_address varchar(32) default NULL,
  http_referer varchar(255) default NULL,
  ctime int(10) default NULL,
  topic_title varchar(128) default NULL,
  action varchar(128) default NULL,
  query_string varchar(128) default NULL,
  PRIMARY KEY  (id)
);";

    $SQL[] = "CREATE TABLE ibf_moderators (
  mid bigint(20) NOT NULL auto_increment,
  forum_id int(5) NOT NULL default '0',
  member_name varchar(32) NOT NULL default '',
  member_id varchar(32) NOT NULL default '0',
  edit_post tinyint(1) default NULL,
  edit_topic tinyint(1) default NULL,
  delete_post tinyint(1) default NULL,
  delete_topic tinyint(1) default NULL,
  view_ip tinyint(1) default NULL,
  open_topic tinyint(1) default NULL,
  close_topic tinyint(1) default NULL,
  mass_move tinyint(1) default NULL,
  mass_prune tinyint(1) default NULL,
  move_topic tinyint(1) default NULL,
  pin_topic tinyint(1) default NULL,
  unpin_topic tinyint(1) default NULL,
  post_q tinyint(1) default NULL,
  topic_q tinyint(1) default NULL,
  allow_warn tinyint(1) default NULL,
  edit_user tinyint(1) NOT NULL default '0',
  is_group tinyint(1) default '0',
  group_id smallint(3) NOT NULL default '',
  group_name varchar(200) default NULL,
  split_merge tinyint(1) default '0',
  PRIMARY KEY  (mid),
  KEY forum_id (forum_id),
  KEY group_id (group_id),
  KEY member_id (member_id)
);";

    $SQL[] = "CREATE TABLE ibf_pfields_content (
  member_id bigint(20) NOT NULL default '0',
  updated int(10) default '0',
  PRIMARY KEY  (member_id)
);";

    $SQL[] = "CREATE TABLE ibf_pfields_data (
  fid smallint(5) NOT NULL auto_increment,
  ftitle varchar(200) NOT NULL default '',
  fdesc varchar(250) default '',
  fcontent text,
  ftype varchar(250) default 'text',
  freq tinyint(1) default '0',
  fhide tinyint(1) default '0',
  fmaxinput smallint(6) default '250',
  fedit tinyint(1) default '1',
  forder smallint(6) default '1',
  fshowreg tinyint(1) default '0',
  PRIMARY KEY  (fid)
);";

    $SQL[] = "CREATE TABLE ibf_polls (
  pid bigint(20) NOT NULL auto_increment,
  tid bigint(20) NOT NULL default '0',
  start_date int(10) default NULL,
  choices text,
  starter_id varchar(32) default NULL,
  votes bigint(20) default NULL,
  forum_id bigint(20) default NULL,
  poll_question varchar(255) default NULL,
  PRIMARY KEY  (pid)
);";

    $SQL[] = "CREATE TABLE ibf_posts (
  append_edit tinyint(1) default '0',
  edit_time int(10) default NULL,
  pid bigint(20) NOT NULL auto_increment,
  author_id int(10) NOT NULL default '0',
  author_name varchar(32) default NULL,
  use_sig varchar(8) default NULL,
  use_emo varchar(8) default NULL,
  ip_address varchar(32) default NULL,
  post_date int(10) NOT NULL default '',
  icon_id smallint(3) default NULL,
  post text,
  queued tinyint(1) default NULL,
  topic_id bigint(20) NOT NULL default '0',
  forum_id int(10) NOT NULL default '0',
  attach_id varchar(64) default NULL,
  attach_hits int(10) default NULL,
  attach_type varchar(128) default NULL,
  attach_file varchar(255) default NULL,
  post_title varchar(255) default NULL,
  new_topic tinyint(1) default '0',
  edit_name varchar(255) default NULL,
  PRIMARY KEY  (pid),
  KEY topic_id (topic_id,author_id),
  KEY author_id (author_id),
  KEY forum_id (forum_id,post_date)
);";

    $SQL[] = "CREATE TABLE ibf_reg_antispam (
  regid varchar(32) NOT NULL default '',
  regcode varchar(8) NOT NULL default '',
  ip_address varchar(32) default NULL,
  ctime int(10) default NULL,
  PRIMARY KEY  (regid)
);";

    $SQL[] = "CREATE TABLE ibf_rules (
  fid mediumint(6) NOT NULL default '0',
  title varchar(128) NOT NULL default '',
  body text,
  updated int(10) default NULL,
  show_all tinyint(1) default NULL,
  PRIMARY KEY  (fid)
);";

    $SQL[] = "CREATE TABLE ibf_search_results (
  id varchar(32) NOT NULL default '',
  topic_id text NOT NULL,
  search_date int(12) NOT NULL default '0',
  topic_max int(3) NOT NULL default '0',
  sort_key varchar(32) NOT NULL default 'last_post',
  sort_order varchar(4) NOT NULL default 'desc',
  member_id mediumint(10) default '0',
  ip_address varchar(64) default NULL,
  post_id text,
  post_max int(10) NOT NULL default '0'
);";

    $SQL[] = "CREATE TABLE ibf_skin_templates (
  suid int(10) NOT NULL auto_increment,
  set_id int(10) NOT NULL default '0',
  group_name varchar(255) NOT NULL default '',
  section_content mediumtext,
  func_name varchar(255) default NULL,
  func_data text,
  updated int(10) default NULL,
  can_remove tinyint(4) default '0',
  PRIMARY KEY  (suid)
);";

    $SQL[] = "CREATE TABLE ibf_skins (
  uid int(10) NOT NULL auto_increment,
  sname varchar(100) NOT NULL default '',
  sid int(10) NOT NULL default '0',
  set_id int(5) NOT NULL default '0',
  tmpl_id int(10) NOT NULL default '0',
  macro_id int(10) NOT NULL default '1',
  css_id int(10) NOT NULL default '1',
  img_dir varchar(200) default '1',
  tbl_width varchar(250) default NULL,
  tbl_border varchar(250) default NULL,
  hidden tinyint(1) NOT NULL default '0',
  default_set tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (uid),
  KEY tmpl_id (tmpl_id),
  KEY css_id (css_id)
);";

    $SQL[] = "CREATE TABLE ibf_stats (
  TOTAL_REPLIES bigint(20) NOT NULL default '0',
  TOTAL_TOPICS bigint(20) NOT NULL default '0',
  LAST_MEM_NAME varchar(32) default NULL,
  LAST_MEM_ID varchar(32) default NULL,
  MOST_DATE int(10) default NULL,
  MOST_COUNT int(10) default '0',
  MEM_COUNT bigint(20) default '0'
);";

    $SQL[] = 'CREATE TABLE ibf_templates (
  tmid int(10) NOT NULL auto_increment,
  template mediumtext,
  name varchar(128) default NULL,
  PRIMARY KEY  (tmid)
);';

    $SQL[] = 'CREATE TABLE ibf_titles (
  id smallint(5) NOT NULL auto_increment,
  posts int(10) default NULL,
  title varchar(128) default NULL,
  pips varchar(128) default NULL,
  PRIMARY KEY  (id)
);';

    $SQL[] = "CREATE TABLE ibf_tmpl_names (
  skid int(10) NOT NULL auto_increment,
  skname varchar(60) NOT NULL default 'Invision P Board',
  author varchar(250) default '',
  email varchar(250) default '',
  url varchar(250) default '',
  PRIMARY KEY  (skid)
);";

    $SQL[] = "CREATE TABLE ibf_topics (
  tid bigint(20) NOT NULL auto_increment,
  title varchar(70) NOT NULL default '',
  description varchar(70) default NULL,
  state varchar(8) default NULL,
  posts smallint(4) default NULL,
  starter_id varchar(32) default NULL,
  start_date int(10) default NULL,
  last_poster_id varchar(32) default NULL,
  last_post int(10) default '0' NOT NULL,
  icon_id tinyint(2) default NULL,
  starter_name varchar(32) default NULL,
  last_poster_name varchar(32) default NULL,
  poll_state varchar(8) default NULL,
  last_vote int(10) default NULL,
  views smallint(5) default NULL,
  forum_id smallint(5) NOT NULL default '0',
  approved tinyint(1) NOT NULL default '0',
  author_mode tinyint(1) default NULL,
  pinned tinyint(1) NOT NULL default '0',
  moved_to varchar(64) default NULL,
  rating text,
  total_votes int(5) NOT NULL default '0',
  PRIMARY KEY  (tid),
  KEY forum_id (forum_id,approved,pinned,last_post)
);";

    $SQL[] = "CREATE TABLE ibf_tracker (
  trid bigint(20) NOT NULL auto_increment,
  member_id varchar(32) NOT NULL default '',
  topic_id bigint(20) NOT NULL default '0',
  start_date int(10) default NULL,
  last_sent int(10) NOT NULL default '0',
  PRIMARY KEY  (trid)
);";

    $SQL[] = "CREATE TABLE ibf_voters (
  vid bigint(20) NOT NULL auto_increment,
  ip_address varchar(16) NOT NULL default '',
  vote_date int(10) NOT NULL default '0',
  tid bigint(20) NOT NULL default '0',
  member_id varchar(32) default NULL,
  forum_id bigint(20) default NULL,
  PRIMARY KEY  (vid)
);";

    // BEgin updates

    $SQL[] = 'ALTER TABLE xbb_users RENAME xbb_users_old ';

    $SQL[] = 'DROP TABLE IF EXISTS xbb_ipb_sessions';

    $SQL[] = "CREATE TABLE xbb_users (
  uid mediumint(8) unsigned NOT NULL auto_increment,
  name varchar(60) NOT NULL default '',
  uname varchar(25) NOT NULL default '',
  email varchar(60) NOT NULL default '',
  url varchar(100) NOT NULL default '',
  user_avatar varchar(30) NOT NULL default 'blank.gif',
  user_regdate int(10) unsigned NOT NULL default '0',
  user_icq varchar(15) NOT NULL default '',
  user_from varchar(100) NOT NULL default '',
  user_sig tinytext NOT NULL,
  user_viewemail tinyint(1) unsigned NOT NULL default '0',
  actkey varchar(8) NOT NULL default '',
  user_aim varchar(18) NOT NULL default '',
  user_yim varchar(25) NOT NULL default '',
  user_msnm varchar(100) NOT NULL default '',
  pass varchar(32) NOT NULL default '',
  posts mediumint(8) unsigned NOT NULL default '0',
  attachsig tinyint(1) unsigned NOT NULL default '0',
  rank smallint(5) unsigned NOT NULL default '0',
  level tinyint(3) unsigned NOT NULL default '1',
  theme varchar(100) NOT NULL default '',
  timezone_offset float(3,1) NOT NULL default '0.0',
  last_login int(10) unsigned NOT NULL default '0',
  umode varchar(10) NOT NULL default '',
  uorder tinyint(1) unsigned NOT NULL default '0',
  user_occ varchar(100) NOT NULL default '',
  bio tinytext NOT NULL,
  user_intrest varchar(150) NOT NULL default '',
  user_mailok tinyint(1) unsigned NOT NULL default '1',
  PRIMARY KEY  (uid),
  KEY uname (uname),
  KEY email (email),
  KEY uiduname (uid,uname),
  KEY unamepass (uname,pass)
);";

    $SQL[] = "CREATE TABLE xbb_session (
  sess_id varchar(32) NOT NULL default '',
  sess_updated int(10) unsigned NOT NULL default '0',
  sess_ip varchar(15) NOT NULL default '',
  sess_data text NOT NULL,
	member_name varchar(64) default NULL,
	member_id varchar(32) default NULL,
	browser varchar(64) default NULL,
	login_type tinyint(1) default NULL,
	location varchar(40) default NULL,
	member_group smallint(3) default NULL,
	in_forum int(10) NOT NULL default '0',
	in_topic int(10) NOT NULL default '0', 
  PRIMARY KEY  (sess_id),
  	KEY in_topic (in_topic),
	KEY in_forum (in_forum),
  KEY updated (sess_updated)
) ENGINE = ISAM; 
";

    $SQL[] = "ALTER TABLE xbb_users
CHANGE uid uid bigint(10) unsigned NOT NULL auto_increment default '0',
CHANGE uname uname varchar(32) NOT NULL default '',
CHANGE user_avatar user_avatar varchar(128),
CHANGE user_aim user_aim varchar(40) default NULL,
CHANGE user_icq user_icq varchar(40) default NULL,
CHANGE user_from user_from varchar(128) default NULL,
CHANGE user_yim user_yim varchar(32) default NULL,
CHANGE user_viewemail user_viewemail varchar(8) default NULL,
CHANGE user_msnm user_msnm varchar(64) default NULL,
CHANGE user_intrest user_intrest text,
CHANGE attachsig attachsig tinyint(1) default '1',
CHANGE actkey actkey varchar(8) NULL default '' ,
CHANGE user_occ user_occ varchar(100) NULL default '',

ADD signature text,
ADD avatar_size varchar(9) default NULL,
ADD mgroup tinyint(2) NOT NULL default '3',
ADD ip_address varchar(16) NOT NULL default '',
ADD title varchar(64) default NULL,
ADD allow_admin_mails tinyint(1) default NULL,
ADD email_pm tinyint(1) default NULL,
ADD email_full tinyint(1) default NULL,
ADD skin smallint(5) default NULL,
ADD warn_level int(10) default NULL,
ADD language varchar(32) default NULL,
ADD last_post int(10) default NULL,
ADD allow_post tinyint(1) default '1',
ADD view_img tinyint(1) default '1',
ADD view_avs tinyint(1) default '1',
ADD view_pop tinyint(1) default '1',
ADD bday_day int(2) default NULL,
ADD bday_month int(2) default NULL,
ADD bday_year int(4) default NULL,
ADD new_msg tinyint(2) default NULL,
ADD msg_from_id varchar(32) default NULL,
ADD msg_msg_id int(10) default NULL,
ADD msg_total smallint(5) default NULL,
ADD vdirs text,
ADD show_popup tinyint(1) default NULL,
ADD validate_key varchar(32) default NULL,
ADD prev_group smallint(3) default '0',
ADD new_pass varchar(32) default NULL,
ADD misc varchar(128) default NULL,
ADD last_visit int(10) default '0',
ADD last_activity int(10) default '0',
ADD dst_in_use tinyint(1) default '0',
ADD view_prefs varchar(64) default '-1&-1',
ADD coppa_user tinyint(1) default '0',
ADD mod_posts tinyint(1) default '0',
ADD auto_track tinyint(1) default '0',
ADD INDEX mgroup (mgroup),
ADD INDEX bday_day (bday_day),
ADD INDEX bday_month (bday_month)
";

    $SQL[] = 'DELETE FROM xbb_users;';

    $SQL[] = 'DELETE FROM ibf_voters;';

    $SQL[] = 'DELETE FROM ibf_tracker;';

    $SQL[] = 'DELETE FROM ibf_topics;';

    $SQL[] = 'DELETE FROM ibf_titles;';

    $SQL[] = 'DELETE FROM ibf_stats;';

    $SQL[] = 'DELETE FROM ibf_posts;';

    $SQL[] = 'DELETE FROM ibf_polls;';

    $SQL[] = 'DELETE FROM ibf_moderator_logs;';

    $SQL[] = 'DELETE FROM ibf_moderators;';

    $SQL[] = 'DELETE FROM ibf_messages;';

    $SQL[] = 'DELETE FROM ibf_member_extra;';

    $SQL[] = 'DELETE FROM ibf_languages;';

    $SQL[] = 'DELETE FROM ibf_groups;';

    $SQL[] = 'DELETE FROM ibf_forums;';

    $SQL[] = 'DELETE FROM ibf_categories;';

    $SQL[] = 'INSERT INTO ibf_attachments  SELECT * FROM xbb_attachments;';

    $SQL[] = 'INSERT INTO ibf_badwords  SELECT * FROM xbb_badwords;';

    $SQL[] = 'INSERT INTO ibf_categories  SELECT * FROM xbb_categories;';

    $SQL[] = 'INSERT INTO ibf_contacts  SELECT * FROM xbb_contacts;';

    $SQL[] = 'INSERT INTO ibf_forums (id, topics, posts, last_post, last_poster_id, last_poster_name, name, description, position, use_ibc, use_html, status, start_perms, reply_perms, read_perms, password, category, last_title, last_id, sort_key, sort_order, prune, show_rules, preview_posts, allow_poll, allow_pollbump, inc_postcount, skin_id, parent_id, subwrap) 
	SELECT id, topics, posts, last_post, last_poster_id, last_poster_name, name, description, position, use_ibc, use_html, status, start_perms, reply_perms, read_perms, password, category, last_title, last_id, sort_key, sort_order, prune, show_rules, preview_posts, allow_poll, allow_pollbump, inc_postcount, skin_id, parent_id, subwrap
	FROM xbb_forums;';

    $SQL[] = 'INSERT INTO ibf_groups (g_id, g_view_board, g_mem_info, g_other_topics, g_use_search, g_email_friend, g_invite_friend, g_edit_profile, g_post_new_topics, g_reply_own_topics, g_reply_other_topics, g_edit_posts, g_delete_own_posts, g_open_close_posts, g_delete_own_topics, g_post_polls, g_vote_polls, g_use_pm, g_is_supmod, g_access_cp, g_title, g_can_remove, g_append_edit, g_access_offline, g_avoid_q, g_avoid_flood, g_icon, g_attach_max, g_avatar_upload, g_calendar_post, prefix, suffix)
	SELECT * FROM xbb_groupsib;';

    $SQL[] = 'INSERT INTO ibf_languages SELECT * FROM xbb_languages;';

    $SQL[] = 'INSERT INTO ibf_member_extra SELECT * FROM xbb_member_extra;';

    $SQL[] = 'INSERT INTO ibf_messages (msg_id, msg_date, read_state, title, message, from_id, vid, member_id, recipient_id, attach_type, attach_file) 
	SELECT * FROM xbb_messages;';

    $SQL[] = 'INSERT INTO ibf_moderator_logs SELECT * FROM xbb_moderator_logs;';

    $SQL[] = 'INSERT INTO ibf_moderators (mid, forum_id, member_name, member_id, edit_post, edit_topic, delete_post, delete_topic, view_ip, open_topic, close_topic, mass_move, mass_prune, move_topic, pin_topic, unpin_topic, post_q, topic_q, allow_warn, edit_user)
	SELECT * FROM xbb_moderators;';

    $SQL[] = 'INSERT INTO ibf_polls (pid, tid, start_date, choices, starter_id, votes, forum_id) 
	SELECT * FROM xbb_polls;';

    $SQL[] = 'INSERT INTO ibf_posts (pid, author_id, author_name, use_sig, use_emo, ip_address, post_date, icon_id, post, queued, topic_id, forum_id, attach_id, attach_hits, attach_type, attach_file, post_title, new_topic) 
	SELECT * FROM xbb_posts;';

    $SQL[] = 'INSERT INTO ibf_rules SELECT * FROM xbb_rules;';

    $SQL[] = 'INSERT INTO ibf_stats SELECT * FROM xbb_stats;';

    $SQL[] = 'INSERT INTO ibf_titles SELECT * FROM xbb_titles;';

    $SQL[] = 'INSERT INTO ibf_topics SELECT * FROM xbb_topicsib;';

    $SQL[] = 'INSERT INTO ibf_tracker SELECT * FROM xbb_tracker;';

    $SQL[] = 'INSERT INTO ibf_voters SELECT * FROM xbb_voters;';

    $SQL[] = 'INSERT INTO xbb_users (uid, name, uname, email, url, user_avatar, user_regdate, user_icq, user_from, signature, user_viewemail, actkey, user_aim, user_yim, user_msnm, pass, posts, attachsig, rank, level, theme, timezone_offset, last_login, umode, uorder, user_occ, bio, user_intrest, mgroup, ip_address, avatar_size, title, allow_admin_mails, email_pm, email_full, skin, warn_level, language, last_post, allow_post, view_img, view_avs, view_pop, bday_day, bday_month, bday_year, new_msg, msg_from_id, msg_msg_id, msg_total, vdirs, show_popup, validate_key, prev_group, new_pass, misc, last_visit, last_activity, dst_in_use)
	SELECT uid, name, uname, email, url, user_avatar, user_regdate, user_icq, user_from, user_sig, user_viewemail, actkey, user_aim, user_yim, user_msnm, pass, posts, attachsig, rank, level, theme, timezone_offset, last_login, umode, uorder, user_occ, bio, user_intrest, mgroup, ip_address, avatar_size,title, allow_admin_mails, email_pm, email_full, skin, warn_level, language, last_post, allow_post, view_img, view_avs, view_pop, bday_day, bday_month, bday_year, new_msg, msg_from_id, msg_msg_id, msg_total, vdirs, show_popup, validate_key, prev_group, new_pass, misc, last_visit, last_activity, dst_in_use 
	FROM xbb_users_old WHERE uid >0 ;';

    $SQL[] = 'DROP TABLE  IF EXISTS 
xbb_users_old, 
xbb_voters, 
xbb_tracker, 
xbb_topicsib, 
xbb_titles, 
xbb_stats, 
xbb_search_results, 
xbb_skin_templates, 
xbb_skins, 
xbb_templates, 
xbb_tmpl_names, 
xbb_images, 
xbb_rules, 
xbb_posts, 
xbb_polls, 
xbb_moderators, 
xbb_moderator_logs, 
xbb_admin_sessions, 
xbb_css, 
xbb_faq, 
xbb_attachments, 
xbb_badwords, 
xbb_categories, 
xbb_contacts, 
xbb_groupsib, 
xbb_languages, 
xbb_member_extra, 
xbb_messages, 
xbb_forums
';

    // END update

    $SQL[] = "INSERT INTO ibf_css (cssid, css_name, css_text, css_comments) VALUES (1, 'Invision Style Sheet', 'form { display:inline }\r\nTABLE, TR, TD { font-family: Verdana, Tahoma, Arial; font-size: 8.5pt; color: #000000 }\r\na:link, a:visited, a:active { text-decoration: underline; color: #000000 }\r\na:hover { color: #465584 }\r\n.hlight { background-color: #DFE6EF }\r\n.dlight { background-color: #EEF2F7 }\r\n.mainbg { background-color: #FFFFFF }\r\n.mainfoot { background-color: #BCD0ED }\r\n.forum1 { background-color: #DFE6EF }\r\n.forum2 { background-color: #E4EAF2 }\r\n.post1 { background-color: #F5F9FD }\r\n.post2 { background-color: #EEF2F7 }\r\n.posthead { background-color: #E4EAF2 }\r\n\r\n.postbak { background-color: #D2D2D0 }\r\n.title { background-color: #C4DCF7 }\r\n.row1 { background-color: #EEF2F7 }\r\n.row2 { background-color: #F5F9FD }\r\n.postsep { background-color: #C7D2E0; height: 1px }\r\n\r\n.signature { font-size: 7.5pt; color: #333399 }\r\n.postdetails { font-size: 7.5pt }\r\n.postcolor, #postcolor { font-size: 9pt; line-height: 160% }\r\n.membertitle { font-size: 10px; line-height: 150%; color: #000000 }\r\n.normalname { font-size: 12px; font-weight: bold; color: #000033; padding-bottom: 2px }\r\n.normalname a:link, .normalname a:visited, .normalname a:active { text-decoration: underline; color: #000033; padding-bottom: 2px }\r\n.unreg { font-size: 11px; font-weight: bold; color: #990000 }\r\n.highlight { color: #FF0000 }\r\n.highlight a:link, .highlight a:visited, .highlight a:active { text-decoration: underline; color: #FF0000 }\r\n.highlight a:hover { text-decoration: underline }\r\n.desc { font-size: 8.0pt; color: #434951 }\r\n.copyright { font-family: Verdana, Tahoma, Arial; font-size: 7.5pt; line-height: 12px }\r\n.category { font-weight: bold; line-height: 150%; color: #4C77B6; background-color: #C2CFDF }\r\n.category   a:link, #category   a:visited, #category   a:active { text-decoration: none; color: #4C77B6 }\r\n.postfoot         {\r\n\r\n    font-weight:bold;\r\n\r\n    color:#3A4F6C;\r\n\r\n    height: 24px;\r\n\r\n    background-color: #D1DCEB;\r\n\r\n}\r\n.titlefoot { font-weight: bold; color: #3A4F6C; height: 24px; background-color: #BCD0ED }\r\n.titlemedium         {\r\n\r\n    font-weight:bold;\r\n    color:#3A4F6C;\r\n\r\n    height: 30px;\r\n\r\n    background-color: #9FBCE3;\r\n    \r\n    background-image: url(style_images/<#IMG_DIR#>/tile_sub.gif);\r\n\r\n}\r\n.titlemedium  a:link,  .titlefoot  a:link, .titlemedium  a:visited, .titlefoot  a:visited, .titlemedium  a:active, .titlefoot  a:active { text-decoration: underline; color: #3A4F6C }\r\n.titlemedium a:hover, .subtitle a:hover, .titlefoot a:hover { text-decoration: underline; color: #000000 }\r\n.maintitle         {\r\n\r\n    color:#FFFFFF;\r\n\r\n    font-size: 9.5pt;\r\n    \r\n    height: 26px;\r\n    \r\n    background-image: url(style_images/<#IMG_DIR#>/tile_back.gif);\r\n\r\n}\r\n.edit { font-size: 9px }\r\n.fancyborder { border: 1px dashed #999999 }\r\n.solidborder { border: 1px solid #999999 }\r\n.maintitle  a:link, .maintitle  a:visited, .maintitle  a:active { text-decoration: none; color: #FFFFFF }\r\n.maintitle a:hover { text-decoration: underline }\r\n.nav { font-weight: bold; color: #000000; font-size: 8.5pt }\r\n.pagetitle { color: #4C77B6; font-size: 18px; font-weight: bold; letter-spacing: -1px; line-height: 120% }\r\n.useroptions { background-color: #598CC3; height: 25px; font-weight: bold; color: #FFFFFF }\r\n.useroptions a:link, .useroptions a:visited,.useroptions a:active { text-decoration: none; color: #FFFFFF }\r\n.bottomborder { border-bottom: 1px dashed #D2D2D0 }\r\n.linkthru { color: #000000; font-size:8.5pt }\r\n.linkthru  a:link, .linkthru  a:active { text-decoration: underline; color: #000000 }\r\n.linkthru  a:visited { text-decoration: underline; color: #000000 }\r\n.linkthru  a:hover { text-decoration: underline; color: #465584 }\r\n#QUOTE { font-family: Verdana, Arial; font-size: 8pt; color: #333333; background-color: #FAFCFE; border: 1px solid Black; padding-top: 2px; padding-right: 2px; padding-bottom: 2px; padding-left: 2px }\r\n#CODE { font-family: Verdana, Arial; font-size: 8pt; color: #333333; background-color: #FAFCFE; border: 1px solid Black; padding-top: 2px; padding-right: 2px; padding-bottom: 2px; padding-left: 2px }\r\n.codebuttons { font-size: 8.5pt; font-family: verdana, helvetica, sans-serif; vertical-align: middle }\r\n.forminput { font-size: 9pt; font-family: verdana, helvetica, sans-serif; vertical-align: middle }\r\n.textinput { font-size: 9pt; font-family: verdana, helvetica, sans-serif; vertical-align: middle }\r\n.input { font-size: 9pt; font-family: verdana, helvetica, sans-serif; vertical-align: middle }\r\n', NULL)";

    $SQL[] = "INSERT INTO ibf_faq (id, title, text, description) VALUES (1, 'Registration benefits', 'To be able to use all the features on this board, the administrator will probably require that you register for a member account. Registration is free and only takes a moment to complete.\r<br>\r<br>During registration, the adminstrator requires that you supply a valid email address. This is important as the administrator may require that you validate your registration via an email. If this is the case, you will be notified when registering.\r<br>In some cases, the administrator will need to approve your regsitration before you can use your member account fully. If this is the case you will be notified during registration.\r<br>\r<br>Once you have registered and logged in, you will have access to your private messenger and your control panel.\r<br>\r<br>For more information on these items, please see the relevant sections in this documentation.', 'How to register and the added benefits of being a registered member.')";

    $SQL[] = "INSERT INTO ibf_faq (id, title, text, description) VALUES (2, 'Cookies and cookie usage', 'Using cookies is optional, but strongly recommended. Cookies are used to track topics, showing you which topics have new replies since your last visit and to automatically log you in when you return.\r<br>\r<br>This board will also allow you to remove the long session id\'s found in the URL\'s. You can do this by entering your control panel (click the link at the top of the board) and entering the \'Board Settings\' section. It is recommended that you enable this feature because you may experience problems if your internet connection shares a proxy and you share a link with another user. If in doubt, choose to hide the session id.\r<br>\r<br><b>Clearing Cookies</b>\r<br>\r<br>You can clear the cookies at any time by clicking on the link found at the bottom of the main board page (the first page you see when returning to the board). If this does not work for you, you may need to remove the cookies manually.\r<br>\r<br><u>Removing Cookies in Internet Explorer for Windows</u>\r<br>\r<br><ul>\r<br><li> Close all open Internet Explorer Windows\r<br><li> Click on the \'start\' button\r<br><li> Move up to \'Find\' and click on \'Files and Folders\'\r<br><li> When the new window appears, type in the domain name of the board you are using into the \'containing text\' field. (If the boards address was \'http://www.invisionboard.com/forums/index.php\' you would enter \'invisionboard.com\' without the quotes)\r<br><li> In the \'look in\' box, type in <b>C:&#92;Windows&#92;Cookies</b> and press \'Find Now\'\r<br><li> After it has finished searching, highlight all files (click on a file then press CTRL+A) and delete them.\r<br></ul>\r<br>\r<br><u>Removing Cookies in Internet Explorer for Macintosh</u>\r<br>\r<br><ul>\r<br><li> With Internet Explorer active, choose \'Edit\' and then \'Preferences\' from the Macintosh menu bar at the top of the screen\r<br><li> When the preferences panel opens, choose \'Cookies\' found in the \'Receiving Files\' section.\r<br><li> When the cookie pane loads, look for the domain name of the board (If the boards address was \'http://www.invisionboard.com/forums/index.php\' look for \'invisionboard.com\' or \'www.invisionboard.com\'\r<br><li> For each cookie, click on the entry and press the delete button.\r<br></ul>\r<br>\r<br>Your cookies should now be removed. In some cases you may need to restart your computer for the changes to take effect.', 'The benefits of using cookies and how to remove cookies set by this board.')";

    $SQL[] = "INSERT INTO ibf_faq (id, title, text, description) VALUES (3, 'Recovering lost or forgotten passwords', 'Security is a big feature on this board, and to that end, all passwords are encrypted when you register.\r<br>This means that we cannot email your password to you as we hold no record of your \'uncrypted\' password. You can however, apply to have your password reset.\r<br>\r<br>To do this, click on the <a href=\'index.php?act=Reg&CODE=10\'>Lost Password link</a> found on the log in page.\r<br>\r<br>Further instruction is available from there.', 'How to reset your password if you\'ve forgotton it.')";

    $SQL[] = "INSERT INTO ibf_faq (id, title, text, description) VALUES (4, 'Your Control Panel', 'Your control panel is your own private board console. You can change how the board looks and feels as well as your own information from here.\r<br>\r<br><b>Edit Profile Info</b>\r<br>\r<br>This section allows you to add or edit your contact information and enter some personal information if you choose. All of this information is optional and can be omitted.\r<br>\r<br><b>Edit Signature</b>\r<br>\r<br>A board \'signature\' is very similar to an email signature. This signature is attached to the foot of every message you post unless you choose to check the box that allows you to ommit the signature in the message you are posting. You may use BB Code if available and in some cases, pure HTML (if the board administrator allows it).\r<br>\r<br><b>Edit Avatar Settings</b>\r<br>\r<br>An avatar is a little image that appears under your username when you view a topic or post you authored. If the administrator allows, you may either choose from the board gallery, enter a URL to an avatar stored on your server or upload an avatar to use. You may also set the width of the avatar to ensure that it\'s sized in proportion.\r<br>\r<br><b>Email Settings</b>\r<br>\r<br><u>Hide my email address</u> allows you to deny the ability for other users to send you an email from the board.\r<br><u>Send me updates sent by the board administrator</u> will allow the administrator to include your email address in any mailings they send out - this is used mostly for important updates and community information.\r<br><u>Include a copy of the post when emailing me from a subscribed topic</u>, this allows you to have the new post included in any reply to topic notifications.\r<br>\r<br><b>Board Settings</b>\r<br>\r<br>From this section, you can set your timezone, choose to not see users signatures, avatars and posted images.\r<br>You can choose to get a pop up window informing you when you have a new message and hide the long session ID in links (see the \'Cookies\' help file for more infomation on this).\r<br>\r<br><b>Skins and Languages</b>\r<br>\r<br>If available, you can choose a skin style and language choice. This affects how the board is displayed so you may wish to preview the skin before submitting the form.\r<br>\r<br><b>Change Email Address</b>\r<br>\r<br>At any time, you can change the email address that is registered to your account. In some cases, you will need to revalidate your account after changing your email address. If this is the case, you will be notified before your email address change is processed.\r<br>\r<br><b>Change Password</b>\r<br>\r<br>You may change your password from this section. Please note that you will need to know your current password before you can change your password.\r<br>\r<br><b>View Subsciptions</b>\r<br>\r<br>This is where you manage your topic subscriptions. Please see the help file \'Email Notification of new messages\' for more information on how to subscribe to topics.', 'Editing contact information, personal information, avatars, signatures, board settings, languages and style choices.')";

    $SQL[] = "INSERT INTO ibf_faq (id, title, text, description) VALUES (5, 'Email Notification of new messages', 'This board can notify you when a new reply is added to a topic. Many users find this useful to keep up to date on topics without the need to view the board to check for new messages.\r<br>\r<br>To do this, simply click on the \'Track This Topic\' link found at the top and bottom of any topic. When you click this link, the topic will be added to your subscriptions that are managed from your control panel.\r<br>\r<br>Please note that to avoid multiple emails being sent to your email address, you will only get notified once per day of new replies.', 'How to get emailed when a new reply is added to a topic.')";

    $SQL[] = "INSERT INTO ibf_faq (id, title, text, description) VALUES (6, 'Your Personal Messenger', 'Your personal messenger acts much like an email account in that you can send and receive messages and store messages in folders.\r<br>\r<br><b>Send a new PM</b>\r<br>\r<br>This will allow you to send a message to another member. If you have names in your contact list, you can choose a name from it - or you may choose to enter a name in the relevant form field. This will be automatically filled in if you clicked a \'PM\' button on the board (from the memberlist or a post).\r<br>If the administrator allows, you may use BB Code and HTML in your private message. If you choose to check the \'Add a copy of this message to you sent items folder\' box, a copy of the message will be saved for you for later reference. If you check the \'Get notified when this message is read\' box, you will receive a PM informing you when the message was read for your records.\r<br>\r<br><b>Contact List</b>\r<br>\r<br>You may add in users names in this section, or edit any saved entries. You can also use this as a ban list, denying the named member the ability to message you.\r<br>Names entered in this section will appear in the drop down list when sending a new PM, allowing you to quickly choose the members name when sending a message.\r<br>\r<br><b>Edit Folders</b>\r<br>\r<br>You may rename, add or remove folders to store messages is, allowing you to organise your messages to your preference. You cannot remove \'Sent Items\' or \'Inbox\'.\r<br>\r<br><b>Go to Inbox</b>\r<br>\r<br>Your inbox is where all new messages are sent to. Clicking on the message title will show you the message in a similar format to the board topic view. You can also delete or move messages from your inbox.\r<br>\r<br><b>Archive Messages</b>\r<br>\r<br>If your messenger folders are full and you are unable to receive new messages, you can archive them off. This compiles the messages into a single HTML page or Microsoft &copy; Excel Format. This page is then emailed to your registered email address for your convenience.\r<br>\r<br>', 'How to send personal messages, edit your messenger folders and archive stored messages.')";

    $SQL[] = "INSERT INTO ibf_faq (id, title, text, description) VALUES (7, 'Contacting the moderating team', 'If you need to contact a moderator or simply wish to view the complete administration team, you can click the link \'The moderating team\' found at the top of the main board page (the first page you see when visiting the board).\r<br>\r<br>This list will show you administrators (those who have administration control panel access), global moderators (those who can moderate in all forums) and the moderators of the individual forums.\r<br>\r<br>If you wish to contact someone about your member account, then contact an administrator - if you wish to contact someone about a post or topic, contact either a global moderator or the forum moderator.', 'Where to find a list of the board moderators and administrators.')";

    $SQL[] = "INSERT INTO ibf_faq (id, title, text, description) VALUES (8, 'Viewing members profile information', 'You can view a members profile at any time by clicking on their name when it is underlined (as a link) or by clicking on their name in a post within a topic.\r<br>\r<br>This will show you their profile page which contains their contact information (if they have entered some) and their \'active stats\'.', 'How to view members contact information.')";

    $SQL[] = "INSERT INTO ibf_faq (id, title, text, description) VALUES (9, 'Viewing todays active topics', 'You can view which new topics have new replies today by clicking on the \'Todays Active Topics\' link found on the main board page (the first page you see when visiting the board).\r<br>\r<br>You can set your own date critera, choosing to view all topics  with new replies during several date choices.', 'How to view all the topics which have a new reply today')";

    $SQL[] = "INSERT INTO ibf_faq (id, title, text, description) VALUES (10, 'Searching Topics and Posts', 'The search feature is designed to allow you to quickly find topics and posts that contain the keywords you enter.\r<br>\r<br>The \'Simple Search\' option from the drop down box allows you to enter a single keyword or phrase to search by - the advanced option allows you to join keywords with \'AND\', \'OR\' to fine tune the search. Using this option will make the search slightly slower.\r<br>\r<br>The bottom section of the form allows you to further fine tune the search. You can choose a category to search in, or a forum - or choose all forums to search in.\r<br>\r<br>If you enter a name into the \'Search by member name\' section, all posts started or replied in by this member are returned.', 'How to use the search feature.')";

    $SQL[] = "INSERT INTO ibf_faq (id, title, text, description) VALUES (11, 'Logging in and out', 'If you have chosen not to remember your log in details in cookies, or you are accessing the board on another computer, you will need to log into the board to access your member profile and post with your registered name.\r<br>\r<br>When you log in, you have the choice to save cookies that will log you in automatically when you return. Do not use this option on a shared computer for security.\r<br>\r<br>You can also choose to hide - this will keep your name from appearing in the active users list.\r<br>\r<br>Logging out is simply a matter of clicking on the \'Log Out\' link that is displayed when you are logged in. If you find that you are not logged out, you may need to manually remove your cookies. See the \'Cookies\' help file for more information.', 'How to log in and out from the board and how to remain anonymous and not be shown on the active users list.')";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (1, 'A_LOCKED_B', '<img src=\'style_images/<#IMG_DIR#>/t_closed.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (2, 'A_MOVED_B', '<img src=\'style_images/<#IMG_DIR#>/t_moved.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (3, 'A_POLL', '<img src=\'style_images/<#IMG_DIR#>/t_poll.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (4, 'A_POLLONLY_B', '<img src=\'style_images/<#IMG_DIR#>/t_closed.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (5, 'A_POST', '<img src=\'style_images/<#IMG_DIR#>/t_new.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (6, 'A_REPLY', '<img src=\'style_images/<#IMG_DIR#>/t_reply.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (7, 'A_STAR', '<img src=\'style_images/<#IMG_DIR#>/pip.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (8, 'B_HOT', '<img src=\'style_images/<#IMG_DIR#>/f_hot.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (9, 'B_HOT_NN', '<img src=\'style_images/<#IMG_DIR#>/f_hot_no.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (10, 'B_LOCKED', '<img src=\'style_images/<#IMG_DIR#>/f_closed.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (11, 'B_MOVED', '<img src=\'style_images/<#IMG_DIR#>/f_moved.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (12, 'B_NEW', '<img src=\'style_images/<#IMG_DIR#>/f_norm.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (13, 'B_NORM', '<img src=\'style_images/<#IMG_DIR#>/f_norm_no.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (14, 'B_PIN', '<img src=\'style_images/<#IMG_DIR#>/f_pinned.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (15, 'B_POLL', '<img src=\'style_images/<#IMG_DIR#>/f_poll.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (16, 'B_POLL_NN', '<img src=\'style_images/<#IMG_DIR#>/f_poll_no.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (17, 'C_LOCKED', '<img src=\'style_images/<#IMG_DIR#>/bf_readonly.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (18, 'C_OFF', '<img src=\'style_images/<#IMG_DIR#>/bf_nonew.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (19, 'C_OFF_CAT', '<img src=\'style_images/<#IMG_DIR#>/bc_nonew.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (20, 'C_OFF_RES', '<img src=\'style_images/<#IMG_DIR#>/br_nonew.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (21, 'C_ON', '<img src=\'style_images/<#IMG_DIR#>/bf_new.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (22, 'C_ON_CAT', '<img src=\'style_images/<#IMG_DIR#>/bc_new.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (23, 'C_ON_RES', '<img src=\'style_images/<#IMG_DIR#>/br_new.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (24, 'F_ACTIVE', '<img src=\'style_images/<#IMG_DIR#>/user.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (25, 'F_NAV_SEP', ' ->', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (26, 'F_NAV', '<img src=\'style_images/<#IMG_DIR#>/nav.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (27, 'F_STATS', '<img src=\'style_images/<#IMG_DIR#>/stats.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (28, 'GO_LAST_ON', '', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (29, 'GO_LAST_OFF', '', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (30, 'M_ADDMEM', '<img src=\'style_images/<#IMG_DIR#>/msg_l_addmem.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (31, 'M_DELETE', '<img src=\'style_images/<#IMG_DIR#>/msg_l_delete.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (32, 'M_READ', '<img src=\'style_images/<#IMG_DIR#>/f_norm_no.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (33, 'M_REPLY', '<img src=\'style_images/<#IMG_DIR#>/msg_l_reply.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (34, 'M_UNREAD', '<img src=\'style_images/<#IMG_DIR#>/f_norm.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (35, 'P_AOL', '<img src=\'style_images/<#IMG_DIR#>/p_aim.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (36, 'P_DELETE', '<img src=\'style_images/<#IMG_DIR#>/p_delete.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (37, 'P_EDIT', '<img src=\'style_images/<#IMG_DIR#>/p_edit.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (38, 'P_EMAIL', '<img src=\'style_images/<#IMG_DIR#>/p_email.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (39, 'P_ICQ', '<img src=\'style_images/<#IMG_DIR#>/p_icq.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (40, 'P_MSG', '<img src=\'style_images/<#IMG_DIR#>/p_pm.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (41, 'P_PROFILE', '[ Profile ]', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (42, 'P_QUOTE', '<img src=\'style_images/<#IMG_DIR#>/p_quote.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (43, 'P_WEBSITE', '<img src=\'style_images/<#IMG_DIR#>/p_www.gif\' border=\'0\'  alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (44, 'CAT_IMG', '<img src=\'style_images/<#IMG_DIR#>/nav_m.gif\' border=\'0\'  alt=\'\' width=\'8\' height=\'8\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (45, 'B_HOT_DOT', '<img src=\'style_images/<#IMG_DIR#>/f_hot_dot.gif\' border=\'0\' alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (46, 'B_NEW_DOT', '<img src=\'style_images/<#IMG_DIR#>/f_norm_dot.gif\' border=\'0\' alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (47, 'B_HOT_NN_DOT', '<img src=\'style_images/<#IMG_DIR#>/f_hot_no_dot.gif\' border=\'0\' alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (48, 'B_NORM_DOT', '<img src=\'style_images/<#IMG_DIR#>/f_norm_no_dot.gif\' border=\'0\' alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (49, 'B_POLL_DOT', '<img src=\'style_images/<#IMG_DIR#>/f_poll_dot.gif\' border=\'0\' alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (50, 'B_POLL_NN_DOT', '<img src=\'style_images/<#IMG_DIR#>/f_poll_no_dot.gif\' border=\'0\' alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (51, 'NEW_POST', '<img src=\'style_images/<#IMG_DIR#>/newpost.gif\' border=\'0\'  alt=\'Goto last unread\' title=\'Goto last unread\' hspace=2>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (52, 'tbl_width', '95%', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (53, 'tbl_border', '#345487', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (55, 'P_YIM', '<img src=\'style_images/<#IMG_DIR#>/p_yim.gif\' border=\'0\' alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro (macro_id, macro_value, macro_replace, can_remove, macro_set) VALUES (56, 'P_MSN', '<img src=\'style_images/<#IMG_DIR#>/p_msn.gif\' border=\'0\' alt=\'\'>', 0, 1)";

    $SQL[] = "INSERT INTO ibf_macro_name (set_id, set_name) VALUES (1, 'IBF Default Macro Set')";

    // xoops

    $SQL[] = "UPDATE xbb_users SET uid='0', level='0' WHERE uname='Guest' AND user_regdate='0'";

    //xoops

    $SQL[] = "INSERT INTO ibf_skins (uid, sname, sid, set_id, tmpl_id, macro_id, css_id, img_dir, tbl_width, tbl_border, hidden, default_set) VALUES (1, 'Invision Board', 0, 1, 1, 1, 1, '1', '95%', '#999999', 0, 1)";

    $SQL[] = "INSERT INTO ibf_stats (TOTAL_REPLIES, TOTAL_TOPICS, LAST_MEM_NAME, LAST_MEM_ID, MOST_DATE, MOST_COUNT, MEM_COUNT) VALUES (0, 1, '', '1', <%time%>, 1, 1)";

    $SQL[] = "INSERT INTO ibf_templates (tmid, template, name) VALUES (1, '<html>    <head>      <title>                <% TITLE %>      </title>      <% GENERATOR %>      <% CSS %>      <% JAVASCRIPT %>          </head><body bgcolor=\'#FFFFFF\' leftmargin=\"0\" topmargin=\"0\" marginwidth=\"0\" marginheight=\"0\" alink=\'#000000\' vlink=\'#000000\'><% BOARD HEADER %><% NAVIGATION %><% BOARD %><% STATS %><% COPYRIGHT %></body></html>', 'Invision Power Board Standard')";

    $SQL[] = "INSERT INTO ibf_tmpl_names (skid, skname, author, email, url) VALUES (1, 'Invision Power Board Template Set', 'Invision Power Board', 'skins@invisionboard.com', 'http://www.invisionboard.com')";

    return $SQL;
}

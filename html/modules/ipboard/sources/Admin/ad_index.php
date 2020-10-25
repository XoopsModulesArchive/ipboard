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
|   > Admin "welcome" screen functions
|   > Module written by Matt Mecham
|   > Date started: 1st march 2002
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

$idx = new index_page();

class index_page
{
    public $mysql_version = '';

    public function __construct()
    {
        global $DB, $IN, $INFO, $ADMIN, $MEMBER, $SKIN, $std;

        //---------------------------------------

        // Kill globals - globals bad, Homer good.

        //---------------------------------------

        $tmp_in = array_merge($_GET, $_POST, $_COOKIE);

        foreach ($tmp_in as $k => $v) {
            unset($$k);
        }

        $ADMIN->page_title = 'Welcome to the Invision Power Board Administration CP';

        $ADMIN->page_detail = 'You can set up and customize your board from within this control panel.<br><br>Clicking on one of the links in the left menu pane will show you the relevant options for that administration category. Each option will contain further information on configuration, etc.';

        //---------------------------------

        // Get mySQL & PHP Version

        //---------------------------------

        $DB->query('SELECT VERSION() AS version');

        if (!$row = $DB->fetch_row()) {
            $DB->query("SHOW VARIABLES LIKE 'version'");

            $row = $DB->fetch_row();
        }

        $this->mysql_version = $row['version'];

        $phpv = phpversion();

        $ADMIN->page_detail .= "<br><br><b>PHP VERSION:</b> $phpv, <b>mySQL VERSION:</b> " . $this->mysql_version;

        //---------------------------------

        $DB->query('SELECT * FROM ibf_stats');

        $row = $DB->fetch_row();

        if ($row['TOTAL_REPLIES'] < 0) {
            $row['TOTAL_REPLIES'] = 0;
        }

        if ($row['TOTAL_TOPICS'] < 0) {
            $row['TOTAL_TOPICS'] = 0;
        }

        if ($row['MEM_COUNT'] < 0) {
            $row['MEM_COUNT'] = 0;
        }

        $DB->query("SELECT COUNT(*) as reg FROM xbb_members WHERE mgroup='" . $INFO['auth_group'] . "' AND (new_pass='' or new_pass IS NULL)");

        $reg = $DB->fetch_row();

        if ($reg['reg'] < 1) {
            $reg['reg'] = 0;
        }

        $DB->query("SELECT COUNT(*) as coppa FROM xbb_members WHERE mgroup='" . $INFO['auth_group'] . "' AND coppa_user=1");

        $coppa = $DB->fetch_row();

        if ($coppa['coppa'] < 1) {
            $coppa['coppa'] = 0;
        }

        //-------------------------------------------------

        // Make sure the uploads path is correct

        //-------------------------------------------------

        $uploads_size = 0;

        if ($dh = opendir($INFO['upload_dir'])) {
            while ($file = readdir($dh)) {
                if (!preg_match('/^..?$|^index/i', $file)) {
                    $uploads_size += @filesize($INFO['upload_dir'] . '/' . $file);
                }
            }

            closedir($dh);
        }

        // This piece of code from Jesse's (jesse@jess.on.ca) contribution

        // to the PHP manual @ php.net

        if ($uploads_size >= 1048576) {
            $uploads_size = round($uploads_size / 1048576 * 100) / 100 . ' mb';
        } elseif ($uploads_size >= 1024) {
            $uploads_size = round($uploads_size / 1024 * 100) / 100 . ' k';
        } else {
            $uploads_size .= ' bytes';
        }

        //+-----------------------------------------------------------

        // BOARD OFFLINE?

        //+-----------------------------------------------------------

        if ($INFO['board_offline']) {
            $SKIN->td_header[] = ['&nbsp;', '100%'];

            $ADMIN->html .= $SKIN->start_table('Offline Notice');

            $ADMIN->html .= $SKIN->add_td_row(
                [
                    "Your board is currently offline<br><br>&raquo; <a href='{$ADMIN->base_url}&act=op&code=board'>Turn Board Online</a>",
                ]
            );

            $ADMIN->html .= $SKIN->end_table();

            $ADMIN->html .= $SKIN->add_td_spacer();
        }

        //+-----------------------------------------------------------

        // ADMINS USING CP

        //+-----------------------------------------------------------

        $SKIN->td_header[] = ['Name', '20%'];

        $SKIN->td_header[] = ['IP Address', '20%'];

        $SKIN->td_header[] = ['Log In', '20%'];

        $SKIN->td_header[] = ['Last Click', '20%'];

        $SKIN->td_header[] = ['Location', '20%'];

        $ADMIN->html .= $SKIN->start_table('Administrators using the CP');

        $t_time = time() - 60 * 10;

        $DB->query("SELECT MEMBER_NAME, LOCATION, LOG_IN_TIME, RUNNING_TIME, IP_ADDRESS FROM ibf_admin_sessions WHERE RUNNING_TIME > $t_time");

        $time_now = time();

        $seen_name = [];

        while (false !== ($r = $DB->fetch_row())) {
            if (1 == $seen_name[$r['MEMBER_NAME']]) {
                continue;
            }

            $seen_name[$r['MEMBER_NAME']] = 1;

            $log_in = $time_now - $r['LOG_IN_TIME'];

            $click = $time_now - $r['RUNNING_TIME'];

            if (($log_in / 60) < 1) {
                $log_in = sprintf('%0d', $log_in) . ' seconds ago';
            } else {
                $log_in = sprintf('%0d', ($log_in / 60)) . ' minutes ago';
            }

            if (($click / 60) < 1) {
                $click = sprintf('%0d', $click) . ' seconds ago';
            } else {
                $click = sprintf('%0d', ($click / 60)) . ' minutes ago';
            }

            $ADMIN->html .= $SKIN->add_td_row(
                [
                    $r['MEMBER_NAME'],
                    "<center><a href='javascript:alert(\"Host Name: " . @gethostbyaddr($r['IP_ADDRESS']) . "\")' title='Get host name'>" . $r['IP_ADDRESS'] . '</a></center>',
                    '<center>' . $log_in . '</center>',
                    '<center>' . $click . '</center>',
                    '<center>' . $r['LOCATION'] . '</center>',
                ]
            );
        }

        $ADMIN->html .= $SKIN->end_table();

        //+-----------------------------------------------------------

        $ADMIN->html .= $SKIN->add_td_spacer();

        //+-----------------------------------------------------------

        if ($MEMBER['mgroup'] == $INFO['admin_group']) {
            //+-----------------------------------------------------------

            // LAST 5 Admin Actions

            //+-----------------------------------------------------------

            $SKIN->td_header[] = ['Member Name', '20%'];

            $SKIN->td_header[] = ['Action Performed', '40%'];

            $SKIN->td_header[] = ['Time of action', '20%'];

            $SKIN->td_header[] = ['IP address', '20%'];

            $ADMIN->html .= $SKIN->start_table('Last 5 Admin Actions');

            $DB->query(
                'SELECT m.*, mem.uid, mem.uname FROM ibf_admin_logs m, xbb_members mem
						WHERE  m.member_id=mem.uid ORDER BY m.ctime DESC LIMIT 0, 5'
            );

            if ($DB->get_num_rows()) {
                while (false !== ($rowb = $DB->fetch_row())) {
                    $rowb['ctime'] = $ADMIN->get_date($rowb['ctime'] + $ADMIN->timezone_offset, 'LONG');

                    $ADMIN->html .= $SKIN->add_td_row(
                        [
                            "<b>{$rowb['uname']}</b>",
                            (string)($rowb['note']),
                            (string)($rowb['ctime']),
                            (string)($rowb['ip_address']),
                        ]
                    );
                }
            } else {
                $ADMIN->html .= $SKIN->add_td_basic('<center>No results</center>');
            }

            $ADMIN->html .= $SKIN->end_table();

            //+-----------------------------------------------------------

            $ADMIN->html .= $SKIN->add_td_spacer();
        }

        //+-----------------------------------------------------------

        $SKIN->td_header[] = ['Definition', '25%'];

        $SKIN->td_header[] = ['Value', '25%'];

        $SKIN->td_header[] = ['Definition', '25%'];

        $SKIN->td_header[] = ['Value', '25%'];

        $ADMIN->html .= $SKIN->start_table('System Overview');

        $ADMIN->html .= $SKIN->add_td_row(
            [
                'Total Unique Topics',
                $row['TOTAL_TOPICS'],
                'Total Replies to topics',
                $row['TOTAL_REPLIES'],
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(['Total Members', $row['MEM_COUNT'], 'Public Upload Folder Size', $uploads_size]);

        $ADMIN->html .= $SKIN->add_td_row(
            [
                "<a href='{$SKIN->base_url}&act=mem&code=mod'>Users awaiting validation</a>",
                $reg['reg'],
                "<a href='{$SKIN->base_url}&act=mem&code=mod'>COPPA Requests</a> from 'Users awaiting validation' total",
                $coppa['coppa'],
            ]
        );

        $ADMIN->html .= $SKIN->end_table();

        //+-----------------------------------------------------------

        $ADMIN->html .= $SKIN->add_td_spacer();

        //+-----------------------------------------------------------

        $ADMIN->html .= $SKIN->start_form();

        $SKIN->td_header[] = ['&nbsp;', '40%'];

        $SKIN->td_header[] = ['&nbsp;', '30%'];

        $SKIN->td_header[] = ['&nbsp;', '30%'];

        $ADMIN->html .= $SKIN->start_table('Quick Clicks');

        $ADMIN->html .= "
				
					<script language='javascript'>
					<!--
					  function edit_member() {
						
						if (document.forms[0].username.value == \"\") {
							alert(\"You must enter a username!\");
						} else {
							window.parent.body.location = '{$SKIN->base_url}' + '&act=mem&code=stepone&USER_NAME=' + escape(document.forms[0].username.value);
						}
					  }
					  
					  function new_cat() {
						
						if (document.forms[0].cat_name.value == \"\") {
							alert(\"You must enter a category name!\");
						} else {
							window.parent.body.location = '{$SKIN->base_url}' + '&act=cat&code=new&name=' + escape(document.forms[0].cat_name.value);
						}
					  }
					  
					  function new_forum() {
						
						if (document.forms[0].forum_name.value == \"\") {
							alert(\"You must enter a forum name!\");
						} else {
							window.parent.body.location = '{$SKIN->base_url}' + '&act=forum&code=new&name=' + escape(document.forms[0].forum_name.value);
						}
					  }
					//-->
					
					</script>
					<form name='DOIT' action=''>
						
		";

        $ADMIN->html .= $SKIN->add_td_row(
            [
                'Edit Member:',
                "<input type='text' style='width:100%' id='textinput' name='username' value='Enter name here' onfocus='this.value=\"\"'>",
                "<input type='button' value='Find Member' id='button' onClick='edit_member()'>",
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                'Add New Category:',
                "<input type='text' style='width:100%' name='cat_name' id='textinput' value='Category title here' onfocus='this.value=\"\"'>",
                "<input type='button' value='Add Category' id='button' onClick='new_cat()'>",
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                'Add New Forum:',
                "<input type='text' style='width:100%' name='forum_name' id='textinput' value='Forum title here' onfocus='this.value=\"\"'>",
                "<input type='button' value='Add Forum' id='button' onClick='new_forum()'>",
            ]
        );

        $ADMIN->html .= '</form>';

        $ADMIN->html .= $SKIN->end_table();

        $ADMIN->output();
    }
}

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
|   > Admin Quick Help System
|   > Module written by Matt Mecham
|   > Date started: 1st march 2002
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

$idx = new quick_help();

class quick_help
{
    public $help_text = [];

    public function init_help_array()
    {
        return [
            'mg_upload' => [
                'title' => 'Upload Permissions',
'body' => "If you wish to allow this group to attach files (upload) when posting, you will have to ensure you have completed the following:
											  			  <ul>
											  			  <li>You have entered a reasonable numerical figure in the groups 'Max upload file size' field.
											  			  <li>You have edited the forum permissions for this group and checked the 'Upload' checkbox.
											  			  </ul>
											  			  This allows you to control in which forums this group can upload to.
														  <br><br><b>Warning!</b><br>If, when posting, no post is made and you are returned back to the board index, disable this groups upload by entering 0 into this field. This will turn off the mutli-part form the uploads use.
														 ",
            ],

            'mg_promote' => [
                'title' => 'Group Promotion',
'body' => "If enabled (by choosing a member group to promote your members to and by entering a number of posts to achieve this)
											    		   when your members meet or exceed the number of posts set they will be 'promoted' to the specified group.
											    		   <br><br>
											    		   Many administrators use this feature to set up a 'Senior Members' group with more functionality (such as a longer edit time, larger post uploads) and even
											    		   allow access to otherwise hidden forums - when your members have made enough posts, they are promoted to this group allowing you to intice more posting and
											    		   allow for a more restrictive set of permissions for newcomers.
											    		   <br><br><b>Warning!</b><br>Use this feature carefully and always check the information before proceeding.<br>It is possible to advance to an Admin group - you have been warned.
											   			  ",
            ],
's_reg_antispam' => [
                'title' => 'Registration AntiSpam',
'body' => 'To prevent robots from registering (such as a malicious denial of service attack registering thousands of new accounts and forcing thousands of emails to be sent from your server)
													            you can enable this option.
													            <br><br>When enabled, a random 6 digit numerical string is generated and shown in a graphical format (to prevent advanced bots from reading the source page). The user must enter
													            this string exactly when registering or the account will not be created.',
            ],

            'm_bulkemail' => [
                'title' => 'Bulk Emailing',
'body' => "<b>Overview</b><br>Bulk emailing allows you to target a specific section of your community or email all your registered members.
												    <br><br><b>Settings</b><br>You can choose which user groups will receive the email and elect to override the user set 'Allow Admin Emails' function. It is NOT recommended that you do override this
												    however.<hr>
												    <b>Allowed Tags</b><br>Although the email system sends the mail via BCC to preserve system resources, you can add in dynamic content with the following tags.
												    <br>{board_name} will return the name of your board
													<br>{reg_total} will return the total number of registered members
													<br>{total_posts} will return the total number of posts
													<br>{busy_count} will return the most number of online users
													<br>{busy_time} will return the date of the most online users
													<br>{board_url} will return the URL to the board
													<br><br>As the email is sent via BCC, it is not possible to include the members username, password or other user profile data.",
            ],
        ];
    }

    public function __construct()
    {
        global $DB, $IN, $INFO, $ADMIN, $SKIN, $std;

        $id = $IN['id'];

        $this->help_text = $this->init_help_array();

        if ('' == $this->help_text[$id]['title']) {
            $ADMIN->error('No help information is available for this function at present');
        }

        print "<html>
				<head>
				 <title>Quick Help</title>
				</head>
				<body leftmargin='0' topmargin='0' bgcolor='#F5F9FD'>
				 <table width='95%' align='center' border='0' cellpadding='6'>
				 <tr>
				  <td style='font-family:verdana, arial, tahoma;color:#4C77B6;font-size:16px;letter-spacing:-1px;font-weight:bold'>{$this->help_text[$id]['title']}</td>
				 </tr>
				 <tr>
				  <td style='font-family:verdana, arial, tahoma;color:black;font-size:9pt'>{$this->help_text[$id]['body']}</td>
				 </tr>
				 </table>
				</body>
				</html>";

        exit();
    }
}

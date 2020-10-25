<?php

class skin_global
{
    public function Member_bar($msg, $ad_link, $mod_link)
    {
        global $ibforums;

        return <<<EOF
<table width='100%' cellpadding='0' cellspacing='0' border='0' align='center'>
<tr>
 <td align='left' valign='middle'><b>{$ibforums->lang['logged_in_as']} {$ibforums->member['name']}</b> ( <a href='{$ibforums->base_url}&act=Login&CODE=03'>{$ibforums->lang['log_out']}</a>$ad_link $mod_link )</td>
 <td align='right' valign='middle'>
   <b><a href='{$ibforums->base_url}&act=UserCP&CODE=00' title='{$ibforums->lang['cp_tool_tip']}'>{$ibforums->lang['your_cp']}</a></b> | <a href='{$ibforums->base_url}&act=Msg&CODE=01'>{$msg[TEXT]}</a>
   | <a href='{$ibforums->base_url}&act=Search&CODE=getnew'>{$ibforums->lang['view_new_posts']}</a> | <a href='javascript:buddy_pop();' title='{$ibforums->lang['bb_tool_tip']}'>{$ibforums->lang['l_qb']}</a>
 </td>
</tr>
</table>
EOF;
    }

    public function Member_no_usepm_bar($ad_link, $mod_link)
    {
        global $ibforums;

        return <<<EOF
<table width='100%' cellpadding='0' cellspacing='0' border='0' align='center'>
<tr>
 <td align='left' valign='middle'><b>{$ibforums->lang['logged_in_as']} {$ibforums->member['name']}</b> ( <a href='{$ibforums->base_url}&act=Login&CODE=03'>{$ibforums->lang['log_out']}</a>$ad_link $mod_link )</td>
 <td align='right' valign='middle'>
   <b><a href='{$ibforums->base_url}&act=UserCP&CODE=00' title='{$ibforums->lang['cp_tool_tip']}'>{$ibforums->lang['your_cp']}</a></b>
   | <a href='{$ibforums->base_url}&act=Search&CODE=getnew'>{$ibforums->lang['view_new_posts']}</a> | <a href='javascript:buddy_pop();' title='{$ibforums->lang['bb_tool_tip']}'>{$ibforums->lang['l_qb']}</a>
 </td>
</tr>
</table>
EOF;
    }

    public function ibf_banner()
    {
        global $ibforums;

        return <<<EOF
<a href='http://www.ipshosting.com' target='_blank'><img src='html/sys-img/ipshosting.gif' border='0' alt='IPS Hosting'></a>
EOF;
    }

    public function BoardHeader($time = '')
    {
        global $ibforums;

        return <<<EOF

 <script language='JavaScript'>
     <!--
      function buddy_pop()
      {
           window.open('index.{$ibforums->vars['php_ext']}?act=buddy&s={$ibforums->session_id}','BrowserBuddy','width=200,height=450,resizable=yes,scrollbars=yes'); 
      }
     //-->
 </script>

<table width='<{tbl_width}>' border='0' align='center' cellpadding='0' cellspacing='1' bgcolor='<{tbl_border}>'>
  <tr> 
    <td align='left'>
     <table width='100%' border='0' cellspacing='0' cellpadding='0' background='{$ibforums->vars['img_url']}/header_tile.gif'>
      <tr>
       <td align='left'><a href='{$ibforums->base_url}' title='Board Home'><img src='{$ibforums->vars['img_url']}/logo.jpg' alt='Powered by Invision Power Board' border='0'></a></td>
       <td align='right' valign='middle' background='{$ibforums->vars['img_url']}/header_tile.gif'><!--IBF.BANNER--></td>
  	  </tr>
  	 </table>
  	</td>
  </tr>
  <tr> 
    <td class='row1'>
      <table width='100%' border='0' cellspacing='0' cellpadding='8'>
        <tr>
          <td width='100%' valign='middle' align='center'> <% MEMBER BAR %>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</td>
</table>
<br>
EOF;
    }

    public function start_nav()
    {
        global $ibforums;

        return <<<EOF
<table width='<{tbl_width}>' align='center' border="0" cellspacing="0" cellpadding="2">
<tr> 
    <td width='1%' valign='middle'><{F_NAV}></td>
    <td width="100%" align='left' valign='middle' class="nav">
EOF;
    }

    public function show_chat_link_inline()
    {
        global $ibforums;

        return <<<EOF
<span style='color:<{tbl_border}>'>|</span> <a href='{$ibforums->base_url}&act=chat'>{$ibforums->lang['live_chat']}</a>
EOF;
    }

    public function show_chat_link_popup()
    {
        global $ibforums;

        return <<<EOF
     <script language='JavaScript'>
     <!--
        function chat_pop()
        {
			window.open('index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}&act=chat&pop=1','Chat','width={$ibforums->vars['chat_width']},height={$ibforums->vars['chat_height']},resizable=yes,scrollbars=yes');
     	}
     //-->
     </script>
<span style='color:<{tbl_border}>'>|</span> <a href="javascript:chat_pop();">{$ibforums->lang['live_chat']}</a>
EOF;
    }

    public function end_nav()
    {
        global $ibforums;

        return <<<EOF
	</td>
	<td align='right' valign='middle' nowrap>
	<table cellpadding='5' cellspacing='0' border='0' style='border:1px solid <{tbl_border}>' class='row2'>
	 <tr>
	   <td nowrap><a href='{$ibforums->base_url}&act=Help'>{$ibforums->lang['tb_help']}</a>
	   <span style='color:<{tbl_border}>'>|</span> <a href='{$ibforums->base_url}&act=Search&f={$ibforums->input['f']}'>{$ibforums->lang['tb_search']}</a>
	   <span style='color:<{tbl_border}>'>|</span> <a href='{$ibforums->base_url}&act=Members'>{$ibforums->lang['tb_mlist']}</a>
	   <span style='color:<{tbl_border}>'>|</span> <a href='{$ibforums->base_url}&act=calendar'>{$ibforums->lang['tb_calendar']}</a>
	   <!--IBF.CHATLINK-->
	   </td>
	 </tr>
	</table>
	
	</td>
  </tr>
</table>
<br>
EOF;
    }

    public function Redirect($Text, $Url, $css)
    {
        global $ibforums;

        return <<<EOF
<html>
<head>
<title>{$ibforums->lang['stand_by']}</title>
<meta http-equiv='refresh' content='2; url={$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}$Url'>
$css
</head>
<body class='mainbg'>
<table width='<{tbl_width}>' height='85%' align='center'>
<tr>
	<td valign='middle'>
		<table align='center' border="0" cellspacing="1" cellpadding="0" bgcolor="#000000">
		<tr> 
			<td class='mainbg'>
				<table width="100%" border="0" cellspacing="1" cellpadding="12">
					<tr> 
						<td width="100%" align="center" class='row1'>
							{$ibforums->lang['thanks']}, 
							$Text<br><br>
							{$ibforums->lang['transfer_you']}<br><br>
							(<a href='{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}$Url'>{$ibforums->lang['dont_wait']}</a>)</td>
					</tr>
				</table>
			</td>
		</tr>
	  </table>
	</td>
</tr>
</table>
</body>
</html>
EOF;
    }

    public function PM_popup()
    {
        global $ibforums;

        return <<<EOF
     <script language='JavaScript'>
     <!--
       window.open('index.{$ibforums->vars['php_ext']}?act=Msg&CODE=99&s={$ibforums->session_id}','NewPM','width=500,height=250,resizable=yes,scrollbars=yes'); 
     //-->
     </script>
EOF;
    }

    public function Guest_bar()
    {
        global $ibforums;

        return <<<EOF
&nbsp;{$ibforums->lang['guest_stuff']} ( <a href='{$ibforums->base_url}&act=Login&CODE=00'>{$ibforums->lang['log_in']}</a> | <a href='{$ibforums->base_url}&act=Reg&CODE=00'>{$ibforums->lang['register']}</a> )
EOF;
    }

    public function admin_link()
    {
        global $ibforums;

        return <<<EOF
&nbsp;| <b><a href='{$ibforums->vars['board_url']}/admin.{$ibforums->vars['php_ext']}' target='_blank'>{$ibforums->lang['admin_cp']}</a></b>
EOF;
    }

    public function mod_link()
    {
        global $ibforums;

        return <<<EOF
&nbsp;| <b><a href='{$ibforums->base_url}&act=modcp'>{$ibforums->lang['mod_cp']}</a></b>
EOF;
    }

    public function error_log_in($q_string)
    {
        global $ibforums;

        return <<<EOF
<form action='{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}' method='post'>
     <input type='hidden' name='act' value='Login'>
     <input type='hidden' name='CODE' value='01'>
     <input type='hidden' name='s' value='{$ibforums->session_id}'>
     <input type='hidden' name='referer' value='$q_string'>
     <input type='hidden' name='CookieDate' value='1'>
     <table cellpadding='0' cellspacing='0' border='0' width='80%' bgcolor='<{tbl_border}>' align='center'>
        <tr>
            <td>
                <table cellpadding='3' cellspacing='1' border='0' width='100%'>
                <tr>
                <td align='left' colspan='2' class='titlemedium'>{$ibforums->lang['er_log_in_title']}</td>
                </tr>
                <tr>
                <td class='row1' width='40%'>{$ibforums->lang['erl_enter_name']}</td>
                <td class='row1'><input type='text' size='20' maxlength='64' name='UserName' class='forminput'></td>
                </tr>
                <tr>
                <td class='row1' width='40%'>{$ibforums->lang['erl_enter_pass']}</td>
                <td class='row1'><input type='password' size='20' name='PassWord' class='forminput'></td>
                </tr>
                <tr>
                <td class='row2' align='center' colspan='2'>
                <input type='submit' name='submit' value='{$ibforums->lang['erl_log_in_submit']}' class='forminput'>
                </td>
                </table>
             </td>
         </tr>
     </table>
   </form>
EOF;
    }

    public function board_offline($message = '')
    {
        global $ibforums;

        return <<<EOF
<table width='<{tbl_width}>' border='0' align='center' cellpadding='0' cellspacing='1' bgcolor='<{tbl_border}>'>
  <tr> 
    <td >
			<table width='100%' border='0' cellspacing='0' cellpadding='3'>
        <tr> 
          <td><img src='{$ibforums->vars['img_url']}/nav_m.gif' alt='' width='8' height='8'></td>
          <td width='100%' class='titlemedium'>{$ibforums->lang['offline_title']}</td>
        </tr>
      </table>
		</td>
  </tr>
  <tr> 
    <td class='mainbg'>
			<table width='100%' border='0' cellspacing='1' cellpadding='4'>
				<tr> 
          <td colspan='2' valign='top' class='post1'> <p>$message</p></td>
        </tr>
        <tr> 
          <td colspan='2' valign='top' class='posthead'>{$ibforums->lang['offline_login']}</td>
        </tr>
        <tr>
					<form action='{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}' method='post'>
					<input type='hidden' name='act' value='Login'>
					<input type='hidden' name='CODE' value='01'>
					<input type='hidden' name='s' value='{$ibforums->session_id}'>
					<input type='hidden' name='referer' value=''>
					<input type='hidden' name='CookieDate' value='1'>
          <td class='row1'>{$ibforums->lang['erl_enter_name']}<br><img src='{$ibforums->vars['img_url']}/spacer.gif' alt='' width='180' height='1'></td>
          <td width='100%' class='row1'><input type='text' size='20' maxlength='64' name='UserName' class='forminput'></td>
        </tr>
        <tr> 
          <td class='row1'>{$ibforums->lang['erl_enter_pass']}</td>
          <td width='100%' class='row1'><input type='password' size='20' name='PassWord' class='forminput'></td>
        </tr>
        <tr> 
          <td colspan='2' align='center' class='titlefoot'><input type='submit' name='submit' value='{$ibforums->lang['erl_log_in_submit']}' class='forminput'></td>
					</form>
        </tr>
      </table></td>
  </tr>
</table>
EOF;
    }

    public function Error($message, $ad_email_one = '', $ad_email_two = '')
    {
        global $ibforums;

        return <<<EOF
	<script language='javascript'>
	<!--
	  function contact_admin() {
	  
	  	// Very basic spam bot stopper
	  		
	  	admin_email_one = '$ad_email_one';
	  	admin_email_two = '$ad_email_two';
	  	
	  	window.location = 'mailto:'+admin_email_one+'@'+admin_email_two+'?subject=Error on the forums';
	  	
	  }
	  
	  //-->
	  </script>

<table width='<{tbl_width}>' border='0' cellspacing='1' align='center' cellpadding='0' bgcolor='<{tbl_border}>'>
  <tr> 
    <td class='maintitle' > 
      <table width='100%' border='0' cellspacing='0' cellpadding='3'>
        <tr> 
          <td><img src='{$ibforums->vars['img_url']}/nav_m.gif' alt='' width='8' height='8'></td>
          <td width='100%' class='maintitle'><b>{$ibforums->lang['error_title']}</b></td>
        </tr>
      </table>
    </td>
  </tr>
  <tr> 
    <td class='mainbg'> 
      <table width='100%' border='0' cellspacing='1' cellpadding='4'>
        <tr> 
          <td class='row1' valign='top'>
							{$ibforums->lang['exp_text']}<br><br>
						  <b>{$ibforums->lang['msg_head']}</b>
							<br><br>
							<span class='highlight'>$message</span>
							<br><br>
							<!-- IBF.LOG_IN_TABLE -->
							<br><br>
							<b>Useful Links:</b>
							<br><br>
			 &#149; <a href='{$ibforums->base_url}&act=Reg&CODE=10'>{$ibforums->lang['er_lost_pass']}</a><br>
              &#149; <a href='{$ibforums->base_url}&act=Reg&CODE=00'>{$ibforums->lang['er_register']}</a><br>
              &#149; <a href='{$ibforums->base_url}&act=Help&CODE=00'>{$ibforums->lang['er_help_files']}</a><br>
              &#149; <a href='javascript:contact_admin();'>{$ibforums->lang['er_contact_admin']}</a></p>
          </td>
        </tr>
        <tr> 
          <td class='titlefoot' align='center'>&lt; <a href='javascript:history.go(-1)'>{$ibforums->lang['error_back']}</a></td>
        </tr>
      </table>
    </td>
  </tr>
</table>
EOF;
    }
}

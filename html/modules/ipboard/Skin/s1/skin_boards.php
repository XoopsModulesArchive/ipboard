<?php

class skin_boards
{
    public function stats_header()
    {
        global $ibforums;

        return <<<EOF
<!-- Board Stats -->
	<!--IBF.QUICK_LOG_IN-->
    <br>
	<table width="<{tbl_width}>" align="center" border="0" cellspacing="4" cellpadding="0">
	  <tr> 
		<td align='center'><a href='{$ibforums->base_url}&act=Stats&CODE=leaders'>{$ibforums->lang['sm_forum_leaders']}</a> |
		  <a href='{$ibforums->base_url}&act=Search&CODE=getactive'>{$ibforums->lang['sm_todays_posts']}</a> |
		  <a href='{$ibforums->base_url}&act=Stats'>{$ibforums->lang['sm_today_posters']}</a> |
		  <a href='{$ibforums->base_url}&act=Members&max_results=10&sort_key=posts&sort_order=desc'>{$ibforums->lang['sm_all_posters']}</a></td>
	  </tr>
	 </table>
    <br>
    <table cellpadding='2' width='<{tbl_width}>' align='center' class='solidborder'>
    <tr>
     <td>
     
    <table cellpadding='0' cellspacing='0' border='0' width='100%' bgcolor='<{tbl_border}>' align='center'>
        <tr>
          <td>
            <table cellpadding='4' cellspacing='1' border='0' width='100%'>
            <tr>
			   <td class='titlemedium' colspan='2'>{$ibforums->lang['board_stats']}</td>
		   </tr>
EOF;
    }

    public function ActiveUsers($active)
    {
        global $ibforums;

        return <<<EOF
        <tr>
           <td class='category' colspan='2'>$active[TOTAL] {$ibforums->lang['active_users']}</td>
    	</tr>
    	<tr>
          <td width="5%" class='forum1'><{F_ACTIVE}></td>
          <td class='forum2' width='95%'><b>{$active[GUESTS]}</b> {$ibforums->lang['guests']}, <b>$active[MEMBERS]</b> {$ibforums->lang['public_members']} <b>$active[ANON]</b> {$ibforums->lang['anon_members']} {$active[LINK]}<br>{$active[NAMES]}</td>
        </tr>
EOF;
    }

    public function ShowStats($text)
    {
        global $ibforums;

        return <<<EOF
		   <tr>
		     <td class='category' colspan='2'>{$ibforums->lang['board_stats']}</td>
		   </tr>
		   <tr>
			 <td class='forum1' width='5%' valign='middle'><{F_STATS}></td>
			 <td class='forum2' width="95%" align='left'>$text<br>{$ibforums->lang['most_online']}</td>
		   </tr>
EOF;
    }

    public function birthdays($birthusers = '', $total = '', $birth_lang = '')
    {
        global $ibforums;

        return <<<EOF
        <tr>
           <td class='category' colspan='2'>{$ibforums->lang['birthday_header']}</td>
    	</tr>
    	<tr>
          <td class='forum1' width='5%' valign='middle'><{F_ACTIVE}></td>
          <td class='forum2' width='95%'><b>$total</b> $birth_lang<br>$birthusers</td>
        </tr>
EOF;
    }

    public function calendar_events($events = '')
    {
        global $ibforums;

        return <<<EOF
        <tr>
           <td class='category' colspan='2'>{$ibforums->lang['calender_f_title']}</td>
    	</tr>
    	<tr>
          <td class='forum1' width='5%' valign='middle'><{F_ACTIVE}></td>
          <td class='forum2' width='95%'>$events</td>
        </tr>
EOF;
    }

    public function stats_footer()
    {
        global $ibforums;

        return <<<EOF
         </table>
		</td>
	  </tr>
	 </table>
	 
	 </td>
	 </tr>
	 </table>
    <!-- Board Stats -->
EOF;
    }

    public function BoardInformation()
    {
        global $ibforums;

        return <<<EOF
   <br>
   <table cellspacing='4' cellpadding='0' width='70%' border='0' align='center'>
      <tr>
       <td align='center'>[ <a href="{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}&act=Login&CODE=06">{$ibforums->lang['d_delete_cookies']}</a> ] :: [ <a href="{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}&act=Login&CODE=05">{$ibforums->lang['d_post_read']}</a> ]</td>
    </tr>
   </table>
EOF;
    }

    public function CatHeader_Expanded($Data)
    {
        global $ibforums;

        return <<<EOF
<table width="<{tbl_width}>" align="center" border="0" cellspacing="1" cellpadding="0" bgcolor="<{tbl_border}>">
  <tr> 
    <td> 
      <table width="100%" border="0" cellspacing="0" cellpadding="3" class='maintitle'>
        <tr> 
          <td class="maintitle"><{CAT_IMG}></td>
          <td width="100%" class="maintitle"><a href="{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}&c={$Data['id']}">{$Data['name']}</a></td>
        </tr>
      </table>
    </td>
  </tr>
  <tr> 
    <td class='mainbg'> 
      <table width="100%" border="0" cellspacing="1" cellpadding="4">
        <tr> 
          <td align="center" nowrap class='titlemedium'><img src="{$ibforums->vars['img_url']}/spacer.gif" alt="" width="28" height="1"></td>
          <td width="59%" nowrap class='titlemedium'>{$ibforums->lang['cat_name']}</td>
          <td align="center" width="7%" nowrap class='titlemedium'>{$ibforums->lang['topics']}</td>
          <td align="center" width="7%" nowrap class='titlemedium'>{$ibforums->lang['replies']}</td>
          <td width="27%" nowrap class='titlemedium'>{$ibforums->lang['last_post_info']}</td>
        </tr>
EOF;
    }

    public function subheader()
    {
        global $ibforums;

        return <<<EOF
    <br>
	<table width="<{tbl_width}>" align="center" border="0" cellspacing="1" cellpadding="0" bgcolor="<{tbl_border}>">
	  <tr> 
		<td class='mainbg'> 
            <table width="100%" border="0" cellspacing="1" cellpadding="4">
			<tr> 
			  <td align="center" nowrap class='titlemedium'><img src="{$ibforums->vars['img_url']}/spacer.gif" alt="" width="28" height="1"></td>
			  <td width="59%" nowrap class='titlemedium'>{$ibforums->lang['cat_name']}</td>
			  <td align="center" width="7%" nowrap class='titlemedium'>{$ibforums->lang['topics']}</td>
			  <td align="center" width="7%" nowrap class='titlemedium'>{$ibforums->lang['replies']}</td>
			  <td width="27%" nowrap class='titlemedium'>{$ibforums->lang['last_post_info']}</td>
			</tr>
EOF;
    }

    public function end_this_cat()
    {
        global $ibforums;

        return <<<EOF
<tr> 
          <td class='mainfoot' colspan="5">&nbsp;</td>
        </tr>
      </table>
    </td>
  </tr>
</table>
<br>
EOF;
    }

    public function end_all_cats()
    {
        global $ibforums;

        return <<<EOF
	
EOF;
    }

    public function newslink($fid = '', $title = '', $tid = '')
    {
        global $ibforums;

        return <<<EOF
<b>{$ibforums->vars['board_name']} {$ibforums->lang['newslink']} <a href='{$ibforums->base_url}&act=ST&f=$fid&t=$tid'>$title</a></b><br>
EOF;
    }

    public function PageTop($lastvisit)
    {
        global $ibforums;

        return <<<EOF
	<table cellpadding='4' cellspacing='0' border='0' width='<{tbl_width}>' align='center'>
	 <tr>
	  <td align='left' valign='bottom'><!-- IBF.NEWSLINK -->{$ibforums->lang['welcome_back_text']} $lastvisit</td>
	  <td align='right' valign='bottom'>&nbsp;</td>
	 </tr>
	</table>
EOF;
    }

    public function quick_log_in()
    {
        global $ibforums;

        return <<<EOF
	<form style='display:inline' action="{$ibforums->base_url}&act=Login&CODE=01&CookieDate=1" method="post">
	<table cellpadding='3' cellspacing='0' border='0' width='<{tbl_width}>' align='center'>
	 <tr>
	  <td align='right' valign='middle' width='100%'><b>{$ibforums->lang['qli_title']}</b></td>
	  <td align='right' valign='middle' nowrap>
	  	<input type='text' class='forminput' size='10' name='UserName' onFocus='this.value=""' value='{$ibforums->lang['qli_name']}'>
	  	<input type='password' class='forminput' size='10' name='PassWord' onFocus='this.value=""' value='ibfrules'>
	  	<input type='submit' class='forminput' value='{$ibforums->lang['qli_go']}'>
	  </td>
	 </tr>
	</table>
	</form>
EOF;
    }

    public function ForumRow($info)
    {
        global $ibforums;

        return <<<EOF
    <!-- Forum {$info['id']} entry -->
        <tr> 
          <td class="forum2" align="center">{$info['img_new_post']}</td>
          <td class="forum2" width="59%"><span class="linkthru"><b><a href="{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}&act=SF&f={$info['id']}">{$info['name']}</a></b></span><br><span class='desc'>{$info['description']}</span><br>{$info['moderator']}</td>
          <td class="forum1" align="center" width="7%">{$info['topics']}</td>
          <td class="forum1" align="center" width="7%">{$info['posts']}</td>
          <td class="forum1" width="27%">{$info['last_post']}<br>{$ibforums->lang['in']}: {$info['last_topic']}<br>{$ibforums->lang['by']}: {$info['last_poster']}</td>
        </tr>
    <!-- End of Forum {$info['id']} entry -->
EOF;
    }
}

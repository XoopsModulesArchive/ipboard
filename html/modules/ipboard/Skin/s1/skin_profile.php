<?php

class skin_profile
{
    public function user_edit($info)
    {
        global $ibforums;

        return <<<EOF
<tr>
 <td align='left'><a href='{$info['base_url']}&act=UserCP&CODE=22'>{$ibforums->lang['edit_my_sig']}</a> |
                  <a href='{$info['base_url']}&act=UserCP&CODE=24'>{$ibforums->lang['edit_avatar']}</a> |
                  <a href='{$info['base_url']}&act=UserCP&CODE=01'>{$ibforums->lang['edit_profile']}</a></td>
</tr>
EOF;
    }

    public function show_profile($info)
    {
        global $ibforums;

        return <<<EOF
<table cellpadding='0' cellspacing='1' border='0' width='<{tbl_width}>' align='center'>
 <tr>
  <td><span class='pagetitle'>{$info['name']}</span></td>
 </tr>
 <tr>
  <td align='left'><a href='{$info['base_url']}&act=Search&CODE=getalluser&mid={$info['mid']}'>{$ibforums->lang['find_posts']}</a> |
      <a href='{$info['base_url']}&act=Msg&CODE=02&MID={$info['mid']}'>{$ibforums->lang['add_to_contact']}</a></td>
  </tr>
  <!--MEM OPTIONS-->
</table>

<table cellpadding='0' cellspacing='1' border='0' width='<{tbl_width}>' align='center'>
  <tr>
    <td>
      <table cellpadding='0' cellspacing='1' border='0' width='100%'  bgcolor='<{tbl_border}>'>
        <tr>
          <td width='50%' valign='top' class='row1'>
           <table cellpadding='6' cellspacing='1' border='0' width='100%' class='row1'>
            <tr>
              <td align='center' colspan='2' class='titlemedium'>{$ibforums->lang['active_stats']}</td>
            </tr>
            <tr>
              <td align='left' width='30%' valign='top'><b>{$ibforums->lang['total_posts']}</b></td>
              <td align='left'  class='bottomborder'><b>{$info['posts']}</b><br>( {$info['total_pct']}% {$ibforums->lang['total_percent']} )</td>
            </tr>
            <tr>
              <td align='left' valign='top'><b>{$ibforums->lang['posts_per_day']}</b></td>
              <td align='left' class='bottomborder'><b>{$info['posts_day']}</b></td>
            </tr>
            <tr>
              <td align='left' valign='top'><b>{$ibforums->lang['joined']}</b></td>
              <td align='left' class='bottomborder'><b>{$info['joined']}</b></td>
            </tr>
            <tr>
			  <td align='left' valign='top'><b>{$ibforums->lang['fav_forum']}</b></td>
			  <td align='left'><a href='{$info['base_url']}&act=SF&f={$info['fav_id']}'>{$info['fav_forum']}</a><br>{$info['fav_posts']} {$ibforums->lang['fav_posts']}<br>( {$info['percent']}% {$ibforums->lang['fav_percent']} )</td>
			</tr>
			<tr>
			  <td align='left' valign='top'><b>{$ibforums->lang['user_local_time']}</b></td>
			  <td align='left'>{$info['local_time']}</td>
			</tr>
			</table>
	      </td>
	      
	      <!-- Communication -->
	      
	     <td width='50%' valign='top' class='row1'>
           <table cellpadding='6' cellspacing='1' border='0' width='100%' class='row1'>
            <tr>
              <td align='center' colspan='2' class='titlemedium'>{$ibforums->lang['communicate']}</td>
            </tr>
            <tr>
              <td align='left' width='30%' valign='top'><b>{$ibforums->lang['email']}</b></td>
              <td align='left'  class='bottomborder'>{$info['email']}</td>
            </tr>
            <tr>
              <td align='left' valign='top'><b>{$ibforums->lang['aim']}</b></td>
              <td align='left' class='bottomborder'>{$info['aim_name']}</td>
            </tr>
            <tr>
              <td align='left' valign='top'><b>{$ibforums->lang['icq']}</b></td>
              <td align='left' class='bottomborder'>{$info['icq_number']}</td>
            </tr>
            <tr>
			  <td align='left' valign='top'><b>{$ibforums->lang['yahoo']}</b></td>
			  <td align='left' class='bottomborder'>{$info['yahoo']}</td>
			</tr>
			<tr>
			  <td align='left' valign='top'><b>{$ibforums->lang['msn']}</b></td>
			  <td align='left' class='bottomborder'>{$info['msn_name']}</td>
			</tr>
			<tr>
			  <td align='left' valign='top'><b>{$ibforums->lang['pm']}</b></td>
			  <td align='left'><a href='{$info['base_url']}&act=Msg&CODE=4&MID={$info['mid']}'>{$ibforums->lang['click_here']}</a></td>
			</tr>
			</table>
	      </td>
	      
	      <!-- END CONTENT ROW 1 -->
	      <!-- information -->
	      
	    </tr>
	    <tr>
          <td width='50%' valign='top' class='row1'>
           <table cellpadding='6' cellspacing='1' border='0' width='100%' class='row1'>
            <tr>
              <td align='center' colspan='2' class='titlemedium'>{$ibforums->lang['info']}</td>
            </tr>
            <tr>
              <td align='left' valign='top'><b>{$ibforums->lang['homepage']}</b></td>
              <td align='left' class='bottomborder'>{$info['homepage']}</td>
            </tr>
            <tr>
			  <td align='left' valign='top'><b>{$ibforums->lang['birthday']}</b></td>
			  <td align='left' class='bottomborder'>{$info['birthday']}</td>
			</tr>
			<tr>
			  <td align='left' valign='top'><b>{$ibforums->lang['location']}</b></td>
			  <td align='left' class='bottomborder'>{$info['location']}</td>
			</tr>
			<tr>
			  <td align='left' valign='top'><b>{$ibforums->lang['interests']}</b></td>
			  <td align='left'>{$info['interests']}</td>
			</tr>
			<!--{CUSTOM.FIELDS}-->
			</table>
	      </td>
	      
	      <!-- Profile -->
	      
	     <td width='50%' valign='top' class='row1'>
           <table cellpadding='6' cellspacing='1' border='0' width='100%' class='row1'>
            <tr>
              <td align='center' colspan='2' class='titlemedium'>{$ibforums->lang['post_detail']}</td>
            </tr>
            <tr>
              <td align='left' width='30%' valign='top'><b>{$ibforums->lang['mgroup']}</b></td>
              <td align='left'  class='bottomborder'>{$info['group_title']}</td>
            </tr>
            <tr>
              <td align='left' valign='top'><b>{$ibforums->lang['mtitle']}</b></td>
              <td align='left' class='bottomborder'>{$info['member_title']}</td>
            </tr>
            <tr>
              <td align='left' width='30%' valign='top'><b>{$ibforums->lang['avatar']}</b></td>
              <td align='left'  class='bottomborder'>{$info['avatar']}</td>
            </tr>
            <tr>
              <td align='left' valign='top'><b>{$ibforums->lang['siggie']}</b></td>
              <td align='left'>{$info['signature']}</td>
            </tr>
			</table>
	      </td>
	      </tr>
	      <tr>
	       <td colspan='2' class='row2' align='center' style='height:24px'>&lt;( <a href='javascript:history.go(-1)'>{$ibforums->lang['back']}</a> )</td>
	      </tr>
	  </table>
	</td>
   </tr>
 </table>
EOF;
    }

    public function custom_field($title, $value = '')
    {
        global $ibforums;

        return <<<EOF
			<tr>
              <td align='left' width='30%' valign='top'><b>$title</b></td>
              <td align='left'  class='bottomborder'>$value</td>
            </tr>
EOF;
    }
}

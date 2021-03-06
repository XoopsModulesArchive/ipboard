<?php

class skin_topic
{
    public function report_link($data)
    {
        global $ibforums;

        return <<<EOF
<span class='desc'><a href='{$ibforums->base_url}&act=report&f={$data['forum_id']}&t={$data['topic_id']}&p={$data['pid']}&st={$ibforums->input['st']}'>{$ibforums->lang['snitch_geezer_to_a_copper']}</a></span>
EOF;
    }

    public function ip_show($data)
    {
        global $ibforums;

        return <<<EOF
<span class='desc'>{$ibforums->lang['ip']}: $data</span>
EOF;
    }

    public function golastpost_link($fid, $tid)
    {
        global $ibforums;

        return <<<EOF
( <a href='{$ibforums->base_url}&act=ST&f=$fid&t=$tid&view=getnewpost'>{$ibforums->lang['go_new_post']}</a> )
EOF;
    }

    public function Mod_Panel($data, $fid, $tid)
    {
        global $ibforums;

        return <<<EOF
    <!--<table cellpadding='0' cellspacing='0' width='100%' border='0' align='right'>
      <tr>
          <td align='right'>-->
          <form method='POST' style='display:inline' name='modform' action='{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}'>
          <input type='hidden' name='s' value='{$ibforums->session_id}'>
          <input type='hidden' name='t' value='$tid'>
          <input type='hidden' name='f' value='$fid'>
          <input type='hidden' name='st' value='{$ibforums->input['st']}'>
          <input type='hidden' name='act' value='Mod'>
          <select name='CODE' class='forminput' style="font-weight:bold;color:red">
          <option value='-1' style='color:black'>{$ibforums->lang['moderation_ops']}</option>
          $data
          </select>&nbsp;<input type='submit' value='{$ibforums->lang['jmp_go']}' class='forminput'></form>
          <!--</td>
        </tr>
    </table>-->
EOF;
    }

    public function mod_wrapper($id = '', $text = '')
    {
        global $ibforums;

        return <<<EOF
<option value='$id'>$text</option>
EOF;
    }

    public function TableFooter($data)
    {
        global $ibforums;

        return <<<EOF
      </table></td>
  </tr>
  <tr> 
    <td class='mainbg'>
      <!--IBF.TOPIC_ACTIVE-->
	   <table width='100%' border='0' cellspacing='1' cellpadding='4'>
        <tr>
          <td class='titlemedium' width='100%'>
			<table width='100%' border='0' cellspacing='0' cellpadding='0'>
              <tr>
                <td align='left'><b>{$ibforums->lang['topic_stats']}</b></td>
              	<td align='right'><b><a href='{$ibforums->base_url}&act=Track&f={$data['FORUM']['id']}&t={$data['TOPIC']['tid']}'>{$ibforums->lang['track_topic']}</a> |
									<a href='{$ibforums->base_url}&act=Forward&f={$data['FORUM']['id']}&t={$data['TOPIC']['tid']}'>{$ibforums->lang['forward']}</a> |
									<a href='{$ibforums->base_url}&act=Print&client=printer&f={$data['FORUM']['id']}&t={$data['TOPIC']['tid']}'>{$ibforums->lang['print']}</a></b></td>
               </tr>
            </table>
		  </td>
        </tr>
      </table>
		</td>
  </tr>
</table>
<table width='<{tbl_width}>' align='center' border='0' cellspacing='0' cellpadding='4'>
  <tr> 
    <td width='100%'>{$data[TOPIC][SHOW_PAGES]}
    <br>&lt;&lt; <a href='{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}&act=SF&f={$data['FORUM']['id']}'>{$ibforums->lang['b_back_to']} {$data['FORUM']['name']}</a></td>
    <td align='right' nowrap>{$data[TOPIC][REPLY_BUTTON]}<a href='{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}&act=Post&CODE=00&f={$data[FORUM]['id']}' title='{$ibforums->lang['start_new_topic']}'><{A_POST}></a>{$data[TOPIC][POLL_BUTTON]}</td>
  </tr>
</table>
<br>
<table width='<{tbl_width}>' align='center' border='0' cellspacing='0' cellpadding='3'>
  <tr>
  	<td width='60%' align='left'>
  	   <!--IBF.EMAIL_OPTIONS-->
  	</td>
    <td width='40%' align='right' nowrap valign='top'>{$data[FORUM]['JUMP']}<br><br><!--IBF.MOD_PANEL--></td>
  </tr>
</table>
<br>
EOF;
    }

    public function email_options($tid, $fid)
    {
        global $ibforums;

        return <<<EOF
	<table cellpadding='0' cellspacing='0' width='80%' align='left' border='0'>
  	    <tr>
  	     <td>
  	     	<b><a href='{$ibforums->base_url}&act=Track&f=$fid&t=$tid'>{$ibforums->lang['tt_title']}</a></b>
  	     	<br>{$ibforums->lang['tt_desc']}
  	     	<br><br>
  	     	<b><a href='{$ibforums->base_url}&act=Track&f=$fid&type=forum'>{$ibforums->lang['ft_title']}</a></b>
  	     	<br>{$ibforums->lang['ft_desc']}
  	     	<br><br>
  	     	<b><a href='{$ibforums->base_url}&act=Print&client=choose&f=$fid&t=$tid'>{$ibforums->lang['av_title']}</a></b>
  	     	<br>{$ibforums->lang['av_desc']}
  	     </td>
  	    </tr>
  	   </table>
EOF;
    }

    public function RenderRow($data)
    {
        global $ibforums;

        return <<<EOF
		<!--Begin Msg Number {$data[POST]['pid']}-->
		 <tr>
		 <td valign='middle' class='posthead'><a name='entry{$data[POST]['pid']}'></a><span class='{$data[POST]['name_css']}'>{$data[POSTER]['name']}</span></td>
          <td class='posthead' valign='top'>
			<table width='100%' border='0' cellspacing='0' cellpadding='0'>
              <tr> 
                <td class='posthead'>{$data[POST]['post_icon']}<span class='postdetails'><b>{$ibforums->lang['posted_on']}</b> {$data[POST]['post_date']}</span></td>
                <td class='posthead' align='right'>{$data[POST]['delete_button']}{$data[POST]['edit_button']}<a href='{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}&act=Post&CODE=06&f={$ibforums->input[f]}&t={$ibforums->input[t]}&p={$data[POST]['pid']}'><{P_QUOTE}></a></td>
              </tr>
            </table>
		 </td>
        </tr>
		<tr>
          <td valign='top' class='{$data['POST']['post_css']}'>
            	<span class='postdetails'>{$data[POSTER]['avatar']}<br><br>
				{$data[POSTER]['title']}<br>
				{$data[POSTER]['member_rank_img']}<br><br>
            	{$data[POSTER]['member_group']}<br>
            	{$data[POSTER]['member_posts']}<br>
            	{$data[POSTER]['member_number']}<br>
            	{$data[POSTER]['member_joined']}<br>
            	{$data[POSTER][WARN_GFX]}<br><br></span>
		        <img src='{$ibforums->vars['img_url']}/spacer.gif' alt='' width='160' height='1'><br> 
          </td>
          <td width='100%' valign='top' class='{$data['POST']['post_css']}'><span class='postcolor'>{$data[POST]['post']} {$data[POST]['attachment']} {$data[POST]['signature']}</td>
        </tr>
        <tr>
		   <td class='postfoot' align='left'>{$data[POST]['ip_address']}&nbsp;</td>
		   <td class='postfoot' nowrap align='left'>
		    <table width='100%' border='0' cellspacing='0' cellpadding='0'>
              <tr>
                <td class='postfoot' align='left' valign='middle' nowrap>{$data[POSTER]['message_icon']}{$data[POSTER]['email_icon']}{$data[POSTER]['website_icon']}{$data[POSTER]['icq_icon']}{$data[POSTER]['aol_icon']}{$data[POSTER]['yahoo_icon']}{$data[POSTER]['msn_icon']}</td>
                <td class='postfoot' valign='middle' align='right'>{$data[POST]['report_link']}&nbsp;</td>
		   		<td class='postfoot' valign='middle' align='right' width='2%'><a href='javascript:scroll(0,0);'><img src='{$ibforums->vars['img_url']}/p_up.gif' alt='Top' border='0'></a></td>
		      </tr>
		    </table>
		   </td>
        </tr>
        <tr> 
          <td class='postsep' colspan='2'><img src='{$ibforums->vars['img_url']}/spacer.gif' alt='' width='1' height='1'></td>
		</tr>
  <!-- end Message -->
EOF;
    }

    public function PageTop($data)
    {
        global $ibforums;

        return <<<EOF
    <script language='javascript'>
    <!--
    function delete_post(theURL) {
       if (confirm('{$ibforums->lang['js_del_1']}')) {
          window.location.href=theURL;
       }
       else {
          alert ('{$ibforums->lang['js_del_2']}');
       } 
    }
    
    function PopUp(url, name, width,height,center,resize,scroll,posleft,postop) {
    if (posleft != 0) { x = posleft }
    if (postop  != 0) { y = postop  }

    if (!scroll) { scroll = 1 }
    if (!resize) { resize = 1 }

    if ((parseInt (navigator.appVersion) >= 4 ) && (center)) {
      X = (screen.width  - width ) / 2;
      Y = (screen.height - height) / 2;
    }
    if (scroll != 0) { scroll = 1 }

    var Win = window.open( url, name, 'width='+width+',height='+height+',top='+Y+',left='+X+',resizable='+resize+',scrollbars='+scroll+',location=no,directories=no,status=no,menubar=no,toolbar=no');
	}
    //-->
    </script>
<a name='top'></a>
<!-- Cgi-bot Start Forum page unique top -->
<table width='<{tbl_width}>' border='0' cellspacing='0' cellpadding='4' align='center'>
  <tr> 
    <td width='100%'>{$data['TOPIC']['SHOW_PAGES']}&nbsp;{$data['TOPIC']['go_new']}</td>
    <td align='right' nowrap>{$data[TOPIC][REPLY_BUTTON]}<a href='{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}&act=Post&CODE=00&f={$data[FORUM]['id']}' title='{$ibforums->lang['start_new_topic']}'><{A_POST}></a>{$data[TOPIC][POLL_BUTTON]}</td>
  </tr>
</table>
<table width='<{tbl_width}>' border='0' cellspacing='1' cellpadding='0' bgcolor='<{tbl_border}>' align='center'>
  <tr> 
    <td class='maintitle'>
			<table width='100%' border='0' cellspacing='0' cellpadding='3'>
        <tr> 
          <td><img src='{$ibforums->vars['img_url']}/nav_m.gif' alt='' width='8' height='8'></td>
          <td width='100%' class='maintitle'><b>{$data['TOPIC']['title']}</b>{$data['TOPIC']['description']}</td>
        </tr>
      </table>
		</td>
  </tr>
  <tr> 
	<td class='mainbg'>
	<!--{IBF.POLL}-->
	<table width='100%' border='0' cellspacing='1' cellpadding='4'>
	<tr> 
	  <td width='100%' class='titlemedium' nowrap colspan="2"> 
		<table width='100%' border='0' cellspacing='0' cellpadding='0'>
		  <tr>
		    <td align='left'><b>&laquo; <a href='{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}&act=ST&f={$data[FORUM]['id']}&t={$data[TOPIC]['tid']}&view=old'>{$ibforums->lang['t_old']}</a> | <a href='{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}&act=ST&f={$data[FORUM]['id']}&t={$data[TOPIC]['tid']}&view=new'>{$ibforums->lang['t_new']}</a> &raquo;</b></td>
			<td align='right'><b>
				<a href='{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}&act=Track&f={$data['FORUM']['id']}&t={$data['TOPIC']['tid']}'>{$ibforums->lang['track_topic']}</a> |
				<a href='{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}&act=Forward&f={$data['FORUM']['id']}&t={$data['TOPIC']['tid']}'>{$ibforums->lang['forward']}</a> |
				<a href='{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}&act=Print&client=printer&f={$data['FORUM']['id']}&t={$data['TOPIC']['tid']}'>{$ibforums->lang['print']}</a></b></td>
			</tr>
		</table>
	  </td>
	</tr>

<!-- Cgi-bot End Forum page unique top -->
EOF;
    }

    public function topic_active_users($active = [])
    {
        global $ibforums;

        return <<<EOF
	
	  <table width='100%' border='0' cellspacing='1' cellpadding='4'>
		  <tr> 
			<td class='titlemedium' align='left'>{$ibforums->lang['active_users_title']} ({$ibforums->lang['active_users_detail']})</td>
		  </tr>
		  <tr>
			<td class='forum1'><b>{$ibforums->lang['active_users_members']}</b> {$active['names']}</td>
		  </tr>
	  </table>
	 

EOF;
    }

    public function Show_attachments_img($data)
    {
        global $ibforums;

        return <<<EOF
<br><br><center><b>{$ibforums->lang['pic_attach']}</b></center><br>
<table cellpadding='4' cellspacing='0' border='0' width='50%' align='center' class='fancyborder'>
 <tr>
  <td valign='middle' align='center'><img src='{$ibforums->vars['upload_url']}/{$data['file_name']}' border='0' alt='{$ibforums->lang['pic_attach']}'></td>
 </tr>
</table>
<p>
EOF;
    }

    public function Show_attachments($data)
    {
        global $ibforums;

        return <<<EOF
    <br><br>
     <table cellpadding='4' cellspacing='0' border='0' width='50%' align='center' class='fancyborder'>
      <tr>
       <td align='right' valign='middle' rowspan='2'><img src='{$ibforums->vars['mime_img']}/{$data['image']}' border='0' alt='User Attached Image'></td>
        <td align='left'><a href='{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}&act=Attach&type=post&id={$data['pid']}' target='_blank'>{$ibforums->lang['attach_dl']}</a></td>
      </tr>
      <tr>
      <td align='left' valign='middle' width='98%'>{$data['name']}  ( {$ibforums->lang['attach_hits']}: {$data['hits']} )</td>
      </tr>
     </table>
     <br><br>
EOF;
    }
}

<?php

class skin_forum
{
    public function Forum_log_in($Data)
    {
        global $ibforums;

        return <<<EOF
     <form action='{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id};act=SF&f=$Data' method='post'>
     <input type='hidden' name='act' value='SF'>
     <input type='hidden' name='f' value='$Data'>
     <input type='hidden' name='L' value='1'>
     <input type='hidden' name='s' value='{$ibforums->session_id}'>
     <table cellpadding='0' cellspacing='1' border='0' width='<{tbl_width}>' bgcolor='<{tbl_border}>' align='center'>
        <tr>
            <td>
                <table cellpadding='4' cellspacing='0' border='0' width='100%'>
                <tr>
                <td align='left' colspan='2' class='titlemedium'>{$ibforums->lang['need_password']}</td>
                </tr>
                <tr>
                <td class='row1' colspan='2'>{$ibforums->lang['need_password_txt']}</td>
                </tr>
                <tr>
                <td class='row1' width='40%'><b>{$ibforums->lang['enter_pass']}</b></td>
                <td class='row1'><input type='password' size='20' name='f_password'></td>
                </tr>
                <tr>
                <td class='row2' align='center' colspan='2'><input type='submit' value='{$ibforums->lang['f_pass_submit']}' class='forminput'></td>
                </tr>
                </table>
            </td>
       </tr>
    </table>
    </form>
EOF;
    }

    public function show_rules_full($rules)
    {
        global $ibforums;

        return <<<EOF
    <!-- Show FAQ/Forum Rules -->
		<br>
    <table cellpadding='4' cellspacing='0' border='0' width='<{tbl_width}>' align='center'>
        <tr>
          <td align='left' ><b>{$rules['title']}</b><br><br>{$rules['body']}</td>
      </tr>
   </table>
	 <br>
   <!-- End FAQ/Forum Rules -->
EOF;
    }

    public function show_rules_link($rules)
    {
        global $ibforums;

        return <<<EOF
		<!-- Show FAQ/Forum Rules -->
		<br>
    <table cellpadding='4' cellspacing='0' border='0' width='<{tbl_width}>' align='center'>
        <tr>
          <td align='left' valign='middle'><b>&gt;&gt;<a href='{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}&act=SR&f={$rules['fid']}'>{$rules['title']}</a></b></td>
      </tr>
   </table>
	 <br>
   <!-- End FAQ/Forum Rules -->
EOF;
    }

    public function show_sub_link($fid)
    {
        global $ibforums;

        return <<<EOF
		| <a href='{$ibforums->base_url}&act=Track&f=$fid&type=forum'>{$ibforums->lang['ft_title']}</a>
EOF;
    }

    public function TableEnd($Data)
    {
        global $ibforums;

        return <<<EOF
      </table>
    </td>
  </tr>
  <tr>
    <td class='mainbg'>
      <!--IBF.FORUM_ACTIVE-->
	  <table width='100%' border='0' cellspacing='1' cellpadding='4'>
        <tr> 
          <td class='titlefoot' width='100%' align='center'>
		    <table border='0' cellspacing='0' cellpadding='0'>
              <tr> 
                <td>{$ibforums->lang['showing_text']}{$ibforums->lang['sort_text']}</td>
                <td>&nbsp;<input type='submit' value='{$ibforums->lang['sort_submit']}' class='forminput'></td>
              </tr>
            </table>
		 </td>
        </tr>
      </table>
	</td>
  </form>
  </tr>
</table>
<table width='<{tbl_width}>' border='0' cellspacing='0' cellpadding='4' align='center'>
  <tr> 
    <td width='100%'>{$Data[SHOW_PAGES]}</td>
    <td align='right' nowrap><a href='{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}&act=Post&CODE=00&f={$Data['id']}'><{A_POST}></a>{$Data[POLL_BUTTON]}</td>
  </tr>
</table>
<table width='<{tbl_width}>' border='0' cellspacing='0' cellpadding='3' align='center'>
  <tr> 
    <td><{B_NEW}></td>
    <td nowrap>{$ibforums->lang['pm_open_new']}&nbsp;</td>
    <td><{B_POLL}></td>
    <td nowrap>{$ibforums->lang['pm_poll']}&nbsp;</td>
    <td width='100%' rowspan='4' align='right'> 
      <table border='0' cellspacing='0' cellpadding='0'>
        <tr>
		  <form action='{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}' name='search'>
		  <input type='hidden' name='s' value='{$ibforums->session_id}'>
		  <input type='hidden' name='forums' value='{$Data['id']}'>
		  <input type='hidden' name='cat_forum' value='forum'>
		  <input type='hidden' name='act' value='Search'>
		  <input type='hidden' name='joinname' value='1'>
		  <input type='hidden' name='CODE' value='01'>
		  <td>{$ibforums->lang['search_forum']}&nbsp;</td>
          <td nowrap><input type='text' size='30' name='keywords' class='forminput' value='{$ibforums->lang['enter_keywords']}' onFocus='this.value = "";'> <input type='submit' value='{$ibforums->lang['search_go']}' class='forminput'></td>
		  </form>
        </tr>
      </table>
      <br>
      {$Data[FORUM_JUMP]}
    </td>
  </tr>
  <tr> 
    <td><{B_NORM}></td>
    <td nowrap>{$ibforums->lang['pm_open_no']}&nbsp;</td>
    <td><{B_POLL_NN}></td>
    <td nowrap>{$ibforums->lang['pm_poll_no']}&nbsp;</td>
  </tr>
  <tr> 
    <td><{B_HOT}></td>
    <td nowrap>{$ibforums->lang['pm_hot_new']}&nbsp;</td>
    <td><{B_LOCKED}></td>
    <td nowrap>{$ibforums->lang['pm_locked']}&nbsp;</td>
  </tr>
  <tr> 
    <td><{B_HOT_NN}></td>
    <td nowrap>{$ibforums->lang['pm_hot_no']}&nbsp;</td>
    <td><{B_MOVED}></td>
    <td nowrap>{$ibforums->lang['pm_moved']}&nbsp;</td>
  </tr>
</table>
EOF;
    }

    public function show_rules($rules)
    {
        global $ibforums;

        return <<<EOF
   <!-- Show Forum FAQ/Rules -->
     <br>
     <table cellpadding='4' cellspacing='0' border='0' width='<{tbl_width}>' bgcolor='<{tbl_border}>' align='center'>
        <tr>
            <td>
                <table cellpadding='3' cellspacing='1' border='0' width='100%'>
                <tr>
                <td valign='left' class='titlemedium'>{$rules['title']}</td>
                </tr>
                <tr>
                <td class='row1'>{$rules['body']}</td>
               </tr>
               <tr>
                <td class='titlemedium' align='center'>&gt;&gt;<a href='{$ibforums->base_url}&act=SF&f={$rules['fid']}'>{$ibforums->lang['back_to_forum']}</td>
               </tr>
               </table>
            </td>
      </tr>
   </table>
	 <br>
   <!-- End Forum FAQ/Rules -->
EOF;
    }

    public function page_title($title = '', $pages = '')
    {
        global $ibforums;

        return <<<EOF
	<table cellpadding='0' cellspacing='0' border='0' width='<{tbl_width}>' align='center'>
	<tr>
	 <td><span class='pagetitle'>$title</span>$pages</td>
	</tr>
	</table>
EOF;
    }

    public function PageTop($info)
    {
        global $ibforums;

        return <<<EOF
<!-- Forum page unique top -->

<script language='javascript'>
<!--
	function who_posted(tid)
	{
		window.open("{$ibforums->base_url}&act=Stats&CODE=who&t="+tid, "WhoPosted", "toolbar=no,scrollbars=yes,resizable=yes,width=230,height=300");
	}
//-->
</script>

<!--IBF.SUBFORUMS-->

<table width='<{tbl_width}>' border='0' cellspacing='0' cellpadding='4' align='center'>
  <tr> 
    <td width='100%'>{$info['SHOW_PAGES']}</td>
    <td align='right' nowrap><a href='{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}&act=Post&CODE=00&f={$info['id']}'><{A_POST}></a>{$info[POLL_BUTTON]}</td>
  </tr>
</table>
<table width='<{tbl_width}>' align='center' border='0' cellspacing='1' cellpadding='0' bgcolor='<{tbl_border}>'>
 
  <tr> 
    <form action='{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}' method='POST'>
		<td class='maintitle'>
		<table width='100%' border='0' cellspacing='0' cellpadding='3'>
        <tr> 
          <td><img src='{$ibforums->vars['img_url']}/nav_m.gif' alt='' width='8' height='8'></td>
          <td width='100%' class='maintitle'>{$info['name']}</td>
          <td align='right' class='maintitle' nowrap><span style='font-size:10px'>[ <a href='{$ibforums->base_url}&act=Login&CODE=04&f={$info['id']}'>{$ibforums->lang['mark_as_read']}</a> <!--IBF.SUB_FORUM_LINK--> ]</span></td>
        </tr>
      </table>
	</td>
  </tr>
  <tr> 
    <td class='mainbg'>
	  <table width='100%' border='0' cellspacing='1' cellpadding='4'>
        <tr> 
          <td align='center' nowrap class='titlemedium'>
			<!-- Tuck away hidden form elements -->
            <input type='hidden' name='act' value='SF'>
            <input type='hidden' name='f'   value='{$info['id']}'>
            <input type='hidden' name='s'   value='{$ibforums->session_id}'>
            <input type='hidden' name='st'  value='{$ibforums->input['st']}'>
            <!-- End of tucking :D -->
			<img src='{$ibforums->vars['img_url']}/spacer.gif' alt='' width='20' height='1'></td>
          <td align='center' nowrap class='titlemedium'><img src='{$ibforums->vars['img_url']}/spacer.gif' alt='' width='20' height='1'></td>
          <td width='45%' nowrap class='titlemedium'>{$ibforums->lang['h_topic_title']}</td>
          <td width='14%' align='center' nowrap class='titlemedium'>{$ibforums->lang['h_topic_starter']}</td>
          <td width='7%' align='center' nowrap class='titlemedium'>{$ibforums->lang['h_replies']}</td>
          <td width='7%' align='center' nowrap class='titlemedium'>{$ibforums->lang['h_hits']}</td>
          <td width='27%' nowrap class='titlemedium'>{$ibforums->lang['h_last_action']}</td>
        </tr>
        <!-- Forum page unique top -->
EOF;
    }

    public function forum_active_users($active = [])
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

    public function show_no_matches()
    {
        global $ibforums;

        return <<<EOF
				<tr> 
					<td class='forum2' colspan='7' align='center'>
						<br>
                         <b>{$ibforums->lang['no_topics']}</b>
						<br><br>
					</td>
        </tr>
EOF;
    }

    public function who_link($tid, $posts)
    {
        global $ibforums;

        return <<<EOF
    <a href='javascript:who_posted($tid);'>$posts</a>
EOF;
    }

    public function RenderRow($Data)
    {
        global $ibforums;

        return <<<EOF
    <!-- Begin Topic Entry {$Data['tid']} -->
    <tr> 
	  <td align='center' class='forum2'>{$Data['folder_img']}</td>
      <td align='center' class='forum1'>{$Data['topic_icon']}</td>
      <td class='forum2'>
	  <table width='100%' border='0' cellspacing='0' cellpadding='0'>
		  <tr> 
			<td valign='middle'>{$Data['go_new_post']}</td>
            <td width='100%'><span class='linkthru'>{$Data['prefix']} <a href='{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?act=ST&f={$Data['forum_id']}&t={$Data['tid']}&s={$ibforums->session_id}' class='linkthru' title='{$ibforums->lang['topic_started_on']} {$Data['start_date']}'>{$Data['title']}</a></span>  {$Data[PAGES]}</td>
          </tr>
        </table>
        <span class='desc'>{$Data['description']}</span></td>
      <td align='center' class='forum1'>{$Data['starter']}</td>
      <td align='center' class='forum2'>{$Data['posts']}</td>
      <td align='center' class='forum1'>{$Data['views']}</td>
      <td class='forum1'><span class='desc'>{$Data['last_post']}<br><a href='{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}&act=ST&f={$Data['forum_id']}&t={$Data['tid']}&view=getlastpost'>{$Data['last_text']}</a> <b>{$Data['last_poster']}</b></span></td>
    </tr>
    <!-- End Topic Entry {$Data['tid']} -->
EOF;
    }

    public function render_pinned_start()
    {
        global $ibforums;

        return <<<EOF
    <!-- START PINNED -->
    <tr>
      <td align='center' class='category'>&nbsp;</td>
      <td align='center' class='category'>&nbsp;</td>
	  <td align='left' class='category' colspan='5'>{$ibforums->lang['pinned_start']}</td>
    </tr>
EOF;
    }

    public function render_pinned_end()
    {
        global $ibforums;

        return <<<EOF
    <!-- END PINNED -->
    <tr>
      <td align='center' class='category'>&nbsp;</td>
      <td align='center' class='category'>&nbsp;</td>
	  <td align='left' class='category' colspan='5'>{$ibforums->lang['regular_topics']}</td>
    </tr>
EOF;
    }

    public function render_pinned_row($Data)
    {
        global $ibforums;

        return <<<EOF
    <!-- Begin Pinned Topic Entry {$Data['tid']} -->
    <tr> 
	  <td align='center' class='forum2'>{$Data['folder_img']}</td>
      <td align='center' class='forum1'>{$Data['topic_icon']}</td>
      <td class='forum2'>
	  <table width='100%' border='0' cellspacing='0' cellpadding='0'>
		  <tr> 
			<td valign='middle'>{$Data['go_new_post']}</td>
            <td width='100%'><span class='linkthru' style='font-weight:bold'>{$Data['prefix']} <a href='{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?act=ST&f={$Data['forum_id']}&t={$Data['tid']}&s={$ibforums->session_id}' class='linkthru' title='{$ibforums->lang['topic_started_on']} {$Data['start_date']}'>{$Data['title']}</a></span>  {$Data[PAGES]}</td>
          </tr>
        </table>
        <span class='desc'>{$Data['description']}</span></td>
      <td align='center' class='forum2'>{$Data['starter']}</td>
      <td align='center' class='forum2'>{$Data['posts']}</td>
      <td align='center' class='forum2'>{$Data['views']}</td>
      <td class='forum2'><span class='desc'>{$Data['last_post']}<br><a href='{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}&act=ST&f={$Data['forum_id']}&t={$Data['tid']}&view=getlastpost'>{$Data['last_text']}</a> <b>{$Data['last_poster']}</b></span></td>
    </tr>
    <!-- End Pinned Topic Entry {$Data['tid']} -->
EOF;
    }
}

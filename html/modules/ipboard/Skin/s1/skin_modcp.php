<?php

class skin_modcp
{
    public function mod_cp_start()
    {
        global $ibforums;

        return <<<EOF
<table width="<{tbl_width}>" align="center" border="0" cellspacing="1" cellpadding="0">
  <tr> 
    <td><span class='pagetitle'>{$ibforums->lang['cp_modcp_ptitle']}</span></td>
  </tr>
  <tr>
  	<td><b>{$ibforums->lang['mod_opts']}:</b> 
  	<a href='{$ibforums->base_url}&act=modcp&CODE=showforums'>{$ibforums->lang['menu_forums']}</a> |
    <a href='{$ibforums->base_url}&act=modcp&CODE=members'>{$ibforums->lang['menu_users']}</a> |
    <a href='{$ibforums->base_url}&act=modcp&CODE=ip'>{$ibforums->lang['menu_ip']}</a></td>
  </tr>
 </table>
<br>
EOF;
    }

    public function modtopicview_start($tid, $forumname, $fid, $title)
    {
        global $ibforums;

        return <<<EOF

<form name='ibform' action='{$ibforums->base_url}' method='POST'>
 <input type='hidden' name='s' value='{$ibforums->session_id}'>
 <input type='hidden' name='act' value='modcp'>
 <input type='hidden' name='CODE' value='domodposts'>
 <input type='hidden' name='f' value='{$fid}'>
 <input type='hidden' name='tid' value='{$tid}'>
 
    <table cellpadding='0' cellspacing='10' border='0' width='<{tbl_width}>' align='center'>
     <tr>
       <td><span class='nav'>{$ibforums->lang['cp_mod_posts_title2']} $forumname</span>
       <br>$pages
       </td>
     </tr>
	</table>
	  <table width='<{tbl_width}>' align='center' cellpadding='4' cellspacing='1' bgcolor='<{tbl_border}>'>
		<tr>
			<td valign='middle' class='titlemedium' align='left' colspan='2'>$title</td>
		</tr>
                
EOF;
    }

    public function modpost_topicstart($forumname, $fid)
    {
        global $ibforums;

        return <<<EOF

    <table cellpadding='0' cellspacing='0' border='0' width='<{tbl_width}>' align='center'>
     <tr>
       <td><span class='nav'>{$ibforums->lang['cp_mod_posts_title2']} $forumname</span>
       </td>
     </tr>
     </table>
	 <table width='<{tbl_width}>' cellpadding='4'  align='center' cellspacing='1' bgcolor='<{tbl_border}>'>
	  <tr>
	    <td class='titlemedium' width='40%'>{$ibforums->lang['cp_3_title']}</td>
	    <td class='titlemedium' width='20%' align='center'>{$ibforums->lang['cp_3_replies']}</td>
	    <td class='titlemedium' width='20%' align='center'>{$ibforums->lang['cp_3_approveall']}</td>
	    <td class='titlemedium' width='20%' align='center'>{$ibforums->lang['cp_3_viewall']}</td>
	  </tr>
	 
EOF;
    }

    public function modpost_topicentry($title, $tid, $replies, $fid)
    {
        global $ibforums;

        return <<<EOF

	  <tr>
	    <td class='row1' width='40%' align='left'><b><a href='{$ibforums->base_url}&act=ST&f=$fid&t=$tid' target='_blank'>$title</a></b></td>
	    <td class='row1' width='20%' align='center'>$replies</td>
	    <td class='row1' width='20%' align='center'><a href='{$ibforums->base_url}&act=modcp&f=$fid&tid=$tid&CODE=modtopicapprove'>{$ibforums->lang['cp_3_approveall']}</a></td>
	    <td class='row1' width='20%' align='center'><a href='{$ibforums->base_url}&act=modcp&f=$fid&tid=$tid&CODE=modtopicview'>{$ibforums->lang['cp_3_viewall']}</a></td>
	  </tr>
	 
EOF;
    }

    public function modpost_topicend()
    {
        global $ibforums;

        return <<<EOF

	  </table>
	 </td>
	</tr>
   </table>
	 
EOF;
    }

    public function modtopics_start($pages, $forumname, $fid)
    {
        global $ibforums;

        return <<<EOF

<form name='ibform' action='{$ibforums->base_url}' method='POST'>
 <input type='hidden' name='s' value='{$ibforums->session_id}'>
 <input type='hidden' name='act' value='modcp'>
 <input type='hidden' name='CODE' value='domodtopics'>
 <input type='hidden' name='f' value='{$fid}'>
 
 <table cellpadding='2' cellspacing='1' border='0' width='<{tbl_width}>' align='center'>
 <tr>
   <td><span class='nav'>{$ibforums->lang['cp_mod_topics_title2']} $forumname</span>
   <br>$pages
   </td>
 </tr>
 </table>
	 
EOF;
    }

    public function modtopics_end()
    {
        global $ibforums;

        return <<<EOF

	<table width='<{tbl_width}>' cellpadding='4' align='center' cellspacing='1' bgcolor='<{tbl_border}>'>
	<tr>
	 <td class='row2' align='center'><input type='submit' value='{$ibforums->lang['cp_1_go']}' class='forminput'></td>
	</tr>
	</table>
  </form>
	 
EOF;
    }

    public function mod_topic_title($title, $topic_id)
    {
        global $ibforums;

        return <<<EOF

			  <table width='<{tbl_width}>' cellpadding='4' align='center' cellspacing='1' bgcolor='<{tbl_border}>'>
                <tr>
                	<td valign='middle' class='titlemedium' align='left' colspan='2'><select name='TID_$topic_id' class='forminput'><option value='approve'>{$ibforums->lang['cp_1_approve']}</option><option value='remove'>{$ibforums->lang['cp_1_remove']}</option><option value='leave'>{$ibforums->lang['cp_1_leave']}</option></select>&nbsp;&nbsp; $title</td>
                </tr>
                
EOF;
    }

    public function mod_postentry($data)
    {
        global $ibforums;

        return <<<EOF
			
                <tr>
        		    <td valign='top' class='row1' width='25%'><span class='normalname'>{$data['member']['name']}</span><br><br>{$data['member']['avatar']}<span class='postdetails'><br>{$data['member']['MEMBER_GROUP']}<br>{$data['member']['MEMBER_POSTS']}<br>{$data['member']['MEMBER_JOINED']}</span></td>
                    <td valign='top' height='100%' class='row1' width='75%'>
                    	<b>{$ibforums->lang['posted_on']} {$data['msg']['post_date']}</b><br><br>
            		    <span class='postcolor'>
           			     {$data['msg']['post']}
                        </span>
                    </td>
                 </tr>
			  

EOF;
    }

    public function mod_postentry_checkbox($pid)
    {
        global $ibforums;

        return <<<EOF

			<tr>
			 <td align='left' colspan='2' class='category'><select name='PID_$pid' class='forminput'><option value='approve'>{$ibforums->lang['cp_1_approve']}</option><option value='remove'>{$ibforums->lang['cp_1_remove']}</option><option value='leave'>{$ibforums->lang['cp_1_leave']}</option></select>&nbsp;&nbsp;{$ibforums->lang['cp_3_postno']}&nbsp;$pid</td>
			</tr>

EOF;
    }

    public function mod_topic_spacer()
    {
        global $ibforums;

        return <<<EOF

			</table>
			<br>

EOF;
    }

    public function results($text)
    {
        global $ibforums;

        return <<<EOF

<tr>
  <td colspan='2'>
    <table cellpadding='2' cellspacing='1' border='0' width='100%' class='fancyborder' align='center'>
     <tr>
       <td><span class='pagetitle'>{$ibforums->lang['cp_results']}</span>
       </td>
     </tr>
	  <tr>
	    <td colspan='2'><b>$text</b></td>
	  </tr>
	 </table>
   </td>
  </tr>

EOF;
    }

    public function prune_confirm($tcount, $count, $link, $link_text)
    {
        global $ibforums;

        return <<<EOF


<table width='<{tbl_width}>' cellpadding='1' align='center' cellspacing='1' bgcolor='<{tbl_border}>'>
 <tr>
  <td>
   <table width='100%' align='center' cellpadding='0' cellspacing='4' class='row1'>
     <tr>
       <td><span class='pagetitle'>{$ibforums->lang['cp_check_result']}</span>
           <br>{$ibforums->lang['cp_check_text']}
       </td>
     </tr>
	  <tr>
	    <td><b>{$ibforums->lang['cp_total_topics']}</b></td>
	    <td>$tcount</td>
	  </tr>
	  <tr>
	    <td><b><span style='color:red'>{$ibforums->lang['cp_total_match']}</span></b></td>
	    <td><span style='color:red'>$count</span></td>
	  </tr>
	  <tr>
	    <td colspan='2' align='center' class='row2'>
	     <form action='{$ibforums->base_url}$link' method='POST'>
	     <input type='submit' class='forminput' value='$link_text'>
	    </form>
	    </td>
	  </tr>
	 </table>
   </td>
  </tr>
 </table>
 <br>

EOF;
    }

    public function prune_splash($forum, $forums, $select)
    {
        global $ibforums;

        return <<<EOF

 <table width="<{tbl_width}>" align="center" border="0" cellspacing="1" cellpadding="0">
  <tr> 
    <td><span class='nav'>{$ibforums->lang['cp_prune']} {$forum['name']}</span></td>
  </tr>
  <tr>
  	<td>{$ibforums->lang['cp_prune_text']}</td>
  </tr>
 </table>
 
 <br>
 
 <!-- IBF.CONFIRM -->
 
 
  <form name='ibform' action='{$ibforums->base_url}' method='POST'>
 <input type='hidden' name='s' value='{$ibforums->session_id}'>
 <input type='hidden' name='act' value='modcp'>
 <input type='hidden' name='CODE' value='prune'>
 <input type='hidden' name='f' value='{$forum['id']}'>
 <input type='hidden' name='check' value='1'>
 
 <table width='<{tbl_width}>' cellpadding='0' align='center' cellspacing='1' bgcolor='<{tbl_border}>'>
 <tr>
  <td>
   <table width='100%' align='center' cellpadding='4' cellspacing='1' class='row1'>
 <tr>
  <td width='40%'>{$ibforums->lang['cp_action']}</td>
  <td ><select name='df' class='forminput'>$forums</select></td>
 </tr>
 
 <tr>
  <td width='40%'>{$ibforums->lang['cp_prune_days']}</td>
  <td><input type='text' size='40' name='dateline' value='{$ibforums->input['dateline']}' class='forminput'></td>
 </tr>
 
 <tr>
  <td width='40%'>{$ibforums->lang['cp_prune_type']}</td>
  <td>$select</td>
 </tr>
 
 <tr>
  <td width='40%'>{$ibforums->lang['cp_prune_replies']}</td>
  <td><input type='text' size='40' name='posts' value='{$ibforums->input['posts']}' class='forminput'></td>
 </tr>
 
 <tr>
  <td width='40%'>{$ibforums->lang['cp_prune_member']}</td>
  <td><input type='text' size='40' name='member' value='{$ibforums->input['member']}' class='forminput'></td>
 </tr>
 <tr>
  <td colspan='2' align='center'><input type='submit' value='{$ibforums->lang['cp_prune_sub1']}' class='forminput'></td>
 </tr>
 </table>
</td>
</tr>
</table>
 </form>

EOF;
    }

    public function edit_user_form($profile)
    {
        global $ibforums;

        return <<<EOF

 <form name='ibform' action='{$ibforums->base_url}' method='POST'>
 <input type='hidden' name='s' value='{$ibforums->session_id}'>
 <input type='hidden' name='act' value='modcp'>
 <input type='hidden' name='CODE' value='compedit'>
 <input type='hidden' name='memberid' value='{$profile['id']}'>
 
 <table width="<{tbl_width}>" align="center" border="0" cellspacing="1" cellpadding="0" bgcolor="<{tbl_border}>">
  <tr> 
    <td class='mainbg'>
      <table width="100%" border="0" cellspacing="1" cellpadding="3">
	 <tr>
	  <td colspan='2' class='maintitle'>{$ibforums->lang['cp_edit_user']}: {$profile['name']}</td>
	 </tr>
	 <tr>
	  <td width='40%' class='row2'>{$ibforums->lang['cp_remove_av']}</td>
	  <td class='row2'><select name='avatar' class='forminput'><option value='0'>{$ibforums->lang['no']}</option><option value='1'>{$ibforums->lang['yes']}</option></select></td>
	 </tr>
	 
	 <tr>
	  <td width='40%' class='row2'>{$ibforums->lang['cp_edit_website']}</td>
	  <td class='row2'><input type='text' size='40' name='website' value='{$profile['website']}' class='forminput'></td>
	 </tr>
	 
	 <tr>
	  <td width='40%' class='row2'>{$ibforums->lang['cp_edit_location']}</td>
	  <td class='row2'><input type='text' size='40' name='location' value='{$profile['location']}' class='forminput'></td>
	 </tr>
	 
	 <tr>
	  <td width='40%' class='row2'>{$ibforums->lang['cp_edit_interests']}</td>
	  <td class='row2'><textarea cols='50' rows='3' name='interests' class='forminput'>{$profile['interests']}</textarea></td>
	 </tr>
	 
	  <tr>
	  <td  class='row2' width='40%'>{$ibforums->lang['cp_edit_signature']}</td>
	  <td class='row2'><textarea cols='50' rows='5' name='signature' class='forminput'>{$profile['signature']}</textarea></td>
	 </tr>
	 
	 <tr>
	  <td colspan='2' class='row1' align='center'><input type='submit' value='{$ibforums->lang['cp_find_2_submit']}' class='forminput'></td>
	 </tr>
	 </table>
   </td>
  </tr>
 </table>
 </form>

EOF;
    }

    public function find_two($select)
    {
        global $ibforums;

        return <<<EOF

 <form name='ibform' action='{$ibforums->base_url}' method='POST'>
 <input type='hidden' name='s' value='{$ibforums->session_id}'>
 <input type='hidden' name='act' value='modcp'>
 <input type='hidden' name='CODE' value='doedituser'>
<table width="<{tbl_width}>" align="center" border="0" cellspacing="1" cellpadding="0" bgcolor="<{tbl_border}>">
  <tr> 
    <td class='mainbg'>
      <table width="100%" border="0" cellspacing="1" cellpadding="3">
	 <tr>
	  <td colspan='2' class='maintitle'>{$ibforums->lang['cp_edit_user']}</td>
	 </tr>
	 <tr>
	  <td width='40%' class='row1'>{$ibforums->lang['cp_find_2_user']}</td>
	  <td class='row1'>$select</td>
	 </tr>
	 <tr>
	  <td colspan='2' class='row1' align='center'><input type='submit' value='{$ibforums->lang['cp_find_2_submit']}' class='forminput'></td>
	 </tr>
	</table>
   </td>
  </tr>
 </table>
 </form>

EOF;
    }

    public function find_user()
    {
        global $ibforums;

        return <<<EOF

 <form name='ibform' action='{$ibforums->base_url}' method='POST'>
 <input type='hidden' name='s' value='{$ibforums->session_id}'>
 <input type='hidden' name='act' value='modcp'>
 <input type='hidden' name='CODE' value='dofinduser'>
 <table width="<{tbl_width}>" align="center" border="0" cellspacing="1" cellpadding="0" bgcolor="<{tbl_border}>">
  <tr> 
    <td class='mainbg'>
      <table width="100%" border="0" cellspacing="1" cellpadding="3">
	 <tr>
	  <td colspan='2' class='maintitle'>{$ibforums->lang['cp_edit_user']}</td>
	 </tr>
	 <tr>
	  <td width='40%' class='row1'>{$ibforums->lang['cp_find_user']}</td>
	  <td class='row1'><input type='text' size='40' name='name' value='' class='forminput'></td>
	 </tr>
	 <tr>
	  <td colspan='2' align='center' class='row1'><input type='submit' value='{$ibforums->lang['cp_find_submit']}' class='forminput'></td>
	 </tr>
	</table>
   </td>
  </tr>
 </table>
 </form>

EOF;
    }

    public function ip_start_form($ip_addr)
    {
        global $ibforums;

        return <<<EOF

 <form name='ibform' action='{$ibforums->base_url}' method='POST'>
 <input type='hidden' name='s' value='{$ibforums->session_id}'>
 <input type='hidden' name='act' value='modcp'>
 <input type='hidden' name='CODE' value='doip'>
 <table width="<{tbl_width}>" align="center" border="0" cellspacing="1" cellpadding="0">
  <tr>
  <td class='desc'>{$ibforums->lang['ip_desc_text']}<br><br>{$ibforums->lang['ip_warn_text']}</td>
  </tr>
 </table>
 <br>
 <table width="<{tbl_width}>" align="center" border="0" cellspacing="1" cellpadding="0" bgcolor="<{tbl_border}>">
  <tr> 
    <td class='mainbg'>
      <table width="100%" border="0" cellspacing="1" cellpadding="3">
	 <tr>
	  <td colspan='2' class='maintitle'>{$ibforums->lang['menu_ip']}</td>
	 </tr>
	 <tr>
	  <td width='40%' class='row1'>{$ibforums->lang['ip_enter']}</td>
	  <td class='row1'>
	  	<input type='text' size='3' maxlength='3' name='ip1' value='{$ip_addr[0]}' class='forminput'><b>.</b>
	  	<input type='text' size='3' maxlength='3' name='ip2' value='{$ip_addr[1]}' class='forminput'><b>.</b>
	  	<input type='text' size='3' maxlength='3' name='ip3' value='{$ip_addr[2]}' class='forminput'><b>.</b>
	  	<input type='text' size='3' maxlength='3' name='ip4' value='{$ip_addr[3]}' class='forminput'>&nbsp;
	  	<select name='iptool' class='forminput'>
	  		<option value='resolve'>{$ibforums->lang['ip_resolve']}</option>
	  		<option value='posts'>{$ibforums->lang['ip_posts']}</option>
	  		<option value='members'>{$ibforums->lang['ip_members']}</option>
	  	</select>
	  </td>
	 </tr>
	 <tr>
	  <td colspan='2' align='center' class='row1'><input type='submit' value='{$ibforums->lang['ip_submit']}' class='forminput'></td>
	 </tr>
	</table>
   </td>
  </tr>
 </table>
 </form>

EOF;
    }

    public function ip_member_start($pages)
    {
        global $ibforums;

        return <<<EOF

 <table width="<{tbl_width}>" align="center" border="0" cellspacing="1" cellpadding="0">
  <tr>
  <td align='left'>$pages</td>
  </tr>
 </table>
 <br>
 <table width="<{tbl_width}>" align="center" border="0" cellspacing="1" cellpadding="0" bgcolor="<{tbl_border}>">
  <tr> 
    <td class='mainbg'>
      <table width="100%" border="0" cellspacing="1" cellpadding="3">
	 <tr>
	  <td colspan='5' class='maintitle'>{$ibforums->lang['ipm_title']}</td>
	 </tr>
	 <tr>
	  <td class='titlemedium' width='20%'>{$ibforums->lang['ipm_name']}</td>
	  <td class='titlemedium' width='20%'>{$ibforums->lang['ipm_ip']}</td>
	  <td class='titlemedium' width='10%'>{$ibforums->lang['ipm_posts']}</td>
	  <td class='titlemedium' width='20%'>{$ibforums->lang['ipm_reg']}</td>
	  <td class='titlemedium' width='30%'>{$ibforums->lang['ipm_options']}</td>
	 </tr>

EOF;
    }

    public function ip_member_row($row)
    {
        global $ibforums;

        return <<<EOF

	 <tr>
	  <td class='row2'>{$row['name']}</td>
	  <td class='row2'>{$row['ip_address']}</td>
	  <td class='row2'>{$row['posts']}</td>
	  <td class='row2'>{$row['joined']}</td>
	  <td class='row2' align='center'><a href='{$ibforums->base_url}&act=Profile&MID={$row['id']}' target='_blank'>{$ibforums->lang['ipm_view']}</a>
	  | <a href='{$ibforums->base_url}&act=modcp&CODE=doedituser&memberid={$row['id']}'>{$ibforums->lang['ipm_edit']}</a></td>
	 </tr>

EOF;
    }

    public function ip_member_end($pages)
    {
        global $ibforums;

        return <<<EOF

	 </table>
	</td>
   </tr>
  </table>
   <table width="<{tbl_width}>" align="center" border="0" cellspacing="1" cellpadding="0">
  <tr>
  <td align='left'>$pages</td>
  </tr>
 </table>

EOF;
    }

    public function splash($tcount, $pcount, $forum)
    {
        global $ibforums;

        return <<<EOF

 <tr>
  <td class='pagetitle'>{$ibforums->lang['cp_welcome']}</td>
 </tr>
 <tr>
  <td>{$ibforums->lang['cp_welcome_text']}</td>
 </tr>
 <tr>
  <td>
    <table cellpadding='2' cellspacing='1' border='0' width='75%' class='fancyborder' align='center'>
	  <tr>
	    <td><b>{$ibforums->lang['cp_mod_in']}</b></td>
	    <td>$forum</td>
	  </tr>
	  <tr>
	    <td><b>{$ibforums->lang['cp_topics_wait']}</b></td>
	    <td>$tcount</td>
	  </tr>
	  <tr>
	    <td><b>{$ibforums->lang['cp_posts_wait']}</b></td>
	    <td>$pcount</td>
	  </tr>
	 </table>
   </td>
  </tr>

EOF;
    }

    public function mod_exp($words)
    {
        global $ibforums;

        return <<<EOF



                <tr>
                <td class='row1' colspan='2'>$words</td>
                </tr>


EOF;
    }

    public function end_form($action)
    {
        global $ibforums;

        return <<<EOF


                <tr>
                <td class='row2' align='center' colspan='2'>
                <input type="submit" name="submit" value="$action" class='forminput'>
                </td></tr></table>
                </td></tr></table>
                </form>


EOF;
    }

    public function forum_row($info)
    {
        global $ibforums;

        return <<<EOF
    <!-- Forum {$info['id']} entry -->
        <tr>
          <td class='forum2' align='center' width='5%'>{$info['folder_icon']}</td>
          <td class="forum2" colspan=2><span class="linkthru"><b><a href="{$ibforums->base_url}&act=modcp&CODE=showtopics&f={$info['id']}">{$info['name']}</a></b></span><br><span class='desc'>{$info['description']}</span><br>{$info['moderator']}</td>
          <td class="forum2" align="center">{$info['q_topics']}</td>
          <td class="forum2" align="center">{$info['q_posts']}</td>
          <td class="forum2">{$info['last_post']}<br>{$ibforums->lang['in']}: {$info['last_topic']}<br>{$ibforums->lang['by']}: {$info['last_poster']}</td>
		  <td class="forum2" align="center">{$info['select_button']}</td>        
        </tr>
    <!-- End of Forum {$info['id']} entry -->
EOF;
    }

    public function subforum_row($info)
    {
        global $ibforums;

        return <<<EOF
    <!-- Forum {$info['id']} entry -->
        <tr>
          <td class='forum1' align='center' width='5%'>&nbsp;</td>
          <td class='forum1' align='center' width='5%'>{$info['folder_icon']}</td>
          <td class="forum1"><span class="linkthru"><b><a href="{$ibforums->base_url}&act=modcp&CODE=showtopics&f={$info['id']}">{$info['name']}</a></b></span><br><span class='desc'>{$info['description']}</span><br>{$info['moderator']}</td>
          <td class="forum1" align="center">{$info['q_topics']}</td>
          <td class="forum1" align="center">{$info['q_posts']}</td>
          <td class="forum1">{$info['last_post']}<br>{$ibforums->lang['in']}: {$info['last_topic']}<br>{$ibforums->lang['by']}: {$info['last_poster']}</td>
		  <td class="forum1" align="center"><input type='radio' name='f' value='{$info['id']}'></td>        
        </tr>
    <!-- End of Forum {$info['id']} entry -->
EOF;
    }

    public function forum_page_start()
    {
        global $ibforums;

        return <<<EOF
<form action='{$ibforums->base_url}&act=modcp&CODE=fchoice' method='post'>
<table width="<{tbl_width}>" align="center" border="0" cellspacing="1" cellpadding="0" bgcolor="<{tbl_border}>">
  <tr> 
    <td class='mainbg'>
      <table width="100%" border="0" cellspacing="1" cellpadding="3">
EOF;
    }

    public function cat_row($cat_name)
    {
        global $ibforums;

        return <<<EOF
      	<tr>
      	  <td colspan='7' class='maintitle'>$cat_name</td>
      	</tr>
        <tr> 
          <td class='titlemedium' align='left' width='5%'>&nbsp;</td>
          <td width="45%" colspan=2 nowrap class='titlemedium'>{$ibforums->lang['cat_name']}</td>
          <td align="center" width="10%" nowrap class='titlemedium'>{$ibforums->lang['f_q_topics']}</td>
          <td align="center" width="10%" nowrap class='titlemedium'>{$ibforums->lang['f_q_posts']}</td>
          <td width="25%" nowrap class='titlemedium'>{$ibforums->lang['last_post_info']}</td>
          <td width="5%" nowrap class='titlemedium'>{$ibforums->lang['f_select']}</td>
        </tr>
EOF;
    }

    public function forum_page_end()
    {
        global $ibforums;

        return <<<EOF
	<tr>
	 <td colspan='6' class='row2' align='right'><b>{$ibforums->lang['f_w_selected']}</b>
	 <select class='forminput' name='fact'>
	 <option value='mod_topic'>{$ibforums->lang['cp_mod_topics']}</option>
	 <option value='mod_post'>{$ibforums->lang['cp_mod_posts']}</option>
	 <option value='prune_move'>{$ibforums->lang['cp_prune_posts']}</option>
	 </select>&nbsp;<input type='submit' value='{$ibforums->lang['f_go']}' class='forminput'>
	 </td>
	</tr>
	</table>
  </td>
 </tr>
</table>
</form>
EOF;
    }

    public function mod_simple_page($title = '', $msg = '')
    {
        global $ibforums;

        return <<<EOF
<table cellpadding='5' align='center' width='<{tbl_width}>' cellspacing='0' border='0' style='border:1px solid <{tbl_border}>' class='row2'>
 <tr>
  <td class='pagetitle'>$title</td>
 </tr>
 <tr>
  <td>$msg</td>
 </tr>
 </table>

EOF;
    }

    public function ip_post_results($uid = '', $count = '')
    {
        global $ibforums;

        return <<<EOF
{$ibforums->lang['ipp_found']} $count
<br>
<br>
<a target='_blank' href='{$ibforums->base_url}&act=Search&CODE=show&searchid=$uid&search_in=posts&result_type=posts'>{$ibforums->lang['ipp_click']}</a>

EOF;
    }

    public function start_topics($pages, $info)
    {
        global $ibforums;

        return <<<EOF

<script language='javascript'>
<!--
 function checkdelete() {
 
   isDelete = document.topic.tact.options[document.topic.tact.selectedIndex].value;
   
   msg = '';
   
   if (isDelete == 'delete')
   {
	   msg = "{$ibforums->lang['cp_js_delete']}";
	   
	   formCheck = confirm(msg);
	   
	   if (formCheck === true)
	   {
		   return true;
	   }
	   else
	   {
		   return false;
	   }
   }
 }
//-->
</script>

<table width='<{tbl_width}>' border='0' cellspacing='0' cellpadding='4' align='center'>
  <tr> 
    <td width='100%'>$pages</td>
  </tr>
</table>
<table width='<{tbl_width}>' align='center' border='0' cellspacing='1' cellpadding='0' bgcolor='<{tbl_border}>'>
  <tr> 
    <form action='{$ibforums->base_url}&act=modcp&f={$info['id']}&CODE=topicchoice' method='POST' name='topic' onSubmit='return checkdelete();'>
		<td class='maintitle'>
		<table width='100%' border='0' cellspacing='0' cellpadding='3'>
        <tr> 
          <td><img src='{$ibforums->vars['img_url']}/nav_m.gif' alt='' width='8' height='8'></td>
          <td width='100%' class='maintitle'><b>{$info['name']}</b> [ <a target='_blank' href='{$ibforums->base_url}&act=SF&f={$info['id']}'>{$ibforums->lang['new_show_forum']}</a> ]</td>
        </tr>
      </table>
	</td>
  </tr>
  <tr> 
    <td class='mainbg'>
	  <table width='100%' border='0' cellspacing='1' cellpadding='4'>
        <tr> 
          <td align='center' nowrap class='titlemedium'><img src='{$ibforums->vars['img_url']}/spacer.gif' alt='' width='20' height='1'></td>
          <td align='center' nowrap class='titlemedium'><img src='{$ibforums->vars['img_url']}/spacer.gif' alt='' width='20' height='1'></td>
          <td width='40%' nowrap class='titlemedium'>{$ibforums->lang['h_topic_title']}</td>
          <td width='14%' align='center' nowrap class='titlemedium'>{$ibforums->lang['h_topic_starter']}</td>
          <td width='7%' align='center' nowrap class='titlemedium'>{$ibforums->lang['h_replies']}</td>
          <td width='7%' align='center' nowrap class='titlemedium'>{$ibforums->lang['h_hits']}</td>
          <td width='27%' nowrap class='titlemedium'>{$ibforums->lang['h_last_action']}</td>
          <td width='27%' nowrap class='titlemedium'>{$ibforums->lang['f_select']}</td>
        </tr>
        <!-- Forum page unique top -->
EOF;
    }

    public function show_no_topics()
    {
        global $ibforums;

        return <<<EOF
				<tr> 
					<td class='forum2' colspan='8' align='center'>
						<br>
                         <b>{$ibforums->lang['fv_no_topics']}</b>
						<br><br>
					</td>
        </tr>
EOF;
    }

    public function topic_row($Data)
    {
        global $ibforums;

        return <<<EOF
    <!-- Begin Topic Entry {$Data['tid']} -->
    <tr> 
	  <td align='center' class='forum2'>{$Data['folder_img']}</td>
      <td align='center' class='forum1'>{$Data['topic_icon']}</td>
      <td class='forum2'>
      <span class='linkthru'>{$Data['prefix']} <a target='_blank' href='{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?act=ST&f={$Data['forum_id']}&t={$Data['tid']}&s={$ibforums->session_id}' class='linkthru' title='{$ibforums->lang['topic_started_on']} {$Data['start_date']}'>{$Data['title']}</a></span>
      <br><span class='desc'>{$Data['description']}</span></td>
      <td align='center' class='forum1'>{$Data['starter']}</td>
      <td align='center' class='forum2'>{$Data['posts']}</td>
      <td align='center' class='forum1'>{$Data['views']}</td>
      <td class='forum1'><span class='desc'>{$Data['last_post']}<br>{$Data['last_text']} <b>{$Data['last_poster']}</b></span></td>
      <td align='center' class='forum1'><input type='checkbox' name='TID_{$Data['real_tid']}' value=1></td>
    </tr>
    <!-- End Topic Entry {$Data['tid']} -->
EOF;
    }

    public function topics_end($Data)
    {
        global $ibforums;

        return <<<EOF
      </table>
    </td>
  </tr>
  <tr>
    <td class='mainbg'>
	  <table width='100%' border='0' cellspacing='1' cellpadding='4'>
        <tr> 
          <td class='titlefoot' width='100%' align='center'>
		    <table border='0' cellspacing='0' cellpadding='0'>
              <tr> 
                <td align='right'><b>{$ibforums->lang['t_w_selected']}</b>
	 			<select class='forminput' name='tact'>
	 			<option value='close'>{$ibforums->lang['cpt_close']}</option>
	 			<option value='open'>{$ibforums->lang['cpt_open']}</option>
	 			<option value='pin'>{$ibforums->lang['cpt_pin']}</option>
	 			<option value='unpin'>{$ibforums->lang['cpt_unpin']}</option>
	 			<option value='move'>{$ibforums->lang['cpt_move']}</option>
	 			<option value='delete'>{$ibforums->lang['cpt_delete']}</option>
	 			</select>
	 			&nbsp;<input type='submit' value='{$ibforums->lang['f_go']}' class='forminput'></td>
              </tr>
            </table>
		 </td>
        </tr>
      </table>
	</td>
  </form>
  </tr>
</table>
EOF;
    }

    public function move_checked_form_end($jump_html)
    {
        global $ibforums;

        return <<<EOF

      <tr>
       <td class='row1' colspan='2' align='center'>{$ibforums->lang['cp_tmove_to']}&nbsp;&nbsp;<select class='forminput' name='df'>$jump_html</select></td>
      </tr>
      <tr>
       <td class='row2' colspan='2' align='center'><input type='submit' value='{$ibforums->lang['cp_tmove_end']}' class='forminput'></td>
      </tr>
    </table>
   </td>
  </tr>
 </table>
</form>
EOF;
    }

    public function move_checked_form_entry($tid, $title)
    {
        global $ibforums;

        return <<<EOF

      <tr>
       <td class='row1' width='90%' align='left'>$title</td>
       <td class='row1' width='10%' align='center'><input type='checkbox' name='TID_$tid' value='1' checked></td>
      </tr>
EOF;
    }

    public function move_checked_form_start($forum_name, $fid)
    {
        global $ibforums;

        return <<<EOF
<form action='{$ibforums->base_url}&act=modcp&CODE=topicchoice&tact=domove&f=$fid' method='post'>
<table width="<{tbl_width}>" align="center" border="0" cellspacing="1" cellpadding="0" bgcolor="<{tbl_border}>">
  <tr> 
    <td class='mainbg'>
      <table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr>
       <td class='maintitle' colspan='2'>{$ibforums->lang['cp_tmove_start']} $forum_name</td>
      </tr>
EOF;
    }
}

<?php

class skin_search
{
    public function RenderRow($Data)
    {
        global $ibforums;

        return <<<EOF
    <!-- Begin Topic Entry {$Data['tid']} -->
    <tr> 
	  <td align='center' class='forum2'>{$Data['folder_img']}</td>
      <td align='center' width='3%' class='forum1'>{$Data['topic_icon']}</td>
      <td class='forum2'>
	  <table width='100%' border='0' cellspacing='0' cellpadding='0'>
		  <tr> 
			<td valign='middle'>{$Data['go_new_post']}</td>
            <td width='100%'><span class='linkthru'>{$Data['prefix']} <a href='{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?act=ST&f={$Data['forum_id']}&t={$Data['tid']}&hl={$Data['keywords']}&s={$ibforums->session_id}' class='linkthru'>{$Data['title']}</a></span>  {$Data[PAGES]}</td>
          </tr>
        </table>
        <span class='desc'>{$Data['description']}</span></td>
      <td class='forum2' width='20%' align='center'><span class="linkthru"><a href="{$ibforums->base_url}&act=SF&f={$Data['forum_id']}">{$Data['forum_name']}</a></span></td>
      <td align='center' class='forum1'>{$Data['starter']}</td>
      <td align='center' class='forum2'>{$Data['posts']}</td>
      <td align='center' class='forum1'>{$Data['views']}</td>
      <td class='forum1'>{$Data['last_post']}<br><a href='{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}&act=ST&f={$Data['forum_id']}&t={$Data['tid']}&view=getlastpost'>{$Data['last_text']}</a> <b>{$Data['last_poster']}</b></td>
    </tr>
    <!-- End Topic Entry {$Data['tid']} -->
EOF;
    }

    public function RenderPostRow($Data)
    {
        global $ibforums;

        return <<<EOF
    <!-- Begin Topic Entry {$Data['tid']} -->
    <tr> 
	  <td align='center' class='titlemedium' colspan='2'>
	  	<table cellpadding='5' cellspacing='0' width='100%' border='0'>
	  	 <tr>
	  	  <td align='left'>{$Data['folder_img']}</td>
	  	  <td align='left' width='100%'><b>{$ibforums->lang['rp_topic']} <span class='linkthru'>{$Data['prefix']} <a href='{$ibforums->base_url}&act=ST&f={$Data['forum_id']}&t={$Data['tid']}&hl={$Data['keywords']}' class='linkthru'>{$Data['title']}</a></span></b>  {$Data[PAGES]}</td>
         </tr>
        </table>
       </td>
      </tr>
      <tr>
       <td width='150' align='left' class='posthead'><span class='normalname'>{$Data['author_name']}</span></td>
       <td class='posthead'>
         {$ibforums->lang['rp_forum']} <span class="linkthru"><a href="{$ibforums->base_url}&act=SF&f={$Data['forum_id']}">{$Data['forum_name']}</a></span>&nbsp;&nbsp;&nbsp;&nbsp;{$ibforums->lang['rp_postedon']} {$Data['post_date']}&nbsp;&nbsp;&nbsp;&nbsp;{$ibforums->lang['rp_post']} <a href='{$ibforums->base_url}&act=ST&f={$Data['forum_id']}&t={$Data['tid']}&hl={$Data['keywords']}&view=findpost&p={$Data['pid']}' class='linkthru'>#{$Data['pid']}</a>
       </td>
       </tr>
       <tr>
       <td class='post1' align='left'>
        <img src='{$ibforums->vars['img_url']}/spacer.gif' alt='' width='150' height='15'>
        <br>
        <span class='postdetails'>{$ibforums->lang['rp_replies']} <b>{$Data['posts']}</b><br>{$ibforums->lang['rp_hits']} <b>{$Data['views']}</b></span>
       </td>
       <td class='post1' align='left'>{$Data['post']}</td>
      </tr>
      <tr> 
        <td class='postsep' colspan='2'><img src='{$ibforums->vars['img_url']}/spacer.gif' alt='' width='1' height='1'></td>
	</tr>
    <!-- End Topic Entry {$Data['tid']} -->
EOF;
    }

    public function start_as_post($Data)
    {
        global $ibforums;

        return <<<EOF
   <!-- Cgi-bot Start Forum page unique top -->
   <table cellpadding='0 'cellspacing='4' border='0' width='<{tbl_width}>' align='center'>
      <tr>
         <td valign='middle' width='50%' nowrap align='left'>{$Data[SHOW_PAGES]}</td>
      </tr>
     </table>
       <table cellpadding='0' cellspacing='1' border='0' width='<{tbl_width}>' bgcolor='<{tbl_border}>' align='center'>
        <tr>
            <td class='mainbg'>
            	<table cellpadding='0' cellspacing='0' border='0' width='100%' align='center' >
        		 <tr>
            		<td>
					 <table cellpadding='2' cellspacing='1' border='0' width='100%'>
					   <tr>
						  <td align='center' width='150' class='maintitle'>{$ibforums->lang['rp_author']}</td>
						  <td align='center' width='100%' class='maintitle'>{$ibforums->lang['rp_message']}</td>
					   </tr>
EOF;
    }

    public function end_as_post($Data)
    {
        global $ibforums;

        return <<<EOF
  <tr><td colspan='2' class='titlemedium'>&nbsp;</td></tr>
  </table>
  </td>
  </tr>
  </table>
  </td>
  </tr>
  </table>
  <table cellpadding='0 'cellspacing='4' border='0' width='<{tbl_width}>' align='center'>
      <tr>
         <td valign='middle' width='50%' nowrap align='left'>{$Data[SHOW_PAGES]}</td>
      </tr>
     </table>
   <table cellpadding='0' cellspacing='4' border='0' width='50%' align='center'>
     <tr>
        <td valign='middle' nowrap><{B_NEW}>&nbsp;{$ibforums->lang['pm_open_new']}</td>
        <td valign='middle' nowrap><{B_HOT}>&nbsp;{$ibforums->lang['pm_hot_new']}</td>
        <td valign='middle' nowrap><{B_POLL}>&nbsp;{$ibforums->lang['pm_poll']}</td>
        <td valign='middle' nowrap><{B_LOCKED}>&nbsp;{$ibforums->lang['pm_locked']}</td>
     </tr>
     <tr>
        <td valign='middle' nowrap><{B_NORM}>&nbsp;{$ibforums->lang['pm_open_no']}</td>
        <td valign='middle' nowrap><{B_HOT_NN}>&nbsp;{$ibforums->lang['pm_hot_no']}</td>
        <td valign='middle' nowrap><{B_POLL_NN}>&nbsp;{$ibforums->lang['pm_poll_no']}</td>
        <td valign='middle' nowrap><{B_MOVED}>&nbsp;{$ibforums->lang['pm_moved']}</td>
      </tr>
   </table>
EOF;
    }

    public function end($Data)
    {
        global $ibforums;

        return <<<EOF
  <tr><td colspan='8' class='titlemedium'>&nbsp;</td></tr>
  </table>
  </td>
  </tr>
  </table>
  </td>
  </tr>
  </table>
  <table cellpadding='0 'cellspacing='4' border='0' width='<{tbl_width}>' align='center'>
      <tr>
         <td valign='middle' width='50%' nowrap align='left'>{$Data[SHOW_PAGES]}</td>
      </tr>
     </table>
   <table cellpadding='0' cellspacing='4' border='0' width='50%' align='center'>
     <tr>
        <td valign='middle' nowrap><{B_NEW}>&nbsp;{$ibforums->lang['pm_open_new']}</td>
        <td valign='middle' nowrap><{B_HOT}>&nbsp;{$ibforums->lang['pm_hot_new']}</td>
        <td valign='middle' nowrap><{B_POLL}>&nbsp;{$ibforums->lang['pm_poll']}</td>
        <td valign='middle' nowrap><{B_LOCKED}>&nbsp;{$ibforums->lang['pm_locked']}</td>
     </tr>
     <tr>
        <td valign='middle' nowrap><{B_NORM}>&nbsp;{$ibforums->lang['pm_open_no']}</td>
        <td valign='middle' nowrap><{B_HOT_NN}>&nbsp;{$ibforums->lang['pm_hot_no']}</td>
        <td valign='middle' nowrap><{B_POLL_NN}>&nbsp;{$ibforums->lang['pm_poll_no']}</td>
        <td valign='middle' nowrap><{B_MOVED}>&nbsp;{$ibforums->lang['pm_moved']}</td>
      </tr>
   </table>
EOF;
    }

    public function Form($forums, $cats)
    {
        global $ibforums;

        return <<<EOF
    <!-- Search Form -->
    
    <SCRIPT LANGUAGE="JavaScript">
    	<!--
        function chooseForum() {
        	document.sForm.cat_forum[1].checked = true;
        }
        function chooseCat() {
        	document.sForm.cat_forum[0].checked = true;
        }
        //-->
        </SCRIPT>
    <form action="{$ibforums->base_url}&act=Search&CODE=01" method="post" name='sForm'>
    $hidden_fields
    <br>
    <table cellpadding='0' cellspacing='1' border='0' width='<{tbl_width}>' bgcolor='<{tbl_border}>' align='center'>
        <tr>
            <td>
                <table cellpadding='4' cellspacing='1' border='0' width='100%'>
                    <tr>
                        <td colspan='2' class="maintitle"  align='center'>{$ibforums->lang['keywords_title']}</td>
                    </tr>
                    <tr>
                        <td class='titlemedium' width='50%'><b>{$ibforums->lang['key_search']}</b></td>
                        <td class='titlemedium' width='50%'><b>{$ibforums->lang['mem_search']}</b></td>
                    </tr>
                    <tr>
                        <td class='row1' valign='top'><input type='text' maxlength='100' size='40' name='keywords' class='forminput'><br><br>{$ibforums->lang['keysearch_text']}</td>
                        <td class='row1' valign='top'>
                        	<table width='100%' cellpadding='4' cellspacing='0' border='0' align='center'>
                        	 <tr>
                        	  <td colspan='2'><input type='text' maxlength='100' size='50' name='namesearch' class='forminput'></td>
                        	 </tr>
                        	 <td width='40%'>
                        		    <input name='exactname' type='radio' value='1' checked>&nbsp;{$ibforums->lang['match_name_ex']}
                        		<br><input name='exactname' type='radio' value='0'>&nbsp;{$ibforums->lang['match_name_pa']}
                        	 </td>
                        	 <td width='60%'>
                        	        <input name='joinname' type='radio' value='1' checked>&nbsp;{$ibforums->lang['joinname_true']}
                        		<br><input name='joinname' type='radio' value='0'>&nbsp;{$ibforums->lang['joinname_false']}
                        	 </td>
                        	</tr>
                         </table>
                        </td>
                    </tr>
                </table>
             </td>
           </tr>
         </table>
         
         
         <br>
         
         
   		<table cellpadding='0' cellspacing='1' border='0' width='<{tbl_width}>' bgcolor='<{tbl_border}>' align='center'>
          <tr>
              <td>
                <table cellpadding='4' cellspacing='1' border='0' width='100%'>         
                    
                    <tr>
                        <td colspan='2' class="maintitle"  align='center'>{$ibforums->lang['search_options']}</td>
                    </tr>
                    
                    <tr>
                        <td class='titlemedium' width='50%' valign='middle'><b>{$ibforums->lang['search_where']}</b></td>
                        <td class='titlemedium' width='50%' valign='middle'><b>{$ibforums->lang['search_refine']}</b></td>
                    </tr>
                    
                    <tr>
                        <td class='row1' valign='middle'>
                        <table cellspacing='4' cellpadding='0' width='100%' align='center' border='0'>
                        	<tr>
                        	 <td valign='top' width='40%' nowrap><input type='radio' name='cat_forum' value='cat'>&nbsp;<b>{$ibforums->lang['search_cats']}</b></td>
                        	 <td valign='top' width='60%'>$cats</td>
                        	</tr>
                        	<tr>
                        	<td valign='top' nowrap><input type='radio' name='cat_forum' value='forum' checked>&nbsp;<b>{$ibforums->lang['search_forums']}</b></td>
                        	<td valign='top'>$forums</td>
                        	</tr>
                        	<tr>
                        	 <td valign='top' colspan=2><input type='checkbox' name='searchsubs' value='1' checked>&nbsp;{$ibforums->lang['search_in_subs']}</td>
                        	</tr>
                        	<tr>
                        	 <td><input type='radio' name='search_in' value='posts' checked>&nbsp;{$ibforums->lang['in_posts']}<br><input type='radio' name='search_in' value='titles'>&nbsp;{$ibforums->lang['in_topics']}</td>
                        	 <td><input type='radio' name='result_type' value='topics' checked>&nbsp;{$ibforums->lang['results_topics']}<br><input type='radio' name='result_type' value='posts'>&nbsp;{$ibforums->lang['results_post']}</td>
                        	</tr>
                          </table>
                        </td>
                        <td class='row1' valign='top'>
                        	<table cellspacing='4' cellpadding='0' width='100%' align='center' border='0'>
                        	<tr>
                        	 <td valign='top'>
                        		<b>{$ibforums->lang['search_from']}</b>
                       			<br>
								<select name='prune' class='forminput'>
								<option value='1'>{$ibforums->lang['today']}
								<option value='7'>{$ibforums->lang['this_week']}
								<option value='30' selected>{$ibforums->lang['this_month']}
								<option value='365'>{$ibforums->lang['this_year']}
								<option value='0'>{$ibforums->lang['ever']}
								</select>
                             	<br>{$ibforums->lang['and']}&nbsp;<input type='radio' name='prune_type' value='older' class='forminput' style='background-color:<{MISCBACK_ONE}>'>&nbsp;{$ibforums->lang['older']}&nbsp;<input type='radio' name='prune_type' value='newer' class='forminput' style='background-color:<{MISCBACK_ONE}>' checked>&nbsp;{$ibforums->lang['newer']}
                            </td>
                            <td valign='top'>
								<b>{$ibforums->lang['sort_results']}</b><br>
								<select name='sort_key' class='forminput'>
								<option value='last_post'>{$ibforums->lang['last_date']}</option>
								<option value='posts'>{$ibforums->lang['number_topics']}</option>
								<option value='starter_name'>{$ibforums->lang['poster_name']}</option>
								<option value='forum_id'>{$ibforums->lang['forum_name']}</option>
								</select>
								<br><input type='radio' name='sort_order' value='desc' checked>{$ibforums->lang['descending']}
								<br><input type='radio' name='sort_order' value='asc''>{$ibforums->lang['ascending']}
                            </td>
                            </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td class='row1' colspan='2' align='center'><input type='submit' value='{$ibforums->lang['do_search']}' class='forminput'></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    </form>
    
EOF;
    }

    public function active_start($Data)
    {
        global $ibforums;

        return <<<EOF
   <script language='Javascript'>
   <!--
    function checkvalues() {
    	f = document.dateline;
    	if (f.st_day.value < f.end_day.value) {
    		alert("{$ibforums->lang['active_js_error']}");
    		return false;
    	}
    	if (f.st_day.value == f.end_day.value) {
    		alert("{$ibforums->lang['active_js_error']}");
    		return false;
    	}
    }
    -->
    </script>
    <br>
   <table cellpadding='0 'cellspacing='4' border='0' width='<{tbl_width}>' align='center'>
      <tr>
         <td valign='middle' nowrap align='left'><span class='pagetitle'>{$ibforums->lang['active_topics']}</span><br>{$Data[SHOW_PAGES]}</td>
         <td valign='middle' align='right'>
          <form action='{$ibforums->base_url}&act=Search&CODE=getactive' method='POST' name='dateline' onSubmit='return checkvalues();'>
         </td>
        </tr>
     </table>
     <table cellpadding='2' cellspacing='0' border='0' width='<{tbl_width}>' bgcolor='<{tbl_border}>' align='center'>
       	<tr> 
    		<td class='maintitle'  align='center'> 
      			{$ibforums->lang['active_st_text']}
				<select name='st_day' class='forminput'>
				 <option value='s1'>{$ibforums->lang['active_yesterday']}</option>
				 <option value='s2'>2 {$ibforums->lang['active_days']}</option>
				 <option value='s3'>3 {$ibforums->lang['active_days']}</option>
				 <option value='s4'>4 {$ibforums->lang['active_days']}</option>
				 <option value='s5'>5 {$ibforums->lang['active_days']}</option>
				 <option value='s6'>6 {$ibforums->lang['active_days']}</option>
				 <option value='s7'>{$ibforums->lang['active_week']}</option>
				 <option value='s30'>{$ibforums->lang['active_month']}</option>
				</select>
				&nbsp;&nbsp;{$ibforums->lang['active_end_text']}&nbsp;&nbsp;
				<select name='end_day' class='forminput'>
				 <option value='e0'>{$ibforums->lang['active_today']}</option>
				 <option value='e1'>{$ibforums->lang['active_yesterday']}</option>
				 <option value='e2'>2 {$ibforums->lang['active_days']}</option>
				 <option value='e3'>3 {$ibforums->lang['active_days']}</option>
				 <option value='e4'>4 {$ibforums->lang['active_days']}</option>
				 <option value='e5'>5 {$ibforums->lang['active_days']}</option>
				 <option value='e6'>6 {$ibforums->lang['active_days']}</option>
				 <option value='e7'>{$ibforums->lang['active_week']}</option>
				</select>
				&nbsp;&nbsp;<input type='submit' value='&gt;&gt;' class='forminput'></form>
    		</td>
  		</tr>
        <tr>
            <td>
            	<table cellpadding='0' cellspacing='0' border='0' width='100%' align='center'>
        		 <tr>
            		<td class='mainbg'>
					 <table cellpadding='4' cellspacing='1' border='0' width='100%'>
					   <tr>
						  <td class='titlemedium' colspan='2' >&nbsp;</td>
						  <td align='left' class='titlemedium'>{$ibforums->lang['h_topic_title']}</td>
						  <td align='center' class='titlemedium'>{$ibforums->lang['h_forum_name']}</td>
						  <td align='center' class='titlemedium'>{$ibforums->lang['h_topic_starter']}</td>
						  <td align='center' class='titlemedium'>{$ibforums->lang['h_replies']}</td>
						  <td align='center' class='titlemedium'>{$ibforums->lang['h_hits']}</td>
						  <td class='titlemedium'>{$ibforums->lang['h_last_action']}</td>
					   </tr>
EOF;
    }

    public function start($Data)
    {
        global $ibforums;

        return <<<EOF
   <!-- Cgi-bot Start Forum page unique top -->
   <table cellpadding='0 'cellspacing='4' border='0' width='<{tbl_width}>' align='center'>
      <tr>
         <td valign='middle' width='50%' nowrap align='left'>{$Data[SHOW_PAGES]}</td>
      </tr>
     </table>
       <table cellpadding='1' cellspacing='0' border='0' width='<{tbl_width}>' bgcolor='<{tbl_border}>' align='center'>
        <tr>
            <td>
            	<table cellpadding='0' cellspacing='0' border='0' width='100%' align='center'>
        		 <tr>
            		<td class='mainbg'>
					 <table cellpadding='2' cellspacing='1' border='0' width='100%'>
					   <tr>
						  <td class='titlemedium' colspan='2' >&nbsp;</td>
						  <td align='left' class='titlemedium'>{$ibforums->lang['h_topic_title']}</td>
						  <td align='center' class='titlemedium'>{$ibforums->lang['h_forum_name']}</td>
						  <td align='center' class='titlemedium'>{$ibforums->lang['h_topic_starter']}</td>
						  <td align='center' class='titlemedium'>{$ibforums->lang['h_replies']}</td>
						  <td align='center' class='titlemedium'>{$ibforums->lang['h_hits']}</td>
						  <td class='titlemedium'>{$ibforums->lang['h_last_action']}</td>
					   </tr>
EOF;
    }

    public function active_none()
    {
        global $ibforums;

        return <<<EOF
<tr><td colspan='8' class='row1' align='center'><b>{$ibforums->lang['active_no_topics']}</b></td></tr>
EOF;
    }
}

<?php

class skin_msg
{
    public function preview($data)
    {
        global $ibforums;

        return <<<EOF

	<td colspan='2' class='row1'>
     <table cellpadding='0' cellspacing='1' border='0' width='100%' bgcolor='<{tbl_border}>' align='center'>
        <tr>
            <td>
                <table cellpadding='5' cellspacing='1' border='0' width='100%'>
                <tr>
                <td class='category' valign='top' align='left'><b>{$ibforums->lang['pm_preview']}</b></td>
                </tr>
                <tr>
                <td class='row1' valign='top' align='left'>$data</td>
                </tr>
                </table>
            </td>
        </tr>
    </table>
    </td>
    </tr>
    <tr>

EOF;
    }

    public function pm_errors($data)
    {
        global $ibforums;

        return <<<EOF

	<td colspan='2' class='row1'>
     <table cellpadding='0' cellspacing='1' border='0' width='100%' bgcolor='<{tbl_border}>' align='center'>
        <tr>
            <td>
                <table cellpadding='5' cellspacing='1' border='0' width='100%'>
                <tr>
                <td class='category' valign='top' align='left'><span class='highlight'><b>{$ibforums->lang['pme_title']}</b></span></td>
                </tr>
                <tr>
                <td class='row1' valign='top' align='left'>$data<br><br>{$ibforums->lang['pme_none_sent']}</td>
                </tr>
                </table>
            </td>
        </tr>
    </table>
    </td>
    </tr>
    <tr>

EOF;
    }

    public function pm_popup($text, $mid)
    {
        global $ibforums;

        return <<<EOF
<script language='javascript'>
<!--
 function goto_inbox() {
 	opener.document.location.href = '{$ibforums->base_url}&act=Msg&CODE=01';
 	window.close();
 }
 
 function goto_this_inbox() {
 	window.resizeTo('700','500');
 	document.location.href = '{$ibforums->base_url}&act=Msg&CODE=01';
 }
 
 function go_read_msg() {
 	window.resizeTo('700','500');
 	document.location.href = '{$ibforums->base_url}&act=Msg&CODE=03&VID=in&MSID=$mid';
 }
 
//-->
</script>

<table cellspacing='1' cellpadding='10' width='100%' height='100%' align='center' class='row1'>
<tr>
   <td class='pagetitle' align='center'>{$ibforums->lang['pmp_title']}</td>
</tr>
<tr>
   <td align='center'>$text</td>
</tr>
<tr>
   <td align='center' style='font-size:12px;font-weight:bold'>
   <a href='javascript:go_read_msg();'>{$ibforums->lang['pmp_get_last']}</a>
   <br><br>
   <a href='javascript:goto_inbox();'>{$ibforums->lang['pmp_go_inbox']}</a> ( <a href='javascript:goto_this_inbox();'>{$ibforums->lang['pmp_thiswindow']}</a> )<br><br><a href='javascript:window.close();'>{$ibforums->lang['pmp_ignore']}</a></td>
</tr>
</table>
EOF;
    }

    public function archive_html_header()
    {
        global $ibforums;

        return <<<EOF
<html>
 <head>
  <title>Private Message Archive</title>
 </head>
 <body bgcolor='#FFFFFF'>
EOF;
    }

    public function archive_html_entry($info)
    {
        global $ibforums;

        return <<<EOF
<table cellspacing='1' cellpadding='0' width='80%' bgcolor='#000000' align='center'>
 <tr>
  <td>
   <table cellspacing='1' cellpadding='4' width='100%' align='center'>
    <tr>
     <td valign='top' bgcolor='#FEFEFE'><font face='verdana' size='3'><b>{$info['msg_title']}</b></td>
    </tr>
    <tr>
    <td bgcolor='#FFFFFF'><font face='verdana size='2'>{$info['msg_content']}</td>
    </tr>
    <tr>
    <td valign='top' bgcolor='#FEFEFE'><font face='verdana' size='3'>Sent by <b>{$info['msg_sender']}</b> on {$info['msg_date']}</td>
    </tr>
   </table>
  </td>
 </tr>
</table>
<br>
EOF;
    }

    public function archive_html_footer()
    {
        global $ibforums;

        return <<<EOF
 </body>
</html>
EOF;
    }

    public function archive_complete()
    {
        global $ibforums;

        return <<<EOF
                   <td class='row1 colspan='2'><span class='pagetitle'>{$ibforums->lang['arc_comp_title']}</span><br>{$ibforums->lang['arc_complete']}</td>
                 </tr>
EOF;
    }

    public function archive_form($jump_html = '')
    {
        global $ibforums;

        return <<<EOF
                   <td class='row1 colspan='2'><span class='pagetitle'>{$ibforums->lang['archive_title']}</span><br>{$ibforums->lang['archive_text']}</td>
                 </tr>
                <tr>
                   <td class='row1 colspan='2'>
                    <form action="{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}" method="post">
                    <input type='hidden' name='act' value='Msg'>
                    <input type='hidden' name='CODE' value='15'>
                    <input type='hidden' name='s' value='{$ibforums->session_id}'>
                   </td>
                 </tr>
                 <tr>
                 	<td class='row1'><b>{$ibforums->lang['arc_folders']}</b></td>
                 	<td class='row1'>$jump_html</td>
                 </tr>
                 <tr>
					<td class='row2'><b>{$ibforums->lang['arc_dateline']}</b></td>
					<td class='row2' valign='middle'><select name='dateline' class='forminput'><option value='1'>1</option><option value='7'>7</option><option value='30' selected>30</option><option value='90'>90</option><option value='365'>365</option><option value='all'>{$ibforums->lang['arc_alldays']}</option></select>&nbsp;&nbsp;{$ibforums->lang['arc_days']}</td>
				 </tr>
				 <tr>
					<td class='row1'><b>{$ibforums->lang['arc_max']}</b></td>
					<td class='row1' valign='middle'><select name='number' class='forminput'><option value='5'>5</option><option value='10'>10</option><option value='20' selected>20</option><option value='30'>30</option><option value='40'>40</option><option value='50'>50</option></select></td>
				 </tr>
				 <tr>
					<td class='row2'><b>{$ibforums->lang['arc_delete']}</b></td>
					<td class='row2' valign='middle'><select name='delete' class='forminput'><option value='yes' selected>{$ibforums->lang['arc_yes']}</option><option value='no'>{$ibforums->lang['arc_no']}</option></select></td>
				 </tr>
				 <tr>
					<td class='row1'><b>{$ibforums->lang['arc_type']}</b></td>
					<td class='row1' valign='middle'><select name='type' class='forminput'><option value='xls' selected>{$ibforums->lang['arc_xls']}</option><option value='html'>{$ibforums->lang['arc_html']}</option></select></td>
				 </tr>
				 <tr>
				  <td colspan='2' align='center' class='row2'><input type="submit" value="{$ibforums->lang['arc_submit']}" class='forminput'></td>
				 </tr>
				 </form>
EOF;
    }

    public function No_msg_inbox()
    {
        global $ibforums;

        return <<<EOF
      <tr>
      <td class='row1' colspan='5' align='center'><b>{$ibforums->lang['inbox_no_msg']}</b></td>                
      </tr>
EOF;
    }

    public function prefs_add_dirs()
    {
        global $ibforums;

        return <<<EOF
                <tr>
                   <td colspan='2' class='category'><b>{$ibforums->lang['prefs_new']}</b></td>
                 </tr>
                <tr>
                   <td class='row1 colspan='2'>{$ibforums->lang['prefs_text_b']}</td>
                 </tr>
EOF;
    }

    public function end_address_table()
    {
        global $ibforums;

        return <<<EOF
</table></td></tr>
EOF;
    }

    public function Address_header()
    {
        global $ibforums;

        return <<<EOF
                   <td colspan='2' class='category'><b>{$ibforums->lang['address_current']}</b></td>
                 </tr>
EOF;
    }

    public function Address_none()
    {
        global $ibforums;

        return <<<EOF
      <tr>
      <td class='row1 colspan='2' align='center'><b>{$ibforums->lang['address_none']}</b></td>                
      </tr>
EOF;
    }

    public function address_edit($data)
    {
        global $ibforums;

        return <<<EOF
                  <td colspan='2' class='category'><b>{$ibforums->lang['member_edit']}</b></font></td>
                  </tr>
                  <tr>
                  <td class='row1 align='left' valign='middle' colspan='2'>
                    <table cellspacing='1' cellpadding='2' width='100%' border='0'>
                    <form action="{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}" method="post">
                    <input type='hidden' name='act' value='Msg'>
                    <input type='hidden' name='CODE' value='12'>
                    <input type='hidden' name='s' value='{$ibforums->session_id}'>
                    <input type='hidden' name='MID' value='{$data[MEMBER]['contact_id']}'>
                    <tr>
                    <td valign='middle' align='left'><b>{$data[MEMBER]['contact_name']}</b></td>
                    <td valign='middle' align='left'>{$ibforums->lang['enter_desc']}<br><input type='text' name='mem_desc' size='30' maxlength='60' value='{$data[MEMBER]['contact_desc']}' class='forminput'></td>
                    <td valign='middle' align='left'>{$ibforums->lang['allow_msg']}<br>{$data[SELECT]}</td>
                    </tr>
                    <tr>
                    <td colspan='3' align='center'><input type="submit" value="{$ibforums->lang['submit_address_edit']}" class='forminput'></td>
                    </tr>
                    </form>
                    </table>
                    </td>
                    </tr>
EOF;
    }

    public function render_address_row($entry)
    {
        global $ibforums;

        return <<<EOF
                 <tr>
                   <td class='row1 align='left' valign='middle'><a href='{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}&act=Profile&CODE=03&MID={$entry['contact_id']}'><b>{$entry['contact_name']}</b></a> &nbsp; &nbsp;[ {$entry['contact_desc']} ]</td>
                   <td class='row1 align='left' valign='middle'>
                        [ <a href='{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}&act=Msg&CODE=11&MID={$entry['contact_id']}' class='misc'>{$ibforums->lang['edit']}</a> ] :: [ <a href='{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}&act=Msg&CODE=10&MID={$entry['contact_id']}'>{$ibforums->lang['delete']}</a> ]
                         &nbsp;&nbsp;( {$entry['text']} )
                   </td>
                 </tr>
EOF;
    }

    public function address_add($mem_to_add)
    {
        global $ibforums;

        return <<<EOF
                  <tr>
                  <td colspan='2' class='category'><b>{$ibforums->lang['member_add']}</b></font></td>
                  </tr>
                  <tr>
                  <td class='row1 align='left' valign='middle' colspan='2'>
                    <table cellspacing='1' cellpadding='2' width='100%' border='0'>
                    <form action="{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}" method="post">
                    <input type='hidden' name='act' value='Msg'>
                    <input type='hidden' name='CODE' value='09'>
                    <input type='hidden' name='s' value='{$ibforums->session_id}'>
                    <tr>
                    <td valign='middle' align='left'>{$ibforums->lang['enter_a_name']}<br><input type='text' name='mem_name' size='20' maxlength='40' value='$mem_to_add' class='forminput'></td>
                    <td valign='middle' align='left'>{$ibforums->lang['enter_desc']}<br><input type='text' name='mem_desc' size='30' maxlength='60' value='' class='forminput'></td>
                    <td valign='middle' align='left'>{$ibforums->lang['allow_msg']}<br><select name='allow_msg' class='forminput'><option value='yes' selected>{$ibforums->lang['yes']}<option value='no'>{$ibforums->lang['no']}</select></td>
                    </tr>
                    <tr>
                    <td class='row2' colspan='3' align='center'><input type="submit" value="{$ibforums->lang['submit_address']}" class='forminput'></td>
                    </tr>
                    </form>
                    </table>
                    </td>
                    </tr>
EOF;
    }

    public function prefs_footer()
    {
        global $ibforums;

        return <<<EOF
                 <tr>
                   <td class='row2 colspan='2' align='center'><input type='submit' value='{$ibforums->lang['prefs_submit']}' class='forminput'></td>
                 </tr></form>
EOF;
    }

    public function prefs_row($data)
    {
        global $ibforums;

        return <<<EOF
                 <tr>
                   <td class='row1 colspan='2'><input type='text' name='{$data[ID]}' value='{$data[REAL]}' class='forminput'>{$data[EXTRA]}</td>
                 </tr>
EOF;
    }

    public function Address_table_header()
    {
        global $ibforums;

        return <<<EOF
                 <tr>
                 <td valign='top' class='row1 colspan='2'>
                 <table cellpadding='4' cellspacing='0' align='center' width='100%' style='border:1px solid {$this->class['SKIN']['TABLE_BORDER_COL']}'>
                 <tr>
                   <td class='row2 align='left' width='60%' class='titlemedium'><b>{$ibforums->lang['member_name']}</b></td>
                   <td class='row2 align='left' width='40%' class='titlemedium'><b>{$ibforums->lang['enter_block']}</b></td>
                 </tr>
EOF;
    }

    public function Render_msg($data)
    {
        global $ibforums;

        return <<<EOF
			<script language='javascript'>
			<!--
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
			<td class='row1 align='left' width='100%'><span  class='pagetitle'>{$data['msg']['title']}</span><br>{$data['msg']['msg_date']}</td>
            <td class='row1 align='right' nowrap>
            [ <a href='{$ibforums->base_url}&CODE=04&act=Msg&MSID={$data['msg']['msg_id']}&MID={$data['member']['id']}&fwd=1'>{$ibforums->lang['vm_forward_pm']}</a> | <a href='{$ibforums->base_url}&CODE=04&act=Msg&MID={$data['member']['id']}&MSID={$data['msg']['msg_id']}'>{$ibforums->lang['pm_reply_link']}</a> ]
            </td>
            </tr>
           
            <tr>
             <td colspan='2'>
			  <table width='100%' cellpadding='0' cellspacing='1' bgcolor='<{tbl_border}>'>
			  	<tr>
			  	 <td class='mainbg'>
			  	  <table width='100%' cellpadding='4' cellspacing='1' border='0'>
                <tr>
                	<td valign='middle' class='titlemedium' align='left' width='25%'>{$ibforums->lang['author']}</td>
                	<td valign='middle' class='titlemedium' align='left' width='75%'>
                		<table width='100%' cellpadding='0' cellspacing='0' border='0' align='left'>
                		 <tr>
                		 	<td align='left' width='100%' valign='middle' class='titlemedium'>{$ibforums->lang['m_pmessage']}</td>
                		 	<td align='left' valign='middle' nowrap class='titlemedium'>[ <a href='{$ibforums->base_url}&CODE=05&act=Msg&MSID={$data['msg']['msg_id']}&VID={$data['member']['VID']}'>{$ibforums->lang['vm_delete_pm']}</a> ]</td>
                		 </tr>
                		 </table>
                    </td>
                </tr>
                <tr>
        		    <td valign='top' class='row1'><span class='normalname'>{$data['member']['name']}</span><br>{$data['member']['avatar']}<br><span class="membertitle">{$data['member']['title']}</span><span class='postdetails'><br>{$data['member']['MEMBER_GROUP']}<br>{$data['member']['MEMBER_POSTS']}<br>{$data['member']['MEMBER_JOINED']}</span></td>
                    <td valign='top' height='100%' class='row1'>
            		    <span class='postcolor'>
           			     {$data['msg']['message']}
                         {$data['member']['signature']}
                        </span>
                    </td>
                 </tr>
                 <tr>
                   <td class='postfoot' align='center'>[ <a href='{$ibforums->base_url}&CODE=02&act=Msg&MID={$data['member']['id']}'>{$ibforums->lang['add_to_book']}</a> ]</td>
                   <td class='postfoot' align='left'>{$data['member']['MESSAGE_ICON']}{$data['member']['EMAIL_ICON']}{$data['member']['WEBSITE_ICON']}{$data['member']['ICQ_ICON']}{$data['member']['AOL_ICON']}{$data['member']['YAHOO_ICON']}</td>
                 </tr>
			  </table>
			</td>
		</tr>
		</table>
		</td>
		</tr>
        <tr>
        <td class='row1 align='left' width='100%'>
                     <form action="{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}" name='jump' method="post">
                     <input type='hidden' name='act' value='Msg'>
                     <input type='hidden' name='CODE' value='01'>
                     <input type='hidden' name='s'    value='{$ibforums->session_id}'>
                     <font class='misc'><b>{$ibforums->lang[goto_folder]}:</b>&nbsp; {$data['jump']}
                     <input type='submit' name='submit' value='{$ibforums->lang[goto_submit]}' class='forminput'>
                     </form>
            </td>
            <td class='row1 align='right' nowrap>
            	[ <a href='{$ibforums->base_url}&CODE=04&act=Msg&MSID={$data['msg']['msg_id']}&MID={$data['member']['id']}&fwd=1'>{$ibforums->lang['vm_forward_pm']}</a> | <a href='{$ibforums->base_url}&CODE=04&act=Msg&MID={$data['member']['id']}&MSID={$data['msg']['msg_id']}'>{$ibforums->lang['pm_reply_link']}</a> ]
            </td>
        </tr>


EOF;
    }

    public function trackread_table_header()
    {
        global $ibforums;

        return <<<EOF
                 <!-- inbox folder -->
                     <script language='JavaScript'>
                     <!--
                     function CheckAll(fmobj) {
                         for (var i=0;i<fmobj.elements.length;i++) {
                             var e = fmobj.elements[i];
                             if ((e.name != 'allbox') && (e.type=='checkbox') && (!e.disabled)) {
                                 e.checked = fmobj.allbox.checked;
                             }
                         }
                     }
                     function CheckCheckAll(fmobj) {	
                         var TotalBoxes = 0;
                         var TotalOn = 0;
                         for (var i=0;i<fmobj.elements.length;i++) {
                             var e = fmobj.elements[i];
                             if ((e.name != 'allbox') && (e.type=='checkbox')) {
                                 TotalBoxes++;
                                 if (e.checked) {
                                     TotalOn++;
                                 }
                             }
                         }
                         if (TotalBoxes==TotalOn) {fmobj.allbox.checked=true;}
                         else {fmobj.allbox.checked=false;}
                     }
                     //-->
                     </script>
                 
                 <form action="{$ibforums->base_url}&CODE=31&act=Msg" name='trackread' method="post">
                 <tr>
                 <td valign='top' colspan='2'><span class='pagetitle'>{$ibforums->lang['tk_read_messages']}</span><br>{$ibforums->lang['tk_read_desc']}</td>
                 </tr>
                 <tr>
                 <td valign='top' colspan='2'>
                 <table cellpadding='4' cellspacing='1' align='center' width='100%' style='border:1px solid <{tbl_border}>'>
                 <tr>
                   <td  align='left' width='5%' class='titlemedium'>&nbsp;</td>
                   <td  align='left' width='30%' class='titlemedium'><b>{$ibforums->lang['message_title']}</b></td>
                   <td  align='left' width='30%' class='titlemedium'><b>{$ibforums->lang['pms_message_to']}</b></td>
                   <td  align='left' width='20%' class='titlemedium'><b>{$ibforums->lang['tk_read_date']}</b></td>
                   <td  align='left' width='5%' class='titlemedium'><input name="allbox" type="checkbox" value="Check All" onClick="CheckAll(document.trackread);"></td>
                 </tr>
EOF;
    }

    public function trackread_row($data)
    {
        global $ibforums;

        return <<<EOF
                 <tr>
                   <td class='row2' align='left' valign='middle'>{$data['icon']}</td>
                   <td class='row2' align='left'>{$data['title']}</td>
                   <td class='row2' align='left'><a href='{$ibforums->base_url}&act=Profile&MID={$data['memid']}'>{$data['to_name']}</a></td>
                   <td class='row2' align='left'>{$data['date']}</td>
                   <td class='row2' align='left'><input type='checkbox' name='msgid_{$data['msg_id']}' value='yes' class='forminput' onClick="CheckCheckAll(document.trackread);"></td>
                 </tr>
EOF;
    }

    public function trackread_end()
    {
        global $ibforums;

        return <<<EOF
				  <tr>
				   <td align='right' nowrap class='titlemedium' colspan='6'><input type='submit' name='endtrack' value='{$ibforums->lang['tk_untrack_button']}' class='forminput'> {$ibforums->lang['selected_msg']}</td>
                  </tr>
                  </table>
                  </form>
                  </td></tr>
                  
EOF;
    }

    public function trackUNread_table_header()
    {
        global $ibforums;

        return <<<EOF
                 <form action="{$ibforums->base_url}&CODE=32&act=Msg" name='trackunread' method="post">
                 <tr>
                 <td valign='top' colspan='2'><span class='pagetitle'>{$ibforums->lang['tk_unread_messages']}</span><br>{$ibforums->lang['tk_unread_desc']}</td>
                 </tr>
                 <tr>
                 <td valign='top' colspan='2'>
                 <table cellpadding='4' cellspacing='1' align='center' width='100%' style='border:1px solid <{tbl_border}>'>
                 <tr>
                   <td  align='left' width='5%' class='titlemedium'>&nbsp;</td>
                   <td  align='left' width='30%' class='titlemedium'><b>{$ibforums->lang['message_title']}</b></td>
                   <td  align='left' width='30%' class='titlemedium'><b>{$ibforums->lang['pms_message_to']}</b></td>
                   <td  align='left' width='20%' class='titlemedium'><b>{$ibforums->lang['tk_unread_date']}</b></td>
                   <td  align='left' width='5%' class='titlemedium'><input name="allbox" type="checkbox" value="Check All" onClick="CheckAll(document.trackunread);"></td>
                 </tr>
EOF;
    }

    public function trackUNread_row($data)
    {
        global $ibforums;

        return <<<EOF
                 <tr>
                   <td class='row2' align='left' valign='middle'>{$data['icon']}</td>
                   <td class='row2' align='left'>{$data['title']}</td>
                   <td class='row2' align='left'><a href='{$ibforums->base_url}&act=Profile&MID={$data['memid']}'>{$data['to_name']}</a></td>
                   <td class='row2' align='left'>{$data['date']}</td>
                   <td class='row2' align='left'><input type='checkbox' name='msgid_{$data['msg_id']}' value='yes' class='forminput' onClick="CheckCheckAll(document.trackunread);"></td>
                 </tr>
EOF;
    }

    public function trackUNread_end()
    {
        global $ibforums;

        return <<<EOF
				  <tr>
				   <td align='right' nowrap class='titlemedium' colspan='6'><input type='submit' name='delete' value='{$ibforums->lang['delete_button']}' class='forminput'> {$ibforums->lang['selected_msg']}</td>
                  </tr>
                  </table>
                  </form>
                  </td></tr>
                  
EOF;
    }

    public function unsent_table_header()
    {
        global $ibforums;

        return <<<EOF
                 <!-- inbox folder -->
                     <script language='JavaScript'>
                     <!--
                     function CheckAll(cb) {
                         var fmobj = document.mutliact;
                         for (var i=0;i<fmobj.elements.length;i++) {
                             var e = fmobj.elements[i];
                             if ((e.name != 'allbox') && (e.type=='checkbox') && (!e.disabled)) {
                                 e.checked = fmobj.allbox.checked;
                             }
                         }
                     }
                     function CheckCheckAll(cb) {	
                         var fmobj = document.mutliact;
                         var TotalBoxes = 0;
                         var TotalOn = 0;
                         for (var i=0;i<fmobj.elements.length;i++) {
                             var e = fmobj.elements[i];
                             if ((e.name != 'allbox') && (e.type=='checkbox')) {
                                 TotalBoxes++;
                                 if (e.checked) {
                                     TotalOn++;
                                 }
                             }
                         }
                         if (TotalBoxes==TotalOn) {fmobj.allbox.checked=true;}
                         else {fmobj.allbox.checked=false;}
                     }
                     //-->
                     </script>
                 
                 <form action="{$ibforums->base_url}&CODE=06&act=Msg&saved=1" name='mutliact' method="post">
                 <tr>
                 <td valign='top' colspan='2'><span class='pagetitle'>{$ibforums->lang['pms_saved_title']}</span></td>
                 </tr>
                 <tr>
                 <td valign='top' colspan='2'>
                 <table cellpadding='4' cellspacing='1' align='center' width='100%' style='border:1px solid <{tbl_border}>'>
                 <tr>
                   <td  align='left' width='5%' class='titlemedium'>&nbsp;</td>
                   <td  align='left' width='30%' class='titlemedium'><b>{$ibforums->lang['message_title']}</b></td>
                   <td  align='left' width='30%' class='titlemedium'><b>{$ibforums->lang['pms_message_to']}</b></td>
                   <td  align='left' width='20%' class='titlemedium'><b>{$ibforums->lang['pms_saved_date']}</b></td>
                   <td  align='left' width='10%' class='titlemedium'><b>{$ibforums->lang['pms_cc_users']}</b></td>
                   <td  align='left' width='5%' class='titlemedium'><input name="allbox" type="checkbox" value="Check All" onClick="CheckAll();"></td>
                 </tr>
EOF;
    }

    public function unsent_row($data)
    {
        global $ibforums;

        return <<<EOF
                 <tr>
                   <td class='row2' align='left' valign='middle'>{$data['msg']['icon']}</td>
                   <td class='row2' align='left'><a href='{$ibforums->base_url}&act=Msg&CODE=21&MSID={$data['msg']['msg_id']}'>{$data['msg']['title']}</a></td>
                   <td class='row2' align='left'><a href='{$ibforums->base_url}&act=Profile&MID={$data['msg']['recipient_id']}'>{$data['msg']['to_name']}</a></td>
                   <td class='row2' align='left'>{$data['msg']['date']}</td>
                   <td class='row2' align='center'>{$data['msg']['cc_users']}</td>
                   <td class='row2' align='left'><input type='checkbox' name='msgid_{$data['msg']['msg_id']}' value='yes' class='forminput'></td>
                 </tr>
EOF;
    }

    public function unsent_end()
    {
        global $ibforums;

        return <<<EOF
				  <tr>
				   <td align='center' nowrap class='titlemedium' colspan='6'><input type='submit' name='delete' value='{$ibforums->lang['delete_button']}' class='forminput'> {$ibforums->lang['selected_msg']}</td>
                  </tr>
                  </table>
                  </form>
                  </td></tr>
                  
EOF;
    }

    public function inbox_table_header($dirname, $info, $vdi_html = '', $pages = '')
    {
        global $ibforums;

        return <<<EOF
                 <!-- inbox folder -->
                     <script language='JavaScript'>
                     <!--
                     
                     var ie  = document.all  ? 1 : 0;
                     //var ns4 = document.layers ? 1 : 0;
                     
                     function hl(cb)
                     {
                     	if (ie)
						{
							while (cb.tagName != "TR")
							{
								cb = cb.parentElement;
							}
						}
						else
						{
							while (cb.tagName != "TR")
							{
								cb = cb.parentNode;
							}
						}
						cb.className = 'hlight';
                     }
                     
                     function dl(cb) {
                     	if (ie)
						{
							while (cb.tagName != "TR")
							{
								cb = cb.parentElement;
							}
						}
						else
						{
							while (cb.tagName != "TR")
							{
								cb = cb.parentNode;
							}
						}
						cb.className = 'dlight';
                     }
                     
                     function cca(cb) {
                     	if (cb.checked)
                     	{
                     		hl(cb);
                     	}
                     	else
                     	{
                     		dl(cb);
                     	}
                     }
                     		
                     function CheckAll(cb) {
                         var fmobj = document.mutliact;
                         for (var i=0;i<fmobj.elements.length;i++) {
                             var e = fmobj.elements[i];
                             if ((e.name != 'allbox') && (e.type=='checkbox') && (!e.disabled)) {
                                 e.checked = fmobj.allbox.checked;
                                 if (fmobj.allbox.checked)
                                 {
                                 	hl(e);
                                 }
                                 else
                                 {
                                 	dl(e);
                                 }
                             }
                         }
                     }
                     function CheckCheckAll(cb) {	
                         var fmobj = document.mutliact;
                         var TotalBoxes = 0;
                         var TotalOn = 0;
                         for (var i=0;i<fmobj.elements.length;i++) {
                             var e = fmobj.elements[i];
                             if ((e.name != 'allbox') && (e.type=='checkbox')) {
                                 TotalBoxes++;
                                 if (e.checked) {
                                     TotalOn++;
                                 }
                             }
                         }
                         if (TotalBoxes==TotalOn) {fmobj.allbox.checked=true;}
                         else {fmobj.allbox.checked=false;}
                     }
                     
                     function select_read() {	
                         var fmobj = document.mutliact;
                         for (var i=0;i<fmobj.elements.length;i++) {
                             var e = fmobj.elements[i];
                             if ((e.type=='hidden') && (e.value == 1) && (! isNaN(e.name) ))
                             {
                                 eval("fmobj.msgid_" + e.name + ".checked=true;");
                                 hl(e);
                             }
                         }
                     }
                     
                     function unselect_all() {	
                         var fmobj = document.mutliact;
                         for (var i=0;i<fmobj.elements.length;i++) {
                             var e = fmobj.elements[i];
                             if (e.type=='checkbox') {
                                 e.checked=false;
                                 dl(e);
                             }
                         }
                     }
                     
                     //-->
                     </script>
                 
                 <tr>
                 <td valign='top' colspan='2'><span class='pagetitle'>$dirname</span></td>
                 </tr>
                 <tr>
                   <td colspan='2'>
                     <table cellpadding='1' cellspacing='0' width='100%' align='center'>
                      <tr>
                       <td align='left' valign='middle' width='300'>
                         <img src='{$ibforums->vars['img_url']}/spacer.gif' alt='' width='300' height='1'>
                         <br>
                         <table cellpadding='0' cellspacing='1' width='100%' align='center' border='0' bgcolor='<{tbl_border}>'>
                          <tr>
                            <td class='mainbg'>
                              <table cellpadding='4' cellspacing='1' width='100%' align='center' border='0'>
                               <tr>
                                <td class='row1' align='left' colspan='3'>{$info['full_messenger']}</td>
                               </tr>
                               <tr>
                            	<td align='left' valign='middle' class='row2' colspan='3'><img src='{$ibforums->vars['img_url']}/bar_left.gif' border='0' width='4' height='11' align='middle' alt=''><img src='{$ibforums->vars['img_url']}/bar.gif' border='0' width='{$info['img_width']}' height='11' align='middle' alt=''><img src='{$ibforums->vars['img_url']}/bar_right.gif' border='0' width='4' height='11' align='middle' alt=''></td>
                               </tr>
                               <tr>
                                 <td class='row1' width='33%' align='left' valign='middle'>0%</td>
                                 <td class='row1' width='33%' align='center' valign='middle'>50%</td>
                                 <td class='row1' width='33%' align='right' valign='middle'>100%</td>
                               </tr>
                   		      </table>
                   		     </td>
                   		    </tr>
                   		  </table>
                   		 </td>
                   		 <!-- Right side -->
                   		 <td width='100%' align='right' valign='bottom'>
                   		  $pages<br><br>
                   		  <a href='javascript:select_read()'>{$ibforums->lang['pmpc_mark_read']}</a> :: <a href='javascript:unselect_all()'>{$ibforums->lang['pmpc_unmark_all']}</a><br><br>
                   		  <form action="{$ibforums->base_url}&CODE=01&act=Msg" name='jump' method="post">
						  <b>{$ibforums->lang['goto_folder']}: </b>&nbsp; $vdi_html <input type='submit' name='submit' value='{$ibforums->lang['goto_submit']}' class='forminput'>
						  </form>
                   		 </td>
                   		</tr>
                   	  </table>
                   	 </td>
                   </tr>
                 <tr>
                 <td valign='top' colspan='2' class='dlight'>
                 <form action="{$ibforums->base_url}&CODE=06&act=Msg" name='mutliact' method="post">
                 <table cellpadding='4' cellspacing='1' align='center' width='100%' style='border:1px solid <{tbl_border}>'>
                 <tr>
                   <td  align='left' width='5%' class='titlemedium'>&nbsp;</td>
                   <td  align='left' width='40%' class='titlemedium'><a href='{$ibforums->base_url}&act=Msg&CODE=01&VID={$info['vid']}&sort=title&st={$ibforums->input['st']}'><b>{$ibforums->lang['message_title']}</b></a></td>
                   <td  align='left' width='30%' class='titlemedium'><a href='{$ibforums->base_url}&act=Msg&CODE=01&VID={$info['vid']}&sort=name&st={$ibforums->input['st']}'><b>{$ibforums->lang['message_from']}</b></a></td>
                   <td  align='left' width='20%' class='titlemedium'><a href='{$ibforums->base_url}&act=Msg&CODE=01&VID={$info['vid']}&sort={$info['date_order']}&st={$ibforums->input['st']}'><b>{$ibforums->lang['message_date']}</b></a></td>
                   <td  align='left' width='5%' class='titlemedium'><input name="allbox" type="checkbox" value="Check All" onClick="CheckAll();"></td>
                 </tr>
EOF;
    }

    public function inbox_row($data)
    {
        global $ibforums;

        return <<<EOF
                 <tr>
                   <td align='left' valign='middle'>{$data['msg']['icon']}</td>
                   <td align='left'><a href='{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}&act=Msg&CODE=03&VID={$data['stat']['current_id']}&MSID={$data['msg']['msg_id']}'>{$data['msg']['title']}</a></td>
                   <td align='left'><a href='{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}&act=Profile&MID={$data['msg']['from_id']}'>{$data['msg']['from_name']}</a> {$data['msg']['add_to_contacts']}</td>
                   <td align='left'>{$data['msg']['date']}</td>
                   <td align='left'><input type='hidden' name='{$data['msg']['msg_id']}' value='{$data['msg']['read_state']}'><input type='checkbox' name='msgid_{$data['msg']['msg_id']}' value='yes' class='forminput' onClick="cca(this);"></td>
                 </tr>
EOF;
    }

    public function end_inbox($vdi_html, $amount_info = '', $pages = '')
    {
        global $ibforums;

        return <<<EOF
				  <tr>
				   <td align='right' nowrap class='titlemedium' colspan='5'>
                     <input type='submit' name='move' value='{$ibforums->lang['move_button']}' class='forminput'> $vdi_html {$ibforums->lang['move_or_delete']} <input type='submit' name='delete' value='{$ibforums->lang['delete_button']}' class='forminput'> {$ibforums->lang['selected_msg']}
                  </td>
                  </tr>
                  </table>
                  </form>
                  </td>
                  </tr>
                  <tr>
                  <td class='row1 align='left' valign='middle'>
                    <{M_READ}>&nbsp;{$ibforums->lang['icon_read']}<br><{M_UNREAD}>&nbsp;{$ibforums->lang['icon_unread']}
                  </td>
                  <td class='row1' align='right' valign='middle' nowrap>$pages<br><i>$amount_info</i></td>
                  </tr>
EOF;
    }

    public function send_form_footer()
    {
        global $ibforums;

        return <<<EOF
				<tr>
				 <td colspan='2' class='category'><b>{$ibforums->lang['msg_options']}</b></td>
                </tr>
			    <tr>
			     <td class='row1' align='left' width='40%'>&nbsp;</td>
				 <td class='row1' align='left' width='60%'>
				    <input type='checkbox' name='add_sent' value='yes' checked>&nbsp;<b>{$ibforums->lang['auto_sent_add']}</b>
				    <br><input type='checkbox' name='add_tracking' value='1'>&nbsp;<b>{$ibforums->lang['vm_track_msg']}</b>
				 </td>
				</tr>
                <tr>
                <td class='row2' align='center' colspan='2'>
                <input type="submit" value="{$ibforums->lang['submit_send']}" class='forminput' name='submit'>
                <input type="submit" value="{$ibforums->lang['pm_pre_button']}" class='forminput' name='preview'>
                <input type="submit" value="{$ibforums->lang['pms_send_later']}" class='forminput' name='save'>
                </td>
                </tr>
                </form>
EOF;
    }

    public function Send_form($data)
    {
        global $ibforums;

        return <<<EOF

	 <script language='javascript'>
	 <!--
	 function find_users()
	 {
		 
	   window.open('index.{$ibforums->vars['php_ext']}?act=legends&CODE=finduser_one&s={$ibforums->session_id}&entry=textarea&name=carbon_copy&sep=line','FindUsers','width=400,height=250,resizable=yes,scrollbars=yes'); 
		  
	 }
	 //-->
	</script>
     <form action="{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}" method="post" name='REPLIER' onSubmit='return ValidateForm(1)'>
     <input type='hidden' name='act' value='Msg'>
     <input type='hidden' name='CODE' value='04'>
     <input type='hidden' name='MODE' value='01'>
     <input type='hidden' name='OID'  value='{$data['OID']}'>
     <input type='hidden' name='s' value='{$ibforums->session_id}'>
                 
                 <td colspan='2' class='solidborder'>
                  <table cellpadding='4' cellspacing='0' border='0' width='100%'>
                   <tr>
                     <td colspan='2' class='category'><b>{$ibforums->lang['to_whom']}</b></td>
                   </tr>
				   <tr>
					 <td class='row1' align='left' width='40%'>{$ibforums->lang['address_list']}</td>
					 <td class='row1' align='left' width='60%' valign='top'>{$data[CONTACTS]}</td>
				   </tr>  
				   <tr>
					 <td class='row1' align='left' width='40%'>{$ibforums->lang['enter_name']}</td>
					 <td class='row1' align='left' width='60%' valign='top'><input type='text' name='entered_name' size='50' value='{$data[N_ENTER]}' class='forminput'></td>
				   </tr>
				   <!--IBF.MASS_PM_BOX-->
				  </table>
				 </td>
				</tr>
				<tr>
				   <td class='row1' colspan='2'>&nbsp;</td>
				</tr>
                <tr>
                <td colspan='2' class='category'><b>{$ibforums->lang['enter_message']}</b></td>
                </tr>
                <tr>
                <td class='row1' align='left'  width='40%'>{$ibforums->lang['msg_title']}</td>
                <td class='row1' align='left' width='60%' valign='top'><input type='text' name='msg_title' size='40' maxlength='40' value='{$data[O_TITLE]}' class='forminput'></td>
                </tr>
EOF;
    }

    public function mass_pm_box($names = '')
    {
        global $ibforums;

        return <<<EOF
     
                <tr>
                <td colspan='2' class='category'><b>{$ibforums->lang['carbon_copy_title']}</b></td>
                </tr>
                <tr>
                <td class='row1' align='left'  width='40%' valign='top'>{$ibforums->lang['carbon_copy_desc']}</td>
                <td class='row1' align='left' width='60%' valign='middle'>
                  <table cellspacing='0' width='100%' cellpadding='4' border='0'>
                  	<tr>
                  	  <td align='left' valign='middle'><textarea name='carbon_copy' rows='5' cols='40'>$names</textarea></td>
                  	  <td align='left' width='100%' valign='middle'>
                  	   <input type='button' class='forminput' name='findusers' onClick='find_users()' value='{$ibforums->lang['find_user_names']}'>
                  	  </td>
                  	 </tr>
                   </table>
                </td>
                </tr>
EOF;
    }

    public function prefs_header()
    {
        global $ibforums;

        return <<<EOF
                   <td colspan='2' class='category'><b>{$ibforums->lang['prefs_current']}</b></td>
                 </tr>
                <tr>
                   <td class='row1 colspan='2'>{$ibforums->lang['prefs_text_a']}</td>
                 </tr>
                    <form action="{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}" method="post">
                    <input type='hidden' name='act' value='Msg'>
                    <input type='hidden' name='CODE' value='08'>
                    <input type='hidden' name='s' value='{$ibforums->session_id}'>
EOF;
    }
}

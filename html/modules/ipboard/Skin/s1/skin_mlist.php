<?php

class skin_mlist
{
    public function Page_header($links)
    {
        global $ibforums;

        return <<<EOF
<script language='Javascript' type='text/javascript'>
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

<table width="<{tbl_width}>" border="0" align='center' cellspacing="0" cellpadding="4">
  <tr> 
    <td width="100%" height='38'>{$links[SHOW_PAGES]}</td>
  </tr>
</table>
<table width="<{tbl_width}>" border="0" align='center' cellspacing="1" cellpadding="0" bgcolor="<{tbl_border}>">
  <tr> 
    <td class='maintitle' > 
      &nbsp;
    </td>
  </tr>
  <tr> 
    <td class='mainbg'> 
      <table width="100%" border="0" cellspacing="1" cellpadding="4">
				<tr> 
          <td nowrap  class='titlemedium' width="30%">{$ibforums->lang['member_name']}</td>
          <td nowrap  class='titlemedium' align="center" width="20%">{$ibforums->lang['member_level']}</td>
          <td nowrap  class='titlemedium' align="center" width="20%">{$ibforums->lang['member_group']}</td>
          <td nowrap  class='titlemedium' align="center" width="20%">{$ibforums->lang['member_joined']}</td>
          <td nowrap  class='titlemedium' align="center" width="10%">{$ibforums->lang['member_posts']}</td>
          <td nowrap  class='titlemedium' align="center">{$ibforums->lang['member_email']}</td>
          <td nowrap  class='titlemedium' align="center">{$ibforums->lang['member_aol']}</td>
          <td nowrap  class='titlemedium' align="center">{$ibforums->lang['member_icq']}</td>
        </tr>
EOF;
    }

    public function end($links)
    {
        global $ibforums;

        return <<<EOF
<table width="<{tbl_width}>" border="0" align='center' cellspacing="0" cellpadding="4">
  <tr> 
    <td width="100%" height='38'>{$links[SHOW_PAGES]}</td>
  </tr>
</table>
EOF;
    }

    public function no_results()
    {
        global $ibforums;

        return <<<EOF
No results
EOF;
    }

    public function start()
    {
        global $ibforums;

        return <<<EOF
<!-- nothing here -->
EOF;
    }

    public function Page_end()
    {
        global $ibforums;

        return <<<EOF
            <!-- End content Table -->
        
		<form action='{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id};act=Members' method='POST'>
        <input type='hidden' name='act' value='Members'>
        <input type='hidden' name='s'   value='{$ibforums->session_id}'>
        <tr> 
          <td class='postfoot' colspan="9" align='center' valign='middle'>
          <select class='forminput' name='name_box'>
           <option value='begins'>{$ibforums->lang['ch_begins']}</option>
           <option value='contains'>{$ibforums->lang['ch_contains']}</option>
           <option value='all' selected>{$ibforums->lang['ch_all']}</option>
           </select>&nbsp;&nbsp;<input class='forminput' type='text' size='25' name='name' value='{$ibforums->input['name']}'><br>
          {$ibforums->lang['sorting_text']}&nbsp;<input type='submit' value='{$ibforums->lang['sort_submit']}' class='forminput'></td>
        </tr>
	</form>
        
      </table>
    </td>
  </tr>
</table>
EOF;
    }

    public function show_row($member)
    {
        global $ibforums;

        return <<<EOF
              <!-- Entry for {$member[MEMBER_NAME]} -->
							<tr> 
								<td class='forum2' width="30%"><b><a href="{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}&act=Profile&CODE=03&MID={$member[MEMBER_ID]}">{$member[MEMBER_NAME]}</a></b></td>
								<td class='forum2' nowrap width="20%">{$member[MEMBER_PIPS_IMG]}</td>
								<td class='forum1' align="center" width="20%">{$member[MEMBER_GROUP]}</td>
								<td class='forum2' nowrap align="center" width="20%">{$member[MEMBER_JOINED]}</td>
								<td class='forum2' align="center" width="10%">{$member[MEMBER_POSTS]}</td>
								<td class='forum1' align="center">{$member[MEMBER_EMAIL]}</td>
								<td class='forum1' align="center">{$member[AOLNAME]}</td>
								<td class='forum1' align="center">{$member[ICQNUMBER]}</td>
							</tr>
              <!-- End of Entry -->
EOF;
    }
}

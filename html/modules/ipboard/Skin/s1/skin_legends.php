<?php

class skin_legends
{
    public function find_user_one($entry = '', $name = '', $sep = 'comma')
    {
        global $ibforums;

        return <<<EOF

<form name='finduser' method='post' action='{$ibforums->base_url}&act=legends&entry=$entry&name=$name&sep=$sep&CODE=finduser_two'>
<table cellspacing='1' cellpadding='10' width='100%' height='100%' align='center' class='row1'>
<tr>
   <td class='pagetitle' align='left'>{$ibforums->lang['fu_title']}<hr noshade></td>
</tr>
<tr>
   <td align='center' valign='middle'><b>{$ibforums->lang['fu_enter_name']}</b><br><br><input type='text' size='40' name='username' class='forminput'><br><br><input type='submit' value='{$ibforums->lang['fu_search_but']}'></td>
</tr>
<tr>
   <td align='center' style='font-size:12px;font-weight:bold'><a href='javascript:window.close();'>{$ibforums->lang['fu_close_win']}</a></td>
</tr>
</table>
</form>

EOF;
    }

    public function find_user_error($msg)
    {
        global $ibforums;

        return <<<EOF

<form name='finduser' method='post' action='{$ibforums->base_url}&entry=$entry&name=$name&sep=$sep&CODE=finduser_two'>
<table cellspacing='1' cellpadding='10' width='100%' height='100%' align='center' class='row1'>
<tr>
   <td class='pagetitle' align='left'>{$ibforums->lang['fu_error']}<hr noshade></td>
</tr>
<tr>
   <td align='center' valign='middle'>$msg</td>
</tr>
<tr>
   <td align='center' style='font-size:12px;font-weight:bold'><a href='javascript:history.go(-1);'>{$ibforums->lang['fu_back']}</a> :: <a href='javascript:window.close();'>{$ibforums->lang['fu_close_win']}</a></td>
</tr>
</table>
</form>

EOF;
    }

    public function find_user_final($names = '', $entry = '', $name = '', $sep = 'line')
    {
        global $ibforums;

        return <<<EOF

<script language='javascript'>
<!--
	function add_to_form()
	{
		
		var separator = '$sep';
		var entry     = '$entry';
		
		var name = document.finduser.username.options[document.finduser.username.selectedIndex].value;
		
		if (separator == 'line')
		{
			separator = '\\n';
		}
		
		if (entry == 'textarea')
		{
			// Where shall we put the separator?
			
			var tbox     = opener.document.REPLIER.$name.value;
			var tboxSize = opener.document.REPLIER.$name.value.length;
			
			// Remove leading spaces...
			
			while ( opener.document.REPLIER.$name.value.slice(0,1) == " " )
			{
				opener.document.REPLIER.$name.value = opener.document.REPLIER.$name.value.substr(1, opener.document.REPLIER.$name.value.length - 1);
				opener.document.REPLIER.$name.value.length = opener.document.REPLIER.$name.value.length;
			}
			
			// Remove trailing spaces...
			
			while ( opener.document.REPLIER.$name.value.slice(opener.document.REPLIER.$name.value.length - 1, opener.document.REPLIER.$name.value.length) == " " )
			{
				opener.document.REPLIER.$name.value = opener.document.REPLIER.$name.value.substr(0, opener.document.REPLIER.$name.value.length - 1);
				opener.document.REPLIER.$name.value.length = opener.document.REPLIER.$name.value.length;
			}
			
			// Do we have a leading comma?
			
			while ( opener.document.REPLIER.$name.value.slice(0,1) == "\\n" )
			{
				opener.document.REPLIER.$name.value = opener.document.REPLIER.$name.value.substr(1, opener.document.REPLIER.$name.value.length - 1);
				opener.document.REPLIER.$name.value.length = opener.document.REPLIER.$name.value.length;
			}
			
			// Do we have a trailing comma?...
			
			while ( opener.document.REPLIER.$name.value.slice(opener.document.REPLIER.$name.value.length - 1, opener.document.REPLIER.$name.value.length) == "\\n" )
			{
				opener.document.REPLIER.$name.value = opener.document.REPLIER.$name.value.substr(0, opener.document.REPLIER.$name.value.length - 1);
				opener.document.REPLIER.$name.value.length = opener.document.REPLIER.$name.value.length;
			}
			
			// First in box?
			
			if ( opener.document.REPLIER.$name.value.length == 0)
			{
				opener.document.REPLIER.$name.value += name;
			}
			else
			{
				opener.document.REPLIER.$name.value += separator + name;
			}
		}
		
	}
//-->
</script>

<form name='finduser'>
<table cellspacing='1' cellpadding='10' width='100%' height='100%' align='center' class='row1'>
<tr>
   <td class='pagetitle' align='left'>{$ibforums->lang['fu_title']}<hr noshade></td>
</tr>
<tr>
   <td align='center' valign='middle'>{$ibforums->lang['fu_add_desc']}<br><br><select name='username' class='forminput'>$names</select><br><br><input type='button' name='add' onClick='add_to_form()' value='{$ibforums->lang['fu_add_mem']}'></td>
</tr>
<tr>
   <td align='center' style='font-size:12px;font-weight:bold'><a href='javascript:history.go(-1);'>{$ibforums->lang['fu_back']}</a> :: <a href='javascript:window.close();'>{$ibforums->lang['fu_close_win']}</a></td>
</tr>
</table>
</form>

EOF;
    }

    public function emoticon_javascript()
    {
        return <<<EOF
<script language='javascript'>
<!--
	function add_smilie(code)
	{
		opener.document.REPLIER.Post.value += ' ' + code + ' ';
		//return true;
	}
//-->
</script>
EOF;
    }

    public function page_header($title, $row1, $row2)
    {
        global $ibforums;

        return <<<EOF

<table cellpadding='0' cellspacing='0' border='0' width='100%' bgcolor='<{tbl_border}>' align='center'>
        <tr>
            <td>
              <table cellpadding='5' cellspacing='1' border='0' width='100%'>
                <tr>
                   <td colspan='4' class='titlemedium' align='center'>$title</td>
                </tr>
                <tr>
                   <td width='50%' align='center'   class='category'   valign='middle'>$row1</td>
                   <td width='50%' align='center'   class='category'    valign='middle'>$row2</td>
                </tr>


EOF;
    }

    public function emoticons_row($code, $image, $in = "'", $out = "'")
    {
        global $ibforums;

        return <<<EOF

                <tr>
                   <td align='center' class='row1' valign='middle'><a href={$out}javascript:add_smilie({$in}$code{$in}){$out}>$code</a></td>
                   <td align='center' class='row2' valign='middle'><a href={$out}javascript:add_smilie({$in}$code{$in}){$out}><img src='{$ibforums->vars['EMOTICONS_URL']}/$image' border='0' valign='absmiddle' alt='$image'></a></td>
                </tr>


EOF;
    }

    public function page_footer()
    {
        return <<<EOF

				</table>
			  </td>
			 </tr>
			</table>

EOF;
    }
}

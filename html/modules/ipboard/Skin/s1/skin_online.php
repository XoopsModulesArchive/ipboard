<?php

class skin_online
{
    public function show_row($session)
    {
        global $ibforums;

        return <<<EOF
              <!-- Entry for {$session['member_id']} -->
              <tr>
                <td class='row1'>{$session['member_name']}</td>
                <td class='row1'>{$session['where_line']}</td>
                <td class='row1' align='center'>{$session['running_time']}</td>
                <td class='row1' align='center'>{$session['msg_icon']}</td>
              </tr>
              <!-- End of Entry -->
EOF;
    }

    public function Page_end($links)
    {
        global $ibforums;

        return <<<EOF
            <!-- End content Table -->
            <tr>
            <td colspan='4' class='category'>&nbsp;</td>
            </tr>
            </table>
            </td>
            </tr>
            </table>
            <br>
            <table cellpadding='0' cellspacing='4' border='0' width='<{tbl_width}>' align='center'>
            <tr>
              <td valign='middle' align='left'>$links</td>
            </tr>
          </table>
EOF;
    }

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


          <table cellpadding='0' cellspacing='4' border='0' width='<{tbl_width}>' align='center'>
            <tr>
              <td valign='middle' align='left'>$links</td>
            </tr>
          </table>
          <br>
       <table cellpadding='0' cellspacing='0' border='0' width='<{tbl_width}>' bgcolor='<{tbl_border}>' align='center'>
        <tr>
            <td>
              <table cellpadding='4' cellspacing='1' border='0' width='100%'>
                <tr>
                   <td align='left' width='30%' class='titlemedium'>{$ibforums->lang['member_name']}</td>
                   <td align='left' width='30%' class='titlemedium'>{$ibforums->lang['where']}</td>
                   <td align='center' width='20%' class='titlemedium'>{$ibforums->lang['time']}</td>
                   <td align='left' width='10%' class='titlemedium'>&nbsp;</td>
                </tr>
EOF;
    }
}

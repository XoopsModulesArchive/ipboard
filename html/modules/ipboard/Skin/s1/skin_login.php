<?php

class skin_login
{
    public function ShowLogOutForm()
    {
        global $ibforums;

        return <<<EOF
     <br>
     <form action="{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}" method="post">
     <input type='hidden' name='act' value='Login'>
     <input type='hidden' name='CODE' value='03'>
     <input type='hidden' name='s' value='{$ibforums->session_id}'>
     <table cellpadding='0' cellspacing='0' border='0' width='<{tbl_width}>' bgcolor='<{tbl_border}>' align='center'>
        <tr>
            <td>
                <table cellpadding='3' cellspacing='1' border='0' width='100%'>
                <tr>
                <td bgcolor='<{TITLEBACK}>' valign='left' colspan='2' class='titlelarge'>{$ibforums->lang['log_out']}</td>
                </tr>
                <tr>
                <td class='row1' colspan='2' valign='middle'><br>{$ibforums->lang['log_out_txt']}<br></td>
                </tr>
                <tr>
                <td class='row2' align='center' colspan='2'>
                <input type="submit" value="{$ibforums->lang['log_out_submit']}" class='forminput'>
                </td></tr></table>
                </td></tr></table>
                </form>
EOF;
    }

    public function errors($data)
    {
        global $ibforums;

        return <<<EOF
     <table cellpadding='0' cellspacing='1' border='0' width='<{tbl_width}>' bgcolor='<{tbl_border}>' align='center'>
        <tr>
            <td>
                <table cellpadding='5' cellspacing='1' border='0' width='100%'>
                <tr>
                <td class='row1' valign='top' align='left' class='highlight'><b>{$ibforums->lang['errors_found']}</b><hr noshade size='1' color='<{tbl_border}>'>$data</td>
                </tr>
                </table>
            </td>
        </tr>
    </table>
    <br>
EOF;
    }

    public function ShowForm($message, $referer = '')
    {
        global $ibforums;

        return <<<EOF
    <script language='JavaScript'>
    <!--
    function ValidateForm() {
        var Check = 0;
        if (document.LOGIN.UserName.value == '') { Check = 1; }
        if (document.LOGIN.PassWord.value == '') { Check = 1; }

        if (Check == 1) {
            alert("{$ibforums->lang['blank_fields']}");
            return false;
        } else {
            document.LOGIN.submit.disabled = true;
            return true;
        }
    }
    //-->
    </script>     
     <br>
     <table cellpadding='3' cellspacing='1' border='0' align='center' width='<{tbl_width}>'>
     <tr>
     <td align='left'>{$ibforums->lang['login_text']}</td>
     </tr>
     <tr>
     <td align='left'><b>{$ibforums->lang['forgot_pass']} <a href='{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?act=Reg&CODE=10'>{$ibforums->lang['pass_link']}</a></b></td>
     </tr>
     </table>
     <form action="{$ibforums->base_url}&act=Login&CODE=01" method="post" name='LOGIN' onSubmit='return ValidateForm()'>
     <input type='hidden' name='referer' value="$referer">
     <table cellpadding='0' cellspacing='0' border='0' width='<{tbl_width}>' bgcolor='<{tbl_border}>' align='center'>
        <tr>
            <td>
                <table cellpadding='3' cellspacing='1' border='0' width='100%'>
                <tr>
                <td align='left' colspan='2' class='titlemedium'>$message</td>
                </tr>
                <tr>
                <td class='row1' width='40%'>{$ibforums->lang['enter_name']}</td>
                <td class='row1'><input type='text' size='20' maxlength='64' name='UserName' class='forminput'></td>
                </tr>
                <tr>
                <td class='row1' width='40%'>{$ibforums->lang['enter_pass']}</td>
                <td class='row1'><input type='password' size='20' name='PassWord' class='forminput'></td>
                </tr>
                </table>
             </td>
         </tr>
     </table>
     <br>
     <table cellpadding='0' cellspacing='0' border='0' width='<{tbl_width}>' bgcolor='<{tbl_border}>' align='center'>
        <tr>
            <td>
                <table cellpadding='3' cellspacing='1' border='0' width='100%'>
                <tr>
                <td align='left' colspan='2' class='titlemedium'>{$ibforums->lang['options']}</td>
                </tr>
                <tr>
                <td class='row1' width='40%' align='left' valign='top'>{$ibforums->lang['cookies']}</td>
                <td class='row1' width='40%'><input type="radio" name="CookieDate" value="1" checked>{$ibforums->lang['cookie_yes']}<br><input type="radio" name="CookieDate" value="0">{$ibforums->lang['cookie_no']}</td>
                </tr>
                <tr>
                <td class='row1' width='40%' align='left' valign='top'>{$ibforums->lang['privacy']}</td>
                <td class='row1' width='40%'><input type="checkbox" name="Privacy" value="1">{$ibforums->lang['anon_name']}</td>
                </tr>
                <tr>
                <td class='row2' align='center' colspan='2'>
                <input type="submit" name='submit' value="{$ibforums->lang['log_in_submit']}" class='forminput'>
                </td></tr></table>
                </td></tr></table>
                </form>
EOF;
    }
}

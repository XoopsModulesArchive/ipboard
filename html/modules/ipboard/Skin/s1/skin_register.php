<?php

class skin_register
{
    public function coppa_form()
    {
        global $ibforums;

        return <<<EOF
     <html>
      <head>
       <title>{$ibforums->lang['cpf_title']}</title>
       <!--<link rel='stylesheet' href='style_sheets/stylesheet_<{css_id}>.css' type='text/css'>-->
      </head>
     <body bgcolor='white'>
     <table cellpadding='0' cellspacing='4' border='0' width='95%' align='center'>
     <tr>
        <td valign='middle' align='left'>
        	<span class='pagetitle'>{$ibforums->vars['board_name']}: {$ibforums->lang['cpf_title']}</span>
        	<br><br>
        	<b><span style='font-size:12px'>{$ibforums->lang['cpf_perm_parent']}</span></b>
        	<br><br>
        	{$ibforums->lang['cpf_fax']} {$ibforums->vars['coppa_fax']}
        	<br><br>
        	{$ibforums->lang['cpf_address']}
        	<br>
        	{$ibforums->vars['coppa_address']}
        
        </td>
     </tr>
     </table>
     <br>
     <table cellpadding='4' cellspacing='2' border='1' width='95%' align='center'>
      <tr>
		<td width='40%'>{$ibforums->lang['user_name']}</td>
		<td>&nbsp;</td>
		</tr>
		<tr>
		<td width='40%'>{$ibforums->lang['pass_word']}</td>
		<td>&nbsp;</td>
		</tr>
		<tr>
		<td width='40%'>{$ibforums->lang['email_address']}</td>
		<td>&nbsp;</td>
	  </tr>
     </table>
     <br>
     <table cellpadding='0' cellspacing='4' border='0' width='95%' align='center'>
     <tr>
        <td valign='middle' align='left'>
        	<b><span style='font-size:12px'>{$ibforums->lang['cpf_sign']}</span></b>
        </td>
     </tr>
     </table>
     <br>
     <table cellpadding='10' cellspacing='2' border='1' width='95%' align='center'>
      <tr>
		<td width='40%'>{$ibforums->lang['cpf_name']}</td>
		<td>&nbsp;</td>
		</tr>
		<tr>
		<td width='40%'>{$ibforums->lang['cpf_relation']}</td>
		<td>&nbsp;</td>
		</tr>
		<tr>
		<td width='40%'>{$ibforums->lang['cpf_signature']}</td>
		<td>&nbsp;</td>
	    </tr>
	    <tr>
		<td width='40%'>{$ibforums->lang['cpf_email']}</td>
		<td>&nbsp;</td>
		</tr>
		<tr>
		<td width='40%'>{$ibforums->lang['cpf_phone']}</td>
		<td>&nbsp;</td>
		</tr>
		<tr>
		<td width='40%'>{$ibforums->lang['cpf_date']}</td>
		<td>&nbsp;</td>
		</tr>
     </table>
    </body>
  </html>
EOF;
    }

    public function coppa_two()
    {
        global $ibforums;

        return <<<EOF
     <table cellpadding='0' cellspacing='0' border='0' width='<{tbl_width}>' bgcolor='<{tbl_border}>' align='center'>
        <tr>
            <td>
                <table cellpadding='3' cellspacing='1' border='0' width='100%'>
                <tr>
                <td valign='left' class='titlemedium'>{$ibforums->lang['cp2_title']}</td>
                </tr>
                <tr>
                <td class='row1' align='left'>
                	{$ibforums->lang['cp2_text']}
                	<br><br>
                	<center><span style='font-weight:bold;font-size:12px'>
                	 &lt;&lt; <a href='{$ibforums->base_url}'>{$ibforums->lang['cp2_cancel']}</a>
                	- <a href='{$ibforums->base_url}&act=Reg&coppa_pass=1&coppa_user=1'>{$ibforums->lang['cp2_continue']}</a> &gt;&gt;
                	</span></center>
                </td>
                </tr>
                <tr>
                <td valign='left' class='titlemedium'>{$ibforums->lang['coppa_form']}</td>
                </tr>
                <tr>
                 <td class='row1' align='left'>{$ibforums->lang['coppa_form_text']} <a href='mailto:{$ibforums->vars['email_in']}'>{$ibforums->vars['email_in']}</a></td>
                </tr>
                </table>
            </td>
        </tr>
    </table>
EOF;
    }

    public function coppa_start($coppadate)
    {
        global $ibforums;

        return <<<EOF
     <table cellpadding='0' cellspacing='0' border='0' width='<{tbl_width}>' bgcolor='<{tbl_border}>' align='center'>
        <tr>
            <td>
                <table cellpadding='3' cellspacing='1' border='0' width='100%'>
                <tr>
                <td valign='left' class='titlemedium'>{$ibforums->lang['coppa_info']}</td>
                </tr>
                <tr>
                <td class='row1' align='center' style='font-weight:bold;font-size:12px'>
                	<br><br>
                	{$ibforums->lang['coppa_link']}
                	<br><br>
                	&lt; <a href='{$ibforums->base_url}&act=Reg&coppa_pass=1'>{$ibforums->lang['coppa_date_before']} $coppadate</a>
                	- <a href='{$ibforums->base_url}&act=Reg&CODE=coppa_two'>{$ibforums->lang['coppa_date_after']} $coppadate</a> &gt;
                	<br><br>&nbsp;
                </td>
                </tr>
                <tr>
                <td valign='left' class='titlemedium'>{$ibforums->lang['coppa_form']}</td>
                </tr>
                <tr>
                 <td class='row1' align='left'>{$ibforums->lang['coppa_form_text']} <a href='mailto:{$ibforums->vars['email_in']}'>{$ibforums->vars['email_in']}</a></td>
                </tr>
                </table>
            </td>
        </tr>
    </table>
EOF;
    }

    public function lost_pass_form($lasid = '')
    {
        global $ibforums;

        return <<<EOF
     <br>
     <form action="{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}" method="post">
     <input type='hidden' name='act' value='Reg'>
     <input type='hidden' name='CODE' value='11'>
     <table cellpadding='0' cellspacing='4' border='0' width='<{tbl_width}>' align='center'>
     <tr>
        <td valign='middle' align='left'><b>{$ibforums->lang['lp_header']}</b><br><br>{$ibforums->lang['lp_text']}</td>
     </tr>
     </table>
     <br>
     <table cellpadding='0' cellspacing='0' border='0' width='<{tbl_width}>' bgcolor='<{tbl_border}>' align='center'>
        <tr>
            <td>
                <table cellpadding='3' cellspacing='1' border='0' width='100%'>
                	<tr>
                		<td valign='left' colspan='2' class='titlemedium'>{$ibforums->lang['complete_form']}</td>
                	</tr>
                	<tr>
                		<td class='row1' width='40%'>{$ibforums->lang['lp_user_name']}</td>
                		<td class='row1'><input type='text' size='32' maxlength='32' name='member_name' class='forminput'></td>
                	</tr>
					 <tr>
					 <td valign='left' colspan='2' class='titlemedium'>{$ibforums->lang['las_title']}</td>
					 </tr>
					 <tr>
					 <td class='row1' width='40%'>{$ibforums->lang['las_numbers']}</td>
					 <td class='row1'>
					  <input type='hidden' name='regid' value='$lasid'>
					  <img src='{$ibforums->base_url}&act=Reg&CODE=image&rc={$lasid}&p=1' border='0' alt='Code Bit'>
					  &nbsp;<img src='{$ibforums->base_url}&act=Reg&CODE=image&rc={$lasid}&p=2' border='0' alt='Code Bit'>
					  &nbsp;<img src='{$ibforums->base_url}&act=Reg&CODE=image&rc={$lasid}&p=3' border='0' alt='Code Bit'>
					  &nbsp;<img src='{$ibforums->base_url}&act=Reg&CODE=image&rc={$lasid}&p=4' border='0' alt='Code Bit'>
					  &nbsp;<img src='{$ibforums->base_url}&act=Reg&CODE=image&rc={$lasid}&p=5' border='0' alt='Code Bit'>
					  &nbsp;<img src='{$ibforums->base_url}&act=Reg&CODE=image&rc={$lasid}&p=6' border='0' alt='Code Bit'>
					 </td>
					 </tr>
					 <tr>
					 <td class='row2' width='40%'>{$ibforums->lang['las_text']}</td>
					 <td class='row2'><input type='text' size='32' maxlength='32' name='reg_code' class='forminput'></td>
					 </tr>
					 <tr>
                		<td class='row2' align='center' colspan='2'>
                		<input type="submit" value="{$ibforums->lang['lp_send']}" class='forminput'>
                		</td>
                	</tr>
					 </table>
				 </td>
			 </tr>
		  </table>
        </form>
EOF;
    }

    public function show_authorise($member)
    {
        global $ibforums;

        return <<<EOF
     <table cellpadding='0' cellspacing='0' border='0' width='<{tbl_width}>' bgcolor='<{tbl_border}>' align='center'>
        <tr>
            <td>
                <table cellpadding='3' cellspacing='1' border='0' width='100%'>
                <tr>
                <td valign='left' class='titlemedium'>{$ibforums->lang['registration_process']}</td>
                </tr>
                <tr>
                <td class='row1'>{$ibforums->lang['thank_you']} {$member['name']}. {$ibforums->lang['auth_text']}  {$member['email']}</td>
                </tr>
                </table>
            </td>
        </tr>
    </table>
EOF;
    }

    public function show_preview($member)
    {
        global $ibforums;

        return <<<EOF
     <table cellpadding='0' cellspacing='0' border='0' width='<{tbl_width}>' bgcolor='<{tbl_border}>' align='center'>
        <tr>
            <td>
                <table cellpadding='3' cellspacing='1' border='0' width='100%'>
                <tr>
                <td valign='left' class='titlemedium'>{$ibforums->lang['registration_process']}</td>
                </tr>
                <tr>
                <td class='row1'>{$ibforums->lang['thank_you']} {$member['name']}. {$ibforums->lang['preview_reg_text']}</td>
                </tr>
                </table>
            </td>
        </tr>
    </table>
EOF;
    }

    public function show_dumb_form($type = 'reg')
    {
        global $ibforums;

        return <<<EOF
    <script language='javascript'>
    <!--
    function Validate() {
        // Check for Empty fields
        if (document.REG.uid.value == "" || document.REG.aid.value == "") {
            alert ("{$ibforums->lang['js_blanks']}");
            return false;
        }

    }
    //-->
    </script>
     <br>
     <form action="{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}" method="post" name='REG' onSubmit='return Validate()'>
     <input type='hidden' name='act' value='Reg'>
     <input type='hidden' name='CODE' value='03'>
     <input type='hidden' name='type' value='$type'>
     <table cellpadding='0' cellspacing='4' border='0' width='<{tbl_width}>' align='center'>
     <tr>
        <td valign='middle' align='left'><b>{$ibforums->lang['dumb_header']}</b><br><br>{$ibforums->lang['dumb_text']}</td>
     </tr>
     </table>
     <br>
     <table cellpadding='0' cellspacing='0' border='0' width='<{tbl_width}>' bgcolor='<{tbl_border}>' align='center'>
        <tr>
            <td>
                <table cellpadding='3' cellspacing='1' border='0' width='100%'>
                <tr>
                <td valign='left' colspan='2' class='titlemedium'>{$ibforums->lang['complete_form']}</td>
                </tr>
                <tr>
                <td class='row1' width='40%'>{$ibforums->lang['user_id']}</td>
                <td class='row1'><input type='text' size='32' maxlength='32' name='uid' class='forminput'></td>
                </tr>
                <tr>
                <td class='row2' width='40%'>{$ibforums->lang['val_key']}</td>
                <td class='row2'><input type='text' size='32' maxlength='50' name='aid' class='forminput'></td>
                </tr>
                <tr>
                <td class='row2' align='center' colspan='2'>
                <input type="submit" value="{$ibforums->lang['dumb_submit']}" class='forminput'>
                </td></tr></table>
                </td></tr></table>
                </form>
EOF;
    }

    public function ShowForm($data)
    {
        global $ibforums;

        return <<<EOF
    <script language='javascript'>
    <!--
    function Validate() {
        // Check for Empty fields
        if (document.REG.UserName.value == "" || document.REG.PassWord.value == "" || document.REG.PassWord_Check.value == "" || document.REG.EmailAddress.value == "") {
            alert ("{$ibforums->lang['js_blanks']}");
            return false;
        }

        // Have we checked the checkbox?

        if (document.REG.agree.checked === true) {
            return true;
        } else {
            alert ("{$ibforums->lang['js_no_check']}");
            return false;
        }
    }
    //-->
    </script>
     <br>
     <form action="{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}" method="post" name='REG' onSubmit='return Validate()'>
     <input type='hidden' name='act' value='Reg'>
     <input type='hidden' name='CODE' value='02'>
     <input type='hidden' name='coppa_user' value='{$data['coppa_user']}'>
     <table cellpadding='0' cellspacing='4' border='0' width='<{tbl_width}>' align='center'>
     <tr>
        <td valign='middle' align='left'>{$ibforums->lang['reg_header']}</b><br><br>{$data['TEXT']}</td>
     </tr>
     </table>
     <br>
     <table cellpadding='0' cellspacing='0' border='0' width='<{tbl_width}>' bgcolor='<{tbl_border}>' align='center'>
        <tr>
            <td>
                <table cellpadding='3' cellspacing='1' border='0' width='100%'>
                <tr>
                <td valign='left' colspan='2' class='titlemedium'>{$ibforums->lang['complete_form']}</td>
                </tr>
                <tr>
                <td class='row1' width='40%'>{$ibforums->lang['user_name']}</td>
                <td class='row1'><input type='text' size='32' maxlength='64' value='{$ibforums->input['UserName']}' name='UserName' class='forminput'></td>
                </tr>
                <tr>
                <td class='row2' width='40%'>{$ibforums->lang['pass_word']}</td>
                <td class='row2'><input type='password' size='32' maxlength='32' value='{$ibforums->input['PassWord']}' name='PassWord' class='forminput'></td>
                </tr>
                <tr>
                <td class='row2' width='40%'>{$ibforums->lang['re_enter_pass']}</td>
                <td class='row2'><input type='password' size='32' maxlength='32' value='{$ibforums->input['PassWord_Check']}'  name='PassWord_Check' class='forminput'></td>
                </tr>
                <tr>
                <td class='row1' width='40%'>{$ibforums->lang['email_address']}</td>
                <td class='row1'><input type='text' size='32' maxlength='50' value='{$ibforums->input['EmailAddress']}'  name='EmailAddress' class='forminput'></td>
                </tr>
                <tr>
                <td class='row1' width='40%'>{$ibforums->lang['email_address_two']}</td>
                <td class='row1'><input type='text' size='32' maxlength='50'  value='{$ibforums->input['EmailAddress_two']}' name='EmailAddress_two' class='forminput'></td>
                </tr>
                <!--{REQUIRED.FIELDS}-->
                <!--{OPTIONAL.FIELDS}-->
                </table>
            </td>
        </tr>
     </table>
    <!--{REG.ANTISPAM}-->
     <br>
     <table cellpadding='0' cellspacing='0' border='0' width='<{tbl_width}>' bgcolor='<{tbl_border}>' align='center'>
        <tr>
            <td>
                <table cellpadding='3' cellspacing='1' border='0' width='100%'>
                <tr>
                <td  valign='left' class='titlemedium'>{$ibforums->lang['terms_service']}</td>
                </tr>
                <tr>
                <td class='row1' align='center'>{$ibforums->lang['terms_service_text']}<br>
                    <textarea cols='75' rows='9' wrap='soft' name='Post' class='textinput' style='font-size:10px'>{$data[RULES]}</textarea>
                    <br><br><b>{$ibforums->lang['agree_submit']}</b>&nbsp;<input type='checkbox' name='agree' value='1'>
                </td>
                </tr>
                <tr>
                <td class='row2' align='center'>
                <input type="submit" value="{$ibforums->lang['submit_form']}" class='forminput'>
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
                <td class='row1' valign='top' align='left'><b>{$ibforums->lang['errors_found']}</b></font><hr noshade size='1' color='<{tbl_border}>'>$data</td>
                </tr>
                </table>
            </td>
        </tr>
    </table>
    <br>
EOF;
    }

    public function reg_antispam($regid)
    {
        global $ibforums;

        return <<<EOF
 <br>
     <table cellpadding='0' cellspacing='0' border='0' width='<{tbl_width}>' bgcolor='<{tbl_border}>' align='center'>
        <tr>
            <td>
                <table cellpadding='3' cellspacing='1' border='0' width='100%'>
                <tr>
                <td valign='left' colspan='2' class='titlemedium'>{$ibforums->lang['ras_title']}</td>
                </tr>
                <tr>
                <td class='row1' width='40%'>{$ibforums->lang['ras_numbers']}</td>
                <td class='row1'>
                 <input type='hidden' name='regid' value='$regid'>
                 <img src='{$ibforums->base_url}&act=Reg&CODE=image&rc={$regid}&p=1' border='0' alt='Code Bit'>
                 &nbsp;<img src='{$ibforums->base_url}&act=Reg&CODE=image&rc={$regid}&p=2' border='0' alt='Code Bit'>
                 &nbsp;<img src='{$ibforums->base_url}&act=Reg&CODE=image&rc={$regid}&p=3' border='0' alt='Code Bit'>
                 &nbsp;<img src='{$ibforums->base_url}&act=Reg&CODE=image&rc={$regid}&p=4' border='0' alt='Code Bit'>
                 &nbsp;<img src='{$ibforums->base_url}&act=Reg&CODE=image&rc={$regid}&p=5' border='0' alt='Code Bit'>
                 &nbsp;<img src='{$ibforums->base_url}&act=Reg&CODE=image&rc={$regid}&p=6' border='0' alt='Code Bit'>
                </td>
                </tr>
                <tr>
                <td class='row2' width='40%'>{$ibforums->lang['ras_text']}</td>
                <td class='row2'><input type='text' size='32' maxlength='32' name='reg_code' class='forminput'></td>
                </tr>
                </table>
            </td>
        </tr>
     </table>
EOF;
    }

    public function optional_title()
    {
        global $ibforums;

        return <<<EOF
     		    <tr>
                <td valign='left' colspan='2' class='titlemedium'>{$ibforums->lang['cf_optional']}</td>
                </tr>
EOF;
    }

    public function field_entry($title, $desc, $content)
    {
        global $ibforums;

        return <<<EOF
            <tr>
			<td class='row1' width='40%' valign='top'><b>$title</b><br>$desc</td>
			<td class='row1'>$content</td>
			</tr>
EOF;
    }

    public function field_textinput($name, $value = '')
    {
        global $ibforums;

        return <<<EOF
            <input type='text' size='50' name='$name' value='$value' class='forminput'>
EOF;
    }

    public function field_dropdown($name, $options)
    {
        global $ibforums;

        return <<<EOF
            <select name='$name' class='forminput'>$options</select>
EOF;
    }

    public function field_textarea($name, $value)
    {
        global $ibforums;

        return <<<EOF
            <textarea cols='60' rows='5' wrap='soft' name='$name' class='forminput'>$value</textarea>
EOF;
    }
}

<?php

class skin_ucp
{
    public function birthday($day, $month, $year)
    {
        global $ibforums;

        return <<<EOF
            <tr>
            <td class='row2' width='40%'><b>{$ibforums->lang['birthday']}</b></td>
            <td class='row2'>
            <select name='day' class='forminput'>{$day}</select> 
            <select name='month' class='forminput'>{$month}</select> 
            <select name='year' class='forminput'>{$year}</select>
            </td>
            </tr>
EOF;
    }

    public function email_change($txt = '', $regid = '', $msg = '')
    {
        global $ibforums;

        return <<<EOF
                   <td  colspan='2' class='category'><b>{$ibforums->lang['change_email_title']}</b></td>
                 </tr>
				
				 <script language='Javascript'>
				    var msg = "$msg";
				    if ( msg != "")
				    {
				    	alert(msg);
				    }
				 </script>
				 
                 <form action="{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}" method="post" name='form1'>
                 <input type='hidden' name='act' value='UserCP'>
                 <input type='hidden' name='CODE' value='09'>
                 <input type='hidden' name='s' value='{$ibforums->session_id}'>
                 <tr>
                 	<td class='row1' colspan='2'>$txt</td>
                 </tr>
                 <tr>
                   <td class='row1' nowrap><b>{$ibforums->lang['ce_new_email']}</b></td>
                   <td class='row1' width='70%' align='left'><input type='text' name='in_email_1' value='' class='forminput'></td>
                 </tr>
                  <tr>
                   <td class='row1' nowrap><b>{$ibforums->lang['ce_new_email2']}</b></td>
                   <td class='row1' width='70%' align='left'><input type='text' name='in_email_2' value='' class='forminput'></td>
                 </tr>
                 
				 <tr>
					 <td colspan='2'>
						 <table cellpadding='3' cellspacing='1' border='0' width='100%'>
						 <tr>
						 <td valign='left' colspan='2' class='titlemedium'>{$ibforums->lang['ras_title']}</td>
						 </tr>
						 <tr>
						 <td class='row1' width='40%'>{$ibforums->lang['ras_numbers']}</td>
						 <td class='row1'>
						  <input type='hidden' name='regid' value='$regid'>
						  <img src='{$ibforums->base_url}&act=UserCP&CODE=show_image&rc={$regid}&p=1' border='0' alt='Code Bit'>
						  &nbsp;<img src='{$ibforums->base_url}&act=UserCP&CODE=show_image&rc={$regid}&p=2' border='0' alt='Code Bit'>
						  &nbsp;<img src='{$ibforums->base_url}&act=UserCP&CODE=show_image&rc={$regid}&p=3' border='0' alt='Code Bit'>
						  &nbsp;<img src='{$ibforums->base_url}&act=UserCP&CODE=show_image&rc={$regid}&p=4' border='0' alt='Code Bit'>
						  &nbsp;<img src='{$ibforums->base_url}&act=UserCP&CODE=show_image&rc={$regid}&p=5' border='0' alt='Code Bit'>
						  &nbsp;<img src='{$ibforums->base_url}&act=UserCP&CODE=show_image&rc={$regid}&p=6' border='0' alt='Code Bit'>
						 </td>
						 </tr>
						 <tr>
						 <td class='row2' width='40%'>{$ibforums->lang['ras_text']}</td>
						 <td class='row2'><input type='text' size='32' maxlength='32' name='reg_code' class='forminput'></td>
						 </tr>
						 </table>
					 </td>
				 </tr>
                 
                 <tr>
                     <td class='row2' align='center' colspan='2'><input type="submit" name='change_email' value="{$ibforums->lang['account_email_submit']}" class='forminput'></td>
                 </tr>
                 </form>
EOF;
    }

    public function pass_change()
    {
        global $ibforums;

        return <<<EOF
				<form action="{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}" method="post" name='form1'>
                 <input type='hidden' name='act' value='UserCP'>
                 <input type='hidden' name='CODE' value='29'>
                 <input type='hidden' name='s' value='{$ibforums->session_id}'>

                   <td colspan='2' class='category'><b>{$ibforums->lang['account_pass_title']}</b></td>
                 </tr>
                 <tr>
                   <td class='row1' colspan='2'>{$ibforums->lang['pass_change_text']}</td>
                 </tr>
                 <tr>
                   <td class='row2' nowrap><b>{$ibforums->lang['account_pass_old']}</b></td>
                   <td class='row2' align='left'><input type='password' name='current_pass' value='' class='forminput'></td>
                 </tr>
                 <tr>
                   <td class='row1' nowrap><b>{$ibforums->lang['account_pass_new']}</b></td>
                   <td class='row1' align='left'><input type='password' name='new_pass_1' value='' class='forminput'></td>
                 </tr>
                 <tr>
                   <td class='row1' nowrap><b>{$ibforums->lang['account_pass_new2']}</b></td>
                   <td class='row1' align='left'><input type='password' name='new_pass_2' value='' class='forminput'></td>
                 </tr>
                 <tr>
                    <td class='row2' align='center' colspan='2'><input type="submit" name='s_pass' value="{$ibforums->lang['account_pass_submit']}" class='forminput'></td>
                 </tr>
                 </form>
EOF;
    }

    public function member_title($title)
    {
        global $ibforums;

        return <<<EOF
                <tr>
                <td class='row1' width='40%'>{$ibforums->lang['member_title']}</td>
                <td class='row1'><input type='text' size='40' maxlength='120' name='member_title' value='$title' class='forminput'></td>
                </tr>
EOF;
    }

    public function personal_avatar($data, $formextra = '', $hidden_field = '')
    {
        global $ibforums;

        return <<<EOF
                <script langauge='javascript'>
                <!--
                
                  function checkTheBox() {
                  	
                  	var isUrl = "{$ibforums->vars['avatar_url']}";
                  	
                  	if (isUrl == 1)
                  	{
                  		document.creator.choice[1].checked = true;
                  	}
                  	else
                  	{
                  		document.creator.choice[0].checked = true;
                  	}
                  }
                  
                  function showavatar(theURL) {
                    
                    document.creator.choice[0].checked = true;
                    
                    document.images.show_avatar.src=theURL+document.creator.gallery_list.options[document.creator.gallery_list.selectedIndex].value;
                  }
                  
                  function select_url() {
                  	document.creator.choice[1].checked = true;
                  }
                  
                  function select_upload() {
                  	document.creator.choice[1].checked = true;
                  	document.creator.url_avatar.value = "";
                  }
                  
                  function select_none() {
                  	document.creator.choice[2].checked = true;
                  }
                  
                //-->
                </script>
                
                <form action='{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}' method='post' onload='checkTheBox' $formextra name='creator'>
                <input type='hidden' name='act' value='UserCP'>
                <input type='hidden' name='CODE' value='25'>
                <input type='hidden' name='s' value='{$ibforums->session_id}'>
                $hidden_field
                
                <td align='left' colspan='2' class='category'><b>{$ibforums->lang['av_current']}</b></td>
                </tr>
                <tr>
                <td class='row2' width='40%' valign='middle'>{$ibforums->lang['this_avatar']}</td>
                <td class='row2' width='60%' valign='middle'>{$data[CUR_AV]}</td>
                <tr>
                <td valign='left' colspan='2' class='category'><input type='radio' name='choice' value='gallery' onclick='return true;'><b>{$ibforums->lang['avatar_pre_title']}</b></td>
                </tr>
                <tr>
                <td class='row1' width='40%' valign='top'>{$ibforums->lang['avatar_pre_txt']}</td>
                <td class='row1' valign='top'>{$data[AVATARS]} &nbsp; &nbsp; {$data[SHOW_AVS]}</td>
                </tr>
EOF;
    }

    public function avatar_upload_field($text = '')
    {
        global $ibforums;

        return <<<EOF
				<tr>
                <td class='row1' width='40%' valign='top'><b>{$ibforums->lang['upload_avatar']}</b></td>
                <td class='row1'><input type='file' size='30' name='FILE_UPLOAD' class='forminput' onfocus='select_upload()' onclick='select_upload()'><br>$text</td>
                </tr>
EOF;
    }

    public function personal_avatar_URL($Profile, $avatar, $allowed_ext)
    {
        global $ibforums;

        return <<<EOF
                <tr>
                <td valign='left' colspan='2' class='category'><input type='radio' name='choice' value='url' checked onclick='return true;'><b>{$ibforums->lang['avatar_url_title']}</b></td>
                </tr>
                <tr>
                <td class='row1' width='40%' valign='top'>{$ibforums->lang['avatar']}<br>{$ibforums->lang['avatar_url_ext']}<br><b>$allowed_ext</b></td>
                <td class='row1'><input type='text' size='55' maxlength='80' name='url_avatar' value='$avatar' class='forminput' onfocus='select_url()'></td>
                </tr>
                <!-- IBF.UPLOAD_AVATAR -->
                <tr>
                <td class='row1' width='40%' valign='top'>{$ibforums->lang['avatar_dims']}<br>{$ibforums->lang['maximum']} {$ibforums->lang['width']} = {$ibforums->vars['av_width']} {$ibforums->lang['pixels']}<br>{$ibforums->lang['maximum']} {$ibforums->lang['height']} = {$ibforums->vars['av_height']} {$ibforums->lang['pixels']}</td>
                <td class='row1'>{$ibforums->lang['width']} &nbsp; <input type='text' size='3' maxlength='3' name='Avatar_width' value='{$Profile[AVATAR_WIDTH]}' class='forminput' onfocus='select_url()'>&nbsp; x &nbsp; {$ibforums->lang['height']} &nbsp; <input type='text' size='3' maxlength='3' name='Avatar_height' value='{$Profile[AVATAR_HEIGHT]}' onfocus='select_url()' class='forminput'></td>
                </tr>
EOF;
    }

    public function personal_avatar_end()
    {
        global $ibforums;

        return <<<EOF
                <tr>
                <td valign='left' colspan='2' class='category'><input type='radio' name='choice' value='none' onclick='return true;'><b>{$ibforums->lang['av_tt_one']}</b></td>
                </tr>
                <tr>
                <td class='row1' align='center' colspan='2'>{$ibforums->lang['av_tt_two']}</td>
                </tr> 
                <tr>
                <td class='row2' align='center' colspan='2'><input type="submit" value="{$ibforums->lang['avatar_pre_submit']}" class='forminput'></td>
                </tr>         
                </form>
EOF;
    }

    public function signature($sig, $t_sig)
    {
        global $ibforums;

        return <<<EOF
<script language="javascript1.2">
<!--

var MessageMax  = "{$ibforums->lang['the_max_length']}";
var Override    = "{$ibforums->lang['override']}";

function CheckLength() {
    MessageLength  = document.REPLIER.Post.value.length;
    message  = "";

        if (MessageMax > 0) {
            message = "{$ibforums->lang['js_max_length']} " + MessageMax + " {$ibforums->lang['js_characters']}.";
        } else {
            message = "";
        }
        alert(message + "\\n{$ibforums->lang['js_used']} " + MessageLength + " {$ibforums->lang['js_characters']}.");
}

function ValidateForm() {
    MessageLength  = document.REPLIER.Post.value.length;
    errors = "";

    if (MessageMax !=0) {
        if (MessageLength > MessageMax) {
            errors = "{$ibforums->lang['js_max_length']} " + MessageMax + " {$ibforums->lang['js_characters']}.\\n{$ibforums->lang['js_current']}: " + MessageLength;
        }
    }
    if (errors != "" && Override == "") {
        alert(errors);
        return false;
    } else {
        document.REPLIER.submit.disabled = true;
        return true;
    }
}



// IBC Code stuff
	var text_enter_url      = "{$ibforums->lang['jscode_text_enter_url']}";
	var text_enter_url_name = "{$ibforums->lang['jscode_text_enter_url_name']}";
	var text_enter_image    = "{$ibforums->lang['jscode_text_enter_image']}";
	var text_enter_email    = "{$ibforums->lang['jscode_text_enter_email']}";
	var text_enter_flash    = "{$ibforums->lang['jscode_text_enter_flash']}";
	var text_code           = "{$ibforums->lang['jscode_text_code']}";
	var text_quote          = "{$ibforums->lang['jscode_text_quote']}";
	var error_no_url        = "{$ibforums->lang['jscode_error_no_url']}";
	var error_no_title      = "{$ibforums->lang['jscode_error_no_title']}";
	var error_no_email      = "{$ibforums->lang['jscode_error_no_email']}";
	var error_no_width      = "{$ibforums->lang['jscode_error_no_width']}";
	var error_no_height     = "{$ibforums->lang['jscode_error_no_height']}";
	var prompt_start        = "{$ibforums->lang['js_text_to_format']}";
	
	var help_bold           = "{$ibforums->lang['hb_bold']}";
	var help_italic         = "{$ibforums->lang['hb_italic']}";
	var help_under          = "{$ibforums->lang['hb_under']}";
	var help_font           = "{$ibforums->lang['hb_font']}";
	var help_size           = "{$ibforums->lang['hb_size']}";
	var help_color          = "{$ibforums->lang['hb_color']}";
	var help_close          = "{$ibforums->lang['hb_close']}";
	var help_url            = "{$ibforums->lang['hb_url']}";
	var help_img            = "{$ibforums->lang['hb_img']}";
	var help_email          = "{$ibforums->lang['hb_email']}";
	var help_quote          = "{$ibforums->lang['hb_quote']}";
	var help_list           = "{$ibforums->lang['hb_list']}";
	var help_code           = "{$ibforums->lang['hb_code']}";
	var help_click_close    = "{$ibforums->lang['hb_click_close']}";
	var list_prompt         = "{$ibforums->lang['js_tag_list']}";
//-->
</script>


		<form action="{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}" method="POST" name='REPLIER'>
		<input type='hidden' name='act' value='UserCP'>
		<input type='hidden' name='CODE' value='23'>
		<input type='hidden' name='s' value='{$ibforums->session_id}'>
		<td class='title' colspan='2'><b>{$ibforums->lang['cp_current_sig']}</b></td>
		</tr>
		<tr>
		<td class='row1' align='center' colspan='2'>
			<table cellpadding='2' cellspacing='0' width='75%' align='center'>
				<tr>
					<td class='signature'>$sig</td>
				</tr>
			</table>
		</td>
		</tr>
		<tr>
		<td class='title' colspan='2'><b>{$ibforums->lang['cp_edit_sig']}</b></td>
		</tr>
        <tr> 
          <td class='row1' width='20%' nowrap>
          	<input type='radio' name='bbmode' value='ezmode' onClick='setmode(this.value)'>&nbsp;<b>{$ibforums->lang['bbcode_guided']}</b><br>
          	<input type='radio' name='bbmode' value='normal' onClick='setmode(this.value)' checked>&nbsp;<b>{$ibforums->lang['bbcode_normal']}</b>
          </td>
          <script language='Javascript' src='html/ibfcode.js'></script>
          <td class='row1' width="80%" valign="top">
			<table cellpadding='2' cellspacing='2' width='100%' align='center'>
                		<tr>
                			<td nowrap width='10%'>
							  <input type='button' accesskey='b' value=' B '       onClick='simpletag("B")' class='codebuttons' name='B' style="font-weight:bold" onMouseOver="hstat('bold')">
							  <input type='button' accesskey='i' value=' I '       onClick='simpletag("I")' class='codebuttons' name='I' style="font-style:italic" onMouseOver="hstat('italic')">
							  <input type='button' accesskey='u' value=' U '       onClick='simpletag("U")' class='codebuttons' name='U' style="text-decoration:underline" onMouseOver="hstat('under')">
							  
							  <select name='ffont' class='codebuttons' onchange="alterfont(this.options[this.selectedIndex].value, 'FONT')"  onMouseOver="hstat('font')">
							  <option value='0'>{$ibforums->lang['ct_font']}</option>
							  <option value='Arial' style='font-family:Arial'>{$ibforums->lang['ct_arial']}</option>
							  <option value='Times' style='font-family:Times'>{$ibforums->lang['ct_times']}</option>
							  <option value='Courier' style='font-family:Courier'>{$ibforums->lang['ct_courier']}</option>
							  <option value='Impact' style='font-family:Impact'>{$ibforums->lang['ct_impact']}</option>
							  <option value='Geneva' style='font-family:Geneva'>{$ibforums->lang['ct_geneva']}</option>
							  <option value='Optima' style='font-family:Optima'>Optima</option>
							  </select><select name='fsize' class='codebuttons' onchange="alterfont(this.options[this.selectedIndex].value, 'SIZE')" onMouseOver="hstat('size')">
							  <option value='0'>{$ibforums->lang['ct_size']}</option>
							  <option value='1'>{$ibforums->lang['ct_sml']}</option>
							  <option value='7'>{$ibforums->lang['ct_lrg']}</option>
							  <option value='14'>{$ibforums->lang['ct_lest']}</option>
							  </select><select name='fcolor' class='codebuttons' onchange="alterfont(this.options[this.selectedIndex].value, 'COLOR')" onMouseOver="hstat('color')">
							  <option value='0'>{$ibforums->lang['ct_color']}</option>
							  <option value='blue' style='color:blue'>{$ibforums->lang['ct_blue']}</option>
							  <option value='red' style='color:red'>{$ibforums->lang['ct_red']}</option>
							  <option value='purple' style='color:purple'>{$ibforums->lang['ct_purple']}</option>
							  <option value='orange' style='color:orange'>{$ibforums->lang['ct_orange']}</option>
							  <option value='yellow' style='color:yellow'>{$ibforums->lang['ct_yellow']}</option>
							  <option value='gray' style='color:gray'>{$ibforums->lang['ct_grey']}</option>
							  <option value='green' style='color:green'>{$ibforums->lang['ct_green']}</option>
							  </select>
							  &nbsp; <a href='javascript:closeall();' onMouseOver="hstat('close')">{$ibforums->lang['js_close_all_tags']}</a>
							</td>
						 </tr>
						 <tr>
						    <td align='left'>
							  <input type='button' accesskey='h' value=' http:// ' onClick='tag_url()'            class='codebuttons' name='url' onMouseOver="hstat('url')">
							  <input type='button' accesskey='g' value=' IMG '     onClick='tag_image()'          class='codebuttons' name='img' onMouseOver="hstat('img')">
							  <input type='button' accesskey='e' value='  @  '     onClick='tag_email()'          class='codebuttons' name='email' onMouseOver="hstat('email')">
							  <input type='button' accesskey='q' value=' QUOTE '   onClick='simpletag("QUOTE")'   class='codebuttons' name='QUOTE' onMouseOver="hstat('quote')">
							  <input type='button' accesskey='p' value=' CODE '    onClick='simpletag("CODE")'    class='codebuttons' name='CODE' onMouseOver="hstat('code')">
							  <input type='button' accesskey='l' value=' LIST '     onClick='tag_list()'          class='codebuttons' name="LIST" onMouseOver="hstat('list')">
							  <!--<input type='button' accesskey='l' value=' SQL '     onClick='simpletag("SQL")'     class='codebuttons' name='SQL'>
							  <input type='button' accesskey='t' value=' HTML '    onClick='simpletag("HTML")'    class='codebuttons' name='HTML'>-->
							</td>
						</tr>
						<tr>
						<!-- Help Box -->
						 <td align='left'>
						  <input type='text' name='helpbox' size='50' maxlength='120' style='width:450px;font-size:10px;font-family:verdana,arial;border:0px;font-weight:bold;' readonly class='row1' value="{$ibforums->lang['hb_start']}">
						  <br>
          				  <b>{$ibforums->lang['hb_open_tags']}:</b>&nbsp;<input type='text' name='tagcount' size='3' maxlength='3' style='font-size:10px;font-family:verdana,arial;border:0px;font-weight:bold;' readonly class='row1' value="0">
						 </td>
						</tr>
					</table>
                </td>
                </tr>
                <tr>
                <td class='row1' align='center' colspan='2'><textarea cols='60' rows='12' wrap='soft' name='Post' tabindex='3' class='textinput'>$t_sig</textarea><br>(<a href='javascript:CheckLength()'>{$ibforums->lang['check_length']}</a>)</td>
                </tr>
                <tr>
                <td class='row1' align='center' colspan='2'><input type='submit' value='{$ibforums->lang['cp_submit_sig']}'></td>
                </tr>
                </form>
EOF;
    }

    public function personal_panel($Profile)
    {
        global $ibforums;

        return <<<EOF
<script language="javascript">
<!--

var LocationMax  = "{$ibforums->vars['max_location_length']}";
var InterestMax  = "{$ibforums->vars['max_interest_length']}";

function CheckLength(Type) {
    LocationLength  = document.theForm.Location.value.length;
    InterestLength  = document.theForm.Interests.value.length;
    message  = "";

    if (Type == "location") {
        if (LocationMax !=0) {
            message = "{$ibforums->lang['js_location']}: {$ibforums->lang['js_max']} " + LocationMax + " {$ibforums->lang['js_characters']}.";
        } else {
            message = "";
        }
        alert(message + "  {$ibforums->lang['js_used']} " + LocationLength + " {$ibforums->lang['js_so_far']}.");
    }
    if (Type == "interest") {
        if (InterestMax !=0) {
            message = "{$ibforums->lang['js_interests']}: {$ibforums->lang['js_max']} " + InterestMax + " {$ibforums->lang['js_characters']}.";
        } else {
            message = "";
        }
        alert(message + "  {$ibforums->lang['js_used']} " + InterestLength + " {$ibforums->lang['js_so_far']}.");
    }
    
}

function ValidateProfile() {

    LocationLength  = document.theForm.Location.value.length;
    InterestLength  = document.theForm.Interests.value.length;

    errors = "";

    if (LocationMax !=0) {
        if (LocationLength > LocationMax) {
            errors = "{$ibforums->lang['js_location']}:  {$ibforums->lang['js_max']} " + LocationMax + " {$ibforums->lang['js_characters']}.  {$ibforums->lang['js_used']}: " + LocationLength;
        }
    }
    if (InterestMax !=0) {
        if (InterestLength > InterestMax) {
            errors = errors + "  {$ibforums->lang['js_interests']}:  {$ibforums->lang['js_max']} " + InterestMax + " {$ibforums->lang['js_characters']}.  {$ibforums->lang['js_used']}: " + InterestLength;
        }
    } 
    
    if (errors != "") {
        alert(errors);
        return false;
    } else {
        return true;
    }
}
//-->
</script>
<form action="{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}" method="post" name='theForm' onSubmit='return ValidateProfile()'>
     <input type='hidden' name='act' value='UserCP'>
     <input type='hidden' name='CODE' value='21'>
     <input type='hidden' name='s' value='{$ibforums->session_id}'>
				<!--{REQUIRED.FIELDS}-->
                <td valign='left' colspan='2' class='category'>{$ibforums->lang['profile_title']}</td>
                </tr>
                <!--{MEMBERTITLE}-->
                <!--{BIRTHDAY}-->
                <!-- for v1.1<tr>
                <td class='row1' width='40%'>{$ibforums->lang['photo']}</td>
                <td class='row1'><input type='text' size='40' maxlength='120' name='Photo' value='{$Profile['photo']}' class='forminput'></td>
                </tr>  -->
                <tr>
                <td class='row2' width='40%'>{$ibforums->lang['website']}</td>
                <td class='row2'><input type='text' size='40' maxlength='1200' name='WebSite' value='{$Profile['website']}' class='forminput'></td>
                </tr>  
                <tr>
                <td class='row1' width='40%'>{$ibforums->lang['icq']}</td>
                <td class='row1'><input type='text' size='40' maxlength='20' name='ICQNumber' value='{$Profile['icq_number']}' class='forminput'></td>
                </tr>
                <tr>
                <td class='row2' width='40%'>{$ibforums->lang['aol']}</td>
                <td class='row2'><input type='text' size='40' maxlength='30' name='AOLName' value='{$Profile['aim_name']}' class='forminput'></td>
                </tr>
                <tr>
                <td class='row1' width='40%'>{$ibforums->lang['yahoo']}</td>
                <td class='row1'><input type='text' size='40' maxlength='30' name='YahooName' value='{$Profile['yahoo']}' class='forminput'></td>
                </tr>
                <tr>
                <td class='row2' width='40%'>{$ibforums->lang['msn']}</td>
                <td class='row2'><input type='text' size='40' maxlength='30' name='MSNName' value='{$Profile['msnname']}' class='forminput'></td>
                </tr>
                <tr>
                <td class='row1' width='40%'>{$ibforums->lang['location']}<br>(<a href='javascript:CheckLength("location");'>{$ibforums->lang['check_length']}</a>)</td>
                <td class='row1'><input type='text' size='40' name='Location' value='{$Profile['location']}' class='forminput'></td>
                </tr>
                <tr>
                <td class='row2' width='40%' valign='top'>{$ibforums->lang['interests']}<br>(<a href='javascript:CheckLength("interest");'>{$ibforums->lang['check_length']}</a>)</td>
                <td class='row2'><textarea cols='60' rows='10' wrap='soft' name='Interests' class='forminput'>{$Profile['interests']}</textarea></td>
                </tr>
                <!--{OPTIONAL.FIELDS}-->
                <tr>
                <td class='row2' align='center' colspan='2'>
                <input type="submit" value="{$ibforums->lang['submit_profile']}" class='forminput'>
                </td></tr>
                </form>
EOF;
    }

    public function required_title()
    {
        global $ibforums;

        return <<<EOF
            <td valign='left' colspan='2' class='category'>{$ibforums->lang['cf_required']}</td>
            </tr>
            <tr>
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

    public function Menu_bar($base_url)
    {
        global $ibforums;

        return <<<EOF
<br>
<table width='<{tbl_width}>' bgcolor='<{tbl_border}>' align='center' border="0" cellspacing="1" cellpadding="0">
  <tr> 
    <td class='maintitle' > 
      &nbsp;
    </td>
  </tr>
  <tr> 
    <td> 
      <table class='mainbg' width="100%" border="0" cellspacing="1" cellpadding="4">
        <tr> 
          <td nowrap class='titlemedium'>&nbsp;</td>
          <td width="100%" nowrap class='titlemedium'>&nbsp;</td>
        </tr>
        <tr> 
          <td class='row1' valign="top">
           <table cellpadding='0' cellspacing='0' width='100%' align='left' border='0'>
            <tr>
              <td style='line-height:150%' nowrap>

			  <!-- Messenger -->
			  <b>{$ibforums->lang['m_messenger']}</b><br>
              <img src="{$ibforums->vars['img_url']}/dark_line.gif" alt="" width="155" height="7"><br>
              &#149; <a href='{$base_url}&act=Msg&CODE=01'><b>{$ibforums->lang['mess_inbox']}</b></a><br>
              &#149; <a href='{$base_url}&act=Msg&CODE=04'><b>{$ibforums->lang['mess_new']}</b></a><br>
              &#149; <a href='{$base_url}&act=Msg&CODE=02'>{$ibforums->lang['mess_contact']}</a><br>
              &#149; <a href='{$base_url}&act=Msg&CODE=07'>{$ibforums->lang['mess_folders']}</a><br>
              &#149; <a href='{$base_url}&act=Msg&CODE=14'>{$ibforums->lang['mess_archive']}</a><br>
              &#149; <a href='{$base_url}&act=Msg&CODE=20'>{$ibforums->lang['mess_saved']}</a><br>
              &#149; <a href='{$base_url}&act=Msg&CODE=30'>{$ibforums->lang['mess_tracker']}</a><br>
              <br>
              <!-- End Messenger -->
              
              <!-- Topic Tracker -->
			  
              <b>{$ibforums->lang['m_tracker']}</b><br>
              <img src="{$ibforums->vars['img_url']}/dark_line.gif" alt="" width="155" height="7"><br>
              &#149; <a href='{$base_url}&act=UserCP&CODE=26'>{$ibforums->lang['m_view_subs']}</a><br>
              &#149; <a href='{$base_url}&act=UserCP&CODE=50'>{$ibforums->lang['m_view_forum']}</a><br>
			  <br>
			  
			  <!-- Profile -->
              <b>{$ibforums->lang['m_personal']}</b><br>
              <img src="{$ibforums->vars['img_url']}/dark_line.gif" alt="" width="155" height="7"><br>
              &#149; <a href='{$base_url}&act=UserCP&CODE=01'>{$ibforums->lang['m_contact_info']}</a><br>
              <!-- &#149; <a href='#'>{$ibforums->lang['m_bio_info']}</a><br> -->
              &#149; <a href='{$base_url}&act=UserCP&CODE=22'>{$ibforums->lang['m_sig_info']}</a><br>
              &#149; <a href='{$base_url}&act=UserCP&CODE=24'>{$ibforums->lang['m_avatar_info']}</a><br>
              <!-- &#149; <a href='#'>{$ibforums->lang['m_edit_links']}</a><br> -->

			  <!-- Options -->
              <br>
              <b>{$ibforums->lang['m_options']}</b><br>
              <img src="{$ibforums->vars['img_url']}/dark_line.gif" alt="" width="155" height="7"><br>
              &#149; <a href='{$base_url}&act=UserCP&CODE=02'>{$ibforums->lang['m_email_opt']}</a><br>
              &#149; <a href='{$base_url}&act=UserCP&CODE=04'>{$ibforums->lang['m_board_opt']}</a><br>
              &#149; <a href='{$base_url}&act=UserCP&CODE=06'>{$ibforums->lang['m_skin_lang']}</a><br>
              &#149; <a href='{$base_url}&act=UserCP&CODE=08'>{$ibforums->lang['m_email_change']}</a><br>
              &#149; <a href='{$base_url}&act=UserCP&CODE=28'>{$ibforums->lang['m_passy_opt']}</a><br>
              
			  
             </td>
            </tr>
           </table>
          </td>
          <td valign='top' class='row1'>
          <table cellpadding='4' cellspacing='3' width='100%' align='left' border='0'>
            <tr>
             
			     <!-- Start main CP area -->
EOF;
    }

    public function CP_end()
    {
        global $ibforums;

        return <<<EOF
          <!-- end main CP area -->
        
       </table>
       </td>
      </tr>
      </table>
    </td>
  </tr>
</table>
EOF;
    }

    public function splash($member)
    {
        global $ibforums;

        return <<<EOF
      <td class='titlemedium' width="100%">{$ibforums->lang['stats_header']}</td>
        </tr>
        <tr> 
          <td class='row1' valign="top" width="100%">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
              <tr> 
                <td width="40%">{$ibforums->lang['email_address']}</td>
                <td width="60%">{$member[MEMBER_EMAIL]}</td>
              </tr>
              <tr> 
                <td width="40%">{$ibforums->lang['number_posts']}</td>
                <td width="60%">{$member[MEMBER_POSTS]}</td>
              </tr>
              <tr> 
                <td width="40%">{$ibforums->lang['registered']}</td>
                <td width="60%">{$member[DATE_REGISTERED]}</td>
              </tr>
              <tr> 
                <td width="40%">{$ibforums->lang['daily_average']}</td>
                <td width="60%">{$member[DAILY_AVERAGE]}</td>
              </tr>
            </table>
          </td>
        </tr>
        <!-- MSG -->
        <tr> 
          <td class='titlemedium' width="100%">{$ibforums->lang['messenger_summary']}</td>
        </tr>
        <tr> 
          <td class='row1' valign="top" width="100%">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
              <tr> 
                <td width="40%">{$ibforums->lang['total_messages']}</td>
                <td width="60%">{$member['total_messages']} {$member['full_percent']}</td>
              </tr>
              <tr> 
                <td width="40%">{$ibforums->lang['messages_left']}</td>
                <td width="60%">{$member['space_free']} {$member['full_messenger']}</td>
              </tr>
            </table>
          </td>
        </tr>
       <!-- END MSG -->
        <tr>
          <td class='titlemedium' width="100%">{$ibforums->lang['note_pad']}</td>
        </tr>
        <tr>
          <td class='row1' valign="top" width="100%">
                <form name='notepad' action="{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}" method="post">
				<input type='hidden' name='act' value='UserCP'>
				<input type='hidden' name='s' value='{$ibforums->session_id}'>
				<input type='hidden' name='CODE' value='20'>
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
              <tr> 
				<td align="center">
				<textarea cols='65' rows='{$member['SIZE']}' wrap='soft' name='notes' class='forminput' style='width:100%'>{$member['NOTES']}</textarea>
				</td>
              </tr>
            </table>
            <br>
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
              <tr> 
                <td align="right" width="50%">{$ibforums->lang['ta_size']}&nbsp;</td>
                <td nowrap width="50%"> 
				   <select name='ta_size' class='forminput'>
				   {$member['SIZE_CHOICE']}
				   </select>
                  <input type='submit' value='{$ibforums->lang['submit_notepad']}' class='forminput'>
                </td>
              </tr>
            </table>
            </form>
          </td>
        </tr>
EOF;
    }

    public function settings_header($Profile, $time_select, $time, $dst_check)
    {
        global $ibforums;

        return <<<EOF
     <form action="{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}" method="post">
     <input type='hidden' name='act' value='UserCP'>
     <input type='hidden' name='CODE' value='05'>
     <input type='hidden' name='s' value='{$ibforums->session_id}'>

                  
                   <td colspan='2' class='category'><b>{$ibforums->lang['settings_time']}</b></td>
                 </tr>
                 <tr>
                   <td class='row1' colspan='2'><b>{$ibforums->lang['settings_time_txt']}</b><br><span class='highlight'>$time</span></td>
                 </tr>
                 <tr>
                   <td class='row1' colspan='2' align='left'>$time_select</td>
                 </tr>
                 <tr>
                   <td class='row1' nowrap>{$ibforums->lang['dst_box']}</td>
                   <td class='row1' width='70%' align='left'><input type='checkbox' class='forminput' name='DST' value='1' $dst_check></td>
                 </tr>
EOF;
    }

    public function skin_lang_header($lang_select)
    {
        global $ibforums;

        return <<<EOF
	<script language='Javascript'>
      <!--
		function do_preview() {
			
			var f = document.prefs.u_skin;
			
			var base_url = "{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}&";
			
			if (f.options[f.selectedIndex].value == -1) {
				return false;
			}
			
			window.open( base_url + 'skinid='+f.options[f.selectedIndex].value, 'Preview', 'width=800,height=600,top=0,left=0,resizable=1,scrollbars=1,location=no,directories=no,status=no,menubar=no,toolbar=no');
			
		}
	  -->
    </script>

     <form action="{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}" method="post" name='prefs'>
     <input type='hidden' name='act' value='UserCP'>
     <input type='hidden' name='CODE' value='07'>
     <input type='hidden' name='s' value='{$ibforums->session_id}'>

                   <td colspan='2' class='category'><b>{$ibforums->lang['settings_title']}</b></td>                
                 </tr>
                 <tr>
                   <td class='row1' nowrap><b>{$ibforums->lang['settings_lang_txt']}</b></td>
                   <td class='row1' align='left'>$lang_select</td>
                 </tr>
EOF;
    }

    public function settings_skin($skin)
    {
        global $ibforums;

        return <<<EOF
                 </tr>
                   <td colspan='2' class='category'><b>{$ibforums->lang['settings_skin']}</b></td>
                 </tr>
                 <tr>
                   <td class='row1' nowrap><b>{$ibforums->lang['settings_skin_txt']}</b></td>
                   <td class='row1' align='left'>$skin &nbsp;&nbsp; <input type='button' value='{$ibforums->lang['cp_skin_preview']}' class='forminput' onClick='do_preview()'></td>
                 </tr>
EOF;
    }

    public function skin_lang_end()
    {
        global $ibforums;

        return <<<EOF
                 <tr>
                   <td class='row2' colspan='2' align='center'><input type='submit' name='submit' value='{$ibforums->lang['settings_submit']}' class='forminput'></form></td>
                 </tr>
EOF;
    }

    public function settings_end($data)
    {
        global $ibforums;

        return <<<EOF
                 <tr>
                   <td colspan='2' class='category'><b>{$ibforums->lang['settings_display']}</b></td>
                 </tr>
                 <tr>
                   <td class='row1' nowrap><b>{$ibforums->lang['settings_viewsig']}</b></td>
                   <td class='row1' width='70%' align='left'>{$data[SIG]}</td>
                 </tr>
                 <tr>
                   <td class='row1' nowrap><b>{$ibforums->lang['settings_viewimg']}</b></td>
                   <td class='row1' width='70%' align='left'>{$data[IMG]}</td>
                 </tr>
                 <tr>
                   <td class='row1' nowrap><b>{$ibforums->lang['settings_viewava']}</b></td>
                   <td class='row1' width='70%' align='left'>{$data[AVA]}</td>
                 </tr>
                 <tr>
                   <td class='row1' nowrap><b>{$ibforums->lang['settings_dopopup']}</b></td>
                   <td class='row1' width='70%' align='left'>{$data[POP]}</td>
                 </tr>
                 <tr>
                   <td class='row1' nowrap>{$ibforums->lang['hide_session_txt']}</td>
                   <td class='row1' width='70%' align='left'>{$data[SESS]}</td>
                 </tr>
                 <tr>
                   <td class='row1' nowrap><b>{$ibforums->lang['pp_number_posts']}</b></td>
                   <td class='row1' width='70%' align='left'><select name='postpage' class='forminput'>{$data['PPS']}</select></td>
                 </tr>
                 <tr>
                   <td class='row1' nowrap><b>{$ibforums->lang['pp_number_topics']}</b></td>
                   <td class='row1' width='70%' align='left'><select name='topicpage' class='forminput'>{$data['TPS']}</select></td>
                 </tr>
                 <tr>
                   <td class='row2' colspan='2' align='center'><input type='submit' name='submit' value='{$ibforums->lang['settings_submit']}' class='forminput'></form></td>
                 </tr>
EOF;
    }

    public function email($Profile)
    {
        global $ibforums;

        return <<<EOF
<form action="{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}" method="post">
     <input type='hidden' name='act' value='UserCP'>
     <input type='hidden' name='CODE' value='03'>
     <input type='hidden' name='s' value='{$ibforums->session_id}'>
                   <td valign='left' colspan='2' class='category'><b>{$ibforums->lang['privacy_settings']}</b></td>
                 </tr>
                <tr>
                <td class='row1' align='right' valign='top'><input type='checkbox' name='hide_email' value='1' {$Profile['hide_email']}></td>
                <td class='row1' align='left' width='100%'>{$ibforums->lang['hide_email']}</td>
                </tr>  
                <tr>
                <td class='row1' align='right' valign='top'><input type='checkbox' name='admin_send' value='1' {$Profile['allow_admin_mails']}></td>
                <td class='row1' align='left'  width='100%'>{$ibforums->lang['admin_send']}</td>
                </tr>
                 <tr>
                   <td valign='left' colspan='2' class='category'><b>{$ibforums->lang['board_prefs']}</b></td>
                 </tr>
                <tr>
                <td class='row1' align='right' valign='top'><input type='checkbox' name='send_full_msg' value='1' {$Profile['email_full']}></td>
                <td class='row1' align='left'  width='100%'>{$ibforums->lang['send_full_msg']}</td>
                </tr>
                <tr>
                <td class='row1' align='right' valign='top'><input type='checkbox' name='pm_reminder' value='1' {$Profile['email_pm']}></td>
                <td class='row1' align='left'  width='100%'>{$ibforums->lang['pm_reminder']}</td>
                </tr>
                <tr>
                <td class='row1' align='right' valign='top'><input type='checkbox' name='auto_track' value='1' {$Profile['auto_track']}></td>
                <td class='row1' align='left'  width='100%'>{$ibforums->lang['auto_track']}</td>
                </tr>
                <tr>
                <td class='row2' align='center' colspan='2'>
                <input type="submit" value="{$ibforums->lang['submit_email']}" class='forminput'>
                </td></tr>
                </form>
EOF;
    }

    public function forum_subs_header()
    {
        global $ibforums;

        return <<<EOF

                   <td align='left' colspan='2' class='pagetitle'>{$ibforums->lang['forum_subs_header']}</td>
                 </tr>
                 <tr>
                 <td valign='top' colspan='2' >
                 <table cellpadding='4' cellspacing='1' align='center' width='100%' bgcolor='<{tbl_border}>'>
                 <tr>
                   <td class='titlemedium' align='left' width='5%'>&nbsp;</td>
                   <td class='titlemedium' align='left' width='50%'>{$ibforums->lang['ft_forum']}</td>
                   <td class='titlemedium' align='center' width='5%'>{$ibforums->lang['ft_topics']}</td>
                   <td class='titlemedium' align='center' width='5%'>{$ibforums->lang['ft_posts']}</td>
                   <td class='titlemedium' align='center' width='35%'>{$ibforums->lang['ft_last_post']}</td>
                 </tr>
EOF;
    }

    public function forum_subs_row($data)
    {
        global $ibforums;

        return <<<EOF
                 <tr>
                   <td class='row1' align='center' width='5%'>{$data['folder_icon']}</td>
                   <td class='row1' align='left'>
                       <span class='linkthru'><b><a href='{$ibforums->base_url}&act=SF&f={$data['id']}'>{$data['name']}</a></b>
                       <br><span class='desc'>{$data['description']}</span>
                       <br><br><b>[ <a href='{$ibforums->base_url}&act=UserCP&CODE=51&f={$data['id']}'>{$ibforums->lang['ft_unsub']}</a> ]</b>
                   </td>
                   <td class='row1' align='center'>{$data['topics']}</td>
                   <td class='row1' align='center'>{$data['posts']}</td>
                   <td class='row1' align='left'>{$data['last_post']}<br>{$ibforums->lang['in']} {$data['last_topic']}<br>{$ibforums->lang['by']} {$data['last_poster']}</td>
                 </tr>
EOF;
    }

    public function forum_subs_none()
    {
        global $ibforums;

        return <<<EOF
                 <tr>
                   <td class='row1' align='center' colspan='5'>{$ibforums->lang['forum_subs_none']}</td>
                 </tr>
EOF;
    }

    public function forum_subs_end()
    {
        global $ibforums;

        return <<<EOF
<tr>
 <td align='right' class='titlemedium' valign='middle' colspan='5'><a href='{$ibforums->base_url}&act=UserCP&CODE=51&f=all'>{$ibforums->lang['ft_unsub_all']}</a></td>
</tr>
</table>
</td>
</tr>
EOF;
    }

    public function subs_header()
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
                 
                   <td align='left' nowrap class='pagetitle'>{$ibforums->lang['subs_header']}</td>
                   <td>
					  <form action="{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}" name='mutliact' method="post">
					  <input type='hidden' name='act' value='UserCP'>
					  <input type='hidden' name='CODE' value='27'>
					  <input type='hidden' name='s'    value='{$ibforums->session_id}'>
                   </td>
                 </tr>
                 <tr>
                 <td valign='top' colspan='2' >
                 <table cellpadding='4' cellspacing='1' align='center' width='100%' bgcolor='<{tbl_border}>'>
                 <tr>
                   <td class='titlemedium' align='left' width='5%'>&nbsp;</td>
                   <td class='titlemedium' align='left' width='*'>{$ibforums->lang['subs_topic']}</td>
                   <td class='titlemedium' align='center' width='5%'>{$ibforums->lang['subs_replies']}</td>
                   <td class='titlemedium' align='center' width='5%'>{$ibforums->lang['subs_view']}</td>
                   <td class='titlemedium' align='left' width='20%'>{$ibforums->lang['subs_last_post']}</td>
                   <td align='center' width='5%' class='titlemedium'><input name="allbox" type="checkbox" value="Check All" onClick="CheckAll();"></td>
                 </tr>
EOF;
    }

    public function subs_row($data)
    {
        global $ibforums;

        return <<<EOF
                 <tr>
                   <td class='row1' align='center' width='5%'>{$data['folder_icon']}</td>
                   <td class='row1' align='left'><span class='linkthru'><a href='{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}&act=ST&f={$data['forum_id']}&t={$data['tid']}'>{$data['title']}</a> ( <a href='{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}&act=ST&f={$data['forum_id']}&t={$data['tid']}' target='_blank'>{$ibforums->lang['new_window']}</a> )</span><br><span class='desc'>{$data['description']}{$ibforums->lang['subs_start']} {$data['start_date']}</span></td>
                   <td class='row1' align='center'>{$data['posts']}</td>
                   <td class='row1' align='center'>{$data['views']}</td>
                   <td class='row1' align='left'>{$data['last_post_date']}<br>{$ibforums->lang['subs_by']} {$data['last_poster']}</td>
                   <td class='row2' align='center'><input type='checkbox' name='id-{$data['trid']}' value='yes' class='forminput'></td>
                 </tr>
EOF;
    }

    public function subs_none()
    {
        global $ibforums;

        return <<<EOF
                 <tr>
                   <td class='row1' align='center' colspan='6'>{$ibforums->lang['subs_none']}</td>
                 </tr>
EOF;
    }

    public function subs_forum_row($fid, $fname)
    {
        global $ibforums;

        return <<<EOF
                 <tr>
                   <td colspan='6' class='titlemedium' align='left'><a href='{$ibforums->base_url}&act=SF&f=$fid'>$fname</a></td>
                 </tr>
EOF;
    }

    public function subs_end($text = '', $days = '')
    {
        global $ibforums;

        return <<<EOF
<tr>
 <td align='center' class='titlemedium' valign='middle' colspan='6'><input type='submit' class='forminput' value='{$ibforums->lang['subs_delete']}'>&nbsp;&nbsp;{$ibforums->lang['with_selected']}</td>
</tr>
</form>
</table>
<br>
<table width='<{tbl_width}>' cellpadding='2' cellspacing='0' align='center'>
<tr>
 <td align='right' nowrap><i>$text</i></td>
</tr>
<tr>
 <td align='right' valign='middle' width='100%'>
 	<form action='{$ibforums->base_url}&act=UserCP&CODE=26' method='post'>
 	{$ibforums->lang['show_topics_from']} <select class='forminput' name='datecut'>$days</select>
 	<input type='submit' class='forminput' value='{$ibforums->lang['jmp_go']}'>
 	</form>
 </td>
</tr>
</table>
</td></tr>
EOF;
    }

    public function forum_jump($data, $menu_extra = '')
    {
        global $ibforums;

        return <<<EOF
    <script language='Javascript'>
      <!--
		function jump_page(internal) {
			
			var f = document.jumpForm.themenu;
			
			var base_url = "{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}&";
			
			if (f.options[f.selectedIndex].value == -1) {
				return false;
			}
			
			var l_data = f.options[f.selectedIndex].value;
			
			s_data = l_data.preg_split("|");
			
			if (s_data[0] == 0) {
				window.location = base_url + s_data[1];
			}
			else {
				window.open( s_data[1], 'width=800,height=600,top=0,left=0,resizable=1,scrollbars=1,location=yes,directories=yes,status=yes,menubar=yes,toolbar=yes');
			}
			
		}
	  -->
    </script>
<br>
<table cellpadding='0' cellspacing='0' border='0' width='<{tbl_width}>' align='center'>
	<tr>
		<td align='left'>
		<form name='jumpForm'>
			<select name='themenu' onchange='jump_page("1")' class='forminput'>
    	            <option value='-1'>{$ibforums->lang['qc_header']}</option>
    	            <option value='0|act=Msg&CODE=00'>{$ibforums->lang['qc_messenger']}</option>
    				<option value='0|act=Msg&CODE=01&VID=in'>{$ibforums->lang['qc_inbox']}</option>
    				<option value='0|act=Msg&CODE=01&VID=sent'>{$ibforums->lang['qc_sent']}</option>
    				<option value='0|act=Msg&CODE=04'>{$ibforums->lang['qc_compose']}</option>
    				<option value='0|act=Msg&CODE=07'>{$ibforums->lang['qc_prefs']}</option>
    				<option value='-1'>--------------</option>
    				<option value='0|act=Search&CODE=00'>{$ibforums->lang['qc_search']}</option>
    				<option value=''>{$ibforums->lang['qc_home']}</option>
    				<option value='0|act=Help&CODE=00'>{$ibforums->lang['qc_help']}</option>
					$menu_extra    				
    		</select>
    	</form>
    	</td>
		<td align='right'>{$data}</td>
	</tr>
</table>
<br>
EOF;
    }
}

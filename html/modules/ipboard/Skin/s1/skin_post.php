<?php

class skin_post
{
    public function nameField_unreg($data)
    {
        global $ibforums;

        return <<<EOF
<tr> 
          <td class="postfoot" colspan="2">{$ibforums->lang['unreg_namestuff']}</td>
        </tr>
        <tr> 
          <td class="row1">{$ibforums->lang['guest_name']}</td>
          <td class="row1" width="100%"><input type='text' size='40' maxlength='40' name='UserName' value='$data' onMouseOver="this.focus()" onFocus="this.select()"></td>
        </tr>
EOF;
    }

    public function poll_box($data, $extra = '')
    {
        global $ibforums;

        return <<<EOF
<tr> 
          <td class="postfoot" colspan="2">{$ibforums->lang['tt_poll_settings']}</td>
        </tr>
        <tr> 
          <td class="row1">{$ibforums->lang['poll_question']}</td>
          <td class="row1" width="100%"><input type='text' size='40' maxlength='250' name='pollq' value='' class='textinput'></td>
        </tr>
        <tr>
          <td class='row1' valign='top'>{$ibforums->lang['poll_choices']}<br><br>$extra</td>
          <td class='row1' width="100%" valign="top"><textarea cols='60' rows='12' wrap='soft' name='PollAnswers' class='textinput'>$data</textarea>
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
              <tr> 
                <td><input type='checkbox' size='40' value='1' name='allow_disc' class='forminput'></td>
                <td width="100%">{$ibforums->lang['poll_only']}</td>
              </tr>
            </table></td>
        </tr>
EOF;
    }

    public function pm_postbox_buttons($data)
    {
        global $ibforums;

        return <<<EOF
<script language="javascript1.2">
<!--
	var MessageMax  = "{$ibforums->lang['the_max_length']}";
	var Override    = "{$ibforums->lang['override']}";
	
	
function emo_pop()
{
	
  window.open('index.{$ibforums->vars['php_ext']}?act=legends&CODE=emoticons&s={$ibforums->session_id}','Legends','width=250,height=500,resizable=yes,scrollbars=yes'); 
     
}	
function CheckLength() {
	MessageLength  = document.REPLIER.Post.value.length;
	message  = "";
		if (MessageMax !=0) {
			message = "{$ibforums->lang['js_post']}:{$ibforums->lang['js_max_length']} " + MessageMax + "{$ibforums->lang['js_characters']}.";
		} else {
			message = "";
		}
		alert(message + "     {$ibforums->lang['js_used']} " + MessageLength + "{$ibforums->lang['js_characters']}.");
}
	
	function ValidateForm(isMsg) {
		MessageLength  = document.REPLIER.Post.value.length;
		errors = "";
		
		if (isMsg == 1)
		{
			if (document.REPLIER.msg_title.value.length < 2)
			{
				errors = "{$ibforums->lang['msg_no_title']}";
			}
		}
	
		if (MessageLength < 2) {
			 errors = "{$ibforums->lang['js_no_message']}";
		}
		if (MessageMax !=0) {
			if (MessageLength > MessageMax) {
				errors = "{$ibforums->lang['js_max_length']} " + MessageMax + " {$ibforums->lang['js_characters']}. {$ibforums->lang['js_current']}: " + MessageLength;
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
        <tr> 
          <td class="postfoot" colspan="2">{$ibforums->lang['ib_code_buttons']}</td>
        </tr>
        <tr> 
          <td class='row1'>
          	<input type='radio' name='bbmode' value='ezmode' onClick='setmode(this.value)'>&nbsp;<b>{$ibforums->lang['bbcode_guided']}</b><br>
          	<input type='radio' name='bbmode' value='normal' onClick='setmode(this.value)' checked>&nbsp;<b>{$ibforums->lang['bbcode_normal']}</b>
          </td>
          <script language='Javascript' src='html/ibfcode.js'></script>
          <td class='row1' width="100%" valign="top">
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
						 <td align='left' valign='middle'>
						 {$ibforums->lang['hb_open_tags']}:&nbsp;<input type='text' name='tagcount' size='3' maxlength='3' style='font-size:10px;font-family:verdana,arial;border:0px;font-weight:bold;' readonly class='row1' value="0">
						  &nbsp;<input type='text' name='helpbox' size='50' maxlength='120' style='width:80%;font-size:10px;font-family:verdana,arial;border:0px' readonly class='row1' value="{$ibforums->lang['hb_start']}">
						 </td>
						</tr>
					</table>
          </td>
        </tr>
        <tr> 
          <td class="postfoot" colspan="2">{$ibforums->lang['post']}</td>
        </tr>
        <tr> 
          <td class='row1'>(<a href='javascript:CheckLength()'>{$ibforums->lang['check_length']}</a>)<br><br><!--SMILIE TABLE--><img src="{$ibforums->vars['img_url']}/spacer.gif" alt="" width="180" height="1"></td>
          <td class='row1' width="100%" valign="top"><textarea cols='60' style='width:95%' rows='15' wrap='soft' name='Post' tabindex='3' class='textinput'>$data</textarea></td>
        </tr>
EOF;
    }

    public function postbox_buttons($data)
    {
        global $ibforums;

        return <<<EOF
<script language="javascript1.2">
<!--
	var MessageMax  = "{$ibforums->lang['the_max_length']}";
	var Override    = "{$ibforums->lang['override']}";
	
	
function emo_pop()
{
	
  window.open('index.{$ibforums->vars['php_ext']}?act=legends&CODE=emoticons&s={$ibforums->session_id}','Legends','width=250,height=500,resizable=yes,scrollbars=yes'); 
     
}	
function CheckLength() {
	MessageLength  = document.REPLIER.Post.value.length;
	message  = "";
		if (MessageMax !=0) {
			message = "{$ibforums->lang['js_post']}: {$ibforums->lang['js_max_length']} " + MessageMax + " {$ibforums->lang['js_characters']}.";
		} else {
			message = "";
		}
		alert(message + "      {$ibforums->lang['js_used']} " + MessageLength + " {$ibforums->lang['js_characters']}.");
}
	
	function ValidateForm(isMsg) {
		MessageLength  = document.REPLIER.Post.value.length;
		errors = "";
		
		if (isMsg == 1)
		{
			if (document.REPLIER.msg_title.value.length < 2)
			{
				errors = "{$ibforums->lang['msg_no_title']}";
			}
		}
	
		if (MessageLength < 2) {
			 errors = "{$ibforums->lang['js_no_message']}";
		}
		if (MessageMax !=0) {
			if (MessageLength > MessageMax) {
				errors = "{$ibforums->lang['js_max_length']} " + MessageMax + " {$ibforums->lang['js_characters']}. {$ibforums->lang['js_current']}: " + MessageLength;
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
        <tr> 
          <td class="postfoot" colspan="2">{$ibforums->lang['ib_code_buttons']}</td>
        </tr>
        <tr> 
          <td class='row1'>
          	<input type='radio' name='bbmode' value='ezmode' onClick='setmode(this.value)'>&nbsp;<b>{$ibforums->lang['bbcode_guided']}</b><br>
          	<input type='radio' name='bbmode' value='normal' onClick='setmode(this.value)' checked>&nbsp;<b>{$ibforums->lang['bbcode_normal']}</b>
          </td>
          <script language='Javascript' src='html/ibfcode.js'></script>
          <td class='row1' width="100%" valign="top">
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
						 <td align='left' valign='middle'>
						 {$ibforums->lang['hb_open_tags']}:&nbsp;<input type='text' name='tagcount' size='3' maxlength='3' style='font-size:10px;font-family:verdana,arial;border:0px;font-weight:bold;' readonly class='row1' value="0">
						  &nbsp;<input type='text' name='helpbox' size='50' maxlength='120' style='width:80%;font-size:10px;font-family:verdana,arial;border:0px' readonly class='row1' value="{$ibforums->lang['hb_start']}">
						 </td>
						</tr>
					</table>
          </td>
        </tr>
        <tr> 
          <td class="postfoot" colspan="2">{$ibforums->lang['post']}</td>
        </tr>
        <tr> 
          <td class='row1'>(<a href='javascript:CheckLength()'>{$ibforums->lang['check_length']}</a>)<br><br><!--SMILIE TABLE--><img src="{$ibforums->vars['img_url']}/spacer.gif" alt="" width="180" height="1"></td>
          <td class='row1' width="100%" valign="top"><textarea cols='60' style='width:95%' rows='15' wrap='soft' name='Post' tabindex='3' class='textinput'>$data</textarea><table width="100%" border="0" cellspacing="0" cellpadding="0">
              <tr> 
                <td><input type='checkbox' name='enableemo' value='yes' checked></td>
                <td width="100%">{$ibforums->lang['enable_emo']}</td>
              </tr>
              <!--IBF.SIG_CLICK-->
              <tr> 
                <td><input type='checkbox' name='enablesig' value='yes' checked></td>
                <td width="100%">{$ibforums->lang['enable_sig']}</td>
              </tr>
              <!--IBF.END_SIG_CLICK-->
            </table></td>
        </tr>
EOF;
    }

    public function nameField_reg()
    {
        global $ibforums;

        return <<<EOF
<!-- REG NAME -->
EOF;
    }

    public function mod_options($jump)
    {
        global $ibforums;

        return <<<EOF
<tr> 
          <td class="postfoot" colspan="2">{$ibforums->lang['tt_options']}</td>
        </tr>
        <tr> 
          <td class="row1">{$ibforums->lang['mod_options']}</td>
          <td class="row1" width="100%">$jump</td>
        </tr>
EOF;
    }

    public function quote_box($data)
    {
        global $ibforums;

        return <<<EOF
<tr> 
          <td class="postfoot" colspan="2">{$ibforums->lang['post_to_quote']}</td>
        </tr>
        <tr> 
          <td class='row1' valign="top">{$ibforums->lang['post_to_quote_txt']}</td>
          <td class='row1' width="100%" valign="top">
                <textarea cols='60' rows='12' wrap='soft' name='QPost' class='textinput'>{$data['post']}</textarea><input type='hidden' name='QAuthor' value='{$data['author_id']}'><input type='hidden' name='QAuthorN' value='{$data['author_name']}'><input type='hidden' name='QDate'   value='{$data['post_date']}'></td>
        </tr>
EOF;
    }

    public function TopicSummary_top()
    {
        global $ibforums;

        return <<<EOF
<a name="top">
    <!-- Cgi-bot TopicSummaryTop -->
    <br>
     <table cellpadding='0' cellspacing='0' border='0' width='<{tbl_width}>' bgcolor='<{tbl_border}>' align='center'>
       <tr>
         <td>
            <table cellpadding='3' cellspacing='1' border='0' width='100%'>
             <tr>
               <td valign='left' colspan='2' class='titlemedium'>{$ibforums->lang['last_posts']}</td>
             </tr>  
        <!-- Cgi-bot End TopicSummaryTop -->
EOF;
    }

    public function preview($data)
    {
        global $ibforums;

        return <<<EOF
<table cellpadding='0' cellspacing='1' border='0' width='<{tbl_width}>' bgcolor='<{tbl_border}>' align='center'>
        <tr>
            <td>
                <table cellpadding='5' cellspacing='1' border='0' width='100%'>
                <tr>
                <td class='row1' valign='top' align='left'><b>{$ibforums->lang['post_preview']}</b><hr noshade size='1' color='<{tbl_border}>'><span class='postcolor'>$data</span></td>
                </tr>
                </table>
            </td>
        </tr>
    </table>
    <br>
EOF;
    }

    public function TopicSummary_body($data)
    {
        global $ibforums;

        return <<<EOF
<tr class='postdetails'>
               <td class='row1' align='left' valign='top' width='20%'><b>{$data['author']}</b></td>
               <td class='row1' align='left' valign='top' width='80%'>{$ibforums->lang['posted_on']} {$data['date']}<hr noshade size='1'><span class='postcolor'>{$data['post']}</span></td>
             </tr>
EOF;
    }

    public function edit_upload_field($data, $file_name = '')
    {
        global $ibforums;

        return <<<EOF
<tr> 
          <td class="postfoot" colspan="2">{$ibforums->lang['upload_title']}</td>
        </tr>
        <tr> 
          <td class='row1'>{$ibforums->lang['upload_text']} $data</td>
          <td class='row1' width="100%">
           <table cellpadding='4' cellspacing='0' width='100%' border='0'>
            <tr>
             <td><input type='radio' name='editupload' value='keep' checked></td>
             <td width='100%'><b>{$ibforums->lang['eu_keep']}</b> ( $file_name )</td>
            </tr>
            <tr>
             <td><input type='radio' name='editupload' value='delete'></td>
             <td width='100%'><b>{$ibforums->lang['eu_delete']}</b></td>
            </tr>
            <tr>
             <td valign='middle'><input type='radio' name='editupload' value='new'></td>
             <td width='100%'><b>{$ibforums->lang['eu_new']}</b><br><input class='textinput' type='file' size='30' name='FILE_UPLOAD' onClick='document.REPLIER.editupload[2].checked=true;'></td>
            </tr>
           </table>
          </td>
        </tr>
EOF;
    }

    public function Upload_field($data)
    {
        global $ibforums;

        return <<<EOF
<tr> 
          <td class="postfoot" colspan="2">{$ibforums->lang['upload_title']}</td>
        </tr>
        <tr> 
          <td class='row1'>{$ibforums->lang['upload_text']} $data</td>
          <td class='row1' width="100%"><input class='textinput' type='file' size='30' name='FILE_UPLOAD'></td>
        </tr>
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

    public function calendar_end_form($data)
    {
        global $ibforums;

        return <<<EOF
<tr> 
          <td class='mainfoot' align="center" colspan="2"><input type="submit" name="submit" value="$data" tabindex='4' class='forminput'></td>
        </tr>
      </table>
    </td>
  </tr>
  </form>
</table>
EOF;
    }

    public function EndForm($data)
    {
        global $ibforums;

        return <<<EOF
<tr> 
          <td class='mainfoot' align="center" colspan="2"><input type="submit" name="submit" value="$data" tabindex='4' class='forminput' accesskey='s'>&nbsp;
                <input type="submit" name="preview" value="{$ibforums->lang['button_preview']}" tabindex='5' class='forminput'></td>
        </tr>
      </table>
    </td>
  </tr>
  </form>
</table>
EOF;
    }

    public function smilie_table()
    {
        global $ibforums;

        return <<<EOF
<table align="center" cellspacing='1' cellpadding='3' border='0' class='row2' style="border-width:1px; border-color:<{tbl_border}>; border-style:solid; width:95%" align='left'>
    <tr>
        <td colspan='{$ibforums->vars['emo_per_row']}' align='center'>{$ibforums->lang['click_smilie']}</td>
    </tr>
    <!--THE SMILIES-->
    <tr>
        <td colspan='{$ibforums->vars['emo_per_row']}' class='row1' align='center'><a href='javascript:emo_pop()'>{$ibforums->lang['all_emoticons']}</a></td>
    </tr>
    </table>
EOF;
    }

    public function TopicSummary_bottom()
    {
        global $ibforums;

        return <<<EOF
<!-- Cgi-bot TopicSummaryBottom -->
        <tr>
           <td valign='left' colspan='2' class='titlemedium'><a href="javascript:PopUp('index.{$ibforums->vars['php_ext']}?act=ST&f={$ibforums->input['f']}&t={$ibforums->input['t']}','TopicSummary',700,450,1,1)">{$ibforums->lang['review_topic']}</a></td>
        </tr>
        </table>
      </td>
     </tr>
    </table>
    <!-- Cgi-bot End TopicSummaryBottom -->
EOF;
    }

    public function PostIcons()
    {
        global $ibforums;

        return <<<EOF
<tr> 
          <td valign="top" class='row1'>{$ibforums->lang['post_icon']}</td>
          <td valign="top" width="100%" class='row1'>
				  <INPUT type="radio" name="iconid" value="1">&nbsp;&nbsp;<IMG SRC="{$ibforums->vars['img_url']}/icon1.gif"  ALIGN='center' alt=''>&nbsp;&nbsp;&nbsp;<INPUT type="radio" name="iconid" value="2" >&nbsp;&nbsp;<IMG SRC="{$ibforums->vars['img_url']}/icon2.gif"  ALIGN='center' alt=''>&nbsp;&nbsp;&nbsp;<INPUT type="radio" name="iconid" value="3" >&nbsp;&nbsp;<IMG SRC="{$ibforums->vars['img_url']}/icon3.gif"  ALIGN='center' alt=''>&nbsp;&nbsp;&nbsp;<INPUT type="radio" name="iconid" value="4" >&nbsp;&nbsp;<IMG SRC="{$ibforums->vars['img_url']}/icon4.gif"  ALIGN='center' alt=''>&nbsp;&nbsp;&nbsp;<INPUT type="radio" name="iconid" value="5" >&nbsp;&nbsp;<IMG SRC="{$ibforums->vars['img_url']}/icon5.gif"  ALIGN='center' alt=''>&nbsp;&nbsp;&nbsp;<INPUT type="radio" name="iconid" value="6" >&nbsp;&nbsp;<IMG SRC="{$ibforums->vars['img_url']}/icon6.gif"  ALIGN='center' alt=''>&nbsp;&nbsp;&nbsp;<INPUT type="radio" name="iconid" value="7" >&nbsp;&nbsp;<IMG SRC="{$ibforums->vars['img_url']}/icon7.gif"  ALIGN='center' alt=''>&nbsp;&nbsp;&nbsp;<br>
				  <INPUT type="radio" name="iconid" value="8">&nbsp;&nbsp;<IMG SRC="{$ibforums->vars['img_url']}/icon8.gif"  ALIGN='center' alt=''>&nbsp;&nbsp;&nbsp;<INPUT type="radio" name="iconid" value="9" >&nbsp;&nbsp;<IMG SRC="{$ibforums->vars['img_url']}/icon9.gif"  ALIGN='center' alt=''>&nbsp;&nbsp;&nbsp;<INPUT type="radio" name="iconid" value="10" >&nbsp;&nbsp;<IMG SRC="{$ibforums->vars['img_url']}/icon10.gif"  ALIGN='center' alt=''>&nbsp;&nbsp;&nbsp;<INPUT type="radio" name="iconid" value="11" >&nbsp;&nbsp;<IMG SRC="{$ibforums->vars['img_url']}/icon11.gif"  ALIGN='center' alt=''>&nbsp;&nbsp;&nbsp;<INPUT type="radio" name="iconid" value="12" >&nbsp;&nbsp;<IMG SRC="{$ibforums->vars['img_url']}/icon12.gif"  ALIGN='center' alt=''>&nbsp;&nbsp;&nbsp;<INPUT type="radio" name="iconid" value="13" >&nbsp;&nbsp;<IMG SRC="{$ibforums->vars['img_url']}/icon13.gif"  ALIGN='center' alt=''>&nbsp;&nbsp;&nbsp;<INPUT type="radio" name="iconid" value="14" >&nbsp;&nbsp;<IMG SRC="{$ibforums->vars['img_url']}/icon14.gif"  ALIGN='center' alt=''>&nbsp;&nbsp;&nbsp;
				  <BR>
				  <INPUT type="radio" name="iconid" value="0" CHECKED>&nbsp;&nbsp;[ Use None ]
				 </td>
        </tr>
EOF;
    }

    public function table_top($data)
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
<table width="<{tbl_width}>" align='center' border="0" cellspacing="1" cellpadding="0" bgcolor="<{tbl_border}>">
  <tr> 
    <td class='maintitle'> 
      &nbsp;
    </td>
  </tr>
  <tr> 
    <td class='mainbg'> 
      <table width="100%" border="0" cellspacing="1" cellpadding="4">
        <tr> 
          <td class='titlemedium' colspan="2">$data</td>
        </tr>
EOF;
    }

    public function calendar_start_form()
    {
        global $ibforums;

        return <<<EOF
<form action='{$ibforums->base_url}&act=calendar&code=addnewevent' method='POST' name='REPLIER' onSubmit='return ValidateForm()'>
EOF;
    }

    public function calendar_start_edit_form($eventid)
    {
        global $ibforums;

        return <<<EOF
<form action='{$ibforums->base_url}&act=calendar&code=doedit&eventid=$eventid' method='POST' name='REPLIER' onSubmit='return ValidateForm()'>
EOF;
    }

    public function calendar_event_title($data = '')
    {
        global $ibforums;

        return <<<EOF
<tr> 
          <td class="row1" width='20%'>{$ibforums->lang['calendar_title']}</td>
          <td class="row1" width="100%"><input type='text' size='40' maxlength='40' name='event_title' value='$data'></td>
        </tr>
EOF;
    }

    public function calendar_delete_box()
    {
        global $ibforums;

        return <<<EOF
<tr> 
          <td class="row1" width="100%" colspan='2' style='height:40px;border:1px solid black'><input type='checkbox' name='event_delete' value='1'>&nbsp;{$ibforums->lang['calendar_delete_box']}</td>
        </tr>
EOF;
    }

    public function calendar_choose_date($days, $months, $years)
    {
        global $ibforums;

        return <<<EOF
<tr> 
          <td class="row1" nowrap>{$ibforums->lang['calendar_event_date']}</td>
          <td class="row1" width="100%"><select name='e_day' class='forminput'>$days</select>&nbsp;&nbsp;<select name='e_month' class='forminput'>$months</select>&nbsp;&nbsp;<select name='e_year' class='forminput'>$years</select></td>
        </tr>
EOF;
    }

    public function calendar_event_type($pub_select = '', $priv_select = '')
    {
        global $ibforums;

        return <<<EOF
<tr> 
          <td class="row1" nowrap>{$ibforums->lang['calendar_event_type']}</td>
          <td class="row1" width="100%"><select name='e_type' class='forminput'><option value='public'$pub_select>{$ibforums->lang['calendar_type_public']}</option><option value='private'$priv_select>{$ibforums->lang['calendar_type_private']}</option></select></td>
        </tr>
EOF;
    }

    public function calendar_admin_group_box($groups)
    {
        global $ibforums;

        return <<<EOF
<tr> 
          <td class="row1">{$ibforums->lang['calendar_group_filter']}</td>
          <td class="row1" width="100%"><select name='e_groups[]' class='forminput' size='5' multiple>$groups</select></td>
        </tr>
EOF;
    }

    public function table_structure()
    {
        global $ibforums;

        return <<<EOF
<!--START TABLE-->
<!--NAME FIELDS-->
<!--TOPIC TITLE-->
<!--POLL BOX-->
<!--POST BOX-->
<!--QUOTE BOX-->
<!--POST ICONS-->
<!--UPLOAD FIELD-->
<!--MOD OPTIONS-->
<!--END TABLE-->
EOF;
    }

    public function add_edit_box($checked = '')
    {
        global $ibforums;

        return <<<EOF
<tr> 
          <td class="row1" width='20%'><b>{$ibforums->lang['edit_ops']}</b></td>
          <td class="row1" width="100%" valign='middle'><input type='checkbox' name='add_edit' value='1' $checked class='forminput'>&nbsp;{$ibforums->lang['append_edit']}</td>
        </tr>
EOF;
    }

    public function topictitle_fields($data)
    {
        global $ibforums;

        return <<<EOF
<tr> 
          <td class="postfoot" colspan="2">{$ibforums->lang['tt_topic_settings']}</td>
        </tr>
        <tr> 
          <td class='row1'>{$ibforums->lang['topic_title']}</td>
          <td class='row1' width="100%" valign="top"><input type='text' size='40' maxlength='50' name='TopicTitle' value='{$data[TITLE]}' tabindex='1' class='forminput'></td>
        </tr>
        <tr> 
          <td class='row1'>{$ibforums->lang['topic_desc']}</td>
          <td class='row1' width="100%" valign="top"><input type='text' size='40' maxlength='40' name='TopicDesc' value='{$data[DESC]}' tabindex='2' class='forminput'></td>
        </tr>
EOF;
    }
}

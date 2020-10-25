<?php

class skin_mod
{
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

    public function move_form($jhtml, $forum_name)
    {
        global $ibforums;

        return <<<EOF


                <tr>
                <td class='row1'>{$ibforums->lang[move_from]} <b>$forum_name</b> {$ibforums->lang[to]}:</td>
                <td class='row1'><select name='move_id' class='forminput'>$jhtml</select></td>
                </tr>
                <tr>
                <td class='row1'><b>{$ibforums->lang['leave_link']}</b></td>
                <td class='row1'>
                  <select name='leave' class='forminput'>
                  <option value='y'  selected>{$ibforums->lang['yes']}</option>
                  <option value='n'>{$ibforums->lang['no']}</option>
                  </select>
                </td>
                </tr>


EOF;
    }

    public function delete_js()
    {
        global $ibforums;

        return <<<EOF

          <script language='JavaScript'>
          <!--
          function ValidateForm() {
             document.REPLIER.submit.disabled = true;
             return true;
          }
          //-->
          </script>
          
EOF;
    }

    public function topictitle_fields($title, $desc)
    {
        global $ibforums;

        return <<<EOF


                <tr>
                <td class='row1'><b>{$ibforums->lang[edit_f_title]}</b></td>
                <td class='row1'><input type='text' size='40' maxlength='50' name='TopicTitle' value='$title'></td>
                </tr>
                <tr>
                <td class='row1'><b>{$ibforums->lang[edit_f_desc]}</b></td>
                <td class='row1'><input type='text' size='40' maxlength='40' name='TopicDesc' value='$desc'></td>
                </tr>


EOF;
    }

    public function poll_entry($id, $entry)
    {
        global $ibforums;

        return <<<EOF

				<tr>
				<td class='row1'><b>{$ibforums->lang['pe_option']} $id</b></td>
                <td class='row1'><input type='text' size='60' maxlength='250' name='POLL_$id' value='$entry'></td>
                </tr>
                
EOF;
    }

    public function poll_select_form($poll_question = '')
    {
        global $ibforums;

        return <<<EOF
				<tr>
				<td class='row1'><b>{$ibforums->lang['pe_question']}</b></td>
                <td class='row1'><input type='text' size='60' maxlength='250' name='poll_question' value='$poll_question'></td>
                </tr>
				<tr>
				<td class='row1'><b>{$ibforums->lang['pe_pollonly']}</b></td>
                <td class='row1'><select name='pollonly' class='forminput'><option value='0'>{$ibforums->lang['pe_no']}</option><option value='1'>{$ibforums->lang['pe_yes']}</option></select></td>
                </tr>
                
EOF;
    }

    public function table_top($posting_title)
    {
        global $ibforums;

        return <<<EOF
	<br>
     <table cellpadding='0' cellspacing='0' border='0' width='<{tbl_width}>' bgcolor='<{tbl_border}>' align='center'>
        <tr>
            <td>
                <table cellpadding='5' cellspacing='1' border='0' width='100%'>
                <tr>
                <td valign='left' colspan='2' class='titlemedium'>$posting_title</td>
                </tr>


EOF;
    }

    public function topic_history($data)
    {
        global $ibforums;

        return <<<EOF
	<br>
     <table cellpadding='0' cellspacing='0' border='0' width='<{tbl_width}>' bgcolor='<{tbl_border}>' align='center'>
        <tr>
          <td>
            <table cellpadding='5' cellspacing='1' border='0' width='100%'>
            <tr>
            <td valign='left' colspan='2' class='titlemedium'>{$ibforums->lang['th_title']}</td>
            </tr>
            <tr>
             <td class='row1' width='40%'><b>{$ibforums->lang['th_topic']}</b></td>
             <td class='row1' width='60%'>{$data['th_topic']}</td>
            </tr>
			<tr>
             <td class='row1'><b>{$ibforums->lang['th_desc']}</b></td>
             <td class='row1'>{$data['th_desc']}</td>
            </tr>
            <tr>
             <td class='row1'><b>{$ibforums->lang['th_start_date']}</b></td>
             <td class='row1'>{$data['th_start_date']}</td>
            </tr>
            <tr>
             <td class='row1'><b>{$ibforums->lang['th_start_name']}</b></td>
             <td class='row1'>{$data['th_start_name']}</td>
            </tr>
            <tr>
             <td class='row1'><b>{$ibforums->lang['th_last_date']}</b></td>
             <td class='row1'>{$data['th_last_date']}</td>
            </tr>
            <tr>
             <td class='row1'><b>{$ibforums->lang['th_last_name']}</b></td>
             <td class='row1'>{$data['th_last_name']}</td>
            </tr>
            <tr>
             <td class='row1'><b>{$ibforums->lang['th_avg_post']}</b></td>
             <td class='row1'>{$data['th_avg_post']}</td>
            </tr>
            </table>
           </td>
          </tr>
         </table>
EOF;
    }

    public function mod_log_start()
    {
        global $ibforums;

        return <<<EOF
	<br>
     <table cellpadding='0' cellspacing='0' border='0' width='<{tbl_width}>' bgcolor='<{tbl_border}>' align='center'>
        <tr>
          <td>
            <table cellpadding='5' cellspacing='1' border='0' width='100%'>
            <tr>
            <td valign='left' colspan='3' class='titlemedium'>{$ibforums->lang['ml_title']}</td>
            </tr>
            <tr>
             <td class='category' width='30%'><b>{$ibforums->lang['ml_name']}</b></td>
             <td class='category' width='50%'><b>{$ibforums->lang['ml_desc']}</b></td>
             <td class='category' width='20%'><b>{$ibforums->lang['ml_date']}</b></td>
            </tr>

EOF;
    }

    public function mod_log_none()
    {
        global $ibforums;

        return <<<EOF
            <tr>
             <td class='row1' colspan='3' align='center'><i>{$data['ml_none']}</i></td>
            </tr>

EOF;
    }

    public function mod_log_row($data)
    {
        global $ibforums;

        return <<<EOF
            <tr>
             <td class='row1'>{$data['member']}</td>
             <td class='row1'>{$data['action']}</td>
             <td class='row1'>{$data['date']}</td>
            </tr>

EOF;
    }

    public function mod_log_end()
    {
        global $ibforums;

        return <<<EOF
             </table>
           </td>
          </tr>
         </table>

EOF;
    }

    public function forum_jump($data, $menu_extra = '')
    {
        global $ibforums;

        return <<<EOF

<br>
<table cellpadding='0' cellspacing='0' border='0' width='<{tbl_width}>' align='center'>
	<tr>
		<td align='right'>{$data}</td>
	</tr>
</table>
<br>
EOF;
    }

    public function split_body($jump = '')
    {
        global $ibforums;

        return <<<EOF


                <tr>
                <td class='row1'><b>{$ibforums->lang['mt_new_title']}</b></td>
                <td class='row1'><input type='text' size='40' maxlength='50' name='title' value=''></td>
                </tr>
                <tr>
                <td class='row1'><b>{$ibforums->lang['mt_new_desc']}</b></td>
                <td class='row1'><input type='text' size='40' maxlength='40' name='desc' value=''></td>
                </tr>
                <tr>
                <td class='row1'>{$ibforums->lang['st_forum']}</td>
                <td class='row1'><select name='fid' class='forminput'>$jump</select></td>
                </tr>
                <tr>
                <td class='row1' colspan='2'>
                 <table width='100%' cellpadding='4' cellspacing='1' border='0' style='border:1px solid <{tbl_border}>'>
                  <tr>
                    <td class='titlemedium'>{$ibforums->lang['st_post']}</td>
                  </tr>


EOF;
    }

    public function split_row($row)
    {
        global $ibforums;

        return <<<EOF

				<tr>
				 <td style='border-bottom:1px solid <{tbl_border}>' class='{$row['post_css']}'>{$row['st_top_bit']}
				 <hr noshade size=1 color="<{tbl_border}>">
				 <br>{$row['post']}
				 <br><div align='right'><b>{$ibforums->lang['st_split']}</b>&nbsp;&nbsp;<input type='checkbox' name='post_{$row['pid']}' value='1'></div>
				 </td>
				</tr>


EOF;
    }

    public function split_end_form($action)
    {
        global $ibforums;

        return <<<EOF

				</table>
				</td>
                <tr>
                <td class='row2' align='center' colspan='2'>
                <input type="submit" name="submit" value="$action" class='forminput'>
                </td></tr></table>
                </td></tr></table>
                </form>


EOF;
    }

    public function merge_body($title = '', $desc = '')
    {
        global $ibforums;

        return <<<EOF


                <tr>
                <td class='row1'><b>{$ibforums->lang['mt_new_title']}</b></td>
                <td class='row1'><input type='text' size='40' maxlength='50' name='title' value='$title'></td>
                </tr>
                <tr>
                <td class='row1'><b>{$ibforums->lang['mt_new_desc']}</b></td>
                <td class='row1'><input type='text' size='40' maxlength='40' name='desc' value='$desc'></td>
                </tr>
                <tr>
                <td class='row1'>{$ibforums->lang['mt_tid']}</td>
                <td class='row1'><input type='text' size='80' name='topic_url' value=''></td>
                </tr>


EOF;
    }
} // end class

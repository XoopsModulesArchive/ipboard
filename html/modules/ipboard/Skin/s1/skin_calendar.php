<?php

class skin_calendar
{
    public function cal_edit_button($id)
    {
        global $ibforums;

        return <<<HTML
	<div align='right'><a href='{$ibforums->base_url}&act=calendar&code=edit&eventid=$id'><{P_EDIT}></a></div><br>
HTML;
    }

    public function cal_show_event($event, $member, $event_type, $edit_button = '')
    {
        global $ibforums;

        return <<<HTML

	<table width="<{tbl_width}>" align='center' border="0" cellspacing="1" cellpadding="0" bgcolor="<{tbl_border}>">
  	<tr> 
	 <td colspan='2' class='maintitle'  style='height:30px'>&nbsp;&nbsp;<b>{$ibforums->lang['event_date']} {$event['mday']} {$event['month_text']} {$event['year']}</b>&nbsp;&nbsp;[ <b>{$event['title']}</b> ] ($event_type)</td>
	</tr>
	<tr>
     <td class='mainbg'> 
       <table width="100%" border="0" cellspacing="1" cellpadding="4">
	    <tr>
		 <td valign='top' class='row1' width='25%'><span class='normalname'><a href='{$ibforums->base_url}&act=Profile&MID={$member['id']}'>{$member['name']}</a></span><br><br>{$member['avatar']}<span class='postdetails'><br>{$ibforums->lang['group']} {$member['g_title']}<br>{$ibforums->lang['posts']} {$member['posts']}<br>{$ibforums->lang['joined']} {$member['joined']}</span></td>
		 <td valign='top' height='100%' class='row1' width='75%'>
		 	$edit_button
			 <span class='postcolor'>
			  {$event['event_text']}
			 </span>
			 
		 </td>
	  </tr>
	  <tr>
		<td class='titlefoot' width='100%' align='center' colspan='2'>
		 <table border='0' cellspacing='0' cellpadding='0' width='100%' align='center'>
		  <tr>
		   <td align='center' valign='middle'>
			 &lt;&lt; <a href='{$ibforums->base_url}&act=calendar&d={$event['mday']}&m={$event['month']}&y={$event['year']}'>{$ibforums->lang['back']}</a>
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

HTML;
    }

    public function cal_page_events_start()
    {
        global $ibforums;

        return <<<HTML

	<br>
	<table cellpadding='0' cellspacing='0' border='0' width='<{tbl_width}>' align='center'>
	<tr>
	<td valign='middle' align='left'><span class='pagetitle'>{$ibforums->lang['cal_title_events']}</td>
	</tr>
	</table>

HTML;
    }

    public function cal_birthday_start()
    {
        global $ibforums;

        return <<<HTML
	
	<table width="<{tbl_width}>" align='center' border="0" cellspacing="1" cellpadding="0" bgcolor="<{tbl_border}>">
  	<tr> 
	 <td class='maintitle'  style='height:30px'> 
		 <table width='100%' cellpadding='4' cellspacing='1'>
		  <tr>
		   <td align='left' class='maintitle'><b>{$ibforums->lang['cal_birthdays']}</b></td>
		  </tr>
		 </table>
	 </td>
  	</tr>
  	<tr> 
     <td class='mainbg'> 
       <table width="100%" border="0" cellspacing="1" cellpadding="4">
	    <tr>
	    <br>
		  <ul>
		 
	
HTML;
    }

    public function cal_birthday_entry($uid, $uname, $age = '')
    {
        global $ibforums;

        return <<<HTML
		<li><a href='{$ibforums->base_url}&act=Profile&MID=$uid'>$uname</a> ($age)</li>
HTML;
    }

    public function cal_birthday_end()
    {
        global $ibforums;

        return <<<HTML
		</tr>
		<tr>
		   <td class='titlefoot' width='100%' align='center' colspan='7'>
			<table border='0' cellspacing='0' cellpadding='0' width='100%' align='center'>
			 <tr>
			  <td align='center' valign='middle'>
			  	&lt;&lt; <a href='{$ibforums->base_url}&act=calendar&d={$ibforums->input['d']}&m={$ibforums->input['m']}&y={$ibforums->input['y']}'>{$ibforums->lang['back']}</a>
			  </td>
			 </tr>
			</table>
		  </td>
		 </tr>
	   </table>
	  </td>
	 </tr>
	 </table>
HTML;
    }

    public function cal_main_content($month, $year, $prev, $next)
    {
        global $ibforums;

        return <<<HTML

	<br>
	<form action='{$ibforums->base_url}&act=calendar' method='POST'>
	<table cellpadding='0' cellspacing='0' border='0' width='<{tbl_width}>' align='center'>
	<tr>
	<td valign='middle' align='left'><span class='pagetitle'>$month $year</td>
	</tr>
	</table>
	
	<table width="<{tbl_width}>" align='center' border="0" cellspacing="1" cellpadding="0" bgcolor="<{tbl_border}>">
  	<tr> 
	 <td class='maintitle'  style='height:30px'> 
		 <table width='100%' cellpadding='4' cellspacing='1'>
		  <tr>
		   <td align='left' class='maintitle'>{$ibforums->lang['table_title']}</td>
		   <td align='right' class='maintitle' style='font-weight:bold'>
			  &lt; <a href='{$ibforums->base_url}&act=calendar&m={$prev['month_id']}&y={$prev['year_id']}'>{$prev['month_name']} {$prev['year_id']}</a>
			  &nbsp;&nbsp; <a href='{$ibforums->base_url}&act=calendar&m={$next['month_id']}&y={$next['year_id']}'>{$next['month_name']} {$next['year_id']}</a> &gt;
			  </td>
		  </tr>
		 </table>
	 </td>
  	</tr>
  	<tr> 
     <td class='mainbg'> 
       <table width="100%" border="0" cellspacing="1" cellpadding="4">
	    <tr>
		  <!--IBF.DAYS_TITLE_ROW-->
		
		  <!--IBF.DAYS_CONTENT-->
		 </tr>
		 <tr>
		   <td class='titlefoot' width='100%' align='center' colspan='7'>
			<table border='0' cellspacing='0' cellpadding='0' width='100%' align='center'>
			 <tr>
			  <td align='left' valign='middle'>
			  	<select name='m' class='forminput'><!--IBF.MONTH_BOX--></select>&nbsp;<select name='y' class='forminput'><!--IBF.YEAR_BOX--></select>&nbsp;&nbsp;<input type='submit' value='{$ibforums->lang['form_submit_show']}' class='forminput'>
			  </td>
			  <td align='center' valign='middle'><a href='{$ibforums->base_url}&act=calendar&code=newevent'>{$ibforums->lang['post_new_event']}</a></td>
			  <td align='right' style='font-weight:bold'>
			  &lt; <a href='{$ibforums->base_url}&act=calendar&m={$prev['month_id']}&y={$prev['year_id']}'>{$prev['month_name']} {$prev['year_id']}</a>
			  &nbsp;&nbsp; <a href='{$ibforums->base_url}&act=calendar&m={$next['month_id']}&y={$next['year_id']}'>{$next['month_name']} {$next['year_id']}</a> &gt;
			  </td>
			 </tr>
			</table>
		  </td>
		 </tr>
	   </table>
	  </td>
	 </tr>
	 </table>
	 </form>
	 
		
	
HTML;
    }

    public function cal_day_bit($day)
    {
        global $ibforums;

        return <<<HTML

	<td width='14%' class='titlemedium'><b>$day</b></td>
HTML;
    }

    public function cal_new_row()
    {
        global $ibforums;

        return <<<HTML

	</tr>
	<!-- NEW ROW-->
	<tr>
	
HTML;
    }

    public function cal_blank_cell()
    {
        global $ibforums;

        return <<<HTML

	<td style='height:80px' class='row2'><br></td>
	
HTML;
    }

    public function cal_date_cell($month_day, $events = '')
    {
        global $ibforums;

        return <<<HTML

	<td style='height:80px' valign='top' class='row1' align='left'>$month_day $events</td>
	
HTML;
    }

    public function cal_date_cell_today($month_day, $events = '')
    {
        global $ibforums;

        return <<<HTML

	<td style='height:80px;border:2px;border-style:outset' valign='top' class='row1' align='left'><span class='highlight'><b>$month_day</b></span>$events</td>
	
HTML;
    }

    public function cal_events_start()
    {
        global $ibforums;

        return <<<HTML
	<!--<ul>-->
	<br>
HTML;
    }

    public function cal_events_wrap($link, $text)
    {
        global $ibforums;

        return <<<HTML
	<li type='square'><a href='{$ibforums->base_url}&act=calendar&$link'>$text</a>
HTML;
    }

    public function cal_events_end()
    {
        global $ibforums;

        return <<<HTML
	<!--</ul>-->
HTML;
    }
}

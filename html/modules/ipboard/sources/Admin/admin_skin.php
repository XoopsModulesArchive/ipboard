<?php

/*
+--------------------------------------------------------------------------
|   Invision Power Board v1.1
|   ========================================
|   by Matthew Mecham
|   (c) 2001,2002 Invision Power Services
|   http://www.ibforums.com
|   ========================================
|   Web: http://www.ibforums.com
|   Email: phpboards@ibforums.com
|   Licence Info: phpib-licence@ibforums.com
+---------------------------------------------------------------------------
|
|   > Admin HTML stuff library
|   > Script written by Matt Mecham
|   > Date started: 1st march 2002
|
+--------------------------------------------------------------------------
*/

class admin_skin
{
    public $base_url;

    public $img_url;

    public $has_title;

    public $td_widths = [];

    public $td_header = [];

    public $td_colspan;

    public function __construct()
    {
        global $INFO, $IN;

        $this->base_url = $INFO['board_url'] . '/admin.' . $INFO['php_ext'] . '?adsess=' . $IN['AD_SESS'];

        $this->img_url = $INFO['html_url'] . '/sys-img';
    }

    //+--------------------------------------------------------------------

    //+--------------------------------------------------------------------

    // Javascript elements

    //+--------------------------------------------------------------------

    //+--------------------------------------------------------------------

    public function js_help_link($help = '')
    {
        return "( <a href='#' onClick=\"window.open('{$this->base_url}&act=quickhelp&id=$help','Help','width=250,height=400,resizable=yes,scrollbars=yes');\">Quick Help</a> )";
    }

    public function js_template_tools()
    {
        return "
				<script language='javascript'>
				<!--
					
					var baseUrl = \"{$this->base_url}\";
					
					function restore(suid, expand)
					{
						 if (confirm(\"Are you sure you want to restore the template?\\nALL UNSAVED CHANGES WILL BE LOST!\"))
						 {
          					self.location.href= baseUrl + '&act=templ&code=edit_bit&suid=' + suid + '&expand=' + expand;
       					 }
       					 else
       					 {
          					alert (\"Restore Cancelled\");
      					 }
      				}
      				
      				function edit_box_size(cols, rows)
      				{
      					if (cols == '') { cols = 80; }
      					if (rows == '') { rows = 40; }
      					
      					userCols = prompt(\"Enter the number of columns for the text area (width)\", cols);
						if ( (userCols != null) && (userCols != \"\") )
						{
							userRows = prompt(\"Enter the number of rows for the text area (height)\", rows);
							if ( (userRows != null) && (userRows != \"\") )
							{
								// Rows and cols set, save cookie, present alert.
								
								document.cookie = 'ad_tempform='+userRows+'-'+userCols+'; path=/; expires=Wed, 1 Jan 2020 00:00:00 GMT;';
								alert('Edit box preferences updated.\\nThe changes will take effect next time the edit screen is loaded');
							}
							else
							{
								alert('You must enter a value for the number of rows');
							}
						}
						else
						{
							alert('You must enter a value for the number of columns');
						}
					}
					
					function pop_win(theUrl, winName, theWidth, theHeight)
					{
						 	if (winName == '') { winName = 'Preview'; }
						 	if (theHeight == '') { theHeight = 400; }
						 	if (theWidth == '') { theWidth = 400; }
						 	
						 	window.open('{$this->base_url}&act=rtempl&'+theUrl,winName,'width='+theWidth+',height='+theHeight+',resizable=yes,scrollbars=yes');
					}
					
				//-->
				</script>
				";
    }

    public function js_checkdelete()
    {
        return "
				<script language='javascript'>
				<!--
				function checkdelete(theURL) {
				
					final_url = \"{$this->base_url}&\" + theURL;
					
					if ( confirm('Are you sure you wish to remove this?\\nIt cannot be undone!') )
					{
						document.location.href=final_url;
					}
					else
					{
						alert('Ok, remove cancelled!');
					}
				}
				//-->
				</script>
				";
    }

    public function js_no_specialchars()
    {
        return "
				<script language='javascript'>
				<!--
				function no_specialchars(type) {
				
			      var name;
				
				  if (type == 'sets')
				  {
				  	var field = document.theAdminForm.sname;
				  	name = 'Skin Set Title';
				  }
				  
				  if (type == 'wrapper')
				  {
				  	var field = document.theAdminForm.name;
				  	name = 'Wrapper Title';
				  }
				  
				  if (type == 'csssheet')
				  {
				  	var field = document.theAdminForm.name;
				  	name = 'StyleSheet Title';
				  }
				  
				  if (type == 'templates')
				  {
				  	var field = document.theAdminForm.skname;
				  	name = 'Template Set Name';
				  }
				  
				  if (type == 'images')
				  {
				  	var field = document.theAdminForm.setname;
				  	name = 'Image & Macro Set Title';
				  }
				
				  var valid = 'abcdefghijklmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890.()[]:;~+-_';
				  var ok = 1;
				  var temp;
				  
				  for (var i=0; i < field.value.length; i++) {
				      temp = \"\" + field.value.substring(i,i+1);
				      if (valid.indexOf(temp) == \"-1\")
				      {
				      	ok = 0;
				      }
				  }
				  if (ok == 0)
				  {
				  	alert('Invalid entry for: ' + name + ', you can only use alphanumerics and the following special characters.\\n. ( ) : ; ~ + - _');
				  	return false;
				  } else {
				  	return true;
				  }
				}
				//-->
				</script>
				";
    }

    //+--------------------------------------------------------------------

    //+--------------------------------------------------------------------

    // FORM ELEMENTS

    //+--------------------------------------------------------------------

    //+--------------------------------------------------------------------

    public function start_form($hiddens = '', $name = 'theAdminForm', $js = '')
    {
        global $IN, $INFO;

        $form = "<form action='{$this->base_url}' method='post' name='$name' $js>
				 <input type='hidden' name='adsess' value='{$IN['AD_SESS']}'>";

        if (is_array($hiddens)) {
            foreach ($hiddens as $k => $v) {
                $form .= "\n<input type='hidden' name='{$v[0]}' value='{$v[1]}'>";
            }
        }

        return $form;
    }

    //+--------------------------------------------------------------------

    public function form_hidden($hiddens = '')
    {
        if (is_array($hiddens)) {
            foreach ($hiddens as $k => $v) {
                $form .= "\n<input type='hidden' name='{$v[0]}' value='{$v[1]}'>";
            }
        }

        return $form;
    }

    //+--------------------------------------------------------------------

    public function end_form($text = '', $js = '')
    {
        // If we have text, we print another row of TD elements with a submit button

        $html = '';

        $colspan = '';

        if ('' != $text) {
            if ($this->td_colspan > 0) {
                $colspan = " colspan='" . $this->td_colspan . "' ";
            }

            $html .= "<tr><td align='center' id='tdrow2'" . $colspan . "><input type='submit' value='$text'" . $js . " id='button' accesskey='s'></td></tr>\n";
        }

        $html .= '</form>';

        return $html;
    }

    //+--------------------------------------------------------------------

    public function form_upload($name = 'FILE_UPLOAD', $js = '')
    {
        if ('' != $js) {
            $js = ' ' . $js . ' ';
        }

        return "<input class='textinput' type='file' $js size='30' name='$name'>";
    }

    //+--------------------------------------------------------------------

    public function form_input($name, $value = '', $type = 'text', $js = '')
    {
        if ('' != $js) {
            $js = ' ' . $js . ' ';
        }

        return "<input type='$type' name='$name' value='$value' style='width:95%'" . $js . " id='textinput'>";
    }

    public function form_simple_input($name, $value = '', $size = '5')
    {
        return "<input type='text' name='$name' value='$value' size='$size' id='textinput'>";
    }

    //+--------------------------------------------------------------------

    public function form_textarea($name, $value = '', $cols = '60', $rows = '5', $wrap = 'soft')
    {
        return "<textarea name='$name' cols='$cols' rows='$rows' wrap='$wrap' id='multitext'>$value</textarea>";
    }

    //+--------------------------------------------------------------------

    public function form_dropdown($name, $list = [], $default_val = '', $js = '')
    {
        if ('' != $js) {
            $js = ' ' . $js . ' ';
        }

        $html = "<select name='$name'" . $js . " id='dropdown'>\n";

        foreach ($list as $k => $v) {
            $selected = '';

            if (('' != $default_val) and ($v[0] == $default_val)) {
                $selected = ' selected';
            }

            $html .= "<option value='" . $v[0] . "'" . $selected . '>' . $v[1] . "</option>\n";
        }

        $html .= "</select>\n\n";

        return $html;
    }

    //+--------------------------------------------------------------------

    public function form_yes_no($name, $default_val = '')
    {
        $yes = "Yes &nbsp; <input type='radio' name='$name' value='1' id='green'>";

        $no = "<input type='radio' name='$name' value='0' id='red'> &nbsp; No";

        if (1 == $default_val) {
            $yes = "Yes &nbsp; <input type='radio' name='$name' value='1' checked id='green'>";
        } else {
            $no = "<input type='radio' name='$name' value='0' checked id='red'> &nbsp; No";
        }

        return $yes . '&nbsp;&nbsp;&nbsp;' . $no;
    }

    //+--------------------------------------------------------------------

    public function build_group_perms($read = '*', $write = '*', $reply = '*', $upload = '*')
    {
        global $DB;

        $html = "
		
				<script language='Javascript1.1'>
				<!--
				
				function check_all(str_part) {
				
					var f = document.theAdminForm;
				
					for (var i = 0 ; i < f.elements.length; i++)
					{
						var e = f.elements[i];
						
						if ( (e.name != 'UPLOAD_ALL') && (e.name != 'READ_ALL') && (e.name != 'REPLY_ALL') && (e.name != 'START_ALL') && (e.type == 'checkbox') && (! e.disabled) )
						{
							s = e.name;
							a = s.substring(0, 4);
							
							if (a == str_part)
							{
								e.checked = true;
							}
						}
					}
				}
				
				function obj_checked(IDnumber) {
				
					var f = document.theAdminForm;
					
					str_part = '';
					
					if (IDnumber == 1) { str_part = 'READ' }
					if (IDnumber == 2) { str_part = 'REPL' }
					if (IDnumber == 3) { str_part = 'STAR' }
					if (IDnumber == 4) { str_part = 'UPLO' }
					
					totalboxes = 0;
					total_on   = 0;
					
					for (var i = 0 ; i < f.elements.length; i++)
					{
						var e = f.elements[i];
						
						if ( (e.name != 'UPLOAD_ALL') && (e.name != 'READ_ALL') && (e.name != 'REPLY_ALL') && (e.name != 'START_ALL') && (e.type == 'checkbox') )
						{
							s = e.name;
							a = s.substring(0, 4);
							
							if (a == str_part)
							{
								totalboxes++;
								
								if (e.checked)
								{
									total_on++;
								}
							}
						}
					}
					
					if (totalboxes == total_on)
					{
						if (IDnumber == 1) { f.READ_ALL.checked  = true; }
						if (IDnumber == 2) { f.REPLY_ALL.checked = true; }
						if (IDnumber == 3) { f.START_ALL.checked = true; }
						if (IDnumber == 4) { f.UPLOAD_ALL.checked = true; }
					}
					else
					{
						if (IDnumber == 1) { f.READ_ALL.checked  = false; }
						if (IDnumber == 2) { f.REPLY_ALL.checked = false; }
						if (IDnumber == 3) { f.START_ALL.checked = false; }
						if (IDnumber == 4) { f.UPLOAD_ALL.checked = false; }
					}
					
				}
				
				//-->
				
				</script>
				
				";

        $html .= "<table cellspacing='0' cellpadding='2' width='100%' border='0' align='center' border='0'>
				  <tr>
				  <td width='20%'><b><i>Member Group</i></b></td>
				  <td width='20%'><b>Read Topics</b></td>
				  <td width='20%'><b>Reply to Topics</b></td>
				  <td width='20%'><b>Start Topics</b></td>
				  <td width='20%'><b>Can Upload</b></td>
				  </tr>
				  <tr>
					 <td align='left'><span style='color:red'>ALL MEMBER GROUPS</span><br>(current and future)</td>\n";

        //+-------------------------------------------------------------------------

        if ('*' == $read) {
            $html .= "<td align='center' id='memgroup'><input type='checkbox' onClick='check_all(\"READ\")' name='READ_ALL' value='1' checked></td>\n";
        } else {
            $html .= "<td align='center' id='memgroup'><input type='checkbox' onClick='check_all(\"READ\")' name='READ_ALL' value='1'></td>\n";
        }

        //+-------------------------------------------------------------------------

        if ('*' == $reply) {
            $html .= "<td align='center' id='mggreen'><input type='checkbox' onClick='check_all(\"REPL\")' name='REPLY_ALL' value='1' checked></td>\n";
        } else {
            $html .= "<td align='center' id='mggreen'><input type='checkbox' onClick='check_all(\"REPL\")' name='REPLY_ALL' value='1'></td>\n";
        }

        //+-------------------------------------------------------------------------

        if ('*' == $write) {
            $html .= "<td align='center' id='mgred'><input type='checkbox' onClick='check_all(\"STAR\")' name='START_ALL' value='1' checked></td>\n";
        } else {
            $html .= "<td align='center' id='mgred'><input type='checkbox' onClick='check_all(\"STAR\")' name='START_ALL' value='1'></td>\n";
        }

        if ('*' == $upload) {
            $html .= "<td align='center' id='mgblue'><input type='checkbox' onClick='check_all(\"UPLO\")' name='UPLOAD_ALL' value='1' checked></td>\n";
        } else {
            $html .= "<td align='center' id='mgblue'><input type='checkbox' onClick='check_all(\"UPLO\")' name='UPLOAD_ALL' value='1'></td>\n";
        }

        //+-------------------------------------------------------------------------

        $html .= "</tr>
				  <tr>
				  <td colspan='5'><br><b><i>OR</i> adjust the member groups individually below</b><br>&nbsp;</td>
				  </tr>
				  <tr>\n";

        $DB->query('SELECT g_id, g_title FROM ibf_groups ORDER BY g_title ASC');

        while (false !== ($data = $DB->fetch_row())) {
            $html .= "<tr>
						<td align='right'><b>{$data['g_title']} &raquo;</b></td>\n";

            if ('*' == $read) {
                $html .= "<td align='center' id='memgroup'>Read&nbsp;<input type='checkbox' name='READ_{$data['g_id']}' value='1' checked onclick=\"obj_checked(1)\"></td>";
            } elseif (preg_match('/(^|,)' . $data['g_id'] . '(,|$)/', $read)) {
                $html .= "<td align='center' id='memgroup'>Read&nbsp;<input type='checkbox' name='READ_{$data['g_id']}' value='1' checked onclick=\"obj_checked(1)\"></td>";
            } else {
                $html .= "<td align='center' id='memgroup'>Read&nbsp;<input type='checkbox' name='READ_{$data['g_id']}' value='1' onclick=\"obj_checked(1)\"></td>";
            }

            //+----------------------------------------------------------------------------------------

            if ('*' == $reply) {
                $html .= "<td align='center' id='mggreen'>Reply&nbsp;<input type='checkbox' name='REPLY_{$data['g_id']}' value='1' checked onclick=\"obj_checked(2)\"></td>";
            } elseif (preg_match('/(?:^|,)' . $data['g_id'] . '(?:,|$)/', $reply)) {
                $html .= "<td align='center' id='mggreen'>Reply&nbsp;<input type='checkbox' name='REPLY_{$data['g_id']}' value='1' onclick=\"obj_checked(2)\" checked></td>";
            } else {
                $html .= "<td align='center' id='mggreen'>Reply&nbsp;<input type='checkbox' name='REPLY_{$data['g_id']}' value='1' onclick=\"obj_checked(2)\"></td>";
            }

            //+----------------------------------------------------------------------------------------

            if ('*' == $write) {
                $html .= "<td align='center' id='mgred'>Start&nbsp;<input type='checkbox' name='START_{$data['g_id']}' value='1' checked onclick=\"obj_checked(3)\"></td>";
            } elseif (preg_match('/(?:^|,)' . $data['g_id'] . '(?:,|$)/', $write)) {
                $html .= "<td align='center' id='mgred'>Start&nbsp;<input type='checkbox' name='START_{$data['g_id']}' value='1' checked onclick=\"obj_checked(3)\"></td>";
            } else {
                $html .= "<td align='center' id='mgred'>Start&nbsp;<input type='checkbox' name='START_{$data['g_id']}' value='1' onclick=\"obj_checked(3)\"></td>";
            }

            //+----------------------------------------------------------------------------------------

            if ('*' == $upload) {
                $html .= "<td align='center' id='mgblue'>Upload&nbsp;<input type='checkbox' name='UPLOAD_{$data['g_id']}' value='1' checked onclick=\"obj_checked(4)\"></td>";
            } elseif (preg_match('/(?:^|,)' . $data['g_id'] . '(?:,|$)/', $upload)) {
                $html .= "<td align='center' id='mgblue'>Upload&nbsp;<input type='checkbox' name='UPLOAD_{$data['g_id']}' value='1' checked onclick=\"obj_checked(4)\"></td>";
            } else {
                $html .= "<td align='center' id='mgblue'>Upload&nbsp;<input type='checkbox' name='UPLOAD_{$data['g_id']}' value='1' onclick=\"obj_checked(4)\"></td>";
            }

            $html .= "</tr><tr><td colspan='5' style='height:3px; border-bottom:1px dashed black'>&nbsp;</td></tr>\n\n";
        }

        $html .= "</table>\n\n";

        return $html;
    }

    //+--------------------------------------------------------------------

    //+--------------------------------------------------------------------

    // SCREEN ELEMENTS

    //+--------------------------------------------------------------------

    //+--------------------------------------------------------------------

    public function add_subtitle($title = '', $id = 'subtitle', $colspan = '')
    {
        if ('' != $colspan) {
            $colspan = " colspan='$colspan' ";
        }

        return "\n<tr><td id='$id'" . $colspan . ">$title</td><tr>\n";
    }

    //+--------------------------------------------------------------------

    public function start_table($title = '', $desc = '')
    {
        if ('' != $desc) {
            $desc = "<br><span id='smalltitle'>&nbsp;&nbsp;&nbsp;&nbsp;$desc</span>";
        }

        if ('' != $title) {
            $this->has_title = 1;

            $html .= "<tr>
						<td id='subtitle'>$title $desc</td>
					  <tr>
					  <td>
					  	<table cellpadding='0' cellspacing='0' width='100%' align='center' border='0' id='tablewrap'>
					 ";
        }

        $html .= "<tr>
				  <td>
				<table width='100%' cellspacing='1' cellpadding='0' align='center' border='0' id='table1'>
				 <tr>
				  <td>
				   <table width='100%' cellspacing='2' cellpadding='3' align='center' border='0'>";

        if (isset($this->td_header[0])) {
            $html .= "<tr>\n";

            foreach ($this->td_header as $td) {
                if ('' != $td[1]) {
                    $width = " width='{$td[1]}' ";
                } else {
                    $width = '';
                }

                $html .= "<td id='tdtop'" . $width . "align='center'>{$td[0]}</td>\n";

                $this->td_colspan++;
            }

            $html .= "</tr>\n";
        }

        return $html;
    }

    //+--------------------------------------------------------------------

    public function add_td_row($array, $css = '')
    {
        if (is_array($array)) {
            $html = "<tr>\n";

            $count = count($array);

            $this->td_colspan = $count;

            for ($i = 0; $i < $count; $i++) {
                $td_col = $i % 2 ? 'tdrow2' : 'tdrow1';

                if ('' != $css) {
                    $td_col = $css;
                }

                if (is_array($array[$i])) {
                    $text = $array[$i][0];

                    $colspan = $array[$i][1];

                    $html .= "<td id='$td_col' colspan='$colspan' id='$css'>" . $text . "</td>\n";
                } else {
                    $html .= "<td id='$td_col'>" . $array[$i] . "</td>\n";
                }
            }

            $html .= "</tr>\n";

            return $html;
        }
    }

    //+--------------------------------------------------------------------

    public function add_td_basic($text = '', $align = 'left', $id = 'tdrow1')
    {
        $html = '';

        $colspan = '';

        if ('' != $text) {
            if ($this->td_colspan > 0) {
                $colspan = " colspan='" . $this->td_colspan . "' ";
            }

            $html .= "<tr><td align='$align' id='$id'" . $colspan . ">$text</td></tr>\n";
        }

        return $html;
    }

    //+--------------------------------------------------------------------

    public function add_td_spacer()
    {
        if ($this->td_colspan > 0) {
            $colspan = " colspan='" . $this->td_colspan . "' ";
        }

        return '<tr><td' . $colspan . "><img src='html/sys-img/blank.gif' height='7' width='1'></td></tr>";
    }

    //+--------------------------------------------------------------------

    public function end_table()
    {
        $this->td_header = [];  // Reset TD headers

        if (1 == $this->has_title) {
            $this->has_title = 0;

            return '</table></td></tr></table></td></tr></table></td></tr>';
        }

        return '</table></td></tr></table></td></tr>';
    }

    //+--------------------------------------------------------------------

    //+--------------------------------------------------------------------

    //+--------------------------------------------------------------------

    public function get_css()
    {
        return "<style type='text/css'>
		          	TABLE, TR, TD     { font-family:Verdana, Arial;font-size: 11px; color:#000000; line-height:120% }
					BODY      { font: 10px Verdana; color:#333333 }
					a:link, a:visited, a:active  { color:#000055 }
					a:hover                      { color:#333377;text-decoration:underline }
					
					
					#normal      { font: 10px Verdana; color:#333333 }
					
					#title  { font-size:10px; font-weight:bold; line-height:150%; color:#FFFFFF; height: 26px; background-image: url({$this->img_url}/tile_back.gif); }
					#title  a:link, #title  a:visited, #title  a:active { text-decoration: underline; color : #FFFFFF; font-size:11px }
					
					#detail { font-family: Arial; font-size:11px; color: #333333 }
					
 					#large { font-family: verdana, arial; font-size:18px; color:#4C77B6; font-weight:bold; letter-spacing:-1px }
 					
					#subtitle { font-family: Arial,Verdana; font-size:16px; color:#FF9900; font-weight:bold }
					#smalltitle { font-family: Arial,Verdana; font-size:11px; color:#FF9900; font-weight:bold }
					
					#table1 {  background-color:#FFFFFF; width:100%; align:center; border:1px solid black }
					
					#tdrow1 { background-color:#EEF2F7 }
					
					#subforum { background-color:#DFE6EF }
					
					#tdrow2 { background-color:#F5F9FD }
					
					#catrow { font-weight:bold; height:24px; line-height:150%; color:#4C77B6; background-image: url({$this->img_url}/tile_sub.gif); }
					#catrow2 { font-size:11px; font-weight:bold; line-height:150%; color:#4C77B6; background-color:#D3DFEF; }
					
					#tablewrap { border-top:2px solid #F5F9FD;border-left:2px solid #F5F9FD;border-bottom:2px solid #BCBCBC; border-right:2px solid #BCBCBC }
					
					#copy { color:#555555; font-size:9px }
					
					#nav { color:#000000; font-size:9px }
					
					#description { color:#000000; font-size:10px }
					
					#tdtop  { font-weight:bold; height:24px; line-height:150%; color:#FFFFFF; background-image: url({$this->img_url}/tile_back.gif); }
					
					#memgroup { border:1px solid #777777 }
					
					#mgred   { border:1px solid #777777; background-color: #f5cdcd }
					#mggreen { border:1px solid #777777; background-color: #caf2d9 }
					#mgblue  { border:1px solid #777777; background-color: #DFE6EF }
					
					#green    { background-color: #caf2d9 }
					#red      { background-color: #f5cdcd }
					
					#button   { background-color: #4C77B6; color: #FFFFFF; font-family:Verdana, Arial; font-size:11px }
					
					#editbutton   { background-color: #DDDDDD; color: #000000; font-family:Verdana, Arial; font-size:9px }
					
					#textinput { background-color: #FFFFFF; color:Ê#000000; font-family:Verdana, Arial; font-size:10px }
					
					#dropdown { background-color: #F5F9FD; color:Ê#000000; font-family:Verdana, Arial; font-size:10px }
					
					#multitext { background-color: #F5F9FD; color:Ê#000000; font-family:Verdana, Arial; font-size:10px }
					
				  </style>";
    }

    public function print_top($title = '', $desc = '')
    {
        $css = $this->get_css();

        return "<html>
		          <head><title>Menu</title>
		          <meta HTTP-EQUIV=\"Pragma\"  CONTENT=\"no-cache\">
				  <meta HTTP-EQUIV=\"Cache-Control\" CONTENT=\"no-cache\">
				  <meta HTTP-EQUIV=\"Expires\" CONTENT=\"Mon, 06 May 1996 04:57:00 GMT\">
		          $css
				  </head>
				 <body marginheight='0' marginwidth='0' leftmargin='0' topmargin='0' bgcolor='#E7E7E7'>
				 <table cellspacing='0' cellpadding='2' border='0' align='center' width='100%' bgcolor='#F5F9FD'>
				 <tr>
				  <td align='center' id='title'>Invision Power Board Administration</td>
				 </tr>
				 </table>
				 <table cellspacing='0' cellpadding='0' width='100%' align='center' border='0' bgcolor='#E7E7E7'>
				 <tr>
				  <td>
				   <table cellspacing='3' cellpadding='2' width='100%' align='center' border='0' bgcolor='#FFFFFF' style='border:1px solid #4C77B6'>
					  <tr>
					   <td valign='top' bgcolor='#FFFFFF'>
					   <table cellspacing='0' cellpadding='2' border='0' align='center' width='100%' height='100%' bgcolor='#FFFFFF'>
						 <tr>
				  	      <td id='large' style='border-bottom:1px solid #4C77B6'>$title</td>
				  	     </tr>
				  	    <!--NAV-->
				  	   <tr>
				  	   <td id='description'><br>$desc<br>&nbsp;</td>
				  	   </tr>";
    }

    public function wrap_nav($links)
    {
        return "
				<!--<tr>
				 <td>-->
				 <tr>
				 <table cellspacing='0' cellpadding='4' border='0' align='center' width='100%'  id='tdrow2'  style='border-bottom:1px solid #4C77B6'>
				 <tr>
				  <td width='1%' align='left' valign='middle' nowrap><img src='html/sys-img/item.gif' border='0' title='ACP Navigation'></td>
				  <td width='99%' align='left' valign='middle' id='nav'>$links</td>
				 </tr>
				 </table>
				 <!--</td>
				</tr>-->
				</tr>
			  ";
    }

    //+--------------------------------------------------------------------

    public function print_foot()
    {
        return "        </td>
				 	  </tr>
				 	 </table>
				  </td>
				  </tr>
				  <tr><td><br>&nbsp;</td></tr>
				  </table>
				  </td>
				 </tr>
				 <tr><td align='center' id='copy'><br>Invision Power Board &copy 2003 <a href='http://www.invisionpower.com' target='_blank'>IPS, Inc.</a><br>&nbsp;</td></tr>
				 </table>
				 </body>
				 </html>";
    }

    //+--------------------------------------------------------------------

    //{ background-color:#C2CFDF; font-weight:bold; font-size:12px; color:#000055 }

    public function menu_top()
    {
        global $INFO;

        return "<html>
		          <head><title>Menu</title>
		          <style type='text/css'>
		          	TABLE, TR, TD     { font-family:Verdana, Arial;font-size: 9px; color:#333333 }
					BODY      { font: 9px Verdana; color:#333333 }
					a:link, a:visited, a:active  { color:#333355 }
					a:hover                      { color:#333355;text-decoration:underline }
					
					#title  { font-size:10px; font-weight:bold; line-height:150%; color:#FFFFFF; height: 24px; background-image: url({$this->img_url}/tile_back.gif); }
					#title  a:link, #title  a:visited, #title  a:active { text-decoration: none; color : #555555 }
					
					#detail { font-family: Arial; font-size:7.5pt; color: #333333; background-color:#EEF2F7 }
					
					#cattitle  { font-size:10px; font-weight:bold; line-height:150%; color:#4C77B6; background-color:#C4DCF7; height: 24px; background-image: url({$this->img_url}/tile_sub.gif); }
					#cattitle  a:link, #cattitle  a:visited, #cattitle  a:active { text-decoration: underline; color : #4C77B6; }
					
				  </style>
				  </head>
				 <body marginheight='0' marginwidth='0' leftmargin='0' topmargin='0' bgcolor='#E7E7E7'>
				 <table cellspacing='3' cellpadding='2' width='100%' align='center' border='0' bgcolor='#E7E7E7'>
				 <tr>
				  <td>
				   <table cellspacing='1' cellpadding='2' width='100%' align='center' border='0' bgcolor='#FFFFFF' style='border:thin solid black'>
					<tr>
					  <td align='center'>
					  	<table cellspacing='0' cellpadding='0' width='100%' align='center' border='0' bgcolor='#FFFFFF'>
					  	 <tr>
					  	   <td align='center' id='title'><img src='{$this->img_url}/ad-logo.jpg' border='0'></td>
						</tr>
						<tr>
					  	   <td align='center' id='cattitle'>Administration Menu</td>
						 </tr>
					    </table>
					  </td>
					 </tr>
					<tr>
					 <td valign='top' bgcolor='#FFFFFF' align='center'>
					  
					  <a href='{$this->base_url}&act=menu&show=all' target='menu'>Expand All</a> | <a href='{$this->base_url}&act=menu&show=none' target='menu'>Reduce All</a>
					  <br>
					  <a href='{$this->base_url}&act=index' target='body'>ACP Home</a> | <a href='{$INFO['board_url']}/index.{$INFO['php_ext']}' target='_blank'>Board Home</a>
					 </td>
					  </tr>
					  <tr>
					   <td valign='top' bgcolor='#FFFFFF'>
					   <table cellspacing='0' cellpadding='2' border='0' align='center' width='100%' height='100%' bgcolor='#FFFFFF'>
						 <tr>
				  	   <td>";
    }

    //+--------------------------------------------------------------------

    public function menu_foot()
    {
        return '        </td>
				 	  </tr>
				 	 </table>
				  </td>
				  </tr>
				  </table>
				  </tr>
				 </td>
				 </table>
				 </body>
				 </html>';
    }

    //+--------------------------------------------------------------------

    public function menu_cat_expanded($name = '', $links = '', $id = '')
    {
        global $IN;

        return "<a name='cat$id'></a>
				<table cellpadding='0' cellspacing='0' width='100%' border='0' align='center' style='border:1px solid #333333'>
				<tr>
				 <td id='cattitle'>
				 	<table cellspacing='1' cellpadding='3' border='0' width='100%'>
				 	 <tr>
				 	  <td align='left' valign='middle' id='cattitle'>&nbsp;&nbsp;<a href='{$this->base_url}&act=menu&show={$IN['show']}&out=$id' target='menu'><img src='{$this->img_url}/minus.gif' border='0' alt='Collapse Category' title='Collapse Category'></a></td>
				 	  <td align='left' valign='middle' id='cattitle'>&nbsp;&nbsp;<a href='{$this->base_url}&act=menu&show={$IN['show']}&out=$id' target='menu'><span style='color:#000000'>$name</span></a></td>
					 </tr>
					</table>
				 </td>
				</tr>
				$links
				</table>
				<br>";
    }

    //+--------------------------------------------------------------------

    public function menu_cat_collapsed($name = '', $id = '', $desc = '')
    {
        global $IN;

        return "<table cellpadding='0 cellspacing='2' width='100%' border='0' align='center' style='border:1px solid #777777'>
				<tr>
				 <td id='cattitle'>
				 	<table cellspacing='0' cellpadding='3' border='0' width='100%'>
				 	 <tr>
				 	  <td align='left' valign='middle' id='cattitle'>&nbsp;&nbsp;<a href='{$this->base_url}&act=menu&show=,{$IN['show']},$id' target='menu'><img src='{$this->img_url}/plus.gif' border='0' alt='Expand Category' title='Expand Category'></a></td>
				 	  <td align='left' valign='middle' id='cattitle'>&nbsp;&nbsp;<a href='{$this->base_url}&act=menu&show=,{$IN['show']},$id#cat$id' target='menu'>$name</a></td>
					 </tr>
					</table>
				 </td>
				 </tr>
				 <tr>
				 <td id='detail'>$desc</td>
				</tr>
				</table>
				<br>";
    }

    //+--------------------------------------------------------------------

    public function menu_cat_link($url = '', $name = '')
    {
        return "<tr><td bgcolor='#EEF2F7' valign='middle' style='height:16px'>&nbsp;&nbsp;<img src='{$this->img_url}/item.gif' border='0' alt='' valign='absmiddle'><a href='{$this->base_url}&$url' target='body' style='text-decoration:none'>$name</a></td></tr>";
    }

    //+--------------------------------------------------------------------

    public function frame_set()
    {
        global $IN, $ibforums;

        $frames = "<html>
		   			 <head><title>Invision Power Board Administration Center</title></head>
					   <frameset cols='185, *' frameborder='no' border='0' framespacing='0'>
					   	<frame name='menu' noresize scrolling='auto' src='{$this->base_url}&act=menu'>
					   	<frame name='body' noresize scrolling='auto' src='{$this->base_url}&act=index'>
					   </frameset>
				   </html>";

        return $frames;
    }
}

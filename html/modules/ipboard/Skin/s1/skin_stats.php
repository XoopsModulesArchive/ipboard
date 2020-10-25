<?php

class skin_stats
{
    public function who_header($fid, $tid, $title)
    {
        global $ibforums;

        return <<<EOF

<script language='javascript'>
<!--
 function bog_off()
 {
 	var tid = '$tid';
 	var fid = '$fid';
 	
 	opener.location= '$ibforums->base_url' + '&act=ST&f=' + fid + '&t=' + tid;
 	self.close();
 }
 //-->
 </script>
<table cellpadding='0' cellspacing='0' border='0' width='100%' bgcolor='<{tbl_border}>' align='center'>
        <tr>
            <td>
              <table cellpadding='5' cellspacing='1' border='0' width='100%'>
                <tr>
                   <td colspan='2' class='titlemedium' align='center' >{$ibforums->lang['who_farted']} $title</td>
                </tr>
                <tr>
                   <td width='70%' align='left' class='category'   valign='middle'>{$ibforums->lang['who_poster']}</td>
                   <td width='30%' align='center' class='category' valign='middle'>{$ibforums->lang['who_posts']}</td>
                </tr>
EOF;
    }

    public function who_row($row)
    {
        global $ibforums;

        return <<<EOF
                <tr>
                   <td align='left' class='row1' valign='middle'>{$row['author_name']}</td>
                   <td align='center' class='row1' valign='middle'>{$row['pcount']}</td>
                </tr>
EOF;
    }

    public function who_name_link($id, $name)
    {
        global $ibforums;

        return <<<EOF
                <a href='{$ibforums->base_url}&act=Profile&MID=$id' target='_blank'>$name</a>
                   
EOF;
    }

    public function who_end()
    {
        global $ibforums;

        return <<<EOF
                <tr>
                   <td colspan='2' align='center' class='titlemedium' valign='middle'><a href='javascript:bog_off();'>{$ibforums->lang['who_go']}</a></td>
                </tr>
              </table>
            </td>
           </tr>
         </table>
EOF;
    }

    public function page_title($title)
    {
        global $ibforums;

        return <<<EOF
    <table cellpadding='0' cellspacing='0' border='0' width='<{tbl_width}>' align='center'>
      <tr>
         <td valign='middle' align='left'><span class='pagetitle'>{$title}</td>
      </tr>
     </table>
EOF;
    }

    public function group_strip($group)
    {
        global $ibforums;

        return <<<EOF
<table cellpadding='0' cellspacing='0' border='0' width='<{tbl_width}>' bgcolor='<{tbl_border}>' align='center'>
        <tr>
            <td>
              <table cellpadding='5' cellspacing='1' border='0' width='100%'>
                <tr>
                   <td colspan='4' class='titlemedium' align='center' >$group</td>
                </tr>
                <tr>
                   <td width='30%' align='left' class='category'   valign='middle'>{$ibforums->lang['leader_name']}</td>
                   <td width='40%' align='center' class='category' valign='middle'>{$ibforums->lang['leader_forums']}</td>
                   <td align='center' width='25%' class='category' valign='middle'>{$ibforums->lang['leader_location']}</td>
                   <td align='center' width='5%' class='category' valign='middle'>&nbsp;</td>
                </tr>
EOF;
    }

    public function leader_row($info, $forums)
    {
        global $ibforums;

        return <<<EOF
                <tr>
                   <td align='left' class='row1' valign='middle'><a href='{$ibforums->base_url}&act=Profile&MID={$info['id']}'>{$info['name']}</a></td>
                   <td align='center' class='row1' valign='middle'>$forums</td>
                   <td align='center' class='row1' valign='middle'>{$info['location']}</td>
                   <td align='center' class='row2' valign='middle'>{$info['msg_icon']}</td>
                </tr>
EOF;
    }

    public function close_strip()
    {
        global $ibforums;

        return <<<EOF
				</table>
			  </td>
			 </tr>
			</table>
		    <br>
EOF;
    }

    public function top_poster_header()
    {
        global $ibforums;

        return <<<EOF
<table cellpadding='0' cellspacing='0' border='0' width='<{tbl_width}>' bgcolor='<{tbl_border}>' align='center'>
        <tr>
            <td>
              <table cellpadding='5' cellspacing='1' border='0' width='100%'>
                <tr>
                   <td width='30%' align='left' class='titlemedium' valign='middle'>{$ibforums->lang['member']}</td>
                   <td width='20%' align='center' class='titlemedium' valign='middle'>{$ibforums->lang['member_joined']}</td>
                   <td align='center' width='15%' class='titlemedium' valign='middle'>{$ibforums->lang['member_posts']}</td>
                   <td align='center' width='15%' class='titlemedium' valign='middle'>{$ibforums->lang['member_today']}</td>
                   <td align='center' width='20%' class='titlemedium' valign='middle'>{$ibforums->lang['member_percent']}</td>
                </tr>
EOF;
    }

    public function top_poster_row($info)
    {
        global $ibforums;

        return <<<EOF
                <tr>
                   <td align='left' class='row1' valign='middle'><a href='{$ibforums->base_url}&act=Profile&MID={$info['id']}'>{$info['name']}</a></td>
                   <td align='center' class='row1' valign='middle'>{$info['joined']}</td>
                   <td align='center' class='row1' valign='middle'>{$info['posts']}</td>
                   <td align='center' class='row2' valign='middle'>{$info['tpost']}</td>
                   <td align='center' class='row2' valign='middle'>{$info['today_pct']}%</td>
                </tr>
EOF;
    }

    public function top_poster_footer($info)
    {
        global $ibforums;

        return <<<EOF
                <tr>
                   <td colspan='5' align='center' class='titlemedium' valign='middle'>{$ibforums->lang['total_today']} $info</td>
                </tr>
              </table>
            </td>
           </tr>
         </table>
EOF;
    }

    public function top_poster_no_info()
    {
        global $ibforums;

        return <<<EOF
                <tr>
                   <td colspan='5' align='center' class='row1' valign='middle'>{$ibforums->lang['no_info']}</td>
                </tr>
EOF;
    }
}

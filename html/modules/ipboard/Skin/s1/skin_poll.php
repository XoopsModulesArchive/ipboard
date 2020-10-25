<?php

class skin_poll
{
    public function edit_link($tid, $fid)
    {
        global $ibforums;

        return <<<EOF
[ <a href="{$ibforums->base_url}&act=Mod&CODE=20&f=$fid&t=$tid">{$ibforums->lang['ba_edit']}</a> ]
EOF;
    }

    public function delete_link($tid, $fid)
    {
        global $ibforums;

        return <<<EOF
[ <a href="{$ibforums->base_url}&act=Mod&CODE=22&f=$fid&t=$tid">{$ibforums->lang['ba_delete']}</a> ]
EOF;
    }

    public function Render_row_form($votes, $id, $answer)
    {
        global $ibforums;

        return <<<EOF
    <tr>
    <td class='row1' colspan='3'><INPUT type="radio" name="poll_vote" value="$id">&nbsp;<b>$answer</b></td>
    </tr>
EOF;
    }

    public function ShowPoll_header($tid, $poll_q, $edit, $delete)
    {
        global $ibforums;

        return <<<EOF
    <table cellpadding='0' cellspacing='0' border='0' width='100%' align='center'>
        <tr>
            <td>
                <table cellpadding='4' cellspacing='1' border='0' width='100%'>
                <tr>
                <td align='right' colspan='3' width='99%' class='titlemedium'><form action="{$ibforums->base_url}&act=Poll&t=$tid" method="post"> $edit &nbsp; $delete</td>
                </tr>
                <tr>
                 <td class='row1'>
                  <table cellpadding='4' cellspacing='1' border='0' align='center'>
                  <tr>
                   <td colspan='3' align='center'><b>$poll_q</b></td>
                  </tr>
EOF;
    }

    public function ShowPoll_Form_header($tid, $poll_q, $edit, $delete)
    {
        global $ibforums;

        return <<<EOF
    <table cellpadding='0' cellspacing='0' border='0' width='100%' align='center'>
        <tr>
            <td>
                <table cellpadding='4' cellspacing='1' border='0' width='100%'>
                <tr>
                <td align='right' colspan='3' width='99%' class='titlemedium'><form action="{$ibforums->base_url}&act=Poll&t=$tid" method="post"> $edit &nbsp; $delete</td>
                </tr>
                <tr>
                 <td class='row1'>
                  <table cellpadding='4' cellspacing='1' border='0' align='center'>
                  <tr>
                   <td colspan='3' align='center'><b>$poll_q</b></td>
                  </tr>
EOF;
    }

    public function ShowPoll_footer($vote_button)
    {
        global $ibforums;

        return <<<EOF

				  </table>
				 </td>
                <tr>
                <td class='titlemedium' align='center' colspan='3'>
                $vote_button</form>
                </td></tr></table>
                </td></tr></table>
              </tr>
              <tr>
               <td class='mainbg'>
                
EOF;
    }

    public function Render_row_results($votes, $id, $answer, $percentage, $width)
    {
        global $ibforums;

        return <<<EOF
    <tr>
    <td class='row1'>$answer</td>
    <td class='row1'> [ <b>$votes</b> ] </td>
    <td class='row1'><img src='{$ibforums->vars['img_url']}/bar_left.gif' border='0' width='4' height='11' align='middle' alt=''><img src='{$ibforums->vars['img_url']}/bar.gif' border='0' width='$width' height='11' align='middle' alt=''><img src='{$ibforums->vars['img_url']}/bar_right.gif' border='0' width='4' height='11' align='middle' alt=''>&nbsp;[$percentage%]</td>
    </tr>
EOF;
    }
}

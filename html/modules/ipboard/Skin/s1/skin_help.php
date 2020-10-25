<?php

class skin_help
{
    public function row($entry)
    {
        global $ibforums;

        return <<<EOF
          <!-- Help Entry ID:{$entry[ID]} -->
          <tr>
            <td class='{$entry['CELL_COLOUR']}' style='height:28px'><a href='{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?act=Help&s={$ibforums->session_id}&CODE=01&HID={$entry['id']}'><b>{$entry['title']}</b></a><br>{$entry['description']}</td>
          </tr>
          <!-- End Help Entry -->
EOF;
    }

    public function display($text)
    {
        global $ibforums;

        return <<<EOF
          <!-- Displaying Help Topic -->
          <tr>
            <td class='row1' colspan='2' class='postcolor'>$text</td>
          </tr>
          <!-- End Display -->
EOF;
    }

    public function end()
    {
        global $ibforums;

        return <<<EOF
          </table>
          </td>
          </tr>
          </table>
EOF;
    }

    public function no_results()
    {
        global $ibforums;

        return <<<EOF
                <tr>
                   <td class='row1' colspan='2'><b>{$ibforums->lang['no_results']}</b></td>
                 </tr>
EOF;
    }

    public function start($one_text, $two_text, $three_text)
    {
        global $ibforums;

        return <<<EOF
     <table cellpadding=4 cellspacing='0' border='0' width='<{tbl_width}>' align='center'>
      <tr><td>$two_text</td></tr>
     </table>

     <table cellpadding=0 cellspacing='1' border='0' width='<{tbl_width}>' bgcolor='<{tbl_border}>' align='center'>
      <tr>
        <td>
          <table cellpadding='4' cellspacing='1' border='0' width='100%'>
          <tr>
          <td  align='left' colspan='2' class='titlemedium' >$one_text</td>
          </tr>
          <tr>
              <td class='row1' colspan='2'>
               <form action="{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?act=Help;s={$ibforums->session_id};CODE=02" method="post">
               <input type='hidden' name='act' value='Help'>
               <input type='hidden' name='CODE' value='02'>
               <input type='hidden' name='s' value='{$ibforums->session_id}'>
               {$ibforums->lang['search_txt']}&nbsp;&nbsp;<input type='text' maxlength='60' size='30' class='forminput' name='search_q'>&nbsp;<input type='submit' value='{$ibforums->lang['submit']}' class='forminput'>
              </form>
             </td>
           </tr>
           </table>
          </td>
         </tr>
      </table>
      <br>
     <table cellpadding=0 cellspacing='1' border='0' width='<{tbl_width}>' bgcolor='<{tbl_border}>' align='center'>
      <tr>
        <td>
          <table cellpadding='4' cellspacing='1' border='0' width='100%'>
           <tr>
             <td colspan='2' class='titlemedium' ><b>$three_text</b></td>
           </tr>
EOF;
    }
}

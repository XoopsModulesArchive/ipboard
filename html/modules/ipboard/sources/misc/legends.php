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
|   > Show all emo's / BB Tags module
|   > Module written by Matt Mecham
|   > Date started: 18th April 2002
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

$idx = new legends();

class legends
{
    public $output = '';

    public $base_url = '';

    public $html = '';

    public function __construct()
    {
        //------------------------------------------------------

        // $is_sub is a boolean operator.

        // If set to 1, we don't show the "topic subscribed" page

        // we simply end the subroutine and let the caller finish

        // up for us.

        //------------------------------------------------------

        global $ibforums, $DB, $std, $print, $skin_universal;

        $ibforums->lang = $std->load_words($ibforums->lang, 'lang_legends', $ibforums->lang_id);

        $this->html = $std->load_template('skin_legends');

        $this->base_url = "{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}";

        //--------------------------------------------

        // What to do?

        //--------------------------------------------

        switch ($ibforums->input['CODE']) {
            case 'emoticons':
                $this->show_emoticons();
                break;
            case 'finduser_one':
                $this->find_user_one();
                break;
            case 'finduser_two':
                $this->find_user_two();
                break;
            default:
                $this->show_emoticons();
                break;
        }

        // If we have any HTML to print, do so...

        $print->pop_up_window($this->page_title, $this->output);
    }

    //--------------------------------------------------------------

    public function find_user_one()
    {
        global $ibforums, $DB, $std;

        // entry=textarea&name=carbon_copy&sep=comma

        $entry = $ibforums->input['entry'] ?? 'textarea';

        $name = $ibforums->input['name'] ?? 'carbon_copy';

        $sep = $ibforums->input['sep'] ?? 'line';

        $this->output .= $this->html->find_user_one($entry, $name, $sep);

        $this->page_title = $ibforums->lang['fu_title'];
    }

    //--------------------------------------------------------------

    public function find_user_two()
    {
        global $ibforums, $DB, $std;

        $entry = $ibforums->input['entry'] ?? 'textarea';

        $name = $ibforums->input['name'] ?? 'carbon_copy';

        $sep = $ibforums->input['sep'] ?? 'line';

        //-----------------------------------------

        // Check for input, etc

        //-----------------------------------------

        $ibforums->input['username'] = mb_strtolower(trim($ibforums->input['username']));

        if ('' == $ibforums->input['username']) {
            $this->find_user_error('fu_no_data');

            return;
        }

        //-----------------------------------------

        // Attempt a match

        //-----------------------------------------

        $DB->query("SELECT uid, uname FROM xbb_members WHERE LOWER(uname) LIKE '" . $ibforums->input['username'] . "%' LIMIT 0,101");

        if (!$DB->get_num_rows()) {
            $this->find_user_error('fu_no_match');

            return;
        } elseif ($DB->get_num_rows() > 99) {
            $this->find_user_error('fu_kc_loads');

            return;
        }

        $select_box = '';

        while (false !== ($row = $DB->fetch_row())) {
            if ($row['uid'] > 0) {
                $select_box .= "<option value='{$row['uname']}'>{$row['uname']}</option>\n";
            }
        }

        $this->output .= $this->html->find_user_final($select_box, $entry, $name, $sep);

        $this->page_title = $ibforums->lang['fu_title'];
    }

    //--------------------------------------------------------------

    public function find_user_error($error)
    {
        global $ibforums, $DB, $std;

        $this->page_title = $ibforums->lang['fu_title'];

        $this->output = $this->html->find_user_error($ibforums->lang[$error]);
    }

    //--------------------------------------------------------------

    public function show_emoticons()
    {
        global $ibforums, $DB, $std;

        $this->page_title = $ibforums->lang['emo_title'];

        $this->output .= $this->html->emoticon_javascript();

        $this->output .= $this->html->page_header($ibforums->lang['emo_title'], $ibforums->lang['emo_type'], $ibforums->lang['emo_img']);

        $DB->query('SELECT code, smile_url FROM xbb_emoticons');

        if ($DB->get_num_rows()) {
            while (false !== ($r = $DB->fetch_row())) {
                if (mb_strstr($r['code'], '&quot;')) {
                    $in_delim = "'";

                    $out_delim = '"';
                } else {
                    $in_delim = '"';

                    $out_delim = "'";
                }

                $this->output .= $this->html->emoticons_row(stripslashes($r['code']), stripslashes($r['smile_url']), $in_delim, $out_delim);
            }
        }

        $this->output .= $this->html->page_footer();
    }
}








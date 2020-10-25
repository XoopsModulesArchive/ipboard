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
|   > Access the help files
|   > Module written by Matt Mecham
|   > Date started: 24th February 2002
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

$idx = new Help();

class Help
{
    public $output = '';

    public $page_title = '';

    public $nav = [];

    public $html = '';

    public function __construct()
    {
        global $ibforums, $DB, $std, $print, $xoopsLogger;

        if ('' == $ibforums->input['CODE']) {
            $ibforums->input['CODE'] = '00';
        }

        //--------------------------------------------

        // Require the HTML and language modules

        //--------------------------------------------

        $ibforums->lang = $std->load_words($ibforums->lang, 'lang_help', $ibforums->lang_id);

        $this->html = $std->load_template('skin_help');

        $this->base_url = "{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}";

        //--------------------------------------------

        // What to do?

        //--------------------------------------------

        switch ($ibforums->input['CODE']) {
            case '01':
                $this->show_section();
                break;
            case '02':
                $this->do_search();
                break;
            default:
                $this->show_titles();
                break;
        }

        // If we have any HTML to print, do so...

        require '../../header.php';

        $print->add_output((string)$this->output);

        $print->do_output(['TITLE' => $this->page_title, 'JS' => 0, NAV => $this->nav]);

        require '../../footer.php';
    }

    public function show_titles()
    {
        global $ibforums, $DB, $std;

        $seen = [];

        $this->output = $this->html->start($ibforums->lang['page_title'], $ibforums->lang['help_txt'], $ibforums->lang['choose_file']);

        $DB->query('SELECT id, title, description FROM ibf_faq ORDER BY title ASC');

        $cnt = 0;

        while (false !== ($row = $DB->fetch_row())) {
            if (isset($seen[$row['title']])) {
                continue;
            }

            $seen[$row['title']] = 1;

            $row['CELL_COLOUR'] = $cnt % 2 ? 'row1' : 'row2';

            $cnt++;

            $this->output .= $this->html->row($row);
        }

        $this->output .= $this->html->end();

        $this->page_title = $ibforums->lang['page_title'];

        $this->nav = [$ibforums->lang['page_title']];
    }

    public function show_section()
    {
        global $ibforums, $DB, $std;

        $id = $ibforums->input['HID'];

        if (!preg_match("/^(\d+)$/", $id)) {
            $std->Error([LEVEL => 1, MSG => 'no_help_file']);
        }

        $DB->query("SELECT id, title, text from ibf_faq WHERE ID='$id'");

        $topic = $DB->fetch_row();

        $this->output = $this->html->start($ibforums->lang['help_topic'], $ibforums->lang['topic_text'], $topic['title']);

        $this->output .= $this->html->display($std->text_tidy($topic['text']));

        $this->output .= $this->html->end();

        $this->page_title = $ibforums->lang['help_topic'];

        $this->nav = ["<a href='{$this->base_url}&act=Help'>{$ibforums->lang['help_topics']}</a>", $ibforums->lang['help_topic']];
    }

    public function do_search()
    {
        global $ibforums, $DB, $std;

        if (empty($ibforums->input['search_q'])) {
            $std->Error([LEVEL => 1, MSG => 'no_help_file']);
        }

        $search_string = mb_strtolower(str_replace('*', '%', $ibforums->input['search_q']));

        $search_string = preg_replace("/[<>\!\@£\$\^&\+\=\=\[\]\{\}\(\)\"':;\.,\/]/", '', $search_string);

        $seen = [];

        $this->output = $this->html->start($ibforums->lang['page_title'], $ibforums->lang['results_txt'], $ibforums->lang['search_results']);

        $DB->query("SELECT id, title, description from ibf_faq WHERE LOWER(title) LIKE '%$search_string%' or LOWER(text) LIKE '%$search_string%' ORDER BY title ASC");

        $cnt = 0;

        while (false !== ($row = $DB->fetch_row())) {
            if (isset($seen[$row['title']])) {
                continue;
            }

            $seen[$row['title']] = 1;

            $row['CELL_COLOUR'] = $cnt % 2 ? 'row1' : 'row2';

            $cnt++;

            $this->output .= $this->html->row($row);
        }

        if (0 == $cnt) {
            $this->output .= $this->html->no_results();
        }

        $this->output .= $this->html->end();

        $this->page_title = $ibforums->lang['page_title'];

        $this->nav = ["<a href='{$this->base_url}&act=Help'>{$ibforums->lang['help_topics']}</a>", $ibforums->lang['results_title']];
    }
}

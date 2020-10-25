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
|   > Searching procedures
|   > Module written by Matt Mecham
|   > Date started: 24th February 2002
|
|	> Module Version Number: 1.1.0
+--------------------------------------------------------------------------
|
|   MODULE NOTES:
|   -------------
|
|   I've been deliberating whether we should implement some kind of WORD ID /
|   search logging table for index.
|   iB3 PERL had such a feature, but that was to aide the DBM searches, which
|   were slow at best.
|   The biggest complaint we had was that adding a word ID / search logging table
|   effectively doubled the amount of space needed in the mySQL database. I've seen
|   this complaint echo'd on the support boards of other forum software.
|   The other downside to such a feature would be the overhaul needed to update it
|   with new posts/topics and topic movement / deletion.
|
|   I figure that people post more than they search, so a full table scan once per
|   search request is offset against the CPU to keep the search log tables up to date
|
|   I'm going to stick my neck out and go the full table scan route. It's not as effecient
|   as a search table would be, but the benefits include no overhead updating it, and a smaller
|   overall DB size - which is vital to some people on certain hosts.
|
|   This may change if it becomes apparent that it's not very efficient in the long run
+--------------------------------------------------------------------------
*/

$idx = new Search();

class Search
{
    public $output = '';

    public $page_title = '';

    public $nav = [];

    public $html = '';

    public $first = 0;

    public $search_type = 'posts';

    public $sort_order = 'desc';

    public $sort_key = 'last_post';

    public $search_in = 'posts';

    public $prune = '30';

    public $st_time = [];

    public $end_time = [];

    public $st_stamp = '';

    public $end_stamp = '';

    public $result_type = 'topics';

    public $parser = '';

    public function __construct()
    {
        global $ibforums, $DB, $std, $print;

        if (!$ibforums->vars['allow_search']) {
            $std->Error([LEVEL => 1, MSG => 'search_off']);
        }

        if (1 != $ibforums->member['g_use_search']) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_permission']);
        }

        if ('' == $ibforums->input['CODE']) {
            $ibforums->input['CODE'] = '00';
        }

        //--------------------------------------------

        // Require the HTML and language modules

        //--------------------------------------------

        $ibforums->lang = $std->load_words($ibforums->lang, 'lang_search', $ibforums->lang_id);

        $ibforums->lang = $std->load_words($ibforums->lang, 'lang_forum', $ibforums->lang_id);

        $this->html = $std->load_template('skin_search');

        $this->base_url = "{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}";

        if (isset($ibforums->input['st'])) {
            $this->first = $ibforums->input['st'];
        }

        //--------------------------------------------

        // What to do?

        //--------------------------------------------

        if (!isset($ibforums->member['g_use_search'])) {
            $std->Error([LEVEL => 1, MSG => 'cant_use_feature']);
        }

        switch ($ibforums->input['CODE']) {
            case '01':
                $this->do_search();
                break;
            case 'getnew':
                $this->get_new_posts();
                break;
            case 'getactive':
                $this->get_active();
                break;
            case 'show':
                $this->show_results();
                break;
            case 'getreplied':
                $this->get_replies();
                break;
            case 'lastten':
                $this->get_last_ten();
                break;
            case 'getalluser':
                $this->get_all_user();
                break;
            default:
                $this->show_form();
                break;
        }

        // If we have any HTML to print, do so...

        $print->add_output((string)$this->output);

        $print->do_output(['TITLE' => $this->page_title, 'JS' => 0, NAV => $this->nav]);
    }

    //---------------------------------

    public function get_all_user()
    {
        global $ibforums, $DB, $std, $_POST, $print;

        //------------------------------------

        // Do we have flood control enabled?

        //------------------------------------

        if ($ibforums->member['g_search_flood'] > 0) {
            $flood_time = time() - $ibforums->member['g_search_flood'];

            // Get any old search results..

            $DB->query("SELECT id FROM ibf_search_results WHERE (member_id='" . $ibforums->member['uid'] . "' OR ip_address='" . $ibforums->input['IP_ADDRESS'] . "') AND search_date > '$flood_time'");

            if ($DB->get_num_rows()) {
                $std->Error(['LEVEL' => 1, 'MSG' => 'search_flood', 'EXTRA' => $ibforums->member['g_search_flood']]);
            }
        }

        $ibforums->input['forums'] = 'all';

        $forums = $this->get_searchable_forums();

        $mid = (int)$ibforums->input['mid'];

        //------------------------------------

        // Do we have any forums to search in?

        //------------------------------------

        if ('' == $forums) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_search_forum']);
        }

        if ('' == $mid) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_search_results']);
        }

        //------------------------------------------------

        // Get the topic ID's to serialize and store into

        // the database

        //------------------------------------------------

        $DB->query("SELECT pid FROM ibf_posts WHERE queued <> 1 AND forum_id IN($forums) AND author_id=$mid");

        $max_hits = $DB->get_num_rows();

        $posts = '';

        while (false !== ($row = $DB->fetch_row())) {
            $posts .= $row['pid'] . ',';
        }

        $DB->free_result();

        $posts = preg_replace('/,$/', '', $posts);

        //------------------------------------------------

        // Do we have any results?

        //------------------------------------------------

        if ('' == $posts) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_search_results']);
        }

        //------------------------------------------------

        // If we are still here, store the data into the database...

        //------------------------------------------------

        $unique_id = md5(uniqid(microtime(), 1));

        $str = $DB->compile_db_insert_string(
            [
                'id' => $unique_id,
'search_date' => time(),
'post_id' => $posts,
'post_max' => $max_hits,
'sort_key' => $this->sort_key,
'sort_order' => $this->sort_order,
'member_id' => $ibforums->member['uid'],
'ip_address' => $ibforums->input['IP_ADDRESS'],
            ]
        );

        $DB->query("INSERT INTO ibf_search_results ({$str['FIELD_NAMES']}) VALUES ({$str['FIELD_VALUES']})");

        $print->redirect_screen($ibforums->lang['search_redirect'], "act=Search&nav=lv&CODE=show&searchid=$unique_id&search_in=posts&result_type=posts");

        exit();
    }

    //---------------------------------

    public function get_new_posts()
    {
        global $ibforums, $DB, $std, $_POST, $print;

        //------------------------------------

        // Do we have flood control enabled?

        //------------------------------------

        if ($ibforums->member['g_search_flood'] > 0) {
            $flood_time = time() - $ibforums->member['g_search_flood'];

            // Get any old search results..

            $DB->query("SELECT id FROM ibf_search_results WHERE (member_id='" . $ibforums->member['uid'] . "' OR ip_address='" . $ibforums->input['IP_ADDRESS'] . "') AND search_date > '$flood_time'");

            if ($DB->get_num_rows()) {
                $std->Error(['LEVEL' => 1, 'MSG' => 'search_flood', 'EXTRA' => $ibforums->member['g_search_flood']]);
            }
        }

        $ibforums->input['forums'] = 'all';

        $ibforums->input['nav'] = 'lv';

        $forums = $this->get_searchable_forums();

        //------------------------------------

        // Do we have any forums to search in?

        //------------------------------------

        if ('' == $forums) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_search_forum']);
        }

        //------------------------------------------------

        // Get the topic ID's to serialize and store into

        // the database

        //------------------------------------------------

        $DB->query("SELECT topic_id FROM ibf_posts WHERE queued <> 1 AND forum_id IN($forums) AND post_date > '" . $ibforums->member['last_visit'] . "'");

        $max_hits = $DB->get_num_rows();

        $posts = '';

        while (false !== ($row = $DB->fetch_row())) {
            $posts .= $row['topic_id'] . ',';
        }

        $DB->free_result();

        $posts = preg_replace('/,$/', '', $posts);

        //------------------------------------------------

        // Do we have any results?

        //------------------------------------------------

        if ('' == $posts) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_search_results']);
        }

        //------------------------------------------------

        // If we are still here, store the data into the database...

        //------------------------------------------------

        $unique_id = md5(uniqid(microtime(), 1));

        $str = $DB->compile_db_insert_string(
            [
                'id' => $unique_id,
'search_date' => time(),
'topic_id' => $posts,
'topic_max' => $max_hits,
'sort_key' => $this->sort_key,
'sort_order' => $this->sort_order,
'member_id' => $ibforums->member['uid'],
'ip_address' => $ibforums->input['IP_ADDRESS'],
            ]
        );

        $DB->query("INSERT INTO ibf_search_results ({$str['FIELD_NAMES']}) VALUES ({$str['FIELD_VALUES']})");

        $print->redirect_screen($ibforums->lang['search_redirect'], "act=Search&nav=lv&CODE=show&searchid=$unique_id&search_in=topics&result_type=topics");

        exit();
    }

    //--------------------------------------------------------

    public function get_last_ten()
    {
        global $ibforums, $DB, $std, $_POST, $print;

        $ibforums->input['forums'] = 'all';

        $forums = $this->get_searchable_forums();

        //------------------------------------

        // Do we have any forums to search in?

        //------------------------------------

        if ('' == $forums) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_search_forum']);
        }

        if ($read = $std->my_getcookie('topicsread')) {
            $this->read_array = unserialize(stripslashes($read));
        }

        //------------------------------------------------

        // Get the topic ID's to serialize and store into

        // the database

        //------------------------------------------------

        $DB->query("SELECT p.*, t.*, f.id as forum_id, f.name as forum_name FROM ibf_forums f, ibf_posts p, ibf_topics t WHERE p.queued <> 1 AND p.forum_id IN($forums) AND p.author_id='" . $ibforums->member['uid'] . "' AND t.tid=p.topic_id AND f.id=p.forum_id ORDER BY p.post_date DESC LIMIT 0,10");

        if ($DB->get_num_rows()) {
            require './sources/lib/post_parser.php';

            $this->parser = new post_parser();

            $this->output .= $this->html->start_as_post(['SHOW_PAGES' => $links]);

            while (false !== ($row = $DB->fetch_row())) {
                $row['keywords'] = $url_words;

                $row['post_date'] = $std->get_date($row['post_date'], 'LONG');

                $this->output .= $this->html->RenderPostRow($this->parse_entry($row, 1));
            }

            $this->output .= $this->html->end_as_post(['SHOW_PAGES' => $links]);
        } else {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_search_results']);
        }

        $DB->free_result();

        $this->page_title = $ibforums->lang['nav_lt'];

        $this->nav = [$ibforums->lang['nav_lt']];
    }

    //--------------------------------------------------------

    public function get_replies()
    {
        global $ibforums, $DB, $std, $_POST, $print;

        //------------------------------------

        // Do we have flood control enabled?

        //------------------------------------

        if ($ibforums->member['g_search_flood'] > 0) {
            $flood_time = time() - $ibforums->member['g_search_flood'];

            // Get any old search results..

            $DB->query("SELECT id FROM ibf_search_results WHERE (member_id='" . $ibforums->member['uid'] . "' OR ip_address='" . $ibforums->input['IP_ADDRESS'] . "') AND search_date > '$flood_time'");

            if ($DB->get_num_rows()) {
                $std->Error(['LEVEL' => 1, 'MSG' => 'search_flood', 'EXTRA' => $ibforums->member['g_search_flood']]);
            }
        }

        $ibforums->input['forums'] = 'all';

        $ibforums->input['nav'] = 'lv';

        $forums = $this->get_searchable_forums();

        //------------------------------------

        // Do we have any forums to search in?

        //------------------------------------

        if ('' == $forums) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_search_forum']);
        }

        //------------------------------------------------

        // Get the topic ID's to serialize and store into

        // the database

        //------------------------------------------------

        $DB->query("SELECT tid FROM ibf_topics WHERE starter_id='" . $ibforums->member['uid'] . "' AND last_post > " . $ibforums->member['last_visit'] . " AND forum_id IN($forums) AND approved=1");

        $max_hits = $DB->get_num_rows();

        $topics = '';

        while (false !== ($row = $DB->fetch_row())) {
            $topics .= $row['tid'] . ',';
        }

        $DB->free_result();

        $topics = preg_replace('/,$/', '', $topics);

        //------------------------------------------------

        // Do we have any results?

        //------------------------------------------------

        if ('' == $topics) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_search_results']);
        }

        //------------------------------------------------

        // If we are still here, store the data into the database...

        //------------------------------------------------

        $unique_id = md5(uniqid(microtime(), 1));

        $str = $DB->compile_db_insert_string(
            [
                'id' => $unique_id,
'search_date' => time(),
'topic_id' => $topics,
'topic_max' => $max_hits,
'sort_key' => $this->sort_key,
'sort_order' => $this->sort_order,
'member_id' => $ibforums->member['uid'],
'ip_address' => $ibforums->input['IP_ADDRESS'],
            ]
        );

        $DB->query("INSERT INTO ibf_search_results ({$str['FIELD_NAMES']}) VALUES ({$str['FIELD_VALUES']})");

        $print->redirect_screen($ibforums->lang['search_redirect'], "act=Search&nav=gr&CODE=show&searchid=$unique_id&search_in=posts&result_type=topics");

        exit();
    }

    public function show_form()
    {
        global $DB, $std, $ibforums;

        $last_cat_id = -1;

        $the_hiddens = '';

        $db_cats = [];

        $db_children = [];

        $db_forums = [];

        $DB->query('SELECT f.id AS forum_id, f.name AS forum_name, f.position, f.read_perms, f.parent_id, f.subwrap, c.id AS cat_id, c.name AS cat_name FROM ibf_forums f, ibf_categories c WHERE c.id=f.category ORDER BY c.position, f.position');

        $forums = "<select name='forums' class='forminput' onChange='chooseForum()'>\n" . "<option value='all'>&gt;&gt;All open forums";

        $cats = "<select name='cats' class='forminput' onChange='chooseCat()'>\n" . "<option value='all'>&gt;&gt;All Categories";

        while (false !== ($r = $DB->fetch_row())) {
            if ($last_cat_id != $r['cat_id']) {
                $db_cats[$r['cat_id']] = [
                    'id' => $r['cat_id'],
'name' => $r['cat_name'],
                ];

                $last_cat_id = $r['cat_id'];
            }

            if ($r['parent_id'] > 0) {
                $db_children[$r['parent_id']][$r['forum_id']] = $r;
            } else {
                $db_forums[$r['forum_id']] = $r;
            }
        }

        foreach ($db_cats as $thunder => $cat) {
            // SNARF, SNARF!

            $cats .= "<option value='{$cat['id']}'>{$cat['name']}</option>\n";
        }

        $last_cat_id = -1;

        foreach ($db_forums as $an_eye_for_an => $i) {
            $selected = '';

            $is_sub = '';

            if ($last_cat_id != $i['cat_id']) {
                $forums .= "<option value=\"\">--------------------------</option>\n";

                $last_cat_id = $i['cat_id'];
            }

            if (isset($ibforums->input['f']) and ($ibforums->input['f'] == $i['forum_id'])) {
                $selected = ' selected';
            }

            // Add in sub cat thingy

            if (1 == $i['subwrap']) {
                $is_sub = $ibforums->lang['is_sub'];
            }

            if ('*' == $i['read_perms']) {
                $forums .= "<option value=\"{$i['forum_id']}\"$selected>{$i['forum_name']}$is_sub\n";
            } elseif (preg_match('/(^|,)' . $ibforums->member['mgroup'] . '(,|$)/', $i['read_perms'])) {
                $forums .= "<option value='{$i['forum_id']}'$selected>{$i['forum_name']}$is_sub\n";
            } else {
                continue;
            }

            // Add in sub cat children

            if ((1 == $i['subwrap']) and (count($db_children[$i['forum_id']]) > 0)) {
                foreach ($db_children[$i['forum_id']] as $idx => $cdata) {
                    if ('*' == $cdata['read_perms']) {
                        $forums .= "<option value=\"{$cdata['forum_id']}\"$selected> +---- {$cdata['forum_name']}\n";
                    } elseif (preg_match('/(^|,)' . $ibforums->member['mgroup'] . '(,|$)/', $cdata['read_perms'])) {
                        $forums .= "<option value='{$cdata['forum_id']}'$selected> +---- {$cdata['forum_name']}\n";
                    } else {
                        continue;
                    }
                }
            }
        }

        $forums .= '</select>';

        $cats .= '</select>';

        $this->output = $this->html->Form($forums, $cats);

        $this->page_title = $ibforums->lang['search_title'];

        $this->nav = [$ibforums->lang['search_form']];
    }

    /******************************************************/

    // Searching is split into two queries. One query to

    // regexp the matches, and pull the topic ID's, the other

    // to pull the data from the topic_id. As topic_id's are indexed

    // the second query shouldn't be too CPU intensive.

    // To find the maximum records that match our criteria to

    // generate the page span, we cap all searches off to '200'

    // results. We then use mysql_num_rows to return the actual

    // number of rows matched, loop over and push the info to

    // our $topic var until we have 25 results, which is the max.

    // number per page.

    // The other alternative would be to SELECT COUNT(*) then

    // re-query with a 25 LIMIT cap, this of course would be

    // a heavy drain on the SQL engine.

    /******************************************************/

    public function do_search()
    {
        global $ibforums, $DB, $std, $_POST, $print;

        //------------------------------------

        // Do we have flood control enabled?

        //------------------------------------

        if ($ibforums->member['g_search_flood'] > 0) {
            $flood_time = time() - $ibforums->member['g_search_flood'];

            // Get any old search results..

            $DB->query("SELECT id FROM ibf_search_results WHERE (member_id='" . $ibforums->member['uid'] . "' OR ip_address='" . $ibforums->input['IP_ADDRESS'] . "') AND search_date > '$flood_time'");

            if ($DB->get_num_rows()) {
                $std->Error(['LEVEL' => 1, 'MSG' => 'search_flood', 'EXTRA' => $ibforums->member['g_search_flood']]);
            }
        }

        //------------------------------------

        // Do we have any input?

        //------------------------------------

        if ('' != $ibforums->input['namesearch']) {
            $name_filter = $this->filter_keywords($ibforums->input['namesearch'], 1);
        }

        if ('' != $ibforums->input['useridsearch']) {
            $keywords = $this->filter_keywords($ibforums->input['useridsearch']);

            $this->search_type = 'userid';
        } else {
            $keywords = $this->filter_keywords($ibforums->input['keywords']);

            $this->search_type = 'posts';
        }

        //------------------------------------

        $check_keywords = trim($keywords);

        $check_keywords = str_replace('%', '', $check_keywords);

        if ((!$check_keywords) or ('' == $check_keywords) or (!isset($check_keywords))) {
            if (1 == $ibforums->input['joinname']) {
                $std->Error(['LEVEL' => 1, 'MSG' => 'no_search_words']);
            }
        }

        //------------------------------------

        if ('titles' == $ibforums->input['search_in']) {
            $this->search_in = 'titles';
        }

        //------------------------------------

        $forums = $this->get_searchable_forums();

        //------------------------------------

        // Do we have any forums to search in?

        //------------------------------------

        if ('' == $forums) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_search_forum']);
        }

        //------------------------------------

        foreach (['last_post', 'posts', 'starter_name', 'forum_id'] as $v) {
            if ($ibforums->input['sort_key'] == $v) {
                $this->sort_key = $v;
            }
        }

        //------------------------------------

        foreach ([1, 7, 30, 365, 0] as $v) {
            if ($ibforums->input['prune'] == $v) {
                $this->prune = $v;
            }
        }

        //------------------------------------

        if ('asc' == $ibforums->input['sort_order']) {
            $this->sort_order = 'asc';
        }

        //------------------------------------

        if ('posts' == $ibforums->input['result_type']) {
            $this->result_type = 'posts';
        }

        if ($ibforums->vars['min_search_word'] < 1) {
            $ibforums->vars['min_search_word'] = 4;
        }

        //------------------------------------

        // Add on the prune days

        //------------------------------------

        if ($this->prune > 0) {
            $gt_lt = 'older' == $ibforums->input['prune_type'] ? '<' : '>';

            $time = time() - ($ibforums->input['prune'] * 86400);

            $topics_datecut = "t.last_post $gt_lt $time AND";

            $posts_datecut = "p.post_date $gt_lt $time AND";
        }

        // Is this a membername search?

        $name_filter = trim($name_filter);

        $member_string = '';

        if ('' != $name_filter) {
            //------------------------------------------------------------------

            // Get all the possible matches for the supplied name from the DB

            //------------------------------------------------------------------

            if (1 == $ibforums->input['exactname']) {
                $sql_query = "SELECT uid from xbb_members WHERE lower(uname)='" . $name_filter . "'";
            } else {
                $sql_query = "SELECT uid from xbb_members WHERE uname like '%" . $name_filter . "%'";
            }

            $DB->query($sql_query);

            while (false !== ($row = $DB->fetch_row())) {
                $member_string .= "'" . $row['uid'] . "',";
            }

            $member_string = preg_replace('/,$/', '', $member_string);

            // Error out of we matched no members

            if ('' == $member_string) {
                $std->Error(['LEVEL' => 1, 'MSG' => 'no_name_search_results']);
            }

            $posts_name = " AND p.author_id IN ($member_string)";

            $topics_name = " AND t.starter_id IN ($member_string)";
        }

        if (1 == $ibforums->input['joinname']) {
            if (preg_match('/ and|or /', $keywords)) {
                preg_match_all("/(^|and|or)\s{1,}(\S+?)\s{1,}/", $keywords, $matches);

                $title_like = '(';

                $post_like = '(';

                for ($i = 0, $iMax = count($matches[0]); $i < $iMax; $i++) {
                    $boolean = $matches[1][$i];

                    $word = trim($matches[2][$i]);

                    if (mb_strlen($word) < $ibforums->vars['min_search_word']) {
                        $std->Error(['LEVEL' => 1, 'MSG' => 'search_word_short', 'EXTRA' => $ibforums->vars['min_search_word']]);
                    }

                    if ($boolean) {
                        $boolean = " $boolean";
                    }

                    $title_like .= "$boolean LOWER(t.title) LIKE '%$word%' ";

                    $post_like .= "$boolean LOWER(p.post) LIKE '%$word%' ";
                }

                $title_like .= ')';

                $post_like .= ')';
            } else {
                if (mb_strlen(trim($keywords)) < $ibforums->vars['min_search_word']) {
                    $std->Error(['LEVEL' => 1, 'MSG' => 'search_word_short', 'EXTRA' => $ibforums->vars['min_search_word']]);
                }

                $title_like = " LOWER(t.title) LIKE '%" . trim($keywords) . "%' ";

                $post_like = " LOWER(p.post) LIKE '%" . trim($keywords) . "%' ";
            }
        }

        //$posts_datecut $topics_datecut $post_like $title_like $posts_name $topics_name

        $unique_id = md5(uniqid(microtime(), 1));

        if (1 == $ibforums->input['joinname']) {
            $topics_query = "SELECT t.tid
							FROM ibf_topics t
							WHERE $topics_datecut t.forum_id IN ($forums)
							$topics_name AND t.approved=1 AND ($title_like)";

            $posts_query = 'SELECT p.pid ' . 'FROM ibf_posts p ' . "WHERE $posts_datecut  p.forum_id IN ($forums)" . ' AND p.queued <> 1' . " $posts_name AND ($post_like)";
        } else {
            $topics_query = "SELECT t.tid
							FROM ibf_topics t
							WHERE $topics_datecut t.forum_id IN ($forums)
							$topics_name";

            $posts_query = 'SELECT p.pid ' . 'FROM ibf_posts p ' . "WHERE $posts_datecut  p.forum_id IN ($forums)" . ' AND p.queued <> 1' . " $posts_name";
        }

        //------------------------------------------------

        // Get the topic ID's to serialize and store into

        // the database

        //------------------------------------------------

        $topics = '';

        $posts = '';

        //------------------------------------

        $DB->query($topics_query);

        $topic_max_hits = $DB->get_num_rows();

        while (false !== ($row = $DB->fetch_row())) {
            $topics .= $row['tid'] . ',';
        }

        $DB->free_result();

        //------------------------------------

        $DB->query($posts_query);

        $post_max_hits = $DB->get_num_rows();

        while (false !== ($row = $DB->fetch_row())) {
            $posts .= $row['pid'] . ',';
        }

        $DB->free_result();

        //------------------------------------

        $topics = preg_replace('/,$/', '', $topics);

        $posts = preg_replace('/,$/', '', $posts);

        //------------------------------------------------

        // Do we have any results?

        //------------------------------------------------

        if ('' == $topics and '' == $posts) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_search_results']);
        }

        //------------------------------------------------

        // If we are still here, store the data into the database...

        //------------------------------------------------

        $unique_id = md5(uniqid(microtime(), 1));

        $str = $DB->compile_db_insert_string(
            [
                'id' => $unique_id,
'search_date' => time(),
'topic_id' => $topics,
'topic_max' => $topic_max_hits,
'sort_key' => $this->sort_key,
'sort_order' => $this->sort_order,
'member_id' => $ibforums->member['uid'],
'ip_address' => $ibforums->input['IP_ADDRESS'],
'post_id' => $posts,
'post_max' => $post_max_hits,
            ]
        );

        $DB->query("INSERT INTO ibf_search_results ({$str['FIELD_NAMES']}) VALUES ({$str['FIELD_VALUES']})");

        $print->redirect_screen($ibforums->lang['search_redirect'], "act=Search&CODE=show&searchid=$unique_id&search_in=" . $this->search_in . '&result_type=' . $this->result_type . '&highlite=' . urlencode(trim($keywords)));

        exit();

        //&debug=1
    }

    /******************************************************/

    // Show Results

    // Shows the results of the search

    /******************************************************/

    public function show_results()
    {
        global $ibforums, $DB, $std, $_POST;

        if ($read = $std->my_getcookie('topicsread')) {
            $this->read_array = unserialize(stripslashes($read));
        }

        $this->result_type = $ibforums->input['result_type'];

        $this->search_in = $ibforums->input['search_in'];

        //------------------------------------------------

        // We have a search ID, so lets get the parsed results.

        // Delete old search queries (older than 24 hours)

        //------------------------------------------------

        $t_time = time() - (60 * 60 * 24);

        $DB->query("DELETE FROM ibf_search_results WHERE search_date < '$t_time'");

        $this->unique_id = $ibforums->input['searchid'];

        if ('' == $this->unique_id) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_search_results']);
        }

        $DB->query("SELECT * FROM ibf_search_results WHERE id='{$this->unique_id}'");

        $sr = $DB->fetch_row();

        $tmp_topics = $sr['topic_id'];

        $topic_max_hits = ''; //$sr['topic_max'];

        $tmp_posts = $sr['post_id'];

        $post_max_hits = ''; //$sr['post_max'];

        $this->sort_order = $sr['sort_order'];

        $this->sort_key = $sr['sort_key'];

        //------------------------------------------------

        // Remove duplicates from the topic_id and post_id

        //------------------------------------------------

        $topics = ',';

        $posts = ',';

        foreach (explode(',', $tmp_topics) as $tid) {
            if (!preg_match("/,$tid,/", $topics)) {
                $topics .= "$tid,";

                $topic_max_hits++;
            }
        }

        //-------------------------------------

        foreach (explode(',', $tmp_posts) as $pid) {
            if (!preg_match("/,$pid,/", $posts)) {
                $posts .= "$pid,";

                $post_max_hits++;
            }
        }

        $topics = str_replace(',,', ',', $topics);

        $posts = str_replace(',,', ',', $posts);

        //-------------------------------------

        if (',' == $topics and ',' == $posts) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_search_results']);
        }

        $url_words = $this->convert_highlite_words($ibforums->input['highlite']);

        if ('topics' == $this->result_type) {
            if ('titles' == $this->search_in) {
                $this->output .= $this->start_page($topic_max_hits);

                $DB->query(
                    "SELECT t.*, f.id as forum_id, f.name as forum_name
							FROM ibf_topics t, ibf_forums f
							 WHERE t.tid IN(0{$topics}-1) and f.id=t.forum_id
							 ORDER BY " . $this->sort_key . ' ' . $this->sort_order . '
							LIMIT ' . $this->first . ',25'
                );
            } else {
                //--------------------------------------------

                // we have tid and pid to sort out, woohoo NOT

                //--------------------------------------------

                if (',' != $posts) {
                    $DB->query("SELECT topic_id FROM ibf_posts WHERE pid IN(0{$posts}0)");

                    while (false !== ($pr = $DB->fetch_row())) {
                        if (!preg_match('/,' . $pr['topic_id'] . ',/', $topics)) {
                            $topics .= $pr['topic_id'] . ',';

                            $topic_max_hits++;
                        }
                    }

                    $topics = str_replace(',,', ',', $topics);
                }

                $this->output .= $this->start_page($topic_max_hits);

                $DB->query(
                    "SELECT t.*, f.id as forum_id, f.name as forum_name
							FROM ibf_topics t
							 LEFT JOIN ibf_forums f ON (f.id=t.forum_id)
							 WHERE t.tid IN(0{$topics}0)
							 ORDER BY t." . $this->sort_key . ' ' . $this->sort_order . '
							LIMIT ' . $this->first . ',25'
                );
            }

            //--------------------------------------------

            while (false !== ($row = $DB->fetch_row())) {
                $row['keywords'] = $url_words;

                $this->output .= $this->html->RenderRow($this->parse_entry($row));
            }

            //--------------------------------------------

            $this->output .= $this->html->end(['SHOW_PAGES' => $this->links]);
        } else { //--------------------------------------------
            require './sources/lib/post_parser.php';

            $this->parser = new post_parser();

            if ('titles' == $this->search_in) {
                $this->output .= $this->start_page($topic_max_hits, 1);

                $DB->query(
                    "SELECT t.*, p.pid, p.author_id, p.author_name, p.post_date, p.post, f.id as forum_id, f.name as forum_name
				            FROM ibf_topics t
				              LEFT JOIN ibf_posts p ON (t.tid=p.topic_id AND p.new_topic=1)
				              LEFT JOIN ibf_forums f ON (f.id=t.forum_id)
				            WHERE t.tid IN(0{$topics}-1)
				            ORDER BY " . $this->sort_key . ' ' . $this->sort_order . '
				            LIMIT ' . $this->first . ',25'
                );
            } else {
                if (',' != $topics) {
                    $DB->query("SELECT pid FROM ibf_posts WHERE topic_id IN(0{$topics}0) AND new_topic=1");

                    while (false !== ($pr = $DB->fetch_row())) {
                        if (!preg_match('/,' . $pr['pid'] . ',/', $posts)) {
                            $posts .= $pr['pid'] . ',';

                            $post_max_hits++;
                        }
                    }

                    $posts = str_replace(',,', ',', $posts);
                }

                $this->output .= $this->start_page($post_max_hits, 1);

                $DB->query(
                    "SELECT t.*, p.pid, p.author_id, p.author_name, p.post_date, p.post, f.id as forum_id, f.name as forum_name
							FROM ibf_posts p
							  LEFT JOIN ibf_topics t ON (t.tid=p.topic_id)
							  LEFT JOIN ibf_forums f ON (f.id=p.forum_id)
							WHERE p.pid IN(0{$posts}0)
							ORDER BY " . $this->sort_key . ' ' . $this->sort_order . '
							LIMIT ' . $this->first . ',25'
                );
            }

            while (false !== ($row = $DB->fetch_row())) {
                $row['keywords'] = $url_words;

                $row['post_date'] = $std->get_date($row['post_date'], 'LONG');

                $this->output .= $this->html->RenderPostRow($this->parse_entry($row, 1));
            }

            $this->output .= $this->html->end_as_post(['SHOW_PAGES' => $this->links]);
        }

        $this->page_title = $ibforums->lang['search_results'];

        if ('lv' == $ibforums->input['nav']) {
            $this->nav = [$ibforums->lang['nav_since_lv']];
        } elseif ('lt' == $ibforums->input['nav']) {
            $this->nav = [$ibforums->lang['nav_lt']];
        } else {
            $this->nav = ["<a href='{$this->base_url}&act=Search'>{$ibforums->lang['search_form']}</a>", $ibforums->lang['search_title']];
        }
    }

    public function start_page($amount, $is_post = 0)
    {
        global $ibforums, $DB, $std;

        $this->links = $std->build_pagelinks(
            [
                TOTAL_POSS => $amount,
PER_PAGE => 25,
CUR_ST_VAL => $this->first,
L_SINGLE => '',
L_MULTI => $ibforums->lang['search_pages'],
BASE_URL => $this->base_url . '&act=Search&nav=' . $ibforums->input['nav'] . '&CODE=show&searchid=' . $this->unique_id . '&search_in=' . $this->search_in . '&result_type=' . $this->result_type . '&hl=' . $url_words,
            ]
        );

        if (0 == $is_post) {
            return $this->html->start(['SHOW_PAGES' => $this->links]);
        }

        return $this->html->start_as_post(['SHOW_PAGES' => $this->links]);
    }

    /******************************************************/

    // Get active

    // Show all topics posted in / created between a user

    // definable amount of days..

    /******************************************************/

    public function get_active()
    {
        global $ibforums, $DB, $std, $_POST;

        //------------------------------------

        // If we don't have a search ID (searchid)

        // then it's a fresh query.

        //------------------------------------

        if (!isset($ibforums->input['searchid'])) {
            //------------------------------------

            // Do we have any start date input?

            //------------------------------------

            if ('' == $ibforums->input['st_day']) {
                // No? Lets work out the start date as 24hrs ago

                $ibforums->input['st_day'] = 1;

                $this->st_stamp = time() - (60 * 60 * 24);
            } else {
                $ibforums->input['st_day'] = str_replace("s", '', $ibforums->input['st_day']);

                $this->st_stamp = time() - (60 * 60 * 24 * $ibforums->input['st_day']);
            }

            //------------------------------------

            // Do we have any END date input?

            //------------------------------------

            if ('' == $ibforums->input['end_day']) {
                // No? Lets work out the end date as now

                $this->end_stamp = time();

                $ibforums->input['end_day'] = 0;
            } else {
                $ibforums->input['end_day'] = str_replace("e", '', $ibforums->input['end_day']);

                $this->end_stamp = time() - (60 * 60 * 24 * $ibforums->input['end_day']);
            }

            //------------------------------------

            // Synchronise our input data

            //------------------------------------

            $ibforums->input['cat_forum'] = 'cat';

            $ibforums->input['cats'] = 'all';

            $forums = $this->get_searchable_forums();

            //------------------------------------

            // Do we have any forums to search in?

            //------------------------------------

            if ('' == $forums) {
                $std->Error(['LEVEL' => 1, 'MSG' => 'no_search_forum']);
            }

            $query = 'SELECT DISTINCT(t.tid)
					  FROM ibf_posts p
					    LEFT JOIN ibf_topics t ON (p.topic_id=t.tid)
					  WHERE p.post_date BETWEEN ' . $this->st_stamp . ' AND ' . $this->end_stamp . "
					    AND p.forum_id IN($forums)
					    AND p.queued <> 1					    
					  ORDER BY t.last_post DESC
					  LIMIT 0,200";

            //------------------------------------------------

            // Get the topic ID's to serialize and store into

            // the database

            //------------------------------------------------

            $DB->query($query);

            $max_hits = $DB->get_num_rows();

            $topics = '';

            while (false !== ($row = $DB->fetch_row())) {
                $topics .= $row['tid'] . ',';
            }

            $DB->free_result();

            $topics = preg_replace('/,$/', '', $topics);

            //------------------------------------------------

            // Do we have any results?

            //------------------------------------------------

            if ('' == $topics) {
                $this->output .= $this->html->active_start(['SHOW_PAGES' => '']);

                $this->output .= $this->html->active_none();

                $this->output .= $this->html->end('');

                $this->page_title = $ibforums->lang['search_results'];

                $this->nav = ["<a href='{$this->base_url}&act=Search'>{$ibforums->lang['search_form']}</a>", $ibforums->lang['search_title']];

                return ''; // return empty handed
            }

            //------------------------------------------------

            // If we are still here, store the data into the database...

            //------------------------------------------------

            $unique_id = md5(uniqid(microtime(), 1));

            $str = $DB->compile_db_insert_string(
                [
                    'id' => $unique_id,
'search_date' => time(),
'topic_id' => $topics,
'topic_max' => $max_hits,
'sort_key' => $this->sort_key,
'sort_order' => $this->sort_order,
'member_id' => $ibforums->member['uid'],
'ip_address' => $ibforums->input['IP_ADDRESS'],
                ]
            );

            $DB->query("INSERT INTO ibf_search_results ({$str['FIELD_NAMES']}) VALUES ({$str['FIELD_VALUES']})");
        } else {
            //------------------------------------------------

            // We have a search ID, so lets get the parsed results.

            // Delete old search queries (older than 24 hours)

            //------------------------------------------------

            $t_time = time() - (60 * 60 * 24);

            $this->first = '' != (int)$ibforums->input['st'] ? (int)$ibforums->input['st'] : 0;

            $DB->query("DELETE FROM ibf_search_results WHERE search_date < '$t_time'");

            $unique_id = $ibforums->input['searchid'];

            $DB->query("SELECT * FROM ibf_search_results WHERE id='$unique_id'");

            $sr = $DB->fetch_row();

            $topics = $sr['topic_id'];

            $max_hits = $sr['topic_max'];

            $this->sort_order = $sr['sort_order'];

            $this->sort_key = $sr['sort_key'];

            if ('' == $topics) {
                $std->Error(['LEVEL' => 1, 'MSG' => 'no_search_results']);
            }
        }

        // Our variables are centralised, lets get the array slice depending on our $this->first

        // position.

        // How cool is this? ;)

        $topic_string = implode(',', array_slice(explode(',', $topics), $this->first, 25));

        // That splits the string into an array by a comma, gets a slice depending on our limit,

        // say 0 to 25 and then joins the array into a string seperated by commas, all in one

        // line :D

        $topic_string = str_replace(' ', '', $topic_string);

        $topic_string = preg_replace('/,$/', '', $topic_string);

        $url_words = urlencode(trim($keywords));

        $links = $std->build_pagelinks(
            [
                TOTAL_POSS => $max_hits,
PER_PAGE => 25,
CUR_ST_VAL => $this->first,
L_SINGLE => '',
L_MULTI => '',
BASE_URL => $this->base_url . "&act=Search&CODE=getactive&searchid=$unique_id",
            ]
        );

        $this->output .= $this->html->active_start(['SHOW_PAGES' => $links]);

        // Regex in our selected values.

        $this->output = preg_replace("/(<option value='s" . $ibforums->input['st_day'] . "')/", '\\1 selected', $this->output);

        $this->output = preg_replace("/(<option value='e" . $ibforums->input['end_day'] . "')/", '\\1 selected', $this->output);

        $DB->query("SELECT t.*, f.id as forum_id, f.name as forum_name FROM ibf_topics t, ibf_forums f WHERE t.tid IN($topic_string) and f.id=t.forum_id ORDER BY " . $this->sort_key . ' ' . $this->sort_order . ' LIMIT 0,25');

        while (false !== ($row = $DB->fetch_row())) {
            $row['keywords'] = $url_words;

            $this->output .= $this->html->RenderRow($this->parse_entry($row));
        }

        $this->page_title = $ibforums->lang['search_results'];

        $this->nav = ["<a href='{$this->base_url}&act=Search'>{$ibforums->lang['search_form']}</a>", $ibforums->lang['search_title']];

        $this->output .= $this->html->end(['SHOW_PAGES' => $links]);
    }

    public function parse_entry($topic, $view_as_post = 0)
    {
        global $DB, $std, $ibforums;

        $topic['last_text'] = $ibforums->lang[last_post_by];

        $topic['last_poster'] = (0 != $topic['last_poster_id']) ? "<b><a href='{$this->base_url}&act=Profile&CODE=03&MID={$topic['last_poster_id']}'>{$topic['last_poster_name']}</a></b>" : '-' . $topic['last_poster_name'] . '-';

        $topic['starter'] = (0 != $topic['starter_id']) ? "<a href='{$this->base_url}&act=Profile&CODE=03&MID={$topic['starter_id']}'>{$topic['starter_name']}</a>" : '-' . $topic['starter_name'] . '-';

        if ($topic['poll_state']) {
            $topic['prefix'] = $ibforums->vars['pre_polls'] . ' ';
        }

        $topic['folder_img'] = $std->folder_icon($topic);

        $topic['topic_icon'] = $topic['icon_id'] ? '<img src="' . $ibforums->vars['img_url'] . '/icon' . $topic['icon_id'] . '.gif" border="0" alt="">' : '&nbsp;';

        if ($topic['pinned']) {
            $topic['topic_icon'] = '<{B_PIN}>';
        }

        $topic['topic_start_date'] = $std->get_date($topic['start_date'], 'LONG');

        $pages = 1;

        if ($topic['posts']) {
            if (0 == (($topic['posts'] + 1) % $ibforums->vars['display_max_posts'])) {
                $pages = ($topic['posts'] + 1) / $ibforums->vars['display_max_posts'];
            } else {
                $number = (($topic['posts'] + 1) / $ibforums->vars['display_max_posts']);

                $pages = ceil($number);
            }
        }

        if ($pages > 1) {
            $topic['PAGES'] = "<span class='small'>({$ibforums->lang['topic_sp_pages']} ";

            for ($i = 0; $i < $pages; ++$i) {
                $real_no = $i * $ibforums->vars['display_max_posts'];

                $page_no = $i + 1;

                if (4 == $page_no) {
                    $topic['PAGES'] .= "<a href='{$this->base_url}&act=ST&f={$topic['forum_id']}&t={$topic['tid']}&st=" . ($pages - 1) * $ibforums->vars['display_max_posts'] . "&hl={$topic['keywords']}'>...$pages </a>";

                    break;
                }

                $topic['PAGES'] .= "<a href='{$this->base_url}&act=ST&f={$topic['forum_id']}&t={$topic['tid']}&st=$real_no&hl={$topic['keywords']}'>$page_no </a>";
            }

            $topic['PAGES'] .= ')</span>';
        }

        if ($topic['posts'] < 0) {
            $topic['posts'] = 0;
        }

        $last_time = $this->read_array[$topic['tid']] > $ibforums->input['last_visit'] ? $this->read_array[$topic['tid']] : $ibforums->input['last_visit'];

        if ($last_time && ($topic['last_post'] > $last_time)) {
            $topic['go_last_page'] = "<a href='{$this->base_url}&act=ST&f={$topic['forum_id']}&t={$topic['tid']}&view=getlastpost'><{GO_LAST_ON}></a>";

            $topic['go_new_post'] = "<a href='{$this->base_url}&act=ST&f={$topic['forum_id']}&t={$topic['tid']}&view=getnewpost'><{NEW_POST}></a>";
        } else {
            $topic['go_last_page'] = "<a href='{$this->base_url}&act=ST&f={$topic['forum_id']}&t={$topic['tid']}&view=getlastpost'><{GO_LAST_OFF}></a>";

            $topic['go_new_post'] = '';
        }

        // Do the quick goto last page icon stuff

        $maxpages = ($pages - 1) * $ibforums->vars['display_max_posts'];

        if ($maxpages < 0) {
            $maxpages = 0;
        }

        $topic['last_post'] = $std->get_date($topic['last_post'], 'LONG');

        if ('link' == $topic['state']) {
            $t_array = explode('&', $topic['moved_to']);

            $topic['tid'] = $t_array[0];

            $topic['forum_id'] = $t_array[1];

            $topic['title'] = $topic['title'];

            $topic['views'] = '--';

            $topic['posts'] = '--';

            $topic['prefix'] = $ibforums->vars['pre_moved'] . ' ';

            $topic['go_new_post'] = '';
        }

        if (1 == $topic['pinned']) {
            $topic['prefix'] = $ibforums->vars['pre_pinned'];

            $topic['topic_icon'] = '<{B_PIN}>';
        }

        if (1 == $view_as_post) {
            $ibforums->vars['search_post_cut'] = ('' != $ibforums->vars['search_post_cut']) ? $ibforums->vars['search_post_cut'] : 100;

            $topic['post'] = mb_substr($this->parser->unconvert($topic['post']), 0, $ibforums->vars['search_post_cut']) . '...';

            $topic['post'] = str_replace("\n", '<br>', $topic['post']);

            if ($topic['author_id']) {
                $topic['author_name'] = "<b><a href='{$this->base_url}&act=Profile&CODE=03&MID={$topic['author_id']}'>{$topic['author_name']}</a></b>";
            }
        }

        return $topic;
    }

    public function filter_keywords($words = '', $name = 0)
    {
        // force to lowercase and swop % into a safer version

        $words = trim(mb_strtolower(str_replace('%', '\\%', $words)));

        // Remove trailing boolean operators

        $words = preg_replace("/\s+(and|or)$/", '', $words);

        // Swop wildcard into *SQL percent

        //$words = str_replace( "*", "%", $words );

        // Make safe underscores

        $words = str_replace('_', '\\_', $words);

        // Remove crap

        if (0 == $name) {
            $words = preg_replace("/[\|\[\]\{\}\(\)\.,:;\?\-\+\#\\\\\/\"']/", '', $words);
        }

        return ' ' . preg_quote($words) . ' ';
    }

    //------------------------------------------------------

    // Make the hl words nice and stuff

    //------------------------------------------------------

    public function convert_highlite_words($words = '')
    {
        $words = trim(urldecode($words));

        // Convert booleans to something easy to match next time around

        $words = preg_replace("/\s+(and|or)(\s+|$)/i", ',\\1,', $words);

        // Convert spaces to plus signs

        $words = preg_replace("/\s/", '+', $words);

        return $words;
    }

    //------------------------------------------------------

    // Get the searchable forums

    //------------------------------------------------------

    public function get_searchable_forums()
    {
        global $ibforums, $DB, $std, $_POST;

        $forum_array = [];

        $forum_string = '';

        $sql_query = '';

        $check_sub = 0;

        // If we have an array of "forums", loop

        // through and build our *SQL IN( ) statement.

        // Are we looking for cats or forums..

        if ('cat' == $ibforums->input['cat_forum']) {
            if ('all' == $ibforums->input['cats']) {
                $sql_query = 'SELECT id, read_perms, password FROM ibf_forums';
            } else {
                if (!preg_match("/^(?:\d+)$/", $ibforums->input['cats'])) {
                    return;
                }

                $sql_query = "SELECT id, read_perms, password from ibf_forums WHERE category='" . $ibforums->input['cats'] . "'";
            }
        } else {
            if ('all' == $ibforums->input['forums']) {
                $sql_query = 'SELECT id, read_perms, password FROM ibf_forums';
            } else {
                if (!preg_match("/^(?:\d+)$/", $ibforums->input['forums'])) {
                    return;
                }

                if (1 == $ibforums->input['searchsubs']) {
                    $check_sub = 1;
                }

                $sql_query = "SELECT id, read_perms, password, subwrap from ibf_forums WHERE id='" . $ibforums->input['forums'] . "'";
            }
        }

        if ('' != $sql_query) {
            $DB->query($sql_query);

            // Check subbie forums..

            // Catch SQL result...

            if (1 == $check_sub) {
                $r = $DB->fetch_row();

                // Do we have perm to view this sub forum wrap?

                if ($this->check_access($r)) {
                    if (1 == $r['subwrap']) {
                        // Pass new SQL for while to catch (children and self)

                        $DB->query("SELECT id, read_perms, password FROM ibf_forums WHERE parent_id={$r['id']} or id={$r['id']}");
                    } else {
                        // Push id to array

                        $forum_array[] = $r['id'];
                    }
                }
            }

            // If we've not pushed, check for parents or

            // results from previous query.

            if (count($forum_array) < 1) {
                while (false !== ($i = $DB->fetch_row())) {
                    if ($this->check_access($i)) {
                        $forum_array[] = $i['id'];
                    }
                }
            }
        }

        $forum_string = implode(',', $forum_array);

        return $forum_string;
    }

    public function check_access($i)
    {
        global $std, $ibforums;

        $can_read = true;

        if ('' != $i['password']) {
            if (!$c_pass = $std->my_getcookie('iBForum' . $i['id'])) {
                $can_read = false;
            }

            if ($c_pass == $i['password']) {
                $can_read = true;
            } else {
                $can_read = false;
            }
        }

        if (true === $can_read) {
            if ('*' == $i['read_perms']) {
                $can_read = true;
            } elseif (preg_match('/(^|,)' . $ibforums->member['mgroup'] . '(,|$)/', $i['read_perms'])) {
                $can_read = true;
            } else {
                $can_read = false;
            }
        }

        return $can_read;
    }
}

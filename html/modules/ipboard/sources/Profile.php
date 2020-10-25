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
|   > User Profile functions
|   > Module written by Matt Mecham
|   > Date started: 28th February 2002
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

$idx = new Profile();

class Profile
{
    public $output = '';

    public $page_title = '';

    public $nav = [];

    public $html = '';

    public $parser;

    public $member = [];

    public $m_group = [];

    public $jump_html = '';

    public $parser = '';

    public $links = [];

    public $bio = '';

    public $notes = '';

    public $size = 'm';

    public $lib;

    public function __construct()
    {
        global $ibforums, $DB, $std, $print;

        require './sources/lib/post_parser.php';

        $this->parser = new post_parser();

        //--------------------------------------------

        // Make sure our code number is numerical only

        //--------------------------------------------

        //$ibforums->input['CODE'] = preg_replace("/^([0-9]+)$/", "$1", $ibforums->input['CODE']);

        if ('' == $ibforums->input['CODE']) {
            $ibforums->input['CODE'] = 00;
        }

        //--------------------------------------------

        // Require the HTML and language modules

        //--------------------------------------------

        $ibforums->lang = $std->load_words($ibforums->lang, 'lang_profile', $ibforums->lang_id);

        $this->html = $std->load_template('skin_profile');

        $this->base_url = "{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}";

        $this->base_url_nosess = "{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}";

        //--------------------------------------------

        // Check viewing permissions, etc

        //--------------------------------------------

        $this->member = $ibforums->member;

        $this->m_group = $ibforums->member;

        //--------------------------------------------

        // What to do?

        //--------------------------------------------

        switch ($ibforums->input['CODE']) {
            case '03':
                $this->view_profile();
                break;
            //------------------------------
            default:
                $this->view_profile();
                break;
        }

        // If we have any HTML to print, do so...

        $print->add_output((string)$this->output);

        $print->do_output(['TITLE' => $this->page_title, 'JS' => 1, NAV => $this->nav]);
    }

    public function view_profile()
    {
        global $ibforums, $DB, $std, $print;

        $info = [];

        if (1 != $ibforums->member['g_mem_info']) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_permission']);
        }

        //--------------------------------------------

        // Check input..

        //--------------------------------------------

        $id = preg_replace("/^(\d+)$/", '\\1', $ibforums->input['MID']);

        if (empty($id)) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'incorrect_use']);
        }

        //--------------------------------------------

        // Prepare Query...

        //--------------------------------------------

        $DB->query("SELECT m.*, g.g_id, g.g_title as group_title FROM xbb_members m, ibf_groups g WHERE m.uid='$id' and m.mgroup=g.g_id");

        $member = $DB->fetch_row();

        if (empty($member['uid'])) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'incorrect_use']);
        }

        // Play it safe

        $member['pass'] = '';

        //--------------------------------------------

        // Find the most posted in forum that the viewing

        // member has access to by this members profile

        //--------------------------------------------

        $DB->query('SELECT id, read_perms FROM ibf_forums');

        $forum_ids = ['0'];

        while (false !== ($r = $DB->fetch_row())) {
            if ('*' == $r['read_perms']) {
                $forum_ids[] = $r['id'];
            } elseif (preg_match('/(^|,)' . $this->member['mgroup'] . '(,|$)/', $r['read_perms'])) {
                $forum_ids[] = $r['id'];
            }
        }

        $forum_id_str = implode(',', $forum_ids);

        $percent = 0;

        $DB->query(
            'SELECT DISTINCT(p.forum_id), f.name, COUNT(p.author_id) as f_posts FROM ibf_posts p, ibf_forums f ' . "WHERE p.forum_id IN ($forum_id_str) AND p.author_id='" . $member['uid'] . "' AND p.forum_id=f.id GROUP BY p.forum_id ORDER BY f_posts DESC"
        );

        $favourite = $DB->fetch_row();

        $DB->query("SELECT COUNT(pid) as total_posts FROM ibf_posts WHERE author_id='" . $member['uid'] . "'");

        $total_posts = $DB->fetch_row();

        $DB->query('SELECT TOTAL_TOPICS, TOTAL_REPLIES FROM ibf_stats');

        $stats = $DB->fetch_row();

        $board_posts = $stats['TOTAL_TOPICS'] + $stats['TOTAL_REPLIES'];

        if ($total_posts['total_posts'] > 0) {
            $percent = round($favourite['f_posts'] / $total_posts['total_posts'] * 100);
        }

        if ($member['posts']) {
            $info['posts_day'] = round($member['posts'] / (((time() - $member['user_regdate']) / 86400)), 1);

            $info['total_pct'] = sprintf('%.2f', ($member['posts'] / $board_posts * 100));
        }

        if ($info['posts_day'] > $member['posts']) {
            $info['posts_day'] = $member['posts'];
        }

        $info['posts'] = $member['posts'] ?: 0;

        $info['name'] = $member['uname'];

        $info['mid'] = $member['uid'];

        $info['fav_forum'] = $favourite['name'];

        $info['fav_id'] = $favourite['forum_id'];

        $info['fav_posts'] = $favourite['f_posts'];

        $info['percent'] = $percent;

        $info['group_title'] = $member['group_title'];

        $info['board_posts'] = $board_posts;

        $info['joined'] = $std->get_date($member['user_regdate'], 'LONG');

        $info['member_title'] = $member['title'] ?: $ibforums->lang['no_info'];

        $info['aim_name'] = $member['user_aim'] ?: $ibforums->lang['no_info'];

        $info['icq_number'] = $member['user_icq'] ?: $ibforums->lang['no_info'];

        $info['yahoo'] = $member['user_yim'] ?: $ibforums->lang['no_info'];

        $info['location'] = $member['user_from'] ?: $ibforums->lang['no_info'];

        $info['interests'] = $member['user_intrest'] ?: $ibforums->lang['no_info'];

        $info['msn_name'] = $member['user_msnm'] ?: $ibforums->lang['no_info'];

        $ibforums->vars['time_adjust'] = '' == $ibforums->vars['time_adjust'] ? 0 : $ibforums->vars['time_adjust'];

        if (1 == $member['dst_in_use']) {
            $member['timezone_offset'] += 1;
        }

        // This is a useless comment. Completely void of any useful information

        $info['local_time'] = '' != $member['timezone_offset'] ? gmdate('h:i A', time() + ($member['timezone_offset'] * 3600) + ($ibforums->vars['time_adjust'] * 60)) : $ibforums->lang['no_info'];

        $info['avatar'] = $std->get_avatar($member['user_avatar'], 1, $member['avatar_size']);

        $info['signature'] = $member['signature'];

        if ($member['url'] and preg_match("/^http:\/\/\S+$/", $member['url'])) {
            $info['homepage'] = "<a href='{$member['url']}' target='_blank'>{$member['url']}</a>";
        } else {
            $info['homepage'] = $ibforums->lang['no_info'];
        }

        if ($member['bday_month']) {
            $info['birthday'] = $member['bday_day'] . ' ' . $ibforums->lang['M_' . $member['bday_month']] . ' ' . $member['bday_year'];
        } else {
            $info['birthday'] = $ibforums->lang['no_info'];
        }

        if ($member['user_viewemail']) {
            $info['email'] = "<a href='{$this->base_url}&act=Mail&CODE=00&MID={$member['uid']}'>{$ibforums->lang['click_here']}</a>";
        } else {
            $info['email'] = $ibforums->lang['private'];
        }

        $info['base_url'] = $this->base_url;

        $this->output .= $this->html->show_profile($info);

        // Is this our profile?

        if ($member['uid'] == $this->member['uid']) {
            $this->output = preg_replace('/<!--MEM OPTIONS-->/e', '$this->html->user_edit($info)', $this->output);
        }

        // Can mods see the hidden parts of this profile?

        $query_extra = 'WHERE fedit=1 AND fhide <> 1';

        $custom_out = '';

        $field_data = [];

        if ($ibforums->member['uid']) {
            if (1 == $ibforums->member['g_is_supmod']) {
                $query_extra = '';
            } elseif ($ibforums->member['mgroup'] == $ibforums->vars['admin_group']) {
                $query_extra = '';
            }
        }

        $DB->query("SELECT * from ibf_pfields_content WHERE member_id='" . $member['uid'] . "'");

        while (false !== ($content = $DB->fetch_row())) {
            foreach ($content as $k => $v) {
                if (preg_match("/^field_(\d+)$/", $k, $match)) {
                    $field_data[$match[1]] = $v;
                }
            }
        }

        $DB->query("SELECT * from ibf_pfields_data $query_extra ORDER BY forder");

        while (false !== ($row = $DB->fetch_row())) {
            if ('drop' == $row['ftype']) {
                $carray = explode('|', trim($row['fcontent']));

                foreach ($carray as $entry) {
                    $value = explode('=', $entry);

                    $ov = trim($value[0]);

                    $td = trim($value[1]);

                    if ($field_data[$row['fid']] == $ov) {
                        $field_data[$row['fid']] = $td;
                    }
                }
            } else {
                $field_data[$row['fid']] = ('' == $field_data[$row['fid']]) ? $ibforums->lang['no_info'] : nl2br($field_data[$row['fid']]);
            }

            $custom_out .= $this->html->custom_field($row['ftitle'], $field_data[$row['fid']]);
        }

        if ('' != $custom_out) {
            $this->output = str_replace('<!--{CUSTOM.FIELDS}-->', $custom_out, $this->output);
        }

        $this->page_title = $ibforums->lang['page_title'];

        $this->nav = [$ibforums->lang['page_title']];
    }
}

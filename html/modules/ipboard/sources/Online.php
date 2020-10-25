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
|   > Show online users
|   > Module written by Matt Mecham
|   > Date started: 12th March 2002
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

$idx = new Online();

class Online
{
    public $output = '';

    public $page_title = '';

    public $nav = [];

    public $html = '';

    public $first = 0;

    public $perpage = 25;

    public $forums = [];

    public $cats = [];

    public $sessions = [];

    public $where = [];

    public $seen_name = [];

    public function __construct()
    {
        global $ibforums, $DB, $std, $print;

        // Are we allowed to see the online list?

        if (1 != $ibforums->vars['allow_online_list']) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'no_permission']);
        }

        if ('' == $ibforums->input['CODE']) {
            $ibforums->input['CODE'] = 'listall';
        }

        //--------------------------------------------

        // Require the HTML and language modules

        //--------------------------------------------

        $ibforums->lang = $std->load_words($ibforums->lang, 'lang_online', $ibforums->lang_id);

        $this->html = $std->load_template('skin_online');

        $this->base_url = "{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}";

        //--------------------------------------------

        // Build up our language hash

        //--------------------------------------------

        foreach ($ibforums->lang as $k => $v) {
            if (preg_match("/^WHERE_(\w+)$/", $k, $match)) {
                $this->where[$match[1]] = $ibforums->lang[$k];
            }
        }

        unset($match);

        //--------------------------------------------

        // What to do?

        //--------------------------------------------

        switch ($ibforums->input['CODE']) {
            case 'listall':
                $this->list_all();
                break;
            case '02':
                $this->list_forum();
                break;
            default:
                $this->list_all();
                break;
        }

        // If we have any HTML to print, do so...

        $print->add_output((string)$this->output);

        $print->do_output(['TITLE' => $this->page_title, 'JS' => 0, NAV => $this->nav]);
    }

    /*****************************************************/

    // list_all

    // ------------------

    // List all online users

    /*****************************************************/

    public function list_all()
    {
        global $ibforums, $DB, $std;

        $this->first = 0;

        if (!empty($ibforums->input['st'])) {
            $this->first = $ibforums->input['st'];
        }

        $last_cat_id = -1;

        $DB->query('SELECT f.id, f.name, f.read_perms, f.password, c.id AS cat_id, c.name AS cat_name FROM ibf_forums f, ibf_categories c WHERE c.id=f.category ORDER BY c.position, f.position');

        while (false !== ($i = $DB->fetch_row())) {
            if ($last_cat_id != $i['cat_id']) {
                // Print the category

                $last_cat_id = $i['cat_id'];

                $this->cats[$i['cat_id']] = $this->cats['cat_name'];
            }

            $this->forums[$i['id']] = [
                'name' => $i['name'],
'read_perms' => $i['read_perms'],
'password' => $i['password'],
            ];
        }

        $DB->free_result();

        $cut_off = ('' != $ibforums->vars['au_cutoff']) ? $ibforums->vars['au_cutoff'] * 60 : 900;

        $t_time = time() - $cut_off;

        $DB->query("SELECT COUNT(sess_id) as total_sessions FROM ibf_sessions WHERE login_type <> 1 AND running_time > $t_time");

        $max = $DB->fetch_row();

        $DB->free_result();

        $links = $std->build_pagelinks(
            [
                'TOTAL_POSS' => $max['total_sessions'],
'PER_PAGE' => 25,
'CUR_ST_VAL' => $this->first,
'L_SINGLE' => '',
'L_MULTI' => $ibforums->lang['pages'],
'BASE_URL' => $this->base_url . '&act=Online&CODE=listall',
            ]
        );

        $this->output = $this->html->Page_header($links);

        // Grab all the current sessions.

        $final = [];

        $tid_array = [];

        $topics = [];

        $DB->query(
            "SELECT s.id, s.in_forum, s.in_topic, s.member_name, s.member_id, s.sess_ip, s.running_time, s.location, s.login_type, s.member_group, g.prefix, g.suffix FROM ibf_sessions s, ibf_groups g WHERE s.running_time > $t_time AND s.member_group=g.g_id ORDER BY running_time DESC LIMIT "
            . $this->first
            . ',25'
        );

        while (false !== ($r = $DB->fetch_row())) {
            $final[] = $r;

            if ('' != $r['in_topic']) {
                $tid_array[] = $r['in_topic'];
            }
        }

        if (count($tid_array) > 0) {
            $tid_string = implode(',', $tid_array);

            $DB->query("SELECT tid, title FROM ibf_topics WHERE tid IN ($tid_string)");

            while (false !== ($t = $DB->fetch_row())) {
                $topics[$t['tid']] = $t['title'];
            }
        }

        foreach ($final as $idx => $sess) {
            //----------------------------------------------------

            // Is this a member, and have we seen them before?

            // Proxy servers, etc can confuse the session handler,

            // creating duplicate session IDs for the same user when

            // their IP address changes.

            //----------------------------------------------------

            $inv = '';

            if (1 == $sess['login_type']) {
                if (($ibforums->member['mgroup'] == $ibforums->vars['admin_group']) and (1 != $ibforums->vars['disable_admin_anon'])) {
                    $inv = '*';
                } else {
                    continue;
                }
            }

            if (!empty($sess['member_name'])) {
                if (isset($this->seen_name[$sess['member_name']])) {
                    continue;
                }

                $this->seen_name[$sess['member_name']] = 1;
            }

            if (isset($sess['location'])) {
                $line = '';

                [$act, $pid] = explode(',', $sess['location']);

                $fid = $sess['in_forum'];

                $tid = $sess['in_topic'];

                if (isset($act)) {
                    $line = $this->where[$act] ?? $ibforums->lang['board_index'];
                }

                if ('' != $fid and ('SF' == $act or 'ST' == $act or 'Post' == $act)) {
                    $pass = 0;

                    if ('*' == $this->forums[$fid]['read_perms']) {
                        $pass = 1;
                    } elseif (preg_match('/(^|,)' . $ibforums->member['mgroup'] . '(,|$)/', $this->forums[$fid]['read_perms'])) {
                        $pass = 1;
                    }

                    if (1 == $pass) {
                        // Check cookie (monster)

                        if ('' != $this->forums[$fid]['password']) {
                            if (!$c_pass = $std->my_getcookie('iBForum' . $fid)) {
                                $pass = 0;
                            }

                            if ($c_pass == $this->forums[$fid]['password']) {
                                $pass = 1;
                            } else {
                                $pass = 0;
                            }
                        } else {
                            $pass = 1;
                        }
                    }

                    if (1 == $pass) {
                        if (($tid > 0) and ('Post' != $act)) {
                            $line .= " <a href='{$this->base_url}&act=ST&f=$fid&t=$tid'>{$topics[$tid]}</a>";
                        } else {
                            $line .= " <a href='{$this->base_url}&act=SF&f=$fid'>{$this->forums[ $fid ]['name']}</a>";
                        }
                    } else {
                        $line = " <a href='{$this->base_url}'>{$ibforums->lang['board_index']}</a>";
                    }
                }
            } else {
                $line .= " <a href='{$this->base_url}'>{$ibforums->lang['board_index']}</a>";
            }

            $sess['where_line'] = $line;

            if (($ibforums->member['mgroup'] == $ibforums->vars['admin_group']) and (1 != $ibforums->vars['disable_online_ip'])) {
                $sess['ip_address'] = ' ( ' . $sess['sess_ip'] . ' )';
            } else {
                $sess['ip_address'] = '';
            }

            if (isset($sess['member_id'])) {
                $sess['member_name'] = "<a href='{$this->base_url}&act=Profile&CODE=03&MID={$sess['member_id']}'>{$sess['prefix']}{$sess['member_name']}{$sess['suffix']}</a>$inv {$sess['ip_address']}";
            }

            $sess['running_time'] = $std->get_date($sess['sess_updated'], 'LONG');

            $this->output .= $this->do_html_row($sess);
        }

        $this->output .= $this->html->Page_end($links);

        $this->page_title = $ibforums->lang['page_title'];

        $this->nav = [$ibforums->lang['page_title']];
    }

    public function do_html_row($sess)
    {
        global $ibforums;

        if ($sess['member_name'] and $sess['member_id']) {
            $sess['msg_icon'] = "<a href='{$this->base_url}&act=Msg&CODE=04&MID={$sess['member_id']}'><{P_MSG}></a>";
        } else {
            $sess['member_name'] = $sess['prefix'] . $ibforums->lang['guest'] . $sess['suffix'] . ' ' . $sess['ip_address'];

            $sess['msg_icon'] = '&nbsp;';
        }

        return $this->html->show_row($sess);
    }

    public function list_forum()
    {
    }
}

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
|   > Admin Logs Stuff
|   > Module written by Matt Mecham
|   > Date started: 11nd September 2002
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

// Ensure we've not accessed this script directly:

$idx = new ad_adlogs();

class ad_adlogs
{
    public $base_url;

    public $colours = [];

    public function __construct()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        $tmp_in = array_merge($_GET, $_POST, $_COOKIE);

        foreach ($tmp_in as $k => $v) {
            unset($$k);
        }

        // Make sure we're a root admin, or else!

        if ($MEMBER['mgroup'] != $INFO['admin_group']) {
            $ADMIN->error('Sorry, these functions are for the root admin group only');
        }

        $this->colours = [
            'cat' => 'green',
'forum' => 'darkgreen',
'mem' => 'red',
'group' => 'purple',
'mod' => 'orange',
'op' => 'darkred',
'help' => 'darkorange',
'modlog' => 'steelblue',
        ];

        switch ($IN['code']) {
            case 'view':
                $this->view();
                break;
            case 'remove':
                $this->remove();
                break;
            //-------------------------
            default:
                $this->list_current();
                break;
        }
    }

    //---------------------------------------------

    // Remove archived files

    //---------------------------------------------

    public function view()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        $start = $IN['st'] ?: 0;

        $ADMIN->page_detail = 'Viewing all actions by a administrator';

        $ADMIN->page_title = 'Administration Logs Manager';

        if ('' == $IN['search_string']) {
            $DB->query("SELECT COUNT(id) as count FROM ibf_admin_logs WHERE member_id='" . $IN['mid'] . "'");

            $row = $DB->fetch_row();

            $row_count = $row['count'];

            $query = "&act=adminlog&mid={$IN['mid']}&code=view";

            $DB->query(
                "SELECT m.*, mem.uid, mem.uname FROM ibf_admin_logs m, xbb_members mem
					    WHERE m.member_id='" . $IN['mid'] . "' AND m.member_id=mem.uid ORDER BY m.ctime DESC LIMIT $start, 20"
            );
        } else {
            $IN['search_string'] = urldecode($IN['search_string']);

            $dbq = 'm.' . $IN['search_type'] . " LIKE '%" . $IN['search_string'] . "%'";

            $DB->query("SELECT COUNT(m.id) as count FROM ibf_admin_logs m WHERE $dbq");

            $row = $DB->fetch_row();

            $row_count = $row['count'];

            $query = "&act=modlog&code=view&search_type={$IN['search_type']}&search_string=" . urlencode($IN['search_string']);

            $DB->query(
                "SELECT m.*, mem.uid, mem.uname FROM ibf_admin_logs m, xbb_members mem
					    WHERE m.member_id='" . $IN['mid'] . "' AND m.member_id=mem.uid AND $dbq ORDER BY m.ctime DESC LIMIT $start, 20"
            );
        }

        $links = $std->build_pagelinks(
            [
                'TOTAL_POSS' => $row_count,
'PER_PAGE' => 20,
'CUR_ST_VAL' => $start,
'L_SINGLE' => 'Single Page',
'L_MULTI' => 'Pages: ',
'BASE_URL' => $ADMIN->base_url . $query,
            ]
        );

        $ADMIN->page_detail = 'You may view and remove actions performed by your administrators';

        $ADMIN->page_title = 'Administrator Logs Manager';

        //+-------------------------------

        $SKIN->td_header[] = ['Member Name', '20%'];

        $SKIN->td_header[] = ['Action Perfomed', '40%'];

        $SKIN->td_header[] = ['Time of action', '20%'];

        $SKIN->td_header[] = ['IP address', '20%'];

        $ADMIN->html .= $SKIN->start_table('Saved Admin Logs');

        $ADMIN->html .= $SKIN->add_td_basic($links, 'center', 'catrow');

        if ($DB->get_num_rows()) {
            while (false !== ($row = $DB->fetch_row())) {
                $row['ctime'] = $ADMIN->get_date($row['ctime'], 'LONG');

                $ADMIN->html .= $SKIN->add_td_row(
                    [
                        "<b>{$row['uname']}</b>",
                        "<span style='color:{$this->colours[$row['act']]}'>{$row['note']}</span>",
                        (string)($row['ctime']),
                        (string)($row['ip_address']),
                    ]
                );
            }
        } else {
            $ADMIN->html .= $SKIN->add_td_basic('<center>No results</center>');
        }

        $ADMIN->html .= $SKIN->add_td_basic($links, 'center', 'tdtop');

        $ADMIN->html .= $SKIN->end_table();

        //+-------------------------------

        //+-------------------------------

        $ADMIN->output();
    }

    //---------------------------------------------

    // Remove archived files

    //---------------------------------------------

    public function remove()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        if ('' == $IN['mid']) {
            $ADMIN->error('You did not select a member ID to remove by!');
        }

        $DB->query("DELETE FROM ibf_admin_logs WHERE member_id='" . $IN['mid'] . "'");

        $std->boink_it($ADMIN->base_url . '&act=adminlog');

        exit();
    }

    //-------------------------------------------------------------

    // SHOW ALL LANGUAGE PACKS

    //-------------------------------------------------------------

    public function list_current()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        $form_array = [];

        $ADMIN->page_detail = 'You may view and remove actions performed by your administrators in mission critical areas of the administration CP (such as forum control, member control, group control, help files and moderator log management).';

        $ADMIN->page_title = 'Administration Logs Manager';

        //+-------------------------------

        // LAST FIVE ACTIONS

        //+-------------------------------

        $DB->query(
            'SELECT m.*, mem.uid, mem.uname FROM ibf_admin_logs m, xbb_members mem
					    WHERE m.member_id=mem.uid ORDER BY m.ctime DESC LIMIT 0, 5'
        );

        $SKIN->td_header[] = ['Member Name', '20%'];

        $SKIN->td_header[] = ['Action Perfomed', '40%'];

        $SKIN->td_header[] = ['Time of action', '20%'];

        $SKIN->td_header[] = ['IP address', '20%'];

        $ADMIN->html .= $SKIN->start_table('Last 5 Admin Actions');

        if ($DB->get_num_rows()) {
            while (false !== ($row = $DB->fetch_row())) {
                $row['ctime'] = $ADMIN->get_date($row['ctime'], 'LONG');

                $ADMIN->html .= $SKIN->add_td_row(
                    [
                        "<b>{$row['uname']}</b>",
                        "<span style='color:{$this->colours[$row['act']]}'>{$row['note']}</span>",
                        (string)($row['ctime']),
                        (string)($row['ip_address']),
                    ]
                );
            }
        } else {
            $ADMIN->html .= $SKIN->add_td_basic('<center>No results</center>');
        }

        $ADMIN->html .= $SKIN->end_table();

        //+-------------------------------

        $SKIN->td_header[] = ['Member Name', '30%'];

        $SKIN->td_header[] = ['Actions Perfomed', '20%'];

        $SKIN->td_header[] = ['View all by member', '20%'];

        $SKIN->td_header[] = ['Remove all by member', '30%'];

        $ADMIN->html .= $SKIN->start_table('Saved Admininstration Logs');

        $DB->query('SELECT m.*, mem.uname, count(m.id) AS act_count FROM ibf_admin_logs m, xbb_members mem WHERE m.member_id=mem.uid GROUP BY m.member_id ORDER BY act_count DESC');

        while (false !== ($r = $DB->fetch_row())) {
            $ADMIN->html .= $SKIN->add_td_row(
                [
                    "<b>{$r['uname']}</b>",
                    "<center>{$r['act_count']}</center>",
                    "<center><a href='" . $SKIN->base_url . "&act=adminlog&code=view&mid={$r['member_id']}'>View</a></center>",
                    "<center><a href='" . $SKIN->base_url . "&act=adminlog&code=remove&mid={$r['member_id']}'>Remove</a></center>",
                ]
            );
        }

        $ADMIN->html .= $SKIN->end_table();

        //+-------------------------------

        //+-------------------------------

        $ADMIN->html .= $SKIN->start_form(
            [
                1 => ['code', 'view'],
2 => ['act', 'adminlog'],
            ]
        );

        $SKIN->td_header[] = ['&nbsp;', '40%'];

        $SKIN->td_header[] = ['&nbsp;', '60%'];

        $ADMIN->html .= $SKIN->start_table('Search Admin Logs');

        $form_array = [
            0 => ['note', 'Action Performed'],
1 => ['ip_address', 'IP Address'],
2 => ['member_id', 'Member ID'],
3 => ['act', 'ACT Setting'],
4 => ['code', 'CODE Setting'],
        ];

        //+-------------------------------

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Search for...</b>',
                $SKIN->form_input('search_string'),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Search in...</b>',
                $SKIN->form_dropdown('search_type', $form_array),
            ]
        );

        $ADMIN->html .= $SKIN->end_form('Search');

        $ADMIN->html .= $SKIN->end_table();

        //+-------------------------------

        //+-------------------------------

        $ADMIN->output();
    }
}

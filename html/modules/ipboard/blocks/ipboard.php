<?php

// $Id: ipboard.php,v 1.1 29/01/2003 11:21:27 Koudanshi modify Exp $
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <https://www.xoops.org>                             //
//  ------------------------------------------------------------------------ //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
//  ------------------------------------------------------------------------ //
// Author: Kazumi Ono (AKA onokazu)                                          //
// URL: http://www.myweb.ne.jp/, https://www.xoops.org/, http://jp.xoops.org/ //
// Project: The XOOPS Project                                                //
// ------------------------------------------------------------------------- //

function ipboard_topics_show($options)
{
    global $sid_bb, $meminfo, $std;

    $db = XoopsDatabaseFactory::getDatabaseConnection();

    $myts = MyTextSanitizer::getInstance();

    $block = [];

    switch ($options[2]) {
        case 'views':
            $order = 't.views';
            break;
        case 'replies':
            $order = 't.posts';
            break;
        case 'time':
        default:
            $order = 't.last_post';
            break;
    }

    //-----------------------

    // Load skin image dir

    //-----------------------

    $sql = $db->query('SELECT * FROM ' . $db->prefix('ipb_skins') . ' WHERE sid=' . $meminfo['skin'] . '');

    $skin = $db->fetchArray($sql);

    if (empty($skin['img_dir']) or '' == $skin['img_dir']) {
        $skin['img_dir'] = 1;
    }

    $query = 'SELECT t.tid, t.title, t.start_date, t.last_post, t.views, t.posts, t.forum_id, p.author_name, p.author_id, p.icon_id, f.id, f.name, f.read_perms
		FROM ' . $db->prefix('ipb_topics') . ' t, ' . $db->prefix('ipb_posts') . ' p, ' . $db->prefix('ipb_forums') . ' f
		WHERE f.id = t.forum_id
			AND (t.last_post = p.post_date)
			AND (t.tid = p.topic_id)	
			ORDER BY ' . $order . ' DESC';

    if (!$result = $db->query($query, $options[0], 0)) {
        return false;
    }

    if (0 != $options[1]) {
        $block['full_view'] = true;
    } else {
        $block['full_view'] = false;
    }

    $block['lang_forum'] = _MB_IPBOARD_FORUM;

    $block['lang_topic'] = _MB_IPBOARD_TOPIC;

    $block['lang_replies'] = _MB_IPBOARD_RPLS;

    $block['lang_views'] = _MB_IPBOARD_VIEWS;

    $block['lang_by'] = _MB_IPBOARD_BY;

    $block['lang_lastpost'] = _MB_IPBOARD_LPOST;

    $block['lang_visitforums'] = _MB_IPBOARD_VSTFRMS;

    while (false !== ($arr = $db->fetchArray($result))) {
        if ('*' == $arr['read_perms'] or (preg_match('/(^|,)' . $meminfo['mgroup'] . '(,|$)/', $arr['read_perms']))) {
            $topic['forum_id'] = $arr['id'];

            $topic['forum_name'] = htmlspecialchars($arr['name'], ENT_QUOTES | ENT_HTML5);

            $topic['id'] = $arr['tid'];

            $topic['title'] = htmlspecialchars($arr['title'], ENT_QUOTES | ENT_HTML5);

            $topic['replies'] = $arr['posts'];

            $topic['views'] = $arr['views'];

            $topic['time'] = formatTimestamp($arr['last_post'], 'm');

            $topic['sess_id'] = $sid_bb;

            $topic['last_post_name'] = $arr['author_name'];

            $topic['last_post_id'] = $arr['author_id'];

            $topic['pages'] = show_page($arr['posts'], $arr['id'], $arr['tid']);

            $topic['img_dir'] = XOOPS_URL . '/modules/ipboard/style_images/' . $skin['img_dir'] . '/icon' . $arr['icon_id'] . '.gif';

            $block['topics'][] = &$topic;

            unset($topic);
        }
    }

    return $block;
}

function ipboard_topics_edit($options)
{
    $inputtag = "<input type='text' name='options[0]' value='" . $options[0] . "'>";

    $form = sprintf(_MB_IPBOARD_DISPLAY, $inputtag);

    $form .= '<br>' . _MB_IPBOARD_DISPLAYF . "&nbsp;<input type='radio' name='options[1]' value='1'";

    if (1 == $options[1]) {
        $form .= ' checked';
    }

    $form .= '>&nbsp;' . _YES . "<input type='radio' name='options[1]' value='0'";

    if (0 == $options[1]) {
        $form .= ' checked';
    }

    $form .= '>&nbsp;' . _NO;

    $form .= '<input type="hidden" name="options[2]" value="' . $options[2] . '">';

    return $form;
}

function show_page($data, $f, $t)
{
    global $sid_bb;

    include '' . XOOPS_ROOT_PATH . '/modules/ipboard/conf_global.php';

    $pages = 1;

    if (0 == (($data + 1) % $INFO['display_max_posts'])) {
        $pages = ($data + 1) / $INFO['display_max_posts'];
    } else {
        $number = (($data + 1) / $INFO['display_max_posts']);

        $pages = ceil($number);
    }

    $pages_link = '';

    if ($pages > 1) {
        $pages_link = "<span style='font-size:11px; font-weight:bold; font-family:verdana,tahoma;'>(" . _MB_IPBOARD_PAGES . ' ';

        for ($i = 0; $i < $pages; ++$i) {
            $real_no = $i * $INFO['display_max_posts'];

            $page_no = $i + 1;

            if (4 == $page_no) {
                $pages_link .= "<a href='" . XOOPS_URL . "/modules/ipboard/index.php?s=$sid_bb&act=ST&f=$f&t=$t&st=" . ($pages - 1) * $INFO['display_max_posts'] . "'>...$pages </a>";

                break;
            }

            $pages_link .= "<a href='" . XOOPS_URL . "/modules/ipboard/index.php?s=$sid_bb&act=ST&f=$f&t=$t&st=$real_no'>$page_no </a>";
        }

        $pages_link .= ')</span>';
    }

    return $pages_link;
}

function ipboard_bday_show($options)
{
    global $uid_bb, $sid_bb, $meminfo;

    $db = XoopsDatabaseFactory::getDatabaseConnection();

    $myts = MyTextSanitizer::getInstance();

    $block = [];

    switch ($options[2]) {
        case 'ages':
            $order = 'bday_year';
            break;
        case 'name':
        default:
            $order = 'uname';
            break;
    }

    $user_time = time() + ($meminfo['timezone_offset'] * 3600);

    $date = getdate($user_time);

    $day = $date['mday'];

    $month = $date['mon'];

    $year = $date['year'];

    $query = 'SELECT user_avatar, uid, uname, bday_day as DAY, bday_month as MONTH, bday_year as YEAR FROM ' . $db->prefix('users') . " 
				WHERE bday_day=$day 
				AND bday_month=$month 
				ORDER BY " . $order . ' DESC';

    if (!$result = $db->query($query, $options[0], 0)) {
        return false;
    }

    if (0 != $options[1]) {
        $block['avatar'] = true;
    } else {
        $block['avatar'] = false;
    }

    $block['lang_mem'] = _MB_IPBOARD_BDAY_MEM;

    $block['lang_ages'] = _MB_IPBOARD_BDAY_AGES;

    $count = 0;

    while (false !== ($arr = $db->fetchArray($result))) {
        $pyear = $year - $arr['YEAR'];  // $year = 2002 and $user['YEAR'] = 1976

        $bday['name'] = $arr['uname'];

        $bday['sess_id'] = $sid_bb;

        $bday['user_link'] = XOOPS_URL . '/userinfo.php?uid=' . $arr['uid'];

        $bday['avatar'] = XOOPS_URL . '/uploads/' . $arr['user_avatar'];

        $bday['ages'] = $pyear;

        $block['bday'][] = &$bday;

        unset($bday);

        $count++;
    }

    if (0 == $count) {
        $block['no_bday'] = true;

        $block['lang_no_bday'] = _MB_IPBOARD_BDAY_NONE;
    }

    return $block;
}

function ipboard_bday_edit($options)
{
    $inputtag = "<input type='text' name='options[0]' value='" . $options[0] . "'>";

    $form = sprintf(_MB_IPBOARD_BDAY_DISP, $inputtag);

    //Option 2

    $form .= '<br>' . _MB_IPBOARD_BDAY_AV_VIEW . "&nbsp;<input type='radio' name='options[1]' value='1'";

    if (1 == $options[1]) {
        $form .= ' checked';
    }

    $form .= '>&nbsp;' . _YES . "&nbsp;<input type='radio' name='options[1]' value='0'";

    if (0 == $options[1]) {
        $form .= ' checked';
    }

    $form .= '>&nbsp;' . _NO;

    //Option 3

    $form .= '<br>' . _MB_IPBOARD_BDAY_ORDER . "&nbsp;<input type='radio' name='options[2]' value='name'";

    if ('name' == $options[2]) {
        $form .= ' checked';
    }

    $form .= '>&nbsp;' . _MB_IPBOARD_BDAY_ORDER_NAME . "<input type='radio' name='options[2]' value='ages'";

    if ('ages' == $options[2]) {
        $form .= ' checked';
    }

    $form .= '>&nbsp;' . _MB_IPBOARD_BDAY_ORDER_AGES;

    return $form;
}

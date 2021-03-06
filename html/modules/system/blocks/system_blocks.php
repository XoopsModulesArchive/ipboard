<?php

// $Id: system_blocks.php,v 1.32 2003/04/15 08:45:47 okazu Exp $
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

function b_system_online_show()
{
    global $xoopsConfig, $xoopsUser, $xoopsModule, $HTTP_SERVER_VARS;

    $onlineHandler = xoops_getHandler('online');

    // mt_srand((double)microtime() * 1000000);

    // set gc probabillity to 10% for now..

    if (random_int(1, 100) < 11) {
        $onlineHandler->gc(300);
    }

    if (is_object($xoopsUser)) {
        $uid = $xoopsUser->getVar('uid');

        $uname = $xoopsUser->getVar('uname');
    } else {
        $uid = 0;

        $uname = '';
    }

    if (is_object($xoopsModule)) {
        $onlineHandler->write($uid, $uname, time(), $xoopsModule->getVar('mid'), $HTTP_SERVER_VARS['REMOTE_ADDR']);
    } else {
        $onlineHandler->write($uid, $uname, time(), 0, $HTTP_SERVER_VARS['REMOTE_ADDR']);
    }

    $onlines = &$onlineHandler->getAll();

    if (false !== $onlines) {
        $total = count($onlines);

        $block = [];

        $guests = 0;

        $members = '';

        for ($i = 0; $i < $total; $i++) {
            if ($onlines[$i]['online_uid'] > 0) {
                $members .= ' <a href="' . XOOPS_URL . '/userinfo.php?uid=' . $onlines[$i]['online_uid'] . '">' . $onlines[$i]['online_uname'] . '</a>,';
            } else {
                $guests++;
            }
        }

        $block['online_total'] = sprintf(_ONLINEPHRASE, $total);

        if (is_object($xoopsModule)) {
            $mytotal = $onlineHandler->getCount(new Criteria('online_module', $xoopsModule->getVar('mid')));

            $block['online_total'] .= ' (' . sprintf(_ONLINEPHRASEX, $mytotal, $xoopsModule->getVar('name')) . ')';
        }

        $block['lang_members'] = _MEMBERS;

        $block['lang_guests'] = _GUESTS;

        $block['online_names'] = $members;

        $block['online_members'] = $total - $guests;

        $block['online_guests'] = $guests;

        $block['lang_more'] = _MORE;

        return $block;
    }

    return false;
}

function b_system_login_show()
{
    global $xoopsUser, $xoopsConfig, $HTTP_COOKIE_VARS;

    if (!$xoopsUser) {
        $block = [];

        $block['lang_username'] = _USERNAME;

        $block['unamevalue'] = '';

        if (isset($HTTP_COOKIE_VARS[$xoopsConfig['usercookie']])) {
            $block['unamevalue'] = $HTTP_COOKIE_VARS[$xoopsConfig['usercookie']];
        }

        $block['lang_password'] = _PASSWORD;

        $block['lang_login'] = _LOGIN;

        $block['lang_lostpass'] = _MB_SYSTEM_LPASS;

        $block['lang_registernow'] = _MB_SYSTEM_RNOW;

        if (1 == $xoopsConfig['use_ssl'] && '' != $xoopsConfig['sslloginlink']) {
            $block['sslloginlink'] = "<a href=\"javascript:openWithSelfMain('" . $xoopsConfig['sslloginlink'] . "', 'ssllogin', 300, 200);\">" . _MB_SYSTEM_SECURE . '</a>';
        }

        return $block;
    }

    return false;
}

function b_system_main_show()
{
    global $xoopsUser, $xoopsModule;

    $block = [];

    $block['lang_home'] = _MB_SYSTEM_HOME;

    $block['lang_close'] = _CLOSE;

    $moduleHandler = xoops_getHandler('module');

    $criteria = new CriteriaCompo(new Criteria('hasmain', 1));

    $criteria->add(new Criteria('isactive', 1));

    $criteria->add(new Criteria('weight', 0, '>'));

    $modules = $moduleHandler->getObjects($criteria, true);

    $modulepermHandler = xoops_getHandler('groupperm');

    $groups = is_object($xoopsUser) ? $xoopsUser->getGroups() : XOOPS_GROUP_ANONYMOUS;

    $read_allowed = $modulepermHandler->getItemIds('module_read', $groups);

    foreach (array_keys($modules) as $i) {
        if (in_array($i, $read_allowed, true)) {
            $block['modules'][$i]['name'] = $modules[$i]->getVar('name');

            $block['modules'][$i]['directory'] = $modules[$i]->getVar('dirname');

            $sublinks = &$modules[$i]->subLink();

            if ((count($sublinks) > 0) && (!empty($xoopsModule)) && ($i == $xoopsModule->getVar('mid'))) {
                foreach ($sublinks as $sublink) {
                    $block['modules'][$i]['sublinks'][] = ['name' => $sublink['name'], 'url' => XOOPS_URL . '/modules/' . $modules[$i]->getVar('dirname') . '/' . $sublink['url']];
                }
            } else {
                $block['modules'][$i]['sublinks'] = [];
            }
        }
    }

    return $block;
}

function b_system_search_show()
{
    $block = [];

    $block['lang_search'] = _MB_SYSTEM_SEARCH;

    $block['lang_advsearch'] = _MB_SYSTEM_ADVS;

    return $block;
}

function b_system_user_show()
{
    global $xoopsUser, $isbb, $xoopsDB;

    if (is_object($xoopsUser)) {
        $pmHandler = xoops_getHandler('privmessage');

        $block = [];

        $block['lang_youraccount'] = _MB_SYSTEM_VACNT;

        $block['lang_editaccount'] = _MB_SYSTEM_EACNT;

        $block['lang_notifications'] = _MB_SYSTEM_NOTIF;

        $block['uid'] = $xoopsUser->getVar('uid');

        $block['lang_logout'] = _MB_SYSTEM_LOUT;

        $criteria = new CriteriaCompo(new Criteria('read_msg', 0));

        $criteria->add(new Criteria('to_userid', $xoopsUser->getVar('uid')));

        // IPB messages new

        if ($isbb) {
            [$new_messages] = $xoopsDB->fetchRow($xoopsDB->query('SELECT COUNT(*) FROM ' . $xoopsDB->prefix('ipb_messages') . " WHERE recipient_id = '" . $xoopsUser->getVar('uid') . "' AND vid='in' AND read_state='0' "));

            $block['new_messages'] = $new_messages;
        } else {
            $block['new_messages'] = $pmHandler->getCount($criteria);
        }

        $block['lang_inbox'] = _MB_SYSTEM_INBOX;

        if ($xoopsUser->isAdmin()) {
            $block['lang_adminmenu'] = _MB_SYSTEM_ADMENU;
        }

        return $block;
    }

    return false;
}

// this block is deprecated
function b_system_waiting_show()
{
    global $xoopsUser;

    $xoopsDB = XoopsDatabaseFactory::getDatabaseConnection();

    $moduleHandler = xoops_getHandler('module');

    $block = [];

    if ($moduleHandler->getCount(new Criteria('dirname', 'news'))) {
        $result = $xoopsDB->query('SELECT COUNT(*) FROM ' . $xoopsDB->prefix('stories') . ' WHERE published=0');

        if ($result) {
            $block['modules'][0]['adminlink'] = XOOPS_URL . '/modules/news/admin/index.php?op=newarticle';

            [$block['modules'][0]['pendingnum']] = $xoopsDB->fetchRow($result);

            $block['modules'][0]['lang_linkname'] = _MB_SYSTEM_SUBMS;
        }
    }

    if ($moduleHandler->getCount(new Criteria('dirname', 'mylinks'))) {
        $result = $xoopsDB->query('SELECT COUNT(*) FROM ' . $xoopsDB->prefix('mylinks_links') . ' WHERE status=0');

        if ($result) {
            $block['modules'][1]['adminlink'] = XOOPS_URL . '/modules/mylinks/admin/index.php?op=listNewLinks';

            [$block['modules'][1]['pendingnum']] = $xoopsDB->fetchRow($result);

            $block['modules'][1]['lang_linkname'] = _MB_SYSTEM_WLNKS;
        }

        $result = $xoopsDB->query('SELECT COUNT(*) FROM ' . $xoopsDB->prefix('mylinks_broken'));

        if ($result) {
            $block['modules'][2]['adminlink'] = XOOPS_URL . '/modules/mylinks/admin/index.php?op=listBrokenLinks';

            [$block['modules'][2]['pendingnum']] = $xoopsDB->fetchRow($result);

            $block['modules'][2]['lang_linkname'] = _MB_SYSTEM_BLNK;
        }

        $result = $xoopsDB->query('SELECT COUNT(*) FROM ' . $xoopsDB->prefix('mylinks_mod'));

        if ($result) {
            $block['modules'][3]['adminlink'] = XOOPS_URL . '/modules/mylinks/admin/index.php?op=listModReq';

            [$block['modules'][3]['pendingnum']] = $xoopsDB->fetchRow($result);

            $block['modules'][3]['lang_linkname'] = _MB_SYSTEM_MLNKS;
        }
    }

    if ($moduleHandler->getCount(new Criteria('dirname', 'mydownloads'))) {
        $result = $xoopsDB->query('SELECT COUNT(*) FROM ' . $xoopsDB->prefix('mydownloads_downloads') . ' WHERE status=0');

        if ($result) {
            $block['modules'][4]['adminlink'] = XOOPS_URL . '/modules/mydownloads/admin/index.php?op=listNewDownloads';

            [$block['modules'][4]['pendingnum']] = $xoopsDB->fetchRow($result);

            $block['modules'][4]['lang_linkname'] = _MB_SYSTEM_WDLS;
        }

        $result = $xoopsDB->query('SELECT COUNT(*) FROM ' . $xoopsDB->prefix('mydownloads_broken') . '');

        if ($result) {
            $block['modules'][5]['adminlink'] = XOOPS_URL . '/modules/mydownloads/admin/index.php?op=listBrokenDownloads';

            [$block['modules'][5]['pendingnum']] = $xoopsDB->fetchRow($result);

            $block['modules'][5]['lang_linkname'] = _MB_SYSTEM_BFLS;
        }

        $result = $xoopsDB->query('SELECT COUNT(*) FROM ' . $xoopsDB->prefix('mydownloads_mod') . '');

        if ($result) {
            $block['modules'][6]['adminlink'] = XOOPS_URL . '/modules/mydownloads/admin/index.php?op=listModReq';

            [$block['modules'][6]['pendingnum']] = $xoopsDB->fetchRow($result);

            $block['modules'][6]['lang_linkname'] = _MB_SYSTEM_MFLS;
        }
    }

    $result = $xoopsDB->query('SELECT COUNT(*) FROM ' . $xoopsDB->prefix('xoopscomments') . ' WHERE com_status=1');

    if ($result) {
        $block['modules'][7]['adminlink'] = XOOPS_URL . '/modules/system/admin.php?module=0&status=1&fct=comments';

        [$block['modules'][7]['pendingnum']] = $xoopsDB->fetchRow($result);

        $block['modules'][7]['lang_linkname'] = _MB_SYSTEM_COMPEND;
    }

    return $block;
}

function b_system_info_show($options)
{
    global $xoopsConfig, $xoopsUser;

    $xoopsDB = XoopsDatabaseFactory::getDatabaseConnection();

    $myts = MyTextSanitizer::getInstance();

    $block = [];

    if (!empty($options[3])) {
        $block['showgroups'] = true;

        $result = $xoopsDB->query(
            'SELECT u.uid, u.uname, u.email, u.user_viewemail, u.user_avatar, g.name AS groupname FROM '
            . $xoopsDB->prefix('groups_users_link')
            . ' l LEFT JOIN '
            . $xoopsDB->prefix('users')
            . ' u ON l.uid=u.uid LEFT JOIN '
            . $xoopsDB->prefix('groups')
            . " g ON l.groupid=g.groupid WHERE g.group_type='Admin' ORDER BY l.groupid"
        );

        if ($xoopsDB->getRowsNum($result) > 0) {
            $prev_caption = '';

            $i = 0;

            while (false !== ($userinfo = $xoopsDB->fetchArray($result))) {
                if ($prev_caption != $userinfo['groupname']) {
                    $prev_caption = $userinfo['groupname'];

                    $block['groups'][$i]['name'] = htmlspecialchars($userinfo['groupname'], ENT_QUOTES | ENT_HTML5);
                }

                // IPBM avatar hack

                $avatar = XOOPS_URL . '/uploads/' . $userinfo['user_avatar'];

                $avatar = str_replace(XOOPS_URL . '/uploads/http://', 'http://', $avatar);

                // END IPBM

                if ('' != $xoopsUser) {
                    $block['groups'][$i]['users'][] = [
                        'id' => $userinfo['uid'],
                        'name' => htmlspecialchars($userinfo['uname'], ENT_QUOTES | ENT_HTML5),
                        'msglink' => "<a href=\"javascript:openWithSelfMain('"
                                     . XOOPS_URL
                                     . '/pmlite.php?send2=1&amp;to_userid='
                                     . $userinfo['uid']
                                     . "','pmlite',450,370);\"><img src=\""
                                     . XOOPS_URL
                                     . '/images/icons/pm_small.gif" border="0" width="27" height="17" alt=""></a>',
                        'avatar' => $avatar,
                    ];
                } else {
                    if ($userinfo['user_viewemail']) {
                        $block['groups'][$i]['users'][] = [
                            'id' => $userinfo['uid'],
                            'name' => htmlspecialchars($userinfo['uname'], ENT_QUOTES | ENT_HTML5),
                            'msglink' => '<a href="mailto:' . $userinfo['email'] . '"><img src="' . XOOPS_URL . '/images/icons/em_small.gif" border="0" width="16" height="14" alt=""></a>',
                            'avatar' => $avatar,
                        ];
                    } else {
                        $block['groups'][$i]['users'][] = ['id' => $userinfo['uid'], 'name' => htmlspecialchars($userinfo['uname'], ENT_QUOTES | ENT_HTML5), 'msglink' => '&nbsp;', 'avatar' => $avatar];
                    }
                }

                $i++;
            }
        }
    } else {
        $block['showgroups'] = false;
    }

    $block['logourl'] = XOOPS_URL . '/images/' . $options[2];

    $block['recommendlink'] = "<a href=\"javascript:openWithSelfMain('" . XOOPS_URL . '/misc.php?action=showpopups&amp;type=friend&amp;op=sendform&amp;t=' . time() . "','friend'," . $options[0] . ',' . $options[1] . ')">' . _MB_SYSTEM_RECO . '</a>';

    return $block;
}

function b_system_newmembers_show($options)
{
    $block = [];

    $criteria = new CriteriaCompo(new Criteria('level', 0, '>'));

    $limit = (!empty($options[0])) ? $options[0] : 10;

    $criteria->setOrder('DESC');

    $criteria->setSort('user_regdate');

    $criteria->setLimit($limit);

    $memberHandler = xoops_getHandler('member');

    $newmembers = $memberHandler->getUsers($criteria);

    $count = count($newmembers);

    for ($i = 0; $i < $count; $i++) {
        // IPBM avatar hack

        $avatar = XOOPS_URL . '/uploads/' . $newmembers[$i]->getVar('user_avatar');

        $avatar = str_replace(XOOPS_URL . '/uploads/http://', 'http://', $avatar);

        // ENd

        if (1 == $options[1]) {
            $block['users'][$i]['avatar'] = 'blank.gif' != $newmembers[$i]->getVar('user_avatar') ? $avatar : '';
        } else {
            $block['users'][$i]['avatar'] = '';
        }

        $block['users'][$i]['id'] = $newmembers[$i]->getVar('uid');

        $block['users'][$i]['name'] = $newmembers[$i]->getVar('uname');

        $block['users'][$i]['joindate'] = formatTimestamp($newmembers[$i]->getVar('user_regdate'), 's');
    }

    return $block;
}

function b_system_topposters_show($options)
{
    $block = [];

    $criteria = new CriteriaCompo(new Criteria('level', 0, '>'));

    $limit = (!empty($options[0])) ? $options[0] : 10;

    $size = count($options);

    for ($i = 2; $i < $size; $i++) {
        $criteria->add(new Criteria('rank', $options[$i], '<>'));
    }

    $criteria->setOrder('DESC');

    $criteria->setSort('posts');

    $criteria->setLimit($limit);

    $memberHandler = xoops_getHandler('member');

    $topposters = $memberHandler->getUsers($criteria);

    $count = count($topposters);

    for ($i = 0; $i < $count; $i++) {
        $block['users'][$i]['rank'] = $i + 1;

        // IPBM avatar hack

        $avatar = XOOPS_URL . '/uploads/' . $topposters[$i]->getVar('user_avatar');

        $avatar = str_replace(XOOPS_URL . '/uploads/http://', 'http://', $avatar);

        // ENd

        if (1 == $options[1]) {
            $block['users'][$i]['avatar'] = 'blank.gif' != $topposters[$i]->getVar('user_avatar') ? $avatar : '';
        } else {
            $block['users'][$i]['avatar'] = '';
        }

        $block['users'][$i]['id'] = $topposters[$i]->getVar('uid');

        $block['users'][$i]['name'] = $topposters[$i]->getVar('uname');

        $block['users'][$i]['posts'] = $topposters[$i]->getVar('posts');
    }

    return $block;
}

function b_system_comments_show($options)
{
    $block = [];

    require_once XOOPS_ROOT_PATH . '/include/comment_constants.php';

    $commentHandler = xoops_getHandler('comment');

    $criteria = new CriteriaCompo(new Criteria('com_status', XOOPS_COMMENT_ACTIVE));

    $criteria->setLimit((int)$options[0]);

    $criteria->setSort('com_created');

    $criteria->setOrder('DESC');

    $comments = &$commentHandler->getObjects($criteria, true);

    $memberHandler = xoops_getHandler('member');

    $moduleHandler = xoops_getHandler('module');

    $modules = $moduleHandler->getObjects(new Criteria('hascomments', 1), true);

    $comment_config = [];

    foreach (array_keys($comments) as $i) {
        $mid = $comments[$i]->getVar('com_modid');

        $com['module'] = '<a href="' . XOOPS_URL . '/modules/' . $modules[$mid]->getVar('dirname') . '/">' . $modules[$mid]->getVar('name') . '</a>';

        if (!isset($comment_comfig[$mid])) {
            $comment_config[$mid] = $modules[$mid]->getInfo('comments');
        }

        $com['id'] = $i;

        $com['title'] = '<a href="' . XOOPS_URL . '/modules/' . $modules[$mid]->getVar('dirname') . '/' . $comment_config[$mid]['pageName'] . '?' . $comment_config[$mid]['itemName'] . '=' . $comments[$i]->getVar('com_itemid') . '&amp;com_id=' . $i . '&amp;com_rootid=' . $comments[$i]->getVar(
            'com_rootid'
        ) . '&amp;com_mode=thread&amp;' . $comments[$i]->getVar('com_exparams') . '#comment' . $i . '">' . $comments[$i]->getVar('com_title') . '</a>';

        $com['icon'] = $comments[$i]->getVar('com_icon');

        $com['icon'] = ('' != $com['icon']) ? $com['icon'] : 'icon1.gif';

        $com['time'] = formatTimestamp($comments[$i]->getVar('com_created'), 'm');

        if ($comments[$i]->getVar('com_uid') > 0) {
            $poster = $memberHandler->getUser($comments[$i]->getVar('com_uid'));

            if (is_object($poster)) {
                $com['poster'] = '<a href="' . XOOPS_URL . '/userinfo.php?uid=' . $comments[$i]->getVar('com_uid') . '">' . $poster->getVar('uname') . '</a>';
            } else {
                $com['poster'] = $GLOBALS['xoopsConfig']['anonymous'];
            }
        } else {
            $com['poster'] = $GLOBALS['xoopsConfig']['anonymous'];
        }

        $block['comments'][] = &$com;

        unset($com);
    }

    return $block;
}

// RMV-NOTIFY
function b_system_notification_show()
{
    global $xoopsConfig, $xoopsUser, $xoopsModule, $HTTP_SERVER_VARS;

    require_once XOOPS_ROOT_PATH . '/include/notification_functions.php';

    require_once XOOPS_ROOT_PATH . '/language/' . $xoopsConfig['language'] . '/notification.php';

    // Notification must be enabled, and user must be logged in

    if (empty($xoopsUser) || !notificationEnabled('block')) {
        return false; // do not display block
    }

    $notificationHandler = xoops_getHandler('notification');

    // Now build the a nested associative array of info to pass

    // to the block template.

    $block = [];

    $categories = &notificationSubscribableCategoryInfo();

    if (empty($categories)) {
        return false;
    }

    foreach ($categories as $category) {
        $section['name'] = $category['name'];

        $section['title'] = $category['title'];

        $section['description'] = $category['description'];

        $section['itemid'] = $category['item_id'];

        $section['events'] = [];

        $subscribed_events = $notificationHandler->getSubscribedEvents($category['name'], $category['item_id'], $xoopsModule->getVar('mid'), $xoopsUser->getVar('uid'));

        foreach (notificationEvents($category['name'], true) as $event) {
            if (!empty($event['admin_only']) && !$xoopsUser->isAdmin($xoopsModule->getVar('mid'))) {
                continue;
            }

            $subscribed = in_array($event['name'], $subscribed_events, true) ? 1 : 0;

            $section['events'][$event['name']] = ['name' => $event['name'], 'title' => $event['title'], 'caption' => $event['caption'], 'description' => $event['description'], 'subscribed' => $subscribed];
        }

        $block['categories'][$category['name']] = $section;
    }

    // Additional form data

    $block['target_page'] = 'notification_update.php';

    // FIXME: better or more standardized way to do this?

    $script_url = explode('/', $HTTP_SERVER_VARS['SCRIPT_NAME']);

    $script_name = $script_url[count($script_url) - 1];

    $block['redirect_script'] = $script_name;

    $block['submit_button'] = _NOT_UPDATENOW;

    return $block;
}

function b_system_comments_edit($options)
{
    $inputtag = "<input type='text' name='options[]' value='" . (int)$options[0] . "'>";

    $form = sprintf(_MB_SYSTEM_DISPLAYC, $inputtag);

    return $form;
}

function b_system_topposters_edit($options)
{
    require_once XOOPS_ROOT_PATH . '/class/xoopslists.php';

    $inputtag = "<input type='text' name='options[]' value='" . (int)$options[0] . "'>";

    $form = sprintf(_MB_SYSTEM_DISPLAY, $inputtag);

    $form .= '<br>' . _MB_SYSTEM_DISPLAYA . "&nbsp;<input type='radio' id='options[]' name='options[]' value='1'";

    if (1 == $options[1]) {
        $form .= ' checked';
    }

    $form .= '>&nbsp;' . _YES . "<input type='radio' id='options[]' name='options[]' value='0'";

    if (0 == $options[1]) {
        $form .= ' checked';
    }

    $form .= '>&nbsp;' . _NO . '';

    $form .= '<br>' . _MB_SYSTEM_NODISPGR . "<br><select id='options[]' name='options[]' multiple='multiple'>";

    $ranks = XoopsLists::getUserRankList();

    $size = count($options);

    foreach ($ranks as $k => $v) {
        $sel = '';

        for ($i = 2; $i < $size; $i++) {
            if ($k == $options[$i]) {
                $sel = " selected='selected'";
            }
        }

        $form .= "<option value='$k'$sel>$v</option>";
    }

    $form .= '</select>';

    return $form;
}

function b_system_newmembers_edit($options)
{
    $inputtag = "<input type='text' name='options[]' value='" . $options[0] . "'>";

    $form = sprintf(_MB_SYSTEM_DISPLAY, $inputtag);

    $form .= '<br>' . _MB_SYSTEM_DISPLAYA . "&nbsp;<input type='radio' id='options[]' name='options[]' value='1'";

    if (1 == $options[1]) {
        $form .= ' checked';
    }

    $form .= '>&nbsp;' . _YES . "<input type='radio' id='options[]' name='options[]' value='0'";

    if (0 == $options[1]) {
        $form .= ' checked';
    }

    $form .= '>&nbsp;' . _NO . '';

    return $form;
}

function b_system_info_edit($options)
{
    $form = _MB_SYSTEM_PWWIDTH . '&nbsp;';

    $form .= "<input type='text' name='options[]' value='" . $options[0] . "'>";

    $form .= '<br>' . _MB_SYSTEM_PWHEIGHT . '&nbsp;';

    $form .= "<input type='text' name='options[]' value='" . $options[1] . "'>";

    $form .= '<br>' . sprintf(_MB_SYSTEM_LOGO, XOOPS_URL . '/images/') . '&nbsp;';

    $form .= "<input type='text' name='options[]' value='" . $options[2] . "'>";

    $chk = '';

    $form .= '<br>' . _MB_SYSTEM_SADMIN . '&nbsp;';

    if (1 == $options[3]) {
        $chk = ' checked';
    }

    $form .= "<input type='radio' name='options[3]' value='1'" . $chk . '>&nbsp;' . _YES . '';

    $chk = '';

    if (0 == $options[3]) {
        $chk = ' checked="checked"';
    }

    $form .= "&nbsp;<input type='radio' name='options[3]' value='0'" . $chk . '>' . _NO . '';

    return $form;
}

function b_system_themes_show($options)
{
    global $xoopsConfig;

    $theme_options = '';

    foreach ($xoopsConfig['theme_set_allowed'] as $theme) {
        $theme_options .= '<option value="' . $theme . '"';

        if ($theme == $xoopsConfig['theme_set']) {
            $theme_options .= ' selected="selected"';
        }

        $theme_options .= '>' . $theme . '</option>';
    }

    $block = [];

    if (1 == $options[0]) {
        $block['theme_select'] = '<img vspace="2" id="xoops_theme_img" src="'
                                 . XOOPS_URL
                                 . '/themes/'
                                 . $xoopsConfig['theme_set']
                                 . '/shot.gif" alt="screenshot" width="'
                                 . (int)$options[1]
                                 . "\"><br><select id=\"xoops_theme_select\" name=\"xoops_theme_select\" onchange=\"showImgSelected('xoops_theme_img', 'xoops_theme_select', 'themes', '/shot.gif');\">"
                                 . $theme_options
                                 . '</select><input type="submit" value="'
                                 . _GO
                                 . '">';
    } else {
        $block['theme_select'] = '<select name="xoops_theme_select" onchange="submit();" size="3">' . $theme_options . '</select>';
    }

    $block['theme_select'] .= '<br>(' . sprintf(_MB_SYSTEM_NUMTHEME, '<b>' . count($xoopsConfig['theme_set_allowed']) . '</b>') . ')<br>';

    return $block;
}

function b_system_themes_edit($options)
{
    $chk = '';

    $form = _MB_SYSTEM_THSHOW . '&nbsp;';

    if (1 == $options[0]) {
        $chk = ' checked';
    }

    $form .= "<input type='radio' name='options[0]' value='1'" . $chk . '>&nbsp;' . _YES;

    $chk = '';

    if (0 == $options[0]) {
        $chk = ' checked';
    }

    $form .= '&nbsp;<input type="radio" name="options[0]" value="0"' . $chk . '>' . _NO;

    $form .= '<br>' . _MB_SYSTEM_THWIDTH . '&nbsp;';

    $form .= "<input type='text' name='options[1]' value='" . $options[1] . "'>";

    return $form;
}

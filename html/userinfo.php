<?php

// $Id: userinfo.php,v 1.10 2003/03/10 13:32:03 okazu Exp $
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

$xoopsOption['pagetype'] = 'user';
require __DIR__ . '/mainfile.php';
require_once XOOPS_ROOT_PATH . '/class/module.textsanitizer.php';

$uid = (int)$_GET['uid'];
if ($uid <= 0) {
    redirect_header('index.php', 3, _US_SELECTNG);

    exit();
}

if (is_object($xoopsUser)) {
    if ($uid == $xoopsUser->getVar('uid')) {
        $configHandler = xoops_getHandler('config');

        $xoopsConfigUser = $configHandler->getConfigsByCat(XOOPS_CONF_USER);

        $GLOBALS['xoopsOption']['template_main'] = 'system_userinfo.html';

        require XOOPS_ROOT_PATH . '/header.php';

        $xoopsTpl->assign('user_ownpage', true);

        $xoopsTpl->assign('lang_editprofile', _US_EDITPROFILE);

        $xoopsTpl->assign('lang_avatar', _US_AVATAR);

        $xoopsTpl->assign('lang_inbox', _US_INBOX);

        $xoopsTpl->assign('lang_logout', _US_LOGOUT);

        if (1 == $xoopsConfigUser['self_delete']) {
            $xoopsTpl->assign('user_candelete', true);

            $xoopsTpl->assign('lang_deleteaccount', _US_DELACCOUNT);
        } else {
            $xoopsTpl->assign('user_candelete', false);
        }

        $thisUser = &$xoopsUser;
    } else {
        $memberHandler = xoops_getHandler('member');

        $thisUser = $memberHandler->getUser($uid);

        if (!is_object($thisUser) || !$thisUser->isActive()) {
            redirect_header('index.php', 3, _US_SELECTNG);

            exit();
        }

        $GLOBALS['xoopsOption']['template_main'] = 'system_userinfo.html';

        require XOOPS_ROOT_PATH . '/header.php';

        $xoopsTpl->assign('user_ownpage', false);
    }
} else {
    $memberHandler = xoops_getHandler('member');

    $thisUser = $memberHandler->getUser($uid);

    if (!is_object($thisUser) || !$thisUser->isActive()) {
        redirect_header('index.php', 3, _US_SELECTNG);

        exit();
    }

    $GLOBALS['xoopsOption']['template_main'] = 'system_userinfo.html';

    require XOOPS_ROOT_PATH . '/header.php';

    $xoopsTpl->assign('user_ownpage', false);
}
$myts = MyTextSanitizer::getInstance();
if (is_object($xoopsUser) && $xoopsUser->isAdmin()) {
    $xoopsTpl->assign('lang_editprofile', _US_EDITPROFILE);

    $xoopsTpl->assign('lang_deleteaccount', _US_DELACCOUNT);

    $xoopsTpl->assign('user_uid', $thisUser->getVar('uid'));
}
$xoopsTpl->assign('lang_allaboutuser', sprintf(_US_ALLABOUT, $thisUser->getVar('uname')));
$xoopsTpl->assign('lang_avatar', _US_AVATAR);

if (false !== strpos($thisUser->getVar('user_avatar', 'E'), "http")) {
    $xoopsTpl->assign('user_avatarurl', $thisUser->getVar('user_avatar'));
} else {
    $xoopsTpl->assign('user_avatarurl', 'uploads/' . $thisUser->getVar('user_avatar'));
}

$xoopsTpl->assign('lang_realname', _US_REALNAME);
$xoopsTpl->assign('user_realname', $thisUser->getVar('name'));
$xoopsTpl->assign('lang_website', _US_WEBSITE);
$xoopsTpl->assign('user_websiteurl', '<a href="' . $thisUser->getVar('url', 'E') . '" target="_blank">' . $thisUser->getVar('url') . '</a>');
$xoopsTpl->assign('lang_email', _US_EMAIL);
$xoopsTpl->assign('lang_privmsg', _US_PM);
$xoopsTpl->assign('lang_icq', _US_ICQ);
$xoopsTpl->assign('user_icq', $thisUser->getVar('user_icq'));
$xoopsTpl->assign('lang_aim', _US_AIM);
$xoopsTpl->assign('user_aim', $thisUser->getVar('user_aim'));
$xoopsTpl->assign('lang_yim', _US_YIM);
$xoopsTpl->assign('user_yim', $thisUser->getVar('user_yim'));
$xoopsTpl->assign('lang_msnm', _US_MSNM);
$xoopsTpl->assign('user_msnm', $thisUser->getVar('user_msnm'));
$xoopsTpl->assign('lang_location', _US_LOCATION);
$xoopsTpl->assign('user_location', $thisUser->getVar('user_from'));
$xoopsTpl->assign('lang_occupation', _US_OCCUPATION);
$xoopsTpl->assign('user_occupation', $thisUser->getVar('user_occ'));
$xoopsTpl->assign('lang_interest', _US_INTEREST);
$xoopsTpl->assign('user_interest', $thisUser->getVar('user_intrest'));
$xoopsTpl->assign('lang_extrainfo', _US_EXTRAINFO);
$xoopsTpl->assign('user_extrainfo', $myts->displayTarea($thisUser->getVar('bio', 'N'), 0, 1, 1));
$xoopsTpl->assign('lang_statistics', _US_STATISTICS);
$xoopsTpl->assign('lang_membersince', _US_MEMBERSINCE);
$xoopsTpl->assign('user_joindate', formatTimestamp($thisUser->getVar('user_regdate'), 's'));
$xoopsTpl->assign('lang_rank', _US_RANK);
$xoopsTpl->assign('lang_posts', _US_POSTS);
$xoopsTpl->assign('lang_basicInfo', _US_BASICINFO);
$xoopsTpl->assign('lang_more', _US_MOREABOUT);
$xoopsTpl->assign('lang_myinfo', _US_MYINFO);
$xoopsTpl->assign('user_posts', $thisUser->getVar('posts'));
$xoopsTpl->assign('lang_lastlogin', _US_LASTLOGIN);
$xoopsTpl->assign('lang_notregistered', _US_NOTREGISTERED);

$xoopsTpl->assign('lang_signature', _US_SIGNATURE);
$xoopsTpl->assign('user_signature', $myts->displayTarea($thisUser->getVar('user_sig', 'N'), 0, 1, 1));

if (1 == $thisUser->getVar('user_viewemail')) {
    $xoopsTpl->assign('user_email', $thisUser->getVar('email', 'E'));
} else {
    if (is_object($xoopsUser)) {
        if ($xoopsUser->isAdmin() || ($xoopsUser->getVar('uid') == $thisUser->getVar('uid'))) {
            $xoopsTpl->assign('user_email', $thisUser->getVar('email', 'E'));
        } else {
            $xoopsTpl->assign('user_email', '&nbsp;');
        }
    }
}
if (is_object($xoopsUser)) {
    if ($isbb) {
        $xoopsTpl->assign('user_pmlink', "<a href='" . XOOPS_URL . "/modules/ipboard/index.php?s=$sid_bb&act=Msg&CODE=4&MID=" . $thisUser->getVar('uid') . "'><img src=\"" . XOOPS_URL . '/images/icons/pm.gif" alt="' . sprintf(_SENDPMTO, $thisUser->getVar('uname')) . '"></a>');
    } else {
        $xoopsTpl->assign(
            'user_pmlink',
            "<a href=\"javascript:openWithSelfMain('" . XOOPS_URL . '/pmlite.php?send2=1&amp;to_userid=' . $thisUser->getVar('uid') . "', 'pmlite', 450, 380);\"><img src=\"" . XOOPS_URL . '/images/icons/pm.gif" alt="' . sprintf(_SENDPMTO, $thisUser->getVar('uname')) . '"></a>'
        );
    }
} else {
    $xoopsTpl->assign('user_pmlink', '');
}
$userrank = &$thisUser->rank();
if ($userrank['image']) {
    $xoopsTpl->assign('user_rankimage', '<img src="' . XOOPS_URL . '/uploads/' . $userrank['image'] . '" alt="">');
}
$xoopsTpl->assign('user_ranktitle', $userrank['title']);
$date = $thisUser->getVar('last_login');
if (!empty($date)) {
    $xoopsTpl->assign('user_lastlogin', formatTimestamp($date, 'm'));
}

$moduleHandler = xoops_getHandler('module');
$criteria = new CriteriaCompo(new Criteria('hassearch', 1));
$criteria->add(new Criteria('isactive', 1));
$mids = array_keys($moduleHandler->getList($criteria));
foreach ($mids as $mid) {
    $module = $moduleHandler->get($mid);

    $results = $module->search('', '', 5, 0, $thisUser->getVar('uid'));

    $count = count($results);

    if (is_array($results) && $count > 0) {
        for ($i = 0; $i < $count; $i++) {
            if (isset($results[$i]['image']) && '' != $results[$i]['image']) {
                $results[$i]['image'] = 'modules/' . $module->getVar('dirname') . '/' . $results[$i]['image'];
            } else {
                $results[$i]['image'] = 'images/icons/posticon2.gif';
            }

            $results[$i]['link'] = 'modules/' . $module->getVar('dirname') . '/' . $results[$i]['link'];

            $results[$i]['title'] = htmlspecialchars($results[$i]['title'], ENT_QUOTES | ENT_HTML5);

            $results[$i]['time'] = $results[$i]['time'] ? formatTimestamp($results[$i]['time']) : '';
        }

        if (5 == $count) {
            $showall_link = '<form action="search.php" method="post" id="xoopsprofile'
                            . $mid
                            . '" name="xoopsprofile'
                            . $mid
                            . '"><input type="hidden" value="" name="queries"><input type="hidden" name="uid" value="'
                            . $thisUser->getVar('uid')
                            . '"><input type="hidden" name="mid" value="'
                            . $mid
                            . '"><input type="hidden" name="action" value="showallbyuser"><a href="#'
                            . $mid
                            . '" onclick="xoopsGetElementById(\'xoopsprofile'
                            . $mid
                            . '\').submit();return false;">'
                            . _US_SHOWALL
                            . '</a></form>';
        } else {
            $showall_link = '';
        }

        $xoopsTpl->append('modules', ['name' => $module->getVar('name'), 'results' => $results, 'showall_link' => $showall_link]);
    }

    unset($module);
}
require XOOPS_ROOT_PATH . '/footer.php';

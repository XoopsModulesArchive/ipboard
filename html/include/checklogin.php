<?php

// $Id: checklogin.php,v 1.10 2003/04/11 15:19:59 okazu Exp $
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
// URL: https://www.xoops.org/ http://jp.xoops.org/  http://www.myweb.ne.jp/  //
// Project: The XOOPS Project (https://www.xoops.org/)                        //
// ------------------------------------------------------------------------- //

if (!defined('XOOPS_ROOT_PATH')) {
    exit();
}
require_once XOOPS_ROOT_PATH . '/language/' . $xoopsConfig['language'] . '/user.php';

$memberHandler = xoops_getHandler('member');
$myts = MyTextSanitizer::getInstance();

// IPB compatibility
if (isset($_POST['uname']) && isset($_POST['pass'])) {
    $uname = trim($_POST['uname']);

    $pass = trim($_POST['pass']);

    $user = $memberHandler->loginUser(addslashes($myts->stripSlashesGPC($uname)), addslashes(md5($myts->stripSlashesGPC($pass))));
} else {
    $uname = trim($_GET['uname']);

    $pass = trim($_GET['pass']);

    $user = $memberHandler->loginUser(addslashes($myts->stripSlashesGPC($uname)), addslashes($myts->stripSlashesGPC($pass)));
}

if ('' == $uname || '' == $pass) {
    redirect_header(XOOPS_URL . '/user.php', 1, _US_INCORRECTLOGIN);

    exit();
}
if (false !== $user) {
    if (0 == $user->getVar('level')) {
        redirect_header(XOOPS_URL . '/index.php', 5, _US_NOACTTPADM);

        exit();
    }

    if (1 == $xoopsConfig['closesite']) {
        $allowed = false;

        foreach ($user->getGroups() as $group) {
            if (in_array($group, $xoopsConfig['closesite_okgrp'], true) || XOOPS_GROUP_ADMIN == $group) {
                $allowed = true;

                break;
            }
        }

        if (!$allowed) {
            redirect_header(XOOPS_URL . '/index.php', 1, _NOPERM);

            exit();
        }
    }

    $user->setVar('last_login', time());

    if (!$memberHandler->insertUser($user)) {
    }

    $HTTP_SESSION_VARS = [];

    $HTTP_SESSION_VARS['xoopsUserId'] = $user->getVar('uid');

    $HTTP_SESSION_VARS['xoopsUserGroups'] = $user->getGroups();

    if ($xoopsConfig['use_mysession'] && '' != $xoopsConfig['session_name']) {
        setcookie($xoopsConfig['session_name'], session_id(), time() + $xoopsConfig['session_expire'], '/', '', 0);
    }

    $user_theme = $user->getVar('theme');

    if (in_array($user_theme, $xoopsConfig['theme_set_allowed'], true)) {
        $HTTP_SESSION_VARS['xoopsUserTheme'] = $user_theme;
    }

    if (isset($_POST['xoops_redirect'])) {
        $parsed = parse_url(XOOPS_URL);

        $url = isset($parsed['scheme']) ? $parsed['scheme'] . '://' : 'http://';

        if (isset($parsed['host'])) {
            $url .= isset($parsed['port']) ? $parsed['host'] . ':' . $parsed['port'] . trim($_POST['xoops_redirect']) : $parsed['host'] . trim($_POST['xoops_redirect']);
        } else {
            $url = xoops_getenv('HTTP_HOST') . trim($_POST['xoops_redirect']);
        }
    } else {
        $url = XOOPS_URL . '/index.php';
    }

    //IPBM AUTO REDIRECT

    if (false !== strpos($HTTP_SERVER_VARS['HTTP_REFERER'], "ipboard")) {
        $url = $HTTP_SERVER_VARS['HTTP_REFERER'];
    }

    //IPBM AUTO REDIRECT END

    // RMV-NOTIFY

    // Perform some maintenance of notification records

    $notificationHandler = xoops_getHandler('notification');

    $notificationHandler->doLoginMaintenance($user->getVar('uid'));

    redirect_header($url, 1, sprintf(_US_LOGGINGU, $user->getVar('uname')));
} else {
    redirect_header(XOOPS_URL . '/user.php', 1, _US_INCORRECTLOGIN);
}
exit();

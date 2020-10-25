<?php

/*
/------------------------------------\
| Modified by Koudanshi
| Count numbers: 1
| Date: 02-June-2003
| Time: 3:45
\------------------------------------/
*/

// $Id: common.php,v 1.22 2003/04/17 09:15:52 okazu Exp $
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

if (!defined('XOOPS_MAINFILE_INCLUDED')) {
    exit();
}
    define('XOOPS_SIDEBLOCK_LEFT', 0);
    define('XOOPS_SIDEBLOCK_RIGHT', 1);
    define('XOOPS_SIDEBLOCK_BOTH', 2);
    define('XOOPS_CENTERBLOCK_LEFT', 3);
    define('XOOPS_CENTERBLOCK_RIGHT', 4);
    define('XOOPS_CENTERBLOCK_CENTER', 5);
    define('XOOPS_CENTERBLOCK_ALL', 6);
    define('XOOPS_BLOCK_INVISIBLE', 0);
    define('XOOPS_BLOCK_VISIBLE', 1);
    define('XOOPS_MATCH_START', 0);
    define('XOOPS_MATCH_END', 1);
    define('XOOPS_MATCH_EQUAL', 2);
    define('XOOPS_MATCH_CONTAIN', 3);
    define('SMARTY_DIR', XOOPS_ROOT_PATH . '/class/smarty/');
    set_magic_quotes_runtime(0);
    require_once XOOPS_ROOT_PATH . '/class/logger.php';
    $xoopsLogger = XoopsLogger::instance();
    $xoopsLogger->startTime();
    if (!defined('XOOPS_XMLRPC')) {
        define('XOOPS_DB_CHKREF', 1);
    } else {
        define('XOOPS_DB_CHKREF', 0);
    }

    // ############## Include common functions file ##############
    require_once XOOPS_ROOT_PATH . '/include/functions.php';

    // #################### Connect to DB ##################
    if (defined('XOOPS_DB_PREFIX') && defined('XOOPS_DB_HOST') && defined('XOOPS_DB_USER') && defined('XOOPS_DB_PASS') && defined('XOOPS_DB_NAME')) {
        require_once XOOPS_ROOT_PATH . '/class/database/databasefactory.php';

        if ('POST' != $HTTP_SERVER_VARS['REQUEST_METHOD'] || !XoopsSecurity::checkReferer(XOOPS_DB_CHKREF)) {
            define('XOOPS_DB_PROXY', 1);
        }

        $xoopsDB = &XoopsDatabaseFactory::get();
    }

    // ################# Include required files ##############
    require_once XOOPS_ROOT_PATH . '/kernel/object.php';
    //require_once XOOPS_ROOT_PATH.'/kernel/managerfactory.php';
    require_once XOOPS_ROOT_PATH . '/kernel/handlerregistry.php';
    require_once XOOPS_ROOT_PATH . '/class/criteria.php';

    //$xoopsMF =& XoopsManagerFactory::getInstance();

    // #################### Include text sanitizer ##################
    require_once XOOPS_ROOT_PATH . '/class/module.textsanitizer.php';

    // ################# Load Config Settings ##############
    $configHandler = xoops_getHandler('config');
    $xoopsConfig = $configHandler->getConfigsByCat(XOOPS_CONF);

    // #################### Error reporting settings ##################
    error_reporting(0);

    if (1 == $xoopsConfig['debug_mode']) {
        error_reporting(E_ALL);
    }

    if (1 == $xoopsConfig['enable_badips'] && isset($HTTP_SERVER_VARS['REMOTE_ADDR']) && '' != $HTTP_SERVER_VARS['REMOTE_ADDR']) {
        foreach ($xoopsConfig['bad_ips'] as $bi) {
            if (!empty($bi) && preg_match('/' . $bi . '/', $HTTP_SERVER_VARS['REMOTE_ADDR'])) {
                exit();
            }
        }
    }
    unset($bi);
    unset($bad_ips);
    unset($xoopsConfig['badips']);

    // ################# Include version info file ##############
    require_once XOOPS_ROOT_PATH . '/include/version.php';

    // for older versions...will be DEPRECATED!
    $xoopsConfig['xoops_url'] = XOOPS_URL;
    $xoopsConfig['root_path'] = XOOPS_ROOT_PATH . '/';

    // #################### Include site-wide lang file ##################
    if (file_exists(XOOPS_ROOT_PATH . '/language/' . $xoopsConfig['language'] . '/global.php')) {
        require_once XOOPS_ROOT_PATH . '/language/' . $xoopsConfig['language'] . '/global.php';
    } else {
        require_once XOOPS_ROOT_PATH . '/language/english/global.php';
    }

    // ################ Include page-specific lang file ################
    if (isset($xoopsOption['pagetype'])) {
        if (file_exists(XOOPS_ROOT_PATH . '/language/' . $xoopsConfig['language'] . '/' . $xoopsOption['pagetype'] . '.php')) {
            require_once XOOPS_ROOT_PATH . '/language/' . $xoopsConfig['language'] . '/' . $xoopsOption['pagetype'] . '.php';
        } else {
            require_once XOOPS_ROOT_PATH . '/language/english/' . $xoopsOption['pagetype'] . '.php';
        }
    }

    if (!defined('XOOPS_USE_MULTIBYTES')) {
        define('XOOPS_USE_MULTIBYTES', 0);
    }

    // ############## Login a user with a valid session ##############
    $xoopsUser = '';
    $memberHandler = xoops_getHandler('member');
    $sessHandler = xoops_getHandler('session');
    if ($xoopsConfig['use_ssl'] && isset($_POST[$xoopsConfig['sslpost_name']]) && '' != $_POST[$xoopsConfig['sslpost_name']]) {
        session_id($_POST[$xoopsConfig['sslpost_name']]);
    } elseif ($xoopsConfig['use_mysession'] && '' != $xoopsConfig['session_name']) {
        if (isset($HTTP_COOKIE_VARS[$xoopsConfig['session_name']])) {
            session_id($HTTP_COOKIE_VARS[$xoopsConfig['session_name']]);
        } else {
            // no custom session cookie set, destroy session if any

            $HTTP_SESSION_VARS = [];

            session_destroy();
        }

        if (function_exists('session_cache_expire')) {
            session_cache_expire($xoopsConfig['session_expire']);
        }
    }
    session_set_saveHandler([&$sessHandler, 'open'], [&$sessHandler, 'close'], [&$sessHandler, 'read'], [&$sessHandler, 'write'], [&$sessHandler, 'destroy'], [&$sessHandler, 'gc']);
    session_start();
    if (!empty($HTTP_SESSION_VARS['xoopsUserId'])) {
        $xoopsUser = $memberHandler->getUser($HTTP_SESSION_VARS['xoopsUserId']);

        if (!is_object($xoopsUser)) {
            $xoopsUser = '';

            $HTTP_SESSION_VARS = [];

            session_destroy();
        } else {
            if ($xoopsConfig['use_mysession'] && '' != $xoopsConfig['session_name']) {
                setcookie($xoopsConfig['session_name'], session_id(), time() + (60 * $xoopsConfig['session_expire']), '/', '', 0);
            }

            $xoopsUser->setGroups($HTTP_SESSION_VARS['xoopsUserGroups']);
        }
    }
    if (isset($_POST['xoops_theme_select']) && in_array($_POST['xoops_theme_select'], $xoopsConfig['theme_set_allowed'], true)) {
        $xoopsConfig['theme_set'] = $_POST['xoops_theme_select'];

        $HTTP_SESSION_VARS['xoopsUserTheme'] = $_POST['xoops_theme_select'];
    } elseif (isset($HTTP_SESSION_VARS['xoopsUserTheme']) && in_array($HTTP_SESSION_VARS['xoopsUserTheme'], $xoopsConfig['theme_set_allowed'], true)) {
        $xoopsConfig['theme_set'] = $HTTP_SESSION_VARS['xoopsUserTheme'];
    }

    if (1 == $xoopsConfig['closesite']) {
        $allowed = false;

        if (is_object($xoopsUser)) {
            foreach ($xoopsUser->getGroups() as $group) {
                if (in_array($group, $xoopsConfig['closesite_okgrp'], true) || XOOPS_GROUP_ADMIN == $group) {
                    $allowed = true;

                    break;
                }
            }
        } elseif (!empty($_POST['xoops_login'])) {
            require_once XOOPS_ROOT_PATH . '/include/checklogin.php';

            exit();
        }

        if (!$allowed) {
            require_once XOOPS_ROOT_PATH . '/class/template.php';

            $xoopsTpl = new XoopsTpl();

            $xoopsTpl->assign(
                [
                    'sitename' => $xoopsConfig['sitename'],
'xoops_themecss' => xoops_getcss(),
'xoops_imageurl' => XOOPS_URL . '/themes/' . $xoopsConfig['theme_set'] . '/',
'lang_login' => _LOGIN,
'lang_username' => _USERNAME,
'lang_password' => _PASSWORD,
'lang_siteclosemsg' => $xoopsConfig['closesite_text'],
                ]
            );

            $xoopsTpl->xoops_setCaching(1);

            $xoopsTpl->display('db:system_siteclosed.html');

            exit();
        }

        unset($allowed, $group);
    }

    $xoopsRequestUri = @xoops_getenv('REQUEST_URI');
    if (!$xoopsRequestUri) {
        $xoopsRequestUri = !xoops_getenv('SCRIPT_NAME') ? getenv('REQUEST_URI') : xoops_getenv('SCRIPT_NAME');
    }
    if (file_exists('./xoops_version.php')) {
        $url_arr = explode('/', str_replace(str_replace('https://', 'http://', XOOPS_URL . '/modules/'), '', 'http://' . $HTTP_SERVER_VARS['HTTP_HOST'] . $xoopsRequestUri));

        $moduleHandler = xoops_getHandler('module');

        $xoopsModule = $moduleHandler->getByDirname($url_arr[0]);

        unset($url_arr);

        if (!$xoopsModule || !$xoopsModule->getVar('isactive')) {
            require_once XOOPS_ROOT_PATH . '/header.php';

            echo '<h4>' . _MODULENOEXIST . '</h4>';

            require_once XOOPS_ROOT_PATH . '/footer.php';

            exit();
        }

        $modulepermHandler = xoops_getHandler('groupperm');

        if ($xoopsUser) {
            if (!$modulepermHandler->checkRight('module_read', $xoopsModule->getVar('mid'), $xoopsUser->getGroups())) {
                redirect_header(XOOPS_URL . '/user.php', 1, _NOPERM);

                exit();
            }
        } else {
            if (!$modulepermHandler->checkRight('module_read', $xoopsModule->getVar('mid'), XOOPS_GROUP_ANONYMOUS)) {
                redirect_header(XOOPS_URL . '/user.php', 1, _NOPERM);

                exit();
            }
        }

        if (file_exists(XOOPS_ROOT_PATH . '/modules/' . $xoopsModule->getVar('dirname') . '/language/' . $xoopsConfig['language'] . '/main.php')) {
            require_once XOOPS_ROOT_PATH . '/modules/' . $xoopsModule->getVar('dirname') . '/language/' . $xoopsConfig['language'] . '/main.php';
        } else {
            if (file_exists(XOOPS_ROOT_PATH . '/modules/' . $xoopsModule->getVar('dirname') . '/language/english/main.php')) {
                require_once XOOPS_ROOT_PATH . '/modules/' . $xoopsModule->getVar('dirname') . '/language/english/main.php';
            }
        }

        if (1 == $xoopsModule->getVar('hasconfig') || 1 == $xoopsModule->getVar('hascomments') || 1 == $xoopsModule->getVar('hasnotification')) {
            $xoopsModuleConfig = $configHandler->getConfigsByCat(0, $xoopsModule->getVar('mid'));
        }
    }
    //IPB exists + session_id + uid
    $sql = 'SELECT mid FROM ' . $xoopsDB->prefix('modules') . " WHERE dirname='ipboard' AND isactive='1'";
    $ismodule = $xoopsDB->fetchArray($xoopsDB->query($sql));
    if ($ismodule['mid']) {
        $isbb = 1;
    } else {
        $isbb = 0;
    }
    if ($xoopsUser) {
        $uid_bb = $xoopsUser->getVar('uid');
    } else {
        $uid_bb = 0;
    }
    $meminfo = $xoopsDB->fetchArray($xoopsDB->query('SELECT * FROM ' . $xoopsDB->prefix('users') . " WHERE uid = $uid_bb"));
    $sid_bb = session_id();

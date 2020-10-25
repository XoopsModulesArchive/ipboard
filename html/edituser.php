<?php

// $Id: edituser.php,v 1.14 2003/04/13 01:48:34 okazu Exp $
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
require_once XOOPS_ROOT_PATH . '/class/xoopsformloader.php';

// If not a user, redirect
if (!$xoopsUser) {
    redirect_header('index.php', 3, _US_NOEDITRIGHT);

    exit();
}

// initialize $op variable
$op = 'editprofile';

if (isset($_POST)) {
    foreach ($_POST as $k => $v) {
        ${$k} = $v;
    }
}
if (isset($_GET['op'])) {
    $op = $_GET['op'];
}
$configHandler = xoops_getHandler('config');
$xoopsConfigUser = $configHandler->getConfigsByCat(XOOPS_CONF_USER);

if ('saveuser' == $op) {
    $uid = (int)$uid;

    if (empty($uid) || $xoopsUser->getVar('uid') != $uid) {
        redirect_header('index.php', 3, _US_NOEDITRIGHT);

        exit();
    }

    $errors = [];

    $myts = MyTextSanitizer::getInstance();

    if (1 == $xoopsConfigUser['allow_chgmail']) {
        $email = $myts->stripSlashesGPC(trim($email));

        if (!isset($email) || '' == $email || !checkEmail($email)) {
            $errors[] = _US_INVALIDMAIL;
        }
    }

    if (isset($pass)) {
        $pass = trim($pass);
    }

    if (isset($vpass)) {
        $vpass = trim($vpass);
    }

    if ((isset($pass)) && ($pass != $vpass)) {
        $errors[] = _US_PASSNOTSAME;
    } elseif (('' != $pass) && (mb_strlen($pass) < $xoopsConfigUser['minpass'])) {
        $errors[] = printf(_US_PWDTOOSHORT, $xoopsConfigUser['minpass']);
    }

    if (count($errors) > 0) {
        require XOOPS_ROOT_PATH . '/header.php';

        echo '<div>';

        foreach ($errors as $er) {
            echo '<span style="color: #ff0000; font-weight: bold;">' . $er . '</span><br>';
        }

        echo '</div><br>';

        $op = 'editprofile';
    } else {
        $memberHandler = xoops_getHandler('member');

        $edituser = $memberHandler->getUser($uid);

        $edituser->setVar('name', $name);

        if (1 == $xoopsConfigUser['allow_chgmail']) {
            $edituser->setVar('email', $email);
        }

        $edituser->setVar('url', formatURL($url));

        $edituser->setVar('user_icq', $user_icq);

        $edituser->setVar('user_from', $user_from);

        $edituser->setVar('user_sig', $user_sig);

        $user_viewemail = (!empty($user_viewemail)) ? 1 : 0;

        $edituser->setVar('user_viewemail', $user_viewemail);

        $edituser->setVar('user_aim', $user_aim);

        $edituser->setVar('user_yim', $user_yim);

        $edituser->setVar('user_msnm', $user_msnm);

        if (isset($pass) && '' != $pass) {
            $edituser->setVar('pass', md5($pass));
        }

        $attachsig = isset($attachsig) ? (int)$attachsig : 0;

        $edituser->setVar('attachsig', $attachsig);

        $edituser->setVar('timezone_offset', $timezone_offset);

        $edituser->setVar('uorder', $uorder);

        $edituser->setVar('umode', $umode);

        $edituser->setVar('notify_method', $notify_method);

        $edituser->setVar('notify_mode', $notify_mode);

        $edituser->setVar('bio', $bio);

        $edituser->setVar('user_occ', $user_occ);

        $edituser->setVar('user_intrest', $user_intrest);

        $edituser->setVar('user_mailok', $user_mailok);

        if ($usecookie) {
            setcookie($xoopsConfig['usercookie'], $xoopsUser->getVar('uname'), time() + 31536000);
        } else {
            setcookie($xoopsConfig['usercookie']);
        }

        if (!$memberHandler->insertUser($edituser)) {
            require XOOPS_ROOT_PATH . '/header.php';

            echo $edituser->getHtmlErrors();

            require XOOPS_ROOT_PATH . '/footer.php';
        } else {
            redirect_header('userinfo.php?uid=' . $uid, 1, _US_PROFUPDATED);
        }

        exit();
    }
}

if ('editprofile' == $op) {
    require XOOPS_ROOT_PATH . '/header.php';

    require XOOPS_ROOT_PATH . '/include/comment_constants.php';

    echo '<a href="userinfo.php?uid=' . $xoopsUser->getVar('uid') . '">' . _US_PROFILE . '</a>&nbsp;<span style="font-weight:bold;">&raquo;&raquo;</span>&nbsp;' . _US_EDITPROFILE . '<br><br>';

    $form = new XoopsThemeForm(_US_EDITPROFILE, 'userinfo', 'edituser.php');

    $uname_label = new XoopsFormLabel(_US_NICKNAME, $xoopsUser->getVar('uname'));

    $form->addElement($uname_label);

    $name_text = new XoopsFormText(_US_REALNAME, 'name', 30, 60, $xoopsUser->getVar('name', 'E'));

    $form->addElement($name_text);

    $email_tray = new XoopsFormElementTray(_US_EMAIL, '<br>');

    if (1 == $xoopsConfigUser['allow_chgmail']) {
        $email_text = new XoopsFormText('', 'email', 30, 60, $xoopsUser->getVar('email'));
    } else {
        $email_text = new XoopsFormLabel('', $xoopsUser->getVar('email'));
    }

    $email_tray->addElement($email_text);

    $email_cbox_value = $xoopsUser->user_viewemail() ? 1 : 0;

    $email_cbox = new XoopsFormCheckBox('', 'user_viewemail', $email_cbox_value);

    $email_cbox->addOption(1, _US_ALLOWVIEWEMAIL);

    $email_tray->addElement($email_cbox);

    $form->addElement($email_tray);

    $url_text = new XoopsFormText(_US_WEBSITE, 'url', 30, 100, $xoopsUser->getVar('url', 'E'));

    $form->addElement($url_text);

    $timezone_select = new XoopsFormSelectTimezone(_US_TIMEZONE, 'timezone_offset', $xoopsUser->getVar('timezone_offset'));

    $icq_text = new XoopsFormText(_US_ICQ, 'user_icq', 30, 100, $xoopsUser->getVar('user_icq', 'E'));

    $aim_text = new XoopsFormText(_US_AIM, 'user_aim', 30, 100, $xoopsUser->getVar('user_aim', 'E'));

    $yim_text = new XoopsFormText(_US_YIM, 'user_yim', 30, 100, $xoopsUser->getVar('user_yim', 'E'));

    $msnm_text = new XoopsFormText(_US_MSNM, 'user_msnm', 30, 100, $xoopsUser->getVar('user_msnm', 'E'));

    $location_text = new XoopsFormText(_US_LOCATION, 'user_from', 30, 100, $xoopsUser->getVar('user_from', 'E'));

    $occupation_text = new XoopsFormText(_US_OCCUPATION, 'user_occ', 30, 100, $xoopsUser->getVar('user_occ', 'E'));

    $interest_text = new XoopsFormText(_US_INTEREST, 'user_intrest', 30, 100, $xoopsUser->getVar('user_intrest', 'E'));

    $sig_tray = new XoopsFormElementTray(_US_SIGNATURE, '<br>');

    require_once __DIR__ . '/include/xoopscodes.php';

    $sig_tarea = new XoopsFormDhtmlTextArea('', 'user_sig', $xoopsUser->getVar('user_sig', 'E'));

    $sig_tray->addElement($sig_tarea);

    $sig_cbox_value = $xoopsUser->getVar('attachsig') ? 1 : 0;

    $sig_cbox = new XoopsFormCheckBox('', 'attachsig', $sig_cbox_value);

    $sig_cbox->addOption(1, _US_SHOWSIG);

    $sig_tray->addElement($sig_cbox);

    $umode_select = new XoopsFormSelect(_US_CDISPLAYMODE, 'umode', $xoopsUser->getVar('umode'));

    $umode_select->addOptionArray(['nest' => _NESTED, 'flat' => _FLAT, 'thread' => _THREADED]);

    $uorder_select = new XoopsFormSelect(_US_CSORTORDER, 'uorder', $xoopsUser->getVar('uorder'));

    $uorder_select->addOptionArray([XOOPS_COMMENT_OLD1ST => _OLDESTFIRST, XOOPS_COMMENT_NEW1ST => _NEWESTFIRST]);

    // RMV-NOTIFY

    // TODO: add this to admin user-edit functions...

    require_once XOOPS_ROOT_PATH . '/language/' . $xoopsConfig['language'] . '/notification.php';

    require_once XOOPS_ROOT_PATH . '/include/notification_constants.php';

    $notify_method_select = new XoopsFormSelect(_NOT_NOTIFYMETHOD, 'notify_method', $xoopsUser->getVar('notify_method'));

    $notify_method_select->addOptionArray([XOOPS_NOTIFICATION_METHOD_DISABLE => _NOT_METHOD_DISABLE, XOOPS_NOTIFICATION_METHOD_PM => _NOT_METHOD_PM, XOOPS_NOTIFICATION_METHOD_EMAIL => _NOT_METHOD_EMAIL]);

    $notify_mode_select = new XoopsFormSelect(_NOT_NOTIFYMODE, 'notify_mode', $xoopsUser->getVar('notify_mode'));

    $notify_mode_select->addOptionArray([XOOPS_NOTIFICATION_MODE_SENDALWAYS => _NOT_MODE_SENDALWAYS, XOOPS_NOTIFICATION_MODE_SENDONCETHENDELETE => _NOT_MODE_SENDONCE, XOOPS_NOTIFICATION_MODE_SENDONCETHENWAIT => _NOT_MODE_SENDONCEPERLOGIN]);

    $bio_tarea = new XoopsFormTextArea(_US_EXTRAINFO, 'bio', $xoopsUser->getVar('bio', 'E'));

    $cookie_radio_value = empty($HTTP_COOKIE_VARS[$xoopsConfig['usercookie']]) ? 0 : 1;

    $cookie_radio = new XoopsFormRadioYN(_US_USECOOKIE, 'usecookie', $cookie_radio_value, _YES, _NO);

    $pwd_text = new XoopsFormPassword('', 'pass', 10, 20);

    $pwd_text2 = new XoopsFormPassword('', 'vpass', 10, 20);

    $pwd_tray = new XoopsFormElementTray(_US_PASSWORD . '<br>' . _US_TYPEPASSTWICE);

    $pwd_tray->addElement($pwd_text);

    $pwd_tray->addElement($pwd_text2);

    $mailok_radio = new XoopsFormRadioYN(_US_MAILOK, 'user_mailok', $xoopsUser->getVar('user_mailok'));

    $uid_hidden = new XoopsFormHidden('uid', $xoopsUser->getVar('uid'));

    $op_hidden = new XoopsFormHidden('op', 'saveuser');

    $submit_button = new XoopsFormButton('', 'submit', _US_SAVECHANGES, 'submit');

    $form->addElement($timezone_select);

    $form->addElement($icq_text);

    $form->addElement($aim_text);

    $form->addElement($yim_text);

    $form->addElement($msnm_text);

    $form->addElement($location_text);

    $form->addElement($occupation_text);

    $form->addElement($interest_text);

    $form->addElement($sig_tray);

    $form->addElement($umode_select);

    $form->addElement($uorder_select);

    $form->addElement($notify_method_select);

    $form->addElement($notify_mode_select);

    $form->addElement($bio_tarea);

    $form->addElement($pwd_tray);

    $form->addElement($cookie_radio);

    $form->addElement($mailok_radio);

    $form->addElement($uid_hidden);

    $form->addElement($op_hidden);

    $form->addElement($submit_button);

    if (1 == $xoopsConfigUser['allow_chgmail']) {
        $form->setRequired($email_text);
    }

    $form->display();

    require XOOPS_ROOT_PATH . '/footer.php';
}

if ('saveuser' == $op) {
    $myts = MyTextSanitizer::getInstance();

    if ($xoopsUser->getVar('uid') != $uid) {
        redirect_header('index.php', 3, _US_NOEDITRIGHT);

        exit();
    }

    if (1 == $xoopsConfigUser['allow_chgmail']) {
        $email = $myts->stripSlashesGPC(trim($email));

        if (!isset($email) || '' == $email || !checkEmail($email)) {
            require XOOPS_ROOT_PATH . '/header.php';

            echo '<div>' . _US_INVALIDMAIL . '</div><br>';

            require XOOPS_ROOT_PATH . '/footer.php';

            exit();
        }
    }

    if (isset($pass)) {
        $pass = trim($pass);
    }

    if (isset($vpass)) {
        $vpass = trim($vpass);
    }

    if ((isset($pass)) && ($pass != $vpass)) {
        echo '<div>' . _US_PASSNOTSAME . '</div>';
    } elseif (('' != $pass) && (mb_strlen($pass) < $xoopsConfigUser['minpass'])) {
        echo '<div>';

        printf(_US_PWDTOOSHORT, $xoopsConfigUser['minpass']);

        echo '</div>';
    } else {
        $memberHandler = xoops_getHandler('member');

        $edituser = $memberHandler->getUser($uid);

        $edituser->setVar('name', $name);

        if (1 == $xoopsConfigUser['allow_chgmail']) {
            $edituser->setVar('email', $email);
        }

        $edituser->setVar('url', formatURL($url));

        $edituser->setVar('user_icq', $user_icq);

        $edituser->setVar('user_from', $user_from);

        $edituser->setVar('user_sig', $user_sig);

        $user_viewemail = (!empty($user_viewemail)) ? 1 : 0;

        $edituser->setVar('user_viewemail', $user_viewemail);

        $edituser->setVar('user_aim', $user_aim);

        $edituser->setVar('user_yim', $user_yim);

        $edituser->setVar('user_msnm', $user_msnm);

        if (isset($pass) && '' != $pass) {
            $edituser->setVar('pass', md5($pass));
        }

        $attachsig = isset($attachsig) ? (int)$attachsig : 0;

        $edituser->setVar('attachsig', $attachsig);

        $edituser->setVar('timezone_offset', $timezone_offset);

        $edituser->setVar('uorder', $uorder);

        $edituser->setVar('umode', $umode);

        $edituser->setVar('notify_method', $notify_method);

        $edituser->setVar('notify_mode', $notify_mode);

        $edituser->setVar('bio', $bio);

        $edituser->setVar('user_occ', $user_occ);

        $edituser->setVar('user_intrest', $user_intrest);

        $edituser->setVar('user_mailok', $user_mailok);

        if ($usecookie) {
            setcookie($xoopsConfig['usercookie'], $xoopsUser->getVar('uname'), time() + 31536000);
        } else {
            setcookie($xoopsConfig['usercookie']);
        }

        if (!$memberHandler->insertUser($edituser)) {
            require XOOPS_ROOT_PATH . '/header.php';

            echo $edituser->getHtmlErrors();

            require XOOPS_ROOT_PATH . '/footer.php';
        } else {
            redirect_header('userinfo.php?uid=' . $uid, 1, _US_PROFUPDATED);
        }

        exit();
    }
}

if ('avatarform' == $op) {
    if (!is_object($xoopsUser)) {
        redirect_header('index.php', 1);
    }

    require XOOPS_ROOT_PATH . '/header.php';

    echo '<a href="userinfo.php?uid=' . $xoopsUser->getVar('uid') . '">' . _US_PROFILE . '</a>&nbsp;<span style="font-weight:bold;">&raquo;&raquo;</span>&nbsp;' . _US_UPLOADMYAVATAR . '<br><br>';

    if ($oldavatar = $xoopsUser->getVar('user_avatar')) {
        echo '<div style="text-align:center;"><h4 style="color:#ff0000; font-weight:bold;">' . _US_OLDDELETED . '</h4>';

        //Avatar fix

        if (false !== strpos($oldavatar, "http")) {
            echo "<img src=$oldavatar alt=''></div>";
        } else {
            echo '<img src="' . XOOPS_URL . '/uploads/' . $oldavatar . '" alt=""></div>';
        }
    }

    if (1 == $xoopsConfigUser['avatar_allow_upload'] && $xoopsUser->getVar('posts') >= $xoopsConfigUser['avatar_minposts']) {
        require_once __DIR__ . '/class/xoopsformloader.php';

        $form = new XoopsThemeForm(_US_UPLOADMYAVATAR, 'uploadavatar', 'edituser.php');

        $form->setExtra('enctype="multipart/form-data"');

        $form->addElement(new XoopsFormLabel(_US_MAXPIXEL, $xoopsConfigUser['avatar_width'] . ' x ' . $xoopsConfigUser['avatar_height']));

        $form->addElement(new XoopsFormLabel(_US_MAXIMGSZ, $xoopsConfigUser['avatar_maxsize']));

        $form->addElement(new XoopsFormFile(_US_SELFILE, 'avatarfile', $xoopsConfigUser['avatar_maxsize']), true);

        $form->addElement(new XoopsFormHidden('op', 'avatarupload'));

        $form->addElement(new XoopsFormHidden('uid', $xoopsUser->getVar('uid')));

        $form->addElement(new XoopsFormButton('', 'submit', _SUBMIT, 'submit'));

        $form->display();
    }

    $avatarHandler = xoops_getHandler('avatar');

    $form2 = new XoopsThemeForm(_US_CHOOSEAVT, 'uploadavatar', 'edituser.php');

    $avatar_select = new XoopsFormSelect('', 'user_avatar', $xoopsUser->getVar('user_avatar'));

    $avatar_select->addOptionArray($avatarHandler->getList('S'));

    $avatar_select->setExtra("onchange='showImgSelected(\"avatar\", \"user_avatar\", \"uploads\")'");

    $avatar_tray = new XoopsFormElementTray(_US_AVATAR, '&nbsp;');

    $avatar_tray->addElement($avatar_select);

    //Avatar fix

    if (false !== strpos($oldavatar, "http")) {
        $avatar_tray->addElement(new XoopsFormLabel('', "<img src='" . $xoopsUser->getVar('user_avatar', 'E') . "' name='avatar' id='avatar' alt=''> <a href=\"javascript:openWithSelfMain('" . XOOPS_URL . "/misc.php?action=showpopups&amp;type=avatars','avatars',600,400);\">" . _LIST . '</a>'));
    } else {
        $avatar_tray->addElement(
            new XoopsFormLabel('', "<img src='./uploads/" . $xoopsUser->getVar('user_avatar', 'E') . "' name='avatar' id='avatar' alt=''> <a href=\"javascript:openWithSelfMain('" . XOOPS_URL . "/misc.php?action=showpopups&amp;type=avatars','avatars',600,400);\">" . _LIST . '</a>')
        );
    }

    $form2->addElement($avatar_tray);

    $form2->addElement(new XoopsFormHidden('uid', $xoopsUser->getVar('uid')));

    $form2->addElement(new XoopsFormHidden('op', 'avatarchoose'));

    $form2->addElement(new XoopsFormButton('', 'submit2', _SUBMIT, 'submit'));

    $form2->display();

    require XOOPS_ROOT_PATH . '/footer.php';
}

if ('avatarupload' == $op) {
    if (!is_object($xoopsUser) || $xoopsUser->getVar('uid') != $uid) {
        redirect_header('index.php', 3, _US_NOEDITRIGHT);

        exit();
    }

    if (1 == $xoopsConfigUser['avatar_allow_upload'] && $xoopsUser->getVar('posts') >= $xoopsConfigUser['avatar_minposts']) {
        require_once XOOPS_ROOT_PATH . '/class/uploader.php';

        $uploader = new XoopsMediaUploader(XOOPS_ROOT_PATH . '/uploads', ['image/gif', 'image/jpeg', 'image/pjpeg', 'image/x-png', 'image/png'], $xoopsConfigUser['avatar_maxsize'], $xoopsConfigUser['avatar_width'], $xoopsConfigUser['avatar_height']);

        if ($uploader->fetchMedia($_POST['xoops_upload_file'][0])) {
            $uploader->setPrefix('cavt');

            if ($uploader->upload()) {
                $avtHandler = xoops_getHandler('avatar');

                $avatar = $avtHandler->create();

                $avatar->setVar('avatar_file', $uploader->getSavedFileName());

                $avatar->setVar('avatar_name', $xoopsUser->getVar('uname'));

                $avatar->setVar('avatar_mimetype', $uploader->getMediaType());

                $avatar->setVar('avatar_display', 1);

                $avatar->setVar('avatar_type', 'C');

                if (!$avtHandler->insert($avatar)) {
                    @unlink($uploader->getSavedDestination());
                } else {
                    $oldavatar = $xoopsUser->getVar('user_avatar');

                    if ($oldavatar && 'blank.gif' != $oldavatar && 0 !== strpos(mb_strtolower($oldavatar), "savt")) {
                        $avatars = &$avtHandler->getObjects(new Criteria('avatar_file', $oldavatar));

                        $avtHandler->delete($avatars[0]);

                        @unlink('uploads/' . $oldavatar);
                    }

                    $sql = sprintf("UPDATE %s SET user_avatar = '%s' WHERE uid = %u", $xoopsDB->prefix('users'), $uploader->getSavedFileName(), $xoopsUser->getVar('uid'));

                    $xoopsDB->query($sql);

                    $avtHandler->addUser($avatar->getVar('avatar_id'), $xoopsUser->getVar('uid'));

                    redirect_header('userinfo.php?t=' . time() . '&amp;uid=' . $xoopsUser->getVar('uid'), 0, _US_PROFUPDATED);
                }
            }
        }

        require XOOPS_ROOT_PATH . '/header.php';

        echo $uploader->getErrors();

        require XOOPS_ROOT_PATH . '/footer.php';
    }
}

if ('avatarchoose' == $op) {
    if (!is_object($xoopsUser) || $xoopsUser->getVar('uid') != $uid) {
        redirect_header('index.php', 3, _US_NOEDITRIGHT);

        exit();
    }

    $memberHandler = xoops_getHandler('member');

    $user_avatar = trim($user_avatar);

    $oldavatar = $xoopsUser->getVar('user_avatar');

    $xoopsUser->setVar('user_avatar', $user_avatar);

    if (!$memberHandler->insertUser($xoopsUser)) {
        require XOOPS_ROOT_PATH . '/header.php';

        echo $xoopsUser->getHtmlErrors();

        require XOOPS_ROOT_PATH . '/footer.php';

        exit();
    }

    $avtHandler = xoops_getHandler('avatar');

    if ($oldavatar && 'blank.gif' != $oldavatar && 0 !== strpos(mb_strtolower($oldavatar), "savt")) {
        $avatars = &$avtHandler->getObjects(new Criteria('avatar_file', $oldavatar));

        if (is_object($avatars[0])) {
            $avtHandler->delete($avatars[0]);
        }

        @unlink('uploads/' . $oldavatar);
    }

    if ('blank.gif' != $user_avatar) {
        $avatar = &$avtHandler->getObjects(new Criteria('avatar_file', $user_avatar));

        if (is_object($avatars[0])) {
            $avtHandler->addUser($avatar[0]->getVar('avatar_id'), $xoopsUser->getVar('uid'));
        }
    }

    redirect_header('userinfo.php?uid=' . $uid, 0, _US_PROFUPDATED);
}

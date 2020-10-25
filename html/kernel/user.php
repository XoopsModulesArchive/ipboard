<?php

// $Id: user.php,v 1.6 2003/03/27 14:52:15 okazu Exp $
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
if (!defined('XOOPS_ROOT_PATH')) {
    exit();
}

/**
 * Class for users
 * @author    Kazumi Ono <onokazu@xoops.org>
 * @copyright copyright (c) 2000-2003 XOOPS.org
 */
class XoopsUser extends XoopsObject
{
    /**
     * Array of groups that user belongs to
     * @var array
     */

    public $_groups = [];

    /**
     * @var bool is the user admin?
     */

    public $_isAdmin = null;

    /**
     * @var string user's rank
     */

    public $_rank = null;

    /**
     * @var bool is the user online?
     */

    public $_isOnline = null;

    /**
     * constructor
     * @param null $id ID of the user to be loaded from the database.
     */
    public function __construct($id = null)
    {
        $this->initVar('uid', XOBJ_DTYPE_INT, null, false);

        $this->initVar('name', XOBJ_DTYPE_TXTBOX, null, false, 60);

        $this->initVar('uname', XOBJ_DTYPE_TXTBOX, null, true, 25);

        $this->initVar('email', XOBJ_DTYPE_TXTBOX, null, true, 60);

        $this->initVar('url', XOBJ_DTYPE_TXTBOX, null, false, 100);

        $this->initVar('user_avatar', XOBJ_DTYPE_TXTBOX, null, false, 30);

        $this->initVar('user_regdate', XOBJ_DTYPE_INT, null, false);

        $this->initVar('user_icq', XOBJ_DTYPE_TXTBOX, null, false, 15);

        $this->initVar('user_from', XOBJ_DTYPE_TXTBOX, null, false, 100);

        $this->initVar('user_sig', XOBJ_DTYPE_TXTAREA, null, false, null);

        $this->initVar('user_viewemail', XOBJ_DTYPE_INT, 0, false);

        $this->initVar('actkey', XOBJ_DTYPE_OTHER, null, false);

        $this->initVar('user_aim', XOBJ_DTYPE_TXTBOX, null, false, 18);

        $this->initVar('user_yim', XOBJ_DTYPE_TXTBOX, null, false, 25);

        $this->initVar('user_msnm', XOBJ_DTYPE_TXTBOX, null, false, 100);

        $this->initVar('pass', XOBJ_DTYPE_TXTBOX, null, false, 32);

        $this->initVar('posts', XOBJ_DTYPE_INT, null, false);

        $this->initVar('attachsig', XOBJ_DTYPE_INT, 0, false);

        $this->initVar('rank', XOBJ_DTYPE_INT, 0, false);

        $this->initVar('level', XOBJ_DTYPE_INT, 0, false);

        $this->initVar('theme', XOBJ_DTYPE_OTHER, null, false);

        $this->initVar('timezone_offset', XOBJ_DTYPE_OTHER, null, false);

        $this->initVar('last_login', XOBJ_DTYPE_INT, 0, false);

        $this->initVar('umode', XOBJ_DTYPE_OTHER, null, false);

        $this->initVar('uorder', XOBJ_DTYPE_INT, 1, false);

        // RMV-NOTIFY

        $this->initVar('notify_method', XOBJ_DTYPE_OTHER, 1, false);

        $this->initVar('notify_mode', XOBJ_DTYPE_OTHER, 0, false);

        $this->initVar('user_occ', XOBJ_DTYPE_TXTBOX, null, false, 100);

        $this->initVar('bio', XOBJ_DTYPE_TXTAREA, null, false, null);

        $this->initVar('user_intrest', XOBJ_DTYPE_TXTBOX, null, false, 150);

        $this->initVar('user_mailok', XOBJ_DTYPE_INT, 1, false);

        // for backward compatibility

        if (isset($id)) {
            if (is_array($id)) {
                $this->assignVars($id);
            } else {
                $memberHandler = xoops_getHandler('member');

                $user = $memberHandler->getUser($id);

                foreach ($user->vars as $k => $v) {
                    $this->assignVar($k, $v['value']);
                }
            }
        }
    }

    //### Methods from here below will be deprecated. ###

    /**
     * find the username for a given ID
     *
     * @param int $userid ID of the user to find
     * @return string name of the user. name for "anonymous" if not found.
     * @deprecated
     */
    public function getUnameFromId($userid)
    {
        $userid = (int)$userid;

        if ($userid > 0) {
            $memberHandler = xoops_getHandler('member');

            $user = $memberHandler->getUser($userid);

            if (is_object($user)) {
                $ts = MyTextSanitizer::getInstance();

                return $ts->htmlSpecialChars($user->getVar('uname'));
            }
        }

        return $GLOBALS['xoopsConfig']['anonymous'];
    }

    /**
     * increase the number of posts for the user
     *
     * @deprecated
     */
    public function incrementPost()
    {
        $memberHandler = xoops_getHandler('member');

        return $memberHandler->updateUserByField($this, 'posts', $this->getVar('posts') + 1);
    }

    /**
     * set the groups for the user
     *
     * @param array $groupsArr Array of groups that user belongs to
     * @deprecated
     */
    public function setGroups($groupsArr)
    {
        if (is_array($groupsArr)) {
            $this->_groups = &$groupsArr;
        }
    }

    /**
     * get the groups that the user belongs to
     *
     * @return array array of groups
     * @deprecated
     */
    public function &getGroups()
    {
        if (empty($this->_groups)) {
            $memberHandler = xoops_getHandler('member');

            $this->_groups = $memberHandler->getGroupsByUser($this->getVar('uid'));
        }

        return $this->_groups;
    }

    /**
     * alias for {@link getGroups()}
     * @return array array of groups
     * @see getGroups()
     * @deprecated
     */
    public function &groups()
    {
        return $this->getGroups();
    }

    /**
     * is the user admin?
     * @param int $module_id check if user is admin of this module
     * @return bool is the user admin of that module?
     * @deprecated
     */
    public function isAdmin($module_id = 0)
    {
        $modulepermHandler = xoops_getHandler('groupperm');

        return $modulepermHandler->checkRight('module_admin', $module_id, $this->getGroups());
    }

    /**
     * get the user's rank
     * @return array array of rank ID and title
     * @deprecated
     */
    public function rank()
    {
        if (!isset($this->_rank)) {
            $this->_rank = xoops_getrank($this->getVar('rank'), $this->getVar('posts'));
        }

        return $this->_rank;
    }

    /**
     * is the user activated?
     * @return bool
     * @deprecated
     */
    public function isActive()
    {
        if (0 == $this->getVar('level')) {
            return false;
        }

        return true;
    }

    /**
     * is the user currently logged in?
     * @return bool
     * @deprecated
     */
    public function isOnline()
    {
        if (!isset($this->_isOnline)) {
            $onlinehandler = xoops_getHandler('online');

            $this->_isOnline = ($onlinehandler->getCount(new Criteria('online_uid', $this->getVar('uid'))) > 0) ? true : false;
        }

        return $this->_isOnline;
    }

    /**#@+
     * specialized wrapper for {@link XoopsObject::getVar()}
     *
     * kept for compatibility reasons.
     *
     * @see XoopsObject::getVar()
     * @deprecated
     */

    /**
     * get the users UID
     * @return int
     */
    public function uid()
    {
        return $this->getVar('uid');
    }

    /**
     * get the users name
     * @param string $format format for the output, see {@link XoopsObject::getVar()}
     * @return string
     */
    public function name($format = 'S')
    {
        return $this->getVar('name', $format);
    }

    /**
     * get the user's uname
     * @param string $format format for the output, see {@link XoopsObject::getVar()}
     * @return string
     */
    public function uname($format = 'S')
    {
        return $this->getVar('uname', $format);
    }

    /**
     * get the user's email
     *
     * @param string $format format for the output, see {@link XoopsObject::getVar()}
     * @return string
     */
    public function email($format = 'S')
    {
        return $this->getVar('email', $format);
    }

    public function url($format = 'S')
    {
        return $this->getVar('url', $format);
    }

    public function user_avatar($format = 'S')
    {
        return $this->getVar('user_avatar');
    }

    public function user_regdate()
    {
        return $this->getVar('user_regdate');
    }

    public function user_icq($format = 'S')
    {
        return $this->getVar('user_icq', $format);
    }

    public function user_from($format = 'S')
    {
        return $this->getVar('user_from', $format);
    }

    public function user_sig($format = 'S')
    {
        return $this->getVar('user_sig', $format);
    }

    public function user_viewemail()
    {
        return $this->getVar('user_viewemail');
    }

    public function actkey()
    {
        return $this->getVar('actkey');
    }

    public function user_aim($format = 'S')
    {
        return $this->getVar('user_aim', $format);
    }

    public function user_yim($format = 'S')
    {
        return $this->getVar('user_yim', $format);
    }

    public function user_msnm($format = 'S')
    {
        return $this->getVar('user_msnm', $format);
    }

    public function pass()
    {
        return $this->getVar('pass');
    }

    public function posts()
    {
        return $this->getVar('posts');
    }

    public function attachsig()
    {
        return $this->getVar('attachsig');
    }

    public function level()
    {
        return $this->getVar('level');
    }

    public function theme()
    {
        return $this->getVar('theme');
    }

    public function timezone()
    {
        return $this->getVar('timezone_offset');
    }

    public function umode()
    {
        return $this->getVar('umode');
    }

    public function uorder()
    {
        return $this->getVar('uorder');
    }

    // RMV-NOTIFY

    public function notify_method()
    {
        return $this->getVar('notify_method');
    }

    public function notify_mode()
    {
        return $this->getVar('notify_mode');
    }

    public function user_occ($format = 'S')
    {
        return $this->getVar('user_occ', $format);
    }

    public function bio($format = 'S')
    {
        return $this->getVar('bio', $format);
    }

    public function user_intrest($format = 'S')
    {
        return $this->getVar('user_intrest', $format);
    }

    public function last_login()
    {
        return $this->getVar('last_login');
    }

    /**#@-*/
}

/**
 * XOOPS user handler class.
 * This class is responsible for providing data access mechanisms to the data source
 * of XOOPS user class objects.
 *
 * @author    Kazumi Ono <onokazu@xoops.org>
 * @copyright copyright (c) 2000-2003 XOOPS.org
 */
class XoopsUserHandler extends XoopsObjectHandler
{
    /**
     * create a new user
     *
     * @param bool $isNew flag the new objects as "new"?
     * @return object XoopsUser
     */
    public function &create($isNew = true)
    {
        $user = new XoopsUser();

        if ($isNew) {
            $user->setNew();
        }

        return $user;
    }

    /**
     * retrieve a user
     *
     * @param int $id UID of the user
     * @return mixed reference to the {@link XoopsUser} object, FALSE if failed
     */
    public function get($id)
    {
        if ((int)$id > 0) {
            $sql = 'SELECT * FROM ' . $this->db->prefix('users') . ' WHERE uid=' . $id;

            if (!$result = $this->db->query($sql)) {
                return false;
            }

            $numrows = $this->db->getRowsNum($result);

            if (1 == $numrows) {
                $user = new XoopsUser();

                $user->assignVars($this->db->fetchArray($result));

                return $user;
            }
        }

        return false;
    }

    /**
     * insert a new user in the database
     *
     * @param \XoopsObject $user reference to the {@link XoopsUser} object
     * @param bool         $force
     * @return bool FALSE if failed, TRUE if already present and unchanged or successful
     */
    public function insert(XoopsObject $user, $force = false)
    {
        global $isbb;

        if ('xoopsuser' != get_class($user)) {
            return false;
        }

        if (!$user->isDirty()) {
            return true;
        }

        if (!$user->cleanVars()) {
            return false;
        }

        foreach ($user->cleanVars as $k => $v) {
            ${$k} = $v;
        }

        // RMV-NOTIFY

        // Added two fields, notify_method, notify_mode

        if ($user->isNew()) {
            $uid = $this->db->genId($this->db->prefix('users') . '_uid_seq');

            $sql = sprintf(
                'INSERT INTO %s (uid, uname, name, email, url, user_avatar, user_regdate, user_icq, user_from, user_sig, user_viewemail, actkey, user_aim, user_yim, user_msnm, pass, posts, attachsig, rank, level, theme, timezone_offset, last_login, umode, uorder, notify_method, notify_mode, user_occ, bio, user_intrest, user_mailok) VALUES (%u, %s, %s, %s, %s, %s, %u, %s, %s, %s, %u, %s, %s, %s, %s, %s, %u, %u, %u, %u, %s, %.2f, %u, %s, %u, %u, %u, %s, %s, %s, %u)',
                $this->db->prefix('users'),
                $uid,
                $this->db->quoteString($uname),
                $this->db->quoteString($name),
                $this->db->quoteString($email),
                $this->db->quoteString($url),
                $this->db->quoteString($user_avatar),
                time(),
                $this->db->quoteString($user_icq),
                $this->db->quoteString($user_from),
                $this->db->quoteString($user_sig),
                $user_viewemail,
                $this->db->quoteString($actkey),
                $this->db->quoteString($user_aim),
                $this->db->quoteString($user_yim),
                $this->db->quoteString($user_msnm),
                $this->db->quoteString($pass),
                $posts,
                $attachsig,
                $rank,
                $level,
                $this->db->quoteString($theme),
                $timezone_offset,
                0,
                $this->db->quoteString($umode),
                $uorder,
                $notify_method,
                $notify_mode,
                $this->db->quoteString($user_occ),
                $this->db->quoteString($bio),
                $this->db->quoteString($user_intrest),
                $user_mailok
            );

            //IPB statistic update

            if ($isbb) {
                $last_uid = $this->db->fetchArray($this->db->query('SELECT MAX(uid) as total FROM ' . $this->db->prefix('users') . ''));

                $this->db->queryF('UPDATE ' . $this->db->prefix('ipb_stats') . " SET MEM_COUNT = '" . $this->getCount() . "', LAST_MEM_ID = $last_uid[total] + 1, LAST_MEM_NAME = '$uname' ");
            }

            //End
        } else {
            $sql = sprintf(
                'UPDATE %s SET uname = %s, name = %s, email = %s, url = %s, user_avatar = %s, user_icq = %s, user_from = %s, user_sig = %s, user_viewemail = %u, user_aim = %s, user_yim = %s, user_msnm = %s, posts = %d,  pass = %s, attachsig = %u, rank = %u, level= %u, theme = %s, timezone_offset = %.2f, umode = %s, last_login = %u, uorder = %u, notify_method = %u, notify_mode = %u, user_occ = %s, bio = %s, user_intrest = %s, user_mailok = %u WHERE uid = %u',
                $this->db->prefix('users'),
                $this->db->quoteString($uname),
                $this->db->quoteString($name),
                $this->db->quoteString($email),
                $this->db->quoteString($url),
                $this->db->quoteString($user_avatar),
                $this->db->quoteString($user_icq),
                $this->db->quoteString($user_from),
                $this->db->quoteString($user_sig),
                $user_viewemail,
                $this->db->quoteString($user_aim),
                $this->db->quoteString($user_yim),
                $this->db->quoteString($user_msnm),
                $posts,
                $this->db->quoteString($pass),
                $attachsig,
                $rank,
                $level,
                $this->db->quoteString($theme),
                $timezone_offset,
                $this->db->quoteString($umode),
                $last_login,
                $uorder,
                $notify_method,
                $notify_mode,
                $this->db->quoteString($user_occ),
                $this->db->quoteString($bio),
                $this->db->quoteString($user_intrest),
                $user_mailok,
                $uid
            );
        }

        if (false !== $force) {
            $result = $this->db->queryF($sql);
        } else {
            $result = $this->db->query($sql);
        }

        if (!$result) {
            return false;
        }

        if (empty($uid)) {
            $uid = $this->db->getInsertId();
        }

        $user->assignVar('uid', $uid);

        return true;
    }

    /**
     * delete a user from the database
     *
     * @param \XoopsObject $user reference to the user to delete
     * @param bool         $force
     * @return bool FALSE if failed.
     */
    public function delete(XoopsObject $user, $force = false)
    {
        global $isbb;

        if ('xoopsuser' != get_class($user)) {
            return false;
        }

        $sql = sprintf('DELETE FROM %s WHERE uid = %u', $this->db->prefix('users'), $user->getVar('uid'));

        if (false !== $force) {
            $result = $this->db->queryF($sql);
        } else {
            $result = $this->db->query($sql);
        }

        if (!$result) {
            return false;
        }

        //IPB statistic update

        if ($isbb) {
            $last_uid = $this->db->fetchArray($this->db->query('SELECT MAX(uid) as total FROM ' . $this->db->prefix('users') . ''));

            $last_uname = $this->db->fetchArray($this->db->query('SELECT uname FROM ' . $this->db->prefix('users') . " WHERE uid = '$last_uid[total]'"));

            $this->db->queryF('UPDATE ' . $this->db->prefix('ipb_stats') . ' SET MEM_COUNT = ' . $this->getCount() . " - 1, LAST_MEM_ID = $last_uid[total], LAST_MEM_NAME = '$last_uname[uname]' ");
        }

        return true;
    }

    /**
     * retrieve users from the database
     *
     * @param null $criteria  {@link CriteriaElement} conditions to be met
     * @param bool $id_as_key use the UID as key for the array?
     * @return array array of {@link XoopsUser} objects
     */
    public function &getObjects($criteria = null, $id_as_key = false)
    {
        $ret = [];

        $limit = $start = 0;

        $sql = 'SELECT * FROM ' . $this->db->prefix('users');

        if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
            $sql .= ' ' . $criteria->renderWhere();

            if ('' != $criteria->getSort()) {
                $sql .= ' ORDER BY ' . $criteria->getSort() . ' ' . $criteria->getOrder();
            }

            $limit = $criteria->getLimit();

            $start = $criteria->getStart();
        }

        $result = $this->db->query($sql, $limit, $start);

        if (!$result) {
            return $ret;
        }

        while (false !== ($myrow = $this->db->fetchArray($result))) {
            $user = new XoopsUser();

            $user->assignVars($myrow);

            if (!$id_as_key) {
                $ret[] = &$user;
            } else {
                $ret[$myrow['uid']] = &$user;
            }

            unset($user);
        }

        return $ret;
    }

    /**
     * count users matching a condition
     *
     * @param null $criteria {@link CriteriaElement} to match
     * @return int count of users
     */
    public function getCount($criteria = null)
    {
        $sql = 'SELECT COUNT(*) FROM ' . $this->db->prefix('users');

        if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
            $sql .= ' ' . $criteria->renderWhere();
        }

        $result = $this->db->query($sql);

        if (!$result) {
            return 0;
        }

        [$count] = $this->db->fetchRow($result);

        return $count;
    }

    /**
     * delete users matching a set of conditions
     *
     * @param null $criteria {@link CriteriaElement}
     * @return bool FALSE if deletion failed
     */
    public function deleteAll($criteria = null)
    {
        $sql = 'DELETE FROM ' . $this->db->prefix('users');

        if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
            $sql .= ' ' . $criteria->renderWhere();
        }

        if (!$result = $this->db->query($sql)) {
            return false;
        }

        return true;
    }

    /**
     * Change a value for users with a certain criteria
     *
     * @param string $fieldname  Name of the field
     * @param string $fieldvalue Value to write
     * @param null   $criteria   {@link CriteriaElement}
     *
     * @return  bool
     */
    public function updateAll($fieldname, $fieldvalue, $criteria = null)
    {
        $set_clause = is_numeric($fieldvalue) ? $fieldname . ' = ' . $fieldvalue : $fieldname . ' = ' . $this->db->quoteString($fieldvalue);

        $sql = 'UPDATE ' . $this->db->prefix('users') . ' SET ' . $set_clause;

        if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
            $sql .= ' ' . $criteria->renderWhere();
        }

        if (!$result = $this->db->query($sql)) {
            return false;
        }

        return true;
    }
}

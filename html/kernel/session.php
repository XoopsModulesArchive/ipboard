<?php

// $Id: session.php,v 1.2 2003/03/12 21:02:08 okazu Exp $
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
/**
 * @author        Kazumi Ono    <onokazu@xoops.org>
 * @copyright     copyright (c) 2000-2003 XOOPS.org
 */

/**
 * Handler for a session
 *
 * @author        Kazumi Ono    <onokazu@xoops.org>
 * @copyright     copyright (c) 2000-2003 XOOPS.org
 */
class XoopsSessionHandler
{
    /**
     * Database connection
     *
     * @var    object
     */

    public $db;

    /**
     * Constructor
     *
     * @param mixed $db
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Open a session
     *
     * @param string $save_path
     * @param string $session_name
     *
     * @return    bool
     */
    public function open($save_path, $session_name)
    {
        return true;
    }

    /**
     * Close a session
     *
     * @return    bool
     */
    public function close()
    {
        return true;
    }

    /**
     * Read a session from the database
     *
     * @param mixed $sess_id
     *
     * @return    array   Session data
     */
    public function read($sess_id)
    {
        $sql = 'SELECT sess_data FROM ' . $this->db->prefix('session') . " WHERE sess_id = '$sess_id'";

        if (false !== $result = $this->db->query($sql)) {
            if (list($sess_data) = $this->db->fetchRow($result)) {
                return $sess_data;
            }
        }

        return '';
    }

    /**
     * Write a session to the database
     *
     * @param string $sess_id
     * @param string $sess_data
     *
     * @return  bool
     **/
    public function write($sess_id, $sess_data)
    {
        global $HTTP_SERVER_VARS, $HTTP_USER_AGENT, $isbb, $meminfo, $sid_bb, $uid_bb;

        [$count] = $this->db->fetchRow($this->db->query('SELECT COUNT(*) FROM ' . $this->db->prefix('session') . " WHERE sess_id='" . $sess_id . "'"));

        //IPBM session

        if ($isbb) {
            require XOOPS_ROOT_PATH . '/modules/ipboard/conf_global.php';

            $INFO['session_expiration'] = $INFO['session_expiration'] ? (time() - $INFO['session_expiration']) : (time() - 3600);

            if ($count > 0) {
                $sql = sprintf("UPDATE %s SET sess_updated = %u, sess_data = '%s', member_id = '%s', member_name = '%s', member_group = '%s' WHERE sess_id = '%s'", $this->db->prefix('session'), time(), $sess_data, $meminfo['uid'], $meminfo['uname'], $meminfo['mgroup'], $sess_id);
            } else {
                $this->db->queryF('DELETE FROM ' . $this->db->prefix('session') . " WHERE sess_updated < '" . $INFO['session_expiration'] . "'");

                $sql = sprintf(
                    "INSERT INTO %s (sess_id, sess_updated, sess_ip, sess_data, member_id, member_name, member_group, browser, login_type) VALUES ('%s', %u, '%s', '%s', '%s', '%s', '%s', '%s', '%s')",
                    $this->db->prefix('session'),
                    $sess_id,
                    time(),
                    $HTTP_SERVER_VARS['REMOTE_ADDR'],
                    $sess_data,
                    $meminfo['uid'],
                    $meminfo['uname'],
                    $meminfo['mgroup'],
                    mb_substr($HTTP_USER_AGENT, 0, 50),
                    0
                );

                if (time() - $meminfo['last_activity'] > 300) {
                    @setcookie('topicsread', '', 0, $INFO['cookie_path'], $INFO['cookie_domain'], 0);

                    $this->db->queryF('UPDATE ' . $this->db->prefix('users') . " SET last_visit = last_activity, last_activity = '" . time() . "' WHERE uid='" . $uid_bb . "' ");
                }
            }
        } else {
            if ($count > 0) {
                $sql = sprintf("UPDATE %s SET sess_updated = %u, sess_data = '%s' WHERE sess_id = '%s'", $this->db->prefix('session'), time(), $sess_data, $sess_id);
            } else {
                $sql = sprintf("INSERT INTO %s (sess_id, sess_updated, sess_ip, sess_data) VALUES ('%s', %u, '%s', '%s')", $this->db->prefix('session'), $sess_id, time(), $HTTP_SERVER_VARS['REMOTE_ADDR'], $sess_data);
            }
        }

        // IPBM end

        if (!$this->db->queryF($sql)) {
            return false;
        }

        return true;
    }

    /**
     * Destroy a session
     *
     * @param string $sess_id
     *
     * @return  bool
     **/
    public function destroy($sess_id)
    {
        global $isbb, $uid_bb;

        $sql = sprintf("DELETE FROM %s WHERE sess_id = '%s'", $this->db->prefix('session'), $sess_id);

        if (!$result = $this->db->queryF($sql)) {
            return false;
        }

        // IPB sessions

        if ($isbb) {
            $this->db->queryF('UPDATE ' . $this->db->prefix('users') . " SET last_visit='" . time() . "', last_activity='" . time() . "' WHERE uid='" . $uid_bb . "' ");
        }

        return true;
    }

    /**
     * Garbage Collector
     *
     * @param int $expire Time in seconds until a session expires
     **/
    public function gc($expire)
    {
        $mintime = time() - (int)$expire;

        $sql = sprintf('DELETE FROM %s WHERE sess_updated < %u', $this->db->prefix('session'), $mintime);

        $this->db->queryF($sql);
    }
}

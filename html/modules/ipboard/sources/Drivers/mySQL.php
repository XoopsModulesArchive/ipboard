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
|   > mySQL DB abstraction module
|   > Module written by Matt Mecham
|   > Date started: 14th February 2002
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

class db_driver
{
    public $obj = [
        'sql_database' => '',
'sql_user' => 'root',
'sql_pass' => '',
'sql_host' => 'localhost',
'sql_port' => '',
'persistent' => '0',
'sql_tbl_prefix' => 'ibf_',
'cached_queries' => [],
'debug' => 0,
    ];

    public $query_id = '';

    public $connection_id = '';

    public $query_count = 0;

    public $record_row = [];

    public $return_die = 0;

    public $error = '';

    /*========================================================================*/

    // Connect to the database

    /*========================================================================*/

    public function connect()
    {
        if ($this->obj['persistent']) {
            $this->connection_id = mysql_pconnect(
                $this->obj['sql_host'],
                $this->obj['sql_user'],
                $this->obj['sql_pass']
            );
        } else {
            $this->connection_id = mysql_connect(
                $this->obj['sql_host'],
                $this->obj['sql_user'],
                $this->obj['sql_pass']
            );
        }

        if (!mysqli_select_db($GLOBALS['xoopsDB']->conn, $this->obj['sql_database'], $this->connection_id)) {
            echo('ERROR: Cannot find database ' . $this->obj['sql_database']);
        }
    }

    /*========================================================================*/

    // Process a query

    /*========================================================================*/

    public function query($the_query, $bypass = 0)
    {
        //--------------------------------------

        // Change the table prefix if needed

        //--------------------------------------

        if (1 != $bypass) {
            if ('ibf_' != $this->obj['sql_tbl_prefix']) {
                //XOOPS

                $the_query = str_replace('ibf_sessions', $this->obj['sql_tbl_prefix'] . 'session', $the_query);

                $the_query = str_replace('xbb_members', $this->obj['sql_tbl_prefix'] . 'users', $the_query);

                $the_query = str_replace('xbb_emoticons', $this->obj['sql_tbl_prefix'] . 'smiles', $the_query);

                $the_query = str_replace('xbb_groups_users_link', $this->obj['sql_tbl_prefix'] . 'groups_users_link', $the_query);

                $the_query = preg_replace("/ibf_(\S+?)([\s\.,]|$)/", $this->obj['sql_tbl_prefix'] . 'ipb_\\1\\2', $the_query);

                $the_query = preg_replace("/xbb_(\S+?)([\s\.,]|$)/", $this->obj['sql_tbl_prefix'] . '\\1\\2', $the_query);

                $the_query = str_replace('m.password', 'm.pass', $the_query);

                $the_query = str_replace('s.id', 's.sess_id', $the_query);

                $the_query = str_replace('running_time', 'sess_updated', $the_query);

                $the_query = str_replace('.running_time', '.sess_updated', $the_query);

                $the_query = str_replace('m.joined', 'm.user_regdate', $the_query);

                $the_query = str_replace('m.avatar,', 'm.user_avatar,', $the_query);

                $the_query = str_replace('aim_name', 'user_aim', $the_query);

                $the_query = str_replace('.aim_name', '.user_aim', $the_query);

                $the_query = str_replace('icq_number', 'user_icq', $the_query);

                $the_query = str_replace('.icq_number', '.user_icq', $the_query);

                $the_query = str_replace('m.website', 'm.url', $the_query);

                $the_query = str_replace('m.yahoo', 'm.user_yim', $the_query);

                $the_query = str_replace('time_offset', 'timezone_offset', $the_query);

                $the_query = str_replace('.time_offset', '.timezone_offset', $the_query);

                $the_query = str_replace('hide_email,', 'user_viewemail,', $the_query);

                $the_query = str_replace('.hide_email', '.user_viewemail', $the_query);

                $the_query = str_replace('msnname', 'user_msnm', $the_query);

                $the_query = str_replace('.msnname', '.user_msnm', $the_query);

                $the_query = str_replace('view_sigs', 'attachsig', $the_query);

                $the_query = str_replace('.view_sigs', '.attachsig', $the_query);
            }
        }

        if ($this->obj['debug']) {
            global $Debug, $ibforums;

            $Debug->startTimer();
        }

        $this->query_id = $GLOBALS['xoopsDB']->queryF($the_query, $this->connection_id);

        if (!$this->query_id) {
            $this->fatal_error("mySQL query error: $the_query");
        }

        if ($this->obj['debug']) {
            $endtime = $Debug->endTimer();

            if (0 === stripos($the_query, "select")) {
                $eid = $GLOBALS['xoopsDB']->queryF("EXPLAIN $the_query", $this->connection_id);

                $ibforums->debug_html .= "<table width='95%' border='1' cellpadding='6' cellspacing='0' bgcolor='#FFE8F3' align='center'>
										   <tr>
										   	 <td colspan='8' style='font-size:14px' bgcolor='#FFC5Cb'><b>Select Query</b></td>
										   </tr>
										   <tr>
										    <td colspan='8' style='font-family:courier new, courier, monaco, arial;font-size:14px'>$the_query</td>
										   </tr>
										   <tr bgcolor='#FFC5Cb'>
											 <td><b>table</b></td><td><b>type</b></td><td><b>possible_keys</b></td>
											 <td><b>key</b></td><td><b>key_len</b></td><td><b>ref</b></td>
											 <td><b>rows</b></td><td><b>Extra</b></td>
										   </tr>\n";

                while (false !== ($array = $GLOBALS['xoopsDB']->fetchBoth($eid))) {
                    $type_col = '#FFFFFF';

                    if ('ref' == $array['type'] or 'eq_ref' == $array['type'] or 'const' == $array['type']) {
                        $type_col = '#D8FFD4';
                    } elseif ('ALL' == $array['type']) {
                        $type_col = '#FFEEBA';
                    }

                    $ibforums->debug_html .= "<tr bgcolor='#FFFFFF'>
											 <td>$array[table]&nbsp;</td>
											 <td bgcolor='$type_col'>$array[type]&nbsp;</td>
											 <td>$array[possible_keys]&nbsp;</td>
											 <td>$array[key]&nbsp;</td>
											 <td>$array[key_len]&nbsp;</td>
											 <td>$array[ref]&nbsp;</td>
											 <td>$array[rows]&nbsp;</td>
											 <td>$array[Extra]&nbsp;</td>
										   </tr>\n";
                }

                if ($endtime > 0.1) {
                    $endtime = "<span style='color:red'><b>$endtime</b></span>";
                }

                $ibforums->debug_html .= "<tr>
										  <td colspan='8' bgcolor='#FFD6DC' style='font-size:14px'><b>mySQL time</b>: $endtime</b></td>
										  </tr>
										  </table>\n<br>\n";
            } else {
                $ibforums->debug_html .= "<table width='95%' border='1' cellpadding='6' cellspacing='0' bgcolor='#FEFEFE'  align='center'>
										 <tr>
										  <td style='font-size:14px' bgcolor='#EFEFEF'><b>Non Select Query</b></td>
										 </tr>
										 <tr>
										  <td style='font-family:courier new, courier, monaco, arial;font-size:14px'>$the_query</td>
										 </tr>
										 <tr>
										  <td style='font-size:14px' bgcolor='#EFEFEF'><b>mySQL time</b>: $endtime</span></td>
										 </tr>
										</table><br>\n\n";
            }
        }

        $this->query_count++;

        $this->obj['cached_queries'][] = $the_query;

        return $this->query_id;
    }

    /*========================================================================*/

    // Fetch a row based on the last query

    /*========================================================================*/

    public function fetch_row($query_id = '')
    {
        if ('' == $query_id) {
            $query_id = $this->query_id;
        }

        $this->record_row = $GLOBALS['xoopsDB']->fetchBoth($query_id, MYSQL_ASSOC);

        return $this->record_row;
    }

    /*========================================================================*/

    // Fetch the number of rows affected by the last query

    /*========================================================================*/

    public function get_affected_rows()
    {
        return $GLOBALS['xoopsDB']->getAffectedRows($this->connection_id);
    }

    /*========================================================================*/

    // Fetch the number of rows in a result set

    /*========================================================================*/

    public function get_num_rows()
    {
        return $GLOBALS['xoopsDB']->getRowsNum($this->query_id);
    }

    /*========================================================================*/

    // Fetch the last insert id from an sql autoincrement

    /*========================================================================*/

    public function get_insert_id()
    {
        return $GLOBALS['xoopsDB']->getInsertId($this->connection_id);
    }

    /*========================================================================*/

    // Return the amount of queries used

    /*========================================================================*/

    public function get_query_cnt()
    {
        return $this->query_count;
    }

    /*========================================================================*/

    // Free the result set from mySQLs memory

    /*========================================================================*/

    public function free_result($query_id = '')
    {
        if ('' == $query_id) {
            $query_id = $this->query_id;
        }

        @$GLOBALS['xoopsDB']->freeRecordSet($query_id);
    }

    /*========================================================================*/

    // Shut down the database

    /*========================================================================*/

    public function close_db()
    {
        return $GLOBALS['xoopsDB']->close($this->connection_id);
    }

    /*========================================================================*/

    // Return an array of tables

    /*========================================================================*/

    public function get_table_names()
    {
        $result = mysql_list_tables($this->obj['sql_database']);

        $num_tables = @mysql_numrows($result);

        for ($i = 0; $i < $num_tables; $i++) {
            $tables[] = mysql_tablename($result, $i);
        }

        $GLOBALS['xoopsDB']->freeRecordSet($result);

        return $tables;
    }

    /*========================================================================*/

    // Return an array of fields

    /*========================================================================*/

    public function get_result_fields($query_id = '')
    {
        if ('' == $query_id) {
            $query_id = $this->query_id;
        }

        while (false !== ($field = mysql_fetch_field($query_id))) {
            $Fields[] = $field;
        }

        //$GLOBALS['xoopsDB']->freeRecordSet($query_id);

        return $Fields;
    }

    /*========================================================================*/

    // Basic error handler

    /*========================================================================*/

    public function fatal_error($the_error)
    {
        global $INFO;

        // Are we simply returning the error?

        if (1 == $this->return_die) {
            $this->error = $GLOBALS['xoopsDB']->error();

            return true;
        }

        $the_error .= "\n\nmySQL error: " . $GLOBALS['xoopsDB']->error() . "\n";

        $the_error .= 'mySQL error code: ' . $GLOBALS['xoopsDB']->errno() . "\n";

        $the_error .= 'Date: ' . date('l dS of F Y h:i:s A');

        $out = "<html><head><title>Invision Power Board Database Error</title>
    		   <style>P,BODY{ font-family:arial,sans-serif; font-size:11px; }</style></head><body>
    		   &nbsp;<br><br><blockquote><b>There appears to be an error with the {$INFO['board_name']} database.</b><br>
    		   You can try to refresh the page by clicking <a href=\"javascript:window.location=window.location;\">here</a>, if this
    		   does not fix the error, you can contact the board administrator by clicking <a href='mailto:{$INFO['email_in']}?subject=SQL+Error'>here</a>
    		   <br><br><b>Error Returned</b><br>
    		   <form name='mysql'><textarea rows=\"15\" cols=\"60\">" . htmlspecialchars($the_error, ENT_QUOTES | ENT_HTML5) . '</textarea></form><br>We apologise for any inconvenience</blockquote></body></html>';

        echo($out);

        die('');
    }

    /*========================================================================*/

    // Create an array from a multidimensional array returning formatted

    // strings ready to use in an INSERT query, saves having to manually format

    // the (INSERT INTO table) ('field', 'field', 'field') VALUES ('val', 'val')

    /*========================================================================*/

    public function compile_db_insert_string($data)
    {
        $field_names = '';

        $field_values = '';

        foreach ($data as $k => $v) {
            $v = preg_replace("/'/", "\\'", $v);

            //$v = preg_replace( "/#/", "\\#", $v );

            $field_names .= "$k,";

            $field_values .= "'$v',";
        }

        $field_names = preg_replace('/,$/', '', $field_names);

        $field_values = preg_replace('/,$/', '', $field_values);

        return [
            'FIELD_NAMES' => $field_names,
'FIELD_VALUES' => $field_values,
        ];
    }

    /*========================================================================*/

    // Create an array from a multidimensional array returning a formatted

    // string ready to use in an UPDATE query, saves having to manually format

    // the FIELD='val', FIELD='val', FIELD='val'

    /*========================================================================*/

    public function compile_db_update_string($data)
    {
        $return_string = '';

        foreach ($data as $k => $v) {
            $v = preg_replace("/'/", "\\'", $v);

            $return_string .= $k . "='" . $v . "',";
        }

        $return_string = preg_replace('/,$/', '', $return_string);

        return $return_string;
    }
} // end class

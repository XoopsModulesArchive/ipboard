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
|   > Topic Tracker module
|   > Module written by Matt Mecham
|   > Date started: 5th March 2002
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

$idx = new tracker();

class tracker
{
    public $output = '';

    public $base_url = '';

    public $html = '';

    public $forum = [];

    public $topic = [];

    public $category = [];

    public $type = 'topic';

    public function __construct($is_sub = 0)
    {
        //------------------------------------------------------

        // $is_sub is a boolean operator.

        // If set to 1, we don't show the "topic subscribed" page

        // we simply end the subroutine and let the caller finish

        // up for us.

        //------------------------------------------------------

        global $ibforums, $DB, $std, $print, $skin_universal;

        $ibforums->lang = $std->load_words($ibforums->lang, 'lang_emails', $ibforums->lang_id);

        //------------------------------------------------------

        // Check the input

        //------------------------------------------------------

        if ('forum' == $ibforums->input['type']) {
            $this->type = 'forum';
        }

        $ibforums->input['t'] = (int)$ibforums->input['t'];

        $ibforums->input['f'] = (int)$ibforums->input['f'];

        //------------------------------------------------------

        // Get the forum info based on the forum ID, get the category name, ID, and get the topic details

        //------------------------------------------------------

        if ('forum' == $this->type) {
            $DB->query("SELECT f.id as fid, f.read_perms, f.password FROM ibf_forums f WHERE f.id='" . $ibforums->input['f'] . "'");
        } else {
            $DB->query("SELECT t.tid, f.id as fid, f.read_perms, f.password FROM ibf_topics t, ibf_forums f WHERE t.tid='" . $ibforums->input['t'] . "' AND t.forum_id=f.id");
        }

        $this->topic = $DB->fetch_row();

        //------------------------------------------------------

        // Error out if we can not find the forum

        //------------------------------------------------------

        if (!$this->topic['fid']) {
            if (1 != $is_sub) {
                $std->Error([LEVEL => 1, MSG => 'missing_files']);
            } else {
                return;
            }
        }

        //------------------------------------------------------

        // Error out if we can not find the topic

        //------------------------------------------------------

        if ('forum' != $this->type) {
            if (!$this->topic['tid']) {
                if (1 != $is_sub) {
                    $std->Error([LEVEL => 1, MSG => 'missing_files']);
                } else {
                    return;
                }
            }
        }

        $this->base_url = "{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}";

        $this->base_url_NS = "{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}";

        //------------------------------------------------------

        // Check viewing permissions, private forums,

        // password forums, etc

        //------------------------------------------------------

        if (!$ibforums->member['uid']) {
            if (1 != $is_sub) {
                $std->Error([LEVEL => 1, MSG => 'no_guests']);
            } else {
                return;
            }
        }

        if ('*' != $this->topic['read_perms']) {
            if (!preg_match('/(^|,)' . $ibforums->member['mgroup'] . '(,|$)/', $this->topic['read_perms'])) {
                if (1 != $is_sub) {
                    $std->Error([LEVEL => 1, MSG => 'forum_no_access']);
                } else {
                    return;
                }
            }
        }

        if ('' != $this->topic['password']) {
            if (!$c_pass = $std->my_getcookie('iBForum' . $this->topic['fid'])) {
                $std->Error([LEVEL => 1, MSG => 'forum_no_access']);
            }

            if ($c_pass != $this->topic['password']) {
                $std->Error([LEVEL => 1, MSG => 'forum_no_access']);
            }
        }

        //------------------------------------------------------

        // Have we already subscribed?

        //------------------------------------------------------

        if ('forum' == $this->type) {
            $DB->query("SELECT frid from ibf_forum_tracker WHERE forum_id='" . $this->topic['fid'] . "' AND member_id='" . $ibforums->member['uid'] . "'");
        } else {
            $DB->query("SELECT trid from ibf_tracker WHERE topic_id='" . $this->topic['tid'] . "' AND member_id='" . $ibforums->member['uid'] . "'");
        }

        if ($DB->get_num_rows()) {
            if (1 != $is_sub) {
                $std->Error([LEVEL => 1, MSG => 'already_sub']);
            } else {
                return;
            }
        }

        //------------------------------------------------------

        // Add it to the DB

        //------------------------------------------------------

        if ('forum' == $this->type) {
            $db_string = $DB->compile_db_insert_string(
                [
                    'member_id' => $ibforums->member['uid'],
'forum_id' => $this->topic['fid'],
'start_date' => time(),
                ]
            );

            $DB->query('INSERT INTO ibf_forum_tracker (' . $db_string['FIELD_NAMES'] . ') VALUES (' . $db_string['FIELD_VALUES'] . ')');
        } else {
            $db_string = $DB->compile_db_insert_string(
                [
                    'member_id' => $ibforums->member['uid'],
'topic_id' => $this->topic['tid'],
'start_date' => time(),
                ]
            );

            $DB->query('INSERT INTO ibf_tracker (' . $db_string['FIELD_NAMES'] . ') VALUES (' . $db_string['FIELD_VALUES'] . ')');
        }

        if (1 != $is_sub) {
            if ('forum' == $this->type) {
                $print->redirect_screen($ibforums->lang['sub_added'], "act=SF&f={$this->topic['fid']}");
            } else {
                $print->redirect_screen($ibforums->lang['sub_added'], "act=ST&f={$this->topic['fid']}&t={$this->topic['tid']}&st={$ibforums->input['st']}");
            }
        } else {
            return;
        }
    }
}








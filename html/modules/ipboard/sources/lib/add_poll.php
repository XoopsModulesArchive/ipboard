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
|   > Add POLL module
|   > Module written by Matt Mecham
|
+--------------------------------------------------------------------------
|
|   QUOTE OF THE MODULE: (Taken from BtVS)
|   --------------------
|	Drusilla: I'm naming all the stars...
|   Spike: You can't see the stars love, That's the ceiling. Also, it's day.
|
+--------------------------------------------------------------------------
*/

$idx = new Poll();

class Poll
{
    public $topic = [];

    public $poll = [];

    public $upload = [];

    public $poll_count = 0;

    public $poll_choices = '';

    public function __construct()
    {
        global $ibforums, $std, $DB, $print;

        $ibforums->lang = $std->load_words($ibforums->lang, 'lang_post', $ibforums->lang_id);

        // Lets do some tests to make sure that we are allowed to start a new topic

        if (!$ibforums->member['g_vote_polls']) {
            $std->Error([LEVEL => 1, MSG => 'no_reply_polls']);
        }

        // Did we choose a choice?

        if (!$ibforums->input['nullvote']) {
            if (!isset($ibforums->input['poll_vote'])) {
                $std->Error([LEVEL => 1, MSG => 'no_vote']);
            }
        }

        // Make sure we have a valid poll id

        $ibforums->input[t] = $std->is_number($ibforums->input[t]);

        if (!$ibforums->input[t]) {
            $std->Error([LEVEL => 1, MSG => 'missing_files']);
        }

        // Load the topic and poll

        $DB->query("SELECT t.*, p.pid as poll_id,p.choices,p.starter_id,p.votes from ibf_polls p, ibf_topics t WHERE t.tid='" . $ibforums->input['t'] . "' and p.tid=t.tid");

        $this->topic = $DB->fetch_row();

        if (!$this->topic['tid']) {
            $std->Error([LEVEL => 1, MSG => 'poll_none_found']);
        }

        if ('open' != $this->topic['state']) {
            $std->Error([LEVEL => 1, MSG => 'locked_topic']);
        }

        // Have we voted before?

        $DB->query("SELECT member_id from ibf_voters WHERE tid='" . $this->topic['tid'] . "' and member_id='" . $ibforums->member['uid'] . "'");

        if ($DB->get_num_rows()) {
            $std->Error([LEVEL => 1, MSG => 'poll_you_voted']);
        }

        // If we're here, lets add the vote

        $db_string = $std->compile_db_string(
            [
                'member_id' => $ibforums->member['uid'],
'ip_address' => $ibforums->input['IP_ADDRESS'],
'tid' => $this->topic['tid'],
'forum_id' => $this->topic['forum_id'],
'vote_date' => time(),
            ]
        );

        $DB->query('INSERT INTO ibf_voters (' . $db_string['FIELD_NAMES'] . ') VALUES (' . $db_string['FIELD_VALUES'] . ')');

        // If this isn't a null vote...

        if (!$ibforums->input['nullvote']) {
            $poll_answers = unserialize(stripslashes($this->topic['choices']));

            reset($poll_answers);

            $new_poll_array = [];

            foreach ($poll_answers as $entry) {
                $id = $entry[0];

                $choice = $entry[1];

                $votes = $entry[2];

                if ($id == $ibforums->input['poll_vote']) {
                    $votes++;
                }

                $new_poll_array[] = [$id, $choice, $votes];
            }

            $this->topic['choices'] = addslashes(serialize($new_poll_array));

            $DB->query(
                'UPDATE ibf_polls SET ' . 'votes=votes+1, ' . "choices='" . $this->topic['choices'] . "' " . "WHERE pid='" . $this->topic['poll_id'] . "'"
            );

            if ($ibforums->vars['allow_poll_bump']) {
                $this->topic['last_vote'] = time();

                $this->topic['last_post'] = time();

                $DB->query(
                    'UPDATE ibf_topics SET ' . "last_vote='" . $this->topic['last_vote'] . "', " . "last_post='" . $this->topic['last_post'] . "' " . "WHERE tid='" . $this->topic['tid'] . "'"
                );
            } else {
                $this->topic['last_vote'] = time();

                $DB->query(
                    'UPDATE ibf_topics SET ' . "last_vote='" . $this->topic['last_vote'] . "', " . "last_post='" . $this->topic['last_post'] . "' " . "WHERE tid='" . $this->topic['tid'] . "'"
                );
            }
        }

        $lang = $ibforums->input['nullvote'] ? $ibforums->lang['poll_viewing_results'] : $ibforums->lang['poll_vote_added'];

        $print->redirect_screen($lang, "act=ST&f={$this->topic['forum_id']}&t={$this->topic['tid']}");
    }
}

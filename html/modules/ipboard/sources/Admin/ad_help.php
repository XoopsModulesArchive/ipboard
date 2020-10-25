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
|   > Help Control functions
|   > Module written by Matt Mecham
|   > Date started: 2nd April 2002
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

$idx = new ad_settings();

class ad_settings
{
    public $base_url;

    public function __construct()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        //---------------------------------------

        // Kill globals - globals bad, Homer good.

        //---------------------------------------

        $tmp_in = array_merge($_GET, $_POST, $_COOKIE);

        foreach ($tmp_in as $k => $v) {
            unset($$k);
        }

        //---------------------------------------

        switch ($IN['code']) {
            case 'edit':
                $this->show_form('edit');
                break;
            case 'new':
                $this->show_form('new');
                break;
            case 'doedit':
                $this->doedit();
                break;
            case 'donew':
                $this->doadd();
                break;
            case 'remove':
                $this->remove();
                break;
            //-------------------------
            default:
                $this->list_files();
                break;
        }
    }

    //-------------------------------------------------------------

    // HELP FILE FUNCTIONS

    //-------------------------------------------------------------

    public function doedit()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $_POST;

        if ('' == $IN['id']) {
            $ADMIN->error('You must pass a valid emoticon id, silly!');
        }

        $text = preg_replace("/\n/", '<br>', stripslashes($_POST['text']));

        $title = preg_replace("/\n/", '<br>', stripslashes($_POST['title']));

        $desc = preg_replace("/\n/", '<br>', stripslashes($_POST['description']));

        $db_string = $DB->compile_db_update_string(
            [
                'title' => $title,
'text' => $text,
'description' => $desc,
            ]
        );

        $DB->query("UPDATE ibf_faq SET $db_string WHERE id='" . $IN['id'] . "'");

        $ADMIN->save_log('Edited help files');

        $std->boink_it($SKIN->base_url . '&act=help');

        exit();
    }

    //=====================================================

    public function show_form($type = 'new')
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        $ADMIN->page_detail = 'You may add/edit and remove help files below.';

        $ADMIN->page_title = 'Help File Management';

        //+-------------------------------

        if ('new' != $type) {
            if ('' == $IN['id']) {
                $ADMIN->error('You must pass a valid help file id, silly!');
            }

            //+-------------------------------

            $DB->query("SELECT * FROM ibf_faq WHERE id='" . $IN['id'] . "'");

            if (!$r = $DB->fetch_row()) {
                $ADMIN->error('We could not find that help file in the database');
            }

            //+-------------------------------

            $button = 'Edit this Help File';

            $code = 'doedit';
        } else {
            $r = [];

            $button = 'Add this Help File';

            $code = 'donew';
        }

        $ADMIN->html .= $SKIN->start_form(
            [
                1 => ['code', $code],
2 => ['act', 'help'],
3 => ['id', $IN['id']],
            ]
        );

        $SKIN->td_header[] = ['&nbsp;', '20%'];

        $SKIN->td_header[] = ['&nbsp;', '80%'];

        $r['text'] = preg_replace('/<br>/i', "\n", stripslashes($r['text']));

        //+-------------------------------

        $ADMIN->html .= $SKIN->start_table($button);

        $ADMIN->html .= $SKIN->add_td_row(
            [
                'Help File Title',
                $SKIN->form_input('title', stripslashes($r['title'])),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                'Help File Description',
                $SKIN->form_textarea('description', stripslashes($r['description'])),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                'Help File Text',
                $SKIN->form_textarea('text', $r['text'], '60', '10'),
            ]
        );

        $ADMIN->html .= $SKIN->end_form($button);

        $ADMIN->html .= $SKIN->end_table();

        $ADMIN->output();
    }

    //=====================================================

    public function remove()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        if ('' == $IN['id']) {
            $ADMIN->error('You must pass a valid help file id, silly!');
        }

        $DB->query("DELETE FROM ibf_faq WHERE id='" . $IN['id'] . "'");

        $ADMIN->save_log('Removed a help file');

        $std->boink_it($SKIN->base_url . '&act=help');

        exit();
    }

    //=====================================================

    public function doadd()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $_POST;

        if ('' == $IN['title']) {
            $ADMIN->error('You must enter a title, silly!');
        }

        $text = preg_replace("/\n/", '<br>', stripslashes($_POST['text']));

        $title = preg_replace("/\n/", '<br>', stripslashes($_POST['title']));

        $desc = preg_replace("/\n/", '<br>', stripslashes($_POST['description']));

        $db_string = $DB->compile_db_insert_string(
            [
                'title' => $title,
'text' => $text,
'description' => $desc,
            ]
        );

        $DB->query('INSERT INTO ibf_faq (' . $db_string['FIELD_NAMES'] . ') VALUES(' . $db_string['FIELD_VALUES'] . ')');

        $ADMIN->save_log('Added a help file');

        $std->boink_it($SKIN->base_url . '&act=help');

        exit();
    }

    //=====================================================

    public function list_files()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        $ADMIN->page_detail = 'You may add/edit and remove help files below.';

        $ADMIN->page_title = 'Help File Management';

        //+-------------------------------

        $SKIN->td_header[] = ['Title', '50%'];

        $SKIN->td_header[] = ['Edit', '30%'];

        $SKIN->td_header[] = ['Remove', '20%'];

        //+-------------------------------

        $ADMIN->html .= $SKIN->start_table('Current Help Files');

        $DB->query('SELECT * FROM ibf_faq ORDER BY id ASC');

        if ($DB->get_num_rows()) {
            while (false !== ($r = $DB->fetch_row())) {
                $ADMIN->html .= $SKIN->add_td_row(
                    [
                        '<b>' . stripslashes($r['title']) . '</b><br>' . stripslashes($r['description']),
                        "<center><a href='" . $SKIN->base_url . "&act=help&code=edit&id={$r['id']}'>Edit</a></center>",
                        "<center><a href='" . $SKIN->base_url . "&act=help&code=remove&id={$r['id']}'>Remove</a></center>",
                    ]
                );
            }
        }

        $ADMIN->html .= $SKIN->add_td_basic("<a href='" . $SKIN->base_url . "&act=help&code=new'>Add New Help File</a>", 'center', 'title');

        $ADMIN->html .= $SKIN->end_table();

        //+-------------------------------

        $ADMIN->output();
    }
}

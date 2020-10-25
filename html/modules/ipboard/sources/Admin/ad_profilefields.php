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
|   > Custom profile field functions
|   > Module written by Matt Mecham
|   > Date started: 24th June 2002
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

$idx = new ad_fields();

class ad_fields
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

        switch ($IN['code']) {
            case 'add':
                $this->main_form('add');
                break;
            case 'doadd':
                $this->main_save('add');
                break;
            case 'edit':
                $this->main_form('edit');
                break;
            case 'doedit':
                $this->main_save('edit');
                break;
            case 'delete':
                $this->delete_form();
                break;
            case 'dodelete':
                $this->do_delete();
                break;
            default:
                $this->main_screen();
                break;
        }
    }

    //+---------------------------------------------------------------------------------

    // Delete a group

    //+---------------------------------------------------------------------------------

    public function delete_form()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        if ('' == $IN['id']) {
            $ADMIN->error('Could not resolve the group ID, please try again');
        }

        $ADMIN->page_title = 'Deleting a Custom Profile Field';

        $ADMIN->page_detail = 'Please check to ensure that you are attempting to remove the correct custom profile field as <b>all data will be lost!</b>.';

        //+-------------------------------

        $DB->query("SELECT ftitle, fid FROM ibf_pfields_data WHERE fid='" . $IN['id'] . "'");

        if (!$field = $DB->fetch_row()) {
            $ADMIN->error('Could not fetch the row from the database');
        }

        $ADMIN->html .= $SKIN->start_form(
            [
                1 => ['code', 'dodelete'],
2 => ['act', 'field'],
3 => ['id', $IN['id']],
            ]
        );

        //+-------------------------------

        $SKIN->td_header[] = ['&nbsp;', '40%'];

        $SKIN->td_header[] = ['&nbsp;', '60%'];

        //+-------------------------------

        $ADMIN->html .= $SKIN->start_table('Removal Confirmation');

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Custom Profile field to remove</b>',
                '<b>' . $field['ftitle'] . '</b>',
            ]
        );

        $ADMIN->html .= $SKIN->end_form('Delete this custom field');

        $ADMIN->html .= $SKIN->end_table();

        $ADMIN->output();
    }

    public function do_delete()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        if ('' == $IN['id']) {
            $ADMIN->error('Could not resolve the field ID, please try again');
        }

        // Check to make sure that the relevant groups exist.

        $DB->query("SELECT ftitle, fid FROM ibf_pfields_data WHERE fid='" . $IN['id'] . "'");

        if (!$row = $DB->fetch_row()) {
            $ADMIN->error("Could not resolve the ID's passed to deletion");
        }

        $DB->query("ALTER TABLE ibf_pfields_content DROP field_{$row['fid']}");

        $DB->query("DELETE FROM ibf_pfields_data WHERE fid='" . $IN['id'] . "'");

        $ADMIN->done_screen('Profile Field Removed', 'Custom Profile Field Control', 'act=field');
    }

    //+---------------------------------------------------------------------------------

    // Save changes to DB

    //+---------------------------------------------------------------------------------

    public function main_save($type = 'edit')
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $_POST;

        if ('' == $IN['ftitle']) {
            $ADMIN->error('You must enter a field title.');
        }

        if ('edit' == $type) {
            if ('' == $IN['id']) {
                $ADMIN->error('Could not resolve the field id');
            }
        }

        $content = '';

        if ('' != $_POST['fcontent']) {
            $content = str_replace("\n", '|', str_replace("\n\n", "\n", trim($_POST['fcontent'])));
        }

        $db_string = [
            'ftitle' => $IN['ftitle'],
'fdesc' => $IN['fdesc'],
'fcontent' => stripslashes($content),
'ftype' => $IN['ftype'],
'freq' => $IN['freq'],
'fhide' => $IN['fhide'],
'fmaxinput' => $IN['fmaxinput'],
'fedit' => $IN['fedit'],
'forder' => $IN['forder'],
'fshowreg' => $IN['fshowreg'],
        ];

        if ('edit' == $type) {
            $rstring = $DB->compile_db_update_string($db_string);

            $DB->query("UPDATE ibf_pfields_data SET $rstring WHERE fid='" . $IN['id'] . "'");

            $ADMIN->done_screen('Profile Field Edited', 'Custom Profile Field Control', 'act=field');
        } else {
            $rstring = $DB->compile_db_insert_string($db_string);

            $DB->query('INSERT INTO ibf_pfields_data (' . $rstring['FIELD_NAMES'] . ') VALUES (' . $rstring['FIELD_VALUES'] . ')');

            $new_id = $DB->get_insert_id();

            $DB->query("ALTER TABLE ibf_pfields_content ADD field_$new_id text default ''");

            $DB->query('OPTIMIZE TABLE ibf_pfields_content');

            $ADMIN->done_screen('Profile Field Added', 'Custom Profile Field Control', 'act=field');
        }
    }

    //+---------------------------------------------------------------------------------

    // Add / edit group

    //+---------------------------------------------------------------------------------

    public function main_form($type = 'edit')
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        if ('edit' == $type) {
            if ('' == $IN['id']) {
                $ADMIN->error('No group id to select from the database, please try again.');
            }

            $form_code = 'doedit';

            $button = 'Complete Edit';
        } else {
            $form_code = 'doadd';

            $button = 'Add Field';
        }

        if ('' != $IN['id']) {
            $DB->query("SELECT * FROM ibf_pfields_data WHERE fid='" . $IN['id'] . "'");

            $fields = $DB->fetch_row();
        } else {
            $fields = [];
        }

        if ('edit' == $type) {
            $ADMIN->page_title = 'Editing Profile Field ' . $fields['ftitle'];
        } else {
            $ADMIN->page_title = 'Adding a new profile field';

            $fields['ftitle'] = '';
        }

        $ADMIN->page_detail = 'Please double check the information before submitting the form.';

        $ADMIN->html .= $SKIN->start_form(
            [
                1 => ['code', $form_code],
2 => ['act', 'field'],
3 => ['id', $IN['id']],
            ]
        );

        $fields['fcontent'] = str_replace('|', "\n", $fields['fcontent']);

        //+-------------------------------

        $SKIN->td_header[] = ['&nbsp;', '40%'];

        $SKIN->td_header[] = ['&nbsp;', '60%'];

        //+-------------------------------

        $ADMIN->html .= $SKIN->start_table('Field Settings');

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Field Title</b><br>Max characters: 200',
                $SKIN->form_input('ftitle', $fields['ftitle']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Description</b><br>Max Characters: 250<br>Can be used to note hidden/required status',
                $SKIN->form_input('fdesc', $fields['fdesc']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Field Type</b>',
                $SKIN->form_dropdown(
                    'ftype',
                    [
                        0 => ['text', 'Text Input'],
1 => ['drop', 'Drop Down Box'],
2 => ['area', 'Text Area'],
                    ],
                    $fields['ftype']
                ),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Max Input (for text input and text areas) in characters</b>',
                $SKIN->form_input('fmaxinput', $fields['fmaxinput']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Display order (when editing and displaying) numeric 1 lowest.',
                $SKIN->form_input('forder', $fields['forder']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                "<b>Option Content (for drop downs)</b><br>In sets, one set per line<br>Example for 'Gender' field:<br>m=Male<br>f=Female<br>u=Not Telling<br>Will produce:<br><select name='pants'><option value='m'>Male</option><option value='f'>Female</option><option value='u'>Not Telling</option></select><br>m,f or u stored in database. When showing field in profile, will use value from pair (f=Female, shows 'Female')",
                $SKIN->form_textarea('fcontent', $fields['fcontent']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Show on registration page also?</b>',
                $SKIN->form_yes_no('fshowreg', $fields['fshowreg']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Field cannot be left blank?</b><br>(Will not apply if you choose to hide below)',
                $SKIN->form_yes_no('freq', $fields['freq']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Hidden to profile viewers?</b><br>If yes, only admins and super mods can see it, user can still edit.',
                $SKIN->form_yes_no('fhide', $fields['fhide']),
            ]
        );

        $ADMIN->html .= $SKIN->add_td_row(
            [
                '<b>Editable by user?</b><br>If no, user cannot edit information, field can only be seen by admins and super mods. Admins can edit information via ACP',
                $SKIN->form_yes_no('fedit', $fields['fedit']),
            ]
        );

        $ADMIN->html .= $SKIN->end_form($button);

        $ADMIN->html .= $SKIN->end_table();

        $ADMIN->output();
    }

    //+---------------------------------------------------------------------------------

    // Show "Management Screen

    //+---------------------------------------------------------------------------------

    public function main_screen()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        $ADMIN->page_title = 'Custom Profile Fields';

        $ADMIN->page_detail = 'Custom Profile fields can be used to add optional or required fields to be completed when registering or editing a profile. This is useful if you wish to record data from your members that is not already present in the base board.';

        $SKIN->td_header[] = ['Field Title', '30%'];

        $SKIN->td_header[] = ['Type', '20%'];

        $SKIN->td_header[] = ['REQUIRED', '10%'];

        $SKIN->td_header[] = ['HIDDEN', '10%'];

        $SKIN->td_header[] = ['SHOW REG', '10%'];

        $SKIN->td_header[] = ['Edit', '10%'];

        $SKIN->td_header[] = ['Delete', '10%'];

        //+-------------------------------

        $ADMIN->html .= $SKIN->start_table('Custom Profile Field Management');

        $real_types = [
            'drop' => 'Drop Down Box',
'area' => 'Text Area',
'text' => 'Text Input',
        ];

        $DB->query('SELECT * FROM ibf_pfields_data');

        if ($DB->get_num_rows()) {
            while (false !== ($r = $DB->fetch_row())) {
                $hide = '&nbsp;';

                $req = '&nbsp;';

                $regi = '&nbsp;';

                "<center><a href='{$ADMIN->base_url}&act=group&code=delete&id=" . $r['g_id'] . "'>Delete</a></center>";

                //-----------------------------------

                if (1 == $r['fhide']) {
                    $hide = '<center><span style="color:red">Y</span></center>';
                }

                //-----------------------------------

                if (1 == $r['freq']) {
                    $req = '<center><span style="color:red">Y</span></center>';
                }

                if (1 == $r['fshowreg']) {
                    $regi = '<center><span style="color:red">Y</span></center>';
                }

                $ADMIN->html .= $SKIN->add_td_row(
                    [
                        "<b>{$r['ftitle']}</b>",
                        "<center>{$real_types[$r['ftype']]}</center>",
                        $req,
                        $hide,
                        $regi,
                        "<center><a href='{$ADMIN->base_url}&act=field&code=edit&id=" . $r['fid'] . "'>Edit</a></center>",
                        "<center><a href='{$ADMIN->base_url}&act=field&code=delete&id=" . $r['fid'] . "'>Delete</a></center>",
                    ]
                );
            }
        } else {
            $ADMIN->html .= $SKIN->add_td_basic('None found', 'center', 'tdrow2');
        }

        $ADMIN->html .= $SKIN->add_td_basic("<a href='{$ADMIN->base_url}&act=field&code=add'>ADD NEW FIELD</a></center>", 'center', 'title');

        $ADMIN->html .= $SKIN->end_table();

        $ADMIN->output();
    }
}

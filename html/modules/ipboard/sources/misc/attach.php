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
|   > Attachment Handler module
|   > Module written by Matt Mecham
|   > Date started: 10th March 2002
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

$idx = new attach();

class attach
{
    public function __construct()
    {
        global $ibforums, $DB, $std, $print, $skin_universal;

        $ibforums->input['id'] = preg_replace("/^(\d+)$/", '\\1', $ibforums->input['id']);

        if ('' == $ibforums->input['id']) {
            $std->Error(['LEVEL' => 1, 'MSG' => 'missing_files']);
        }

        if ('post' == $ibforums->input['type']) {
            // Handle post attachments.

            $DB->query("SELECT pid, attach_id, attach_type, attach_file FROM ibf_posts WHERE pid='" . $ibforums->input['id'] . "'");

            if (!$DB->get_num_rows()) {
                $std->Error(['LEVEL' => 1, 'MSG' => 'missing_files']);
            }

            $post = $DB->fetch_row();

            if ('' == $post['attach_id']) {
                $std->Error(['LEVEL' => 1, 'MSG' => 'missing_files']);
            }

            $file = $ibforums->vars['upload_dir'] . '/' . $post['attach_id'];

            if (file_exists($file) and ('' != $post['attach_type'])) {
                // Update the "hits"..

                $DB->query("UPDATE ibf_posts SET attach_hits=attach_hits+1 WHERE pid='" . $post['pid'] . "'");

                // Set up the headers..

                @header('Content-Type: ' . $post['attach_type'] . "\nContent-Disposition: inline; filename=\"" . $post['attach_file'] . "\"\nContent-Length: " . (string)(filesize($file)));

                // Open and display the file..

                $fh = fopen($file, 'rb');  // Set binary for Win even if it's an ascii file, it won't hurt.

                fpassthru($fh);

                @fclose($fh);
            } else {
                // File does not exist..

                $std->Error(['LEVEL' => 1, 'MSG' => 'missing_files']);
            }
        }
    }
}








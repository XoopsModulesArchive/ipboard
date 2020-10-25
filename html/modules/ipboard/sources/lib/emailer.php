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
|   > Sending email module
|   > Module written by Matt Mecham
|   > Date started: 26th February 2002
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
|
|   QUOTE OF THE MODULE: (Taken from "Shrek" (c) Dreamworks Pictures)
|   --------------------
|	DONKEY: We can stay up late, swap manly stories and in the morning,
|           I'm making waffles!
|
+--------------------------------------------------------------------------
*/

// This module is fairly basic, more functionality is expected in future
// versions (such as MIME attachments, SMTP stuff, etc)

class emailer
{
    public $from = '';

    public $to = '';

    public $subject = '';

    public $message = '';

    public $header = '';

    public $footer = '';

    public $template = '';

    public $error = '';

    public $parts = [];

    public $bcc = [];

    public $mail_headers = [];

    public $multipart = '';

    public $boundry = '';

    public $smtp_fp = false;

    public $smtp_msg = '';

    public $smtp_port = '';

    public $smtp_host = 'localhost';

    public $smtp_user = '';

    public $smtp_pass = '';

    public $smtp_code = '';

    public $mail_method = 'mail';

    public $temp_dump = 0;

    public function __construct()
    {
        global $ibforums;

        //---------------------------------------------------------

        // Assign $from as the admin out email address, this can be

        // over-riden at any time.

        //---------------------------------------------------------

        $this->from = $ibforums->vars['email_out'];

        $this->temp_dump = $ibforums->vars['fake_mail'];

        //---------------------------------------------------------

        // Set up SMTP if we're using it

        //---------------------------------------------------------

        if ('smtp' == $ibforums->vars['mail_method']) {
            $this->mail_method = 'smtp';

            $this->smtp_port = ('' != (int)$ibforums->vars['smtp_port']) ? (int)$ibforums->vars['smtp_port'] : 25;

            $this->smtp_host = ('' != $ibforums->vars['smtp_host']) ? $ibforums->vars['smtp_host'] : 'localhost';

            $this->smtp_user = $ibforums->vars['smtp_user'];

            $this->smtp_pass = $ibforums->vars['smtp_pass'];
        }

        //---------------------------------------------------------

        // Temporarily assign $header and $footer, this can be over-riden

        // also

        //---------------------------------------------------------

        $this->header = $ibforums->vars['email_header'];

        $this->footer = $ibforums->vars['email_footer'];

        $this->boundry = '----=_NextPart_000_0022_01C1BD6C.D0C0F9F0';  //"b".md5(uniqid(time()));

        $ibforums->vars['board_name'] = $this->clean_message($ibforums->vars['board_name']);
    }

    public function add_attachment($data = '', $name = '', $ctype = 'application/octet-stream')
    {
        $this->parts[] = [
            'ctype' => $ctype,
'data' => $data,
'encode' => 'base64',
'name' => $name,
        ];
    }

    public function build_headers()
    {
        global $ibforums;

        $this->mail_headers = 'From: "' . $ibforums->vars['board_name'] . '" <' . $this->from . ">\n";

        if ('smtp' != $this->mail_method) {
            if (count($this->bcc) > 1) {
                $this->mail_headers .= 'Bcc: ' . implode(',', $this->bcc) . "\n";
            }
        } else {
            if ($this->to) {
                $this->mail_headers .= 'To: ' . $this->to . "\n";
            }

            $this->mail_headers .= 'Subject: ' . $this->subject . "\n";
        }

        $this->mail_headers .= 'Return-Path: ' . $this->from . "\n";

        $this->mail_headers .= "X-Priority: 3\n";

        $this->mail_headers .= "X-Mailer: IBForums PHP Mailer\n";

        if (count($this->parts) > 0) {
            $this->mail_headers .= "MIME-Version: 1.0\n";

            $this->mail_headers .= "Content-Type: multipart/mixed;\n\tboundary=\"" . $this->boundry . "\"\n\nThis is a MIME encoded message.\n\n--" . $this->boundry;

            $this->mail_headers .= "\nContent-Type: text/plain;\n\tcharset=\"iso-8859-1\"\nContent-Transfer-Encoding: quoted-printable\n\n" . $this->message . "\n\n--" . $this->boundry;

            $this->mail_headers .= $this->build_multipart();

            $this->message = '';
        }
    }

    public function encode_attachment($part)
    {
        $msg = chunk_preg_split(base64_encode($part['data']));

        return 'Content-Type: ' . $part['ctype'] . ($part['name'] ? ";\n\tname =\"" . $part['name'] . '"' : '') . "\nContent-Transfer-Encoding: " . $part['encode'] . "\nContent-Disposition: attachment;\n\tfilename=\"" . $part['name'] . "\"\n\n" . $msg . "\n";
    }

    public function build_multipart()
    {
        $multipart = '';

        for ($i = count($this->parts) - 1; $i >= 0; $i--) {
            $multipart .= "\n" . $this->encode_attachment($this->parts[$i]) . '--' . $this->boundry;
        }

        return $multipart . "--\n";
    }

    //+--------------------------------------------------------------------------

    // send_mail:

    // Physically sends the email

    //+--------------------------------------------------------------------------

    public function send_mail()
    {
        global $ibforums;

        $this->to = preg_replace("/[ \t]+/", ' ', $this->to);

        $this->from = preg_replace("/[ \t]+/", ' ', $this->from);

        $this->to = preg_replace('/,,/', ',', $this->to);

        $this->from = preg_replace('/,,/', ',', $this->from);

        $this->to = preg_replace("#\#\[\]'\"\(\):;/\$!£%\^&\*\{\}#", '', $this->to);

        $this->from = preg_replace("#\#\[\]'\"\(\):;/\$!£%\^&\*\{\}#", '', $this->from);

        $this->subject = $this->clean_message($this->subject);

        $this->build_headers();

        if (($this->from) and ($this->subject)) {
            $this->subject .= ' ( From ' . $ibforums->vars['board_name'] . ' )';

            if (1 == $this->temp_dump) {
                $blah = $this->subject . "\n------------\n" . $this->mail_headers . "\n\n" . $this->message;

                $pathy = '/Library/WebServer/Documents/mail/' . date('F.Y.h:i.A') . '.txt'; // OS X rules!

                $fh = fopen($pathy, 'wb');

                fwrite($fh, $blah, mb_strlen($blah));

                fclose($fh);
            } else {
                if ('smtp' != $this->mail_method) {
                    if (!@mail($this->to, $this->subject, $this->message, $this->mail_headers)) {
                        $this->fatal_error('Could not sent the email', "Failed at 'mail' command");
                    }
                } else {
                    $this->smtp_send_mail();
                }
            }
        } else {
            return false;
        }
    }

    //+--------------------------------------------------------------------------

    // get_template:

    // Queries the database, and stores the template we wish to use in memory

    //+--------------------------------------------------------------------------

    public function get_template($name = '', $language = '')
    {
        global $ibforums, $IB, $DB;

        if ('' == $name) {
            $this->error++;

            $this->fatal_error('A valid email template ID was not passed to the email library during template parsing', '');
        }

        if ('' == $ibforums->vars['default_language']) {
            $ibforums->vars['default_language'] = 'en';
        }

        if ('' == $language) {
            $language = $ibforums->vars['default_language'];
        }

        if (!file_exists("./lang/$language/email_content.php")) {
            require './lang/' . $ibforums->vars['default_language'] . '/email_content.php';
        } else {
            require "./lang/$language/email_content.php";
        }

        if (!isset($EMAIL[$name])) {
            $this->fatal_error("Could not find an email template with an ID of '$name'", '');
        }

        $this->template = $EMAIL['header'] . $EMAIL[$name] . $EMAIL['footer'];
    }

    //+--------------------------------------------------------------------------

    // build_message:

    // Swops template tags into the corresponding string held in $words array.

    // Also joins header and footer to message and cleans the message for sending

    //+--------------------------------------------------------------------------

    public function build_message($words)
    {
        global $ibforums;

        if ('' == $this->template) {
            $this->error++;

            $this->fatal_error('Could not build the email message, no template assigned', 'Make sure a template is assigned first.');
        }

        $this->message = $this->template;

        // Add some default words

        $words['BOARD_ADDRESS'] = $ibforums->vars['board_url'] . '/index.' . $ibforums->vars['php_ext'];

        $words['WEB_ADDRESS'] = $ibforums->vars['home_url'];

        $words['BOARD_NAME'] = $ibforums->vars['board_name'];

        $words['SIGNATURE'] = $ibforums->vars['signature'];

        // Swop the words

        $this->message = preg_replace('/<#(.+?)#>/e', '$words[\\1]', $this->message);

        $this->message = $this->clean_message($this->message);
    }

    //+--------------------------------------------------------------------------

    // clean_message: (Mainly used internally)

    // Ensures that \n and <br> are converted into CRLF (\r\n)

    // Also unconverts some iB_CODE.

    //+--------------------------------------------------------------------------

    public function clean_message($message = '')
    {
        $message = preg_replace("/^(\r|\n)+?(.*)$/", '\\2', $message);

        $message = preg_replace('#<b>(.+?)</b>#', '\\1', $message);

        $message = preg_replace('#<i>(.+?)</i>#', '\\1', $message);

        $message = preg_replace('#<s>(.+?)</s>#', '--\\1--', $message);

        $message = preg_replace('#<u>(.+?)</u>#', '-\\1-', $message);

        $message = preg_replace('#<!--emo&(.+?)-->.+?<!--endemo-->#', '\\1', $message);

        $message = preg_replace('#<!--c1-->(.+?)<!--ec1-->#', "\n\n------------ CODE SAMPLE ----------\n", $message);

        $message = preg_replace('#<!--c2-->(.+?)<!--ec2-->#', "\n-----------------------------------\n\n", $message);

        $message = preg_replace('#<!--QuoteBegin-->(.+?)<!--QuoteEBegin-->#', "\n\n------------ QUOTE ----------\n", $message);

        $message = preg_replace("#<!--QuoteBegin-(.+?)\+(.+?)-->(.+?)<!--QuoteEBegin-->#", "\n\n------------ QUOTE ----------\n", $message);

        $message = preg_replace('#<!--QuoteEnd-->(.+?)<!--QuoteEEnd-->#', "\n-----------------------------\n\n", $message);

        $message = preg_replace('#<!--Flash (.+?)-->.+?<!--End Flash-->#e', '(FLASH MOVIE)', $message);

        $message = preg_replace("#<img src=[\"'](\S+?)['\"].+?" . '>#', '(IMAGE: \\1)', $message);

        $message = preg_replace("#<a href=[\"'](http|https|ftp|news)://(\S+?)['\"].+?" . '>(.+?)</a>#', '(URL: \\1)', $message);

        $message = preg_replace("#<a href=[\"']mailto:(.+?)['\"]>(.+?)</a>#", '(EMAIL: \\2)', $message);

        $message = preg_replace('#<!--sql-->(.+?)<!--sql1-->(.+?)<!--sql2-->(.+?)<!--sql3-->#i', "\n\n--------------- SQL -----------\n\\2\n----------------\n\n", $message);

        $message = preg_replace('#<!--html-->(.+?)<!--html1-->(.+?)<!--html2-->(.+?)<!--html3-->#i', "\n\n-------------- HTML -----------\n\\2\n----------------\n\n", $message);

        $message = preg_replace("#<!--EDIT\|.+?\|.+?-->#", '', $message);

        $message = preg_replace('#<.+?' . '>#', '', $message);

        //$message = str_replace( "\r"  , ""    , $message );

        //$message = str_replace( "\n\n", "\n"  , $message );

        $message = str_replace('<br>', "\n", $message);

        $message = str_replace('&quot;', '"', $message);

        $message = str_replace('&#092;', '\\', $message);

        $message = str_replace('&#036;', '$', $message);

        $message = str_replace('&#33;', '!', $message);

        $message = str_replace('&#39;', "'", $message);

        $message = str_replace('&lt;', '<', $message);

        $message = str_replace('&gt;', '>', $message);

        $message = str_replace('&#124;', '|', $message);

        $message = str_replace('&amp;', '&', $message);

        return $message;
    }

    public function fatal_error($msg, $help = '')
    {
        echo("<h1>Mail Error!</h1><br><b>$msg</b><br>$help");

        exit();
    }

    //---------------------------------------------------------

    // SMTP methods

    //---------------------------------------------------------

    //+------------------------------------

    //| get_line()

    //|

    //| Reads a line from the socket and returns

    //| CODE and message from SMTP server

    //|

    //+------------------------------------

    public function smtp_get_line()
    {
        $this->smtp_msg = '';

        while ($line = fgets($this->smtp_fp, 515)) {
            $this->smtp_msg .= $line;

            if (' ' == mb_substr($line, 3, 1)) {
                break;
            }
        }
    }

    //+------------------------------------

    //| send_cmd()

    //|

    //| Sends a command to the SMTP server

    //| Returns TRUE if response, FALSE if not

    //|

    //+------------------------------------

    public function smtp_send_cmd($cmd)
    {
        $this->smtp_msg = '';

        $this->smtp_code = '';

        fwrite($this->smtp_fp, $cmd . "\r\n");

        $this->smtp_get_line();

        $this->smtp_code = mb_substr($this->smtp_msg, 0, 3);

        return '' == $this->smtp_code ? false : true;
    }

    //+------------------------------------

    //| error()

    //|

    //| Returns SMTP error to our global

    //| handler

    //|

    //+------------------------------------

    public function smtp_error($err = '')
    {
        $this->fatal_error('SMTP protocol failure!</b><br>Host: ' . $this->smtp_host . '<br>Return Code: ' . $this->smtp_code . '<br>Return Msg: ' . $this->smtp_msg . "<br>Invision Power Board Error: $err", 'Check your SMTP settings from the admin control panel');
    }

    //+------------------------------------

    //| crlf_encode()

    //|

    //| RFC 788 specifies line endings in

    //| \r\n format with no periods on a

    //| new line

    //+------------------------------------

    public function smtp_crlf_encode($data)
    {
        $data .= "\n";

        $data = str_replace("\n", "\r\n", str_replace("\r", '', $data));

        $data = str_replace("\n.\r\n", "\n. \r\n", $data);

        return $data;
    }

    //+------------------------------------

    //| send_mail

    //|

    //| Does the bulk of the email sending

    //+------------------------------------

    //$this->to, $this->subject, $this->message, $this->mail_headers

    public function smtp_send_mail()
    {
        $this->smtp_fp = fsockopen($this->smtp_host, (int)$this->smtp_port, $errno, $errstr, 30);

        if (!$this->smtp_fp) {
            $this->smtp_error('Could not open a socket to the SMTP server');
        }

        $this->smtp_get_line();

        $this->smtp_code = mb_substr($this->smtp_msg, 0, 3);

        if (220 == $this->smtp_code) {
            $data = $this->smtp_crlf_encode($this->mail_headers . "\n" . $this->message);

            //---------------------

            // HELO!, er... HELLO!

            //---------------------

            $this->smtp_send_cmd('HELO ' . $this->smtp_host);

            if (250 != $this->smtp_code) {
                $this->smtp_error('HELO');
            }

            //---------------------

            // Do you like my user!

            //---------------------

            if ($this->smtp_user and $this->smtp_pass) {
                $this->smtp_send_cmd('AUTH LOGIN');

                if (334 == $this->smtp_code) {
                    $this->smtp_send_cmd(base64_encode($this->smtp_user));

                    if (334 != $this->smtp_code) {
                        $this->smtp_error('Username not accepted from the server');
                    }

                    $this->smtp_send_cmd(base64_encode($this->smtp_pass));

                    if (235 != $this->smtp_code) {
                        $this->smtp_error('Password not accepted from the server');
                    }
                } else {
                    $this->smtp_error('This server does not support authorisation');
                }
            }

            //---------------------

            // We're from MARS!

            //---------------------

            $this->smtp_send_cmd('MAIL FROM:' . $this->from);

            if (250 != $this->smtp_code) {
                $this->smtp_error();
            }

            $to_arry = [$this->to];

            if (count($this->bcc) > 0) {
                foreach ($this->bcc as $bcc) {
                    if ('' != $bcc) {
                        $to_arry[] = $bcc;
                    }
                }
            }

            //---------------------

            // You are from VENUS!

            //---------------------

            foreach ($to_arry as $to_email) {
                $this->smtp_send_cmd('RCPT TO:' . $to_email);

                if (250 != $this->smtp_code) {
                    $this->smtp_error();

                    break;
                }
            }

            //---------------------

            // SEND MAIL!

            //---------------------

            $this->smtp_send_cmd('DATA');

            if (354 == $this->smtp_code) {
                //$this->smtp_send_cmd( $data );

                fwrite($this->smtp_fp, $data . "\r\n");
            } else {
                $this->smtp_error('Error on write to SMTP server');
            }

            //---------------------

            // GO ON, NAFF OFF!

            //---------------------

            $this->smtp_send_cmd('.');

            if (250 != $this->smtp_code) {
                $this->smtp_error();
            }

            $this->smtp_send_cmd('quit');

            if (221 != $this->smtp_code) {
                $this->smtp_error();
            }

            //---------------------

            // Tubby-bye-bye!

            //---------------------

            @fclose($this->smtp_fp);
        } else {
            $this->smtp_error();
        }
    }
}

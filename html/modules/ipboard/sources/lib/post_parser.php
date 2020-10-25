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
|   > Text processor module
|   > Module written by Matt Mecham
|
+--------------------------------------------------------------------------
*/

class post_parser
{
    public $error = '';

    public $image_count = 0;

    public $emoticon_count = 0;

    public $quote_html = [];

    public $quote_open = 0;

    public $quote_closed = 0;

    public $quote_error = 0;

    public $emoticons = '';

    public $badwords = '';

    public $strip_quotes = '';

    public $in_sig = '';

    public function smilie_length_sort($a, $b)
    {
        if (mb_strlen($a['code']) == mb_strlen($b['code'])) {
            return 0;
        }

        return (mb_strlen($a['code']) > mb_strlen($b['code'])) ? -1 : 1;
    }

    public function word_length_sort($a, $b)
    {
        if (mb_strlen($a['type']) == mb_strlen($b['type'])) {
            return 0;
        }

        return (mb_strlen($a['type']) > mb_strlen($b['type'])) ? -1 : 1;
    }

    public function __construct($load = 0)
    {
        global $ibforums, $DB;

        $this->strip_quotes = $ibforums->vars['strip_quotes'];

        if (0 != $load) {
            // Pre-load the bad words

            $DB->query('SELECT * from ibf_badwords');

            if ($DB->get_num_rows()) {
                while (false !== ($r = $DB->fetch_row())) {
                    $this->badwords[] = [
                        'type' => stripslashes($r['type']),
'swop' => stripslashes($r['swop']),
'm_exact' => $r['m_exact'],
                    ];
                }
            }

            // Pre-load the smilies

            $this->emoticons = [];

            $DB->query('SELECT code, smile_url from xbb_emoticons');

            if ($DB->get_num_rows()) {
                while (false !== ($r = $DB->fetch_row())) {
                    $this->emoticons[] = [
                        'code' => stripslashes($r['code']),
'smile_url' => stripslashes($r['smile_url']),
'clickable' => $r['clickable'],
                    ];
                }
            }
        }
    }

    /**************************************************/

    // PARSE POLL TAGS

    // Converts certain code tags for polling

    /**************************************************/

    public function parse_poll_tags($txt)
    {
        // if you want to parse more tags for polls, simply cut n' paste from the "convert" routine

        // anywhere here.

        $txt = preg_replace("#\[img\](.+?)\[/img\]#ie", "\$this->regex_check_image('\\1')", $txt);

        $txt = preg_replace("#\[url\](\S+?)\[/url\]#ie", "\$this->regex_build_url(array('html' => '\\1', 'show' => '\\1'))", $txt);

        $txt = preg_replace("#\[url\s*=\s*\&quot\;\s*(\S+?)\s*\&quot\;\s*\](.*?)\[\/url\]#ie", "\$this->regex_build_url(array('html' => '\\1', 'show' => '\\2'))", $txt);

        $txt = preg_replace("#\[url\s*=\s*(\S+?)\s*\](.*?)\[\/url\]#ie", "\$this->regex_build_url(array('html' => '\\1', 'show' => '\\2'))", $txt);

        return $txt;
    }

    /**************************************************/

    // convert:

    // Parses raw text into smilies, HTML and iB CODE

    /**************************************************/

    public function convert($in = ['TEXT' => '', 'SMILIES' => 0, 'CODE' => 0, 'SIGNATURE' => 0, 'HTML' => 0])
    {
        global $ibforums, $DB;

        $this->in_sig = $in['SIGNATURE'];

        $txt = $in['TEXT'];

        //--------------------------------------

        // Returns any errors as $this->error

        //--------------------------------------

        // Remove session id's from any post

        $txt = preg_replace("#(\?|&amp;|;|&)s=([0-9a-zA-Z]){32}(&amp;|;|&|$)?#e", "\$this->regex_bash_session('\\1', '\\3')", $txt);

        //--------------------------------------

        // convert <br> to \n

        //--------------------------------------

        $txt = preg_replace("/<br>|<br\s*\>/", "\n", $txt);

        //--------------------------------------

        // Are we parsing iB_CODE and do we have either '[' or ']' in the

        // text we are processing?

        //--------------------------------------

        if (1 == $in['CODE']) {
            //---------------------------------

            // Do [CODE] tag

            //---------------------------------

            $txt = preg_replace("#\[code\](.+?)\[/code\]#ies", "\$this->regex_code_tag('\\1')", $txt);

            //--------------------------------------

            // Auto parse URLs

            //--------------------------------------

            $txt = preg_replace("#(^|\s)((http|https|news|ftp)://\w+[^\s\[\]]+)#ie", "\$this->regex_build_url(array('html' => '\\2', 'show' => '\\2', 'st' => '\\1'))", $txt);

            //---------------------------------

            // Do [QUOTE(name,date)] tags

            //---------------------------------

            // Find the first, and last quote tag (greedy match)...

            $txt = preg_replace("#(\[quote(.+?)?\].*\[/quote\])#ies", "\$this->regex_parse_quotes('\\1')", $txt);

            /***********************************************/ // If we are not parsing a siggie, lets have a bash

            // at the [PHP] [SQL] and [HTML] tags.

            /***********************************************/

            if (1 != $in['SIGNATURE']) {
                $txt = preg_replace("#\[sql\](.+?)\[/sql\]#ies", "\$this->regex_sql_tag('\\1')", $txt);

                $txt = preg_replace("#\[html\](.+?)\[/html\]#ies", "\$this->regex_html_tag('\\1')", $txt);

                // [LIST]    [*]    [/LIST]

                //-------------------------

                $txt = preg_replace("#\[list\]#i", '<ul>', $txt);

                $txt = preg_replace("#\[\*\]#", '<li>', $txt);

                $txt = preg_replace("#\[/list\]#i", '</ul>', $txt);
            }

            //---------------------------------

            // Do [IMG] [FLASH] tags

            //---------------------------------

            if ($ibforums->vars['allow_images']) {
                $txt = preg_replace("#\[img\](.+?)\[/img\]#ie", "\$this->regex_check_image('\\1')", $txt);

                $txt = preg_replace("#(\[flash=)(\S+?)(\,)(\S+?)(\])(\S+?)(\[\/flash\])#ie", "\$this->regex_check_flash('\\2','\\4','\\6')", $txt);
            }

            // Start off with the easy stuff

            $txt = preg_replace("#\[b\](.+?)\[/b\]#is", '<b>\\1</b>', $txt);

            $txt = preg_replace("#\[i\](.+?)\[/i\]#is", '<i>\\1</i>', $txt);

            $txt = preg_replace("#\[u\](.+?)\[/u\]#is", '<u>\\1</u>', $txt);

            $txt = preg_replace("#\[s\](.+?)\[/s\]#is", '<s>\\1</s>', $txt);

            // (c) (r) and (tm)

            $txt = preg_replace("#\(c\)#i", '&copy;', $txt);

            $txt = preg_replace("#\(tm\)#i", '&#153;', $txt);

            $txt = preg_replace("#\(r\)#i", '&reg;', $txt);

            // font size, colour and font style

            // [font=courier]Text here[/font]  [size=6]Text here[/size]  [color=red]Text here[/color]

            while (preg_match("#\[size=([^\]]+)\](.+?)\[/size\]#ies", $txt)) {
                $txt = preg_replace("#\[size=([^\]]+)\](.+?)\[/size\]#ies", "\$this->regex_font_attr(array('s'=>'size','1'=>'\\1','2'=>'\\2'))", $txt);
            }

            while (preg_match("#\[font=([^\]]+)\](.*?)\[/font\]#ies", $txt)) {
                $txt = preg_replace("#\[font=([^\]]+)\](.*?)\[/font\]#ies", "\$this->regex_font_attr(array('s'=>'font','1'=>'\\1','2'=>'\\2'))", $txt);
            }

            while (preg_match("#\[color=([^\]]+)\](.+?)\[/color\]#ies", $txt)) {
                $txt = preg_replace("#\[color=([^\]]+)\](.+?)\[/color\]#ies", "\$this->regex_font_attr(array('s'=>'col' ,'1'=>'\\1','2'=>'\\2'))", $txt);
            }

            // email tags

            // [email]matt@index.com[/email]   [email=matt@index.com]Email me[/email]

            $txt = preg_replace("#\[email\](\S+?)\[/email\]#i", "<a href='mailto:\\1'>\\1</a>", $txt);

            $txt = preg_replace("#\[email\s*=\s*\&quot\;([\.\w\-]+\@[\.\w\-]+\.[\.\w\-]+)\s*\&quot\;\s*\](.*?)\[\/email\]#i", "<a href='mailto:\\1'>\\2</a>", $txt);

            $txt = preg_replace("#\[email\s*=\s*([\.\w\-]+\@[\.\w\-]+\.[\w\-]+)\s*\](.*?)\[\/email\]#i", "<a href='mailto:\\1'>\\2</a>", $txt);

            // url tags

            // [url]http://www.index.com[/url]   [url=http://www.index.com]ibforums![/url]

            $txt = preg_replace("#\[url\](\S+?)\[/url\]#ie", "\$this->regex_build_url(array('html' => '\\1', 'show' => '\\1'))", $txt);

            $txt = preg_replace("#\[url\s*=\s*\&quot\;\s*(\S+?)\s*\&quot\;\s*\](.*?)\[\/url\]#ie", "\$this->regex_build_url(array('html' => '\\1', 'show' => '\\2'))", $txt);

            $txt = preg_replace("#\[url\s*=\s*(\S+?)\s*\](.*?)\[\/url\]#ie", "\$this->regex_build_url(array('html' => '\\1', 'show' => '\\2'))", $txt);
        }

        // Swop \n back to <br>

        $txt = preg_replace("/\n/", '<br>', $txt);

        //+---------------------------------------------------------------------------------------------------

        // Parse smilies (disallow smilies in siggies, or we'll have to query the DB for each post

        // and each signature when viewing a topic, not something that we really want to do.

        //+---------------------------------------------------------------------------------------------------

        if (0 != $in['SMILIES'] and 0 == $in['SIGNATURE']) {
            $txt = ' ' . $txt . ' ';

            if (!is_array($this->emoticons)) {
                $DB->query('SELECT code, smile_url from xbb_emoticons');

                $this->emoticons = [];

                if ($DB->get_num_rows()) {
                    while (false !== ($r = $DB->fetch_row())) {
                        $this->emoticons[] = [
                            'code' => stripslashes($r['code']),
'smile_url' => stripslashes($r['smile_url']),
'clickable' => $r['clickable'],
                        ];
                    }
                }
            }

            usort($this->emoticons, ['post_parser', 'smilie_length_sort']);

            if (count($this->emoticons) > 0) {
                foreach ($this->emoticons as $a_id => $row) {
                    $code = $row['code'];

                    $image = $row['smile_url'];

                    /*$code = str_replace( "&", "&amp;", $code );
                    $code = str_replace( "<", "&lt;" , $code );
                    $code = str_replace( ">", "&gt;" , $code );
                    $code = str_replace( "\"", "&quot;", $code);
                    $code = str_replace( "'", "&#039;"  , $code);
                    $code = str_replace( "!", "&#033;"  , $code);
                    $code = str_replace( "|", "&#124;"  , $code);*/

                    // Make safe for regex &gt;:(

                    $code = preg_quote($code, '/');

                    $txt = preg_replace("!(?<=[^\w&;])$code(?=.\W|\W.|\W$)!ei", "\$this->convert_emoticon('$code', '$image')", $txt);
                }
            }

            if ($ibforums->vars['max_emos']) {
                if ($this->emoticon_count > $ibforums->vars['max_emos']) {
                    $this->error = 'too_many_emoticons';
                }
            }
        }

        if (1 == $in['HTML']) {
            $txt = str_replace('&lt;', '<', $txt);

            $txt = str_replace('&gt;', '>', $txt);

            $txt = str_replace('&quot;', '"', $txt);

            $txt = str_replace('&#039;', "'", $txt);

            $txt = str_replace('&amp;', '&', $txt);
        }

        $txt = $this->bad_words($txt);

        return $txt; //trim($txt);
    }

    /**************************************************/

    // Badwords:

    // Swops naughty, naugty words and stuff

    /**************************************************/

    public function bad_words($text = '')
    {
        global $DB, $ibforums;

        if ('' == $text) {
            return '';
        }

        //--------------------------------

        if (!is_array($this->badwords)) {
            $DB->query('SELECT * from ibf_badwords');

            $this->badwords = [];

            if ($DB->get_num_rows()) {
                while (false !== ($r = $DB->fetch_row())) {
                    $this->badwords[] = [
                        'type' => stripslashes($r['type']),
'swop' => stripslashes($r['swop']),
'm_exact' => $r['m_exact'],
                    ];
                }
            }
        }

        usort($this->badwords, ['post_parser', 'word_length_sort']);

        if (count($this->badwords) > 0) {
            foreach ($this->badwords as $idx => $r) {
                if ('' == $r['swop']) {
                    $replace = '######';
                } else {
                    $replace = $r['swop'];
                }

                //---------------------------

                $r['type'] = preg_quote($r['type'], '/');

                //---------------------------

                if (1 == $r['m_exact']) {
                    $text = preg_replace("/(^|\b)" . $r['type'] . "(\b|!|\?|\.|,|$)/i", (string)$replace, $text);
                } else {
                    $text = preg_replace('/' . $r['type'] . '/i', (string)$replace, $text);
                }
            }
        }

        return $text;
    }

    /**************************************************/

    // unconvert:

    // Parses the HTML back into plain text

    /**************************************************/

    public function unconvert($txt = '', $code = 1, $html = 0)
    {
        if (1 == $code) {
            $txt = preg_replace('#<!--emo&(.+?)-->.+?<!--endemo-->#', '\\1', $txt);

            $txt = preg_replace('#<!--sql-->(.+?)<!--sql1-->(.+?)<!--sql2-->(.+?)<!--sql3-->#e', '$this->unconvert_sql("\\2")', $txt);

            $txt = preg_replace('#<!--html-->(.+?)<!--html1-->(.+?)<!--html2-->(.+?)<!--html3-->#e', '$this->unconvert_htm("\\2")', $txt);

            $txt = preg_replace('#<!--Flash (.+?)-->.+?<!--End Flash-->#e', "\$this->unconvert_flash('\\1')", $txt);

            $txt = preg_replace("#<img src=[\"'](\S+?)['\"].+?" . '>#', "\[IMG\]\\1\[/IMG\]", $txt);

            $txt = preg_replace("#<a href=[\"']mailto:(.+?)['\"]>(.+?)</a>#", "\[EMAIL=\\1\]\\2\[/EMAIL\]", $txt);

            $txt = preg_replace("#<a href=[\"'](http://|https://|ftp://|news://)?(\S+?)['\"].+?" . '>(.+?)</a>#', "\[URL=\\1\\2\]\\3\[/URL\]", $txt);

            $txt = preg_replace('#<!--c1-->(.+?)<!--ec1-->#', '[CODE]', $txt);

            $txt = preg_replace('#<!--c2-->(.+?)<!--ec2-->#', '[/CODE]', $txt);

            $txt = preg_replace("#<!--QuoteBegin-(.+?)\+(.+?)-->(.+?)<!--QuoteEBegin-->#", '[QUOTE=\\1,\\2]', $txt);

            $txt = preg_replace("#<!--QuoteBegin-(.+?)\+-->(.+?)<!--QuoteEBegin-->#", '[QUOTE=\\1]', $txt);

            $txt = preg_replace('#<!--QuoteBegin-->(.+?)<!--QuoteEBegin-->#', '[QUOTE]', $txt);

            $txt = preg_replace('#<!--QuoteEnd-->(.+?)<!--QuoteEEnd-->#', '[/QUOTE]', $txt);

            $txt = preg_replace('#<i>(.+?)</i>#is', "\[i\]\\1\[/i\]", $txt);

            $txt = preg_replace('#<b>(.+?)</b>#is', "\[b\]\\1\[/b\]", $txt);

            $txt = preg_replace('#<s>(.+?)</s>#is', "\[s\]\\1\[/s\]", $txt);

            $txt = preg_replace('#<u>(.+?)</u>#is', "\[u\]\\1\[/u\]", $txt);

            $txt = preg_replace('#<ul>#', "\[LIST\]", $txt);

            $txt = preg_replace('#<li>#', "\[*\]", $txt);

            $txt = preg_replace('#</ul>#', "\[/LIST\]", $txt);

            $txt = preg_replace('#<!--me&(.+?)-->(.+?)<!--e--me-->#e', "\$this->unconvert_me('\\1', '\\2')", $txt);

            $txt = preg_replace("#<span style=['\"]font-size:(.+?)pt;line-height:100%['\"]>(.+?)</span>#e", "\$this->unconvert_size('\\1', '\\2')", $txt);

            while (preg_match("#<span style=['\"]color:(.+?)['\"]>(.+?)</span>#is", $txt)) {
                $txt = preg_replace("#<span style=['\"]color:(.+?)['\"]>(.+?)</span>#is", "\[color=\\1\]\\2\[/color\]", $txt);
            }

            //$txt = preg_replace( "#<span style=['\"]color:(.+?)['\"]>(.+?)</span>#"                         , "\[color=\\1\]\\2\[/color\]", $txt );

            $txt = preg_replace("#<span style=['\"]font-family:(.+?)['\"]>(.+?)</span>#", "\[font=\\1\]\\2\[/font\]", $txt);

            // Tidy up the end quote stuff

            $txt = preg_replace("#(\[/QUOTE\])\s*?<br>\s*#si", "\\1\n", $txt);

            $txt = preg_replace("#<!--EDIT\|.+?\|.+?-->#", '', $txt);
        }

        if (1 == $html) {
            $txt = str_replace('&#39;', "'", $txt);
        }

        $txt = preg_replace('#<br>#', "\n", $txt);

        return trim(stripslashes($txt));
    }

    //+-----------------------------------------------------------------------------------------

    //+-----------------------------------------------------------------------------------------

    // UNCONVERT FUNCTIONS

    //+-----------------------------------------------------------------------------------------

    //+-----------------------------------------------------------------------------------------

    public function unconvert_size($size = '', $text = '')
    {
        $size -= 7;

        return '[SIZE=' . $size . ']' . $text . '[/SIZE]';
    }

    public function unconvert_flash($flash = '')
    {
        $f_arr = explode('+', $flash);

        return '[FLASH=' . $f_arr[0] . ',' . $f_arr[1] . ']' . $f_arr[2] . '[/FLASH]';
    }

    public function unconvert_me($name = '', $text = '')
    {
        $text = preg_replace("#<span class='ME'><center>(.+?)</center></span>#", '\\1', $text);

        $text = preg_replace("#$name#", '', $text);

        return '[ME=' . $name . ']' . $text . '[/ME]';
    }

    public function unconvert_sql($sql = '')
    {
        $sql = stripslashes($sql);

        $sql = preg_replace("#<span style='.+?'>(.+?)</span>#", '\\1', $sql);

        $sql = rtrim($sql);

        return '[SQL]' . $sql . '[/SQL]';
    }

    public function unconvert_htm($html = '')
    {
        $html = stripslashes($html);

        $html = preg_replace("#<span style='.+?'>(.+?)</span>#", '\\1', $html);

        $html = rtrim($html);

        return '[HTML]' . $html . '[/HTML]';
    }

    //+-----------------------------------------------------------------------------------------

    //+-----------------------------------------------------------------------------------------

    // CONVERT FUNCTIONS

    //+-----------------------------------------------------------------------------------------

    //+-----------------------------------------------------------------------------------------

    /**************************************************/

    // convert_emoticon:

    // replaces the text with the emoticon image

    /**************************************************/

    public function convert_emoticon($code = '', $image = '')
    {
        global $ibforums;

        if (!$code or !$image) {
            return;
        }

        // Remove slashes added by preg_quote

        $code = stripslashes($code);

        $this->emoticon_count++;

        return "<!--emo&$code--><img src='{$ibforums->vars['EMOTICONS_URL']}/$image' border='0' style='vertical-align:middle' alt='$image'><!--endemo-->";
    }

    /**************************************************/

    // wrap style:

    // code and quote table HTML generator

    /**************************************************/

    public function wrap_style($in = [])
    {
        global $ibforums;

        if (!isset($in['TYPE'])) {
            $in['TYPE'] = 'class';
        }

        if (!isset($in['CSS'])) {
            $in['CSS'] = 1 == $this->in_sig ? 'signature' : 'postcolor';
        }

        if (!isset($in['STYLE'])) {
            $in['STYLE'] = 'QUOTE';
        }

        //-----------------------------

        // This returns two array elements:

        //  START: Contains the HTML code for the start wrapper

        //  END  : Contains the HTML code for the end wrapper

        //-----------------------------

        $possible_use = [
            'CODE' => ['CODE', 'CODE'],
'QUOTE' => ['QUOTE', 'QUOTE'],
'SQL' => ['CODE', 'SQL'],
'HTML' => ['CODE', 'HTML'],
'PHP' => ['CODE', 'PHP'],
        ];

        return [
            'START' => "</span><table border='0' align='center' width='95%' cellpadding='3' cellspacing='1'><tr><td><b>{$possible_use[$in[STYLE]][1]}</b> {$in[EXTRA]}</td></tr><tr><td id='{$possible_use[ $in[STYLE] ][0]}'>",
'END' => "</td></tr></table><span {$in[TYPE]}='{$in[CSS]}'>",
        ];
    }

    /**************************************************/

    // regex_html_tag: HTML syntax highlighting

    /**************************************************/

    public function regex_html_tag($html = '')
    {
        if ('' == $html) {
            return;
        }

        // Ensure that spacing is preserved

        // Too many embedded code/quote/html/sql tags can crash Opera and Moz

        if (preg_match("/\[(quote|code|html|sql)\].+?\[(quote|code|html|sql)\].+?\[(quote|code|html|sql)\].+?\[(quote|code|html|sql)\].+?\[(quote|code|html|sql)\]/i", $html)) {
            return $default;
        }

        //$html = preg_replace( "#\s{2}#", " &nbsp;", $html );

        // Knock off any preceeding newlines (which have

        // since been converted into <br>)

        //$html = nl2br($html);

        // Take a stab at removing most of the common

        // smilie characters.

        $html = preg_replace('#:#', '&#58;', $html);

        $html = preg_replace("#\[#", '&#91;', $html);

        $html = preg_replace("#\]#", '&#93;', $html);

        $html = preg_replace("#\)#", '&#41;', $html);

        $html = preg_replace("#\(#", '&#40;', $html);

        $html = preg_replace('/^<br>/', '', $html);

        $html = ltrim($html);

        $html = preg_replace('#&lt;([^&<>]+)&gt;#', "&lt;<span style='color:blue'>\\1</span>&gt;", $html);   //Matches <tag>
        $html = preg_replace('#&lt;([^&<>]+)=#', "&lt;<span style='color:blue'>\\1</span>=", $html);   //Matches <tag
        $html = preg_replace('#&lt;/([^&]+)&gt;#', "&lt;/<span style='color:blue'>\\1</span>&gt;", $html);   //Matches </tag>
        $html = preg_replace("!=(&quot;|&#39;)(.+?)(&quot;|&#39;)(\s|&gt;)!", "=\\1<span style='color:orange'>\\2</span>\\3\\4", $html);   //Matches ='this'
        $html = preg_replace('!&#60;&#33;--(.+?)--&#62;!', "&lt;&#33;<span style='color:red'>--\\1--</span>&gt;", $html);

        $wrap = $this->wrap_style(['STYLE' => 'HTML']);

        return "<!--html-->{$wrap['START']}<!--html1-->$html<!--html2-->{$wrap['END']}<!--html3-->";
    }

    /**************************************************/

    // regex_sql_tag: SQL syntax highlighting

    /**************************************************/

    public function regex_sql_tag($sql = '')
    {
        if ('' == $sql) {
            return;
        }

        // Too many embedded code/quote/html/sql tags can crash Opera and Moz

        if (preg_match("/\[(quote|code|html|sql)\].+?\[(quote|code|html|sql)\].+?\[(quote|code|html|sql)\].+?\[(quote|code|html|sql)\].+?\[(quote|code|html|sql)\].+?\[(quote|code|html|sql)\].+?\[(quote|code|html|sql)\].+?\[(quote|code|html|sql)\].+?\[(quote|code|html|sql)\]/i", $sql)) {
            return $default;
        }

        // Knock off any preceeding newlines (which have

        // since been converted into <br>)

        $sql = preg_replace('/^<br>/', '', $sql);

        $sql = ltrim($sql);

        // Make certain regex work..

        if (!preg_match("/\s+$/", $sql)) {
            $sql .= ' ';
        }

        $sql = preg_replace("#(=|\+|\-|&gt;|&lt;|~|==|\!=|LIKE|NOT LIKE|REGEXP)#i", "<span style='color:orange'>\\1</span>", $sql);

        $sql = preg_replace("#(MAX|AVG|SUM|COUNT|MIN)\(#i", "<span style='color:blue'>\\1</span>(", $sql);

        $sql = preg_replace('!(&quot;|&#39;|&#039;)(.+?)(&quot;|&#39;|&#039;)!i', "<span style='color:red'>\\1\\2\\3</span>", $sql);

        $sql = preg_replace("#\s{1,}(AND|OR)\s{1,}#i", " <span style='color:blue'>\\1</span> ", $sql);

        $sql = preg_replace("#(WHERE|MODIFY|CHANGE|AS|DISTINCT|IN|ASC|DESC|ORDER BY)\s{1,}#i", "<span style='color:green'>\\1</span> ", $sql);

        $sql = preg_replace("#LIMIT\s*(\d+)\s*,\s*(\d+)#i", "<span style='color:green'>LIMIT</span> <span style='color:orange'>\\1, \\2</span>", $sql);

        $sql = preg_replace("#(FROM|INTO)\s{1,}(\S+?)\s{1,}#i", "<span style='color:green'>\\1</span> <span style='color:orange'>\\2</span> ", $sql);

        $sql = preg_replace('#(SELECT|INSERT|UPDATE|DELETE|ALTER TABLE|DROP)#i', "<span style='color:blue;font-weight:bold'>\\1</span>", $sql);

        $html = $this->wrap_style(['STYLE' => 'SQL']);

        return "<!--sql-->{$html['START']}<!--sql1-->{$sql}<!--sql2-->{$html['END']}<!--sql3-->";
    }

    /**************************************************/

    // regex_code_tag: Builds this code tag HTML

    /**************************************************/

    public function regex_code_tag($txt = '')
    {
        global $ibforums;

        $default = "\[code\]$txt\[/code\]";

        if ('' == $txt) {
            return;
        }

        // Too many embedded code/quote/html/sql tags can crash Opera and Moz

        if (preg_match("/\[(quote|code|html|sql)\].+?\[(quote|code|html|sql)\].+?\[(quote|code|html|sql)\].+?\[(quote|code|html|sql)\].+?\[(quote|code|html|sql)\].+?\[(quote|code|html|sql)\].+?\[(quote|code|html|sql)\]/i", $txt)) {
            return $default;
        }

        // Take a stab at removing most of the common

        // smilie characters.

        //$txt = str_replace( "&" , "&amp;", $txt );

        $txt = preg_replace('#&lt;#', '&#60;', $txt);

        $txt = preg_replace('#&gt;#', '&#62;', $txt);

        $txt = preg_replace('#&quot;#', '&#34;', $txt);

        $txt = preg_replace('#:#', '&#58;', $txt);

        $txt = preg_replace("#\[#", '&#91;', $txt);

        $txt = preg_replace("#\]#", '&#93;', $txt);

        $txt = preg_replace("#\)#", '&#41;', $txt);

        $txt = preg_replace("#\(#", '&#40;', $txt);

        $txt = preg_replace("#\r#", '<br>', $txt);

        $txt = preg_replace("#\n#", '<br>', $txt);

        $txt = preg_replace("#\s{1};#", '&#59;', $txt);

        // Ensure that spacing is preserved

        $txt = preg_replace("#\s{2}#", ' &nbsp;', $txt);

        $html = $this->wrap_style(['STYLE' => 'CODE']);

        return "<!--c1-->{$html['START']}<!--ec1-->$txt<!--c2-->{$html['END']}<!--ec2-->";
    }

    /****************************************************************************************************/

    // regex_parse_quotes: Builds this quote tag HTML

    // [QUOTE] .. [/QUOTE] - allows for embedded quotes

    /**************************************************/

    public function regex_parse_quotes($the_txt = '')
    {
        if ('' == $the_txt) {
            return;
        }

        $txt = $the_txt;

        // Too many embedded code/quote/html/sql tags can crash Opera and Moz

        /*if (preg_match( "/\[(quote|code|html|sql)\].+?\[(quote|code|html|sql)\].+?\[(quote|code|html|sql)\].+?\[(quote|code|html|sql)\].+?\[(quote|code|html|sql)\]/is", $txt) ) {
            $this->quote_error++;
            return $txt;
        }*/

        $this->quote_html = $this->wrap_style(['STYLE' => 'QUOTE']);

        $txt = preg_replace("#\[quote\]#ie", '$this->regex_simple_quote_tag()', $txt);

        $txt = preg_replace("#\[quote=(.+?),(.+?)\]#ie", "\$this->regex_quote_tag('\\1', '\\2')", $txt);

        $txt = preg_replace("#\[quote=(.+?)\]#ie", "\$this->regex_quote_tag('\\1', '')", $txt);

        $txt = preg_replace("#\[/quote\]#ie", '$this->regex_close_quote()', $txt);

        $txt = preg_replace("/\n/", '<br>', $txt);

        if (($this->quote_open == $this->quote_closed) and (0 == $this->quote_error)) {
            // Preserve spacing

            $txt = preg_replace('#(<!--QuoteEBegin-->.+?<!--QuoteEnd-->)#es', "\$this->regex_preserve_spacing('\\1')", trim($txt));

            return $txt;
        }

        return $the_txt;
    }

    /**************************************************/

    // regex_preserve_spacing: keeps double spaces

    // without CSS killing <pre> tags

    /**************************************************/

    public function regex_preserve_spacing($txt = '')
    {
        $txt = preg_replace("#\s{2}#", '&nbsp; ', trim($txt));

        return $txt;
    }

    /**************************************************/

    // regex_simple_quote_tag: Builds this quote tag HTML

    // [QUOTE] .. [/QUOTE]

    /**************************************************/

    public function regex_simple_quote_tag()
    {
        global $ibforums;

        $this->quote_open++;

        return "<!--QuoteBegin-->{$this->quote_html['START']}<!--QuoteEBegin-->";
    }

    /**************************************************/

    // regex_close_quote: closes a quote tag

    /**************************************************/

    public function regex_close_quote()
    {
        if (0 == $this->quote_open) {
            $this->quote_error++;

            return;
        }

        $this->quote_closed++;

        return "<!--QuoteEnd-->{$this->quote_html['END']}<!--QuoteEEnd-->";
    }

    /**************************************************/

    // regex_quote_tag: Builds this quote tag HTML

    // [QUOTE=Matthew,14 February 2002]

    /**************************************************/

    public function regex_quote_tag($name = '', $date = '')
    {
        global $ibforums;

        $name = str_replace('+', '&#043;', $name);

        $default = "\[quote=$name,$date\]";

        $this->quote_open++;

        if ('' == $date) {
            $html = $this->wrap_style(['STYLE' => 'QUOTE', 'EXTRA' => "($name)"]);
        } else {
            $html = $this->wrap_style(['STYLE' => 'QUOTE', 'EXTRA' => "($name @ $date)"]);
        }

        $extra = "-$name+$date";

        return '<!--QuoteBegin' . $extra . "-->{$html['START']}<!--QuoteEBegin-->";
    }

    /****************************************************************************************************/

    // regex_check_flash: Checks, and builds the <object>

    // html.

    /**************************************************/

    public function regex_check_flash($width = '', $height = '', $url = '')
    {
        global $ibforums;

        $default = "\[flash=$width,$height\]$url\[/flash\]";

        if (!$ibforums->vars['allow_flash']) {
            return $default;
        }

        if ($width > $ibforums->vars['max_w_flash']) {
            $this->error = 'flash_too_big';

            return $default;
        }

        if ($height > $ibforums->vars['max_h_flash']) {
            $this->error = 'flash_too_big';

            return $default;
        }

        if (!preg_match("/^http:\/\/(\S+)\.swf$/i", $url)) {
            $this->error = 'flash_url';

            return $default;
        }

        return "<!--Flash $width+$height+$url--><OBJECT CLASSID='clsid:D27CDB6E-AE6D-11cf-96B8-444553540000' WIDTH=$width HEIGHT=$height><PARAM NAME=MOVIE VALUE=$url><PARAM NAME=PLAY VALUE=TRUE><PARAM NAME=LOOP VALUE=TRUE><PARAM NAME=QUALITY VALUE=HIGH><EMBED SRC=$url WIDTH=$width HEIGHT=$height PLAY=TRUE LOOP=TRUE QUALITY=HIGH></EMBED></OBJECT><!--End Flash-->";
    }

    /**************************************************/

    // regex_check_image: Checks, and builds the <img>

    // html.

    /**************************************************/

    public function regex_check_image($url = '')
    {
        global $ibforums;

        if (!$url) {
            return;
        }

        $url = trim($url);

        $default = '[img]' . $url . '[/img]';

        ++$this->image_count;

        // Make sure we've not overriden the set image # limit

        if ($ibforums->vars['max_images']) {
            if ($this->image_count > $ibforums->vars['max_images']) {
                $this->error = 'too_many_img';

                return $default;
            }
        }

        // Are they attempting to post a dynamic image, or JS?

        if (1 != $ibforums->vars['allow_dynamic_img']) {
            if (preg_match('/[?&;]/', $url)) {
                $this->error = 'no_dynamic';

                return $default;
            }

            if (preg_match("/javascript(\:|\s)/i", $url)) {
                $this->error = 'no_dynamic';

                return $default;
            }
        }

        // Is the img extension allowed to be posted?

        if ($ibforums->vars['img_ext']) {
            $extension = preg_replace("#^.*\.(\S+)$#", '\\1', $url);

            $extension = mb_strtolower($extension);

            if ((!$extension) or (preg_match('#/#', $extension))) {
                $this->error = 'invalid_ext';

                return $default;
            }

            $ibforums->vars['img_ext'] = mb_strtolower($ibforums->vars['img_ext']);

            if (!preg_match("/$extension(\||$)/", $ibforums->vars['img_ext'])) {
                $this->error = 'invalid_ext';

                return $default;
            }
        }

        // Is it a legitimate image?

        if (!preg_match("/^(http|https|ftp):\/\//i", $url)) {
            $this->error = 'no_dynamic';

            return $default;
        }

        // If we are still here....

        return "<img src='$url' border='0' alt='user posted image'>";
    }

    /**************************************************/

    // regex_font_attr:

    // Returns a string for an /e regexp based on the input

    /**************************************************/

    public function regex_font_attr($IN)
    {
        if (!is_array($IN)) {
            return '';
        }

        // Trim out stoopid 1337 stuff

        // [color=black;font-size:500pt;border:orange 50in solid;]hehe[/color]

        if (preg_match('/;/', $IN['1'])) {
            $attr = explode(';', $IN['1']);

            $IN['1'] = $attr[0];
        }

        if ('size' == $IN['s']) {
            $IN['1'] += 7;

            if ($IN['1'] > 30) {
                $IN['1'] = 30;
            }

            return "<span style='font-size:" . $IN['1'] . "pt;line-height:100%'>" . $IN['2'] . '</span>';
        } elseif ('col' == $IN['s']) {
            return "<span style='color:" . $IN['1'] . "'>" . $IN['2'] . '</span>';
        } elseif ('font' == $IN['s']) {
            return "<span style='font-family:" . $IN['1'] . "'>" . $IN['2'] . '</span>';
        }
    }

    /**************************************************/

    // regex_build_url: Checks, and builds the a href

    // html

    /**************************************************/

    public function regex_build_url($url = [])
    {
        $skip_it = 0;

        // Make sure the last character isn't punctuation.. if it is, remove it and add it to the

        // end array

        if (preg_match("/([\.,\?]|&#33;)$/", $url['html'], $match)) {
            $url['end'] .= $match[1];

            $url['html'] = preg_replace("/([\.,\?]|&#33;)$/", '', $url['html']);

            $url['show'] = preg_replace("/([\.,\?]|&#33;)$/", '', $url['show']);
        }

        // Make sure it's not being used in a closing code/quote/html or sql block

        if (preg_match("/\[\/(html|quote|code|sql)/i", $url['html'])) {
            return $url['html'];
        }

        // clean up the ampersands

        $url['html'] = preg_replace('/&amp;/', '&', $url['html']);

        // Make sure we don't have a JS link

        $url['html'] = preg_replace('/javascript:/i', 'java script&#58; ', $url['html']);

        // Do we have http:// at the front?

        if (!preg_match('#^(http|news|https|ftp|aim)://#', $url['html'])) {
            $url['html'] = 'http://' . $url['html'];
        }

        //-------------------------

        // Tidy up the viewable URL

        //-------------------------

        if (preg_match('/^<img src/i', $url['show'])) {
            $skip_it = 1;
        }

        $url['show'] = preg_replace('/&amp;/', '&', $url['show']);

        $url['show'] = preg_replace('/javascript:/i', 'javascript&#58; ', $url['show']);

        if (mb_strlen($url['show']) < 55) {
            $skip_it = 1;
        }

        // Make sure it's a "proper" url

        if (!preg_match("/^(http|ftp|https|news):\/\//i", $url['show'])) {
            $skip_it = 1;
        }

        $show = $url['show'];

        if (1 != $skip_it) {
            $stripped = preg_replace("#^(http|ftp|https|news)://(\S+)$#i", '\\2', $url['show']);

            $uri_type = preg_replace("#^(http|ftp|https|news)://(\S+)$#i", '\\1', $url['show']);

            $show = $uri_type . '://' . mb_substr($stripped, 0, 35) . '...' . mb_substr($stripped, -15);
        }

        return $url['st'] . "<a href='" . $url['html'] . "' target='_blank'>" . $show . '</a>' . $url['end'];
    }

    public function regex_bash_session($start_tok, $end_tok)
    {
        // Bug fix :D

        // Case 1: index.php?s=0000        :: Return nothing (parses: index.php)

        // Case 2: index.php?s=0000&this=1 :: Return ?       (parses: index.php?this=1)

        // Case 3: index.php?this=1&s=0000 :: Return nothing (parses: index.php?this=1)

        // Case 4: index.php?t=1&s=00&y=2  :: Return &       (parses: index.php?t=1&y=2)

        // Thanks to LavaSoft for spotting this one.

        $start_tok = str_replace('&amp;', '&', $start_tok);

        $end_tok = str_replace('&amp;', '&', $end_tok);

        //1:

        if ('?' == $start_tok and '' == $end_tok) {
            return '';
        } //2:

        elseif ('?' == $start_tok and '&' == $end_tok) {
            return '?';
        } //3:

        elseif ('&' == $start_tok and '' == $end_tok) {
            return '';
        } elseif ('&' == $start_tok and '&' == $end_tok) {
            return '&';
        }

        return $start_tok . $end_tok;
    }
}

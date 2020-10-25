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
|   > IPS Remote Call thingy
|   > Module written by Matt Mecham
|   > Date started: 17th October 2002
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

// Ensure we've not accessed this script directly:

$idx = new ad_ips();

class ad_ips
{
    public $base_url;

    public $colours = [];

    public $url = 'http://www.invisionboard.com/acp/';

    public $version = '1.1';

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
            case 'news':
                $this->news();
                break;
            case 'updates':
                $this->updates();
                break;
            case 'docs':
                $this->docs();
                break;
            case 'support':
                $this->support();
                break;
            case 'host':
                $this->host();
                break;
            case 'purchase':
                $this->purchase();
                break;
            //-------------------------
            default:
                exit();
                break;
        }
    }

    public function news()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        @header('Location: ' . $this->url . '?news');

        exit();
    }

    public function updates()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        //@header("Location: ".$this->url."?updates&version=".$this->version);

        @header('Location: ' . $this->url . '?updates');

        exit();
    }

    public function docs()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        @header('Location: ' . $this->url . '?docs');

        exit();
    }

    public function support()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        @header('Location: ' . $this->url . '?support');

        exit();
    }

    public function host()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        @header('Location: ' . $this->url . '?host');

        exit();
    }

    public function purchase()
    {
        global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        @header('Location: ' . $this->url . '?purchase');

        exit();
    }
}

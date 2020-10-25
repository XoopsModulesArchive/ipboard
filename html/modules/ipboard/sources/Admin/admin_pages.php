<?php

// Simple library that holds all the links for the admin cp

// CAT_ID => array(  PAGE_ID  => (PAGE_NAME, URL ) )

// $PAGES[ $cat_id ][$page_id][0] = Page name
// $PAGES[ $cat_id ][$page_id][1] = Url

$PAGES = [
    0 => [
        1 => ['IPS Latest News', 'act=ips&code=news'],
2 => ['Check for updates', 'act=ips&code=updates'],
3 => ['Documentation', 'act=ips&code=docs'],
4 => ['Get Support', 'act=ips&code=support'],
5 => ['IPS Hosting', 'act=ips&code=host'],
6 => ['Purchase Services', 'act=ips&code=purchase'],
    ],

    1 => [
        1 => ['IP Chat', 'act=pin&code=ipchat'],
    ],

    2 => [
        1 => ['Basic Config', 'act=op&code=url'],
2 => ['Security & Privacy', 'act=op&code=secure'],
3 => ['Topics, Posts & Polls', 'act=op&code=post'],
4 => ['User Profiles', 'act=op&code=avatars'],
5 => ['Date & Time Formats', 'act=op&code=dates'],
6 => ['CPU Saving', 'act=op&code=cpu'],
7 => ['Cookies', 'act=op&code=cookie'],
8 => ['PM Set up', 'act=op&code=pm'],
9 => ['Board on/off', 'act=op&code=board'],
10 => ['News Set-up', 'act=op&code=news'],
11 => ['Calendar Set-up', 'act=op&code=calendar'],
12 => ['COPPA Set-up', 'act=op&code=coppa'],
14 => ['Email Set-up', 'act=op&code=email'],
15 => ['Server Environment', 'act=op&code=phpinfo'],
    ],

    3 => [
        1 => ['New Category', 'act=cat&code=new'],
2 => ['New Forum', 'act=forum&code=newsp'],
3 => ['Manage', 'act=cat&code=edit'],
4 => ['Re-Order Categories', 'act=cat&code=reorder'],
5 => ['Re-Order Forums', 'act=forum&code=reorder'],
6 => ['Moderators', 'act=mod'],
    ],

    4 => [
        1 => ['Pre-Register', 'act=mem&code=add'],
2 => ['Find/Edit User', 'act=mem&code=edit'],
3 => ['Delete User(s)', 'act=mem&code=del'],
4 => ['Ban Settings', 'act=mem&code=ban'],
5 => ['User Title/Ranks', 'act=mem&code=title'],
6 => ['Manage User Groups', 'act=group'],
7 => ['Manage Registrations', 'act=mem&code=mod'],
8 => ['Custom Profile Fields', 'act=field'],
9 => ['Bulk Email Members', 'act=mem&code=mail'],
    ],

    5 => [
        1 => ['Manage Word Filters', 'act=op&code=bw'],
2 => ['Manage Emoticons', 'act=op&code=emo'],
3 => ['Manage Help Files', 'act=help'],
4 => ['Recount Statistics', 'act=op&code=count'],
5 => ['View Moderator Logs', 'act=modlog'],
6 => ['View Admin Logs', 'act=adminlog'],
    ],

    6 => [
        1 => ['Manage Board Wrappers', 'act=wrap'],
2 => ['Manage HTML Templates', 'act=templ'],
3 => ['Manage Style Sheets', 'act=style'],
4 => ['Manage Macros', 'act=image'],
5 => ['<b>Manage Skin Sets</b>', 'act=sets'],
6 => ['Import Skin files', 'act=import'],
    ],

    7 => [
        1 => ['Manage Languages', 'act=lang'],
2 => ['Import a Language', 'act=lang&code=import'],
    ],

    8 => [
        1 => ['Registration Stats', 'act=stats&code=reg'],
2 => ['New Topic Stats', 'act=stats&code=topic'],
3 => ['Post Stats', 'act=stats&code=post'],
4 => ['Private Message', 'act=stats&code=msg'],
5 => ['Topic Views', 'act=stats&code=views'],
    ],

    9 => [
        1 => ['mySQL Toolbox', 'act=mysql'],
2 => ['mySQL Back Up', 'act=mysql&code=backup'],
3 => ['SQL Runtime Info', 'act=mysql&code=runtime'],
4 => ['SQL System Vars', 'act=mysql&code=system'],
5 => ['SQL Processes', 'act=mysql&code=processes'],
    ],
];

$CATS = [
    0 => 'IPS Services',
1 => 'IPB Plug Ins',
2 => 'Board Settings',
3 => 'Forum Control',
4 => 'Users and Groups',
5 => 'Administration',
6 => 'Skins & Templates',
7 => 'Languages',
8 => 'Statistic Center',
9 => 'SQL Management',
];

$DESC = [
    0 => 'Get IPS latest news, documentation, request support, purchase extra services and more...',
1 => 'Set up and manage your IPB plug in services',
2 => 'Edit forum settings such as cookie paths, security features, posting abilities, etc',
3 => 'Create, edit, remove and re-order categories, forums and moderators',
4 => 'Edit, register, remove and ban members. Set up member titles and ranks. Manage User Groups and moderated registrations',
5 => 'Manage Help Files, Bad Word Filters and Emoticons',
6 => 'Manage templates, skins, colours and images.',
7 => 'Manage language sets',
8 => 'Get registration and posting statistics',
9 => 'Manage your SQL database; repair, optimize and export data',
];

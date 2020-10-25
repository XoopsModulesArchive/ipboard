<?php

$modversion['name'] = _MI_IPBOARD_NAME;
$modversion['version'] = '1.13';
$modversion['description'] = _MI_IPBOARD_DESC;
$modversion['credits'] = 'Made by<br>Koudanshi<br>( http://www.koudanshi.net/ )';
$modversion['author'] = 'Koudanshi';
$modversion['help'] = 'None';
$modversion['license'] = "Please read the licence carefully, IPB 1.1 original is not a open source and IPC, INC don't have responsible for bugs occurs when you use this module";
$modversion['official'] = 0;
$modversion['image'] = 'html/sys-img/ipm_logo.gif';
$modversion['dirname'] = 'ipboard';

// Admin things
$modversion['hasAdmin'] = 1;
$modversion['adminindex'] = 'index.php';
$modversion['adminmenu'] = 'menu.php';

// Search
$modversion['hasSearch'] = 1;
$modversion['search']['file'] = 'search.inc.php';
$modversion['search']['func'] = 'ipboard_search';

// Menu
$modversion['hasMain'] = 1;

// Blocks
$modversion['blocks'][1]['file'] = 'ipboard.php';
$modversion['blocks'][1]['name'] = _MI_IPBOARD_BNAME1;
$modversion['blocks'][1]['description'] = 'Shows recent topics in the forums';
$modversion['blocks'][1]['show_func'] = 'ipboard_topics_show';
$modversion['blocks'][1]['options'] = '10|1|time';
$modversion['blocks'][1]['edit_func'] = 'ipboard_topics_edit';
$modversion['blocks'][1]['template'] = 'ipboard_block_new.html';

$modversion['blocks'][2]['file'] = 'ipboard.php';
$modversion['blocks'][2]['name'] = _MI_IPBOARD_BNAME2;
$modversion['blocks'][2]['description'] = 'Shows most viewed topics in the forums';
$modversion['blocks'][2]['show_func'] = 'ipboard_topics_show';
$modversion['blocks'][2]['options'] = '10|1|views';
$modversion['blocks'][2]['edit_func'] = 'ipboard_topics_edit';
$modversion['blocks'][2]['template'] = 'ipboard_block_top.html';

$modversion['blocks'][3]['file'] = 'ipboard.php';
$modversion['blocks'][3]['name'] = _MI_IPBOARD_BNAME3;
$modversion['blocks'][3]['description'] = 'Shows most active topics in the forums';
$modversion['blocks'][3]['show_func'] = 'ipboard_topics_show';
$modversion['blocks'][3]['options'] = '10|1|replies';
$modversion['blocks'][3]['edit_func'] = 'ipboard_topics_edit';
$modversion['blocks'][3]['template'] = 'ipboard_block_active.html';

$modversion['blocks'][4]['file'] = 'ipboard.php';
$modversion['blocks'][4]['name'] = _MI_IPBOARD_BDAY;
$modversion['blocks'][4]['description'] = "Shows today's birthday ";
$modversion['blocks'][4]['show_func'] = 'ipboard_bday_show';
$modversion['blocks'][4]['options'] = '10|1|name';
$modversion['blocks'][4]['edit_func'] = 'ipboard_bday_edit';
$modversion['blocks'][4]['template'] = 'ipboard_block_bday.html';

// Smarty
$modversion['use_smarty'] = 1;

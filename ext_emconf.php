<?php

########################################################################
# Extension Manager/Repository config file for ext: "nc_staticfilecache"
#
# Auto generated 28-06-2007 16:38
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Static File Cache',
	'description' => 'Transparent static file cache solution using mod_rewrite and mod_expires. Increase response times for static pages by a factor of 230!',
	'category' => 'fe',
	'shy' => 0,
	'version' => '2.3.0',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => 'cli/pre_4.1',
	'state' => 'stable',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => 'pages',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Michiel Roos, Tim Lochmüller, Marc Hörsken',
	'author_email' => 'extensions@netcreators.com',
	'author_company' => 'Netcreators',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:15:{s:36:"class.tx_ncstaticfilecache.debug.php";s:4:"3f9a";s:30:"class.tx_ncstaticfilecache.php";s:4:"168b";s:21:"ext_conf_template.txt";s:4:"0cf8";s:12:"ext_icon.gif";s:4:"03df";s:17:"ext_localconf.php";s:4:"372b";s:14:"ext_tables.php";s:4:"6285";s:14:"ext_tables.sql";s:4:"1a0f";s:16:"locallang_db.xml";s:4:"441a";s:15:"cli/cleaner.php";s:4:"7ed9";s:23:"cli/pre_4.1/cleaner.php";s:4:"ed3e";s:20:"cli/pre_4.1/conf.php";s:4:"2d36";s:14:"doc/_.htaccess";s:4:"63d1";s:14:"doc/manual.sxw";s:4:"4e0b";s:48:"modfunc1/class.tx_ncstaticfilecache_modfunc1.php";s:4:"269e";s:22:"modfunc1/locallang.php";s:4:"6a50";}',
	'suggests' => array(
	),
);

?>
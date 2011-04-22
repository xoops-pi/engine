<?php
/**
 * See the enclosed file license.txt for licensing information.
 * If you did not receive this file, get it at http://www.fsf.org/copyleft/gpl.html
 *
 * @copyright       Xoops Engine
 * @license         BSD License
 * @package         installer
 * @since           3.0
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @author          Skalpa Keo <skalpa@xoops.org>
 * @version         $Id$
 */

if (!defined('XOOPS_INSTALL')) { die('XOOPS Custom Installation die'); }

$configs = array();

// setup config site info
$configs['db_types']  = array('mysql');

// Directories
$configs['paths'] = array(
    'lib'           => array(
        'path'  => array('../lib', 'lib'),
        'url'   => 'browse.php?lib',
    ),
    'var'           => array(
        'path'  => array('../var', 'var'),
        'url'   => 'browse.php?var',
    ),
    'usr'       => array(
        'path'  => array('../usr', 'usr', '%lib/usr'),
        'url'   => false,
    ),
    'app'           => array(
        'path'  => '%usr/apps',
        'url'   => 'browse.php?app',
    ),
    'plugin'        => array(
        'path'  => '%usr/plugins',
        'url'   => 'browse.php?plugin',
    ),
    'applet'        => array(
        'path'  => '%usr/applets',
        'url'   => 'browse.php?applet',
    ),
    'img'           => array(
        'path'  => array('img', '../img', 'static', '../static'),
        'url'   => array(
            '%www/img', 'http://img.' . preg_replace('/^(www\.)/i', '', $_SERVER['HTTP_HOST']),
            '%www/static', 'http://static.' . preg_replace('/^(www\.)/i', '', $_SERVER['HTTP_HOST']),
        ),
    ),
    'theme'         => array(
        'path'  => array('skin', 'themes'),
        'url'   => array('skin', 'themes'),
    ),
    'upload'        => array(
        'path'  => 'uploads',
        'url'   => 'uploads',
    ),
);

// Writable files and directories
$configs['writable']['www'] = array('.htaccess', 'boot.php');
//$configs['writable']['lib'] = array("boot/engine.xoops.ini.php", "boot/hosts.xoops.ini.php");
$configs['writable']['lib'] = array("boot");
$configs['writable']['var'] = "";
/*
$configs['writable']['var'] = array(
    'cache' => array(
        'system',
        'smarty/cache',
        'smarty/compile',
        'themes',
    ),
    'etc'   => array(
        "resource.db.ini.php",
        // see {@\kernel\Service\Module::FILE_META}
        "modules.ini.php",
        "plugins.ini.php"
    ),
    'data'      => array(),
    'log'       => array(),
);
*/
$configs['writable']['upload'] = "";
/*
$configs['file_template'] = array(
    "boot.php"      => __DIR__ . "/boot.dist.php",
    ".htaccess"     => __DIR__ . "/.htaccess.dist",
);
*/
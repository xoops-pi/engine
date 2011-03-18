<?php
/**
 * Installer common include file
 *
 * See the enclosed file license.txt for licensing information.
 * If you did not receive this file, get it at http://www.fsf.org/copyleft/gpl.html
 *
 * @copyright   Xoops Engine
 * @license     BSD License
 * @package     installer
 * @since       3.0
 * @author      Taiwen Jiang <phppp@users.sourceforge.net>
 * @version     $Id$
 */

/**
 * If non-empty, only this user can access this installer
 */
define('INSTALL_USER', '');
define('INSTALL_PASSWORD', '');

define('XOOPS_INSTALL', 1);

/*
$mod_rewrite =  getenv('HTTP_MOD_REWRITE') == 'On' ? true : false ;
if (!$mod_rewrite && function_exists('apache_get_modules')) {
    $mod_rewrite = in_array('mod_rewrite', apache_get_modules());
}
if (!$mod_rewrite) {
    exit("Xoops Engine requires Apache mod_rewrite. Please change your Apache configuration and try again.");
}
*/

error_reporting(-1);
include __DIR__ . '/../class/installwizard.php';
include __DIR__ . '/functions.php';
$pageHasHelp = false;
$pageHasForm = false;

$wizard = new XoopsInstallWizard();
$GLOBALS['installWizard'] = $wizard;

// options for mainfile.php
//$GLOBALS['xoopsOption']["bootstrap"] = false;
$xoopsOption['nocommon'] = true;
if (!empty($xoopsOption['hascommon'])) {
    $xoopsOption['nocommon'] = false;
    //$xoopsOption['bootstrap'] = "setup";
    define('XOOPS_BOOTSTRAP', "setup");
    include '../boot.php';
} else {
    define('XOOPS_BOOTSTRAP', false);
}
<?php
/**
 * Installer language selection page
 *
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Xoops Engine
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @since           3.0
 * @package         Xoops_Setup
 * @version         $Id$
 */

require_once __DIR__ . '/include/common.inc.php';
if (!defined('XOOPS_INSTALL')) { die('XOOPS Installation wizard die'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_REQUEST['persist'])) {
    $wizard->persistentData['persist'] = $_REQUEST['persist'];
    $wizard->redirectToPage('+1');
    exit();
}

$pageHasForm = true;
//$pageHasHelp = true;
$persistSet = false;
$content = '<div>';

if (extension_loaded('apc')) {
    $checked = true;
    $checkedString = 'checked';
} else {
    $checked = false;
    $checkedString = 'disabled';
}
$content .= '<div><input type="radio" name="persist" value="apc" ' . $checkedString . ' />' . _INSTALL_EXTENSION_APC . '</div>';
$content .= '<p>' . _INSTALL_EXTENSION_APC_PROMPT . '</p>';

if (extension_loaded('redis')) {
    $checked = true;
    $checkedString = 'checked';
    $content .= '<div><input type="radio" name="persist" value="redis" ' . $checkedString . ' />' . _INSTALL_EXTENSION_REDIS . '</div>';
    $content .= '<p>' . _INSTALL_EXTENSION_REDIS_PROMPT . '</p>';
}

if (extension_loaded('memcached')) {
    $checkedString = $checked ? '' : ' checked';
    $checked = true;
} else {
    $checkedString = ' disabled';
}
$content .= '<div><input type="radio" name="persist" value="memcached" ' . $checkedString . ' />' . _INSTALL_EXTENSION_MEMCACHED . '</div>';
$content .= '<p>' . _INSTALL_EXTENSION_MEMCACHED_PROMPT . '</p>';

if (extension_loaded('memcache')) {
    $checkedString = $checked ? '' : ' checked';
    $checked = true;
} else {
    $checkedString = ' disabled';
}
$content .= '<div><input type="radio" name="persist" value="memcache" ' . $checkedString . ' />' . _INSTALL_EXTENSION_MEMCACHE . '</div>';
$content .= '<p>' . _INSTALL_EXTENSION_MEMCACHE_PROMPT . '</p>';

if ($checked) {
    $checkedString = '';
} else {
    $checkedString = ' checked';
}
$content .= '<div><input type="radio" name="persist" value="file"' . $checkedString . ' />' . _INSTALL_EXTENSION_FILE . '</div>';
$content .= '<p>' . _INSTALL_EXTENSION_FILE_PROMPT . '</p>';

$content .= '</div>';

include __DIR__ . '/include/install_tpl.php';
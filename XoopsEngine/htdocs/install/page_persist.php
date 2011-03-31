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
$content .= '<div><input type="radio" name="persist" value="apc" ' . $checkedString . ' />APC</div>';
$content .= '<p>The Alternative PHP Cache (APC) is highly recommended for high-performance senario.</p>';
$content .= '<p>Refer to <a href="http://www.php.net/manual/en/intro.apc.php" rel="external" title="APC introduction">APC introduction</a> for details.</p>';

if (extension_loaded('memcached')) {
    $checkedString = $checked ? '' : ' checked';
    $checked = true;
} else {
    $checkedString = ' disabled';
}
$content .= '<div><input type="radio" name="persist" value="memcached"' . $checkedString . ' />Memcached</div>';
$content .= '<p>Memcached is highly recommended for high-performance yet robust distributed senario.</p>';
$content .= '<p>Refer to <a href="http://www.php.net/manual/en/intro.memcached.php" rel="external" title="Memcached introduction">Memcached introduction</a> for details.</p>';

if (extension_loaded('memcache')) {
    $checkedString = $checked ? '' : ' checked';
    $checked = true;
} else {
    $checkedString = ' disabled';
}
$content .= '<div><input type="radio" name="persist" value="memcache"' . $checkedString . ' />Memcache</div>';
$content .= '<p><a href="http://www.php.net/manual/en/intro.memcache.php" rel="external" title="Memcache introduction">Memcache introduction</a></p>';

if ($checked) {
    $checkedString = '';
} else {
    $checkedString = ' checked';
}
$content .= '<div><input type="radio" name="persist" value="file"' . $checkedString . ' />File</div>';
$content .= '<p>Caching storage with files is not recommended. You are highly adviced to check above extensions to ensure they are configured correctly before you are able to choose them.</p>';

$content .= '</div>';

include __DIR__ . '/include/install_tpl.php';
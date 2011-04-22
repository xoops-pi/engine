<?php
/**
 * Installer final page
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

$xoopsOption['hascommon'] = true;
require_once __DIR__ . '/include/common.inc.php';
$wizard->persistentData = array();
//setcookie('xo_install_user', '', null, null, null);
if (!defined('XOOPS_INSTALL')) { die('XOOPS Installation wizard die'); }

register_shutdown_function(array($wizard, 'shutdown'));

$writable_paths = "<ul class='confirmMsg'>";
//foreach ($wizard->configs['writable'] as $path => $data) {
$protectionList = array(
    Xoops::path('www') . '/boot.php',
    Xoops::path('www') . '/.htaccess',
    Xoops::path('var') . '/etc',
);
foreach ($protectionList as $file) {
    @chmod($file, 0644);
    $writable_paths .= "<li class='files'>" . $file . "</li>";
    if (is_dir($file)) {
        $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($file), RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($objects as $object) {
            @chmod($file, 0644);
        }
    }
}
$writable_paths .= "</ul>";

$wizard->loadLangFile("finish");
$content = sprintf(_INSTALL_FINISH_MESSAGE, $writable_paths);


$path = XOOPS::path("var") . "/cache/";
$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);
foreach ($objects as $object) {
    if ($object->isFile() && 'index.html' != $object->getFilename()) {
        unlink($object->getPathname());
    }
}

XOOPS::persist()->clean();
include __DIR__ . '/include/install_tpl.php';
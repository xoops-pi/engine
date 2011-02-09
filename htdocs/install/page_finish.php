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
 * @copyright       The Xoops Engine http://sourceforge.net/projects/xoops/
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
//$wizard->configs['writable']['www'][] = '.htaccess';
foreach ($wizard->configs['writable'] as $path => $data) {
    foreach ($data as $key => $value) {
        if (!is_string($key)) {
            if (false !== strpos($value, ".")) {
                $file = XOOPS::path("{$path}/{$value}");
                chmod($file, 0444);
                $writable_paths .= "<li class='files'>" . $file . "</li>";
            }
            continue;
        }
        foreach ($value as $key2 => $value2) {
            if (is_string($value2) && false !== strpos($value2, ".")) {
                $file = XOOPS::path("{$path}/{$key}/{$value2}");
                chmod($file, 0444);
                $writable_paths .= "<li class='files'>" . $file . "</li>";
            }
        }
    }
}
$writable_paths .= "</ul>";

$wizard->loadLangFile("finish");
$content = sprintf(_INSTALL_FINISH_MESSAGE, $writable_paths);


$path = XOOPS::path("var") . "/cache/";
$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path),
    RecursiveIteratorIterator::SELF_FIRST);
foreach ($objects as $object) {
    if ($object->isFile()) {
        unlink($object->getPathname());
    }
}

XOOPS::persist()->clean();

include __DIR__ . '/include/install_tpl.php';
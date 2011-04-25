<?php
/**
 * Xoops Engine cache cleaning up
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Xoops Engine http://www.xoopsengine.org
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @since           3.0
 * @package         Xoops_Kernel
 * @version         $Id$
 */

defined('XOOPS_BOOTSTRAP') or define('XOOPS_BOOTSTRAP', false);
//define('XOOPS_ENV', 'production');
require __DIR__ . '/boot.php';

$result = array();

$path = XOOPS::path("var") . "/cache/";
$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);
$count = 0;
foreach ($objects as $object) {
    if ($object->isFile() && 'index.html' !== $object->getFilename()) {
        unlink($object->getPathname());
        $count++;
    }
}
$result[] = "Cache folders are cleaned: " . $count . " files removed.";

XOOPS::persist()->clean();
$result[] = "System persist data are cleaned.";

$linebreak = (PHP_SAPI === 'cli') ? PHP_EOL : '<br />';
echo implode($linebreak, $result);
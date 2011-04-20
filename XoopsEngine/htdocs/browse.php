<?php
/**
 * Xoops Engine internal file access
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
 * @todo            specify entries in host? xoops.url/resource/app/mvc/my.css; xoops.url/resource/plugin/comment/some.js
 */


/**#@+
 * Header for big file load, potential values:
 * ACCEL_REDIRECT: nginx X-Accel-Redirect
 * SENDFILE: apache X-Sendfile, see https://tn123.org/mod_xsendfile/
 *
 * In order to use "X-Sendfile", both "XSendFile" and "XSendFilePath" must be configured correctly.
 *
 * Note: No evidence is collected for so-called better performance yet.
 */
//define("XOOPS_HEADER_TYPE", 'SENDFILE');
/*#@-*/

define('XOOPS_BOOTSTRAP', '');
require __DIR__ . '/boot.php';

// Fetch path from query string if path is not set, i.e. through a direct request
if (!empty($_SERVER['QUERY_STRING'])) {
    $path = XOOPS::path(ltrim($_SERVER['QUERY_STRING'], "/"));
}
if (empty($path) || !is_readable($path)) {
    if (substr(PHP_SAPI, 0, 3) == 'cgi') {
        header("Status: 404 Not Found");
    } else {
        header("HTTP/1.1 404 Not Found");
    }
    return;
}

$suffix = strtolower(pathinfo($path, PATHINFO_EXTENSION));
$types = include XOOPS::path('var') . '/etc/mimetypes.php';
//$types = parse_ini_file(XOOPS::path('var/etc/mimetypes.ini.php'));
$content_type = isset($types[$suffix]) ? $types[$suffix] : 'text/plain';
if (in_array($suffix, array('css', 'js', 'gif', 'jpg', 'png'))) {
} else {
    $content_type_category = substr($content_type, 0, strpos($content_type, "/"));
    if (!in_array($content_type_category, array('image', 'text'))) {
        if (substr(PHP_SAPI, 0, 3) == 'cgi') {
            header("Status: 403 Forbidden");
        } else {
            header("HTTP/1.1 403 Forbidden");
        }
        return;
    }
}

header('Content-type: ' . $content_type);
header('Content-Length: ' . filesize($path));

if (defined('XOOPS_HEADER_TYPE')) {
    // For nginx X-Accel-Redirect
    if ('ACCEL_REDIRECT' === XOOPS_HEADER_TYPE) {
        header('X-Accel-Redirect: ' . $path);
        return;
    // For apache X-Sendfile
    } elseif ('SENDFILE' === XOOPS_HEADER_TYPE) {
        header('X-Sendfile: ' . $path);
        return;
    }
}

$handle = fopen($path, "rb");
if (!$handle) {
    return;
}
while (!feof($handle)) {
   $buffer = fread($handle, 4096);
   echo $buffer;
}
fclose($handle);
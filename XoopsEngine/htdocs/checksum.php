<?php
/**
 * Xoops Engine installation md5 checksum script
 *
 * This script allows you to check that the Xoops Engine files have been correctly uploaded.
 * It reads all the XOOPS files and reports missing or invalid ones.
 *
 * Instructions:
 * - Upload this script and xoops.md5 to your XOOPS htdocs
 * - Access it using a browser
 * - Re-upload missing/invalid files
 *
 * @copyright       The Xoops Engine http://www.xoopsengine.org/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @version         $Id$
 * @package         install
 */

// Change the following three lines if you have custom paths
define("PATH_ROOT", ".");
define("PATH_LIB", "../lib");
define("PATH_USR", "../usr");
define("PATH_VAR", "../var");
define("PATH_IMG", "./img");

// DON'T change following code
error_reporting(0);

header("Content-type: text/plain");
$message = array();

$md5_file = __DIR__ . DIRECTORY_SEPARATOR . "checksum.md5";

if (!is_readable($md5_file)) {
    $message[] = "{$md5_file} file not found.";
} else {
    $sums = json_decode(file_get_contents($md5_file), true);
    $paths = array(
        "www"   => PATH_ROOT,
        "lib"   => PATH_LIB,
        "usr"   => PATH_USR,
        "var"   => PATH_VAR,
        "img"   => PATH_IMG,
    );
    $num_files = 0;
    foreach ($paths as $key => $path) {
        $num_files += check_folder($key, $path);
    }
    $message[] = "There are {$num_files} files checked.";
    $message[] = "Please remove the file {$md5_file} and " . basename(__FILE__) . " as soon as possible.";
}

//$linebreak = (PHP_SAPI === 'cli') ? PHP_EOL : '<br />';
$linebreak = PHP_EOL;
echo implode($linebreak, $message);

function check_file($line, $path)
{
    list($file, $sum) = explode(":", $line, 2);
    $file = $path . "/" . $file;
    if (!file_exists($file)) {
        $GLOBALS['message'][] = "File missing: {$file}";
    } else {
        $txt = file_get_contents($file);
        $txt = str_replace(array("\r\n", "\r"), "\n", $txt);
        if (md5($txt) != $sum) {
            $GLOBALS['message'][] = "File invalid: {$file}";
        }
    }
}

function check_folder($key, $path)
{
    $sum = $GLOBALS["sums"][$key];
    //echo "\n==== {$key} ====\n";
    foreach ($sum as $line) {
        check_file($line, $path);
        $num_files ++;
        //flush();
    }

    return $num_files;
}
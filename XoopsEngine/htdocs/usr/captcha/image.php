<?php
/**
 * Xoops Engine CAPTCHA image display
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
 * @package         CAPTCHA
 * @version         $Id$
 */

define('XOOPS_BOOT_SKIP', 1);
define('XOOPS_ENV', 'production');
require __DIR__ . '/../../boot.php';
$options = array();
// admin section may use different session storage from frond end
$options['bootstrap']['resources']['session']['config'] = 'session' . (empty($_GET['section']) ? '' : '.' . htmlspecialchars($_GET['section'], ENT_QUOTES, 'UTF-8'));
/*
$options['bootstrap']['resources']['session'] = array(
    'name'          => 'XOOPSSESSION',
    'savehandler'   => 'Db',
);
*/
XOOPS::boot("", $options);

$id = htmlspecialchars($_GET['id'], ENT_QUOTES, 'UTF-8');
$iamge = false;
if (!empty($id)) {

    $options = array(
        "wordLen"           => 6,
        'width'             => 150,
        'height'            => 50,
        'dotNoiseLevel'     => 30,
        'lineNoiseLevel'    => 1,
        "font"      => XOOPS::path('img/captcha/fonts/Vera.ttf'),
    );

    $captcha = new Xoops_Zend_Captcha_Image($options);
    $image = $captcha->createImage($id);
    session_write_close();
}
if (empty($image)) {
    if (substr(PHP_SAPI, 0, 3) == 'cgi') {
        header("Status: 404 Not Found");
    } else {
        header("HTTP/1.1 404 Not Found");
    }
    return;
}

//return;
header("Content-type: image/png");
imagepng($image);
imagedestroy($image);
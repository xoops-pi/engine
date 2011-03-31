<?php
/**
 * Installer system module creation page
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
if (!defined('XOOPS_INSTALL')) { die('XOOPS Installation wizard die'); }

include_once __DIR__ . '/class/dbmanager.php';
$dbm = new db_manager();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //$GLOBALS['setup_system_language'] = $wizard->language;
    //$GLOBALS['setup_system_locale'] = $wizard->locale['lang'];
    //$GLOBALS['setup_system_charset'] = $wizard->locale['charset'];
    Xoops::config('language', $wizard->language);
    Xoops::config('locale', $wizard->locale['lang']);
    Xoops::config('charset', $wizard->locale['charset']);
    if (!empty($_POST['retry'])) {
        $ret = Xoops_Installer::instance()->uninstall("system");
        if (!$ret) {
            $pageHasForm = true;
            $content = '<div class="x2-note errorMsg">' . _INSTALL_SYSTEM_INSTALLED_FAILED . "</div>" .
                        Xoops_Installer::instance()->getMessage() .
                        "<input type='hidden' name='retry' value='1' />";
        }
    }
    $ret = Xoops_Installer::instance()->install("system");
    if ($ret) {
        $content = '<div class="x2-note successMsg">' . _INSTALL_SYSTEM_INSTALLED_SUCCESS . "</div>";
        //$wizard->redirectToPage('+1');
    } else {
        $pageHasForm = true;
        $content = '<div class="x2-note errorMsg">' . _INSTALL_SYSTEM_INSTALLED_FAILED . "</div>" .
                    Xoops_Installer::instance()->getMessage() .
                    "<input type='hidden' name='retry' value='1' />";
    }
} elseif ($dbm->tableExists('module')) {
    $pageHasForm = true;
    $content = '<div class="x2-note confirmMsg">' . _INSTALL_SYSTEM_ALREADY_INSTALLED . "</div>" .
                "<input type='hidden' name='retry' value='1' />";
} else {
    $pageHasForm = true;
    $content = '<div class="x2-note confirmMsg">' . _INSTALL_SYSTEM_TO_INSTALL . '</div>';
}

include __DIR__ . '/include/install_tpl.php';
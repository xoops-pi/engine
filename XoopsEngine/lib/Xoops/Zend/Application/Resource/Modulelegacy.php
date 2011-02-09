<?php
/**
 * Zend Framework for Xoops Engine
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
 * @category        Xoops_Zend
 * @package         Application
 * @subpackage      Resource
 * @version         $Id$
 */

class Xoops_Zend_Application_Resource_Modulelegacy extends Zend_Application_Resource_ResourceAbstract
{
    public function init()
    {
        global $xoopsModule, $xoopsUser, $module_handler;

        if (file_exists('./xoops_version.php')) {
            $url_arr = explode('/', strstr($_SERVER['PHP_SELF'], '/modules/'), 4);
            $dirname = $url_arr[2];
            $module_handler = xoops_gethandler('module');
            $xoopsModule = $module_handler->getByDirname($dirname);
            if (!$xoopsModule || !$xoopsModule->getVar('isactive')) {
                throw new Exception("Module unavailable!", 404);
            }
            Xoops::service('translate')->loadTranslation("main", $dirname);

            /*
            $moduleperm_handler = xoops_gethandler('groupperm');
            if ($xoopsUser) {
                if (!$moduleperm_handler->checkRight('module_read', $xoopsModule->getVar('mid'), $xoopsUser->getGroups())) {
                    redirect_header(XOOPS_URL, 1, _NOPERM, false);
                    exit();
                }
                $xoopsUserIsAdmin = $xoopsUser->isAdmin($xoopsModule->getVar('mid'));
            } else {
                if (!$moduleperm_handler->checkRight('module_read', $xoopsModule->getVar('mid'), XOOPS_GROUP_ANONYMOUS)) {
                    redirect_header(XOOPS_URL . "/user.php?from=" . $xoopsModule->getVar('dirname', 'n'), 1, _NOPERM);
                    exit();
                }
            }
            xoops_loadLanguage('main', $xoopsModule->getVar('dirname', 'n'));
            $config_handler = xoops_gethandler('config');
            if ($xoopsModule->getVar('hasconfig') == 1 || $xoopsModule->getVar('hascomments') == 1 || $xoopsModule->getVar( 'hasnotification' ) == 1) {
                $xoopsModuleConfig = $config_handler->getConfigsByCat(0, $xoopsModule->getVar('mid'));
            }
            */
        } elseif ($xoopsUser) {
            //$xoopsUserIsAdmin = $xoopsUser->isAdmin(1);
        }
    }
}
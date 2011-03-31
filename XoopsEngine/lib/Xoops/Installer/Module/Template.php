<?php
/**
 * XOOPS module template installer
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Xoops Engine http://www.xoopsengine.org/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @since           3.0
 * @package         Xoops_Installer
 * @subpackage      Installer
 * @version         $Id$
 */

class Xoops_Installer_Module_Template extends Xoops_Installer_Abstract
{
    public function install(&$message)
    {
        $message = $this->message;
        if ($view = XOOPS::registry('view')) {
            $view->getEngine()->clearModuleCache($this->module->dirname);
        }
    }

    public function update(&$message)
    {
        $message = $this->message;
        if ($view = XOOPS::registry('view')) {
            $view->getEngine()->clearModuleCache($this->module->dirname);
        }
    }

    public function uninstall(&$message)
    {
        if (!is_object($this->module)) {
            return;
        }
        $message = $this->message;
        if ($view = XOOPS::registry('view')) {
            $view->getEngine()->clearModuleCache($this->module->dirname);
        }
    }
}
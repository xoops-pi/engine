<?php
/**
 * XOOPS module translate installer
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

class Xoops_Installer_Module_Translate extends Xoops_Installer_Abstract
{
    public function install(&$message)
    {
        XOOPS::service('registry')->translate->create($this->module->dirname);
        return true;
    }

    public function update(&$message)
    {
        XOOPS::service('registry')->translate->flush($this->module->dirname);
    }

    public function uninstall(&$message)
    {
        if (!is_object($this->module)) {
            return;
        }
        XOOPS::service('registry')->translate->flush($this->module->dirname);
    }
}
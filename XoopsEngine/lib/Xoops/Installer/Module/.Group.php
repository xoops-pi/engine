<?php
/**
 * XOOPS module group installer
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

class Xoops_Installer_Module_Group extends Xoops_Installer_Abstract
{
    public function install(&$message)
    {
        $message = $this->message;
        $groups = array(XOOPS_GROUP_ADMIN, XOOPS_GROUP_USERS, XOOPS_GROUP_ANONYMOUS);
        $gperm_handler = XOOPS::getHandler('groupperm');
        foreach ($groups as $mygroup) {
            if ($gperm_handler->checkRight('module_admin', 0, $mygroup)) {
                $mperm = $gperm_handler->create();
                $mperm->setVar('gperm_groupid', $mygroup);
                $mperm->setVar('gperm_itemid', $this->module->id);
                $mperm->setVar('gperm_name', 'module_admin');
                $mperm->setVar('gperm_modid', 1);
                if (!$gperm_handler->insert($mperm)) {
                    $message[] = "Admin permission failed assigned to group {$mygroup}";
                } else {
                    $message[] = "Admin permission assigned to group {$mygroup}";
                }
                unset($mperm);
            }
            $mperm = $gperm_handler->create();
            $mperm->setVar('gperm_groupid', $mygroup);
            $mperm->setVar('gperm_itemid', $this->module->id);
            $mperm->setVar('gperm_name', 'module_read');
            $mperm->setVar('gperm_modid', 1);
            if (!$gperm_handler->insert($mperm)) {
                $message[] = "Access permission failed assigned to group {$mygroup}";
            } else {
                $message[] = "Access permission assigned to group {$mygroup}";
            }
            unset($mperm);
        }
    }

    public function update(&$message)
    {
    }

    public function uninstall(&$message)
    {
        global $xoopsDB;
        if (!is_object($this->module)) {
            return;
        }

        $message = $this->message;
        $gperm_handler = XOOPS::getHandler('groupperm');
        $crit_name= new CriteriaCompo(new Criteria('gperm_name', 'module_read'));
        $crit_name->add(new Criteria('gperm_name', 'module_admin'), 'OR');
        $criteria = new CriteriaCompo($crit_name);
        $criteria->add(new Criteria('gperm_itemid', $this->module->id));
        $gperm_handler->deleteAll($criteria);
        $items = $xoopsDB->getAffectedRows();
        $gperm_handler->deleteByModule($this->module->id);
        $items += $xoopsDB->getAffectedRows();
        $messages[] = "Module permissions removed: " . $items;
    }
}
<?php
/**
 * Legacy module event observer class
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
 * @category        Xoops_Module
 * @package         Legacy
 * @version         $Id$
 */

class Module_Legacy_User
{
    public static function register($data, $module)
    {
        $member_handler = XOOPS::getHandler('member');
        $member_handler->addUserToGroup(XOOPS_GROUP_USERS, $data['id']);
    }

    public static function delete($data, $module)
    {
        $member_handler = XOOPS::getHandler('group');
        $member_handler->deleteUser($data['id']);
    }

    public static function activate($data, $module)
    {
    }
}
<?php
/**
 * User module installer
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
 * @category        Xoops_Module
 * @package         User
 * @version         $Id$
 */

class App_User_Installer extends Xoops_Installer_Abstract
//class User_Installer extends Xoops_Installer_Abstract
{
    public function postInstall(&$message)
    {
        $module = $this->module->dirname;
        $message = $this->message;
        $categoryList = array(
            array(
                "key"   => "basic",
                "title" => "Basic",
            ),
            array(
                "key"   => "contact",
                "title" => "Contact",
            ),
            array(
                "key"   => "preference",
                "title" => "Preference",
            ),
            array(
                "key"   => "stats",
                "title" => "Stats",
            ),
        );
        $modelCategory = Xoops::service('module')->getModel("category", "user");
        foreach ($categoryList as $category) {
            $modelCategory->insert($category);
        }

        if (!\App\User\Gateway::read(1)) {
            $defaultUser = array(
                "id"            => 1,
                "identity"      => "sysop",
                "credential"    => "sysop",
                "email"         => "sysop@example.org",
                "name"          => "SysOp",
                "active"        => 1,
                "create_time"   => time(),
            );
            \App\User\Gateway::create($defaultUser);
        }
    }
}
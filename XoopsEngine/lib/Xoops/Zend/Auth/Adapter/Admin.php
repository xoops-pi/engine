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
 * @copyright       Xoops Engine http://www.xoopsengine.org/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @since           3.0
 * @category        Xoops_Zend
 * @package         Auth
 * @subpackage      Adapter
 * @version         $Id$
 */

class Xoops_Zend_Auth_Adapter_Admin extends Xoops_Zend_Auth_Adapter_Application
{
    public function wakeup(&$data)
    {
        //global $xoopsUser;

        // Overwrite custom expiration time set by "rememberme" for security consideration
        $sessionLifetType =  Xoops::service("session")->getSaveHandler()->getLifeTime();
        if (empty($data["time"]) || ($data["time"] + $sessionLifetType) < time()) {
            return false;
        } else {
            $data["time"] = time();
        }
        if (!isset($data['role'])) {
            $data['role'] = Xoops_User::getRole($data['id']);
        }
        /*
        if (empty($data["id"])) {
            $user = XOOPS::getHandler('user')->create();
            $user->assignVars(array(
                "uname" => $data["identity"],
                "name"  => $data["name"],
            ));
        } else {
            $user = XOOPS::getHandler('user')->get($data["id"]);
            if (is_object($user)) {
                if (isset($data['groups'])) {
                    $user->setGroups($data['groups']);
                } else {
                    $data['groups'] = $user->getGroups();
                }
            }
        }

        $xoopsUser = $user;
        */
        return true;
    }
}

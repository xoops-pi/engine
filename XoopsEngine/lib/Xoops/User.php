<?php
/**
 * XOOPS user container
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
 * @package         Xoops_Core
 * @version         $Id$
 */

class Xoops_User
{
    /**
     * User role
     */
    public $role = "guest";
    public $id = 0;
    public $name = "";
    public $identity = "";

    public function __construct($data = null)
    {
        if (isset($data)) {
            $this->assign($data);
        }
    }

    public function assign($data)
    {
        $this->id       = isset($data['id']) ? $data['id'] : null;
        $this->identity = isset($data['identity']) ? $data['identity'] : $this->identity;
        $this->name     = isset($data['name']) ? $data['name'] : null;
        $this->role     = isset($data['role']) ? $data['role'] : $this->role;

        return $this;
    }

    public function profile()
    {
        $userModel = XOOPS::getModel("user");
        $userRow = $userModel->findRow($this->id);
        return $userRow;
    }

    public function isGuest()
    {
        return empty($this->identity) ? true : false;
    }

    // To be removed
    public function setRole($role)
    {
        $this->role = $role;
    }

    public static function getProfile($account)
    {
        if ($account instanceof self) {
            return $account->profile();
        }
        $userModel = XOOPS::getModel("user");
        $userRow = $userModel->findRow($account);
        return $userRow;
    }

    // To be removed
    public static function getRole($id = null)
    {
        $id = is_null($id) ? XOOPS::registry("user")->id : $id;
        if (empty($id)) {
            return Xoops_Acl::GUEST;
        }
        if (XOOPS::registry("user") !== null && $id == XOOPS::registry("user")->id && XOOPS::registry("user")->role !== null) {
            return XOOPS::registry("user")->role;
        }
        $user = XOOPS::getModel('acl_user')->findRow($id);
        $role = $user->role;
        return $role;
    }
}
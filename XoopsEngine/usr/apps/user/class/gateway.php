<?php
/**
 * User model gateway
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

namespace App\User;

class Gateway
{
    protected static $models = array();
    protected static $cols = array();

    /**
     * Get user data fields
     *
     * @return array
     */
    public static function getCols($model)
    {
        if (!isset(static::$cols[$model])) {
            static::$cols[$model] = static::getModel($model)->info("cols");
        }
        return static::$cols[$model];
    }

    public static function getModel($model)
    {
        if (!isset(static::$models[$model])) {
            static::$models[$model] = \XOOPS::getModel($model);
        }
        return static::$models[$model];
    }

    /**
     * Create a user and populate corresponding profile/data
     *
     * @param array $data   associative array of user data
     * @param array $message message list
     * @return mixed    user ID on success; false on failure
     */
    public static function create($data, &$message = null)
    {
        $userModel = \XOOPS::getModel("user");
        $userRow = $userModel->createRow();
        //$colsAccount = static::getCols("user_account");
        //$user = array();
        foreach ($data as $col => $val) {
            $userRow->{$col} = $val;
            //Debug::e("$col => $val");
        }
            //Debug::e($data);
            //Debug::e($userRow->toArray());

        $id = $userRow->save();
        if (!$id) {
            $message[] = \XOOPS::_("User account was not created.");
            return false;
        }
        $profileRow = $userRow->profile();
        $profileRow->user = $id;
        $status = $profileRow->save();
        if (!$status) {
            $userRow->delete();
            $message[] = \XOOPS::_("User profile was not created.");
            return false;
        }

        if (isset($data["role"])) {
            $userRoleModel = \XOOPS::getModel("acl_user");
            $row = $userRoleModel->createRow(array("user" => $id, "role" => $data["role"]));
            $status = $row->save();
            if (!$status) {
                $message[] = \XOOPS::_("User role was not created.");
            }
        }
        return $id;

        /*
        $data["user"] = $id;
        $profileModel = static::getModel("user_profile");
        $columnsProfile = static::getCols("user_profile");
        $profile = array();
        foreach ($columnsProfile as $col) {
            if (isset($data[$col])) {
                $profile[$col] = $data[$col];
                unset($data[$col]);
            }
        }
        $status = $profileModel->createRow($profile)->save();
        if (!$status) {
            $userModel->delete(array("id = ?" => $id));
            $message[] = XOOPS::_("User profile was not created.");
            return false;
        }
        return $id;
        */
    }

    /**
     * Update a user
     *
     * @param array $data   associative array of user data
     * @param array $message message list
     * @return boolean
     */
    public static function update($data, &$message = null)
    {
        if (empty($data["id"])) {
            $message[] = \XOOPS::_("User ID is required.");
            return false;
        }
        $id = $data["id"];
        unset($data["id"]);
        $userModel = \XOOPS::getModel("user");
        $userRow = $userModel->findRow($id);
        if (!$userRow) {
            $message[] = \XOOPS::_("User identity '" . $id . "' was not found.");
            return false;
        }
        foreach ($data as $col => $val) {
            $userRow->{$col} = $val;
        }
        $status = $userRow->save();
        if (!$status) {
            $message[] = \XOOPS::_("User account was not updated.");
            return false;
        }
        $profileRow = $userRow->profile();
        $status = $profileRow->save();
        if (!$status) {
            $message[] = \XOOPS::_("User profile was not updated.");
            return false;
        }
        if (isset($data["role"])) {
            $userRoleModel = \XOOPS::getModel("acl_user");
            $row = $userRoleModel->findRow($id);
            $row->role = $data["role"];
            $status = $row->save();
            if (!$status) {
                $message[] = \XOOPS::_("User role was not saved.");
            }
        }

        return $status;

        /*
        $id = $data["id"];
        $userModel = XOOPS::getModel("user_account");
        $userRow = $userModel->findRow($id);
        if (!$userRow) {
            $message[] = XOOPS::_("User identity '" . $id . "' was not found.");
            return false;
        }
        $user = array();
        $colsAccount = static::getCols("user_account");
        foreach ($colsAccount as $col) {
            if (isset($data[$col])) {
                $user[$col] = $data[$col];
                unset($data[$col]);
            }
        }
        if (!empty($user)) {
            $status = $userModel->update($user, array("id = ?" => $id));
            if (!$status) {
                $message[] = XOOPS::_("User identity  '" . $id . "' was not updated.");
                return false;
            }
        }

        $profileModel = static::getModel("user_profile");
        $columnsProfile = static::getCols("user_profile");
        $profile = array();
        foreach ($columnsProfile as $col) {
            if (isset($data[$col])) {
                $profile[$col] = $data[$col];
                unset($data[$col]);
            }
        }
        if (!empty($profile)) {
            $profileRow = $profileModel->findRow($id);
            if ($profileRow) {
                $status = $profileModel->update($profile, array("user = ?" => $id));
            } else {
                $profile["user"] = $id;
                $status = $profileModel->insert($profile);
            }
            if (!$status) {
                $message[] = XOOPS::_("User profile was not updated.");
                return false;
            }
        }
        return true;
        */
    }

    /**
     * Read a user's data
     *
     * @param int   $id   user ID
     * @param array $message message list
     * @return array
     */
    public static function read($id, &$message = null)
    {
        $userModel = \XOOPS::getModel("user");
        $profileModel = \XOOPS::getModel("user_profile");
        $select = $userModel->getAdapter()->select()
                                                ->from(array("u" => $userModel->info("name")),
                                                    array("id", "identity", "name", "email"))
                                                ->join(array("p" => $profileModel->info("name")),
                                                    "p.user = u.id")
                                                ->where("u.id = ?", $id);
        $row = $userModel->getAdapter()->fetchRow($select);
        if (!$row) {
            $message[] = \XOOPS::_("No record for user '{$id}' was found.");
        }

        return $row;
    }

    /**
     * Delete a user
     *
     * @param int   $id   user ID
     * @param array $message message list
     * @return boolean
     */
    public static function delete($id, &$message = null)
    {
        $status = true;
        $userModel = \XOOPS::getModel("user");
        $userRow = $userModel->findRow($id);
        if (!$userRow) {
            $message[] = \XOOPS::_("User identity '" . $id . "' was not found.");
            return false;
        }
        $profileRow = $userRow->profile();
        $state = $profileRow->delete();
        if (!$state) {
            $message[] = \XOOPS::_("User profile '{$id}' was not deleted.");
            $status = false;
        }
        $state = $userRow->delete();
        if (!$state) {
            $message[] = \XOOPS::_("User account '{$id}' was not deleted.");
            $status = false;
        }
        $userRoleModel = \XOOPS::getModel("acl_user");
        if ($row = $userRoleModel->findRow($id)) {
            $state = $row->delete();
            if (!$state) {
                $message[] = \XOOPS::_("User role was not deleted.");
                $status = false;
            }
        }

        return $status;
    }

    /**
     * Save user's data
     *
     * It is recommended to use User_Api_Manipulation::create or User_Api_Manipulation::update explicitly
     *
     * @param array $data   associative array of user data
     * @param array $message message list
     * @return mixed    user ID on success; false on failure
     */
    public static function save($data, &$message = null)
    {
        $isNew = true;
        if (!empty($data["id"])) {
            $isNew = false;
        } else {
            $userModel = \XOOPS::getModel("user");
            $userRow = $userModel->fetchRow(array("identity = ?" => $data["identity"]));
            if ($userRow) {
                $isNew = false;
            }
        }

        if ($isNew) {
            $status = static::create($data, $message);
        } else {
            $status = static::update($data, $message);
        }
        return $status;
    }
}
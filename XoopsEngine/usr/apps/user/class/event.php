<?php
/**
 * User module event observer class
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

class Event
{
    const MODULE = "user";

    public static function register($data, $module)
    {
        $model = \XOOPS::getModel("user_profile");
        $profile = $model->findRow($data["id"]);
        if (!$profile) {
            $profile = $model->createRow();
            $profile->user = $data["id"];
        }
        $profile->create_time = time();
        $profile->create_ip = \XOOPS::registry("frontController")->getRequest()->getClientIP();
        $profile->save();
    }

    public static function activate($user, $module)
    {
        $model = \Xoops::service('module')->getModel("log", self::MODULE);
        $log = $model->findRow($user->id);
        if (!$log) {
            $log = $model->createRow();
            $log->user = $user->id;
        }
        $log->activate_time = time();
        $log->activate_ip = \XOOPS::registry("frontController")->getRequest()->getClientIP();
        $log->save();
    }

    public static function update($user, $module)
    {
        $model = \Xoops::service('module')->getModel("log", self::MODULE);
        $log = $model->findRow($user->id);
        if (!$log) {
            $log = $model->createRow();
            $log->user = $user->id;
        }
        $log->update_time = time();
        $log->update_ip = \XOOPS::registry("frontController")->getRequest()->getClientIP();
        $log->save();
    }

    public static function login($data, $module)
    {
        $configs = \XOOPS::service("registry")->config->read(self::MODULE, "login");
        if (empty($configs["log_onsuccess"])) {
            //return true;
        }

        $model = \Xoops::service('module')->getModel("log", self::MODULE);
        $log = $model->findRow($data["id"]);
        if (!$log) {
            $log = $model->createRow();
            $log->user = $data["id"];
        }
        $log->login_time = time();
        $log->login_ip = \XOOPS::registry("frontController")->getRequest()->getClientIP();
        $log->save();
    }

    /**
     * Logging failure information
     *
     * @param array $data   Array of logging data: 0 - identity value, 1 - number of attemps, 2 - identity key
     *
     */
    public static function login_failure($data, $module)
    {
        $configs = \XOOPS::service("registry")->config->read(self::MODULE, "login");
        if (empty($configs["log_onfailure"])) {
            //return true;
        }

        $modelUser = \XOOPS::getModel("user");
        $identityKey = empty($data[2]) ? "identity" : $data[2];
        $identityKey = $modelUser->getAdapter()->quoteIdentifier($identityKey);
        $select = $modelUser->select()->where($identityKey . " = ?", $data[0]);
        $user = $modelUser->fetchRow($select);
        if (!$user) {
            return;
        }
        $model = \Xoops::service('module')->getModel("log", self::MODULE);
        $log = $model->findRow($user->id);
        if (!$log) {
            $log = $model->createRow();
            $log->user = $user->id;
        }
        if (isset($data[1])) {
            $log->attemps = $data[1];
        }
        $log->attempt_time = time();
        $log->attempt_ip = \XOOPS::registry("frontController")->getRequest()->getClientIP();
        $log->save();
    }

    /**
     * Clean profile-meta cache upon module action
     *
     * @param string $module Module dirname
     */
    public static function profile($module, $module)
    {
        $modelMeta = \XOOPS::getModel("user_meta");
        $select = $modelMeta->select()->where("module = ?", $module)->order("id ASC")->from($modelMeta, array("key", "category"));
        $metaList = $modelMeta->getAdapter()->fetchPairs($select);
        if ($metaList) {
            $modelCategory = \Xoops::service('module')->getModel("meta_category", "user");
            $select = $modelCategory->select()->where("meta IN (?)", array_keys($metaList));
            $existList = $modelCategory->getAdapter()->fetchCol($select);
            $todoList = array_diff(array_keys($metaList), $existList);
            $order = 99;
            foreach ($todoList as $meta) {
                $modelCategory->insert(array("meta" => $meta, "category" => $metaList[$meta], "order" => $order++));
            }
        }
        \XOOPS::service("registry")->handler("meta", "user")->flush();
    }
}
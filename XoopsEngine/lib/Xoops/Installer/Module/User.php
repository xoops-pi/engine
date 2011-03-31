<?php
/**
 * XOOPS module user profile plugin installer
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

/**
 * User profile column definition:

"fieldName"   => array(
    'title'         => "Field title",
    'category'      => "",
    'attribute'     => "",
    'view'          => "class::method",
    'edit'          => "class::method",
    'save'          => "class::method",
),
*/

class Xoops_Installer_Module_User extends Xoops_Installer_Abstract
{
    private static $userMeta;

    public function install(&$message)
    {
        $module = $this->module->dirname;
        $message = $this->message;
        $status = true;

        if (empty($this->config)) {
            return;
        }

        $columns = $this->getColumns();
        foreach ($this->config as $key => $meta) {
            if (in_array($key, $columns)) {
                if (!empty($meta["updated"])) {
                    static::dropField($key);
                } else {
                    $message[] = XOOPS::_("Meta '{$key}' was not added: name is already in use.");
                    continue;
                }
            }
            $meta["module"] = $module;
            $meta["key"] = $key;
            $meta["active"] = 1;
            $status = static::addField($meta) * $status;
        }
        XOOPS::service('registry')->user->flush();
        static::cleanMetadataCache();

        return $status;
    }

    public function update(&$message)
    {
        $module = $this->module->dirname;
        $message = $this->message;
        $status = true;

        if (version_compare($this->version, $this->module->version, ">=")) {
            return true;
        }

        $metaModel = XOOPS::getModel("user_meta");
        $columns = $this->getColumns();
        foreach ($this->config as $key => $meta) {
            if (in_array($key, $columns)) {
                continue;
            }
            $meta["module"] = $module;
            $meta["key"] = $key;
            $status = static::addField($meta) * $status;
        }
        $select = $metaModel->select()->where("module = ?", $module)->from($metaModel, "key");
        $columnsProfile = $metaModel->getAdapter()->fetchCol($select);
        $meta_delete = array_diff($columnsProfile, array_keys($this->config));
        foreach ($meta_delete as $key) {
            static::dropField($key);
        }
        XOOPS::service('registry')->user->flush();
        static::cleanMetadataCache();

        return $status;
    }

    public function uninstall(&$message)
    {
        if (!is_object($this->module)) {
            return;
        }

        $module = $this->module->dirname;
        $message = $this->message;
        $status = true;

        if (!$metaModel = XOOPS::getModel("user_meta")) {
            return;
        }
        $select = $metaModel->select()->where("module = ?", $module)->from($metaModel, "key");
        $columnsProfile = $metaModel->getAdapter()->fetchCol($select);
        foreach ($columnsProfile as $key) {
            static::dropField($key);
        }
        XOOPS::service('registry')->user->flush();
        static::cleanMetadataCache();

        return true;
    }

    public function activate(&$message)
    {
        $module = $this->module->dirname;
        $message = $this->message;
        $status = true;

        if (!$metaModel = XOOPS::getModel("user_meta")) {
            return;
        }
        $select = $metaModel->select()->where("module = ?", $module)->where("active = ?", 0)->from($metaModel, "key");
        $columnsProfile = $metaModel->getAdapter()->fetchCol($select);
        foreach ($this->config as $key => $meta) {
            if (!in_array($key, $columnsProfile)) {
                continue;
            }
            //$meta["module"] = $module;
            //$meta["name"] = XOOPS::registry("Db")->foldCase($key);
            $status = static::activateField($key) * $status;
        }

        XOOPS::service('registry')->user->flush();
        static::cleanMetadataCache();

        return true;
    }

    public function deactivate(&$message)
    {
        $module = $this->module->dirname;
        $message = $this->message;
        $status = true;

        if (!$metaModel = XOOPS::getModel("user_meta")) {
            return;
        }
        $select = $metaModel->select()->where("module = ?", $module)->where("active = ?", 1)->from($metaModel, "key");
        $columnsProfile = $metaModel->getAdapter()->fetchCol($select);
        foreach ($this->config as $key => $meta) {
            if (!in_array($key, $columnsProfile)) {
                continue;
            }
            //$key = XOOPS::registry("Db")->foldCase($key);
            $status = static::deactivateField($key) * $status;
        }
        XOOPS::service('registry')->user->flush();
        static::cleanMetadataCache();

        return true;
    }

    public static function addField($col)
    {
        $modelMeta = XOOPS::getModel("user_meta");
        $colsMeta = $modelMeta->info("cols");
        $col["key"] = XOOPS::registry("Db")->foldCase($col["key"]);
        //$meta = array("key" => $col["name"]);
        /*
        if (!empty($col["edit"])) {
            $col["edit"] = serialize($col["edit"]);
        }
        if (!empty($col["admin"])) {
            $col["admin"] = serialize($col["admin"]);
        }
        if (!empty($col["search"])) {
            $col["search"] = serialize($col["search"]);
        }
        if (!empty($col["options"])) {
            $col["options"] = serialize($col["options"]);
        }
        */
        foreach ($col as $key => &$val) {
            if (is_array($val)) {
               $val = serialize($val);
            }
        }
        if (isset($col["category"]) && false === $col["category"]) {
            $col["category"] = "-";
        }
        foreach ($colsMeta as $key) {
            if (isset($col[$key])) {
                $meta[$key] = $col[$key];
            }
        }
        $status = $modelMeta->insert($meta);
        if (!$status) {
            return false;
        }
        if (!empty($col["active"])) {
            $status = static::addColumn($col);
            if (!$status) {
                $modelMeta->delete(array($modelMeta->getAdapter()->quoteIdentifier("id") . " = ?" => $key));
            }
        }

        return $status;
    }

    public static function dropField($col)
    {
        $modelMeta = XOOPS::getModel("user_meta");
        $row = $modelMeta->fetchRow(array($modelMeta->getAdapter()->quoteIdentifier("key") . " = ?" => $col));
        if (!$row) {
            return false;
        }
        if ($row->active) {
            if (!$status = static::dropColumn($col)) {
                return false;
            }
        }
        $status = $row->delete();

        return $status;
    }

    public static function activateField($key)
    {
        $key = XOOPS::registry("Db")->foldCase($key);
        $modelMeta = XOOPS::getModel("user_meta");
        $row = $modelMeta->fetchRow(array($modelMeta->getAdapter()->quoteIdentifier("key") . " = ?" => $key));
        $col = array(
            "key"       => $key,
            "attribute" => $row->attribute,
        );
        $status = static::addColumn($col);
        if ($status) {
            $row->active = 1;
            $status = $row->save();
            /*
            $modelMeta = XOOPS::getModel("user_meta");
            $meta = array("active" => 1);
            $status = $modelMeta->update($meta, array($modelMeta->getAdapter()->quoteIdentifier("key") . " = ?" => $col["name"]));
            */
        }

        return $status;
    }

    public static function deactivateField($key)
    {
        $col = XOOPS::registry("Db")->foldCase($key);
        $modelMeta = XOOPS::getModel("user_meta");
        $row = $modelMeta->fetchRow(array($modelMeta->getAdapter()->quoteIdentifier("key") . " = ?" => $key));
        $status = static::dropColumn($key);
        if ($status) {
            $row->active = 0;
            $status = $row->save();
            /*
            $modelMeta = XOOPS::getModel("user_meta");
            $meta = array("active" => 0);
            $status = $modelMeta->update($meta, array($modelMeta->getAdapter()->quoteIdentifier("key") . " = ?" => $key));
            */
        }

        return $status;
    }

    protected static function addColumn($col)
    {
        $modelProfile = XOOPS::getModel("user_profile");
        $query = "ALTER TABLE " . $modelProfile->info("name") . " ADD COLUMN " . $col["key"]
                . " " . (empty($col["attribute"]) ? "varchar(255) NOT NULL default ''" : $col["attribute"]);
        $result = XOOPS::registry("Db")->query($query) ? true : false;
        return $result;
    }

    protected static function dropColumn($col)
    {
        $modelProfile = XOOPS::getModel("user_profile");
        $query = "ALTER TABLE " . $modelProfile->info("name") . " DROP COLUMN " . $col;
        $result = XOOPS::registry("Db")->query($query);
        return $result;
    }

    protected function getColumns()
    {
        if (!isset(self::$userMeta)) {
            $columnsAccount = XOOPS::getModel("user_account")->info("cols");
            $metaModel = XOOPS::getModel("user_meta");
            $select = $metaModel->select()->from($metaModel, "key");
            $columnsProfile = $metaModel->getAdapter()->fetchCol($select);
            self::$userMeta = array_merge($columnsAccount, $columnsProfile);
        }

        return self::$userMeta;
    }

    public static function cleanMetadataCache()
    {
        //XOOPS::getModel("user_profile")->getMetadataCache()->clean('matchingTag', array("model"));
        XOOPS::getModel("user_profile")->cleanMetaCache();
    }
}
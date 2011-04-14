<?php
/**
 * XOOPS module configuration installer
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
 * Config column definition:

array(
    'name'          => "config_name",
    'title'         => "Config title",
    'category'      => "",
    'description'   => "",
    'default'       => "",
    'edit'          => "formelement",
    //'edit'        => array('module' => 'dirname', 'type' => "formelement"),
    'filter'        => "filtertype",
),
*/

class Xoops_Installer_Module_Config extends Xoops_Installer_Abstract
{
    public function install(&$message)
    {
        $module = $this->module->dirname;
        XOOPS::service('registry')->config->flush($module);
        $message = $this->message;
        $status = true;

        $categories = array();
        if (!empty($this->config['categories'])) {
            $modelCategory = XOOPS::getModel("config_category");
            $order = 0;
            foreach ($this->config['categories'] as $category) {
                if (!isset($category["module"])) {
                    $category["module"] = $module;
                }
                if (!isset($category["order"])) {
                    $category["order"] = ++$order;
                }
                $status = $modelCategory->insert($category) * $status;
            };
        }

        $configs = empty($this->config['items']) ? array() : $this->config['items'];
        $order = 0;
        foreach ($configs as $config) {
            if (!isset($config["order"])) {
                $config["order"] = ++$order;
            }
            $status = $this->addConfig($config, $message) * $status;
        }

        return $status;
    }

    public function update(&$message)
    {
        $module = $this->module->dirname;
        XOOPS::service('registry')->config->flush($module);
        $message = $this->message;

        if (version_compare($this->version, $this->module->version, ">=")) {
            return true;
        }

        $modelCategory = XOOPS::getModel("config_category");
        $modelConfig = XOOPS::getModel("config");
        $modelOption = XOOPS::getModel("config_option");
        $categoryList = empty($this->config['categories']) ? array() : $this->config['categories'];
        $categories = array();
        foreach ($categoryList as $category) {
            $categories[$category['key']] = $category;
        }

        $select = $modelCategory->select()->where("module = ?", $module);
        $categoryList = $modelCategory->fetchAll($select);
        foreach ($categoryList as $row) {
            $key = $row->key;
            // Delete unused category
            if (!isset($categories[$key])) {
                $status = $modelCategory->delete(array("id = ?" => $row->id)) * $status;
            } else {
                // Get existent category id
                $categories[$key]["id"] = $row->id;
                $data = array();
                if ($categories[$key]['name'] != $row->name) {
                    $data["name"] = $categories[$key]['name'];
                }
                if (isset($categories[$key]['order']) && $categories[$key]['order'] != $row->order) {
                    $data["order"] = $categories[$key]['order'];
                }
                // Update existent category
                if (!empty($data)) {
                    $modelCategory->update($data, array("id = ?" => $row->id));
                }
            }
        }
        foreach ($categories as $key => $category) {
            // Skip existent category
            if (isset($category['id'])) continue;
            // Insert new category
            if (!isset($category["module"])) {
                $category["module"] = $module;
            }
            $status = $modelCategory->insert($category) * $status;
        }

        $configs = isset($this->config['items']) ? $this->config['items'] : array();
        $configList = array();
        foreach ($configs as $config) {
            $configList[$config["name"]] = $config;
        }
        $select = $modelConfig->select()->where("module = ?", $module);
        $configSet = $modelConfig->fetchAll($select);
        $configs_update = array();
        foreach ($configSet as $row) {
            if (isset($configList[$row->name])) {
                if (!isset($categories[$configList[$row->name]["category"]])) {
                    $configList[$row->name]["category"] = "";
                }
                $this->updateConfig($row, $configList[$row->name], $message);
                unset($configList[$row->name]);
                continue;
            }
            if (!$modelConfig->delete(array("id = ?" => $row->id))) {
                $message[] = 'Config ' . $row->name . ' failed to delete';
            } else {
                $message[] = 'Config ' . $row->name . ' deleted';
                $modelOption->delete(array("config = ?" => $row->id));
            }
        }
        foreach ($configList as $name => $config) {
            if (!isset($categories[$config["category"]])) {
                $config["category"] = "";
            }
            $this->addConfig($config, $message);
        }
    }

    public function uninstall(&$message)
    {
        if (!is_object($this->module)) {
            return;
        }
        $module = $this->module->dirname;
        XOOPS::service('registry')->config->flush($module);
        $message = $this->message;

        $modelCategory = XOOPS::getModel("config_category");
        $modelConfig = XOOPS::getModel("config");
        $modelOption = XOOPS::getModel("config_option");

        $select = $modelConfig->select()->where("module = ?", $module)->from($modelConfig, array("id"));
        $configIds = $modelConfig->getAdapter()->fetchCol($select);

        $modelCategory->delete(array("module = ?" => $module));
        $modelConfig->delete(array("module = ?" => $module));

        if (!empty($configIds)) {
            $modelOption->delete(array("config IN (?)" => $configIds));
        }
        return;
    }

    private function addConfig($config, &$message)
    {
        $module = $this->module->dirname;
        $modelConfig = XOOPS::getModel("config");
        $modelOption = XOOPS::getModel("config_option");

        $options = array();
        if (isset($config["options"])) {
            $options = $config["options"];
            unset($config["options"]);
        }

        if (!isset($config["module"])) {
            $config["module"] = $module;
        }
        if (isset($config["default"])) {
            $config["value"] = $config["default"];
            unset($config["default"]);
        }

        /*
        if (!empty($config["edit"])) {
            $config["edit"] = serialize($config["edit"]);
        }
        */

        $configRow = $modelConfig->createRow($config);
        $configId = $configRow->save();
        if (empty($configId)) {
            $message[] = "Config " . $config["name"] . " insert failed";
            return false;
        }
        if (!empty($options)) {
            foreach ($options as $name => $value) {
                $data = array(
                    "name"      => $name,
                    "value"     => $value,
                    "config"    => $configId
                );
                $status = $modelOption->insert($data);
                if (empty($status)) {
                    $message[] = "Options for config " . $config["name"] . " insert failed";
                    return false;
                }
            }
        }
        $message[] = "Config " . $config["name"] . " inserted";

        return $configId;
    }

    private function updateConfig($row, $config, &$message)
    {
        $module = $this->module->dirname;
        $modelConfig = XOOPS::getModel("config");
        $modelOption = XOOPS::getModel("config_option");

        $options = array();
        if (isset($config["options"])) {
            $options = $config["options"];
            unset($config["options"]);
        }

        if (!isset($config["module"])) {
            $config["module"] = $module;
        }
        /*
        if (!empty($options)) {
            if (!in_array($row->value, array_values($options))) {
                $config["value"] = isset($config["default"]) ? $config["default"] : "";
            }
        }
        */
        if (isset($config["default"])) {
            unset($config["default"]);
        }

        /*
        if (!empty($config["edit"])) {
            $config["edit"] = serialize($config["edit"]);
        }
        */

        $row->setFromArray($config);
        //$status = $modelConfig->update($config, array("id = ?" => $row->id));
        $status = $row->save();
        if (empty($status)) {
            $message[] = "Config " . $config["name"] . " update failed";
            return false;
        }
        $modelOption->delete(array("config = ?" => $row->id));
        if (!empty($options)) {
            foreach ($options as $name => $value) {
                $data = array(
                    "name"      => $name,
                    "value"     => $value,
                    "config"    => $row->id
                );
                $status = $modelOption->insert($data);
                if (empty($status)) {
                    $message[] = "Options for config " . $config["name"] . " insert failed";
                    return false;
                }
            }
        }
        $message[] = "Config " . $config["name"] . " updated";

        return $configId;
    }
}
<?php
/**
 * Preference form
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
 * @package         System
 * @version         $Id$
 */

class App_System_Form_Preference extends Xoops_Zend_Form
//class System_Form_Preference extends Xoops_Zend_Form
{
    protected function loadDefaultOptions()
    {
        parent::loadDefaultOptions();
        $defaultOptions = array(
            "name"          => "xoopsPreference",
            "method"        => "post",
            "decorators"    => array(
                'FormElements',
                array('HtmlTag', array('tag' => 'dl', 'class' => 'xoops-form')),
                array('Description', array('placement' => 'prepend')),
                'Form'
            ),
        );
        $options = array_merge(parent::loadDefaultOptions(), $defaultOptions);
        return $options;
    }

    /**
     * Add multiple config elements at once
     *
     * @param  mixed $configs   Array of {@Xoops_Model_Config_Row}
     * @return system_form_preference
     */
    public function addConfigs($configs)
    {
        $configList = array();
        $configOptions = array();
        foreach ($configs as $config) {
            $configList[] = $config->id;
        }
        $modelOption = XOOPS::getModel("config_option");
        $select = $modelOption->select()->where("config IN (?)", $configList);
        $rowset = $modelOption->fetchAll($select);
        foreach ($rowset as $row) {
            $configOptions[$row->config][$row->value] = $row->name;
        }
        foreach ($configs as $config) {
            $keyConfig = $config->name;
            $idConfig = $config->id;
            $edit = $config->edit;
            if (!empty($edit)) {
                if (!is_array($edit)) {
                    $edit = array("type" => $edit);
                }
            } else {
                $edit = array();
            }
            $options = isset($edit["options"]) ? $edit["options"] : array();
            $type = empty($edit["type"]) ? "text" : strtolower($edit["type"]);

            if ($type == 'none') {
                continue;
            }
            switch ($type) {
                case "text":
                    if (!isset($options["size"])) {
                        $options["size"] = 50;
                    }
                    break;
                case "textarea":
                    if (!isset($options["rows"])) {
                        $options["rows"] = 3;
                    }
                    if (!isset($options["cols"])) {
                        $options["cols"] = 50;
                    }
                    break;
                default:
                    break;
            }

            if (!isset($options["multiOptions"]) && !empty($configOptions[$idConfig])) {
                $options["multiOptions"] = $configOptions[$idConfig];
            }

            $options["label"] = $config->title;
            $options["description"] = $config->description;


            if (!empty($edit["module"])) {
                /*
                $class = ('app' == Xoops::service('module')->getType($edit["module"]) ? 'App' : 'Module') . "_" . ucfirst($edit["module"]) . "_Form_Element_" . ucfirst($type);
                //$class = $edit["module"] . "_form_element_" . $type;
                if (class_exists($class)) {
                    $element = new $class($keyConfig, $options);
                } else {
                    $element = $this->createElement("text", $keyConfig);
                }
                */
                $element = $this->createElement(array($edit["module"], $type), $keyConfig, $options);
            } else {
                $element = $this->createElement($type, $keyConfig, $options);
            }

            if (null !== ($value = $config->value)) {
                if (is_array($value) && !$element instanceof Zend_Form_Element_Multi) {
                    $value = implode("|", $value);
                }
                $element->setValue($value);
            }

            $this->addElement($element);
            $this->addElement("array", "__ids[{$keyConfig}]", array("value" => $idConfig));
        }
        return $this;
    }

    /**
     * Add multiple categories (or {@Xoops_Zend_Form_DisplayGroup}) at once
     *
     * @param  array $categories   Associative array of categories: key - name: Name, configs: array of configs
     * @return system_form_preference
     */
    public function addCategories(array $categories = array())
    {
        $groups = array();
        foreach ($categories as $key => $category) {
            $groups[$key] = array(
                "options"   => array(
                    "legend" => $category["name"],
                ),
                "elements"  => $category["configs"],
            );
        }
        return $this->addDisplayGroups($groups);
    }
}
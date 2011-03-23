<?php
/**
 * Generic module admin preference controller
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

class Module_PreferenceController extends Xoops_Zend_Controller_Action_Admin
{
    public function indexAction()
    {
        $this->setTemplate("system/admin/preference.html");
        $modules = XOOPS::service("registry")->module->read();
        $module = $this->getRequest()->getModuleName();

        XOOPS::service("translate")->loadTranslation("comment", "");
        XOOPS::service("translate")->loadTranslation("notification", "");
        XOOPS::service("translate")->loadTranslation("modinfo", $module);
        $title = sprintf(XOOPS::_("Configurations of Module %s"), $modules[$module]["name"]);

        $modelCategory = XOOPS::getModel("config_category");
        $select = $modelCategory->select()->where("module = ?", $module)->from($modelCategory, array("key", "name"))->order("order ASC");
        $categoryList = $modelCategory->getAdapter()->fetchPairs($select);
        $categories = array();
        foreach ($categoryList as $key => $name) {
            $categories[$key] = array("name" => $name);
        }

        $modelConfig = XOOPS::getModel("config");
        $select = $modelConfig->select()->where("module = ?", $module)->order("order ASC");
        $configList = $modelConfig->fetchAll($select);
        foreach ($configList as $config) {
            if ($config->category) {
                $categories[$config->category]["configs"][] = $config->name;
            }
        }
        $configs = array(
            "configs"       => $configList,
            "categories"    => $categories,
        );
        $action = $this->view->url(array("action" => "save", "controller" => "preference", "module" => $module));
        $form = $this->getForm($configs, $action);
        $this->template->assign("title", $title);
    }

    public function __call($method, $args)
    {
        Debug::e($method . ' called');
    }

    public function saveAction()
    {
        $modelConfig = XOOPS::getModel("config");
        //$ids = unserialize($this->_getParam("__ids"));
        $ids = $this->_getParam("__ids");
        foreach ($ids as $name => $id) {
            $config = $modelConfig->findRow($id);
            $new_value = $this->_getParam($name);
            if (is_array($new_value) || $new_value != $config->value) {
                $config->value = $new_value;
                $config->save();
                //$modelConfig->update(array("value" => $new_value), array("id = ?" => $id));
            }
        }
        $redirect = array("action" => "index");
        $options = array("message" => XOOPS::_("Configuration data saved"), "time" => 3);
        XOOPS::service("registry")->config->flush($this->getRequest()->getModuleName());
        $this->redirect($redirect, $options);
    }

    private function getForm($configs, $action, $hidden = array())
    {
        $this->view->headLink(array(
            "href"  => "form.css",
            "rel"   => "stylesheet",
            "type"  => "text/css"
        ));
        $options = array(
            "action"    => $action,
        );
        $form = new App_System_Form_Preference($options);
        //$form = new System_Form_Preference($options);
        $form->addConfigs($configs["configs"]);
        $form->addCategories($configs["categories"]);
        $options = array(
            "label"     => "Confirm",
            "required"  => false,
            "ignore"    => true,
        );
        $form->addElement("submit", "save", $options);

        if (!empty($hidden)) {
            foreach ($hidden as $key => $value) {
                $form->addElement("hidden", $key, array("value" => $value));
            }
        }
        $form->assign($this->view);
        return $form;
    }
}
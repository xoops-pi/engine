<?php
/**
 * System admin preference controller
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

class System_PreferenceController extends Xoops_Zend_Controller_Action_Admin
{
    public function indexAction()
    {
        $this->forward("list");
    }

    // System config category list
    public function listAction()
    {
        XOOPS::service("translate")->loadTranslation("preferences", "system");
        $title = XOOPS::_("System Configurations");
        $categories = array();
        $model = XOOPS::getModel("config_category");
        $select = $model->select()->where("module = ?", "")->order(array("order ASC", "id ASC"));
        $categories = $model->getAdapter()->fetchAssoc($select);
        $this->template->assign("categories", $categories);
        $this->template->assign("title", $title);
    }

    // System config management
    public function systemAction()
    {
        $this->setTemplate("system/admin/preference.html");
        XOOPS::service("translate")->loadTranslation("preferences", "system");
        $category = $this->_getParam("category", "general");
        $modelCategory = XOOPS::getModel("config_category");
        if (is_numeric($category)) {
            $categoryRow = $modelCategory->findRow($category);
        } else {
            $categoryRow = $modelCategory->fetchRow(array("`key` = ?" => $category, "module = ?" => ""));
        }

        // Only root users can access root configuration
        if (XOOPS::registry("user")->id != 0 && $categoryRow->key == "root") {
            $urlOptions = array("action" => "index", "controller" => "preference", "route" => "admin");
            $message = XOOPS::_("You are not allowed to access this area.");
            $options = array("time" => 3, "message" => $message);
            $this->redirect($urlOptions, $options);
            return;
        }

        $title = sprintf(XOOPS::_("Configurations of %s"), XOOPS::_($categoryRow->name));

        $modelConfig = XOOPS::getModel("config");
        $select = $modelConfig->select()->where("module = ?", "")->where("category = ?", $categoryRow->key)->order("order ASC");
        $configs = $modelConfig->fetchAll($select);

        $action = $this->view->url(array("action" => "save", "controller" => "preference"));
        $form = $this->getForm($configs, $action, array("category" => $category));
        //$hidden = new XoopsFormHidden('category', $category);
        //$form->addElement('hidden', 'category', $category);
        //$form->assign($this->view);
        $this->template->assign("title", $title);
    }

    // Modules configurations
    public function moduleAction()
    {
        $modules = XOOPS::service("registry")->module->read();
        $title = XOOPS::_("Module Configurations");
        $moduleList = array();
        foreach (array_keys($modules) as $dirname) {
            if ($dirname == "system") continue;
            $info = Xoops::service('module')->loadInfo($dirname);
            // skip if the module does not have configurations
            if (empty($info['extensions']['config'])) {
                continue;
            }
            $moduleList[$dirname] = $modules[$dirname]["name"];
        }
        $this->template->assign("modules", $moduleList);
        $this->template->assign("title", $title);
    }

    // module config management
    public function manageAction()
    {
        $this->setTemplate("system/admin/preference.html");
        $modules = XOOPS::service("registry")->module->read();
        $module = $this->_getParam("dirname");
        if (empty($module) || !isset($modules[$module]) || $module == "system") {
            $this->forward("module");
            return;
        }

        XOOPS::service("translate")->loadTranslation("comment", "");
        XOOPS::service("translate")->loadTranslation("notification", "");
        if (Xoops::service('module')->getType($module) == 'legacy') {
            XOOPS::service("translate")->loadTranslation("global");
            XOOPS::service("translate")->loadTranslation("modinfo", $module);
        } else {
            XOOPS::service("translate")->loadTranslation("admin", $module);
        }
        //XOOPS::service("translate")->loadTranslation("info", $module);
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

        $action = $this->view->url(array("action" => "save", "controller" => "preference"));
        $hidden = array("dirname" => $module);
        if ($referer = $this->getRequest()->getHeader("referer")) {
            $hidden['redirect'] = $referer;
        }
        $form = $this->getForm($configs, $action, $hidden);
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
        if (!$redirect = $this->_getParam("redirect")) {
            if ($module = $this->_getParam("dirname")) {
                $redirect = array("action" => "manage");
            } else {
                $redirect = array("action" => "list");
            }
        }
        $options = array("message" => XOOPS::_("Configuration data saved"), "time" => 3);
        $module = $this->_getParam("dirname");
        XOOPS::service("registry")->config->flush(empty($module) ? "" : $module);

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
        if (is_array($configs) && isset($configs["categories"])) {
            $form->addConfigs($configs["configs"]);
            $form->addCategories($configs["categories"]);
        } else {
            $form->addConfigs($configs);
        }
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
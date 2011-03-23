<?php
/**
 * System admin navigation controller
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

class System_NavigationController extends Xoops_Zend_Controller_Action_Admin
{
    public function indexAction()
    {
        $module = $this->getRequest()->getModuleName();
        $modules = XOOPS::service("registry")->modulelist->read("active");

        $modelNavigation = XOOPS::getModel("navigation");
        $select = $modelNavigation->select()
                        ->from($modelNavigation, array("name", "title", "module"));
        $navigationList = $modelNavigation->getAdapter()->fetchAssoc($select);
        $navigations = array();
        $navigations["front"] = array(
            "title" => $navigationList["front"]["title"],
            "url"   => $this->getFrontController()->getRouter()->assemble(
                array(
                    "module"        => "system",
                    "controller"    => "menu",
                    "action"        => "front",
                ),
                "admin"
            ),
            "edit"  => $this->getFrontController()->getRouter()->assemble(
                array(
                    "module"        => "system",
                    "controller"    => "navigation",
                    "action"        => "edit",
                    "name"          => "front",
                ),
                "admin"
            ),
        );
        unset($navigationList["front"]);
        $navigations["admin"] = array(
            "title" => $navigationList["admin"]["title"],
            "url"   => $this->getFrontController()->getRouter()->assemble(
                array(
                    "module"        => "system",
                    "controller"    => "menu",
                    "action"        => "admin",
                ),
                "admin"
            ),
            "edit"  => $this->getFrontController()->getRouter()->assemble(
                array(
                    "module"        => "system",
                    "controller"    => "navigation",
                    "action"        => "edit",
                    "name"          => "admin",
                ),
                "admin"
            ),
        );
        unset($navigationList["admin"]);
        foreach ($navigationList as $name => $data) {
            if ($data["module"]) continue;
            $navigations[$name] = array(
                "title"     => $data["title"],
                "url"       => $this->getFrontController()->getRouter()->assemble(
                    array(
                        "module"        => "system",
                        "controller"    => "menu",
                        "action"        => "item",
                        "name"          => $name,
                    ),
                    "admin"
                ),
                "edit"  => $this->getFrontController()->getRouter()->assemble(
                    array(
                        "module"        => "system",
                        "controller"    => "navigation",
                        "action"        => "edit",
                        "name"          => $name,
                    ),
                    "admin"
                ),
                "delete"    => $this->getFrontController()->getRouter()->assemble(
                    array(
                        "module"        => "system",
                        "controller"    => "navigation",
                        "action"        => "delete",
                        "name"          => $name,
                    ),
                    "admin"
                ),
            );
        }
        $this->template->assign("navigations", $navigations);
    }

    // add a new navigation
    public function addAction()
    {
        $this->setTemplate("system/admin/navigation_edit.html");
        $module = $this->getRequest()->getModuleName();

        $navigation = array(
            "name"          => "",
            "title"         => "",
            "cache"         => "",
        );
        $title = XOOPS::_("Add a new navigation");
        $action = $this->view->url(array("action" => "save", "controller" => "navigation", "module" => $module));
        $name = "navigation_form_edit";
        $form = $this->getFormNavigationAdd($name, $navigation, $title, $action);
        $form->assign($this->template);
    }

    // edit a navigation
    public function editAction()
    {
        $this->setTemplate("system/admin/navigation_edit.html");
        $module = $this->getRequest()->getModuleName();
        $name = $this->_getParam("name");

        $model = XOOPS::getModel("navigation");
        $select = $model->select()
                        ->where("name = ?", $name);
        $row = $model->fetchRow($select);
        $navigation = $row->toArray();
        $title = XOOPS::_("Navigation Edit");
        $action = $this->view->url(array("action" => "save", "controller" => "navigation", "module" => $module));
        $name = "navigation_form_edit";
        $form = $this->getFormNavigationEdit($name, $navigation, $title, $action);
        $form->addElement(new XoopsFormHidden('id', $navigation["id"]));
        $form->assign($this->template);
    }

    // Save a navigation information into database
    public function saveAction()
    {
        $module = $this->getRequest()->getModuleName();

        $id = $this->getRequest()->getPost("id", 0);
        $title = $this->getRequest()->getPost("title");
        $name = $this->getRequest()->getPost("name");
        $cache = $this->getRequest()->getPost("cache", 0);
        $model = XOOPS::getModel('navigation');

        $success = true;
        if (empty($id)) {
            $select = $model->select()->where("name = ?", $name);
            if ($row = $model->fetchRow($select)) {
                $message = XOOPS::_("The name is already used.");
                $success = false;
            }
            if ($success) {
                $data = compact("title", "name", "cache");
                $model->insert($data);
                $message = XOOPS::_("The navigation is added successfully.");
            }
        } else {
            if (!$navigation = $model->findRow($id)) {
                $message = XOOPS::_("The navigation is not found.");
                $success = false;
            } else {
                $data = compact("title", "cache");
                $model->update($data, array("id = ?" => $id));
                $message = XOOPS::_("The navigation is updated successfully.");
            }
        }
        $options = array("message" => $message, "time" => 3);
        $redirect = array("action" => "index");
        $this->redirect($redirect, $options);
    }

    // delete a navigation from database
    public function deleteAction()
    {
        $name = $this->_getParam("name");
        $model = XOOPS::getModel('navigation');
        $select = $model->select()->where("name = ?", $name);
        if (!$row = $model->fetchRow($select)) {
            $message = XOOPS::_("The navigation is not found.");
            $success = false;
        } else {
            $success = $model->delete(array("name = ?" => $name));
            $message = XOOPS::_("The navigation is deleted successfully.");
        }

        $options = array("message" => $message, "time" => 3);
        $redirect = array("action" => "index");
        $this->redirect($redirect, $options);
    }

    // Navigation form
    private function getFormNavigationAdd($name, $navigation, $title, $action)
    {
        //include_once XOOPS::path('www') . '/class/xoopsformloader.php';
        Xoops_Legacy::autoload();

        $form = new XoopsThemeForm($title, $name, $action, 'post', true);
        $form->addElement(new XoopsFormText(XOOPS::_('Name'), 'name', 50, 64, $navigation["name"]));
        $form->addElement(new XoopsFormText(XOOPS::_('Title'), 'title', 50, 255, $navigation["title"]));
        $selectExpire = new XoopsFormSelect(XOOPS::_('Cache expire'), "cache", $navigation["cache"]);
        $selectExpire->addOptionArray(static::getExpireOptions());
        $form->addElement($selectExpire);

        $form->addElement(new XoopsFormButton('', 'button', _GO, 'submit'));
        return $form;
    }

    private function getFormNavigationEdit($name, $navigation, $title, $action)
    {
        //include_once XOOPS::path('www') . '/class/xoopsformloader.php';
        Xoops_Legacy::autoload();

        $form = new XoopsThemeForm($title, $name, $action, 'post', true);
        //$form->addElement(new XoopsFormText(XOOPS::_('Controller'), 'resource_controller', 50, 64, $resource["controller"]));
        $form->addElement(new XoopsFormText(XOOPS::_('Title'), 'title', 50, 255, $navigation["title"]));
        $selectExpire = new XoopsFormSelect(XOOPS::_('Cache expire'), "cache", $navigation["cache"]);
        $selectExpire->addOptionArray(static::getExpireOptions());
        $form->addElement($selectExpire);

        $form->addElement(new XoopsFormButton('', 'button', _GO, 'submit'));
        return $form;
    }

    protected static function getExpireOptions()
    {
        return array(
            '-1'        => XOOPS::_('Disable'),
            '0'         => _NOCACHE,
            '30'        => sprintf(_SECONDS, 30),
            '60'        => _MINUTE,
            '300'       => sprintf(_MINUTES, 5),
            '1800'      => sprintf(_MINUTES, 30),
            '3600'      => _HOUR,
            '18000'     => sprintf(_HOURS, 5),
            '86400'     => _DAY,
            '259200'    => sprintf(_DAYS, 3),
            '604800'    => _WEEK,
            '2592000'   => _MONTH
        );
    }

}
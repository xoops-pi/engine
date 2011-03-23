<?php
/**
 * Generic module module event controller
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

class Module_EventController extends Xoops_Zend_Controller_Action_Admin
{
    // Event list
    public function indexAction()
    {
        $module = $this->getRequest()->getModuleName();
        $modules = XOOPS::service("registry")->modulelist->read("active");
        $this->setTemplate("system/admin/event_list_module.html");

        $modelEvent = XOOPS::getModel("event");
        $select = $modelEvent->select()->where("module = ?", $module);
        $rowset = $modelEvent->fetchAll($select);
        $events = array();
        foreach ($rowset as $row) {
            $events[$row->name] = array(
                "id"        => $row->id,
                "name"      => $row->name,
                "title"     => $row->title,
                "active"    => $row->active
            );
        }
        $modelObserver = XOOPS::getModel("event_observer");
        $select = $modelObserver->select()->from($modelObserver, array("eventkey" => new Zend_Db_Expr("CONCAT(event_module, '-', event)"), "count" => "COUNT(*)"))
                                        ->where("event_module = ?", $module)
                                        ->where("module IN (?)", array_keys($modules))
                                        ->group("eventkey");
        $countList = $modelObserver->getAdapter()->fetchPairs($select);
        foreach ($events as $key => &$event) {
            $event["count"] = isset($countList[$module . "-" . $key]) ? $countList[$module . "-" . $key] : 0;
        }

        $select = $modelObserver->select()->from($modelObserver, array("count" => "COUNT(*)"))
                                        ->where("module = ?", $module);
        $row = $modelObserver->fetchRow($select);
        $count = $row->count;
        $observer_message = "";
        if ($count > 0) {
            $url = $this->getFrontController()->getRouter()->assemble(
                array(
                    "module"        => $module,
                    "controller"    => "event",
                    "action"        => "observer"
                ),
                "admin"
            );
            $observer_message = "<a href='{$url}' title='" . XOOPS::_("Observers") . "'>" . sprintf(XOOPS::_("There are %d observers, click to edit."), $count). "</a>";
        }

        $title = XOOPS::_("Module events");
        $action = $this->view->url(array("action" => "save", "controller" => "event", "module" => $module));
        $form = $this->getFormList("event_form_list", $events, $title, $action);
        $form->assign($this->template);

        $this->template->assign("title", $title);
        $this->template->assign("observer_message", $observer_message);
    }

    // Observer list
    public function observerAction()
    {
        $module = $this->getRequest()->getModuleName();
        $modules = XOOPS::service("registry")->modulelist->read("active");
        $this->setTemplate("system/admin/event_observers.html");

        $modelEvent = XOOPS::getModel("event");
        $modelObserver = XOOPS::getModel("event_observer");
        $select = $modelObserver->select()->where("module = ?", $module);
        $rowset = $modelObserver->fetchAll($select);
        $observers = array();
        foreach ($rowset as $row) {
            $observer = $row->toArray();
            $select = $modelEvent->select()->where("module = ?", $row->event_module)->where("name = ?", $row->event);
            $observer["event"] = $observer["event_module"] . "::" . $observer["event"];
            if ($rowEvent = $modelEvent->fetchRow($select)) {
                $observer["event_module"] = $modules[$rowEvent->module]["name"];
                $observer["event_title"] = $rowEvent->title;
            } else {
                $observer["suspend"] = 1;
            }
            $observers[$row->id] = $observer;
        }

        $title = XOOPS::_("Module observers");
        $action = $this->view->url(array("action" => "saveobserver", "controller" => "event", "module" => $module));
        $form = $this->getFormObservers("event_form_observers", $observers, $title, $action);
        $form->assign($this->template);

        $this->template->assign("title", $title);
    }

    // Save event active options into database
    public function saveAction()
    {
        $module = $this->getRequest()->getModuleName();
        $actives = $this->getRequest()->getPost("actives");

        //$modules = XOOPS::service("registry")->modulelist->read("active");
        $modelEvent = XOOPS::getModel("event");
        $select = $modelEvent->select()->where("module = ?", $module);
        $rowset = $modelEvent->fetchAll($select);
        $flushList = array();
        foreach ($rowset as $row) {
            $newValue = empty($actives[$row->id]) ? 0 : 1;
            if ($newValue == $row->active) continue;
            $modelEvent->update(array("active" => $newValue), array("id = ?" => $row->id));
            $flushList[] = array($row->module, $row->name);
        }
        foreach ($flushList as $item) {
            XOOPS::service('registry')->event->flush($item[0], $item[1]);
        }
        $options = array("message" => _SYSTEM_AM_DBUPDATED, "time" => 3);
        $redirect = array("action" => "index");
        $this->redirect($redirect, $options);
    }

    // Save event active options into database
    public function saveobserverAction()
    {
        $module = $this->getRequest()->getModuleName();
        //$event = $this->getRequest()->getPost("event");
        $actives = $this->getRequest()->getPost("actives");

        $modelObserver = XOOPS::getModel("event_observer");
        $select = $modelObserver->select()->where("module = ?", $module);
        $rowset = $modelObserver->fetchAll($select);

        $flushList = array();
        foreach ($rowset as $row) {
            if (isset($actives[$row->id]) && $actives[$row->id] == -1) continue;
            $newValue = empty($actives[$row->id]) ? 0 : 1;
            if ($newValue == $row->active) continue;
            $modelObserver->update(array("active" => $newValue), array("id = ?" => $row->id));
            $flushList[] = array($row->event_module, $row->event);
        }
        foreach ($flushList as $item) {
            XOOPS::service('registry')->event->flush($item[0], $item[1]);
        }

        $options = array("message" => _SYSTEM_AM_DBUPDATED, "time" => 3);
        $redirect = array("action" => "observer");
        $this->redirect($redirect, $options);
    }

    // Observer list form
    private function getFormObservers($name, $observers, $title, $action)
    {
        //include_once XOOPS::path('www') . '/class/xoopsformloader.php';
        Xoops_Legacy::autoload();

        $form = new XoopsFormGrid($title, $name, $action, 'post', true);
        $heads = array(
            XOOPS::_("No."),
            XOOPS::_("Class"),
            XOOPS::_("Method"),
            XOOPS::_("Evnt"),
            XOOPS::_("Evnt Module"),
            XOOPS::_("Evnt Title"),
            XOOPS::_("Active"),
        );
        $form->setHead($heads);

        $i = 0;
        foreach ($observers as $key => $observer) {
            $ele = new XoopsFormElementRow(++$i);

            // Observer Class
            $label = new XoopsFormLabel("", $observer["class"]);
            $ele->addElement($label);
            unset($label);

            // Observer Method
            $label = new XoopsFormLabel("", $observer["method"]);
            $ele->addElement($label);
            unset($label);

            // Event Key
            $label = new XoopsFormLabel("", $observer["event"]);
            $ele->addElement($label);
            unset($label);

            // Event Module
            $label = new XoopsFormLabel("", $observer["event_module"]);
            $ele->addElement($label);
            unset($label);

            // Event Title
            $label = new XoopsFormLabel("", $observer["event_title"]);
            $ele->addElement($label);
            unset($label);

            // Observer Active Option
            $checkbox = new XoopsFormCheckBox("", "actives[" . $key . "]", $observer["active"]);
            $checkbox->addOption(1, "");
            if (!empty($observer["suspend"])) {
                $checkbox->setDisabled();
                $form->addElement(new XoopsFormHidden("actives[" . $key . "]", -1));
            }
            $ele->addElement($checkbox);
            unset($checkbox);

            $form->addElement($ele);
            unset($ele);
        }
        $form->addElement(new XoopsFormButton('', 'button', _GO, 'submit'));
        return $form;
    }

    // Module's event list form
    private function getFormList($name, $events, $title, $action)
    {
        //include_once XOOPS::path('www') . '/class/xoopsformloader.php';
        Xoops_Legacy::autoload();

        $form = new XoopsFormGrid($title, $name, $action, 'post', true);
        $heads = array(
            XOOPS::_("No."),
            XOOPS::_("Title"),
            XOOPS::_("Name"),
            XOOPS::_("Observers"),
            XOOPS::_("Active"),
        );
        $form->setHead($heads);

        //$i = 0;
        foreach ($events as $name => $event) {
            // Event Title
            $ele = new XoopsFormElementRow(++$i);

            // Event Title
            $label = new XoopsFormLabel("", $event["title"]);
            $ele->addElement($label);
            unset($label);

            // Event Name
            $label = new XoopsFormLabel("", $event["name"]);
            $ele->addElement($label);
            unset($label);

            // Event Observer Count
            $label = $event["count"];
            $label = new XoopsFormLabel("", $label);
            $ele->addElement($label);
            unset($label);

            // Event Active Option
            $checkbox = new XoopsFormCheckBox("", "actives[" . $event["id"] . "]", $event["active"]);
            $checkbox->addOption(1, "");
            $ele->addElement($checkbox);
            unset($checkbox);

            $form->addElement($ele);
            unset($ele);
        }
        $form->addElement(new XoopsFormButton('', 'button', _GO, 'submit'));
        return $form;
    }
}
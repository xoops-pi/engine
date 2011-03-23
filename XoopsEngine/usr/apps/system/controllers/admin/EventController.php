<?php
/**
 * System admin event controller
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

class System_EventController extends Xoops_Zend_Controller_Action_Admin
{
    // Event list
    public function indexAction()
    {
        $module = $this->getRequest()->getModuleName();
        $modules = XOOPS::service("registry")->modulelist->read("active");
        $this->setTemplate("system/admin/event_list.html");

        $modelEvent = XOOPS::getModel("event");
        $select = $modelEvent->select()->where("module IN (?)", array_keys($modules));
        $rowset = $modelEvent->fetchAll($select);
        $events = array();
        foreach ($rowset as $row) {
            $modules[$row->module]["events"][$row->name] = array(
                "id"        => $row->id,
                "name"      => $row->name,
                "title"     => $row->title,
                "active"    => $row->active
            );
            //$events[] = $row->id;
        }
        $modelObserver = XOOPS::getModel("event_observer");
        $select = $modelObserver->select()->from($modelObserver, array("eventkey" => new Zend_Db_Expr("CONCAT(event_module, '-', event)"), "count" => "COUNT(*)"))
                                        ->where("event_module IN (?)", array_keys($modules))
                                        ->where("module IN (?)", array_keys($modules))
                                        ->group("eventkey");
        $countList = $modelObserver->getAdapter()->fetchPairs($select);
        foreach (array_keys($modules) as $dirname) {
            if (empty($modules[$dirname]["events"])) {
                unset($modules[$dirname]);
                continue;
            }
            foreach ($modules[$dirname]["events"] as $key => &$event) {
                $event["count"] = isset($countList[$dirname . "-" . $key]) ? $countList[$dirname . "-" . $key] : 0;
            }
        }

        $title = XOOPS::_("Module events");
        $action = $this->view->url(array("action" => "save", "controller" => "event", "module" => $module));
        $form = $this->getFormList("event_form_list", $modules, $title, $action);
        $form->assign($this->template);

        $this->template->assign("title", $title);
    }

    public function observerAction()
    {
        $module = $this->getRequest()->getModuleName();
        $this->setTemplate("system/admin/event_observers.html");
        $event = $this->_getParam("event");
        $modules = XOOPS::service("registry")->modulelist->read("active");

        $modelEvent = XOOPS::getModel("event");
        if (!$rowEvent = $modelEvent->findRow($event)) {
            $message = XOOPS::_("Specified event is not found");
            $options = array("message" => $message, "time" => 3);
            $redirect = array("action" => "index");
            $this->redirect($redirect, $options);
            return;
        }

        $modelObserver = XOOPS::getModel("event_observer");
        $select = $modelObserver->select()->where("event_module = ?", $rowEvent->module)
                                        ->where("event = ?", $rowEvent->name);
        $rowset = $modelObserver->fetchAll($select);
        $observers = array();
        foreach ($rowset as $row) {
            $observers[$row->id] = array(
                "id"        => $row->id,
                "module"    => $modules[$row->module]["name"],
                "class"     => $row->class,
                "method"    => $row->method,
                "active"    => $row->active
            );
        }
        $title = sprintf(XOOPS::_("Observers of evnt %s"), $rowEvent->title);
        $action = $this->view->url(array("action" => "saveobserver", "controller" => "event", "module" => $module));
        $form = $this->getFormObservers("event_form_observers", $observers, $title, $action);
        $form->addElement(new XoopsFormHidden('event', $event));
        $form->assign($this->template);

        $this->template->assign("title", $title);
    }

    // Save event active options into database
    public function saveAction()
    {
        $module = $this->getRequest()->getModuleName();
        $actives = $this->getRequest()->getPost("actives");

        $modules = XOOPS::service("registry")->modulelist->read("active");
        $modelEvent = XOOPS::getModel("event");
        $select = $modelEvent->select()->where("module IN (?)", array_keys($modules));
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
        $event = $this->getRequest()->getPost("event");
        $actives = $this->getRequest()->getPost("actives");

        $modelEvent = XOOPS::getModel("event");
        if (!$rowEvent = $modelEvent->findRow($event)) {
            $message = XOOPS::_("Specified event is not found");
            $options = array("message" => $message, "time" => 3);
            $redirect = array("action" => "index");
            $this->redirect($redirect, $options);
            return;
        }

        $modelObserver = XOOPS::getModel("event_observer");
        $select = $modelObserver->select()->where("event_module = ?", $rowEvent->module)
                                        ->where("event = ?", $rowEvent->name);
        $rowset = $modelObserver->fetchAll($select);

        foreach ($rowset as $row) {
            $newValue = empty($actives[$row->id]) ? 0 : 1;
            if ($newValue == $row->active) continue;
            $modelObserver->update(array("active" => $newValue), array("id = ?" => $row->id));
        }
        XOOPS::service('registry')->event->flush($rowEvent->module, $rowEvent->name);

        $options = array("message" => _SYSTEM_AM_DBUPDATED, "time" => 3);
        $redirect = array("action" => "observer", "event" => $event);
        $this->redirect($redirect, $options);
    }

    // Event observer list form
    private function getFormObservers($name, $observers, $title, $action)
    {
        //include_once XOOPS::path('www') . '/class/xoopsformloader.php';
        Xoops_Legacy::autoload();

        $form = new XoopsFormGrid($title, $name, $action, 'post', true);
        $heads = array(
            XOOPS::_("No."),
            XOOPS::_("Module"),
            XOOPS::_("Class"),
            XOOPS::_("Method"),
            XOOPS::_("Active"),
        );
        $form->setHead($heads);

        $i = 0;
        foreach ($observers as $key => $observer) {
            $ele = new XoopsFormElementRow(++$i);

            // Observer Module
            $label = new XoopsFormLabel("", $observer["module"]);
            $ele->addElement($label);
            unset($label);

            // Observer Class
            $label = new XoopsFormLabel("", $observer["class"]);
            $ele->addElement($label);
            unset($label);

            // Observer Method
            $label = new XoopsFormLabel("", $observer["method"]);
            $ele->addElement($label);
            unset($label);

            // Observer Active Option
            $checkbox = new XoopsFormCheckBox("", "actives[" . $key . "]", $observer["active"]);
            $checkbox->addOption(1, "");
            $ele->addElement($checkbox);
            unset($checkbox);

            $form->addElement($ele);
            unset($ele);
        }
        $form->addElement(new XoopsFormButton('', 'button', _GO, 'submit'));
        return $form;
    }

    // Module's event list form
    private function getFormList($name, $modules, $title, $action)
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
        foreach ($modules as $dirname => $data) {
            $form->insertBreak($data["name"]);
            foreach ($data["events"] as $key => $event) {
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
                if ($event["count"] > 0) {
                    $label = "<a href='" . $this->getFrontController()->getRouter()->assemble(
                                                array(
                                                    "module"        => "system",
                                                    "controller"    => "event",
                                                    "action"        => "observer",
                                                    "event"         => $event["id"],
                                                ),
                                                "admin")
                                            . "' title='" . XOOPS::_("Edit") . "'>" . $event["count"] . "</a>";
                } else {
                    $label = $event["count"];
                }
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
        }
        $form->addElement(new XoopsFormButton('', 'button', _GO, 'submit'));
        return $form;
    }
}
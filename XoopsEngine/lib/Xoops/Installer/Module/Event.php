<?php
/**
 * XOOPS module event/hook installer
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
 * Event meta:
 *  // Event list
 *  "events"    => array(
 *      // event name (unique)
 *      "user_call" => array(
 *          // title
 *          "title" => XOOPS::_("Event hook demo"),
 *      ),
 *  ),
 *  // Observer list
 *  "observers" => array(
 *      array(
 *          // event info: module, event name
 *          "event"     => array("pm", "test"),
 *          // callback info: class::method
 *          "callback"  => "event::message",
 *      ),
 *  ),
 */

class Xoops_Installer_Module_Event extends Xoops_Installer_Abstract
{
    public function install(&$message)
    {
        $module = $this->module->dirname;
        $message = $this->message;
        $status = true;

        if (empty($this->config)) {
            return;
        }

        $modelEvent = XOOPS::getModel("event");
        $events = !empty($this->config["events"]) ? $this->config["events"] : array();
        foreach ($events as $name => $event) {
            if (!isset($event["module"])) {
                $event["module"] = $module;
            }
            $event["name"] = $name;
            $status = $modelEvent->insert($event) * $status;
        }
        $modelObserver = XOOPS::getModel("event_observer");
        $observers = !empty($this->config["observers"]) ? $this->config["observers"] : array();
        $flushList = array();

        $classPrefix = (('app' == Xoops::service('module')->getType($module)) ? 'app' : 'module') . '\\' . ($this->module->parent ?: $module);
        foreach ($observers as $observer) {
            list($class, $method) = explode('::', $observer["callback"]);
            $data = array();
            $data["event_module"] = $observer["event"][0];
            $data["event"] = $observer["event"][1];
            $data["module"] = $module;
            $data["class"] = $classPrefix . '\\' . $class;
            $data["method"] = $method;
            $status = $modelObserver->insert($data) * $status;
            $flushList[] = $observer["event"];
        }
        foreach ($flushList as $item) {
            XOOPS::service('registry')->event->flush($item[0], $item[1]);
        }

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

        $modelEvent = XOOPS::getModel("event");
        $modelObserver = XOOPS::getModel("event_observer");

        $events = !empty($this->config["events"]) ? $this->config["events"] : array();
        $select = $modelEvent->select()->where("module = ?", $module);
        $eventList = $modelEvent->fetchAll($select);
        foreach ($eventList as $row) {
            if (!isset($events[$row->name])) {
                $status = $modelEvent->delete(array("id = ?" => $row->id)) * $status;
                $status = $modelObserver->delete(array("event = ?" => $row->name, "event_module = ?" => $module)) * $status;
                continue;
            }
            if ($row->title != $events[$row->name]["title"]) {
                $status = $modelEvent->update(array("title" => $events[$row->name]["title"]), array("id = ?" => $row->id)) * $status;
            }
            unset($events[$row->name]);
        }
        foreach ($events as $name => $event) {
            if (!isset($event["module"])) {
                $event["module"] = $module;
            }
            $event["name"] = $name;
            $status = $modelEvent->insert($event) * $status;
        }

        $observerConfig = !empty($this->config["observers"]) ? $this->config["observers"] : array();
        $observers = array();
        $classPrefix = (('app' == Xoops::service('module')->getType($module)) ? 'app' : 'module') . '\\' . ($this->module->parent ?: $module);
        foreach ($observerConfig as $observer) {
            list($class, $method) = explode('::', $observer["callback"]);
            $data = array();
            $data["event_module"] = $observer["event"][0];
            $data["event"] = $observer["event"][1];
            $data["module"] = $module;
            $data["class"] = $classPrefix . '\\' . $class;
            $data["method"] = $method;
            $key = md5($data["event_module"] . "-" . $data["event"] . "-" . $data["class"] . "-" . $data["method"]);
            $observers[$key] = $data;
        }
        $select = $modelObserver->select()->where("module = ?", $module);
        $observerList = $modelObserver->fetchAll($select);
        $flushList = array();
        foreach ($observerList as $row) {
            $key = md5($row->event_module . "-" . $row->event . "-" . $row->class . "-" . $row->method);
            if (!isset($observers[$key])) {
                $status = $modelObserver->delete(array("id = ?" => $row->id)) * $status;
                $flushList[] = array($row->event_module, $row->event);
            } else {
                unset($observers[$key]);
            }
        }
        foreach ($observers as $key => $observer) {
            $status = $modelObserver->insert($observer) * $status;
            $flushList[] = array($observer["event_module"], $observer["name"]);
        }
        foreach ($flushList as $item) {
            XOOPS::service('registry')->event->flush($item[0], $item[1]);
        }
        XOOPS::service('registry')->event->flush($module);

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

        $modelEvent = XOOPS::getModel("event");
        $modelObserver = XOOPS::getModel("event_observer");
        $modelEvent->delete(array("module = ?" => $module));
        $select = $modelObserver->select()->from($modelObserver, array("event_module", "event"))->where("module = ?", $module);
        $observerList = $modelObserver->fetchAll($select);
        $modelObserver->delete(array("module = ?" => $module));
        foreach ($observerList as $row) {
            XOOPS::service('registry')->event->flush($row->event_module, $row->event);
        }
        XOOPS::service('registry')->event->flush($module);

        return true;
    }

    public function activate(&$message)
    {
        $module = $this->module->dirname;
        $message = $this->message;

        $modelObserver = XOOPS::getModel("event_observer");
        $modelObserver->update(array("active" => 1), array('module = ?' => $module));
        $select = $modelObserver->select()->from($modelObserver, array("event_module", "event"))->where("module = ?", $module);
        $observerList = $modelObserver->fetchAll($select);
        foreach ($observerList as $row) {
            XOOPS::service('registry')->event->flush($row->event_module, $row->event);
        }
        XOOPS::service('registry')->event->flush($module);

        return true;
    }

    public function deactivate(&$message)
    {
        $module = $this->module->dirname;
        $message = $this->message;

        $modelObserver = XOOPS::getModel("event_observer");
        $modelObserver->update(array("active" => 0), array('module = ?' => $module));
        $select = $modelObserver->select()->from($modelObserver, array("event_module", "event"))->where("module = ?", $module);
        $observerList = $modelObserver->fetchAll($select);
        foreach ($observerList as $row) {
            XOOPS::service('registry')->event->flush($row->event_module, $row->event);
        }
        XOOPS::service('registry')->event->flush($module);

        return true;
    }
}
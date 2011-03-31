<?php
/**
 * Kernel service
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
 * @package         Kernel/Service
 * @version         $Id$
 */

namespace Kernel\Service;

class Event extends ServiceAbstract
{
    // Run-time attached observers
    protected $container;

    /**
     * Trigger (or notify) callbacks registered to an event
     *
     * @param string        $event  event name
     * @param object|array  $object object or array
     * @param string        $module The module that triggers the action
     * @return boolean
     */
    public function trigger($event, $object = null, $module = null)
    {
        if (empty($module)) {
            if (!\XOOPS::registry("module")) return;
            $module = \XOOPS::registry("module")->dirname;
        }
        $observers = $this->loadObservers($module, $event);
        foreach ($observers as $dirname => $callback) {
            list($eventClass, $eventMethod) = $callback;
            $eventClass::$eventMethod($object, $dirname);
        }
        if (!empty($this->container[$module][$event])) {
            foreach ($this->container[$module][$event] as $key => $callback) {
                if (isset($callback[2])) {
                    list($eventClass, $eventMethod, $dirname) = $callback;
                } else {
                    list($eventClass, $eventMethod) = $callback;
                    $dirname = null;
                }
                $eventClass::$eventMethod($object, $dirname);
            }
        }

        return true;
    }

    /**
     * Load observers of an event
     *
     * @param string    $module
     * @param string    $event
     * @return array
     */
    public function loadObservers($module, $event)
    {
        throw new \Exception('The abstract method can not be accessed directly');
    }

    /**
     * Attach a predefined observer to an event in run-time
     *
     * @param string    $module
     * @param string    $event
     * @param array     $callback: array of ["class", "method", "callerModule"(optional)]
     * @return boolean
     */
    public function attach($module, $event, $callback)
    {
        $key = serialize($callback);
        $this->container[$module][$event][$key] = $callback;
        return $this;
    }

    /**
     * Detach an observer from an event
     *
     * @param string    $module
     * @param string    $event
     * @param array     $callback: array of ["class", "method", "callerModule"(optional)]
     * @return boolean
     */
    public function detach($module, $event, $callabck = null)
    {
        if ($callabck !== null) {
            $key = serialize($callback);
            $this->container[$module][$event][$key] = null;
        } else {
            $this->container[$module][$event] = null;
        }
        return $this;
    }
}
<?php
/**
 * XOOPS API
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
 * @package         Xoops_Core
 * @version         $Id$
 */

class Xoops_Api
{
    /**
     * Perform an API call
     *
     * @param string    $module
     * @param string    $resource
     * @param array     $args
     * @return mixed
     */
    public static function call($module, $resource, $args = array())
    {
        // Distributed API class: /moduleName/class/api/resource.php
        if (false !== strpos($resource, ":")) {
            list($class, $method) = explode(":", $resource, 2);
            $class = "api_" . $class;
        // Scalar API class: /moduleName/class/api.php
        } else {
            list($class, $method) = array("api", $resource);
        }
        //$class = "app_" . $module . "_" . $class;
        $class = $module . "_" . $class;
        if (!class_exists($class)) {
            trigger_error("API class {$class} not loaded");
            return false;
        }
        $apiHandler = new $class;
        return $apiHandler->$method($args);
    }

    public function put($args = array()) {}
    public function post($args = array()) {}
    public function get($args = array()) {}
    public function delete($args = array()) {}

    public function __call($method, $args)
    {
        return;
    }
}
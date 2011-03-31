<?php
/**
 * Zend Framework for Xoops Engine
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
 * @category        Xoops_Zend
 * @package         Controller
 * @version         $Id$
 */

class Xoops_Zend_Controller_Dispatcher_Admin extends Xoops_Zend_Controller_Dispatcher_Application
{
    public $systemModule = "module";

    /**
     * Determine if a given module is valid
     *
     * @param  string $module
     * @return bool
     */
    public function ____isValidModule($module)
    {
        if (!is_string($module)) {
            return false;
        }

        $module        = strtolower($module);
        if ($module == $this->_defaultModule) {
            return true;
        }
        $modules = XOOPS::service("registry")->module->read();
        if (isset($modules[$module])) {
            return true;
        }

        return false;

        $controllerDir = $this->getControllerDirectory();
        foreach (array_keys($controllerDir) as $moduleName) {
            if ($module == strtolower($moduleName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get controller class name
     *
     * Try request first; if not found, try pulling from request parameter;
     * if still not found, fallback to default
     *
     * @param Zend_Controller_Request_Abstract $request
     * @return string|false Returns class name on success
     */
    public function getControllerClass(Zend_Controller_Request_Abstract $request)
    {
        $controllerName = $request->getControllerName();
        if (empty($controllerName)) {
            if (!$this->getParam('useDefaultControllerAlways')) {
                return false;
            }
            $controllerName = $this->getDefaultControllerName();
            $request->setControllerName($controllerName);
        }

        $className = $this->formatControllerName($controllerName);

        $controllerDirs      = $this->getControllerDirectory();
        $module = $request->getModuleName();
        $found = false;
        // Check if module specified admin controller class exists
        if (!empty($controllerDirs[$module]) && $this->isValidModule($module)) {
            $this->_curModule    = $module;
            $this->_curDirectory = $controllerDirs[$module];
            try {
                $this->loadClass($className);
                $found = true;
            } catch (Zend_Controller_Dispatcher_Exception $e) {
                $found = false;
            }
        }
        // Check if generic admin controller class exists in system/controllers/module/
        if (!$found && !empty($controllerDirs[$this->systemModule])) {
            $this->_curModule    = $this->systemModule;
            $this->_curDirectory = $controllerDirs[$this->systemModule];
            try {
                $this->loadClass($className);
                $found = true;
            } catch (Zend_Controller_Dispatcher_Exception $e) {
                $found = false;
            }
        }
        if (!$found) {
            return parent::getControllerClass($request);
        }

        return $className;
    }
}

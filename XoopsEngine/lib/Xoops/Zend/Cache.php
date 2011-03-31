<?php
/**
 * Cache handler for Xoops Engine
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
 * @package         Xoops_Zend
 * @version         $Id$
 */

class Xoops_Zend_Cache extends Zend_Cache
{
    private static $objects;

    /**
     * Factory
     *
     * @param mixed  $frontend        frontend name (string) or Zend_Cache_Frontend_ object
     * @param mixed  $backend         backend name (string) or Zend_Cache_Backend_ object
     * @param array  $frontendOptions associative array of options for the corresponding frontend constructor
     * @param array  $backendOptions  associative array of options for the corresponding backend constructor
     * @param boolean $customFrontendNaming if true, the frontend argument is used as a complete class name ; if false, the frontend argument is used as the end of "Zend_Cache_Frontend_[...]" class name
     * @param boolean $customBackendNaming if true, the backend argument is used as a complete class name ; if false, the backend argument is used as the end of "Zend_Cache_Backend_[...]" class name
     * @param boolean $autoload if true, there will no require_once for backend and frontend (usefull only for custom backends/frontends)
     * @throws Zend_Cache_Exception
     * @return Zend_Cache_Core|Zend_Cache_Frontend
     */
    public static function factory($frontend = 'core', $backend = 'file', $frontendOptions = array(), $backendOptions = array(), $customFrontendNaming = false, $customBackendNaming = false, $autoload = false)
    {
        $frontend = empty($frontend) ? 'core' : $frontend;
        $backend = empty($backend) ? 'file' : $backend;
        $frontendOptions = !is_array($frontendOptions) ? array() : $frontendOptions;
        $backendOptions = !is_array($backendOptions) ? array() : $backendOptions;

        $key = md5(serialize(compact('frontend', 'backend', 'frontendOptions', 'backendOptions', 'customFrontendNaming', 'customBackendNaming')));
        if (!isset(self::$objects[$key])) {
            if (is_string($backend)) {
                $backendObject = self::_makeBackend($backend, $backendOptions, $customBackendNaming, $autoload);
            } else {
                if ((is_object($backend)) && (in_array('Zend_Cache_Backend_Interface', class_implements($backend)))) {
                    $backendObject = $backend;
                } else {
                    self::throwException('backend must be a backend name (string) or an object which implements Zend_Cache_Backend_Interface');
                }
            }
            if (is_string($frontend)) {
                $frontendObject = self::_makeFrontend($frontend, $frontendOptions, $customFrontendNaming, $autoload);
            } else {
                if (is_object($frontend)) {
                    $frontendObject = $frontend;
                } else {
                    self::throwException('frontend must be a frontend name (string) or an object');
                }
            }
            $frontendObject->setBackend($backendObject);
            self::$objects[$key] = $frontendObject;
        }
        return self::$objects[$key];
    }

    /**
     * Frontend Constructor
     *
     * @param string  $backend
     * @param array   $backendOptions
     * @param boolean $customBackendNaming
     * @param boolean $autoload
     * @return Zend_Cache_Backend
     */
    public static function _makeBackend($backend, $backendOptions, $customBackendNaming = false, $autoload = false)
    {
        if (!$customBackendNaming) {
            $backend  = self::_normalizeName($backend);
        }
        if (in_array($backend, Zend_Cache::$standardBackends)) {
            // we use a standard backend
            $backendClass = 'Zend_Cache_Backend_' . $backend;
            if (class_exists('Xoops_' . $backendClass)) {
                $backendClass = 'Xoops_' . $backendClass;
            } else {
                // security controls are explicit
                //require_once str_replace('_', DIRECTORY_SEPARATOR, $backendClass) . '.php';
            }
        } else {
            // we use a custom backend
            if (!preg_match('~^[\w]+$~D', $backend)) {
                Zend_Cache::throwException("Invalid backend name [$backend]");
            }
            if (!$customBackendNaming) {
                // we use this boolean to avoid an API break
                $backendClass = 'Zend_Cache_Backend_' . $backend;
            } else {
                $backendClass = $backend;
            }
            if (!$autoload) {
                if (class_exists('Xoops_' . $backendClass)) {
                    $backendClass = 'Xoops_' . $backendClass;
                } else {
                    $file = str_replace('_', DIRECTORY_SEPARATOR, $backendClass) . '.php';
                    if (!(self::_isReadable($file))) {
                        self::throwException("file $file not found in include_path");
                    }
                    require_once $file;
                }
            }
        }
        return new $backendClass($backendOptions);
    }

    /**
     * Backend Constructor
     *
     * @param string  $frontend
     * @param array   $frontendOptions
     * @param boolean $customFrontendNaming
     * @param boolean $autoload
     * @return Zend_Cache_Core|Zend_Cache_Frontend
     */
    public static function _makeFrontend($frontend, $frontendOptions = array(), $customFrontendNaming = false, $autoload = false)
    {
        if (!$customFrontendNaming) {
            $frontend = self::_normalizeName($frontend);
        }
        if (in_array($frontend, self::$standardFrontends)) {
            // we use a standard frontend
            // For perfs reasons, with frontend == 'Core', we can interact with the Core itself
            $frontendClass = 'Zend_Cache_' . ($frontend != 'Core' ? 'Frontend_' : '') . $frontend;
            if (class_exists('Xoops_' . $frontendClass)) {
                $frontendClass = 'Xoops_' . $frontendClass;
            } else {
                // security controls are explicit
                //require_once str_replace('_', DIRECTORY_SEPARATOR, $frontendClass) . '.php';
            }
        } else {
            // we use a custom frontend
            if (!preg_match('~^[\w]+$~D', $frontend)) {
                Zend_Cache::throwException("Invalid frontend name [$frontend]");
            }
            if (!$customFrontendNaming) {
                // we use this boolean to avoid an API break
                $frontendClass = 'Zend_Cache_Frontend_' . $frontend;
            } else {
                $frontendClass = $frontend;
            }
            if (!$autoload) {
                if (class_exists('Xoops_' . $frontendClass)) {
                    $frontendClass = 'Xoops_' . $frontendClass;
                } else {
                    $file = str_replace('_', DIRECTORY_SEPARATOR, $frontendClass) . '.php';
                    if (!(self::_isReadable($file))) {
                        self::throwException("file $file not found in include_path");
                    }
                    require_once $file;
                }
            }
        }
        return new $frontendClass($frontendOptions);
    }

    public static function generateId($id, $level = "")
    {
        $prefix = "";
        switch ($level) {
        case "user":
            if (!empty($GLOBALS['xoopsUser'])) {
                $prefix = "user_" . $GLOBALS['xoopsUser']->getVar('uid');
            }
            break;
        case "role":
            $prefix = "role_" . Xoops_User::getRole();
            break;
        case "locale":
            $prefix = "locale_" . XOOPS::registry('locale')->toString();
            break;
        case "group":
            if (!empty($GLOBALS['xoopsUser'])) {
                $groups = $GLOBALS['xoopsUser']->getGroups();
                $prefix = "group_" . md5(serialize(sort($groups)));
            }
            break;
        case "public":
        default:
            break;
        }

        if (!empty($prefix)) {
            $id .= '_' . $prefix;
        }
        return $id;
    }

    public static function generateTag($level, $value = null)
    {
        if (!is_null($value)) {
            return $level . '_' . strval($value);
        }
        $tag = "";
        switch ($level) {
        case "user":
            if (!empty($GLOBALS['xoopsUser'])) {
                $tag = "user_" . $GLOBALS['xoopsUser']->getVar('uid');
            }
            break;
        case "role":
            $tag = "role_" . Xoops_User::getRole();
            break;
        case "locale":
            $prefix = "locale_" . XOOPS::registry('locale')->toString();
            break;
        case "group":
            if (!empty($GLOBALS['xoopsUser'])) {
                $groups = $GLOBALS['xoopsUser']->getGroups();
                $tag = "group_" . md5(serialize(sort($groups)));
            }
            break;
        case "public":
        default:
            break;
        }
        return $tag;
    }

}

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
 * @package         Loader
 * @version         $Id$
 */

/** Zend_Loader */
require_once XOOPS::path('lib') . '/Zend/Loader.php';

class Xoops_Zend_Loader extends Zend_Loader
{
    //protected static $persist;

    /**
     * Prefix for persistent class path
     */
    //protected static $persistPrefix = XOOPS::config("identifier") . ".class.";

    /**
     * Throw exception on failure
     */
    protected static $exceptionOnFailure = false;

    /**
     * suppress file existence error
     */
    protected static $suppressError = true;

    public static function setExceptionOnFailure($flag = true)
    {
        static::$exceptionOnFailure = $flag;
    }

    public static function setSuppressError($flag = true)
    {
        static::$suppressError = $flag;
    }

    /**
     * Loads a class from a PHP file.  The filename must be formatted
     * as "$class.php".
     *
     * If $dirs is a string or an array, it will search the directories
     * in the order supplied, and attempt to load the first matching file.
     *
     * If $dirs is null, it will split the class name at underscores to
     * generate a path hierarchy (e.g., "Zend_Example_Class" will map
     * to "Zend/Example/Class.php").
     *
     * If the file was not found in the $dirs, or if no $dirs were specified,
     * it will attempt to load it from PHP's include_path.
     *
     * @param string $class      - The full class name of a Zend component.
     * @param string|array $dirs - OPTIONAL Either a path or an array of paths
     *                             to search.
     * @return void
     * @throws Zend_Exception
     */
    public static function loadClass($class, $dirs = null)
    {
        if (class_exists($class, false) || interface_exists($class, false)) {
            return true;
        }

        if ((null !== $dirs) && !is_string($dirs) && !is_array($dirs)) {
            //require_once 'Zend/Exception.php';
            throw new Zend_Exception('Directory argument must be a string or an array');
        }

        // Autodiscover the path from the class name
        // Implementation is PHP namespace-aware, and based on
        // Framework Interop Group reference implementation:
        // http://groups.google.com/group/php-standards/web/psr-0-final-proposal
        $className = ltrim($class, '\\');
        $file      = '';
        $namespace = '';
        if ($lastNsPos = strripos($className, '\\')) {
            $namespace = substr($className, 0, $lastNsPos);
            $className = substr($className, $lastNsPos + 1);
            $file      = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
        }
        $file .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

        if (!empty($dirs)) {
            // use the autodiscovered path
            $dirPath = dirname($file);
            if (is_string($dirs)) {
                $dirs = explode(PATH_SEPARATOR, $dirs);
            }
            foreach ($dirs as $key => $dir) {
                if ($dir == '.') {
                    $dirs[$key] = $dirPath;
                } else {
                    $dir = rtrim($dir, '\\/');
                    $dirs[$key] = $dir . DIRECTORY_SEPARATOR . $dirPath;
                }
            }
            $file = basename($file);
            static::loadFile($file, $dirs, true);
        } else {
            static::loadFile($file, null, true);
        }

        if (!class_exists($class, false) && !interface_exists($class, false)) {
            if (static::$exceptionOnFailure) {
                //require_once 'Zend/Exception.php';
                throw new Zend_Exception("File \"$file\" does not exist or class \"$class\" was not found in the file");
            } else {
                return false;
            }
        }

        //static::registerPersist($class, $file);
        XOOPS::persist()->registerClass($class, $file);
        return true;
    }

    /**
     * Loads a PHP file.  This is a wrapper for PHP's include() function.
     *
     * $filename must be the complete filename, including any
     * extension such as ".php".  Note that a security check is performed that
     * does not permit extended characters in the filename.  This method is
     * intended for loading Zend Framework files.
     *
     * If $dirs is a string or an array, it will search the directories
     * in the order supplied, and attempt to load the first matching file.
     *
     * If the file was not found in the $dirs, or if no $dirs were specified,
     * it will attempt to load it from PHP's include_path.
     *
     * If $once is TRUE, it will use include_once() instead of include().
     *
     * @param  string        $filename
     * @param  string|array  $dirs - OPTIONAL either a path or array of paths
     *                       to search.
     * @param  boolean       $once
     * @return boolean
     * @throws Zend_Exception
     */
    public static function loadFile($filename, $dirs = null, $once = false)
    {
        static::_securityCheck($filename);

        /**
         * Search in provided directories, as well as include_path
         */
        $incPath = false;
        if (!empty($dirs) && (is_array($dirs) || is_string($dirs))) {
            if (is_array($dirs)) {
                $dirs = implode(PATH_SEPARATOR, $dirs);
            }
            $incPath = get_include_path();
            set_include_path($dirs . PATH_SEPARATOR . $incPath);
        }

        if (static::$suppressError) {
            $errorReporting = null;
            if (XOOPS::registry("error")) {
                $errorReporting = XOOPS::registry("error")->error_reporting();
                $ep = $errorReporting & ~ E_WARNING;
                $errorReporting = XOOPS::registry("error")->error_reporting($ep);
            }
        }

        /**
         * Try finding for the plain filename in the include_path.
         */
        if ($once) {
            include_once $filename;
        } else {
            include $filename;
        }

        if (static::$suppressError && isset($errorReporting)) {
            XOOPS::registry("error")->error_reporting($errorReporting);
        }

        /**
         * If searching in directories, reset include_path
         */
        if ($incPath) {
            set_include_path($incPath);
        }

        return true;
    }

    /**
     * Loads a core class from a PHP file in lib/Xoops/Core/.
     *
     * @param string $class      - The full class name, should be prepended with "Xoops_"
     * @return void
     * @throws Zend_Exception
     */
    public static function Core($class)
    {
        if (class_exists($class, false) || interface_exists($class, false)) {
            return;
        }

        // Autodiscover the path from the class name
        $className = ltrim($class, '\\');
        list($prefix, $file) = explode("_", $className, 2);
        $file = XOOPS::path("lib") . DIRECTORY_SEPARATOR . "Xoops" . DIRECTORY_SEPARATOR . "Core" . DIRECTORY_SEPARATOR . ucfirst($file) . ".php";
        self::loadFile($file, null, true);

        if (!class_exists($class, false) && !interface_exists($class, false)) {
            if (static::$exceptionOnFailure) {
                //require_once 'Zend/Exception.php';
                throw new Zend_Exception("File \"$file\" does not exist or class \"$class\" was not found in the file");
            } else {
                return false;
            }
        }

        //static::registerPersist($class, $file);
        XOOPS::persist()->registerClass($class, $file);
        return true;
    }

    /**
     * Loads a module class from a PHP file in app/ or in module/.
     *
     * @param string $class      - The full class name, should be prepended with "[moduleDirname]_"
     * @return void
     * @throws Zend_Exception
     */
    public static function Module($class)
    {
        if (class_exists($class, false) || interface_exists($class, false)) {
            return;
        }

        $class = strtolower($class);
        // Autodiscover the path from the class name
        $className = ltrim($class, '\\');
        list($module, $file) = explode("_", $className, 2);
        $file = str_replace('_', DIRECTORY_SEPARATOR, $file) . '.php';
        $file = Xoops::service('module')->getPath($module) . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR . $file;
        self::loadFile($file, null, true);

        if (!class_exists($class, false) && !interface_exists($class, false)) {
            if (static::$exceptionOnFailure) {
                //require_once 'Zend/Exception.php';
                throw new Zend_Exception("File \"$file\" does not exist or class \"$class\" was not found in the file");
            } else {
                return false;
            }
        }

        //static::registerPersist($class, $file);
        XOOPS::persist()->registerClass($class, $file);
        return true;
    }

    /**
     * Loads a plugin class from a PHP file in lib/plugins/.
     *
     * @param string $class      - The full class name, should be prepended with "plugin_"
     * @return void
     * @throws Zend_Exception
     */
    public static function Plugin($class)
    {
        if (class_exists($class, false) || interface_exists($class, false)) {
            return;
        }

        $class = strtolower($class);
        // Autodiscover the path from the class name
        $className = ltrim($class, '\\');
        list($prefix, $file) = explode("_", $className, 2);
        $file = str_replace('_', DIRECTORY_SEPARATOR, $file) . ".php";
        $file = XOOPS::path("lib") . DIRECTORY_SEPARATOR . "plugins" . DIRECTORY_SEPARATOR . $file;
        self::loadFile($file, null, true);

        if (!class_exists($class, false) && !interface_exists($class, false)) {
            if (static::$exceptionOnFailure) {
                //require_once 'Zend/Exception.php';
                throw new Zend_Exception("File \"$file\" does not exist or class \"$class\" was not found in the file");
            } else {
                return false;
            }
        }

        //static::registerPersist($class, $file);
        XOOPS::persist()->registerClass($class, $file);
        return true;
    }

    /**
     * Registers a class path information to perisistent caches
     *
     * @param string $class The full class name
     * @return null|false|true
     */
    public static function loadFromPersist($class)
    {
        $path = XOOPS::persist()->loadClass($class);
        if (!empty($path)) {
            include $path;
            return true;
        }
        return null;
    }
}
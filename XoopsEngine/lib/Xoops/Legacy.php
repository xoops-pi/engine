<?php
/**
 * XOOPS legacy class loader
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
 * @package         Xoops_Core
 * @version         $Id$
 */

/**
 * Placeholder for xoopsform, you are not supposed to call this class
 */
class Xoops_Legacy
{
    static protected $classMap;

    public function __construct()
    {
        static::autoload();
    }

    public static function autoload()
    {
        if (!defined('XOOPS_ROOT_PATH')) {
            define('XOOPS_ROOT_PATH', Xoops::path('www'));
        }
        Xoops::service('translate')->loadTranslation('global');
        Xoops::autoloader()->registerCallback(array(__CLASS__, 'loadClassPath'));
        return;
    }

    /**
     * Loads a legacy class
     *
     * @param string    $class
     * @param string    $scope
     * @return bool
     */
    public static function load($class, $scope = null)
    {
        trigger_error("Class '{$class}' is legacy", E_USER_DEPRECATED);

        if (null === $scope) {
            if (class_exists($class, false)) {
                return true;
            }
            if ($path = static::loadClassPath($class)) {
                return include $path;
            }
        }
        if (!class_exists('XoopsLoad', false)) {
            require Xoops::path('www') . '/class/xoopsload.php';
        }
        return XoopsLoad::load($class, $scope);
    }

    public static function loadClassPath($class)
    {
        if (empty(static::$classMap)) {
            static::loadClassMap();
        }
        $key = strtolower($class);
        if (isset(static::$classMap[$key])) {
            trigger_error("Class '{$class}' is legacy", E_USER_DEPRECATED);
            return static::$classMap[$key];
        }
        return false;
    }

    public static function loadClassMap($map = array())
    {
        if (empty(static::$classMap)) {
            static::loadDefaultMap();
        }
        static::$classMap += array_change_key_case($map);
        return true;
    }

    protected static function loadDefaultMap()
    {
        $persistKey = "autoloader.classmap.legacy." . Xoops::config('identifier');
        if (!$map = Xoops::persist()->load($persistKey)) {
            $classPath = Xoops::path('www') . '/class';
            $map = array(
                'xoopslogger'               => $classPath . '/logger/xoopslogger.php',
                'xoopspagenav'              => $classPath . '/pagenav.php',
                'xoopslists'                => $classPath . '/xoopslists.php',
                'uploader'                  => $classPath . '/uploader.php',
                'utility'                   => $classPath . '/utility/xoopsutility.php',
                'captcha'                   => $classPath . '/captcha/xoopscaptcha.php',
                'cache'                     => $classPath . '/cache/xoopscache.php',
                'file'                      => $classPath . '/file/xoopsfile.php',
                'model'                     => $classPath . '/model/xoopsmodel.php',

                'xoopslocal'                => Xoops::path('www') . '/include/xoopslocal.php',
                'xoopslocalabstract'        => $classPath . '/xoopslocal.php',
                'xoopseditor'               => $classPath . '/xoopseditor/xoopseditor.php',
                'xoopseditorhandler'        => $classPath . '/xoopseditor/xoopseditor.php',
                'xoopssecurity'             => $classPath . '/xoopssecurity.php',

                'mytextsanitizer'           => $classPath . '/module.textsanitizer.php',
            );

            $iterator = new DirectoryIterator($classPath . '/xoopsform');
            foreach ($iterator as $fileinfo) {
                if (!$fileinfo->isFile() || $fileinfo->isDot()) {
                    continue;
                }
                $baseName = strtolower($fileinfo->getFileInfo()->getBasename(".php"));
                $map["xoops" . $baseName] = $fileinfo->getRealPath();
            }
            Xoops::persist()->save($map, $persistKey);
        }
        static::$classMap = $map;
    }

    public function __call($method, $args)
    {
        switch (strtolower($method)) {
            case 'path':
                return call_user_func_array(array('Xoops', 'path'), $args);
                break;
            case 'url':
                return call_user_func_array(array('Xoops', 'url'), $args);
                break;
            case 'buildurl':
                return call_user_func_array(array('Xoops', 'buildUrl'), $args);
                break;
            default:
                break;
        }
        return;
    }

    public static function loadModule($dirname = null)
    {
        global $xoopsModule, $xoopsModuleConfig, $module_handler;

        $dirname = $dirname ?: Xoops::registry('module')->dirname;
        $module_handler = xoops_gethandler('module');
        $xoopsModule = $module_handler->getByDirname($dirname);
        if (!$xoopsModule || !$xoopsModule->getVar('isactive')) {
            throw new Exception("Module unavailable!", 404);
        }
        $xoopsModuleConfig = Xoops::service('module')->loadConfig($dirname);
    }

    public static function loadUser()
    {
        global $xoopsUser;
        $uid = Xoops::registry('user')->id;
        if ($uid) {
            $xoopsUser = XOOPS::getHandler('user')->get($uid);
        }
    }
}
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
 * @copyright       Xoops Engine http://www.xoopsengine.org
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @since           3.0
 * @category        Xoops_Zend
 * @package         Loader
 * @version         $Id$
 */

class Xoops_Zend_Loader_PluginLoader extends Zend_Loader_PluginLoader
{
    protected $registry;

    /**
     * Load a plugin via the name provided
     *
     * @param  string $name
     * @param  bool $throwExceptions Whether or not to throw exceptions if the
     * class is not resolved
     * @return string|false Class name of loaded class; false if $throwExceptions
     * if false and no class found
     * @throws Zend_Loader_Exception if class not found
     */
    public function load($name, $throwExceptions = true)
    {
        $name = $this->_formatName($name);
        if ($this->isLoaded($name)) {
            return $this->getClassName($name);
        }

        if (!isset($this->registry)) {
            if ($this->_useStaticRegistry) {
                $registry = self::$_staticPrefixToPaths[$this->_useStaticRegistry];
            } else {
                $registry = $this->_prefixToPaths;
            }

            $this->registry  = array_reverse($registry, true);
        }


        $found     = false;
        foreach (array_keys($this->registry) as $prefix) {
            $className = $prefix . $name;
            //if (class_exists($className, false)) {
            if (class_exists($className)) {
                return $className;
            }
            $path = XOOPS::persist()->loadClass($className);
            if (!empty($path)) {
                include $path;
                $found = true;
                break;
            }
        }

        if (!$found) {

            $classFile = str_replace('_', DIRECTORY_SEPARATOR, $name) . '.php';
            //$incFile   = self::getIncludeFileCache();
            foreach ($this->registry as $prefix => $paths) {
                $className = $prefix . $name;

                if (class_exists($className, false)) {
                    $found = true;
                    break;
                }

                $paths     = array_reverse($paths, true);

                foreach ($paths as $path) {
                    $loadFile = $path . $classFile;
                    if (Xoops_Zend_Loader::isReadable($loadFile)) {
                        include_once $loadFile;
                        if (class_exists($className, false)) {
                            /*
                            if (null !== $incFile) {
                                self::_appendIncFile($loadFile);
                            }
                            */
                            $found = true;
                            XOOPS::persist()->registerClass($className, $loadFile);
                            break 2;
                        }
                    }
                }
            }
        }

        if (!$found) {
            if (!$throwExceptions) {
                return false;
            }

            $message = "Plugin by name '$name' was not found in the registry; used paths:";
            foreach ($registry as $prefix => $paths) {
                $message .= "\n$prefix: " . implode(PATH_SEPARATOR, $paths);
            }
            require_once 'Zend/Loader/PluginLoader/Exception.php';
            throw new Zend_Loader_PluginLoader_Exception($message);
       }

        if ($this->_useStaticRegistry) {
            self::$_staticLoadedPlugins[$this->_useStaticRegistry][$name]     = $className;
        } else {
            $this->_loadedPlugins[$name]     = $className;
        }
        return $className;
    }
}
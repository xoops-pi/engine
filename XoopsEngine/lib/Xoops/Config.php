<?php
/**
 * Config handler for Xoops Engine
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
 * @package         Xoops_Core
 * @since           3.0
 * @version         $Id$
 * @uses            Zend_Config
 * @uses            Zend_Config_Ini
 */

class Xoops_Config
{
    /**
     * Get realpath of a file from its relative path
     */
    public static function realPath(&$filename)
    {
        if (!file_exists($filename)) {
            $filename = XOOPS::path('var') . DIRECTORY_SEPARATOR . 'etc' . DIRECTORY_SEPARATOR . $filename;
            if (!file_exists($filename)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Load a file's content by different method according to its suffix
     *
     * Note: php extension might be appended to protect from disclosure. In this case, the real suffix will be checked with an extra step, like 'config.ini.php'
     */
    public static function load($filename, $section = null, $options = false)
    {
        // Check if file exists
        if (!static::realPath($filename)) {
            trigger_error("Config file for '$filename' is not found ", E_USER_WARNING);
            return false;
        }
        // Get suffix
        $suffix = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if ($suffix === 'dist') {
            $suffix = strtolower(pathinfo(basename($filename, ".dist"), PATHINFO_EXTENSION));
        } elseif ($suffix === 'php') {
            $suffix_sub = strtolower(pathinfo(basename($filename, ".php"), PATHINFO_EXTENSION));
            if (in_array($suffix_sub, array('ini', 'xml', 'json', 'yaml'))) {
                $suffix = $suffix_sub;
            }
        }

        if ('php' == $suffix) {
            $config = include $filename;
            if (!empty($section)) {
                $config = isset($config[$section]) ? $config[$section] : false;
            }
            return $config;
        }
        $zendConfig = 'Zend_Config_' . ucfirst($suffix);
        if (!class_exists($zendConfig)) {
            return false;
        }
        $config = new $zendConfig($filename, $section, $options);
        return $config->toArray();
    }

    /**
     * Write config content into a file by writer according to its suffix
     *
     * Note: php extension might be appended to protect from disclosure. In this case, the real suffix will be checked with an extra step, like 'config.ini.php'
     */
    public static function write($filename = null, $config = null, $exclusiveLock = null)
    {
        // Get fullpath
        static::realPath($filename);

        $suffix = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if ($suffix === 'dist') {
            $suffix = strtolower(pathinfo(basename($filename, ".dist"), PATHINFO_EXTENSION));
        } elseif ($suffix === 'php') {
            $suffix_sub = strtolower(pathinfo(basename($filename, ".php"), PATHINFO_EXTENSION));
            if (in_array($suffix_sub, array('ini', 'xml', 'json', 'yaml'))) {
                $suffix = $suffix_sub;
            }
        }

        $configClass = 'Zend_Config_Writer_' . ('php' == $suffix ? 'Array' : ucfirst($suffix));
        if (class_exists("Xoops_" . $configClass)) {
            $configClass = "Xoops_" . $configClass;
        } elseif (!class_exists($configClass)) {
            return false;
        }
        if (is_array($config)) {
            $config = new Zend_Config($config);
        }
        $writer = new $configClass();
        $writer->write($filename, $config, $exclusiveLock);
        return true;
    }
}
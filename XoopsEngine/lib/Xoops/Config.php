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

    public static function load($filename, $section = null, $options = false)
    {
        if (!static::realPath($filename)) {
            trigger_error("Config file for '$filename' is not found ", E_USER_WARNING);
            return false;
        }
        $suffix = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
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

    public static function write($filename = null, $config = null, $exclusiveLock = null)
    {
        static::realPath($filename);

        $suffix = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
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
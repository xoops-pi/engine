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
 * @package         Application
 * @subpackage      Resource
 * @version         $Id$
 */

class Xoops_Zend_Application_Resource_Autoload extends Zend_Application_Resource_ResourceAbstract
{
    public function init()
    {
        $options = $this->getOptions();

        // Register autoloader for system module classes in case system is not defined in modules.ini.php
        Xoops::autoloader()->registerCallback(array($this, 'loadSystem'));
    }

    public function loadSystem($class)
    {
        $class = strtolower($class);
        if (substr($class, 0, 7) == "system_") {
            $trimmedClass = substr($class, 7);
            $file = Xoops::path('app') . '/system/class/'
                . str_replace(
                    array('\\', '_'),
                    DIRECTORY_SEPARATOR,
                    $trimmedClass
                )
                . '.php';
            return file_exists($file) ? $file : false;
        }
        return false;
    }
}
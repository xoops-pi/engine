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

class Applet extends ServiceAbstract
{
    protected static $applets = array();

    public function load($key, $options = array())
    {
        if (!isset(static::$applets[$key])) {
            static::$applets[$key] = false;
            $class = "Applet\\" . ucfirst($key);
            if (!class_exists($class)) {
                $class = "Applet_" . ucfirst($key);
                if (!class_exists($class)) {
                    //trigger_error("Applet class \"{$class}\" for \"{$key}\" was not loaded.", E_USER_ERROR);
                    return false;
                }
            }
            static::$applets[$key] = new $class($options);
        } elseif (!empty($options)) {
            static::$applets[$key]->setOptions($options);
        }

        return static::$applets[$key];
    }
}
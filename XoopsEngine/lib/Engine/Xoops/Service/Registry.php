<?php
/**
 * XOOPS Registry service class
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
 * @package         Xoops_Service
 * @version         $Id$
 */

namespace Engine\Xoops\Service;

class Registry extends \Kernel\Service\Registry
{
    protected function loadHandler($name, $module = null)
    {
        if (empty($module)) {
            $class = "Engine\\Xoops\\Registry\\" . ucfirst($name);
        } else {
            //$class = "\\module\\" . $module . "\\registry\\" . $name;
            $ns = 'app' == \Xoops::service('module')->getType($module) ? 'app' : 'module';
            $class = $ns . "\\" . $module . "\\registry\\" . $name;
        }
        if (!class_exists($class)) {
            trigger_error("Registry class \"{$class}\" was not loaded.", E_USER_ERROR);
            $handler = false;
        } else {
            $handler = new $class;
        }
        return $handler;
    }
}
<?php
/**
 * Demo module event observer class
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
 * @category        Xoops_Module
 * @package         Demo
 * @version         $Id$
 */

namespace App\Demo;

class Event
{
    public static function message($data, $module)
    {
        \Debug::e("Called by {$module} through " . __METHOD__);
    }

    public static function selfcall($data, $module)
    {
        \Debug::e("Called by {$module} through " . __METHOD__);
    }

    public static function moduleupdate($data, $module)
    {
        \Xoops::service('logger')->log("Called by {$module} through " . __METHOD__);
    }

    public static function moduleinstall($data, $module)
    {
        \Xoops::service('logger')->log("Called by {$module} through " . __METHOD__);
    }

    public static function runtime($data, $module)
    {
        \Xoops::service('logger')->log("Called by {$module} through " . __METHOD__);
    }

    public static function register($data, $module)
    {
        \Debug::e("Called by {$module} through " . __METHOD__);
    }
}
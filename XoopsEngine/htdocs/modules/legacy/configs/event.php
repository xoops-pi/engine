<?php
/**
 * Legacy module event config
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       The Xoops Engine http://sourceforge.net/projects/xoops/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @since           3.0
 * @category        Xoops_Module
 * @package         Legacy
 * @version         $Id$
 */

return array(
    // Observer list
    "observers" => array(
        array(
            // event info: module, event name
            "event"     => array("user", "register"),
            // callback info: class, method
            "callback"  => array("user", "register")
        ),
        array(
            "event"     => array("user", "activate"),
            "callback"  => array("user", "activate")
        ),
        array(
            "event"     => array("user", "delete"),
            "callback"  => array("user", "delete")
        ),
    ),
);
<?php
/**
 * User module event config
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
 * @package         User
 * @version         $Id$
 */

return array(
    // Event list
    "events"    => array(
        "login" => array(
            "title" => "User login",
        ),
        "login_failure" => array(
            "title" => "User login failure",
        ),
        "register" => array(
            "title" => "User register",
        ),
        "update" => array(
            "title" => "User update",
        ),
        "activate"  => array(
            "title" => "User account activation",
        ),
        "delete" => array(
            "title" => "User account deletion",
        ),
    ),
    // Observer list
    "observers" => array(
        array(
            // event info: module, event name
            "event"     => array("user", "login"),
            // callback info: class, method
            "callback"  => "event::login",
        ),
        array(
            "event"     => array("user", "login_failure"),
            "callback"  => "event::login_failure",
        ),
        array(
            "event"     => array("user", "register"),
            "callback"  => "event::register",
        ),
        array(
            "event"     => array("user", "activate"),
            "callback"  => "event::activate",
        ),
        array(
            "event"     => array("system", "module_install"),
            "callback"  => "event::profile",
        ),
        array(
            "event"     => array("system", "module_uninstall"),
            "callback"  => "event::profile",
        ),
        array(
            "event"     => array("system", "module_activate"),
            "callback"  => "event::profile",
        ),
        array(
            "event"     => array("system", "module_deactivate"),
            "callback"  => "event::profile",
        ),
        array(
            "event"     => array("system", "module_update"),
            "callback"  => "event::profile",
        ),
    ),
);
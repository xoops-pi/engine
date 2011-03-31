<?php
/**
 * System module page config
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
 * @package         System
 * @version         $Id$
 */

return array(
    // Front section
    "front" => array(
        // pseudo page for global blocks
        array(
            'title'         => _SYSTEM_MI_PAGE_BLOCKS,
            'name'          => "block",
            'module'        => "default",
            "parent"        => "public",
            'block'         => 1,
        ),
        // homepage
        array(
            'title'         => _SYSTEM_MI_PAGE_HOMEPAGE,
            'module'        => "default",
            'controller'    => "index",
            'action'        => "index",
            'block'         => 1,
        ),
        // utility page, not used yet
        array(
            'title'         => _SYSTEM_MI_PAGE_UTILITY,
            'controller'    => "utility",
            'module'        => "default",
            'block'         => 1,
        ),
        // error message page
        array(
            'title'         => _SYSTEM_MI_PAGE_ERROR,
            'controller'    => "error",
            'module'        => "default",
            'block'         => 1,
        ),
        // front redirect page of system module
        array(
            "controller"    => "index",
            "action"        => "index",
            'title'         => _SYSTEM_MI_PAGE_REDIRECT,
            "parent"        => array(
                "name"      => "public",
                "module"    => "system",
            ),
            "access"        => array(
                "guest"     => 1,
                "member"    => 1,
            ),
        ),
    ),
    // Admin section
    "admin" => array(
        // System admin generic access
        array("parent"      => "admin",
            "controller"    => "index"),
        // System readme
        array("parent"      => "admin",
            "controller"    => "readme"),

        // System specs
        // preferences
        array("parent"      => "preference",
            "controller"    => "preference"),
        // appearance
        array("parent"      => "appearance",
            "controller"    => "block"),
        array("parent"      => "appearance",
            "controller"    => "theme"),
        // permissions
        array("parent"      => "permission",
            "controller"    => "role"),
        array("parent"      => "permission",
            "controller"    => "resource"),
        array("parent"      => "permission",
            "controller"    => "rule"),
        // modules
        array("parent"      => "module",
            "controller"    => "module"),
        // comment
        array("parent"      => "comment",
            "controller"    => "comment"),
        // notification
        array("parent"      => "notification",
            "controller"    => "notification"),
        // event
        array("parent"      => "event",
            "controller"    => "event"),
        // toolkit
        array("parent"      => "toolkit",
            "controller"    => "cache"),
        array("parent"      => "toolkit",
            "controller"    => "file"),
        array("parent"      => "toolkit",
            "controller"    => "image"),
        array("parent"      => "toolkit",
            "controller"    => "banner"),
    ),
    // Feed section
    "feed" => array(
        array(
            'cache_expire'  => 0,
            'cache_level'   => "",
            'title'         => XOOPS::_("What's new"),
        ),
    ),
);

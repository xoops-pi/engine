<?php
/**
 * User Module navigation config
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
    'front' => array(
        'login'     => array(
            'label'         => "Login",
            'route'         => "login",
            "controller"    => "login",
            "action"        => "login",
            'resource'      => array(
                "module"    => "system",
                "resource"  => "guest",
                "section"   => "front"
            ),
        ),
        'register'     => array(
            'label'         => "Register",
            'route'         => "register",
            "controller"    => "register",
            "action"        => "index",
            'resource'      => array(
                "module"    => "system",
                "resource"  => "guest",
                "section"   => "front"
            ),
        ),
        'profile'     => array(
            'label'         => "Profile",
            'route'         => "profile",
            'resource'      => array(
                "module"    => "system",
                "resource"  => "member",
                "section"   => "front"
            ),
        ),
        'logout'     => array(
            'label'         => "Logout",
            'route'         => "logout",
            "controller"    => "login",
            "action"        => "logout",
            'resource'      => array(
                "module"    => "system",
                "resource"  => "member",
                "section"   => "front"
            ),
        ),
        'user'      => array(
            'label'         => "User",
            'route'         => "user",
            "controller"    => "index",
            "action"        => "index",
            'visible'       => 0,
        ),
    ),
    'admin' => array(
        'user'      => array(
            'label'         => "Users",
            'route'         => "admin",
            'controller'    => "index",
            'action'        => "index",
            'pages' => array(
                'user'      => array(
                    'label'         => "List",
                    'route'         => "admin",
                    'controller'    => "index",
                    'action'        => "index",
                    'pages' => array(
                        'add'       => array(
                            'label'         => "Add",
                            'route'         => "admin",
                            'controller'    => "index",
                            'action'        => "add",
                            'visible'       => 0,
                        ),
                        'edit'      => array(
                            'label'         => "Edit",
                            'route'         => "admin",
                            'controller'    => "index",
                            'action'        => "edit",
                            'visible'       => 0,
                        ),
                    ),
                ),
                'pending'       => array(
                    'label'         => "Pending",
                    'route'         => "admin",
                    'controller'    => "pending",
                    'action'        => "index",
                ),
                'search'       => array(
                    'label'         => "Search",
                    'route'         => "admin",
                    'controller'    => "search",
                    'action'        => "index",
                    'pages' => array(
                        'result'    => array(
                            'label'         => "Search result",
                            'route'         => "admin",
                            'controller'    => "search",
                            'action'        => "result",
                            'visible'       => 0,
                        ),
                    ),
                ),
            ),
        ),

        'profile'     => array(
            'label'         => "Profile",
            'route'         => "admin",
            'controller'    => "meta",
            'action'        => "index",
            'pages' => array(
                'meta'      => array(
                    'label'         => "Meta",
                    'route'         => "admin",
                    'controller'    => "meta",
                    'action'        => "index",
                    'pages' => array(
                        'add'      => array(
                            'label'         => "Meta",
                            'route'         => "admin",
                            'controller'    => "meta",
                            'action'        => "add",
                            'visible'       => 0,
                        ),
                    ),
                ),
                'category'  => array(
                    'label'         => "Category",
                    'route'         => "admin",
                    'controller'    => "category",
                    'action'        => "index",
                ),
                'sort'      => array(
                    'label'         => "Sorting",
                    'route'         => "admin",
                    'controller'    => "sort",
                    'action'        => "index",
                ),
            ),
        ),

        'permission'     => array(
            'label'         => "Permission",
            'route'         => "admin",
            'controller'    => "permission",
            'action'        => "index",
            'pages' => array(
                'role'      => array(
                    'label'         => "User roles",
                    'route'         => "admin",
                    'controller'    => "permission",
                    'action'        => "index",
                ),
                'access'    => array(
                    'label'         => "Role access",
                    'route'         => "admin",
                    'controller'    => "permission",
                    'action'        => "access",
                ),
                'profile'    => array(
                    'label'         => "Meta access",
                    'route'         => "admin",
                    'controller'    => "permission",
                    'action'        => "meta",
                ),
            ),
        ),

    ),
);

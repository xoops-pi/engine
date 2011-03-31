<?php
/**
 * System module navigation config
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

// System settings, don't change
return array(
    "navigations" => array(
        // Front-end navigation template
        "front"     => array(
            "name"      => "front",
            "section"   => "front",
            "title"    => XOOPS::_("Front-end global navigation"),
        ),
        // Front-end module navigation placeholder
        "front-modules"     => array(
            "name"      => "front-modules",
            "section"   => "front",
            "title"    => XOOPS::_("Front-end modules"),
        ),
        // Back-end navigation template
        "admin"     => array(
            "name"      => "admin",
            "section"   => "admin",
            "title"     => XOOPS::_("Admin navigation template"),
        ),
    ),
    // navigation template
    "front-template" => "navigation.front.xml",
    "admin-template" => "navigation.admin.xml",
    // Front navigations
    //"front" => false,
    // Admin navigations
    "admin" => "admin.system.xml",

    // Front navigation
    'front' => array(
        'root'      => array(
            'label'         => "Root Login",
            'route'         => "root",
            'controller'    => 'root',
            'action'        => 'index',
            'pages'         => array(
                'process'   => array(
                    'visible'       => false,
                    'label'         => "Root Login",
                    'route'         => "root",
                    'controller'    => 'root',
                    'action'        => 'process',
                ),
            ),
        ),
        'admin'     => array(
            'label'         => "Admin Area",
            'route'         => "admin",
        ),
    ),
);
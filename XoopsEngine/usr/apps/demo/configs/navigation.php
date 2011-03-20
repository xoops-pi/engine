<?php
/**
 * Demo module navigation config
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

return array(
    //"translate" => "navigation",
    "front"   => array(
        'tree'     => array(
            'label'         => XOOPS::_("Test User Call"),
            'route'         => "default",
            'controller'    => "index",
            'action'        => "user",
        ),
        'pagea'     => array(
            'label'         => XOOPS::_("Homepage"),
            'route'         => "default",
            'controller'    => "index",
            'action'        => "index",
            'pages'         => array(
                'pageaa'    => array(
                    'label'         => XOOPS::_("Subpage one"),
                    'route'         => "default",
                    'controller'    => "index",
                    'action'        => "index",
                ),
                'pageab'    => array(
                    'label'         => XOOPS::_("Subpage two"),
                    'route'         => "default",
                    'controller'    => "index",
                    'action'        => "index",
                    'params'        => array(
                        "op"    => "test",
                    ),
                    'pages'         => array(
                        'pageaba'   => array(
                            'label'         => XOOPS::_("Leaf one"),
                            'route'         => "default",
                            'controller'    => "index",
                            'action'        => "index",
                            'params'        => array(
                                "op"    => "test",
                                "page"  => 2,
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    'admin' => array(
        'pagea'     => array(
            'label'         => "Homepage",
            'route'         => "admin",
            'controller'    => "index",
            'action'        => "index",
        ),
    ),
);

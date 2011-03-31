<?php
/**
 * Demo module config
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

//Debug::backtrace();
return array(
    'name'          => _DEMO_MI_NAME,
    'description'   => _DEMO_MI_DESC,
    'version'       => "1.0.0",
    'email'         => "infomax@gmail.com",
    'author'        => "Taiwen Jiang <phppp@users.sourceforge.net>",
    'credits'       => "XOOPS Development Team; Zend Framework",
    'license'       => "GPL v2",
    'logo'          => "resources/images/logo.png",
    'readme'        => "docs/readme.txt",

    'info'          => array(
        'translate'     => array(
            'adapter'   => 'gettext',
            'data'      => ''
        ),
    ),

    //'onInstall'     => "App_Demo_Module",
    //'onUpdate'      => "App_Demo_Module",
    //'onUninstall'   => "App_Demo_Module",

    'onInstall'     => "Module",
    'onUpdate'      => "Module",
    'onUninstall'   => "Module",

    'extensions'    => array(
        'database'  => array(
            'sqlfile'   => array(
                'mysql' => "sql/mysql.sql"),
            'tables'    => array(
                'demo_test')),
        'config'    => array(
            "categories"    => array(
                array("name"    => "_DEMO_AM_CATEGORY_GENERAL",
                        "key"   => "general",
                        "order" => 5),
                array("name"    => "_DEMO_AM_CATEGORY_TEST",
                        "key"   => "test",
                        "order" => 10)),
            "items"         => array(
                array(
                    'name'          => "test",
                    'category'      => "test",
                    'title'         => "_DEMO_AM_TEST",
                    'description'   => "_DEMO_AM_TEST_DESC",
                    'edit'          => "text",
                    'filter'        => "string",
                    'default'       => "Configuration text"),
                array(
                    'name'          => "add",
                    'category'      => "general",
                    'title'         => "_DEMO_AM_TEST",
                    'description'   => "_DEMO_AM_TEST_DESC",
                    'edit'          => "text",
                    'filter'        => "string",
                    'default'       => "Configuration text"))),
        'event'     => "event.php",
        'test'      => array("custom_handler" => "Demo_Configtest"),
        'search'    => array("callback" => "search::index"),
        'page'      => "page.php",
        'acl'       => "acl.php",
        'navigation'    => "navigation.php",
        'notification'  => "notification.php",
        'comment'   => "comment.php",
        'route'     => "route.ini",
        'block'     => array(
            array(
                'file'          => "blocks.php",
                'title'         => _DEMO_MI_BLOCK,
                'description'   => _DEMO_MI_BLOCK_DESC,
                'show_func'     => "demo_block_show",
                'edit_func'     => "demo_block_edit",
                'options'       => "optOne|optTwo",
                'template'      => 'block_show.html')),

        )
);
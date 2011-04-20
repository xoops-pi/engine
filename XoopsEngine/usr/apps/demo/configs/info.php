<?php
/**
 * Demo app config
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
 * @category        Xoops_App
 * @package         Demo
 * @version         $Id$
 */

/**
 * Application manifest
 */
return array(
    // App name
    'name'          => _DEMO_MI_NAME,
    // Description, for admin
    'description'   => _DEMO_MI_DESC,
    // Version number
    'version'       => '1.0.0',
    // Author: full name <email> <website>
    'author'        => 'Taiwen Jiang <phppp@users.sourceforge.net>',
    // Contact email
    'email'         => 'infomax@gmail.com',
    // Credits for contributors
    'credits'       => 'XOOPS Development Team; Zend Framework',
    // Distribution license
    'license'       => 'GPL v2',
    // Logo image, for admin
    'logo'          => 'resources/images/logo.png',
    // Readme file, for admin
    'readme'        => 'docs/readme.txt',

    // Information for loading, not used
    'info'          => array(
        // translation
        'translate'     => array(
            'adapter'   => 'gettext',
            'data'      => ''
        ),
    ),

    // Callback class for installation
    'onInstall'     => 'Module',
    // Callback for update
    'onUpdate'      => 'Module',
    // Callback for uninstall
    'onUninstall'   => 'Module',

    // extensions
    'extensions'    => array(
        // Database meta
        'database'  => array(
            'sqlfile'   => array(
                'mysql' => 'sql/mysql.sql'),
            // Tables to be removed during uninstall, optional - the table list will be generated automatically upon installation
            'tables'    => array(
                'demo_test')),
        'config'    => array(
            'categories'    => array(
                array('name'    => '_DEMO_AM_CATEGORY_GENERAL',
                        'key'   => 'general',
                        'order' => 5),
                array('name'    => '_DEMO_AM_CATEGORY_TEST',
                        'key'   => 'test',
                        'order' => 10)
            ),
            'items'         => array(
                array(
                    'name'          => 'test',
                    'category'      => 'test',
                    'title'         => '_DEMO_AM_TEST',
                    'description'   => '_DEMO_AM_TEST_DESC',
                    'edit'          => 'text',
                    'filter'        => 'string',
                    'default'       => 'Configuration text'
                ),
                array(
                    'name'          => 'add',
                    'category'      => 'general',
                    'title'         => '_DEMO_AM_TEST',
                    'description'   => '_DEMO_AM_TEST_DESC',
                    'edit'          => 'text',
                    'filter'        => 'string',
                    'default'       => 'Configuration text'
                )
            )
        ),
        'block'     => 'block.php',
        'event'     => 'event.php',
        // Search registry, 'class:method'
        'search'    => array('callback' => 'search::index'),
        'page'      => 'page.php',
        'acl'       => 'acl.php',
        'navigation'    => 'navigation.php',
        'notification'  => 'notification.php',
        'comment'   => 'comment.php',
        'route'     => 'route.ini.php',
        // Callback for stats and monitoring
        'monitor'   => array('callback' => 'monitor::index'),
        // Additional custom extension
        'test'      => array('custom_handler' => 'Demo_Configtest'),
    )
);
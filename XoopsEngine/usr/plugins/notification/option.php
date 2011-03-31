<?php
/**
 * Plugin options
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
 * @category        Xoops_Plugin
 * @package         Notification
 * @version         $Id$
 */

return array(
    array(
        'name'          => "method",
        'title'         => "_PLUGIN_NOTIFICATION_METHOD",
        'description'   => "_PLUGIN_NOTIFICATION_METHOD_DESC",
        'edit'          => "select",
        'filter'        => "string",
        'default'       => "message",
        'options'       => array(
                            "_PLUGIN_NOTIFICATION_METHOD_EMAIL"     => "email",
                            "_PLUGIN_NOTIFICATION_METHOD_MESSAGE"   => "message")),
    array(
        'name'          => "items_iteration",
        'title'         => "_PLUGIN_NOTIFICATION_ITEMS_ITERATION",
        'description'   => "_PLUGIN_NOTIFICATION_ITEMS_ITERATION_DESC",
        'edit'          => "text",
        'filter'        => "number_int",
        'default'       => 100),
    array(
        'name'          => "iteration_interval",
        'title'         => "_PLUGIN_NOTIFICATION_INTERVAL",
        'description'   => "_PLUGIN_NOTIFICATION_INTERVAL_DESC",
        'edit'          => "select",
        'filter'        => "number_int",
        'default'       => 100000,
        'options'       => array(
                            "_PLUGIN_NOTIFICATION_INTERVAL_SEC1"        => 1000000,
                            "_PLUGIN_NOTIFICATION_INTERVAL_SEC10TH"     => 100000,
                            "_PLUGIN_NOTIFICATION_INTERVAL_SEC100TH"    => 10000,
                            "_PLUGIN_NOTIFICATION_INTERVAL_SEC1000TH"   => 1000)),
    array(
        'name'          => "cache_expire",
        'title'         => "_PLUGIN_NOTIFICATION_CACHE_EXPIRE",
        'description'   => "_PLUGIN_NOTIFICATION_CACHE_EXPIRE_DESC",
        'edit'          => "CacheExpire",
        'default'       => 0,
        'filter'        => "number_int"),
);
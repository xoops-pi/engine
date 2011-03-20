<?php
/**
 * Demo module notification config
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
    // For index action
    "index1"    => array(
        "title"         => "_DEMO_PLUGIN_NOTIFICATION_INDEX_ONE",
        "controller"    => "index",
        "action"        => "index",
        "callback"      => "notification",
    ),
    // For index action
    "index2"    => array(
        "title"         => "_DEMO_PLUGIN_NOTIFICATION_INDEX_TWO",
        "controller"    => "index",
        "action"        => "index",
        "content"       => "_DEMO_PLUGIN_NOTIFICATION_INDEX",
        "translate"     => "notification",
    ),
    // For test action
    "index"     => array(
        "title"         => "_DEMO_PLUGIN_NOTIFICATION_TEST",
        "controller"    => "index",
        "action"        => "test",
        "content"       => "_DEMO_PLUGIN_NOTIFICATION_TEST",
    ),
);
<?php
/**
 * Mvc module event config
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
 * @package         Mvc
 * @version         $Id$
 */

return array(
    // Event list
    "events"    => array(
        // event name (unique)
        "user_call" => array(
            // title
            "title" => XOOPS::_("Event hook demo"),
        ),
    ),
    // Observer list
    "observers" => array(
        array(
            // event info: module, event name
            "event"     => array("pm", "test"),
            // callback info: class, method
            "callback"  => array("event", "message")
        ),
        array(
            "event"     => array("mvc", "user_call"),
            "callback"  => array("event", "selfcall")
        ),
        array(
            "event"     => array("system", "module_install"),
            "callback"  => array("event", "moduleinstall")
        ),
        array(
            "event"     => array("system", "module_update"),
            "callback"  => array("event", "moduleupdate")
        ),
    ),
);
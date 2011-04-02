<?php
/**
 * System module event config
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
    // Event list
    "events"    => array(
        // event name (unique)
        "module_install" => array(
            // title
            "title" => XOOPS::_("Module installation"),
        ),
        "module_uninstall"  => array(
            "title" => XOOPS::_("Module uninstallation"),
        ),
        "module_activate"  => array(
            "title" => XOOPS::_("Module activation"),
        ),
        "module_deactivate"  => array(
            "title" => XOOPS::_("Module deactivation"),
        ),
        "module_update"  => array(
            "title" => XOOPS::_("Module update"),
        ),
    ),
    // Observer list
    "observers" => array(
        array(
            // event info: module, event name
            "event"     => array("system", "module_install"),
            // callback info: class::method
            "callback"  => "event::moduleinstall",
        ),
    ),
);
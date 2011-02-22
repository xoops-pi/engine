<?php
/**
 * Legacy module config
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
 * @package         Legacy
 * @version         $Id$
 */

return array(
    'name'          => 'Legacy module agency',
    'description'   => '',
    'version'       => "1.0.0",
    'email'         => "infomax@gmail.com",
    'author'        => "Taiwen Jiang <phppp@users.sourceforge.net>",
    'credits'       => "XOOPS Development Team",
    'license'       => "GPL v2",

    'onInstall'     => "Installer",
    'onUninstall'   => "Installer",

    'extensions'    => array(
        'event'     => "event.php",
        'navigation'    => false,
    ),
);
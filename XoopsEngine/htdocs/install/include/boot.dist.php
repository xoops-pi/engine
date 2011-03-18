<?php
/**
 * Xoops boot file
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Xoops Engine
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @version         $Id$
 */

if (!defined("XOOPS_BOOT_INCLUDED")) {
    define("XOOPS_BOOT_INCLUDED", 1);

    // Engine type, mapped to folders in /lib/Engine, default as "Xoops"
    defined("XOOPS_ENGINE") OR define("XOOPS_ENGINE", "");

    // Physical path to system library (readonly) directory WITHOUT trailing slash
    defined("XOOPS_PATH") OR define("XOOPS_PATH", "");

    // Backend support for persistent data, valid values: Apc, Memcached, Memcache, File
    defined("XOOPS_PERSIST_TYPE") OR define("XOOPS_PERSIST_TYPE", "");

    // Prefix for persistent data key
    defined("XOOPS_PERSIST_PREFIX") OR define("XOOPS_PERSIST_PREFIX", "xoops");

    include XOOPS_PATH . '/Xoops.php';
    if (!defined('XOOPS_BOOT_SKIP')) {
        return XOOPS::boot(XOOPS_ENGINE);
    }
}
<?php
/**
 * XOOPS global index file
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
 * @package         Xoops_Kernel
 * @version         $Id$
 */

define('HOMEPAGE_LEGACY', false);

if (HOMEPAGE_LEGACY) {
    /**#@+
     * Legacy mode
     */
    include "mainfile.php";

    $xoopsOption['template_main'] = "file:app/default/index.html";
    include "header.php";
    include "footer.php";
    return;
    /*#@-*/
}

/**#@+
 * Application mode
 */
if (!empty($_SERVER['REQUEST_URI']) && false !== ($pos = strpos($_SERVER['REQUEST_URI'], "index.php"))) {
    $_SERVER['REQUEST_URI'] = substr($_SERVER['REQUEST_URI'], 0, $pos);
}

define('XOOPS_BOOTSTRAP', 'application');
include __DIR__ . "/boot.php";
exit();
/*#@-*/
<?php
/**
 * TextSanitizer extension
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * Build your custom configuration:
 * 1 Copy the file to config.custom.php
 * 2 Change the values according to your needs
 *
 *
 * @copyright       The XOOPS Project http://sourceforge.net/projects/xoops/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @since           2.3.0
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @version         $Id: config.php 2135 2008-09-21 22:16:44Z phppp $
 * @package         class
 * @subpackage      textsanitizer
 */

return $config = array(
        // Filters XSS scripts on display of text
        // There is considerable trade-off between security and performance
        "filterxss_on_display"  => false,
    );
?>
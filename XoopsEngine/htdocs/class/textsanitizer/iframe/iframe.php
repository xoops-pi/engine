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
 * @copyright       The XOOPS Project http://sourceforge.net/projects/xoops/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @since           2.3.0
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @version         $Id: iframe.php 2007 2008-08-30 13:13:01Z phppp $
 * @package         class
 * @subpackage      textsanitizer
 */

    
class MytsIframe extends MyTextSanitizerExtension
{
    function load(&$ts) 
    {
        $ts->patterns[] = "/\[iframe=(['\"]?)([^\"']*)\\1]([^\"]*)\[\/iframe\]/sU";
        $ts->replacements[] = "<iframe src='\\3' width='100%' height='\\2' scrolling='auto' frameborder='yes' marginwidth='0' marginheight='0' noresize></iframe>";
        
        return true;
    }
}
?>
<?php
/**
 * XOOPS Framework
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       The XOOPS Project http://sourceforge.net/projects/xoops/
 * @license         BSD liscense
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @since           3.0
 * @package         xoops
 * @version         $Id$
 */

global $xoops;
include_once $xoops->path("www") . "/class/xoopsform/formselect.php";

class XoopsFormSelectNavigation extends XoopsFormSelect
{
    /**
     * Constructor
     * 
     * @param    string    $caption
     * @param    string    $name
     * @param    string    $value    Pre-selected value
     * @param    int        $size    Number of rows. "1" makes a drop-down-list.
     */
    function XoopsFormSelectNavigation($caption, $name, $value = null, $size = 1)
    {
        $this->XoopsFormSelect($caption, $name, $value, $size);
        $model = XOOPS::getModel("navigation");
        
        $list = XOOPS::service('registry')->translate->read("");
        $languageList = array();
        foreach (array_keys($list) as $lang) {
            foreach (array_keys($list[$lang]) as $charset) {
                $languageList["{$lang}.{$charset}"] = XOOPS::_($lang) . ":{$charset}";
            }
        }
        $this->addOptionArray($languageList);
        
    }
}
?>
<?php
/**
 * Welcome applet for Xoops Engine
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
 * @category        Xoops_Zend
 * @package         View
 * @version         $Id$
 */


class Xoops_Zend_View_Helper_Applet extends Zend_View_Helper_Abstract
{
    /**
     * Generates content from an applet of its script
     *
     * @access public
     *
     * @param  array|string|int|object    $name        applet name
     * @param  array                $options    options passed to the block: parameters and cacheLifetime, cacheLevel
     * @return string content of the block
     */
    public function applet($name, $options = array())
    {
        if ($applet = Xoops::service('applet')->load($name, $options)) {
            return $applet->render();
        }
        return false;
    }
}
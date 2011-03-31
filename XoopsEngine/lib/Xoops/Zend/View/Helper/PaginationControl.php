<?php
/**
 * Zend Framework for Xoops Engine
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

class Xoops_Zend_View_Helper_PaginationControl extends Zend_View_Helper_PaginationControl
{
    /**
     * Render the provided pages.  This checks if $view->paginator is set and,
     * if so, uses that.  Also, if no scrolling style or partial are specified,
     * the defaults will be used (if set).
     *
     * @param  Zend_Paginator (Optional) $paginator
     * @param  string $scrollingStyle (Optional) Scrolling style
     * @param  string $partial (Optional) View partial
     * @param  array|string $params (Optional) params to pass to the partial
     * @return string
     * @throws Zend_View_Exception
     */
    public function paginationControl(Zend_Paginator $paginator = null, $scrollingStyle = null, $partial = null, $params = null)
    {
        $params = $paginator->getParams();
        $router = XOOPS::registry("frontController")->getRouter();
        foreach($params as $param => $value) {
            $router->setGlobalParam($param, $value);
        }

        return parent::paginationControl($paginator, $scrollingStyle, $partial, $params);
    }
}
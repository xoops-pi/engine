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

class Xoops_Zend_View_Helper_HtmlLink extends Zend_View_Helper_HtmlElement
{
    /**
     * Output an anchor element
     *
     * @param string $href URI of link
     * @param string $text
     * @param string $title short description
     * @param array  $attribs Attribs for the object tag
     * @return string
     */
    public function HtmlLink($href, $text, $title = "", array $attribs = array())
    {
        //$href = XOOPS::url($href);
        // Link header
        $xhtml = '<a href="' . $href . '" title="' . $this->view->escape($title). '" ' . $this->_htmlAttribs($attribs) . '>'. $this->view->escape($text) . '</a>';

        return $xhtml;
    }
}

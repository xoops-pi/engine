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

/**
 * Helper for setting and retrieving html img element
 *
 * <code>
 * XOOPS::registry('view')->htmlImage('src', 'Alt', array());
 * </code>
 */

class Xoops_Zend_View_Helper_HtmlImage extends Zend_View_Helper_HtmlElement
{
    /**
     * Output an image set
     *
     * @param string $src URI of image to embed
     * @param string $alt short description
     * @param array  $attribs Attribs for the object tag
     * @return string
     */
    public function htmlImage($src, $alt, array $attribs = array())
    {
        $src = XOOPS::url($this->view->resourcePath($src));
        // Image header
        //$xhtml = '<img src="' . $src . '" alt="' . htmlspecialchars(strval($alt)). '" ' . $this->_htmlAttribs($attribs) . $this->getClosingBracket();
        $xhtml = '<img src="' . $src . '" alt="' . $this->view->escape(strval($alt)). '" ' . $this->_htmlAttribs($attribs) . $this->getClosingBracket();

        return $xhtml;
    }
}

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

class Xoops_Zend_View_Helper_FormLink extends Zend_View_Helper_FormElement
{
    /**
     * Generates a 'link' element.
     *
     * @access public
     *
     * @param string|array $name If a string, the element name.  If an
     * array, all other parameters are ignored, and the array elements
     * are used in place of added parameters.
     *
     * @param mixed $value The element value.
     *
     * @param array $attribs Attributes for the element tag.
     *
     * @return string The element XHTML.
     */
    public function formLink($name, $value = null, $attribs = null)
    {
        $info = $this->_getInfo($name, $value, $attribs);
        extract($info); // name, value, attribs, options, listsep, disable

        // build the element
        $disabled = '';
        if ($disable) {
            // disabled
            $disabled = ' disabled="disabled"';
        }

        // XHTML or HTML end tag?
        $endTag = ' />';
        if (($this->view instanceof Zend_View_Abstract) && !$this->view->doctype()->isXhtml()) {
            $endTag= '>';
        }

        $xhtml = '<label for="' . $this->view->escape($id) . '-url">' .XOOPS::_("URL") . '</label><input type="text"'
                . ' name="' . $this->view->escape($name) . '[0]"'
                . ' id="' . $this->view->escape($id) . '-url"'
                . ' value="' . $this->view->escape($href) . '"'
                . $disabled
                . $this->_htmlAttribs($attribs)
                . $endTag;
        $xhtml .= '<label for="' . $this->view->escape($id) . '-title">' .XOOPS::_("Title") . '</label><input type="text"'
                . ' name="' . $this->view->escape($name) . '[1]"'
                . ' id="' . $this->view->escape($id) . '-title"'
                . ' value="' . $this->view->escape($title) . '"'
                . $disabled
                . $this->_htmlAttribs($attribs)
                . $endTag;

        return $xhtml;
    }
}

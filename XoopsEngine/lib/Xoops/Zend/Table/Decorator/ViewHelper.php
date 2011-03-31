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
 * @package         Table
 * @version         $Id$
 */

class Xoops_Zend_Table_Decorator_ViewHelper extends Zend_Form_Decorator_ViewHelper
{
    /**
     * Set current table element
     *
     * @param  Xoops_Table|Xoops_Zend_Table_Element $element
     * @return Xoops_Zend_Table_Decorator_HtmlTag
     * @throws Xoops_Zend_Table_Exception on invalid element type
     */
    public function setElement($element)
    {
        if ((!$element instanceof Xoops_Table)
            && (!$element instanceof Xoops_Zend_Table_Element))
        {
            throw new Xoops_Zend_Table_Exception('Invalid element type passed to decorator');
        }

        $this->_element = $element;
        return $this;
    }

    /**
     * Retrieve view helper for rendering element
     *
     * @return string
     */
    public function getHelper()
    {
        if (null === $this->_helper) {
            $options = $this->getOptions();
            if (isset($options['helper'])) {
                $this->setHelper($options['helper']);
                $this->removeOption('helper');
            } else {
                $element = $this->getElement();
                if (null !== $element) {
                    if (null !== ($helper = $element->getAttrib('helper'))) {
                        $this->setHelper($helper);
                    }
                }
            }
        }
        return $this->_helper;
    }

    /**
     * Render an element using a view helper
     *
     * Determine view helper from 'viewHelper' option, or, if none set, from
     * the element type. Then call as
     * helper($element->getName(), $element->getValue(), $element->getAttribs())
     *
     * @param  string $content
     * @return string
     */
    public function render($content)
    {
        if (!$helper = $this->getHelper()) {
            return $content;
        } else {
            return parent::render($content);
        }
    }
}

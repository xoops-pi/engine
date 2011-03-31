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
 * @package         Form
 * @version         $Id$
 */

class Xoops_Zend_Form_Decorator_ViewHelper extends Zend_Form_Decorator_ViewHelper
{
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
                    } elseif (false !== $element->helper) {
                        $type = $element->getType();
                        if ($pos = strrpos($type, '_')) {
                            $type = substr($type, $pos + 1);
                        }
                        $this->setHelper('form' . ucfirst($type));
                    }
                }
            }
        }
        //Debug::e($element->getName() . ": " . $this->_helper);
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
     * @throws Zend_Form_Decorator_Exception if element or view are not registered
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

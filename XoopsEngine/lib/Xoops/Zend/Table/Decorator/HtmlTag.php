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

class Xoops_Zend_Table_Decorator_HtmlTag extends Zend_Form_Decorator_HtmlTag
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
     * Get tag
     *
     * If no tag is registered, either via setTag() or as an option, uses 'div'.
     *
     * @return string
     */
    public function getTag()
    {
        if (null === $this->_tag) {
            if (null !== ($tag = $this->getOption('tag'))) {
                $this->setTag($tag);
            } elseif (null !== ($tag = $this->getElement()->getAttrib('tag'))) {
                $this->setTag($tag);
            }
        }

        return $this->_tag;
    }

    public function getAttribs()
    {
        $attributes = array_merge($this->getElement()->getAttribs(), $this->getOptions());
        if (array_key_exists("tag", $attributes)) {
            unset($attributes["tag"]);
        }
        return $attributes;
    }

    /**
     * Render table row
     *
     * @param  string $content
     * @return string
     */
    public function render($content)
    {
        $element = $this->getElement();
        $elements = (array) $element->getElements();

        $placement = $this->getPlacement();
        $tag = $this->getTag();
        $attribs = $this->getAttribs();

        $contentString = $this->_getOpenTag($tag, $attribs);
        foreach ($elements as $element) {
            $contentString .= $element->render();
        }
        $contentString .= $this->_getCloseTag($tag);

        switch ($placement) {
            case self::APPEND:
                return $content . $contentString;
            case self::PREPEND:
                return $contentString . $content;
            default:
                return $contentString;
        }
    }
}
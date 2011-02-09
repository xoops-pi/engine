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
 * @copyright       The Xoops Engine http://sourceforge.net/projects/xoops/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @since           3.0
 * @category        Xoops_Zend
 * @package         Table
 * @version         $Id$
 */

/**
 * XOOPS table element decorator
 */

class Xoops_Zend_Table_Decorator_Cell extends Xoops_Zend_Table_Decorator_HtmlTag
{
    /**
     * HTML tag to use
     * @var string
     */
    //protected $_tag = "td";

    /**
     * Render table caption
     *
     * @param  string $content
     * @return string
     */
    public function render($content)
    {
        $element = $this->getElement();
        $elementContent = $element->getContent();
        $contentString = is_object($elementContent) && method_exists($elementContent, "render")
                            ? $elementContent->render()
                            : (
                                is_string($elementContent)
                                //? htmlspecialchars($elementContent, ENT_COMPAT, $this->_getEncoding())
                                ? $elementContent
                                : ""
                            );
        $placement = $this->getPlacement();
        $attribs = $this->getAttribs();
        $tag = $this->getTag();
        if ($tag != "th") {
            $tag = "td";
        }

        $contentString = $this->_getOpenTag($tag, $attribs) . $contentString . $this->_getCloseTag($tag);
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

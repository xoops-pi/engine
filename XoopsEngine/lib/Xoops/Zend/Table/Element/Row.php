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

class Xoops_Zend_Table_Element_Row extends Xoops_Zend_Table_Element
{
    /**
     * Element primary decorator
     * @var string
     */
    protected $_decorator = "Row";

    /**
     * @var element tag
     */
    protected $_elementTag;

    /**
     * Element type
     * @var string
     */
    //protected $_type = "row";

    /**
     * @var tag
     */
    //protected $_tag = "tr";

    // Element interaction:

    public function setElementTag($tag)
    {
        $this->_elementTag = $tag;
        return $this;
    }

    public function getElementTag()
    {
        return $this->_elementTag;
    }

    /**
     * Add a new element
     *
     * $element may be either a string, or an object of type
     * Xoops_Zend_Table_Element_Cell or Zend_Form_Element.
     *
     * If a Xoops_Zend_Table_Element_Cell or Zend_Form_Element is provided, $options may be optionally provided,
     *
     * @param  string|Xoops_Zend_Table_Element_Cell|Zend_Form_Element $element
     * @param  array|Zend_Config $options
     * @return Xoops_Zend_Table_Element
     */
    public function addElement($element, $options = null)
    {
        if (!empty($options) && is_string($options)) {
            $options = array("content" => $options);
        }
        if (!isset($options["tag"]) && $this->_elementTag) {
            $options["tag"] = $this->_elementTag;
        }
        if (!$element instanceof Xoops_Zend_Table_Element_Cell) {
            if (!is_array($element)) {
                if (!is_string($element) || strtolower($element) != "cell") {
                    $options["content"] = $element;
                    $element = "cell";
                }
            } elseif (!isset($element["type"])) {
                $element["type"] = "cell";
            }
        }
        parent::addElement($element, $options);
        return $this;
    }

    /**
     * Add multiple cells at once
     *
     * @param  array $elements
     * @return Xoops_Zend_Table_Element
     */
    public function addElements(array $elements)
    {
        $type = "cell";
        foreach ($elements as $spec) {
            if (!is_array($spec)) {
                if ($spec instanceof Xoops_Zend_Table_Element) {
                    $this->addElement($spec);
                } else {
                    $options = array("content" => $spec);
                    $this->addElement($type, $options);
                }
            } else {
                $argc = count($spec);
                $options = array();
                if (isset($spec['type'])) {
                    $type = $spec['type'];
                    if (isset($spec['options'])) {
                        $options = $spec['options'];
                    }
                } elseif (isset($spec['options'])) {
                    $options = $spec['options'];
                } elseif (isset($spec['elements'])) {
                    $options = array("elements" => $spec['elements']);
                } else {
                    if (!is_string($spec[0])) {
                        array_unshift($spec, $type);
                    }
                    $argc = count($spec);
                    switch ($argc) {
                        case 0:
                            continue;
                        case (1 <= $argc):
                            $type = array_shift($spec);
                        case (2 <= $argc):
                            $options = array_shift($spec);
                        case (3 <= $argc):
                        default:
                            if (empty($options)) {
                                $options = array_shift($spec);
                            }
                            break;
                    }
                }
                $this->addElement($type, $options);
            }
        }
        return $this;
    }
}
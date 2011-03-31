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

class Xoops_Zend_Table_Element_Tbody extends Xoops_Zend_Table_Element
{
    /**
     * Element primary decorator
     * @var string
     */
    protected $_decorator = "Tbody";

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

    /**
     * Add a new element
     *
     * @param  array|Xoops_Zend_Table_Element_Row $element
     * @param  array|Zend_Config $options
     * @return Xoops_Zend_Table_Element
     */
    public function addElement($element, $options = null)
    {
        if (is_array($element)) {
            $element["type"] = "row";
        }
        parent::addElement($element, $options);
        return $this;
    }

    /**
     * Add multiple rows at once
     *
     * @param  array $elements
     * @return Xoops_Zend_Table_Element
     */
    public function addElements(array $elements)
    {
        //Debug::e($this->getType() . " elements count: " . count($elements));
        foreach ($elements as $spec) {
            if ($spec instanceof Xoops_Zend_Table_Element_Row) {
                $this->addElement($spec);
                //Debug::e("tbody element: object");
                continue;
            }
            if (is_array($spec)) {
                $argc = count($spec);
                ///Debug::e("tbody element: array - argc: $argc");
                $type = "row";
                $options = array();
                if (isset($spec['type'])) {
                    $type = $spec['type'];
                    if (isset($spec['options'])) {
                        $options = $spec['options'];
                    } elseif (isset($spec['elements'])) {
                        $options = array("elements" => $spec['elements']);
                    }
                } elseif (isset($spec['options'])) {
                    $options = $spec['options'];
                } elseif (isset($spec['elements'])) {
                    $options = array("elements" => $spec['elements']);
                } else {
                    if (0 == $argc) {
                        continue;
                    }
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
                if (!empty($options["elements"])) {
                    $this->addElement($type, $options);
                }
                continue;
            }
                //Debug::e("tbody element: none");
        }
        return $this;
    }
}
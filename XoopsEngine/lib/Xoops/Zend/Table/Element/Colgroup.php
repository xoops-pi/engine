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

class Xoops_Zend_Table_Element_Colgroup extends Xoops_Zend_Table_Element
{
    /**
     * Element primary decorator
     * @var string
     */
    protected $_decorator = "Colgroup";

    /**
     * Element type
     * @var string
     */
    //protected $_type = "colgroup";

    /**
     * @var tag
     */
    //protected $_tag = "colgroup";

    // Element interaction:

    /**
     * Add a new element
     *
     * $element may be either a option array, or an object of type
     * Xoops_Zend_Table_Element_Col.
     *
     * @param  array|Xoops_Zend_Table_Element_Col $element
     * @param  array|Zend_Config $options
     * @return Xoops_Zend_Table_Element
     */
    public function addElement($element, $options = null)
    {
        if (is_array($element) && !isset($element["type"])) {
            $element["type"] = "col";
        }
        parent::addElement($element, $options);

        return $this;
    }
}
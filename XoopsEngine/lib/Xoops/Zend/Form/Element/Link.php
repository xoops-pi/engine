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

class Xoops_Zend_Form_Element_Link extends Zend_Form_Element_Xhtml
{
    /**
     * Default form view helper to use for rendering
     * @var string
     */
    public $helper = 'formLink';

    /**
     * Set element value
     *
     * @param  mixed $value
     * @return Zend_Form_Element
     */
    public function setValue($value)
    {
        if (is_array($value)) {
            list($href, $title) = $value;
        } elseif (false !== strpos($value, " ")) {
            list($href, $title) = explode(" ", $value, 2);
        } else {
            list($href, $title) = array($value, "");
        }
        $this->_value = array($href, $title);
        return $this;
    }

    public function getValue()
    {
        $value = $this->_value[0];
        if (!empty($this->_value[1])) {
            $value .= " " . $this->_value[1];
        }

        return $value;
    }
}
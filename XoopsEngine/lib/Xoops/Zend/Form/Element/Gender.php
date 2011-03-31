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

class Xoops_Zend_Form_Element_Gender extends Zend_Form_Element_Radio
{
    /**
     * Constructor
     *
     * @param  string|array|Zend_Config $spec Element name or configuration
     * @param  string|array|Zend_Config $options Element value or configuration
     * @return void
     */
    public function __construct($spec, $options = null)
    {
        if (!isset($options["multiOptions"])) {
            $options["multiOptions"] = array(
                "male"      => "Male",
                "female"    => "Female",
            );
        }
        if (!isset($options["separator"])) {
            $options["separator"] = " ";
        }
        parent::__construct($spec, $options);
        $this->setServiceOptions();
    }

    protected function setServiceOptions()
    {
        $gender = array(
            "male"      => "Male",
            "female"    => "Female",
        );
        $this->setMultiOptions($gender);
    }
}

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

class Xoops_Zend_Table_Element_Caption extends Xoops_Zend_Table_Element
{
    /**
     * Element primary decorator
     * @var string
     */
    protected $_decorator = "Caption";

    /**
     * Content
     * @var string|object
     */
    protected $_content = "";

    /**
     * Element type
     * @var string
     */
    //protected $_type = "caption";

    /**
     * @var tag
     */
    //protected $_tag = "caption";

    /**
     * Constructor
     *
     * $spec may be:
     * - array: options with which to configure element
     * - Zend_Config: Zend_Config with options for configuring element
     *
     * @param  array|Zend_Config $spec
     * @param  array $options
     * @return void
     */
    public function __construct($spec = null, $options = array())
    {
        if (is_string($spec)) {
            $this->setContent($spec);
            if (is_array($options)) {
                $this->setOptions($options);
            } elseif ($options instanceof Zend_Config) {
                $this->setConfig($options);
            }
        } elseif (is_array($spec)) {
            $this->setOptions($spec);
        } elseif ($spec instanceof Zend_Config) {
            $this->setConfig($spec);
        }

        /*
        if (!empty($elements)) {
            $this->addElements($elements);
        }
        */

        /**
         * Extensions
         */
        $this->init();

        /**
         * Register ViewHelper decorator by default
         */
        $this->loadDefaultDecorators();
    }

    // Element interaction:

    /**
     * Add a new element
     *
     * $element may be either a string element type, or an object of type
     * Xoops_Zend_Table_Element. If a string element type is provided, $name must be
     * provided, and $options may be optionally provided for configuring the
     * element.
     *
     * If a Xoops_Zend_Table_Element is provided, $options may be optionally provided,
     * and any provided $options will be ignored.
     *
     * @param  string|Xoops_Zend_Table_Element $element
     * @param  string $name
     * @param  array|Zend_Config $options
     * @return Xoops_Table
     */
    public function addElement($element, $options = null)
    {
        return $this;
    }

    /**
     * Set content
     *
     * $content may be either a string, or an object.
     *
     * @param  string $content
     * @return Xoops_Zend_Table_Element
     */
    public function setContent($content)
    {
        $this->_content = $content;
        return $this;
    }

    public function getContent()
    {
        return $this->_content;
    }
}
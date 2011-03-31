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

class Xoops_Zend_Table_Element_Cell extends Xoops_Zend_Table_Element
{
    /**
     * Element primary decorator
     * @var string
     */
    protected $_decorator = "Cell";

    /**
     * Element type
     * @var string
     */
    //protected $_type = "cell";

    /**
     * @var tag
     */
    //protected $_tag = "td";

    /**
     * Content
     * @var string|object
     */
    protected $_content = "";

    /**
     * Constructor
     *
     * $spec may be:
     * - string: content
     * - array: options with which to configure content object
     * - Zend_Config: Zend_Config with options for configuring content object
     *
     * @param  array|Zend_Config $spec
     * @param  array $options
     * @return void
     */
    public function __construct($spec = null, $options = array())
    {
        if (is_string($spec) || is_object($spec)) {
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
     * Set content
     *
     * $content may be either a string, or an object.
     *
     * @param  string|object $content
     * @return Xoops_Zend_Table_Element_Element
     */
    public function setContent($content)
    {
        /*
        if ($content instanceof Zend_Form_Element) {
            $decorators = $content->getDecorators();
            if (!isset($decorators['ViewHelper']) && false !== $content->helper) {
                $content->addDecorator('ViewHelper');
            }
            $content->removeDecorator('Errors');
            $content->removeDecorator('Description');
            if ($label = $content->getLabel() || $content->isRequired()) {
                if (!isset($decorators['Label'])) {
                    $content->addDecorator('Label', array('tag' => 'span'));
                }
            } else {
                $content->removeDecorator('Label');
            }
            $content->addDecorator('CompoundElement', array("root" => $this->getRoot()));
            //$element = $this->getRoot()->createElement("cell", $content, $options);
            //$options = null;
        }
        */
        $this->_content = $content;
        return $this;
    }

    public function getContent()
    {
        return $this->_content;
    }

    public function addElement($element, $options = null)
    {
        return $this;
    }
}
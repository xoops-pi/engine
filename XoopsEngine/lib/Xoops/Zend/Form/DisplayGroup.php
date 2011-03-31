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

/**
 * XOOPS form compound
 */

class Xoops_Zend_Form_DisplayGroup extends Zend_Form_DisplayGroup
{
    /**
     * Display mode, compound or group
     * @var string
     */
    //protected $_mode = "";

    /**
     * Root object of hierarchical elements
     * @var string
     */
    //protected $_root;

    /**
     * Element label
     * @var string
     */
    //protected $_label;

    /**
     * Formatted validation error messages
     * @var array
     */
    //protected $_messages = array();

    /**
     * Required flag
     * @var bool
     */
    //protected $_required = false;

    public function ____setRoot($root)
    {
        if ($root != $this->_root) {
            $this->_root = $root;
            foreach ($this->_elements as $key => $element) {
                if ($element instanceof Zend_Form_Element) {
                    $element->removeDecorator('CompoundElement');
                    $element->addDecorator('CompoundElement', array("root" => $this->_root));
                } elseif ($element instanceof Xoops_Zend_Form_DisplayGroup) {
                    $element->setRoot($this->_root);
                }
            }
        }
        return $this;
    }

    public function ____getRoot()
    {
        if (is_null($this->_root)) {
            $this->_root = $this;
        }

        return $this->_root;
    }

    /**
     * Set element label
     *
     * @param  string $label
     * @return Zend_Form_Element
     */
    public function ____setLabel($label)
    {
        return $this->setLegend($label);
    }

    /**
     * Retrieve element label
     *
     * @return string
     */
    public function ____getLabel()
    {
        return $this->getLegend();
    }

    /**
     * Set required flag
     *
     * @param  bool $flag Default value is true
     * @return Zend_Form_Element
     */
    public function ____setRequired($flag = true)
    {
        $this->_required = (bool) $flag;
        return $this;
    }

    /**
     * Is the element required?
     *
     * @return bool
     */
    public function ____isRequired()
    {
        return $this->_required;
    }

    /**
     * Retrieve error messages
     *
     * @return array
     */
    public function ____getMessages()
    {
        return $this->_messages;
    }

    public function ____addMessages(array $messages)
    {
        $this->_messages = array_merge($this->_messages, $messages);
        return $this;
    }

    /**
     * Set mode
     *
     * @param  string $mode
     * @return Zend_Form_DisplayGroup
     */
    public function ____setMode($mode = "")
    {
        $this->_mode = $mode;
        return $this;
    }

    /**
     * get mode
     *
     * @return string
     */
    public function ____getMode()
    {
        return $this->_mode;
    }

    /**
     * Load default decorators
     *
     * @return void
     */
    public function ____loadDefaultDecorators()
    {
        if ($this->loadDefaultDecoratorsIsDisabled()) {
            return;
        }

        if ($this->getMode() == "compound") {
            $this->loadCompoundDecorators();
            return;
        }

        parent::loadDefaultDecorators();
        return;

        $decorators = $this->getDecorators();
        if (empty($decorators)) {
            $this->addDecorator('FormElements')
                 ->addDecorator('HtmlTag', array('tag' => 'dl'))
                 ->addDecorator('Fieldset')
                 ->addDecorator('DtDdWrapper');
        }
    }

    /**
     * Load default compound decorators
     *
     * @return void
     */
    public function ____loadCompoundDecorators()
    {
        $this->addDecorator('GroupElements')
            ->addDecorator('ViewHelper')
            ->addDecorator('Errors')
            ->addDecorator('Description', array('tag' => 'p', 'class' => 'description'))
            ->addDecorator('HtmlTag', array('tag' => 'dd',
                                            'id'  => $this->getId()))
            ->addDecorator('Label', array('tag' => 'dt'));
    }

    /**
     * Add element to stack
     *
     * @param  Zend_Form_Element $element
     * @return Zend_Form_DisplayGroup
     */
    public function ____addElement(Zend_Form_Element $element)
    {
        parent::addElement($element);

        if ($this->getMode() == "compound") {
            $decorators = $element->getDecorators();
            if (!isset($decorators['ViewHelper'])) {
                $element->addDecorator('ViewHelper');
            }
            $element->removeDecorator('Errors');

            if (!isset($decorators['HtmlTag'])) {
                $element->addDecorator('HtmlTag', array('tag' => 'span',
                                                'class' => 'content',
                                                'id'    => $element->getId()));
            }
            if ($label = $element->getLabel() || $element->isRequired()) {
                if (!isset($decorators['Label'])) {
                    $element->addDecorator('Label', array('tag' => 'span', 'class' => 'label'));
                }
            }

            // CompoundElement will collect element errors, descriptions to compund, and render proper htmltag
            $element->addDecorator('CompoundElement', array("root" => $this->getRoot()));
        }

        //Debug::e($this->getMode());
        //Debug::e(array_keys($element->getDecorators()));
        return $this;
    }

    /**
     * Add compound to stack
     *
     * @param  Zend_Form_Compound $element
     * @return Zend_Form_DisplayGroup
     */
    public function ____addCompound(Xoops_Zend_Form_DisplayGroup $element)
    {
        $this->_elements[$element->getName()] = $element;
        $this->_groupUpdated = true;

        $decorators = $element->getDecorators();
        if (!isset($decorators['ViewHelper'])) {
            $element->addDecorator('ViewHelper');
        }
        $element->removeDecorator('Errors');

        if (!isset($decorators['HtmlTag'])) {
            $element->addDecorator('HtmlTag', array('tag' => 'span',
                                            'class' => 'content',
                                            'id'    => $element->getId()));
        }
        if ($label = $this->getLabel()) {
            if (!isset($decorators['Label'])) {
                $element->addDecorator('Label', array('tag' => 'span', 'class' => 'label'));
            }
        }

        // CompoundElement will collect element errors, descriptions to compund, and render proper htmltag
        $element->setRoot($this->getRoot());
        return $this;
    }

    /**
     * Add multiple elements at once
     *
     * @param  array $elements
     * @return Xoops_Zend_Form_Compound
     * @throws Zend_Form_Exception if any element is not a Zend_Form_Element
     */
    public function ____addElements(array $elements)
    {
        foreach ($elements as $element) {
            if ($element instanceof Zend_Form_Element) {
                $this->addElement($element);
            } elseif ($element instanceof Xoops_Zend_Form_DisplayGroup) {
                $this->addCompound($element);
            } else {
                require_once 'Zend/Form/Exception.php';
                throw new Zend_Form_Exception('elements passed via array to addElements() must be Zend_Form_Element or Xoops_Zend_Form_Compound only');
            }
        }
        return $this;
    }
}
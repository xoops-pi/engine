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

class Xoops_Zend_Form_Element_Table extends Zend_Form_Element
{
    /**
     * @var Xoops_Table
     */
    protected $table;

    /**
     * Form object the current element belongs to
     * @var string
     */
    protected $_form;

    /**
     * Root object of hierarchical elements
     * @var string
     */
    protected $_root;

    /**
     * Default view helper to use
     * @var string
     */
    public $helper = false;

    /**
     * Array to which elements belong (if any)
     * @var string
     */
    protected $_elementsBelongTo;

    /**
     * Element type
     * @var string
     */
    protected $_type = "table";

    /**
     * Elements
     * @var array
     */
    protected $_elements = array();

    /**
     * Ignore flag (used when retrieving values at form level)
     * @var bool
     */
    protected $_ignore = true;

    /**
     * Constructor
     *
     * $spec may be:
     * - string: name of element
     * - array: options with which to configure element
     * - Zend_Config: Zend_Config with options for configuring element
     *
     * @param  string|array|Zend_Config $spec
     * @param  array|Zend_Config $options
     * @return void
     * @throws Zend_Form_Exception if no element name after initialization
     */
    public function __construct($spec, $options = null)
    {
        parent::__construct($spec, $options);
        if (!$this->table) {
            $this->setTable(array());
        }
    }

    /**
     * @param array|Xoops_Table $table
     */
    public function setTable($table)
    {
        if (!$table instanceof Xoops_Table) {
            $table = new Xoops_Table((array) $table);
        }
        $this->table = $table;
        return $this;
    }

    public function getTable()
    {
        return $this->table;
    }

    /**
     * Set name of array elements belong to
     *
     * @param  string $array
     * @return Zend_Form
     */
    public function setElementsBelongTo($array)
    {
        $origName = $this->getElementsBelongTo();
        $name = $this->filterName($array, true);
        if (empty($name)) {
            $name = null;
        }
        $this->_elementsBelongTo = $name;

        if (null === $name) {
            $this->setIsArray(false);
            if (null !== $origName) {
                $this->_setElementsBelongTo();
            }
        } else {
            $this->setIsArray(true);
            $this->_setElementsBelongTo();
        }

        return $this;
    }

    /**
     * Set array to which elements belong
     *
     * @param  string $name Element name
     * @return void
     */
    protected function _setElementsBelongTo($name = null)
    {
        $array = $this->getElementsBelongTo();

        if (null === $array) {
            return;
        }

        if (null === $name) {
            foreach ($this->getElements() as $element) {
                $element->setBelongsTo($array);
            }
        } else {
            if (null !== ($element = $this->getElement($name))) {
                $element->setBelongsTo($array);
            }
        }
    }

    /**
     * Get name of array elements belong to
     *
     * @return string|null
     */
    public function getElementsBelongTo()
    {
        return $this->_elementsBelongTo;
    }

    /**
     * Create an element
     *
     * Acts as a factory for creating elements. Elements created with this
     * method will not be attached to the form, but will contain element
     * settings as specified in the form object (including plugin loader
     * prefix paths, default decorators, etc.).
     *
     * @param  string $type
     * @param  string $name
     * @param  array|Zend_Config $options
     * @param  string $belongsTo
     * @return Xoops_Zend_Form_Element_Compound
     */
    public function createElement($type, $name, $options = null, $belongsTo = null)
    {
        $element = $this->_form->createElement($type, $name, $options);
        if (!empty($belongsTo)) {
            $element->setBelongsTo($belongsTo);
        }
        $this->registerElement($element);
        return $element;
    }

    /**
     * Register an element to form but not add to display order
     *
     * @param  Zend_Form_Element $element
     * @return Zend_Form
     */
    public function registerElement(Zend_Form_Element $element)
    {
        if ($belongsTo = $element->getBelongsTo()) {
            if (!isset($this->_elements[$belongsTo])) {
                $compound = $this->_form->createElement("compound", $belongsTo);
                $this->_form->registerElement($compound);
                $this->_elements[$belongsTo] = $compound;
            }
            $this->_elements[$belongsTo]->addElement($element);
        } else {
            $this->_form->registerElement($element);
            $name = $element->getName();
            $this->_elements[$name] = $element;
        }

        $decorators = $element->getDecorators();
        if (!isset($decorators['ViewHelper']) && false !== $element->helper) {
            $element->addDecorator('ViewHelper');
        }
        $element->removeDecorator('Errors');
        $element->removeDecorator('Description');
        if ($label = $element->getLabel() || $element->isRequired()) {
            if (!isset($decorators['Label'])) {
                $element->addDecorator('Label', array('tag' => 'span'));
            }
        } else {
            $element->removeDecorator('Label');
        }
        $element->addDecorator('CompoundElement', array("root" => $this->getRoot()));

        return $this;
    }

    /**
     * Add multiple elements at once
     *
     * @param  array $elements
     * @return Zend_Form_Element_Table
     * @throws Zend_Form_Exception if any element is not a Zend_Form_Element
     */
    public function registerElements(array $elements)
    {
        foreach ($elements as $element) {
            if (!$element instanceof Zend_Form_Element) {
                require_once 'Zend/Form/Exception.php';
                throw new Zend_Form_Exception('elements passed via array to addElements() must be Zend_Form_Elements only');
            }
            $this->registerElement($element);
        }
        return $this;
    }

    /**
     * Set multiple elements at once (overwrites)
     *
     * @param  array $elements
     * @return Zend_Form_DisplayGroup
     */
    public function setElements(array $elements)
    {
        $this->clearElements();
        return $this->registerElements($elements);
    }

    /**
     * Retrieve element
     *
     * @param  string $name
     * @return Zend_Form_Element|null
     */
    public function getElement($name)
    {
        $name = (string) $name;
        if (isset($this->_elements[$name])) {
            return $this->_elements[$name];
        }

        return null;
    }

    /**
     * Retrieve elements
     * @return array
     */
    public function getElements()
    {
        return $this->_elements;
    }

    /**
     * Remove a single element
     *
     * @param  string $name
     * @return boolean
     */
    public function removeElement($name)
    {
        $name = (string) $name;
        if (array_key_exists($name, $this->_elements)) {
            unset($this->_elements[$name]);
            return true;
        }

        return false;
    }

    /**
     * Remove all elements
     *
     * @return Zend_Form_DisplayGroup
     */
    public function clearElements()
    {
        $this->_elements = array();
        return $this;
    }


    /**
     * Load default decorators
     *
     * @return void
     */
    public function loadDefaultDecorators()
    {
        if ($this->loadDefaultDecoratorsIsDisabled()) {
            return;
        }

        $decorators = $this->getDecorators();
        if (empty($decorators)) {
            $this->addDecorator('Table')
                ->addDecorator('Errors')
                ->addDecorator('Description', array('tag' => 'p', 'class' => 'description'))
                ->addDecorator('HtmlTag', array('tag' => 'dd',
                                                'id'  => $this->getName() . '-element'))
                ->addDecorator('Label', array('tag' => 'dt'));
        }
    }

    /**
     * Render form element
     *
     * @param  Zend_View_Interface $view
     * @return string
     */
    public function render(Zend_View_Interface $view = null)
    {
        $this->removeDecorator("ViewHelper");
        return parent::render($view);
    }

    public function setRoot($root)
    {
        if ($root != $this->_root) {
            $this->_root = $root;
            foreach ($this->_elements as $key => $element) {
                if ($element->getType() == "compound") {
                    $element->setRoot($this->_root);
                } elseif ($element instanceof Zend_Form_Element) {
                    $element->removeDecorator('CompoundElement');
                    $element->addDecorator('CompoundElement', array("root" => $this->_root));
                }
            }
        }
        return $this;
    }

    public function getRoot()
    {
        if (is_null($this->_root)) {
            $this->_root = $this;
        }

        return $this->_root;
    }

    public function setForm($form)
    {
        if ($form != $this->_form) {
            $this->_form = $form;
            foreach ($this->_elements as $key => $element) {
                if (method_exists($element, "setForm")) {
                    $element->setForm($this->_form);
                }
                $this->registerForm($element);
            }
        }
        return $this;
    }

    public function getForm()
    {
        return $this->_form;
    }

    public function registerForm(Zend_Form_Element $element)
    {
        if ($this->_form instanceof Xoops_Zend_Form) {
            $this->_form->registerElement($element);
        }
        return $this;
    }

    /**
     * Set values for elements
     *
     * @param  array $value
     * @return Xoops_Zend_Form_Element_Compound
     */
    public function setValue($value)
    {
        return $this;
    }

    /**
     * Retrieve element values
     *
     * @return array
     */
    public function getValue()
    {
        return null;
    }

}
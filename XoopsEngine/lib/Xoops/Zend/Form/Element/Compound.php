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

class Xoops_Zend_Form_Element_Compound extends Zend_Form_Element implements Iterator, Countable
{
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
    protected $_type = "compound";

    /**
     * Element order
     * @var array
     */
    protected $_elementOrder = array();

    /**
     * Elements
     * @var array
     */
    protected $_elements = array();

    /**
     * Whether or not a new element has been added to the compound
     * @var bool
     */
    protected $_compoundUpdated = false;

    /**
     * Set name of array elements belong to
     *
     * @param  string $array
     * @return Zend_Form
     */
    public function setElementsBelongTo($array)
    {
        $origName = $this->getElementsBelongTo();
        if (false === $array) {
            $this->setIgnore(true);
        } else {
            $array = $this->filterName($array, true);
            if (empty($array)) {
                $array = null;
            }
        }
        $this->_elementsBelongTo = $array;

        if (null === $array) {
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
        //if ((null === $this->_elementsBelongTo) && $this->isArray()) {
        if ((null === $this->_elementsBelongTo)) {
            $name = $this->getName();
            if (!empty($name)) {
                return $name;
            }
        }
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
     * @return Xoops_Zend_Form_Element_Compound
     */
    public function createElement($type, $name, $options = null)
    {
        return $this->_form->createElement($type, $name, $options);
    }

    /**
     * Add an element to stack
     *
     * $element may be either a string element type, or an object of type
     * Zend_Form_Element. If a string element type is provided, $name must be
     * provided, and $options may be optionally provided for configuring the
     * element.
     *
     * If a Zend_Form_Element is provided, $name may be optionally provided,
     * and any provided $options will be ignored.
     *
     * @param  string|Zend_Form_Element $element
     * @param  string $name
     * @param  array|Zend_Config $options
     * @return Xoops_Zend_Form_Compound
     */
    public function addElement($element, $name = null, $options = null)
    {
        if (is_string($element)) {
            //Debug::e(__METHOD__ . ": {$element}-{$name}");
            //Debug::e($options);
            if (!isset($options["disableLoadDefaultDecorators"])) {
            //Debug::e("loadDefaultDecorators set to disabled");
                $options["disableLoadDefaultDecorators"] = true;
            }
            $element = $this->createElement($element, $name, $options);
        } else {
            //Debug::e(__METHOD__ . ": " . $name);
        }
        $decorators = $element->getDecorators();
        if (!isset($decorators['ViewHelper']) && false !== $element->helper) {
            $element->addDecorator('ViewHelper');
        }
        $element->removeDecorator('Errors');
        $element->removeDecorator('Description');

        if (!isset($decorators['HtmlTag'])) {
            $element->addDecorator('HtmlTag', array('tag' => 'span',
                                            'class' => 'content',
                                            'id'    => $element->getId()));
        }
        if ($label = $element->getLabel() || $element->isRequired()) {
            if (!isset($decorators['Label'])) {
                $element->addDecorator('Label', array('tag' => 'span'));
            }
        } else {
            $element->removeDecorator('Label');
        }

        // CompoundElement will collect element errors, descriptions to compund, and render proper htmltag
        if (method_exists($element, "setRoot")) {
            $element->setRoot($this->getRoot());
        }
        $element->addDecorator('CompoundElement', array("root" => $this->getRoot()));

        $this->_elements[$element->getName()] = $element;
        $this->_compoundUpdated = true;
        if (false === $this->getElementsBelongTo() || false === $element->getBelongsTo()) {
            $this->registerForm($element);
        } else {
            $this->_setElementsBelongTo($name);
        }

        return $this;
    }

    /**
     * Add multiple elements at once
     *
     * @param  array $elements
     * @return Zend_Form_Element_Compound
     * @throws Zend_Form_Exception if any element is not a Zend_Form_Element
     */
    public function addElements(array $elements)
    {
        foreach ($elements as $element) {
            if (!$element instanceof Zend_Form_Element) {
                throw new Zend_Form_Exception('elements passed via array to addElements() must be Zend_Form_Elements only');
            }
            $this->addElement($element);
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
        return $this->addElements($elements);
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
            $this->_compoundUpdated = true;
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
        $this->_compoundUpdated = true;
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
            $this->addDecorator('CompoundElements')
                //->addDecorator('ViewHelper')
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

    // Interfaces: Iterator, Countable

    /**
     * Current element
     *
     * @return Zend_Form_Element
     */
    public function current()
    {
        $this->_sort();
        current($this->_elementOrder);
        $key = key($this->_elementOrder);
        return $this->getElement($key);
    }

    /**
     * Current element
     *
     * @return string
     */
    public function key()
    {
        $this->_sort();
        return key($this->_elementOrder);
    }

    /**
     * Move pointer to next element
     *
     * @return void
     */
    public function next()
    {
        $this->_sort();
        next($this->_elementOrder);
    }

    /**
     * Move pointer to beginning of element loop
     *
     * @return void
     */
    public function rewind()
    {
        $this->_sort();
        reset($this->_elementOrder);
    }

    /**
     * Determine if current element/subform/display group is valid
     *
     * @return bool
     */
    public function valid()
    {
        $this->_sort();
        return (current($this->_elementOrder) !== false);
    }

    /**
     * Count of elements/subforms that are iterable
     *
     * @return int
     */
    public function count()
    {
        return count($this->_elements);
    }

    /**
     * Sort items according to their order
     *
     * @return void
     */
    protected function _sort()
    {
        if ($this->_compoundUpdated || !is_array($this->_elementOrder)) {
            $elementOrder = array();
            foreach ($this->getElements() as $key => $element) {
                $elementOrder[$key] = $element->getOrder();
            }

            $items = array();
            $index = 0;
            foreach ($elementOrder as $key => $order) {
                if (null === $order) {
                    while (array_search($index, $elementOrder, true)) {
                        ++$index;
                    }
                    $items[$index] = $key;
                    ++$index;
                } else {
                    $items[$order] = $key;
                }
            }

            $items = array_flip($items);
            asort($items);
            $this->_elementOrder = $items;
            $this->_compoundUpdated = false;
        }
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
        $value = (array) $value;
        foreach ($value as $name => $val) {
            if ($element = $this->getElement($name)) {
                //Debug::e($this->getname().":$name:$val:".get_class($element));
                $element->setValue($val);
            }
        }
        return $this;
    }

    /**
     * Retrieve element values
     *
     * @param  bool $suppressArrayNotation
     * @return array
     */
    public function getValue($suppressArrayNotation = false)
    {
        $values = array();
        foreach ($this->getElements() as $key => $element) {
            if (!$element->getIgnore()) {
                $values[$key] = $element->getValue();
            }
        }

        /*
        if (!$suppressArrayNotation) {
            $values = $this->_attachToArray($values, $this->getElementsBelongTo());
        }
        */

        return $values;
    }

    /**
     * Retrieve unfiltered element values
     *
     * @return array
     */
    public function getUnfilteredValue()
    {
        $values = array();
        foreach ($this->getElements() as $key => $element) {
            $values[$key] = $element->getUnfilteredValue();
        }

        return $values;
    }

    /**
     * Set all elements' filters
     *
     * @param  array $filters
     * @return Zend_Form
     */
    public function setFilters(array $filters)
    {
        foreach ($this->getElements() as $element) {
            $element->setFilters($filters);
        }
        return $this;
    }

    /**
     * Validate element value
     *
     * If a translation adapter is registered, any error messages will be
     * translated according to the current locale, using the given error code;
     * if no matching translation is found, the original message will be
     * utilized.
     *
     * Note: The *filtered* value is validated.
     *
     * @param  mixed $value
     * @param  mixed $context
     * @return boolean
     */
    public function isValid($value, $context = null)
    {
        $result = true;
        $value = (array) $value;
        foreach ($value as $name => $val) {
            if ($element = $this->getElement($name)) {
                $result = $element->isValid($val, $context) && $result;
            }
        }
        return $result;
    }

    /**
     * Converts given arrayPath to an array and attaches given value at the end of it.
     *
     * @param  mixed $value The value to attach
     * @param  string $arrayPath Given array path to convert and attach to.
     * @return array
     */
    protected function _attachToArray($value, $arrayPath)
    {
        // As long as we have more levels
        while ($arrayPos = strrpos($arrayPath, '[')) {
            // Get the next key in the path
            $arrayKey = trim(substr($arrayPath, $arrayPos + 1), ']');

            // Attach
            $value = array($arrayKey => $value);

            // Set the next search point in the path
            $arrayPath = trim(substr($arrayPath, 0, $arrayPos), ']');
        }

        $value = array($arrayPath => $value);

        return $value;
    }
}
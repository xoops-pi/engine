<?php
/**
 * Form handler for Xoops Engine
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Xoops Engine http://www.xoopsengine.org/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @since           3.0
 * @package         Xoops_Zend
 * @version         $Id$
 */

class Xoops_Zend_Form extends Zend_Form
{
    /**
     * Default display group class
     * @var string
     */
    protected $_defaultDisplayGroupClass = 'Xoops_Zend_Form_DisplayGroup';

    /**
     * Constructor
     *
     * Registers form view helper as decorator
     *
     * @param mixed $options
     * @return void
     */
    public function __construct($options = null)
    {
        $this->setOptions($this->loadDefaultOptions());
        parent::__construct($options);
        $this->build();
    }

    protected function loadDefaultOptions()
    {
        $default_options = array(
            'prefixPath'    => array(
                'prefix'    => 'Xoops_Zend_Form',
                'path'      => 'Xoops/Zend/Form',
            ),
            'displayGroupPrefixPath'    => array(
                "Xoops_Zend_Form"      => 'Xoops/Zend/Form',
            ),
        );
        return $default_options;
    }

    /**
     * Build elements if available
     *
     * @return Xoops_Zend_Form
     */
    protected function build()
    {
        return $this;
    }

    /**
     * Retrieve translator object
     *
     * @return Zend_Translate|null
     */
    public function getTranslator()
    {
        if ($this->translatorIsDisabled()) {
            return null;
        }

        if (null === $this->_translator) {
            return static::getDefaultTranslator();
        }

        return $this->_translator;
    }

    /**
     * Get global default translator object
     *
     * @return null|Zend_Translate
     */
    public static function getDefaultTranslator()
    {
        if (null === static::$_translatorDefault) {
            $translator = XOOPS::service("translate")->getAdapter();
            if ($translator instanceof Zend_Translate_Adapter) {
                return $translator;
            } elseif ($translator instanceof Zend_Translate) {
                return $translator->getAdapter();
            }
        }
        return static::$_translatorDefault;
    }

    /**
     * Load the default decorators
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
            $this->addDecorator('FormElements')
                ->addDecorator('HtmlTag', array('tag' => 'dl', 'class' => 'xoops-form'))
                ->addDecorator('Description', array('placement' => 'prepend'))
                ->addDecorator('Form');
        }
    }

    /**
     * Render form and assign content to template
     *
     * @param  Zend_View_Interface $view
     * @return Zend_Form
     */
    public function assign(Zend_View_Interface $view = null)
    {
        $content = (string) $this->render($view);
        $view->getEngine()->assign($this->getName(), $content);
        return $this;
    }

    /**
     * Create an element
     *
     * Acts as a factory for creating elements. Elements created with this
     * method will not be attached to the form, but will contain element
     * settings as specified in the form object (including plugin loader
     * prefix paths, default decorators, etc.).
     *
     * @param  string|array $type    type of the element, string for a global element, array($module, $type) for a module specific element
     * @param  string $name
     * @param  array|Zend_Config|string $options
     * @return Zend_Form_Element
     */
    public function createElement($type, $name, $options = null)
    {
        if (null !== $options && !is_array($options) && !($options instanceof Zend_Config)) {
            $options = array('value' => $options);
        }
        if (is_array($type)) {
            list($module, $eleType) = $type;
            $class = ('app' == Xoops::service('module')->getType($module) ? 'App' : 'Module') . "_" . ucfirst($module) . "_Form_Element_" . ucfirst($eleType);
            $element = new $class($name, $options);
        } else {
            $element = parent::createElement($type, $name, $options);
        }
        $element->addPrefixPath("Xoops_Zend_Validate", "Xoops/Zend/Validate", "validate");
        if (method_exists($element, "setForm")) {
            $element->setForm($this);
        }

        return $element;
    }

    /**
     * Add a new element
     *
     * $element may be either a string element type, or an object of type
     * Zend_Form_Element. If a string element type is provided, $name must be
     * provided, and $options may be optionally provided for configuring the
     * element.
     *
     * If a Zend_Form_Element is provided, $name may be optionally provided,
     * and any provided $options will be ignored.
     *
     * @param  string|Zend_Form_Element|array $element
     * @param  string $name
     * @param  array|Zend_Config|string $options
     * @return Zend_Form
     */
    public function addElement($element, $name = null, $options = null)
    {
        if (!($element instanceof Zend_Form_Element)) {
            $element = $this->createElement($element, $name, $options);
        } else {
            if (empty($name) && $element instanceof Zend_Form_Element) {
                $name = $element->getName();
            }
            if (null !== $options && !is_array($options) && !($options instanceof Zend_Config)) {
                $options = array('value' => $options);
            }
            if (method_exists($element, "setForm")) {
                $element->setForm($this);
            }
        }
        parent::addElement($element, $name, $options);
        /*
        $element = $this->_elements[$name];
        if (method_exists($element, "setForm")) {
            $element->setForm($this);
        }
        */
        /*
        if (!$element->translatorIsDisabled() && !$element->hasTranslator()) {
            $element->setTranslator($this->setTranslator());
        }
        */
        return $this;
    }

    /**
     * Remove an element from form's display order
     *
     * @param  Zend_Form_Element $element
     * @return Zend_Form
     */
    public function hideElement(Zend_Form_Element $element)
    {
        if (array_key_exists($element->getName(), $this->_order)) {
            unset($this->_order[$element->getName()]);
            $this->_orderUpdated = true;
        }
        return $this;
    }

    /**
     * Add an element to form's display order
     *
     * @param  Zend_Form_Element $element
     * @return Zend_Form
     */
    public function displayElement(Zend_Form_Element $element)
    {
        $name = $element->getName();
        if (!array_key_exists($name, $this->_order)) {
            /*
            if (!isset($this->_elements[$name])) {
                $this->addElement($element);
            } else {
            */
                $this->_order[$name] = $this->_elements[$name]->getOrder();
                $this->_orderUpdated = true;
            //}
        }
        return $this;
    }

    /**
     * Register an element to form but not add to display order
     *
     * @param  Zend_Form_Element $element
     * @return Zend_Form
     */
    public function registerElement(Zend_Form_Element $element)
    {
        $name = $element->getName();
        $this->_elements[$name] = $element;
        $this->_setElementsBelongTo($name);
        return $this;
    }

    /**
     * Remove element
     *
     * @param  string $name
     * @return boolean
     */
    public function removeElement($name)
    {
        $name = (string) $name;
        if (isset($this->_elements[$name])) {
            if (method_exists($this->_elements[$name], "getElements")) {
                foreach ($this->_elements[$name]->getElements() as $element) {
                    if (isset($this->_elements[$element->getName()])) {
                        $this->displayElement($element);
                    }
                }
            }
            return parent::removeElement($name);
        }
        return false;
    }

    /**
     * Set hash element
     *
     * @param  mixed $options
     * @return Zend_Form
     */
    public function setHash($options = null)
    {
        if (false === $options) {
            return $this;
        }
        $spec = "";
        if (is_string($options)) {
            $spec = $options;
            $options = array();
        } elseif (isset($options["name"])) {
            $spec = $options["name"];
            unset($options["name"]);
        }
        $this->addElement("Hash", $spec, $options);
        return $this;
    }
}
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

class Xoops_Zend_Table_Element implements Xoops_Zend_Table_Element_Interface
{
    /**
     * Element Constants
     */
    const DECORATOR = 'DECORATOR';
    const ELEMENT = 'ELEMENT';

    /**
     * Element primary decorator
     * @var string
     */
    protected $_decorator;

    /**
     * Element decorators
     * @var array
     */
    protected $_decorators = array();

    /**
     * Should we disable loading the default decorators?
     * @var bool
     */
    protected $_disableLoadDefaultDecorators = false;

    /**
     * Order of element
     * @var int
     */
    //protected $_order;

    /**
     * Does the element represent an array?
     * @var bool
     */
    //protected $_isArray = false;

    /**
     * @var Zend_Translate
     */
    protected $_translator;

    /**
     * Is translation disabled?
     * @var bool
     */
    protected $_translatorDisabled = false;

    /**
     * Element type
     * @var string
     */
    protected $_type;

    /**
     * @var Zend_View_Interface
     */
    protected $_view;

    /**
     * Is a specific decorator being rendered via the magic renderDecorator()?
     *
     * This is to allow execution of logic inside the render() methods of child
     * elements during the magic call while skipping the parent render() method.
     *
     * @var bool
     */
    protected $_isPartialRendering = false;

    /**
     * Table elements
     * @var array
     */
    protected $_elements = array();

    /**
     * Plugin loaders for filter and validator chains
     * @var array
     */
    protected $_loaders = array();

    /**
     * @var Xoops_Table
     */
    protected $_root;

    /**
     * @var tag
     */
    protected $_tag;

    /**
     * Constructor
     *
     * $spec may be:
     * - array: options with which to configure element
     * - Zend_Config: Zend_Config with options for configuring element
     *
     * @param  array|Zend_Config $spec
     * @return void
     */
    public function __construct($spec = null)
    {
        if (is_array($spec)) {
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

    /**
     * Initialize object; used by extending classes
     *
     * @return void
     */
    public function init()
    {
    }

    /**
     * Set flag to disable loading default decorators
     *
     * @param  bool $flag
     * @return Zend_Form_Element
     */
    public function setDisableLoadDefaultDecorators($flag)
    {
        $this->_disableLoadDefaultDecorators = (bool) $flag;
        return $this;
    }

    /**
     * Should we load the default decorators?
     *
     * @return bool
     */
    public function loadDefaultDecoratorsIsDisabled()
    {
        return $this->_disableLoadDefaultDecorators;
    }

    /**
     * Load default decorators
     *
     * @return Zend_Form_Element
     */
    public function loadDefaultDecorators()
    {
        if ($this->loadDefaultDecoratorsIsDisabled()) {
            return $this;
        }

        $decorators = $this->getDecorators();
        if (empty($decorators)) {
            $decorators = $this->getDefaultDecorators();
            foreach ($decorators as $name => $options) {
                $this->addDecorator($name, $options);
            }
            if ($this->_decorator) {
                $this->addDecorator($this->_decorator);
            }
            if ($this->_tag) {
                $this->addDecorator("HtmlTag", array("tag" => $this->_tag));
            }
            /*
            $this->addDecorator('ViewHelper')
                ->addDecorator($this->getDecorator("HtmlTag", array("tag" => $this->_tag)));
            */
        }
        return $this;
    }

    public function getDefaultDecorators()
    {
        return array();
    }

    /**
     * Set object state from options array
     *
     * @param  array $options
     * @return Zend_Form_Element
     */
    public function setOptions(array $options)
    {
        if (isset($options['prefixPath'])) {
            $this->addPrefixPaths($options['prefixPath']);
            unset($options['prefixPath']);
        }

        if (isset($options['disableTranslator'])) {
            $this->setDisableTranslator($options['disableTranslator']);
            unset($options['disableTranslator']);
        }

        unset($options['options']);
        unset($options['config']);

        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);

            if (in_array($method, array('setTranslator', 'setPluginLoader', 'setView'))) {
                if (!is_object($value)) {
                    continue;
                }
            }

            if (method_exists($this, $method)) {
                // Setter exists; use it
                $this->$method($value);
            } else {
                // Assume it's metadata
                $this->setAttrib($key, $value);
            }
        }
        return $this;
    }

    /**
     * Set object state from Zend_Config object
     *
     * @param  Zend_Config $config
     * @return Zend_Form_Element
     */
    public function setConfig(Zend_Config $config)
    {
        return $this->setOptions($config->toArray());
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
        if ($element instanceof Xoops_Zend_Table_Element) {
            $prefixPaths              = array();
            $prefixPaths['decorator'] = $this->getPluginLoader('decorator')->getPaths();
            if (!empty($this->_elementPrefixPaths)) {
                $prefixPaths = array_merge($prefixPaths, $this->_elementPrefixPaths);
            }
            $element->addPrefixPaths($prefixPaths);
            if (!empty($options)) {
                $element->setOptions($options);
            }
        } else {
            if (is_string($element)) {
                $type = $element;
            } elseif (isset($element["type"])) {
                $type = $element["type"];
                $options = array_merge(isset($element["options"]) ? $element["options"] : array(), $options);
            } else {
                return $this;
            }
            $element = $this->createElement($type, $options);
        }

        if (is_object($element) && method_exists($element, "setRoot")) {
            $element->setRoot($this->_root);
        }
        $this->_elements[] = $element;
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
     * @param  string $type
     * @param  array|Zend_Config $options
     * @return Xoops_Zend_Table_Element
     */
    public function createElement($type, $options = null)
    {
        if (!is_string($type)) {
            throw new Xoops_Zend_Table_Exception('Element type must be a string indicating type');
        }

        $prefixPaths              = array();
        $prefixPaths['decorator'] = $this->getPluginLoader('decorator')->getPaths();
        if (!empty($this->_elementPrefixPaths)) {
            $prefixPaths = array_merge($prefixPaths, $this->_elementPrefixPaths);
        }

        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }

        if ((null === $options) || !is_array($options)) {
            $options = array('prefixPath' => $prefixPaths);
        } elseif (is_array($options)) {
            if (array_key_exists('prefixPath', $options)) {
                $options['prefixPath'] = array_merge($prefixPaths, $options['prefixPath']);
            } else {
                $options['prefixPath'] = $prefixPaths;
            }
        }

        $class = $this->getPluginLoader(self::ELEMENT)->load($type);
        $element = new $class($options);

        return $element;
    }

    /**
     * Add multiple elements at once
     *
     * @param  array $elements
     * @return Xoops_Zend_Table_Element
     */
    public function addElements(array $elements)
    {
        foreach ($elements as $spec) {
            if (is_string($spec) || ($spec instanceof Xoops_Zend_Table_Element)) {
                $this->addElement($spec);
                continue;
            }

            if (is_array($spec)) {
                $argc = count($spec);
                $options = array();
                if (isset($spec['type'])) {
                    $type = $spec['type'];
                    if (isset($spec['options'])) {
                        $options = $spec['options'];
                    }
                    $this->addElement($type, $options);
                } else {
                    switch ($argc) {
                        case 0:
                            continue;
                        case (1 <= $argc):
                            $type = array_shift($spec);
                        case (2 <= $argc):
                            $options = array_shift($spec);
                        case (3 <= $argc):
                            if (empty($options)) {
                                $options = array_shift($spec);
                            }
                        default:
                            $this->addElement($type, $options);
                    }
                }
            }
        }
        return $this;
    }

    /**
     * Set table elements (overwrites existing elements)
     *
     * @param  array $elements
     * @return Xoops_Table
     */
    public function setElements(array $elements)
    {
        $this->_elements = array();
        return $this->addElements($elements);
    }

    /**
     * Retrieve all elements
     *
     * @return array
     */
    public function getElements()
    {
        return $this->_elements;
    }

    // Localization:

    /**
     * Set translator object for localization
     *
     * @param  Zend_Translate|null $translator
     * @return Zend_Form_Element
     */
    public function setTranslator($translator = null)
    {
        if (null === $translator) {
            $this->_translator = null;
        } elseif ($translator instanceof Zend_Translate_Adapter) {
            $this->_translator = $translator;
        } elseif ($translator instanceof Zend_Translate) {
            $this->_translator = $translator->getAdapter();
        } else {
            throw new Xoops_Zend_Table_Exception('Invalid translator specified');
        }
        return $this;
    }

    /**
     * Retrieve localization translator object
     *
     * @return Zend_Translate_Adapter|null
     */
    public function getTranslator()
    {
        if ($this->translatorIsDisabled()) {
            return null;
        }

        if (null === $this->_translator) {
            return Xoops_Table::getDefaultTranslator();
        }
        return $this->_translator;
    }

    /**
     * Does this element have its own specific translator?
     *
     * @return bool
     */
    public function hasTranslator()
    {
        return (bool)$this->_translator;
    }

    /**
     * Indicate whether or not translation should be disabled
     *
     * @param  bool $flag
     * @return Zend_Form_Element
     */
    public function setDisableTranslator($flag)
    {
        $this->_translatorDisabled = (bool) $flag;
        return $this;
    }

    /**
     * Is translation disabled?
     *
     * @return bool
     */
    public function translatorIsDisabled()
    {
        return $this->_translatorDisabled;
    }

    /**
     * Set element order
     *
     * @param  int $order
     * @return Zend_Form_Element
     */
    public function setOrder($order)
    {
        $this->_order = (int) $order;
        return $this;
    }

    /**
     * Retrieve element order
     *
     * @return int
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * Return element type
     *
     * @return string
     */
    public function getType()
    {
        if (null === $this->_type) {
            $this->_type = get_class($this);
        }

        return $this->_type;
    }

    /**
     * Set element attribute
     *
     * @param  string $name
     * @param  mixed $value
     * @return Zend_Form_Element
     * @throws Zend_Form_Exception for invalid $name values
     */
    public function setAttrib($name, $value)
    {
        $name = (string) $name;
        if ('_' == $name[0]) {
            throw new Xoops_Zend_Table_Exception(sprintf('Invalid attribute "%s"; must not contain a leading underscore', $name));
        }

        if (null === $value) {
            unset($this->$name);
        } else {
            $this->$name = $value;
        }

        return $this;
    }

    /**
     * Set multiple attributes at once
     *
     * @param  array $attribs
     * @return Zend_Form_Element
     */
    public function setAttribs(array $attribs)
    {
        foreach ($attribs as $key => $value) {
            $this->setAttrib($key, $value);
        }

        return $this;
    }

    /**
     * Retrieve element attribute
     *
     * @param  string $name
     * @return string
     */
    public function getAttrib($name)
    {
        $name = (string) $name;
        if (isset($this->$name)) {
            return $this->$name;
        }

        return null;
    }

    /**
     * Return all attributes
     *
     * @return array
     */
    public function getAttribs()
    {
        $attribs = get_object_vars($this);
        foreach ($attribs as $key => $value) {
            if ('_' == substr($key, 0, 1)) {
                unset($attribs[$key]);
            }
        }

        return $attribs;
    }

    /**
     * Overloading: retrieve object property
     *
     * Prevents access to properties beginning with '_'.
     *
     * @param  string $key
     * @return mixed
     */
    public function __get($key)
    {
        if ('_' == $key[0]) {
            throw new Xoops_Zend_Table_Exception(sprintf('Cannot retrieve value for protected/private property "%s"', $key));
        }

        if (!isset($this->$key)) {
            return null;
        }

        return $this->$key;
    }

    /**
     * Overloading: set object property
     *
     * @param  string $key
     * @param  mixed $value
     * @return voide
     */
    public function __set($key, $value)
    {
        $this->setAttrib($key, $value);
    }

    /**
     * Overloading: allow rendering specific decorators
     *
     * Call renderDecoratorName() to render a specific decorator.
     *
     * @param  string $method
     * @param  array $args
     * @return string
     * @throws Zend_Form_Exception for invalid decorator or invalid method call
     */
    public function __call($method, $args)
    {
        if ('render' == substr($method, 0, 6)) {
            $this->_isPartialRendering = true;
            $this->render();
            $this->_isPartialRendering = false;
            $decoratorName = substr($method, 6);
            if (false !== ($decorator = $this->getDecorator($decoratorName))) {
                $decorator->setElement($this);
                $seed = '';
                if (0 < count($args)) {
                    $seed = array_shift($args);
                }
                return $decorator->render($seed);
            }

            throw new Xoops_Zend_Table_Exception(sprintf('Decorator by name %s does not exist', $decoratorName));
        }

        throw new Xoops_Zend_Table_Exception(sprintf('Method %s does not exist', $method));
    }

    // Loaders

    /**
     * Set plugin loader to use for validator or filter chain
     *
     * @param  Zend_Loader_PluginLoader_Interface $loader
     * @param  string $type 'decorator', 'filter', or 'validate'
     * @return Zend_Form_Element
     * @throws Zend_Form_Exception on invalid type
     */
    public function setPluginLoader(Zend_Loader_PluginLoader_Interface $loader, $type)
    {
        $type = strtoupper($type);
        switch ($type) {
            case self::DECORATOR:
            case self::ELEMENT:
                $this->_loaders[$type] = $loader;
                return $this;
            default:
               throw new Xoops_Zend_Table_Exception(sprintf('Invalid type "%s" provided to setPluginLoader()', $type));
        }
    }

    /**
     * Retrieve plugin loader for validator or filter chain
     *
     * Instantiates with default rules if none available for that type. Use
     * 'decorator', 'filter', or 'validate' for $type.
     *
     * @param  string $type
     * @return Zend_Loader_PluginLoader
     * @throws Xoops_Zend_Table_Exception on invalid type.
     */
    public function getPluginLoader($type)
    {
        $type = strtoupper($type);
        switch ($type) {
            case self::ELEMENT:
                $prefixSegment = 'Table_Element';
                $pathSegment   = 'Table/Element';
            case self::DECORATOR:
                if (!isset($prefixSegment)) {
                    $prefixSegment = 'Table_Decorator';
                    $pathSegment   = 'Table/Decorator';
                }
                if (!isset($this->_loaders[$type])) {
                    $this->_loaders[$type] = new Xoops_Zend_Loader_PluginLoader(
                        array('Xoops_Zend_' . $prefixSegment . '_' => XOOPS::path('lib') . '/Xoops/Zend/' . $pathSegment . '/')
                    );
                }
                return $this->_loaders[$type];
            default:
                throw new Xoops_Zend_Table_Exception(sprintf('Invalid type "%s" provided to getPluginLoader()', $type));
        }
    }

    /**
     * Add prefix path for plugin loader
     *
     * If no $type specified, assumes it is a base path for both filters and
     * validators, and sets each according to the following rules:
     * - decorators: $prefix = $prefix . '_Decorator'
     * - filters: $prefix = $prefix . '_Filter'
     * - validators: $prefix = $prefix . '_Validate'
     *
     * Otherwise, the path prefix is set on the appropriate plugin loader.
     *
     * @param  string $prefix
     * @param  string $path
     * @param  string $type
     * @return Zend_Form_Element
     * @throws Zend_Form_Exception for invalid type
     */
    public function addPrefixPath($prefix, $path, $type = null)
    {
        $type = strtoupper($type);
        switch ($type) {
            case self::DECORATOR:
            case self::ELEMENT:
                $loader = $this->getPluginLoader($type);
                $loader->addPrefixPath($prefix, $path);
                return $this;
            case null:
                $prefix = rtrim($prefix, '_');
                $path   = rtrim($path, DIRECTORY_SEPARATOR);
                foreach (array(self::DECORATOR, self::ELEMENT) as $type) {
                    $cType        = ucfirst(strtolower($type));
                    $pluginPath   = $path . DIRECTORY_SEPARATOR . $cType . DIRECTORY_SEPARATOR;
                    $pluginPrefix = $prefix . '_' . $cType;
                    $loader       = $this->getPluginLoader($type);
                    $loader->addPrefixPath($pluginPrefix, $pluginPath);
                }
                return $this;
            default:
                throw new Xoops_Zend_Table_Exception(sprintf('Invalid type "%s" provided to getPluginLoader()', $type));
        }
    }

    /**
     * Add many prefix paths at once
     *
     * @param  array $spec
     * @return Zend_Form_Element
     */
    public function addPrefixPaths(array $spec)
    {
        if (isset($spec['prefix']) && isset($spec['path'])) {
            return $this->addPrefixPath($spec['prefix'], $spec['path']);
        }
        foreach ($spec as $type => $paths) {
            if (is_numeric($type) && is_array($paths)) {
                $type = null;
                if (isset($paths['prefix']) && isset($paths['path'])) {
                    if (isset($paths['type'])) {
                        $type = $paths['type'];
                    }
                    $this->addPrefixPath($paths['prefix'], $paths['path'], $type);
                }
            } elseif (!is_numeric($type)) {
                if (!isset($paths['prefix']) || !isset($paths['path'])) {
                    foreach ($paths as $prefix => $spec) {
                        if (is_array($spec)) {
                            foreach ($spec as $path) {
                                if (!is_string($path)) {
                                    continue;
                                }
                                $this->addPrefixPath($prefix, $path, $type);
                            }
                        } elseif (is_string($spec)) {
                            $this->addPrefixPath($prefix, $spec, $type);
                        }
                    }
                } else {
                    $this->addPrefixPath($paths['prefix'], $paths['path'], $type);
                }
            }
        }
        return $this;
    }

    // Rendering

    /**
     * Set view object
     *
     * @param  Zend_View_Interface $view
     * @return Zend_Form_Element
     */
    public function setView(Zend_View_Interface $view = null)
    {
        $this->_view = $view;
        return $this;
    }

    /**
     * Retrieve view object
     *
     * Retrieves from ViewRenderer if none previously set.
     *
     * @return null|Zend_View_Interface
     */
    public function getView()
    {
        if (null === $this->_view) {
            require_once 'Zend/Controller/Action/HelperBroker.php';
            $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
            $this->setView($viewRenderer->view);
        }
        return $this->_view;
    }

    /**
     * Instantiate a decorator based on class name or class name fragment
     *
     * @param  string $name
     * @param  null|array $options
     * @return Zend_Form_Decorator_Interface
     */
    protected function _getDecorator($name, $options)
    {
        $class = $this->getPluginLoader(self::DECORATOR)->load($name);
        if (null === $options) {
            $decorator = new $class;
        } else {
            $decorator = new $class($options);
        }

        return $decorator;
    }

    /**
     * Add a decorator for rendering the element
     *
     * @param  string|Zend_Form_Decorator_Interface $decorator
     * @param  array|Zend_Config $options Options with which to initialize decorator
     * @return Zend_Form_Element
     */
    public function addDecorator($decorator, $options = null)
    {
        if ($decorator instanceof Xoops_Zend_Table_Decorator_Interface) {
            $name = get_class($decorator);
        } elseif (is_string($decorator)) {
            $name      = $decorator;
            $decorator = array(
                'decorator' => $name,
                'options'   => $options,
            );
        } elseif (is_array($decorator)) {
            foreach ($decorator as $name => $spec) {
                break;
            }
            if (is_numeric($name)) {
                throw new Xoops_Zend_Table_Exception('Invalid alias provided to addDecorator; must be alphanumeric string');
            }
            if (is_string($spec)) {
                $decorator = array(
                    'decorator' => $spec,
                    'options'   => $options,
                );
            } elseif ($spec instanceof Xoops_Zend_Table_Decorator_Interface) {
                $decorator = $spec;
            }
        } else {
            throw new Xoops_Zend_Table_Exception('Invalid decorator provided to addDecorator; must be string or Zend_Form_Decorator_Interface');
        }

        $this->_decorators[$name] = $decorator;

        return $this;
    }

    /**
     * Add many decorators at once
     *
     * @param  array $decorators
     * @return Zend_Form_Element
     */
    public function addDecorators(array $decorators)
    {
        foreach ($decorators as $decoratorName => $decoratorInfo) {
            if (is_string($decoratorInfo) ||
                $decoratorInfo instanceof Xoops_Zend_Table_Decorator_Interface) {
                if (!is_numeric($decoratorName)) {
                    $this->addDecorator(array($decoratorName => $decoratorInfo));
                } else {
                    $this->addDecorator($decoratorInfo);
                }
            } elseif (is_array($decoratorInfo)) {
                $argc    = count($decoratorInfo);
                $options = array();
                if (isset($decoratorInfo['decorator'])) {
                    $decorator = $decoratorInfo['decorator'];
                    if (isset($decoratorInfo['options'])) {
                        $options = $decoratorInfo['options'];
                    }
                    $this->addDecorator($decorator, $options);
                } else {
                    switch (true) {
                        case (0 == $argc):
                            break;
                        case (1 <= $argc):
                            $decorator  = array_shift($decoratorInfo);
                        case (2 <= $argc):
                            $options = array_shift($decoratorInfo);
                        default:
                            $this->addDecorator($decorator, $options);
                            break;
                    }
                }
            } else {
                throw new Xoops_Zend_Table_Exception('Invalid decorator passed to addDecorators()');
            }
        }

        return $this;
    }

    /**
     * Overwrite all decorators
     *
     * @param  array $decorators
     * @return Zend_Form_Element
     */
    public function setDecorators(array $decorators)
    {
        $this->clearDecorators();
        return $this->addDecorators($decorators);
    }

    /**
     * Retrieve a registered decorator
     *
     * @param  string $name
     * @return false|Zend_Form_Decorator_Abstract
     */
    public function getDecorator($name)
    {
        if (!isset($this->_decorators[$name])) {
            $len = strlen($name);
            foreach ($this->_decorators as $localName => $decorator) {
                if ($len > strlen($localName)) {
                    continue;
                }

                if (0 === substr_compare($localName, $name, -$len, $len, true)) {
                    if (is_array($decorator)) {
                        return $this->_loadDecorator($decorator, $localName);
                    }
                    return $decorator;
                }
            }
            return false;
        }

        if (is_array($this->_decorators[$name])) {
            return $this->_loadDecorator($this->_decorators[$name], $name);
        }

        return $this->_decorators[$name];
    }

    /**
     * Retrieve all decorators
     *
     * @return array
     */
    public function getDecorators()
    {
        foreach ($this->_decorators as $key => $value) {
            if (is_array($value)) {
                $this->_loadDecorator($value, $key);
            }
        }
        return $this->_decorators;
    }

    /**
     * Remove a single decorator
     *
     * @param  string $name
     * @return Zend_Form_Element
     */
    public function removeDecorator($name)
    {
        if (isset($this->_decorators[$name])) {
            unset($this->_decorators[$name]);
        } else {
            $len = strlen($name);
            foreach (array_keys($this->_decorators) as $decorator) {
                if ($len > strlen($decorator)) {
                    continue;
                }
                if (0 === substr_compare($decorator, $name, -$len, $len, true)) {
                    unset($this->_decorators[$decorator]);
                    break;
                }
            }
        }

        return $this;
    }

    /**
     * Clear all decorators
     *
     * @return Zend_Form_Element
     */
    public function clearDecorators()
    {
        $this->_decorators = array();
        return $this;
    }

    /**
     * Render form element
     *
     * @param  Zend_View_Interface $view
     * @return string
     */
    public function render(Zend_View_Interface $view = null)
    {
        if ($this->_isPartialRendering) {
            return '';
        }

        if (null !== $view) {
            $this->setView($view);
        }

        $content = '';
        foreach ($this->getDecorators() as $decorator) {
            $decorator->setElement($this);
            $content = $decorator->render($content);
        }
        return $content;
    }

    /**
     * String representation of form element
     *
     * Proxies to {@link render()}.
     *
     * @return string
     */
    public function __toString()
    {
        try {
            $return = $this->render();
            return $return;
        } catch (Exception $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
            return '';
        }
    }

    /**
     * Lazy-load a decorator
     *
     * @param  array $decorator Decorator type and options
     * @param  mixed $name Decorator name or alias
     * @return Zend_Form_Decorator_Interface
     */
    protected function _loadDecorator(array $decorator, $name)
    {
        $sameName = false;
        if ($name == $decorator['decorator']) {
            $sameName = true;
        }

        $instance = $this->_getDecorator($decorator['decorator'], $decorator['options']);
        if ($sameName) {
            $newName            = get_class($instance);
            $decoratorNames     = array_keys($this->_decorators);
            $order              = array_flip($decoratorNames);
            $order[$newName]    = $order[$name];
            $decoratorsExchange = array();
            unset($order[$name]);
            asort($order);
            foreach ($order as $key => $index) {
                if ($key == $newName) {
                    $decoratorsExchange[$key] = $instance;
                    continue;
                }
                $decoratorsExchange[$key] = $this->_decorators[$key];
            }
            $this->_decorators = $decoratorsExchange;
        } else {
            $this->_decorators[$name] = $instance;
        }

        return $instance;
    }

    public function setRoot($root)
    {
        if ($root != $this->_root) {
            $this->_root = $root;
            foreach ($this->_elements as $element) {
                if (is_object($element) && method_exists($element, "setRoot")) {
                    $element->setRoot($this->_root);
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
}
<?php
/**
 * Table handler for Xoops Engine
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
 * @package         Xoops_Core
 * @subpackage      Table
 * @version         $Id$
 * @uses            Xoops_Zend_Loader_PluginLoader
 * @uses            Xoops_Zend_Table_Exception
 * @uses            Xoops_Zend_Table_Element
 * @uses            Xoops_Zend_Table_Decorator_Interface
 * @uses            Zend_Config
 * @uses            Zend_Loader_PluginLoader_Interface
 * @uses            Zend_View_Interface
 * @uses            Zend_Translate_Adapter
 * @uses            Zend_Translate
 */

class Xoops_Table //implements Iterator, Countable
{
    /**#@+
     * Plugin loader type constants
     */
    const DECORATOR = 'DECORATOR';
    const ELEMENT = 'ELEMENT';
    /**#@-*/

    /**
     * Form metadata and attributes
     * @var array
     */
    protected $_attribs = array();

    /**
     * Decorators for rendering
     * @var array
     */
    protected $_decorators = array();

    /**
     * Should we disable loading the default decorators?
     * @var bool
     */
    protected $_disableLoadDefaultDecorators = false;

    /**
     * Prefix paths to use when creating elements
     * @var array
     */
    protected $_elementPrefixPaths = array();

    /**
     * Form elements
     * @var array
     */
    protected $_elements = array();

    /**
     * Table order
     * @var int|null
     */
    protected $_Order;

    /**
     * Plugin loaders
     * @var array
     */
    protected $_loaders = array();

    /**
     * Order in which to display and iterate elements
     * @var array
     */
    //protected $_order = array();

    /**
     * Whether internal order has been updated or not
     * @var bool
     */
    //protected $_orderUpdated = false;

    /**
     * @var Zend_Translate
     */
    protected $_translator;

    /**
     * Global default translation adapter
     * @var Zend_Translate
     */
    protected static $_translatorDefault;

    /**
     * is the translator disabled?
     * @var bool
     */
    protected $_translatorDisabled = false;

    /**
     * @var Zend_View_Interface
     */
    protected $_view;

    /**
     * Constructor
     *
     * @param mixed $options
     * @return void
     */
    public function __construct($options = null)
    {
        if (is_string($options)) {
            $this->setId($options);
        } elseif (is_array($options)) {
            $this->setOptions($options);
        } elseif ($options instanceof Zend_Config) {
            $this->setConfig($options);
        }

        // Extensions...
        $this->init();

        $this->loadDefaultDecorators();
    }

    /**
     * Clone form object and all children
     *
     * @return void
     */
    public function __clone()
    {
        $elements = array();
        foreach ($this->getElements() as $element) {
            $elements[] = clone $element;
        }
        $this->setElements($elements);
    }

    /**
     * Initialize form (used by extending classes)
     *
     * @return void
     */
    public function init()
    {
    }

    /**
     * Set form state from options array
     *
     * @param  array $options
     * @return Xoops_Table
     */
    public function setOptions(array $options)
    {
        if (isset($options['prefixPath'])) {
            $this->addPrefixPaths($options['prefixPath']);
            unset($options['prefixPath']);
        }

        if (isset($options['elementPrefixPath'])) {
            $this->addElementPrefixPaths($options['elementPrefixPath']);
            unset($options['elementPrefixPath']);
        }

        if (isset($options['elements'])) {
            $this->setElements($options['elements']);
            unset($options['elements']);
        }

        if (isset($options['attribs'])) {
            $this->addAttribs($options['attribs']);
            unset($options['attribs']);
        }

        $forbidden = array(
            'Options', 'Config', 'PluginLoader', 'View', 'Translator',
            'Attrib',
        );

        foreach ($options as $key => $value) {
            $normalized = ucfirst($key);
            if (in_array($normalized, $forbidden)) {
                continue;
            }

            $method = 'set' . $normalized;
            if (method_exists($this, $method)) {
                $this->$method($value);
            } else {
                $this->setAttrib($key, $value);
            }
        }

        return $this;
    }

    /**
     * Set form state from config object
     *
     * @param  Zend_Config $config
     * @return Xoops_Table
     */
    public function setConfig(Zend_Config $config)
    {
        return $this->setOptions($config->toArray());
    }


    /**
     * Set table id
     *
     * @param  string $id
     * @return Xoops_Table
     */
    public function setId($id)
    {
        return $this->setAttrib('id', $id);
    }

    /**
     * Get id attribute
     *
     * @return null|string
     */
    public function getId()
    {
        return $this->getAttrib('id');
    }

    // Loaders

    /**
     * Set plugin loaders for use with decorators and elements
     *
     * @param  Zend_Loader_PluginLoader_Interface $loader
     * @param  string $type 'decorator' or 'element'
     * @return Xoops_Table
     * @throws Xoops_Zend_Table_Exception on invalid type
     */
    public function setPluginLoader(Zend_Loader_PluginLoader_Interface $loader, $type = null)
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
     * Retrieve plugin loader for given type
     *
     * $type may be one of:
     * - decorator
     * - element
     *
     * If a plugin loader does not exist for the given type, defaults are
     * created.
     *
     * @param  string $type
     * @return Zend_Loader_PluginLoader_Interface
     */
    public function getPluginLoader($type = null)
    {
        $type = strtoupper($type);
        if (!isset($this->_loaders[$type])) {
            switch ($type) {
                case self::DECORATOR:
                    $prefixSegment = 'Table_Decorator';
                    $pathSegment   = 'Table/Decorator';
                    break;
                case self::ELEMENT:
                    $prefixSegment = 'Table_Element';
                    $pathSegment   = 'Table/Element';
                    break;
                default:
                    throw new Xoops_Zend_Table_Exception(sprintf('Invalid type "%s" provided to getPluginLoader()', $type));
            }

            $this->_loaders[$type] = new Xoops_Zend_Loader_PluginLoader(
                array('Xoops_Zend_' . $prefixSegment . '_' => XOOPS::path('lib') . '/Xoops/Zend/' . $pathSegment . '/')
            );
        }

        return $this->_loaders[$type];
    }

    /**
     * Add prefix path for plugin loader
     *
     * If no $type specified, assumes it is a base path for both filters and
     * validators, and sets each according to the following rules:
     * - decorators: $prefix = $prefix . '_Decorator'
     * - elements: $prefix = $prefix . '_Element'
     *
     * Otherwise, the path prefix is set on the appropriate plugin loader.
     *
     * If $type is 'decorator', sets the path in the decorator plugin loader
     * for all elements. Additionally, if no $type is provided,
     * the prefix and path is added to both decorator and element
     * plugin loader with following settings:
     * $prefix . '_Decorator', $path . '/Decorator/'
     * $prefix . '_Element', $path . '/Element/'
     *
     * @param  string $prefix
     * @param  string $path
     * @param  string $type
     * @return Xoops_Table
     * @throws Xoops_Zend_Table_Exception for invalid type
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
     * @return Xoops_Table
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
                    continue;
                }
                $this->addPrefixPath($paths['prefix'], $paths['path'], $type);
            }
        }
        return $this;
    }

    /**
     * Add prefix path for all elements
     *
     * @param  string $prefix
     * @param  string $path
     * @param  string $type
     * @return Xoops_Table
     */
    public function addElementPrefixPath($prefix, $path, $type = null)
    {
        $this->_elementPrefixPaths[] = array(
            'prefix' => $prefix,
            'path'   => $path,
            'type'   => $type,
        );

        foreach ($this->getElements() as $element) {
            $element->addPrefixPath($prefix, $path, $type);
        }

        return $this;
    }

    /**
     * Add prefix paths for all elements
     *
     * @param  array $spec
     * @return Xoops_Table
     */
    public function addElementPrefixPaths(array $spec)
    {
        $this->_elementPrefixPaths = $this->_elementPrefixPaths + $spec;

        foreach ($this->getElements() as $element) {
            $element->addPrefixPaths($spec);
        }

        return $this;
    }

    // Table metadata:

    /**
     * Set form attribute
     *
     * @param  string $key
     * @param  mixed $value
     * @return Xoops_Table
     */
    public function setAttrib($key, $value)
    {
        $key = (string) $key;
        $this->_attribs[$key] = $value;
        return $this;
    }

    /**
     * Add multiple form attributes at once
     *
     * @param  array $attribs
     * @return Xoops_Table
     */
    public function addAttribs(array $attribs)
    {
        foreach ($attribs as $key => $value) {
            $this->setAttrib($key, $value);
        }
        return $this;
    }

    /**
     * Set multiple form attributes at once
     *
     * Overwrites any previously set attributes.
     *
     * @param  array $attribs
     * @return Xoops_Table
     */
    public function setAttribs(array $attribs)
    {
        $this->clearAttribs();
        return $this->addAttribs($attribs);
    }

    /**
     * Retrieve a single form attribute
     *
     * @param  string $key
     * @return mixed
     */
    public function getAttrib($key)
    {
        $key = (string) $key;
        if (!isset($this->_attribs[$key])) {
            return null;
        }

        return $this->_attribs[$key];
    }

    /**
     * Retrieve all form attributes/metadata
     *
     * @return array
     */
    public function getAttribs()
    {
        return $this->_attribs;
    }

    /**
     * Remove attribute
     *
     * @param  string $key
     * @return bool
     */
    public function removeAttrib($key)
    {
        if (isset($this->_attribs[$key])) {
            unset($this->_attribs[$key]);
            return true;
        }

        return false;
    }

    /**
     * Clear all form attributes
     *
     * @return Xoops_Table
     */
    public function clearAttribs()
    {
        $this->_attribs = array();
        return $this;
    }

    /**
     * Set form order
     *
     * @param  int $index
     * @return Xoops_Table
     */
    public function setOrder($index)
    {
        $this->_Order = (int) $index;
        return $this;
    }

    /**
     * Get form order
     *
     * @return int|null
     */
    public function getOrder()
    {
        return $this->_Order;
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
     * @param  array|Zend_Config $options
     * @return Xoops_Table
     */
    public function addElement($element, $options = null)
    {
        if (is_string($element) || is_array($element)) {
            if (is_string($element)) {
                $type = $element;
            } elseif (isset($element["type"])) {
                $type = $element["type"];
                $options = array_merge(isset($element["options"]) ? $element["options"] : array(), $options);
            } else {
                return $this;
            }
            $element = $this->createElement($type, $options);
        } elseif ($element instanceof Xoops_Zend_Table_Element) {
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
            return $this;
        }

        if (method_exists($element, "setRoot")) {
            $element->setRoot($this);
        }
        $this->_elements[] = $element;

        //$this->_order[$name] = $this->_elements[$name]->getOrder();
        //$this->_orderUpdated = true;

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
     * @return Xoops_Table
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
                    } elseif (isset($spec['elements'])) {
                        $options = array("elements" => $spec['elements']);
                    }
                    //Debug::e($options);
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
                            break;
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
        $this->clearElements();
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

    /**
     * Remove all elements
     *
     * @return Xoops_Table
     */
    public function clearElements()
    {
        $this->_elements        = array();
        //$this->_order           = array();
        //$this->_orderUpdated    = true;
        return $this;
    }

    // Rendering

    /**
     * Set view object
     *
     * @param  Zend_View_Interface $view
     * @return Xoops_Table
     */
    public function setView(Zend_View_Interface $view = null)
    {
        $this->_view = $view;
        return $this;
    }

    /**
     * Retrieve view object
     *
     * If none registered, attempts to pull from ViewRenderer.
     *
     * @return Zend_View_Interface|null
     */
    public function getView()
    {
        if (null === $this->_view) {
            $this->setView(XOOPS::registry("view"));
        }

        return $this->_view;
    }

    /**
     * Instantiate a decorator based on class name or class name fragment
     *
     * @param  string $name
     * @param  null|array $options
     * @return Xoops_Zend_Table_Decorator_Interface
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
     * @param  string|Xoops_Zend_Table_Decorator_Interface $decorator
     * @param  array|Zend_Config $options Options with which to initialize decorator
     * @return Xoops_Table
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
            throw new Xoops_Zend_Table_Exception('Invalid decorator provided to addDecorator; must be string or Xoops_Zend_Table_Decorator_Interface');
        }

        $this->_decorators[$name] = $decorator;

        return $this;
    }

    /**
     * Add many decorators at once
     *
     * @param  array $decorators
     * @return Xoops_Table
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
     * @return Xoops_Table
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
     * @return false|Xoops_Zend_Table_Decorator_Abstract
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
     * @return bool
     */
    public function removeDecorator($name)
    {
        $decorator = $this->getDecorator($name);
        if ($decorator) {
            if (array_key_exists($name, $this->_decorators)) {
                unset($this->_decorators[$name]);
            } else {
                $class = get_class($decorator);
                if (!array_key_exists($class, $this->_decorators)) {
                    return false;
                }
                unset($this->_decorators[$class]);
            }
            return true;
        }

        return false;
    }

    /**
     * Clear all decorators
     *
     * @return Xoops_Table
     */
    public function clearDecorators()
    {
        $this->_decorators = array();
        return $this;
    }

    /**
     * Render form
     *
     * @param  Zend_View_Interface $view
     * @return string
     */
    public function render(Zend_View_Interface $view = null)
    {
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
     * Serialize as string
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
            $message = "Exception caught by form: " . $e->getMessage()
                     . "\nStack Trace:\n" . $e->getTraceAsString();
            trigger_error($message, E_USER_WARNING);
            return '';
        }
    }


    // Localization:

    /**
     * Set translator object
     *
     * @param  Zend_Translate|Zend_Translate_Adapter|null $translator
     * @return Xoops_Table
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
     * Set global default translator object
     *
     * @param  Zend_Translate|Zend_Translate_Adapter|null $translator
     * @return void
     */
    public static function setDefaultTranslator($translator = null)
    {
        if (null === $translator) {
            self::$_translatorDefault = null;
        } elseif ($translator instanceof Zend_Translate_Adapter) {
            self::$_translatorDefault = $translator;
        } elseif ($translator instanceof Zend_Translate) {
            self::$_translatorDefault = $translator->getAdapter();
        } else {
            throw new Xoops_Zend_Table_Exception('Invalid translator specified');
        }
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
            return self::getDefaultTranslator();
        }

        return $this->_translator;
    }

    /**
     * Does this form have its own specific translator?
     *
     * @return bool
     */
    public function hasTranslator()
    {
        return (bool)$this->_translator;
    }

    /**
     * Get global default translator object
     *
     * @return null|Zend_Translate
     */
    public static function getDefaultTranslator()
    {
        if (null === self::$_translatorDefault) {
            if (XOOPS::registry('translate')) {
                $translator = XOOPS::registry('translate');
                if ($translator instanceof Zend_Translate_Adapter) {
                    return $translator;
                } elseif ($translator instanceof Zend_Translate) {
                    return $translator->getAdapter();
                }
            }
        }
        return self::$_translatorDefault;
    }

    /**
     * Is there a default translation object set?
     *
     * @return boolean
     */
    public static function hasDefaultTranslator()
    {
        return (bool)self::$_translatorDefault;
    }

    /**
     * Indicate whether or not translation should be disabled
     *
     * @param  bool $flag
     * @return Xoops_Table
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
     * Overloading: allow rendering specific decorators
     *
     * Call renderDecoratorName() to render a specific decorator.
     *
     * @param  string $method
     * @param  array $args
     * @return string
     * @throws Xoops_Zend_Table_Exception for invalid decorator or invalid method call
     */
    public function __call($method, $args)
    {
        if ('render' == substr($method, 0, 6)) {
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
        } elseif ('add' == substr($method, 0, 3)) {
            $elementType = substr($method, 3);
            $options = array();
            if (0 < count($args)) {
                $options = array_shift($args);
            }

            if (false !== ($element = $this->createElement($elementType, $options))) {
                $this->addElement($element);
                return $this;
            }

            throw new Xoops_Zend_Table_Exception(sprintf('Element by type %s does not exist', $elementType));
        }

        throw new Xoops_Zend_Table_Exception(sprintf('Method %s does not exist', $method));
    }

    /**
     * Set flag to disable loading default decorators
     *
     * @param  bool $flag
     * @return Xoops_Table
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
     * Load the default decorators
     *
     * @return void
     */
    public function loadDefaultDecorators()
    {
        if ($this->loadDefaultDecoratorsIsDisabled()) {
            return $this;
        }

        $decorators = $this->getDecorators();
        if (empty($decorators)) {
            $this->addDecorator('HtmlTag', array('tag' => 'table', 'class' => 'xoops-table'));
                 //->addDecorator('TableMessages', array('tag' => 'div', 'class' => 'xoops-table'));
        }
        return $this;
    }

    /**
     * Lazy-load a decorator
     *
     * @param  array $decorator Decorator type and options
     * @param  mixed $name Decorator name or alias
     * @return Xoops_Zend_Table_Decorator_Interface
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

    /**
     * Render table and assign content to template
     *
     * @param  Zend_View_Interface $view
     * @return Xoops_Table
     */
    public function assign(Zend_View_Interface $view = null)
    {
        $tableName = preg_replace("/[^a-z0-9_]/i", "_", $this->getId());
        $content = (string) $this->render($view);
        $view->getEngine()->assign($tableName, $content);
        return $this;
    }
}
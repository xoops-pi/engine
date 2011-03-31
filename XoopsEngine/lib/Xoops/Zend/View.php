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
 * @copyright       Xoops Engine http://www.xoopsengine.org/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @since           3.0
 * @category        Xoops_Zend
 * @package         View
 * @version         $Id$
 */

class Xoops_Zend_View extends Zend_View_Abstract
{
    /**
     * Template engine
     * @var Xoops_Smarty_Template
     */
    protected $engine = null;
    protected $engineOptions = array();
    protected $engineClass = 'Xoops_Smarty_Engine';
    //protected $themePath = null;
    //public $section = null;

    /**
     * Plugin loaders
     * @var array
     */
    protected $loaders = array();

    /**
     * Plugin types
     * @var array
     */
    protected $loaderTypes = array('filter', 'helper');

    /**
     * Constructor
     *
     * @param  array $config
     * @return void
     */
    public function __construct($config = array())
    {
        if (isset($config['template'])) {
            $this->engineOptions = $config['template'];
            unset($config['template']);
        }
        parent::__construct($config);
        $this->addHelperPath(XOOPS::path('lib') . '/Xoops/Zend/View/Helper', 'Xoops_Zend_View_Helper');
        $this->addFilterPath(XOOPS::path('lib') . '/Xoops/Zend/View/Filter', 'Xoops_Zend_View_Filter');
    }

    /**
     * Retrieve plugin loader for a specific plugin type
     *
     * @param  string $type
     * @return Zend_Loader_PluginLoader
     */
    public function getPluginLoader($type)
    {
        $type = strtolower($type);
        if (!in_array($type, $this->loaderTypes)) {
            $e = new Zend_View_Exception(sprintf('Invalid plugin loader type "%s"; cannot retrieve', $type));
            $e->setView($this);
            throw $e;
        }

        if (!array_key_exists($type, $this->loaders)) {
            $prefix     = 'Zend_View_';
            $pathPrefix = 'Zend/View/';

            $pType = ucfirst($type);
            switch ($type) {
                case 'filter':
                case 'helper':
                default:
                    $prefix     .= $pType;
                    $pathPrefix .= $pType;
                    $loader = new Xoops_Zend_Loader_PluginLoader(array(
                        $prefix             => XOOPS::path("lib") . "/" . $pathPrefix,
                        "Xoops_" . $prefix  => XOOPS::path("lib") . "/Xoops/" . $pathPrefix
                    ));
                    $this->loaders[$type] = $loader;
                    break;
            }
        }
        return $this->loaders[$type];
    }

    /**
     * Return the template engine object
     *
     * Lazy load the template object instead of loading in init()
     *
     * @return Zend_View_Abstract
     */
    public function getEngine()
    {
        if (!isset($this->engine)) {
            $engineClass = $this->engineClass;
            $this->engine = new $engineClass($this->engineOptions);
        }
        return $this->engine;
    }

    public function setEngine($engine = null)
    {
        if (!is_null($engine)) {
            $this->engine = $engine;
        }
        return $this;
    }

    public function display($name = null)
    {
        echo $this->render($name);
    }

    /**
     * Assigns variables to the view script via differing strategies.
     *
     * Zend_View::assign('name', $value) assigns a variable called 'name'
     * with the corresponding $value.
     *
     * Zend_View::assign($array) assigns the array keys as variable
     * names (with the corresponding array values).
     *
     * @see    __set()
     * @param  string|array The assignment strategy to use.
     * @param  mixed (Optional) If assigning a named variable, use this
     * as the value.
     * @return Zend_View_Abstract Fluent interface
     * @throws Zend_View_Exception if $spec is neither a string nor an array,
     * or if an attempt to set a private or protected member is detected
     */
    public function assign($spec, $value = null)
    {
        if (is_array($spec)) {
            $this->getEngine()->assign($spec);
            return;
        }

        $this->getEngine()->assign($spec, $value);
    }

    /**
     * Return list of all assigned variables
     *
     * Returns all public properties of the object. Reflection is not used
     * here as testing reflection properties for visibility is buggy.
     *
     * @return array
     */
    public function getVars()
    {
        return $this->getEngine()->getTemplateVars();
    }

    /**
     * Clear all assigned variables
     *
     * Clears all variables assigned to Zend_View either via {@link assign()} or
     * property overloading ({@link __set()}).
     *
     * @return void
     */
    public function clearVars()
    {
        $this->getEngine()->clearAllAssign();
    }

    /**
     * Includes the view script in a scope with only public $this variables.
     *
     * @param string The view script to execute.
     */
    protected function _run()
    {
        $template = func_get_arg(0);
        if (empty($template)) {
            return "";
        }

        return $this->getEngine()->display($template, null, null, $this->getVars());
    }

    /**
     * Finds a view script from the available directories.
     *
     * @param $name string The base name of the script.
     * @return string   Full paht to the script
     */
    protected function _script($name)
    {
        if (empty($name)) {
            return "";
        }

        if ($this->isLfiProtectionOn() && preg_match('#\.\.[\\\/]#', $name)) {
            require_once 'Zend/View/Exception.php';
            throw new Zend_View_Exception('Requested scripts may not include parent directory traversal ("../", "..\\" notation)');
        }
        if (false !== ($path = $this->resourcePath($name, true))) {
            return $path;
        }

        $message = "script '$name' not found";
        trigger_error($message, E_USER_WARNING);
        //require_once 'Zend/View/Exception.php';
        //throw new Zend_View_Exception($message, $this);
    }

    public function getTheme()
    {
        return Xoops::registry('layout')->getTheme();
    }

    /**
     * Return a themable file resource path
     *
     * Path options:
     * www/, themes/, modules/, apps/
     *
     * @param string    $path
     * @param bool      $isAbsolute return faul path
     * @return string
     */
    public function resourcePath($path, $isAbsolute = false)
    {
        if (false !== strpos($path, ':')) {
            return $path;
        }

        // themes/themeName/modules/moduleName/template.html, themes/themeName/apps/moduleName/template.html
        //if (false !== ($themePath = $this->getHelper('layout')->getLayout()->resourcePath($path, $isAbsolute))) {
        if (false !== ($themePath = Xoops::registry('layout')->resourcePath($path, $isAbsolute))) {
            return $themePath;
        }
        return $isAbsolute ? XOOPS::path($path) : $path;
    }
}
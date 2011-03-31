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
 * @package         Controller
 * @version         $Id$
 */

class Xoops_Zend_Controller_Router_Application extends Zend_Controller_Router_Rewrite
{
    //protected $configs = array();
    //public $section = 'application';
    //protected $route = 'default';
    //public $route = 'application';

    /**
     * Array of invocation parameters to use when instantiating action
     * controllers
     * @var array
     */
    protected $_invokeParams = array(
        'section'   => 'application',
        'route'     => 'application',
    );

    /**
     * Constructor
     *
     * @param array $params
     * @return void
     */
    public function __construct(array $params = array())
    {
        parent::setParams($params);

        // Load routes from ini config file
        // Load configs: filename, all sections, allowModifications
        $configs = XOOPS::service("registry")->route->read($this->getParam('section'));
        if (!empty($configs)) {
            $configs = new Zend_Config($configs);
            $this->addConfig($configs);
        }
    }

    /**
     * Initialize custom route
     *
     * @return Xoops_Zend_Controller_Router_Application
     */
    protected function initRoute($name)
    {
        if (isset($this->_routes[$name])) {
            return $this;
        }

        $dispatcher = $this->getFrontController()->getDispatcher();
        $request = $this->getFrontController()->getRequest();

        $key = ($name == 'default') ? "module" : $name;
        $class = "Zend_Controller_Router_Route_" . ucfirst($key);
        if (class_exists("Xoops_" . $class)) {
            $class = "Xoops_" . $class;
        } elseif (!class_exists($class)) {
            return $this;
        }
        $compat = new $class(array(), $dispatcher, $request);

        $this->_routes = array_merge(array($name => $compat), $this->_routes);

        return $this;
    }

    /**
     * Add default route
     *
     * @return Xoops_Zend_Controller_Router_Application
     */
    protected function addDefaultRoute()
    {
        $dispatcher = $this->getFrontController()->getDispatcher();
        $request = $this->getFrontController()->getRequest();
        $compat = new Zend_Controller_Router_Route_Module(array(), $dispatcher, $request);

        $this->_routes = array_merge(array('default' => $compat), $this->_routes);

        return $this;
    }

    /**
     * Create routes out of Zend_Config configuration
     *
     * Example INI:
     * archive.route = "archive/:year/*"
     * archive.defaults.controller = archive
     * archive.defaults.action = show
     * archive.defaults.year = 2000
     * archive.reqs.year = "\d+"
     *
     * news.type = "Zend_Controller_Router_Route_Static"
     * news.route = "news"
     * news.defaults.controller = "news"
     * news.defaults.action = "list"
     *
     * And finally after you have created a Zend_Config with above ini:
     * $router = new Zend_Controller_Router_Rewrite();
     * $router->addConfig($config, 'routes');
     *
     * @param  Zend_Config $config  Configuration object
     * @param  string      $section Name of the config section containing route's definitions
     * @throws Zend_Controller_Router_Exception
     * @return Zend_Controller_Router_Rewrite
     */
    public function addConfig(Zend_Config $config = null, $section = null)
    {
        if (!empty($config)) {
            parent::addConfig($config, $section);
        }

        return $this;
    }

    /**
     * Retrieve a named route
     *
     * @param string $name Name of the route
     * @throws Zend_Controller_Router_Exception
     * @return Zend_Controller_Router_Route_Interface Route object
     */
    public function loadRoute($name)
    {
        static $configIsLoaded;

        $this->initRoute($name);
        if (!isset($this->_routes[$name]) && !isset($configIsLoaded)) {
            $configs = XOOPS::service("registry")->route->read($this->getParam('section'), $exclude = 1);
            $configs = new Zend_Config($configs);
            $this->addConfig($configs);
        }
        return parent::getRoute($name);
    }

    /**
     * Find a matching route to the current PATH_INFO and inject
     * returning values to the Request object.
     *
     * @throws Zend_Controller_Router_Exception
     * @return Zend_Controller_Request_Abstract Request object
     */
    public function route(Zend_Controller_Request_Abstract $request)
    {
        if (!$request->isDispatched()) {
            // For route-specified routing
            if ($route = $this->getParam('route')) {
                if (!$this->hasRoute($route)) {
                    $route = $this->loadRoute($route);
                }
                //$routes = $this->getRoutes();
                //$this->_routes = array($this->route => $this->getRoute($this->route));
            }
            $request = parent::route($request);

            /*
            // Restore routes
            if (!empty($this->route)) {
                $this->_routes = $routes;
            }
            */
        }

        return $request;
    }

    /**
     * Generates a URL path that can be used in URL creation, redirection, etc.
     *
     * @param  array $userParams Options passed by a user used to override parameters
     * @param  mixed $name The name of a Route to use
     * @param  bool $reset Whether to reset to the route defaults ignoring URL params
     * @param  bool $encode Tells to encode URL parts on output
     * @throws Zend_Controller_Router_Exception
     * @return string Resulting absolute URL path
     */
    public function assemble($userParams, $name = null, $reset = true, $encode = true)
    {
        if ($name == null) {
            try {
                $name = $this->getCurrentRouteName();
            } catch (Zend_Controller_Router_Exception $e) {
                $name = empty($this->route) ? "default" : $name;
            }
        }

        if (!$reset) {
            $params = array_merge($this->_globalParams, $userParams);
        } else {
            $params = $userParams;
        }

        try {
            $route = $this->loadRoute($name);
            $url   = $route->assemble($params, $reset, $encode);
        } catch (Zend_Controller_Router_Exception $e) {
            $url = "";
            XOOPS::service("logger")->log($e->getMessage(), "WARN");
        }

        if (empty($url) || (!preg_match('|^[a-z]+://|', $url) && $url{0} != "/")) {
            $url = rtrim($this->getFrontController()->getBaseUrl(), '/') . '/' . $url;
        }

        return $url;
    }
}
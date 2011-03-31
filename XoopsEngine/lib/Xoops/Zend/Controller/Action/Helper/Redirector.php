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

class Xoops_Zend_Controller_Action_Helper_Redirector extends Zend_Controller_Action_Helper_Redirector
{
    /**
     * Whether or not _redirect() should attempt to prepend the base URL to the
     * passed URL (if it's a relative URL)
     * @var boolean
     */
    protected $_prependBase = false;

    /**
     * Set redirect in response object
     *
     * @return void
     */
    protected function _redirect($url)
    {
        if ($this->getUseAbsoluteUri() && !preg_match('#^(https?|ftp)://#', $url)) {
            $host  = (isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:'');
            $proto = (isset($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=="off") ? 'https' : 'http';
            $port  = (isset($_SERVER['SERVER_PORT'])?$_SERVER['SERVER_PORT']:80);
            $uri   = $proto . '://' . $host;
            if ((('http' == $proto) && (80 != $port)) || (('https' == $proto) && (443 != $port))) {
                $uri .= ':' . $port;
            }
            $url = $uri . '/' . ltrim($url, '/');
        }
        $this->_redirectUrl = $url;

        if (!$this->getExit()) return;
        $this->getResponse()->setRedirect($url, $this->getCode());
    }

    /**
     * Set a redirect URL of the form /module/controller/action/params
     *
     * @param  string $action
     * @param  string $controller
     * @param  string $module
     * @param  array  $params
     * @return void
     */
    public function setGotoSimple($action, $controller = null, $module = null, array $params = array())
    {
        $dispatcher = $this->getFrontController()->getDispatcher();
        $request    = $this->getRequest();
        $curModule  = $request->getModuleName();
        $useDefaultController = false;

        if (null === $controller && null !== $module) {
            $useDefaultController = true;
        }

        if (null === $module) {
            $module = $curModule;
        }

        if ($module == $dispatcher->getDefaultModule()) {
            $module = '';
        }

        if (null === $controller && !$useDefaultController) {
            $controller = $request->getControllerName();
            if (empty($controller)) {
                $controller = $dispatcher->getDefaultControllerName();
            }
        }

        $params['module']     = $module;
        $params['controller'] = $controller;
        $params['action']     = $action;

        $routeName = 'default';
        if (isset($params['route'])) {
            $routeName = $params['route'];
            unset($params['route']);
        }
        $router = $this->getFrontController()->getRouter();
        $url    = $router->assemble($params, $routeName, true);

        $this->_redirect($url);
    }

    /**
     * Build a URL based on a route
     *
     * @param  array   $urlOptions
     * @param  string  $name Route name
     * @param  boolean $reset
     * @param  boolean $encode
     * @return void
     */
    public function setGotoRoute(array $urlOptions = array(), $name = null, $reset = false, $encode = true)
    {
        $router = $this->getFrontController()->getRouter();
        $request = $this->getFrontController()->getRequest();
        if (!isset($urlOptions['module'])) {
            $urlOptions['module'] = $request->getModuleName();
        }
        if (!isset($urlOptions['controller'])) {
            $urlOptions['controller'] = $request->getControllerName();
        }
        if (!isset($urlOptions['action'])) {
            $urlOptions['action'] = $request->getActionName();
        }
        $url = $router->assemble($urlOptions, $name, $reset, $encode);

        $this->_redirect($url);
    }

    /**
     * Set a redirect URL string
     *
     * By default, emits a 302 HTTP status header, prepends base URL as defined
     * in request object if url is relative, and halts script execution by
     * calling exit().
     *
     * $options is an optional associative array that can be used to control
     * redirect behaviour. The available option keys are:
     * - exit: boolean flag indicating whether or not to halt script execution when done
     * - prependBase: boolean flag indicating whether or not to prepend the base URL when a relative URL is provided
     * - code: integer HTTP status code to use with redirect. Should be between 300 and 307.
     *
     * _redirect() sets the Location header in the response object. If you set
     * the exit flag to false, you can override this header later in code
     * execution.
     *
     * If the exit flag is true (true by default), _redirect() will write and
     * close the current session, if any.
     *
     * @param  string $url
     * @param  array  $options
     * @return void
     */
    public function setGotoUrl($url, array $options = array())
    {
        // prevent header injections
        $url = str_replace(array("\n", "\r"), '', $url);

        if (null !== $options) {
            if (isset($options['exit'])) {
                $this->setExit(($options['exit']) ? true : false);
            }
            if (isset($options['prependBase'])) {
                $this->setPrependBase(($options['prependBase']) ? true : false);
            }
            if (isset($options['code'])) {
                $this->setCode($options['code']);
            }
        }
        if ($this->getExit() && !empty($options['message'])) {
            if (false === strpos($options['message'], "?")) {
                $url .= "?message=" . $options['message'];
            } else {
                $url .= "&message=" . $options['message'];
            }
        }

        // If relative URL, decide if we should prepend base URL
        if (!preg_match('|^[a-z]+://|', $url) && $url{0} != "/") {
            $url = $this->_prependBase($url);
        }

        $this->_redirect($url);
    }


    /**
     * exit(): Perform exit for redirector
     *
     * @return void
     */
    public function redirectAndExit()
    {
        if ($this->getCloseSessionOnExit()) {
            // Close session, if started
            if (Xoops::service("session")->isStarted()) {
                Xoops::service("session")->writeClose();
            } elseif (isset($_SESSION)) {
                session_write_close();
            }
        }

        $this->getResponse()->sendHeaders();
        exit();
    }

    /**
     * Redirect to another URL
     *
     * @param string|array $url url or options to assemble a url
     * @param array $options Options to be used when redirecting
     * @return void
     */
    public function redirect($url, array $options = array())
    {
        if (!isset($options['exit'])) {
            $this->setExit(false);
        } else {
            $this->setExit($options['exit']);
            unset($options['exit']);
        }
        if (empty($options['time'])) {
            $this->setExit(true);
        }
        if (is_array($url)) {
            $route = isset($url['route']) ? $url['route'] : null;
            if (isset($url['route'])) {
                $route = $url['route'];
                unset($url['route']);
            } else {
                $route = null;
            }
            $reset = false;
            if (isset($url['reset'])) {
                $reset = $url['reset'];
                unset($url['reset']);
            }
            $this->gotoRoute($url, $route, $reset);
        } else {
            $this->gotoUrl($url, $options);
        }
        $options['url'] = $this->getRedirectUrl();
        $this->getActionController()->forward("redirect", "utility", "default", $options);
    }
}

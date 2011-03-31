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

class Xoops_Zend_Controller_Action_Helper_Confirm extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * HTTP status code for redirects
     * @var int
     */
    //protected $_code = 302;

    /**
     * Url to which to proceed after confirmation
     * @var string
     */
    protected $actionUrl = null;

    /**
     * Whether or not to use an absolute URI for goback
     * @var boolean
     */
    protected $_useAbsoluteUri = true;

    /**
     * Return use absolute URI flag
     *
     * @return boolean
     */
    public function getUseAbsoluteUri()
    {
        return $this->_useAbsoluteUri;
    }

    /**
     * Set use absolute URI flag
     *
     * @param  boolean $flag
     * @return Zend_Controller_Action_Helper_Redirector Provides a fluent interface
     */
    public function setUseAbsoluteUri($flag = true)
    {
        $this->_useAbsoluteUri = ($flag) ? true : false;
        return $this;
    }

    /**
     * Set absolute url
     *
     * @return void
     */
    protected function formulateUrl($url)
    {
        if (!preg_match('#^(https?|ftp)://#', $url)) {
            $host  = (isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:'');
            $proto = (isset($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=="off") ? 'https' : 'http';
            $port  = (isset($_SERVER['SERVER_PORT'])?$_SERVER['SERVER_PORT']:80);
            $uri   = $proto . '://' . $host;
            if ((('http' == $proto) && (80 != $port)) || (('https' == $proto) && (443 != $port))) {
                $uri .= ':' . $port;
            }
            $url = $uri . '/' . ltrim($url, '/');
        }
        return $url;
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
        /*
        $request = $this->getRequest();
        if (!isset($urlOptions['module'])) {
            $urlOptions['module'] = $request->getModuleName();
        }
        if (!isset($urlOptions['controller'])) {
            $urlOptions['controller'] = $request->getControllerName();
        }
        if (!isset($urlOptions['action'])) {
            $urlOptions['action'] = $request->getActionName();
        }
        */
        $url = $router->assemble($urlOptions, $name, $reset, $encode);
        return $url;
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
        }

        return $url;
    }

    /**
     * confirm and proceed to another URL
     *
     * @param array $options Options to be used when confirming:
     *          string      message     Confirmation prompting message to display on confirmation window
     *
     *          [string      form        Name for confirmation form, default as 'confirmForm']
     *          [string      action      Action URI for confirmation form]
     *          string      method      Method for confirmation form, default as 'post'
     *
     *          string      name        Name for confirmation select element
     *          int|array   options     MultiOptions for select element, default as 1 for Yesno
     *
     *          array       hidden      Appended data, associative array
     *
     * @param string|array      $url        URL or options to assemble a URL as confirmation form action
     * @param string|array|bool $urlGoback  Options to be used when generating goback url, true for generating automatically
     * @return void
     */
    public function confirm(array $options = array(), $url = array(), $urlGoback = true)
    {
        $helperObject = $this;
        $buildUrl = function ($url) use ($helperObject)
        {
            if (is_array($url) || empty($url)) {
                $url = (array) $url;
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
                $result = $helperObject->setGotoRoute($url, $route, $reset);
            } else {
                $result = $helperObject->setGotoUrl($url, $options);
            }

            return $result;
        };
        /*
        if (is_array($url) || empty($url)) {
            $url = (array) $url;
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
            $actionUrl = $this->setGotoRoute($url, $route, $reset);
        } else {
            $actionUrl = $this->setGotoUrl($url, $options);
        }
        $gobackUrl = null;
        if (!empty($urlGoback)) {
            if ($urlGoback === true) {
                $urlGoback = array();
            }
            if (is_array($urlGoback)) {
                $reset = false;
                if (isset($urlGoback['reset'])) {
                    $reset = $urlGoback['reset'];
                    unset($urlGoback['reset']);
                }
                $gobackUrl = $this->setGotoRoute($urlGoback, null, $reset);
            } else {
                $gobackUrl = $this->setGotoUrl($urlGoback);
            }
            //$gobackUrl = $this->formulateUrl($gobackUrl);
        }
        $options['action'] = $actionUrl;
        $options['goback'] = $gobackUrl;
        */
        $options['action'] = $buildUrl($url);
        $options['goback'] = empty($urlGoback) ? null : $buildUrl($urlGoback);
        $this->getActionController()->forward("confirm", "utility", "default", $options);
    }
}

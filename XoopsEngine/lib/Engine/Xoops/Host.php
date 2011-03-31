<?php
/**
 * XOOPS host and path container class
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
 * @package         Xoops_Core
 * @since           3.0
 * @version         $Id$
 */

namespace Engine\Xoops;

class Host implements \Kernel\HostInterface
{
    /**
     * Autoloader
     * @var {@Xoops_Zend_Loader_Autoloader}
     */
    //protected $autoloader;

    /**
     * Base URL, segment after baseLocation in installed URL which is: ($scheme:://$hostName[:$port])$baseUrl with leading slash
     * @var string
     */
    protected $baseUrl = "";

    /**
     * Base location: $scheme:://$hostName[:$port]
     * @var string
     */
    protected $baseLocation = "";

    /**
     * Defined paths and corresponding URLs
     * @var array
     */
    protected $paths = array(
    /*
        // document root
        'www'       => array(),
        // intermediate data
        'var'       => array(),
        // library
        'lib'       => array(),
        // static
        'img'       => array(),
        // applications
        'app'       => array(),
        // modules
        'module'    => array(),
        // plugins
        'plugin'    => array(),
        // applets
        'applet'    => array(),
        // uploads
        'upload'    => array(),
        // themes
        'theme'     => array(),
        */
    );

    /**
     * Constructor
     *
     * @param  array    $hostVars   configurations for virtual hosts or section name in host configuration file: null - to load from configuration file and look up automatically; string - to load rom configuration file with specified section; empty string - to skip host configuration
     * @return void
     */
    public function __construct($hostVars = array())
    {
        $this->setHosts($hostVars);

        /**#@+
         * For backward compat
         */
        // Backward compatibility
        /*
        defined("XOOPS_PATH") || define("XOOPS_PATH", $this->path('lib'));
        define("XOOPS_URL", $this->url('www'));
        define("XOOPS_ROOT_PATH", $this->path('www'));
        define("XOOPS_VAR_PATH", $this->path('var'));
        define("XOOPS_THEME_PATH", $this->path('www') . '/themes');
        define("XOOPS_THEME_URL", $this->url('www') . '/themes');
        define("XOOPS_UPLOAD_PATH", $this->path('www') . '/uploads');
        define("XOOPS_UPLOAD_URL", $this->url('www') . '/uploads');
        define("XOOPS_COMPILE_PATH", $this->path('var') . '/cache/smarty/compile');
        define("XOOPS_CACHE_PATH", $this->path('var') . '/cache/system');
        */
        /*#@-*/
    }

    /**
     * Set host data
     *
     * @param  array    $hostVars
     * @return void
     */
    public function setHosts($hostVars = null)
    {
        if (isset($hostVars["paths"])) {
            $this->paths = $hostVars["paths"];
        }
        if (isset($hostVars["location"]["baseLocation"])) {
            $this->baseLocation = $hostVars["location"]["baseLocation"];
        }
        if (isset($hostVars["location"]["baseUrl"])) {
            $this->baseUrl = $hostVars["location"]["baseUrl"];
        }
        return $this;
    }

    /**
     * Get baseLocation by lazy loading
     *
     * @see http://shiflett.org/blog/2006/mar/server-name-versus-http-host
     * @return string
     */
    public function getBaseLocation()
    {
        if (empty($this->baseLocation)) {
            $host = htmlspecialchars($_SERVER['HTTP_HOST']);
            // Secure connection
            if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
                $proto = 'https';
            } else {
                $proto = 'http';
            }
            $this->baseLocation =  $proto . '://' . $host;
        }
        return $this->baseLocation;
    }

    /**
     * Convert a XOOPS path to a physical one
     *
     * @param string    $url        XOOPS path:
     *                                  with URI scheme "://" - absolute URI, do not convert
     *                                  with reserved section separator ":" - do not convert, usually for paths in tempalte engine
     *                                  with leading slash "/" - absolute path, do not convert
     *                                  w/o "/" - relative path, will be translated
     * @param bool      $virtual    whether convert to full URI
     */
    public function path($url, $virtual = false)
    {
        if (isset($this->paths[$url])) {
            list($root, $path) = array($url, "");
        } else {

            // Return directly if absolute path
            // Absolute path under WIN
            if (strpos($url, ":") !== false
                // Absolute path under nix
                || $url{0} == "/") {
                return $url;
            }

            $path = '';
            $hasTrail = false;
            if (strpos($url, "/") === false) {
                list($root, $path) = array($url, "");
            } else {
                list($root, $path) = explode('/', $url, 2);
                $hasTrail = empty($path);
            }

            if (!isset($this->paths[$root])) {
                list($root, $path) = array('www', $url);
            }
            $path = (empty($path) ? "" : '/' . $path) . ($hasTrail ? "/" : "");
        }

        // Return a physical path
        if (!$virtual) {
            return $this->paths[$root][0] . $path;
        }

        // If path section is not defined, return null for invalide path
        if (is_null($this->paths[$root][1])) return null;

        // Return a virtual path
        $localBase = $this->paths[$root][1];

        if (empty($localBase)) {
            $url = $this->baseUrl . $path;
        } elseif (strpos($localBase, '://') === false && $localBase{0} != "/") {
            $url = $this->baseUrl . '/' . $localBase . $path;
        } else {
            $url = $localBase . $path;
        }
        return $url;
    }

    /**
     * Convert a XOOPS path to an URL
     *
     * @param string    $url        url to be converted: with leading slash "/" - absolute path, do not convert; w/o "/" - relative path, will be translated
     * @param bool      $absolute   whether convert to full URI; relative URI is used by default, i.e. no hostname
     */
    public function url($url, $absolute = false)
    {
        if (false === strpos($url, '://')) {
            $url = $this->path($url, true);
            if ($absolute && false === strpos($url, '://')) {
                $url = $this->baseLocation . '/' . ltrim($url, '/');
            }
        }
        return $url;
    }

    /**
     * Build an URL with the specified request params
     */
    public function buildUrl($url, $params = array())
    {
        if ($url == '.') {
            $url = $_SERVER['REQUEST_URI'];
        }
        if (false !== ($pos = strpos($url, "?"))) {
            $queryString = substr($url, $pos + 1);
            parse_str($queryString, $parameters);
            $params = array_merge($parameters, $params);
            $url = substr($url, 0, $pos);
        }
        if (!empty($params)) {
            $url .= "?" . http_build_query($params);
        }
        return $url;
    }

    /**
     * Build URL
     *
     * @param   array   $params
     * @param   string  $route  route name
     * @param   bool    $reset  Whether or not to reset the route defaults with those provided
     * @return  string  assembled URI
     */
    public function assembleUrl($params = array(), $route = 'default', $reset = true, $encode = true)
    {
        $route = $route ?: 'default';
        return \Xoops::registry("frontController")->getRouter()->assemble($params, $route, $reset, $encode);
    }

    /**
     * Build application URL
     *
     * @param   array   $params
     * @param   string  $route  route name
     * @param   bool    $reset  Whether or not to reset the route defaults with those provided
     * @return  string  assembled URI
     */
    public function appUrl($params = array(), $route = 'default', $reset = true, $encode = true)
    {
        $route = $route ?: 'default';
        return \Xoops::registry("frontController")->getRouter()->assemble($params, $route, $reset, $encode);
    }

    /**
     * Build URL mapping a locale resource
     *
     * @param   string  $domain     domain name, potential values: "", moduleName, theme:default, etc.
     * @param   string  $path       path to locale resource
     * @return  string  assembled URI
     */
    public function localeUrl($domain = "", $path = "")
    {
        $rawPath = \Xoops::service('translate')->getPath($domain);
        if (!empty($path)) {
            $rawPath = $rawPath . (empty($path) ? "" : "/" . $path);
            return $this->url($rawPath);
        }

        /*
        $info = \Xoops::service('translate')->loadInfo($domain);
        if (!empty($info)) {
            $keys = array_keys($info);
            $adapter = array_pop($keys);
            if (!empty($info[$adapter])) {
                $rawPath = $info[$adapter] . (empty($path) ? "" : "/" . $path);
                return $this->url($rawPath);
            }
        }
        */

        return false;
    }

    public function get($var)
    {
        if ('baseLocation' === $var) {
            return $this->getBaseLocation();
        }
        if (isset($this->$var)) {
            return $this->$var;
        }
        return null;
    }

    public function set($var, $value = null)
    {
        $this->$var = $value;
        return $this;
    }
}
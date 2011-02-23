<?php
/**
 * Kernel autoloader by prefix and namespace map
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
 * @package         Kernel
 * @since           3.0
 * @version         $Id$
 * @see             https://github.com/weierophinney/zf2/tree/autoloading/library/Zend/Loader
 */

namespace Kernel\Loader;

class StandardAutoloader implements SplAutoloader
{
    const NS_SEPARATOR      = '\\';
    const PREFIX_SEPARATOR  = '_';
    const LOAD_NS           = 'namespaces';
    const LOAD_PREFIX       = 'prefixes';
    const ACT_AS_FALLBACK   = 'fallback_autoloader';

    const NOT_EXIST         = '';

    /**
     * @var array Namespace/directory pairs to search; ZF library added by default
     */
    protected $namespaces = array();

    /**
     * @var array Prefix/directory pairs to search
     */
    protected $prefixes = array();

    /**
     * @var bool Whether or not the autoloader should also act as a fallback autoloader
     */
    protected $fallbackAutoloaderFlag = false;

    const PREFIX_APP    = 'App';
    const PREFIX_MODULE = 'Module';
    const PREFIX_PLUGIN = 'Plugin';
    const PREFIX_APPLET = 'Applet';
    protected $callbacks = array(
        'append'    => array(),
        'prepend'   => array(),
    );

    /**
     * Constructor
     *
     * @param  null|array|Traversable $options
     * @return void
     */
    public function __construct($options = null)
    {
        $this->prefixes = array(
            'Kernel' . self::PREFIX_SEPARATOR   => \XOOPS::ROOT . '/Kernel/',
            'Engine' . self::PREFIX_SEPARATOR   => \XOOPS::ROOT . '/Engine/',
            'Xoops' . self::PREFIX_SEPARATOR    => \XOOPS::ROOT . '/Xoops/',
        );
        $this->namespaces = array(
            'Kernel'. self::NS_SEPARATOR    => \XOOPS::ROOT . '/Kernel/',
            'Engine'. self::NS_SEPARATOR    => \XOOPS::ROOT . '/Engine/',
            'Xoops'. self::NS_SEPARATOR     => \XOOPS::ROOT . '/Xoops/',
        );

        if (null !== $options) {
            $this->setOptions($options);
        }
        //$this->setFallbackAutoloader(true);
    }

    /**
     * Register the autoloader with spl_autoload registry
     *
     * @param  boolean $throw whether spl_autoload_register should throw exceptions on error.
     * @param  boolean $prepend whether spl_autoload_register should prepend the autoloader on the autoload stack instead of appending it
     * @return void
     */
    public function register($throw = true, $prepend = false)
    {
        spl_autoload_register(array($this, 'autoload'), $throw, $prepend);
    }

    /**
     * Defined by Autoloadable; autoload a class
     *
     * @param  string $class
     * @return false|string
     */
    public function autoload($class)
    {
        $path = \XOOPS::persist()->loadClass($class);
        if (false !== $path) {
            if (self::NOT_EXIST === $path) {
                //trigger_error("Class '$class' is not found", E_USER_NOTICE);
            } elseif (!include $path) {
                trigger_error("Class '$class' is not loaded from '$path'");
            }
            return $class;
        }

        if (false !== ($pos = strpos($class, self::NS_SEPARATOR))) {
            $prefix = ucfirst(substr($class, 0, $pos));
            if (\Xoops::bound()) {
                // Loads app class
                if ($prefix === static::PREFIX_APP) {
                    return $this->loadClassApp($class, self::NS_SEPARATOR);
                }
                // Loads module class
                if ($prefix === static::PREFIX_MODULE) {
                    return $this->loadClassModule($class, self::NS_SEPARATOR);
                }
                // Loads plugin class
                if ($prefix === static::PREFIX_PLUGIN) {
                    return $this->loadClassPlugin($class, self::NS_SEPARATOR);
                }
                // Loads applet class
                if ($prefix === static::PREFIX_APPLET) {
                    return $this->loadClassApplet($class, self::NS_SEPARATOR);
                }
            }
            // Loads standard class with defined prefix
            if ($this->loadClass($class, self::LOAD_NS)) {
                return $class;
            }
            /*
            if (\Xoops::bound()) {
                //\Debug::e($class);
                // Loads module class
                if ($this->loadClassModule($class, self::NS_SEPARATOR)) {
                    return $class;
                }
            }
            */
            if ($this->isFallbackAutoloader()) {
                // Loads standard class by mapping to directories
                return $this->loadClass($class, self::ACT_AS_FALLBACK);
            }
            //\XOOPS::persist()->registerClass($class, self::NOT_EXIST);
            return false;
        }

        if (false !== ($pos = strpos($class, self::PREFIX_SEPARATOR))) {
            $prefix = ucfirst(substr($class, 0, $pos));
            if (\Xoops::bound()) {
                if ($prefix === static::PREFIX_APP) {
                    return $this->loadClassApp($class, self::PREFIX_SEPARATOR);
                }
                // Loads module class
                if ($prefix === static::PREFIX_MODULE) {
                    return $this->loadClassModule($class, self::PREFIX_SEPARATOR);
                }
                if ($prefix === static::PREFIX_PLUGIN) {
                    return $this->loadClassPlugin($class, self::PREFIX_SEPARATOR);
                }
                if ($prefix === static::PREFIX_APPLET) {
                    return $this->loadClassApplet($class, self::PREFIX_SEPARATOR);
                }
            }
            if ($this->loadClass($class, self::LOAD_PREFIX)) {
                return $class;
            }
            /*
            if (\Xoops::bound()) {
                if ($this->loadClassModule($class, self::PREFIX_SEPARATOR)) {
                    return $class;
                }
            }
            */
            if ($this->isFallbackAutoloader()) {
                return $this->loadClass($class, self::ACT_AS_FALLBACK);
            }
            \XOOPS::persist()->registerClass($class, self::NOT_EXIST);
            return false;
        }

        return $this->loadClass($class, self::ACT_AS_FALLBACK);
    }

    /**
     * Configure autoloader
     *
     * Allows specifying both "namespace" and "prefix" pairs, using the
     *
     * @param  array|Traversable $options
     * @return StandardAutoloader
     */
    public function setOptions($options)
    {
        if (!is_array($options) && !($options instanceof \Traversable)) {
            throw new \Exception('Options must be either an array or Traversable');
        }

        foreach ($options as $type => $pairs) {
            switch ($type) {
                case self::LOAD_NS:
                    if (is_array($pairs) || $pairs instanceof \Traversable) {
                        $this->registerNamespaces($pairs);
                    }
                    break;
                case self::LOAD_PREFIX:
                    if (is_array($pairs) || $pairs instanceof \Traversable) {
                        $this->registerPrefixes($pairs);
                    }
                    break;
                case self::ACT_AS_FALLBACK:
                    $this->setFallbackAutoloader($pairs);
                    break;
                default:
                    // ignore
            }
        }
        return $this;
    }

    /**
     * Set flag indicating fallback autoloader status
     *
     * @param  bool $flag
     * @return StandardAutoloader
     */
    public function setFallbackAutoloader($flag)
    {
        $this->fallbackAutoloaderFlag = (bool) $flag;
        return $this;
    }

    /**
     * Is this autoloader acting as a fallback autoloader?
     *
     * @return bool
     */
    public function isFallbackAutoloader()
    {
        return $this->fallbackAutoloaderFlag;
    }

    /**
     * Register a namespace/directory pair
     *
     * @param  string $namespace
     * @param  string $directory
     * @return StandardAutoloader
     */
    public function registerNamespace($namespace, $directory)
    {
        $namespace = rtrim($namespace, self::NS_SEPARATOR). self::NS_SEPARATOR;
        $this->namespaces[$namespace] = $this->normalizeDirectory($directory);
        return $this;
    }

    /**
     * Register many namespace/directory pairs at once
     *
     * @param  array $namespaces
     * @return StandardAutoloader
     */
    public function registerNamespaces($namespaces)
    {
        if (!is_array($namespaces) && !$namespaces instanceof \Traversable) {
            throw new \Exception('Namespace pairs must be either an array or Traversable');
        }

        foreach ($namespaces as $namespace => $directory) {
            $this->registerNamespace($namespace, $directory);
        }
        return $this;
    }

    /**
     * Register a prefix/directory pair
     *
     * @param  string $prefix
     * @param  string $directory
     * @return StandardAutoloader
     */
    public function registerPrefix($prefix, $directory)
    {
        $prefix = rtrim($prefix, self::PREFIX_SEPARATOR). self::PREFIX_SEPARATOR;
        $this->prefixes[$prefix] = $this->normalizeDirectory($directory);
        return $this;
    }

    /**
     * Register many namespace/directory pairs at once
     *
     * @param  array $prefixes
     * @return StandardAutoloader
     */
    public function registerPrefixes($prefixes)
    {
        if (!is_array($prefixes) && !$prefixes instanceof \Traversable) {
            throw new \Exception('Prefix pairs must be either an array or Traversable');
        }

        foreach ($prefixes as $prefix => $directory) {
            $this->registerPrefix($prefix, $directory);
        }
        return $this;
    }

    /**
     * Transform the class name to a filename
     *
     * @param  string $class
     * @param  string $directory
     * @return string
     */
    protected function transformClassNameToFilename($class, $directory)
    {
        return $directory
            . str_replace(
                array(self::NS_SEPARATOR, self::PREFIX_SEPARATOR),
                DIRECTORY_SEPARATOR,
                $class
            )
            . '.php';
    }


    /**
     * Normalize the directory to include a trailing directory separator
     *
     * @param  string $directory
     * @return string
     */
    protected function normalizeDirectory($directory)
    {
        $last = $directory[strlen($directory) - 1];
        if (in_array($last, array('/', '\\'))) {
            $directory[strlen($directory) - 1] = DIRECTORY_SEPARATOR;
            return $directory;
        }
        $directory .= DIRECTORY_SEPARATOR;
        return $directory;
    }

    /**
     * Determine if a file exists
     *
     * For PHP versions >= 5.3.2, utilizes stream_resolve_include_path().
     * Otherwise, loops through the elements of the include_path, prefixing
     * them to the filename; if a match is found, it is returned. Otherwise,
     * returns boolean false.
     *
     * @param mixed $filename
     * @return string|false
     */
    protected function fileExists($filename)
    {
        if (version_compare(PHP_VERSION, '5.3.2', '>=')) {
            return stream_resolve_include_path($filename);
        }

        if (file_exists($filename)) {
            return $filename;
        }

        foreach (explode(PATH_SEPARATOR, get_include_path()) as $path) {
            $resolvedName = $path . DIRECTORY_SEPARATOR . $filename;
            if (file_exists($resolvedName)) {
                return $resolvedName;
            }
        }
        return false;
    }

    /**
     * Load a class, based on its type (namespaced or prefixed)
     *
     * @param  string $class
     * @param  string $type
     * @return void
     */
    protected function loadClass($class, $type)
    {
        if (!in_array($type, array(self::LOAD_NS, self::LOAD_PREFIX, self::ACT_AS_FALLBACK))) {
            throw new \InvalidArgumentException();
        }

        // Fallback autoloading
        if ($type === self::ACT_AS_FALLBACK) {
            foreach ($this->callbacks['prepend'] as $callback) {
                if ($resolvedName = call_user_func($callback, $class)) {
                    \XOOPS::persist()->registerClass($class, $resolvedName);
                    return include $resolvedName;
                }
            }
            // create filename
            $filename = $this->transformClassNameToFilename($class, \XOOPS::ROOT . DIRECTORY_SEPARATOR);
            if (false !== ($resolvedName = $this->fileExists($filename))) {
                \XOOPS::persist()->registerClass($class, $resolvedName);
                return include $resolvedName;
            }
            foreach ($this->callbacks['append'] as $callback) {
                if ($resolvedName = call_user_func($callback, $class)) {
                    \XOOPS::persist()->registerClass($class, $resolvedName);
                    return include $resolvedName;
                }
            }
            \XOOPS::persist()->registerClass($class, self::NOT_EXIST);
            return false;
        }

        // Namespace and/or prefix autoloading
        foreach ($this->$type as $leader => $path) {
            if (0 === strpos($class, $leader)) {
                // Trim off leader (namespace or prefix)
                $trimmedClass = substr($class, strlen($leader));
                // create filename
                $filename = $this->transformClassNameToFilename($trimmedClass, $path);
                if (file_exists($filename)) {
                    \XOOPS::persist()->registerClass($class, $filename);
                    return include $filename;
                }
                return false;
            }
        }
        return false;
    }

    /**
     * Register a callback to StandardAutoloader
     *
     * @param array|string  $callback array of (class, method) or function
     * @param bool          $append  Is appendix to default PSR-0 transformation, or prior to it
     * @return
     */
    public function registerCallback($callback, $append = true)
    {
        $this->callbacks[$append ? 'append' : 'prepend'][] = $callback;
        return $this;
    }

    /**
     * Load a application (module) class
     *
     * @param  string $class
     * @param  string $separator
     * @return void
     */
    protected function loadClassApp($class, $separator)
    {
        $class = strtolower($class);
        $segs = explode($separator, $class, 3);
        $module = $segs[1];
        if ($path = $this->loadModulePath($module)) {
            $trimmedClass = $segs[2];
            // create filename
            $filename = $this->transformClassNameToFilename($trimmedClass, $path);
            if (file_exists($filename)) {
                \XOOPS::persist()->registerClass($class, $filename);
                return include $filename;
            }
        }
        \XOOPS::persist()->registerClass($class, self::NOT_EXIST);
        return false;
    }

    /**
     * Load a module class
     *
     * @param  string $class
     * @param  string $separator
     * @return void
     */
    protected function loadClassModule($class, $separator)
    {
        $class = strtolower($class);
        $segs = explode($separator, $class, 3);
        $module = $segs[1];
        if ($path = $this->loadModulePath($module)) {
            //$trimmedClass = strtolower(substr($class, $pos + strlen($separator)));
            $trimmedClass = $segs[2];
            // create filename
            $filename = $this->transformClassNameToFilename($trimmedClass, $path);
            if (file_exists($filename)) {
                \XOOPS::persist()->registerClass($class, $filename);
                return include $filename;
            }
            return false;
        }
        return false;
    }

    protected function loadModulePath($module = null)
    {
        $path = \XOOPS::service('module')->getPath($module);
        if (!empty($path)) {
            $path .= '/class/';
        }
        return $path;
    }

    protected function registerModulePath($module, $path)
    {
        //$module = strtolower($module);
        $persistKey = "autoloader.prefixes.app." . \XOOPS::config("identifier");
        if (!$paths = \XOOPS::persist()->load($persistKey)) {
            $list = \XOOPS::service('module')->getMeta();
            $paths = array();
            foreach ($list as $key => $app) {
                $paths[$key] = \XOOPS::service('module')->getPath($key) . '/class/';
            }
            \XOOPS::persist()->save($paths, $persistKey);
        }
        if (empty($module)) {
            return $paths;
        } else {
            return isset($paths[$module]) ? $paths[$module] : false;
        }
    }

    /**
     * Load a plugin class
     *
     * @param  string $class
     * @param  string $separator
     * @return void
     */
    protected function loadClassPlugin($class, $separator)
    {
        $segs = explode($separator, $class, 3);
        $plugin = $segs[1];
        if ($path = $this->loadPluginPath($plugin)) {
            $trimmedClass = strtolower($segs[2]);
            // create filename
            $filename = $this->transformClassNameToFilename($trimmedClass, $path);
            if (file_exists($filename)) {
                \XOOPS::persist()->registerClass($class, $filename);
                return include $filename;
            }
        }
        \XOOPS::persist()->registerClass($class, self::NOT_EXIST);
        return false;
    }

    protected function loadPluginPath($plugin = null)
    {
        $plugin = strtolower($plugin);
        return \Xoops::path('plugin') . '/' . $plugin . '/';
    }

    /**
     * Load an applet class
     *
     * @param  string $class
     * @param  string $separator
     * @return void
     */
    protected function loadClassApplet($class, $separator)
    {
        $segs = explode($separator, $class, 2);
        $path = \XOOPS::path('applet') . '/';
        $trimmedClass = strtolower($segs[1]);
        // create filename
        $filename = $this->transformClassNameToFilename($trimmedClass, $path);
        if (file_exists($filename)) {
            \XOOPS::persist()->registerClass($class, $filename);
            return include $filename;
        }
        \XOOPS::persist()->registerClass($class, self::NOT_EXIST);
        return false;
    }
}
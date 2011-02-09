<?php
/**
 * Kernel autoloader
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
 */

/**
 * Naming conventions:
 *  Camel case for kernel and engine generic libraries
 *  Lower case for app/module/plugin/applet classes
 */

namespace Kernel\Loader;

class Autoloader
{
    /**
     * @var array All autoloaders registered using the factory
     */
    protected static $loaders = array();

    /**
     * @var StandardAutoloader StandardAutoloader instance for resolving
     * autoloader classes via the include_path
     */
    protected static $standardAutoloader;

    /**
     * @var classMapAutoloader ClassMapAutoloader instance for resolving
     * autoloader classes via the include_path
     */
    protected static $classMapAutoloader;

    public function __construct()
    {
        set_include_path(implode(PATH_SEPARATOR, array(
            \XOOPS::ROOT,
            get_include_path(),
        )));
        self::getStandardAutoloader();
        self::getClassMapAutoloader();
    }

    public function dispatch($class)
    {
        //echo "<br />" . __METHOD__ .' -> '. $class;
    }

    /**
     * Factory for autoloaders
     *
     * Options should be an array or Traversable object of the following structure:
     * <code>
     * array(
     *     '<autoloader class name>' => $autoloaderOptions,
     * )
     * </code>
     *
     * The factory will then loop through and instantiate each autoloader with
     * the specified options, and register each with the spl_autoloader.
     *
     * You may retrieve the concrete autoloader instances later using
     * {@link getRegisteredAutoloaders()}.
     *
     * Note that the class names must be resolvable on the include_path or via
     * the Zend library, using PSR-0 rules (unless the class has already been
     * loaded).
     *
     * @param  array|Traversable $options
     * @return void
     */
    public function registerAutoloader($options)
    {
        if (!is_array($options) && !($options instanceof \Traversable)) {
            throw new Exception('Options provided must be an array or Traversable');
        }

        foreach ($options as $class => $opts) {
            if (!class_exists($class)) {
                if (!self::getStandardAutoloader()->autoload($class)) {
                    throw new Exception(sprintf('Autoloader class "%s" not loaded', $class));
                }
            }
            $loader = new $class($opts);
            if (!$loader instanceof SplAutoloader) {
                throw new Exception(sprintf('Autoloader class "%s" does not implement Zend\Loader\SplAutoloader', $class));
            }
            $loader->register();
            self::$loaders[] = new $loader;
        }
    }

    /**
     * Get an list of all autoloaders registered with the factory
     *
     * Returns an array of autoloader instances.
     *
     * @return array
     */
    public function getRegisteredAutoloaders()
    {
        return static::$loaders;
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
        static::getStandardAutoloader()->registerCallback($callback, $append);
    }

    /**
     * Register an autoload map
     *
     * An autoload map may be either an associative array, or a file returning
     * an associative array.
     *
     * An autoload map should be an associative array containing
     * classname/file pairs.
     *
     * @param  string|array $location
     * @return Autoloader
     */
    public function registerMap($map)
    {
        static::getClassMapAutoloader()->registerAutoloadMap($map);
        return $this;
    }

    /**
     * Register a namespace/directory pair
     *
     * @param  string $namespace
     * @param  string $directory
     * @return StandardAutoloader
     */
    public function registerNamespace($namespace, $directory = null)
    {
        static::getStandardAutoloader()->registerNamespace($namespace, $directory);
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
        static::getStandardAutoloader()->registerNamespaces($namespaces);
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
        static::getStandardAutoloader()->registerPrefix($prefix, $directory);
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
        static::getStandardAutoloader()->registerPrefixes($prefixes);
        return $this;
    }

    /**
     * Get an instance of the standard autoloader
     *
     * Used to attempt to resolve autoloader classes, using the
     * StandardAutoloader. The instance is marked as a fallback autoloader, to
     * allow resolving autoloaders not under the "Zend" namespace.
     *
     * @return SplAutoloader
     */
    protected static function getClassMapAutoloader()
    {
        if (null !== self::$classMapAutoloader) {
            return self::$classMapAutoloader;
        }
        if (!class_exists("ClassMapAutoloader", false)) {
            require __DIR__ . '/ClassMapAutoloader.php';
        }
        $loader = new ClassMapAutoloader();
        $loader->register();
        self::$classMapAutoloader = $loader;
        return self::$classMapAutoloader;
    }

    /**
     * Get an instance of the standard autoloader
     *
     * Used to attempt to resolve autoloader classes, using the
     * StandardAutoloader. The instance is marked as a fallback autoloader, to
     * allow resolving autoloaders not under the "Zend" namespace.
     *
     * @return SplAutoloader
     */
    protected static function getStandardAutoloader()
    {
        if (null !== self::$standardAutoloader) {
            return self::$standardAutoloader;
        }

        if (!class_exists("StandardAutoloader", false)) {
            require __DIR__ . '/StandardAutoloader.php';
        }
        $loader = new StandardAutoloader();
        $loader->setFallbackAutoloader(true);
        $loader->register();
        self::$standardAutoloader = $loader;
        return self::$standardAutoloader;
    }
}


interface SplAutoloader
{
    /**
     * Constructor
     *
     * Allow configuration of the autoloader via the constructor.
     *
     * @param  null|array|Traversable $options
     * @return void
     */
    public function __construct($options = null);

    /**
     * Configure the autoloader
     *
     * In most cases, $options should be either an associative array or
     * Traversable object.
     *
     * @param  array|Traversable $options
     * @return SplAutoloader
     */
    public function setOptions($options);

    /**
     * Register the autoloader with spl_autoload registry
     *
     * Typically, the body of this will simply be:
     * <code>
     * spl_autoload_register(array($this, 'autoload'));
     * </code>
     *
     * @return void
     */
    //public function register();
    public function register($throw = true, $prepend = false);
}

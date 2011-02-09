<?php
/**
 * Kernel autoloader by class map
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

class ClassMapAutoloader implements SplAutoloader
{
    /**
     * Registry of map files that have already been loaded
     * @var array
     */
    protected $mapsLoaded = array();

    /**
     * Class name/filename map
     * @var array
     */
    protected $map = array();
    /**
     * Constructor
     *
     * @param  null|array|Traversable $options
     * @return void
     */
    public function __construct($options = null)
    {
        $this->registerAutoloadMap($this->loadCoreMap());

        if (null !== $options) {
            $this->setOptions($options);
        }
    }

    /**
     * Register the autoloader with spl_autoload registry
     *
     * @param  boolean $throw whether spl_autoload_register should throw exceptions on error.
     * @param  boolean $prepend whether spl_autoload_register should prepend the autoloader on the autoload stack instead of appending it
     * @return void
     */
    public function register($throw = true, $prepend = true)
    {
        spl_autoload_register(array($this, 'autoload'), $throw, $prepend);
    }

    /**
     * Configure the autoloader
     *
     * Proxies to {@link registerAutoloadMaps()}.
     *
     * @param  array|Traversable $options
     * @return ClassMapAutoloader
     */
    public function setOptions($options)
    {
        $this->registerAutoloadMaps($options);
        return $this;
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
     * @return ClassMapAutoloader
     */
    public function registerAutoloadMap($map)
    {
        if (is_string($map)) {
            $location = $map;
            if ($this === ($map = $this->loadMapFromFile($location))) {
                return $this;
            }
        }

        if (!is_array($map)) {
            throw new \Exception('Map file provided does not return a map');
        }

        $this->map = array_merge($this->map, $map);

        if (isset($location)) {
            $this->mapsLoaded[] = $location;
        }

        return $this;
    }

    /**
     * Register many autoload maps at once
     *
     * @param  array $locations
     * @return ClassMapAutoloader
     */
    public function registerAutoloadMaps($locations)
    {
        if (!is_array($locations) && !($locations instanceof \Traversable)) {
            throw new \Exception('Map list must be an array or implement Traversable');
        }
        foreach ($locations as $location) {
            $this->registerAutoloadMap($location);
        }
        return $this;
    }

    /**
     * Retrieve current autoload map
     *
     * @return array
     */
    public function getAutoloadMap()
    {
        return $this->map;
    }

    /**
     * Defined by Autoloadable
     *
     * @param  string $class
     * @return void
     */
    public function autoload($class)
    {
        if (isset($this->map[$class])) {
            include $this->map[$class];
        }
    }

    /**
     * Load a map from a file
     *
     * If the map has been previously loaded, returns the current instance;
     * otherwise, returns whatever was returned by calling include() on the
     * location.
     *
     * @param  string $location
     * @return ClassMapAutoloader|mixed
     * @throws Exception for nonexistent locations
     */
    protected function loadMapFromFile($location)
    {
        if (!file_exists($location)) {
            throw new \Exception('Map file provided does not exist');
        }

        $location = realpath($location);

        if (in_array($location, $this->mapsLoaded)) {
            // Already loaded this map
            return $this;
        }

        $map = include $location;

        return $map;
    }

    public function loadCoreMap()
    {
        $root = dirname(__DIR__);
        $map = array(
            'Kernel\\EngineInterface'   => $root . '/EngineInterface.php',
            'Kernel\\HostInterface'     => $root . '/HostInterface.php',
            'Kernel\\Persist'           => $root . '/Persist.php',
            'Kernel\\Service'           => $root . '/Service.php',
            'Debug'                     => $root . '/Debug.php',
        );
        return $map;
    }
}
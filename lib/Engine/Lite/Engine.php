<?php
/**
 * Engine class
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       The Xoops Engine http://sourceforge.net/projects/xoops/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @package         Kernel
 * @since           3.0
 * @version         $Id$
 */

namespace Engine\Lite;

/**
 * Lite kernel engine
 *
 * Tasks: boot, shutdown; load hosts, configs; set paths, loadClass, loadService, loadConfig
 *
 * @author      Taiwen Jiang <phppp@users.sourceforge.net>
 */

class Engine extends \Engine\Xoops\Engine//implements \Kernel\EngineInterface
{
    /**
     * @var string
     *
     * Versioning schema: uses GNU version numbering scheme: major.minor.revision[state]
     * Version number: segments separated by "."
     *  Major       - increment in first segment, 3.0.0
     *  Minor       - increment in second segment, 3.1.0
     *  Revision    - increment in third segment, 3.1.1
     * Development state:
     *  Production  - "final" or not specified
     *  RC          - "rc" or "rc" + {number}: rc2
     *  Beta        - "beta" or "beta" + {number}: beta2
     *  Alpha       - "alpha" or "alpha" + {number}: alpha3
     *  Dev         - "dev" or "dev" + {number}: dev
     *  Preview     - Informal release, "preview" or "preview" + {number}: preview5
     * A full version number looks like: 3.0.0rc2
     */
    const VERSION = 'Lite Engine 1.0';

    /**
     * Loaded system configs
     * @var assoaciative array
     */
    protected $configs = array(
        // Identifier of system engine, set on installation
        "identifier"    => "lite",
        // Salt for encryption, created on installation
        "salt"          => "xo441c889f6e25003dba02caf7b0bec764",
        // Run environment
        "environment"   => "debug"
    );

    protected function registerAutoloader()
    {
        /*
        $persistKey = "autoloader.classmap.core." . $this->config('identifier');
        if (!$map = \XOOPS::persist()->load($persistKey)) {
            $map = array(
                "Application\\Controller"    => __DIR__ . '/Application/Controller.php',
                "Application\\Plugin"        => __DIR__ . '/Application/Plugin.php',
                "Application\\Applet"        => __DIR__ . '/Application/Applet.php',
            );
            $iterator = new \DirectoryIterator(\XOOPS::ROOT . '/Xoops/Core');
            foreach ($iterator as $fileinfo) {
                if (!$fileinfo->isFile() || $fileinfo->isDot()) {
                    continue;
                }
                $baseName = $fileinfo->getFileInfo()->getBasename(".php");
                $map["Xoops_" . $baseName] = $fileinfo->getRealPath();
            }
            \XOOPS::persist()->save($map, $persistKey);
        }
        */
        $map = array(
            "Application\\Controller"    => __DIR__ . '/Application/Controller.php',
            "Application\\Plugin"        => __DIR__ . '/Application/Plugin.php',
            "Application\\Applet"        => __DIR__ . '/Application/Applet.php',
        );
        \XOOPS::autoloader()->registerMap($map);
        return $this;
    }

    /**
     * Perform the boot sequence
     *
     * The following operations are done in order during the boot-sequence:
     * - Load system bootstrap config file
     * - Load primary services
     * - Application bootstrap
     *
     * @access public
     * @return string   path to boot file
     */
    public function boot($bootstrap = null)
    {
        // Set run environment
        // Defined in configuration
        if (defined('APPLICATION_ENV')) {
            $this->configs['environment'] = APPLICATION_ENV;
        // Defined via system variable
        } elseif (getenv('APPLICATION_ENV')) {
            $this->configs['environment'] = getenv('APPLICATION_ENV');
        }

        try {
            // Load prerequisite basic services
            $services = isset($this->configs['services']) ? $this->configs['services'] : array();
            foreach ($services as $name) {
                \XOOPS::service()->load($name);
            }
        } catch (Exception $e) {
            echo "Exception in basic service: <pre>" . $e->getMessage() . "</pre>";
            if (\Xoops::service()->hasService('error')) {
                \Xoops::service('error')->handleException($e);
            }
        }

        try {
            try {
                $options = array(
                    "autoloader"    => \XOOPS::autoloader(),
                    "bootfile"      => $bootstrap,
                    "engine"        => $this,
                );
                $application = new \Lite_Zend_Application($this->configs["environment"], $options);
            } catch (Exception $e) {
                echo "Exception: <pre>" . $e->getMessage() . "</pre>";
                if (\Xoops::service()->hasService('error')) {
                    \Xoops::service('error')->handleException($e);
                }
            }
            $this->registry('application', $application);
            \XOOPS::service("profiler")->start(__METHOD__ . '=> bootstrap');
            $application->bootstrap();
            \XOOPS::service("profiler")->stop(__METHOD__ . '=> bootstrap');
            \XOOPS::service("profiler")->start(__METHOD__ . '=> run');
            $application->run();
            \XOOPS::service("profiler")->stop(__METHOD__ . '=> run');
        } catch (Exception $e) {
            echo "Exception: <pre>" . $e->getMessage() . "</pre>";
            if (\Xoops::service()->hasService('error')) {
                \Xoops::service('error')->handleException($e);
            }
        }

        // Register shutdown functions for this application
        register_shutdown_function(array(&$this, 'shutdown'));
    }
}
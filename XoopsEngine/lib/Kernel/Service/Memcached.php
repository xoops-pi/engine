<?php
/**
 * Kernel service
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
 * @package         Kernel/Service
 * @version         $Id$
 */

namespace Kernel\Service;

class Memcached extends ServiceAbstract
{
    protected static $instances = array();
    const DEFAULT_PORT =  11211;
    const DEFAULT_WEIGHT  = 1;

    protected function loadOptions($config)
    {
        if (is_string($config)) {
            $config = \Xoops::loadConfig("memcached." . $config . ".ini.php");
        }

        $options = array();
        if (!empty($config['client'])) {
            $clients = array();
            // setup memcached client options
            foreach ($config['client'] as $name => $value) {
                $optId = null;
                if (is_int($name)) {
                    $optId = $name;
                } else {
                    $optConst = 'Memcached::OPT_' . strtoupper($name);
                    if (defined($optConst)) {
                        $optId = constant($optConst);
                    } else {
                        trigger_error("Unknown memcached client option '{$name}' ({$optConst})");
                    }
                }
                if ($optId) {
                    if (is_string($value)) {
                        $memcachedValue = 'Memcached::' . strtoupper($value);
                        $value = defined($memcachedValue) ? constant($memcachedValue) : $value; // For Memcached predefined constants, see http://www.php.net/manual/en/memcached.constants.php
                    }
                    $clients[$optId] = $value;
                }
            }
            if (!empty($clients)) {
                $options['clients'] = $clients;
            }
            unset($config['client']);
        }

        // setup memcached servers
        $serverList = isset($config['servers']) ? $config['servers'] : $config;
        if (isset($serverList['host'])) {
            $serverList = array(0 => $serverList); // Transform it into associative arrays
        }
        $servers = array();
        foreach ($serverList as $idx => $server) {
            if (!array_key_exists('port', $server)) {
                $server['port'] = static::DEFAULT_PORT;
            }
            if (!array_key_exists('weight', $server)) {
                $server['weight'] = static::DEFAULT_WEIGHT;
            }
            $servers[] = array($server['host'], $server['port'], $server['weight']);
        }
        if (!empty($servers)) {
            $options['servers'] = $servers;
        } else {
            $options = array();
        }

        return $options;
    }

    public function load($config = null)
    {
        if (!extension_loaded('memcached')) {
            throw new exception('Memcached extension is not available!');
        }
        // Load default Memcached handler from Xoops::persist to keep consistency
        if (empty($config)) {
            return \Xoops::persist()->loadHandler("Memcached")->getEngine();
        }

        $configKey = is_array($config) ? serialize($config) : $config;
        if (isset(static::$instances[$configKey])) {
            return static::$instances[$configKey];
        }

        static::$instances[$configKey] = false;
        $options = $this->loadOptions($config);
        if (empty($options)) {
            throw new \exception('No valid options!');
        }
        $memcached = new \Memcached;
        if (!empty($options['client'])) {
            // setup memcached client options
            foreach ($options['client'] as $optId => $value) {
                if (!$memcached->setOption($optId, $value)) {
                    trigger_error("Setting memcached client option '{$optId}' failed");
                }
            }
        }
        $memcached->addServers($options['servers']);
        static::$instances[$configKey] = $memcached;

        return static::$instances[$configKey];
    }
}
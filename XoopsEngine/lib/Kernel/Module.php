<?php
/**
 * Kernel module
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

namespace Kernel;

abstract class Module
{
    protected $dirname;
    protected $model;
    protected $configs = array();

    /**
     * Constructor
     *
     * @param string|array  $data   dirname or prperty data array
     * @return void
     */
    public function __construct($data)
    {
        if (is_string($data)) {
            $this->dirname = $data;
            $this->model = $this->loadModel();
        }
        if (is_object($data)) {
            $this->model = $data;
            $this->dirname = $this->model->dirname;
        }
        if (!isset($this->model)) {
            throw new \Exception('Model is not specified');
        }
    }

    /**
     * Get configuration data
     *
     * @param string  $key configuration name
     * @param string  $category configuration category
     * @return
     */
    public function config($key = null, $category = '')
    {
        if (!isset($this->configs[$category])) {
            $this->configs[$category] = $this->readConfig($category);
        }
        if (null === $key) {
            return $this->configs[$category];
        }
        return isset($this->configs[$category][$key]) ? $this->configs[$category][$key] : null;
    }

    /**
     * Set configuration data
     *
     * @param array     $configs array of config data
     * @param string    $category configuration category
     * @return
     */
    public function SetConfigs($configs, $category = '')
    {
        $this->configs[$category] = array_merge($this->configs[$category], $configs);
        return $this;
    }

    /**
     * Load model data
     *
     * @return
     */
    public function loadModel()
    {
        throw new \Exception('The abstract method can not be accessed directly');
        return $this->model;
    }

    /**
     * Get property data
     */
    public function __get($key)
    {
        return $this->model->{$key};
    }

    /**
     * Load config data
     */
    public function loadConfig($category = '')
    {
        return $this->config(null, $category);
        /*
        if (!isset($this->configs[$category])) {
            $this->configs[$category] = $this->readConfig($category);
        }
        return $this->configs[$category];
        */
    }

    /**
     * Read config data from storage
     */
    public function readConfig($category)
    {
        throw new \Exception('The abstract method can not be accessed directly');
        return $this;
    }
}
<?php
/**
 * Xoops Engine Editor Abstract
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
 * @package         Xoops_Editor
 * @version         $Id$
 */

namespace Xoops\Editor;

abstract class AbstractEditor //implements Item
{
    protected $id;
    protected $name;
    protected $value;
    protected $attribs;
    protected $config;
    protected $configFile;
    protected $upload = array(
        'enabled'   => false,
        'path'      => '',
    );

    /**
     * Constructor
     *
     * @param  array $options
     * @return void
     */
    public function __construct($options = array())
    {
        $config = array();
        if (isset($options['config'])) {
            $config = $options['config'];
            unset($options['config']);
        }
        $this->setConfig($config);
        foreach ($options as $key => $val) {
            $this->$key = $val;
        }
    }

    /**
     * Sets config data
     *
     * @param  array $config
     * @return string
     */
    public function setConfig($config = array())
    {
        if (!empty($this->configFile)) {
            $config = array_merge((array) \Xoops::loadConfig($this->configFile), $config);
        }
        $this->config = $config;
    }

    /**
     * Renders editor contents
     *
     * @param  Zend_View_Abstract $view
     * @return string
     */
    public function render(\Zend_View_Interface $view)
    {
        throw new Exception('Access the method is denied.');
    }
}
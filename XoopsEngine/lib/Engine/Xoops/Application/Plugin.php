<?php
/**
 * Application plugin abstract
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

namespace Application;

class Plugin extends \Zend_Controller_Action_Helper_Abstract
{
    protected $name;

    /**
     * options
     * @var array
     */
    protected $options = array();

    /**
     * Whether or not to activate the service
     * @var boolean
     */
     public $active = true;

    /**
     * Constructor
     *
     * @param array     $options    Parameters to send to the plugin during instanciation
     */
    public function __construct($options = array())
    {
        $this->setOptions($options);
    }

    /**
     * @param array    $options
     */
    public function setOptions($options = array())
    {
        if (is_array($options)) {
            $this->options = $options;
        }
    }

    /**
     * getName()
     *
     * @return string
     */
    public function getName()
    {
        if (!isset($this->name)) {
            $full_class_name = get_class($this);

            if (($pos = strpos($full_class_name, '_', 1)) !== false) {
                $section = substr($full_class_name, 0, 6);
                if (strtolower($section) == "plugin") {
                    $helper_name = substr($full_class_name, 7, $pos + 1);
                    $this->name = $helper_name;
                }
            }
            if (!isset($this->name)) {
                $this->name = $full_class_name;
            }
        }
        return $this->name;
    }

    public function disable()
    {
        $this->active = false;
        return $this;
    }

    public function enable()
    {
        $this->active = true;
        return $this;
    }
}
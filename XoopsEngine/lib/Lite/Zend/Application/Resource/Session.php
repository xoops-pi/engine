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
 * @copyright       The Xoops Engine http://sourceforge.net/projects/xoops/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @since           3.0
 * @category        Xoops_Zend
 * @package         Application
 * @subpackage      Resource
 * @version         $Id$
 */

class Lite_Zend_Application_Resource_Session extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * Save handler to use
     *
     * @var Zend_Session_SaveHandler_Interface
     */
    protected $_saveHandler = null;

    /**
     * Set session save handler
     *
     * @param  array|string|Zend_Session_SaveHandler_Interface $saveHandler
     * @return Zend_Application_Resource_Session
     * @throws Zend_Application_Resource_Exception When $saveHandler is no valid save handler
     */
    public function setSaveHandler($saveHandler)
    {
        $this->_saveHandler = $saveHandler;
        return $this;
    }

    /**
     * Set session save handler
     *
     * @param  array|string|Zend_Session_SaveHandler_Interface $saveHandler
     * @return Zend_Application_Resource_Session
     * @throws Zend_Application_Resource_Exception When $saveHandler is no valid save handler
     */
    protected function loadSaveHandler()
    {
        $saveHandler = $this->_saveHandler;
        if (is_array($saveHandler)) {
            if (!array_key_exists('class', $saveHandler)) {
                throw new Zend_Application_Resource_Exception('Session save handler class not provided in options');
            }
            if (array_key_exists('options', $saveHandler)) {
                $options = $saveHandler['options'];
            } else {
                $options = array();
            }
            $handlerClass = $saveHandler['class'];
            $saveHandler = new $handlerClass($options);
        } elseif (is_string($saveHandler)) {
            $handlerClass = "Zend_Session_SaveHandler_" . ucfirst($saveHandler);
            if (class_exists("Xoops_" . $handlerClass)) {
                $handlerClass = "Xoops_" . $handlerClass;
                $saveHandler = new $handlerClass();
            } elseif (class_exists($handlerClass)) {
                $saveHandler = new $handlerClass();
            }
        }

        if (!$saveHandler instanceof Zend_Session_SaveHandler_Interface) {
            return null;
        }

        return $saveHandler;
    }

    /**
     * Defined by Zend_Application_Resource_Resource
     *
     * @return void
     */
    public function init()
    {
        $options = array_change_key_case($this->getOptions(), CASE_LOWER);
        if (isset($options['savehandler'])) {
            unset($options['savehandler']);
        }

        if (!isset($options["cookie_path"]) && $baseUrl = XOOPS::host()->get('baseUrl')) {
            $options["cookie_path"] = rtrim($baseUrl, "/") . "/";
        }
        Xoops::service("session")->setOptions($options);

        if ($this->_saveHandler !== null) {
            $saveHandler = $this->loadSaveHandler();
            Xoops::service("session")->setSaveHandler($saveHandler);
        }
        Xoops::service("session")->start();
    }
}
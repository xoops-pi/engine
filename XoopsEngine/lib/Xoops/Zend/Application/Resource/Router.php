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
 * @copyright       Xoops Engine http://www.xoopsengine.org/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @since           3.0
 * @category        Xoops_Zend
 * @package         Application
 * @subpackage      Resource
 * @version         $Id$
 */

class Xoops_Zend_Application_Resource_Router
    extends Zend_Application_Resource_Router
{
    /**
     * Retrieve router object
     *
     * @return Zend_Controller_Router_Rewrite
     */
    public function getRouter()
    {
        if (null === $this->_router) {
            $options = $this->getOptions();
            $name = empty($options['name']) ? "Application" : ucfirst($options['name']);
            unset($options['name']);
            $router = "Xoops_Zend_Controller_Router_" . $name;
            $bootstrap = $this->getBootstrap();
            $bootstrap->bootstrap('db');
            $bootstrap->bootstrap('FrontController');
            $front = $bootstrap->getResource('FrontController');
            $front->setRouter(new $router);
            $this->_router = $front->getRouter();

            if (isset($options['chainNameSeparator'])) {
                $this->_router->setChainNameSeparator($options['chainNameSeparator']);
                unset($options['chainNameSeparator']);
            }

            if (isset($options['useRequestParametersAsGlobal'])) {
                $this->_router->useRequestParametersAsGlobal($options['useRequestParametersAsGlobal']);
                unset($options['useRequestParametersAsGlobal']);
            }

            if (!empty($options['routes'])) {
                $this->_router->addConfig(new Zend_Config($options['routes']));
                unset($options['routes']);
            }

            if (!empty($options)) {
                $this->_router->setParams($options);
            }
            /*
            if (!empty($options['route'])) {
                $this->_router->route = $options['route'];
            }

            if (!empty($options['section'])) {
                $this->_router->section = $options['section'];
            }
            */
        }
        return $this->_router;
    }
}

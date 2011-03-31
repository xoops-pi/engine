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

class Xoops_Zend_Application_Resource_View extends Zend_Application_Resource_ResourceAbstract
{
    protected $viewClass            = 'Xoops_Zend_View';
    protected $viewRendererClass    = 'Xoops_Zend_Controller_Action_Helper_ViewRenderer';
    protected $layoutClass          = 'Xoops_Zend_Layout';

    /**
     * Defined by Zend_Application_Resource_Resource
     *
     * @return Zend_View
     */
    public function init()
    {
        /*
         The option skelton:
            name

            template
                caching
                compile_check
                debugging
                force_compile
                template_class
                error_unassigned


            layout
                enabled
                name
                theme

                cache
                    frontend
                    backend
                    frontendOptions
                    backendOptions
        */
        $options = $this->getOptions();

        $options_layout = array();
        if (isset($options['layout'])) {
            $options_layout = $options['layout'];
            unset($options['layout']);
        }

        // Initialize and register layout
        $this->getLayout($options_layout);

        // viewRenderer options
        $options_renderer = array();
        if (isset($options['viewRenderer'])) {
            $options_renderer = $options['viewRenderer'];
            unset($options['viewRenderer']);
        }

        // Initialize view
        $view = $this->getView($options);

        // Initialize and register layout
        $this->getRenderer($options_renderer, $view);

        return $view;
    }

    /**
     * Retrieve view object
     *
     * @return Xoops_Zend_View
     */
    protected function getView($options)
    {
        $class = empty($options['class']) ? $this->viewClass : $options['class'];
        $view = new $class($options);
        XOOPS::registry('view', $view);
        return $view;
    }

    /**
     * Retrieve layout object
     *
     * @return Xoops_Zend_Layout
     */
    protected function getLayout($options)
    {
        // Initialize and register layout
        if (empty($options['theme'])) {
            $configName = (!empty($options['type']) && 'admin' == $options['type']) ? 'cpanel' : 'theme_set';
            $options['theme'] = XOOPS::config($configName);
        }

        $initMvc = true;
        if (isset($options['initMvc'])) {
            $initMvc = (bool) $options['initMvc'];
            unset($options['initMvc']);
        }

        $class = empty($options['class']) ? $this->layoutClass : $options['class'];
        if ($initMvc) {
            $layout = $class::startMvc($options);
        } else {
            $layout = new $class($options);
        }
        XOOPS::registry('layout', $layout);
        return $layout;
    }

    /**
     * Retrieve viewRenderer object
     *
     * @return viewRenderer
     */
    protected function getRenderer($options, $view)
    {
        // Check if frontController is booted
        $bootstrap = $this->getBootstrap();
        if ($bootstrap->hasPluginResource('FrontController')) {
            $bootstrap->bootstrap('FrontController');
            $front = $bootstrap->getResource('FrontController');
            $noViewRenderer = $front->getParam('noViewRenderer');

            if (empty($noViewRenderer)) {
                // Register view renderer
                $class = empty($options['class']) ? $this->viewRendererClass : $options['class'];
                $viewRenderer = new $class();
                $viewRenderer->setView($view);
                Xoops_Zend_Controller_Action_HelperBroker::getStack()->offsetSet(-80, $viewRenderer);
                $key = (isset($options['registry_key']) && !is_numeric($options['registry_key']))
                    ? $options['registry_key']
                    : 'viewRenderer';
                XOOPS::registry($key, $viewRenderer);
            }
        }
    }

}
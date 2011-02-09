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

class Xoops_Zend_Application_Resource_View extends Zend_Application_Resource_ResourceAbstract
{
    const DEFAULT_REGISTRY_KEY = 'viewRenderer';
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

        // Initialize view
        $view = $this->getView($options);

        // Check if frontController is booted
        $bootstrap = $this->getBootstrap();
        if ($bootstrap->hasPluginResource('FrontController')) {
            $bootstrap->bootstrap('FrontController');
            $front = $bootstrap->getResource('FrontController');
            $noViewRenderer = $front->getParam('noViewRenderer');

            if (empty($noViewRenderer)) {
                // Register view renderer
                $viewRenderer = new Xoops_Zend_Controller_Action_Helper_ViewRenderer();
                $viewRenderer->setView($view);
                Xoops_Zend_Controller_Action_HelperBroker::getStack()->offsetSet(-80, $viewRenderer);
                $key = (isset($options['registry_key']) && !is_numeric($options['registry_key']))
                    ? $options['registry_key']
                    : self::DEFAULT_REGISTRY_KEY;
                XOOPS::registry($key, $viewRenderer);
            }
        }

        return $view;
    }

    /**
     * Retrieve view object
     *
     * @return Xoops_Zend_View
     */
    private function getView($options)
    {
        $view = new Xoops_Zend_View($options);
        XOOPS::registry('view', $view);
        return $view;
    }

    /**
     * Retrieve layout object
     *
     * @return Xoops_Zend_Layout
     */
    private function getLayout($options)
    {
        // Initialize and register layout
        $options['theme'] = isset($options['theme'])
                            ? $options['theme']
                            : (XOOPS::config('theme_set') ?: 'default');
        $initMvc = true;
        if (isset($options['initMvc'])) {
            $initMvc = (bool) $options['initMvc'];
            unset($options['initMvc']);
        }

        if ($initMvc) {
            $layout = Xoops_Zend_Layout::startMvc($options);
        } else {
            $layout = new Xoops_Zend_Layout($options);
        }
        XOOPS::registry('layout', $layout);
        return $layout;
    }

}
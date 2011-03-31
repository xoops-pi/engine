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
 * @copyright       Xoops Engine http://www.xoopsengine.org
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @since           3.0
 * @category        Xoops_Zend
 * @package         Navigation
 * @version         $Id$
 */

class Xoops_Zend_Navigation_Page_Mvc extends Zend_Navigation_Page_Mvc
{
    /**
     * Whether this page should be considered active
     *
     * @var bool
     */
    protected $_active = null;

    /**
     * Adds a page to the container
     *
     * This method will inject the container as the given page's parent by
     * calling {@link Zend_Navigation_Page::setParent()}.
     *
     * @param  Zend_Navigation_Page|array|Zend_Config $page  page to add
     * @return Zend_Navigation_Container                     fluent interface,
     *                                                       returns self
     * @throws Zend_Navigation_Exception                     if page is invalid
     */
    public function addPage($page)
    {
        if (is_array($page) || $page instanceof Zend_Config) {
            $page = Xoops_Zend_Navigation_Page::factory($page);
        }

        return parent::addPage($page);
    }

    /**
     * Returns whether page should be considered active or not
     *
     * This method will compare the page properties against the request object
     * that is found in the front controller.
     *
     * @param  bool $recursive  [optional] whether page should be considered
     *                          active if any child pages are active. Default is
     *                          false.
     * @return bool             whether page should be considered active or not
     */
    public function isActive($recursive = false)
    {
        if ($this->_active === null) {
            //Debug::e("active not set");
            $front = Xoops::registry('frontController');
            $reqParams = $front->getRequest()->getParams();

            if (!array_key_exists('module', $reqParams)) {
                $reqParams['module'] = $front->getDefaultModule();
            }

            $myParams = $this->_params;

            if (null !== $this->_module) {
                $myParams['module'] = $this->_module;
            } else {
                $myParams['module'] = $front->getDefaultModule();
            }

            if (null !== $this->_controller) {
                $myParams['controller'] = $this->_controller;
            } else {
                $myParams['controller'] = $front->getDefaultControllerName();
            }

            if (null !== $this->_action) {
                $myParams['action'] = $this->_action;
            } else {
                $myParams['action'] = $front->getDefaultAction();
            }

            if (null !== $this->_route) {
                $myParams['route'] = $this->_route;
                if (!isset($reqParams["route"])) {
                    $reqParams["route"] = $front->getRouter()->getCurrentRouteName();
                }
            }

            //Debug::e($myParams);
            if (count(array_intersect_assoc($reqParams, $myParams)) ==
                count($myParams)) {
                //Debug::e($myParams);
                $this->_active = true;
                return true;
            }
        }


        if (!$this->_active && $recursive) {
            foreach ($this->_pages as $page) {
                if ($page->isActive(true)) {
                    return true;
                }
            }
            return false;
        }

        return $this->_active;
    }

    /**
     * Returns href for this page
     *
     * This method uses {@link Zend_Controller_Action_Helper_Url} to assemble
     * the href based on the page's properties.
     *
     * @return string  page href
     */
    public function ____getHref()
    {
        if ($this->_hrefCache) {
            return $this->_hrefCache;
        }

        if (null === static::$_urlHelper) {
            static::$_urlHelper =
                Zend_Controller_Action_HelperBroker::getStaticHelper('Url');
        }

        $params = $this->getParams();

        if ($param = $this->getModule()) {
            $params['module'] = $param;
        }

        if ($param = $this->getController()) {
            $params['controller'] = $param;
        }

        if ($param = $this->getAction()) {
            $params['action'] = $param;
        }

        $url = static::$_urlHelper->url($params,
                                      $this->getRoute(),
                                      $this->getResetParams());

        return $this->_hrefCache = $url;
    }

}

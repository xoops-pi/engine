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

class Xoops_Zend_Navigation_Page_Uri extends Zend_Navigation_Page_Uri
{
    /**
     * Whether this page should be considered active
     *
     * @var bool
     */
    protected $_active = null;

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
        // TODO: frontController is not available or not valid for URI parameter detection
        if (!isset($this->_active) && $front = Xoops::registry('frontController')) {
            $reqPath = $front->getRequest()->getPathInfo();
            $uriPath = parse_url($this->getUri(), PHP_URL_PATH);
            if (substr($uriPath, -1 * strlen($reqPath)) == $reqPath) {
            //if (!strcmp($reqPath, $uriPath)) {
                $this->_active = true;
                if ($uriQuery = parse_url($this->getUri(), PHP_URL_QUERY)) {
                    $reqParams = $front->getRequest()->getParams();
                    parse_str($uriQuery, $uriParams);
                    foreach ($uriParams as $key => $val) {
                        if (!isset($reqParams[$key]) || $reqParams[$key] !== $val) {
                            $this->_active = false;
                            break;
                        }
                    }
                }
                if ($this->_active == true) {
                    return true;
                }
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
     * @return string
     */
    public function getHref()
    {
        $href = $this->getUri();
        if (empty($href)) {
            $href = "#";
        } elseif ($href{0} == "/") {
            $href = XOOPS::url("www") . $href;
        }
        return $href;
    }

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

}

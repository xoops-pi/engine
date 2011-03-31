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

class Xoops_Zend_Navigation_Page_Misc extends Xoops_Zend_Navigation_Page
{
    /**
     * Whether this page should be considered active
     *
     * @var bool
     */
    protected $_active = null;

    /**
     * Returns href for this page
     *
     * @return string
     */
    public function getHref()
    {
        return "#";
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

    public function isActive($recursive = false)
    {
        //Debug::e($this->getLabel());
        if (!isset($this->_active)) {
            $this->_active = false;
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
        //return parent::isActive($recursive);
    }
}

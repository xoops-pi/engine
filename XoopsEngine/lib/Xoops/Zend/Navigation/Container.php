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

abstract class ____Xoops_Zend_Navigation_Container extends Zend_Navigation_Container
{
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
        if ($page === $this) {
            require_once 'Zend/Navigation/Exception.php';
            throw new Zend_Navigation_Exception(
                'A page cannot have itself as a parent');
        }

        if (is_array($page) || $page instanceof Zend_Config) {
            $page = Xoops_Zend_Navigation_Page::factory($page);
        } elseif (!$page instanceof Zend_Navigation_Page) {
            require_once 'Zend/Navigation/Exception.php';
            throw new Zend_Navigation_Exception(
                    'Invalid argument: $page must be an instance of ' .
                    'Zend_Navigation_Page or Zend_Config, or an array');
        }

        $hash = $page->hashCode();

        if (array_key_exists($hash, $this->_index)) {
            // page is already in container
            return $this;
        }

        // adds page to container and sets dirty flag
        $this->_pages[$hash] = $page;
        $this->_index[$hash] = $page->getOrder();
        $this->_dirtyIndex = true;

        // inject self as page parent
        $page->setParent($this);

        return $this;
    }
}
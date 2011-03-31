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
 * @package         View
 * @version         $Id$
 */

class Xoops_Zend_View_Helper_Navigation extends Zend_View_Helper_Navigation
{
    protected $pluginLoader;
    protected $stored;

    /**
     * Returns the helper matching $proxy
     *
     * The helper must implement the interface
     * {@link Zend_View_Helper_Navigation_Helper}.
     *
     * @param string $proxy                        helper name
     * @param bool   $strict                       [optional] whether
     *                                             exceptions should be
     *                                             thrown if something goes
     *                                             wrong. Default is true.
     * @return Zend_View_Helper_Navigation_Helper  helper instance
     * @throws Zend_Loader_PluginLoader_Exception  if $strict is true and
     *                                             helper cannot be found
     * @throws Zend_View_Exception                 if $strict is true and
     *                                             helper does not implement
     *                                             the specified interface
     */
    public function findHelper($proxy, $strict = true)
    {
        if (isset($this->_helpers[$proxy])) {
            $this->_helpers[$proxy]->setContainer($this->getContainer());
            return $this->_helpers[$proxy];
        }

        if (!$this->pluginLoader) {
            $path = str_replace('_', '/', self::NS);
            $this->pluginLoader = new Xoops_Zend_Loader_PluginLoader(array(
                self::NS            => XOOPS::path('lib') . '/' . $path,
                "Xoops_" . self::NS => XOOPS::path('lib') . '/Xoops/' . $path,
                ), __CLASS__);
        }

        if ($class = $this->pluginLoader->load($proxy)) {
            $helper = new $class();
            if (method_exists($helper, 'setView')) {
                $helper->setView($this->view);
            }
        } elseif ($strict) {
            $helper = $this->view->getHelper($proxy);
        } else {
            try {
                $helper = $this->view->getHelper($proxy);
            } catch (Zend_Loader_PluginLoader_Exception $e) {
                return null;
            }
        }

        if (!$helper instanceof Zend_View_Helper_Navigation_Helper) {
            if ($strict) {
                $e = new Zend_View_Exception(sprintf(
                        'Proxy helper "%s" is not an instance of ' .
                        'Zend_View_Helper_Navigation_Helper',
                        get_class($helper)));
                $e->setView($this->view);
                throw $e;
            }

            return null;
        }

        $this->_inject($helper);
        $this->_helpers[$proxy] = $helper;

        return $helper;
    }

}
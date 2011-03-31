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
 * @package         Layout
 * @version         $Id$
 */

/**
 * Helper for interacting with Zend_Layout objects
 */
class Xoops_Zend_Layout_Controller_Action_Helper_Layout extends Zend_Layout_Controller_Action_Helper_Layout
{
    protected $cached = false;

    public function getCached()
    {
        return $this->cached;
    }

    public function setCached($cached = true)
    {
        $this->cached = (bool) $cached;
        return $this;
    }

    /**
     * Get layout object
     *
     * @return Zend_Layout
     */
    public function getLayoutInstance()
    {
        if (null === $this->_layout) {
            if (null === ($this->_layout = Xoops_Zend_Layout::getMvcInstance())) {
                $this->_layout = new Xoops_Zend_Layout();
            }
        }

        return $this->_layout;
    }
}

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
 * @package         Db
 * @version         $Id$
 */

class Xoops_Zend_Filter_Encrypt extends Zend_Filter_Encrypt
{
    /**
     * Sets new encryption options
     *
     * @param  string|array $options (Optional) Encryption options
     * @return Zend_Filter_Encrypt
     */
    public function setAdapter($options = null)
    {
        if (is_string($options)) {
            $adapter = $options;
        } else if (isset($options['adapter'])) {
            $adapter = $options['adapter'];
            unset($options['adapter']);
        } else {
            $adapter = 'Mcrypt';
        }

        if (!is_array($options)) {
            $options = array();
        }

        $adapter = 'Zend_Filter_Encrypt_' . ucfirst($adapter);
        if (class_exists('Xoops_' . $adapter)) {
            $adapter = 'Xoops_' . $adapter;
        }

        $this->_adapter = new $adapter($options);
        if (!$this->_adapter instanceof Zend_Filter_Encrypt_Interface) {
            throw new Zend_Filter_Exception("Encoding adapter '" . $adapter . "' does not implement Zend_Filter_Encrypt_Interface");
        }

        return $this;
    }
}
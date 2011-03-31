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

class Xoops_Zend_Filter_Encrypt_Mcrypt extends Zend_Filter_Encrypt_Mcrypt
{
    /**
     * Definitions for encryption
     * array(
     *     'key' => encryption key string
     *     'algorithm' => algorithm to use
     *     'algorithm_directory' => directory where to find the algorithm
     *     'mode' => encryption mode to use
     *     'modedirectory' => directory where to find the mode
     * )
     */
    protected $_encryption = array(
        'key'                 => 'XoopsEngine',
        'algorithm'           => 'blowfish',
        'algorithm_directory' => '',
        'mode'                => 'cbc',
        'mode_directory'      => '',
        'vector'              => null,
        'salt'                => false
    );

    /**
     * Sets new encryption options
     *
     * @param  string|array $options Encryption options
     * @return Zend_Filter_File_Encryption
     */
    public function setEncryption($options)
    {
        if (is_string($options)) {
            $options = array('key' => $options);
        }

        if (!is_array($options)) {
            throw new Zend_Filter_Exception('Invalid options argument provided to filter');
        }

        $options = $options + $this->getEncryption();
        if (!isset($options['vector'])) {
            $options['vector'] = null;
        }

        $this->_encryption = $options;
        $this->setVector($options['vector']);

        return $this;
    }

    /**
     * Sets the initialization vector
     *
     * @param string $vector (Optional) Vector to set
     * @return Zend_Filter_Encrypt_Mcrypt
     */
    public function setVector($vector = null)
    {
        if (empty($vector)) {
            $cipher = $this->_openCipher();
            $size   = mcrypt_enc_get_iv_size($cipher);
            $vector = substr(Xoops::config('salt'), 0, $size);
            $this->_closeCipher($cipher);
        }
        $this->_encryption['vector'] = $vector;

        return $this;
    }

    /**
     * Defined by Zend_Filter_Interface
     *
     * Decrypts $value with the defined settings
     *
     * @param  string $value Content to decrypt
     * @return string The decrypted content
     */
    public function decrypt($value)
    {
        return trim(parent::decrypt($value));
    }
}
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
 * @package         Form
 * @version         $Id$
 */

/**
 * XOOPS form element hash
 */

class Xoops_Zend_Form_Element_Hash extends Zend_Form_Element_Hash
{
    private static $hash;

    /**
     * Constructor
     *
     * Creates session namespace for CSRF token, and adds validator for CSRF
     * token.
     *
     * @param  string|array|Zend_Config $spec
     * @param  array|Zend_Config $options
     * @return void
     */
    public function __construct($spec = null, $options = null)
    {
        if (empty($spec)) {
            $spec = XOOPS::config('identifier') . '_' . __CLASS__ . '_token';
        }
        $options['salt'] = XOOPS::config('salt');

        parent::__construct($spec, $options);

        $this->setAllowEmpty(false)
             ->setRequired(true)
             ->initCsrfValidator();
    }


    /**
     * Generate CSRF token
     *
     * Generates CSRF token and stores both in {@link $_hash} and element
     * value.
     *
     * @return void
     */
    protected function _generateHash()
    {
        if (!isset(self::$hash)) {
            self::$hash = $this->_hash = md5(
                mt_rand(1,1000000)
                .  $this->getSalt()
                .  $this->getName()
                .  mt_rand(1,1000000)
            );
        } else {
            $this->_hash = self::$hash;
        }
        $this->setValue($this->_hash);
    }
}

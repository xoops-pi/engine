<?php
/**
 * User credential validator
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
 * @category        Xoops_Module
 * @package         User
 * @version         $Id$
 */

class Xoops_Zend_Validate_Authenticate extends Zend_Validate_Abstract
{
    const INVALID = 'invalid';
    protected $identity = null;

    protected $_messageTemplates = array(
        self::INVALID => 'The credential is invalid'
    );

    public function __construct($options = array())
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } elseif (!is_array($options)) {
            $options = func_get_args();
            $temp['identity'] = array_shift($options);
            $options = $temp;
        }

        if (array_key_exists('identity', $options)) {
            $this->identity = $options['identity'];
        }
    }

    public function isValid($value)
    {
        $value = (string) $value;
        $this->_setValue($value);

        $result = Xoops::service("auth")->loadAdapter()->setIdentity($this->identity)->setCredential($value)->authenticate();
        if ($result->isValid()) {
            return true;
        }

        $this->_error(self::INVALID);
        return false;
    }
}
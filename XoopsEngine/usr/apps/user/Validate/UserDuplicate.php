<?php
/**
 * User account duplicate validator
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

//class App_User_Validate_UserDuplicate extends Zend_Validate_Abstract
class User_Validate_UserDuplicate extends Zend_Validate_Abstract
{
    const DUPLICATED = 'duplicated';
    protected $id = null;

    protected $_messageTemplates = array(
        self::DUPLICATED => 'The username has been taken'
    );

    public function __construct($options = array())
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } elseif (!is_array($options)) {
            $options    = func_get_args();
            $temp['id'] = array_shift($options);
            $options = $temp;
        }

        if (array_key_exists('id', $options)) {
            $this->id = $options['id'];
        }
    }

    public function isValid($value, $context = null)
    {
        $value = (string) $value;
        $this->_setValue($value);

        $userModel = XOOPS::getModel("user_account");
        $select = $userModel->select()->where("identity = ?", $value);
        $id = isset($context['id']) ? $context['id'] : $this->id;
        if (!empty($id)) {
            $select->where("id <> ?", $id);
        }
        $rowset = $userModel->fetchAll($select);
        if ($rowset->count() == 0) {
            return true;
        }

        $this->_error(self::DUPLICATED);
        return false;
    }
}
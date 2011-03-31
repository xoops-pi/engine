<?php
/**
 * User meta duplicate validator
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

//class App_User_Validate_MetaDuplicate extends Zend_Validate_Abstract
class User_Validate_MetaDuplicate extends Zend_Validate_Abstract
{
    const DUPLICATED = 'duplicated';

    protected $_messageTemplates = array(
        self::DUPLICATED => 'The key has been taken'
    );

    public function isValid($value)
    {
        $value = (string) $value;
        $this->_setValue($value);

        $modelMeta = XOOPS::getModel("user_meta");
        $select = $modelMeta->select()->where($modelMeta->getAdapter()->quoteIdentifier("key") . " = ?", $value);
        $rowset = $modelMeta->fetchAll($select);
        if ($rowset->count() == 0) {
            return true;
        }

        $this->_error(self::DUPLICATED);
        return false;
    }
}
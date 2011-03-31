<?php
/**
 * Block name duplicate validator
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
 * @package         System
 * @version         $Id$
 */

//class App_System_Validate_ModuleDuplicate extends Zend_Validate_Abstract
class System_Validate_BlockNameDuplicate extends Zend_Validate_Abstract
{
    const DUPLICATED = 'duplicated';
    protected $id;

    protected $_messageTemplates = array(
        self::DUPLICATED => 'The block anem has been taken'
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

    /**
     * Validate if block name is duplicated
     *
     * @param  mixed $value
     * @param  mixed $context
     * @return boolean
     */
    public function isValid($value, $context = null)
    {
        $value = (string) $value;
        $this->_setValue($value);
        if (empty($value)) {
            return true;
        }

        $Model = XOOPS::getModel("block");
        $select = $Model->select()->where("name = ?", $value);
        $id = isset($context['id']) ? $context['id'] : $this->id;
        if (!empty($id)) {
            $select->where("id <> ?", $id);
        }
        $rowset = $Model->fetchAll($select);
        if ($rowset->count() == 0) {
            return true;
        }

        $this->_error(self::DUPLICATED);
        return false;
    }
}
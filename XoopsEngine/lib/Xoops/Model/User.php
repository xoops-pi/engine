<?PHP
/**
 * XOOPS user model
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Xoops Engine http://www.xoopsengine.org/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @since           3.0
 * @package         Xoops_Model
 * @version         $Id$
 */

class Xoops_Model_User extends Xoops_Model_User_Account
{
    /**
     * Classname for row
     *
     * @var string
     */
    protected $_rowClass = 'Xoops_Model_User_Row_User';

    /*
    protected static $models = array();
    protected static $cols = array();

    protected function getCols($model)
    {
        if (!isset(static::$cols[$model])) {
            static::$cols[$model] = $this->getModel($model)->info("cols");
        }
        return static::$cols[$model];
    }

    protected function getModel($model)
    {
        if (!isset(static::$models[$model])) {
            static::$models[$model] = XOOPS::getModel("user_" . $model);
        }
        return static::$models[$model];
    }
    */
    protected $_dependentTables = array('Xoops_Model_User_Profile');
    /*
    protected $profileTable = 'Xoops_Model_User_Profile';

    public function getProfileTable()
    {
        return $this->profileTable;
    }
    */
}
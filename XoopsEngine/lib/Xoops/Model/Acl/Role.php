<?PHP
/**
 * XOOPS role model
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

class Xoops_Model_Acl_Role extends Xoops_Zend_Db_Model
{
    //protected $_primary = "id";
    protected $_name = "acl_role";

    // NOT optimal, to be refactored
    public function getAncestors($role)
    {
        $parents = array();
        $select = $this->getAdapter()->select()
                    ->from(array('r' => $this->_name))
                    ->where('r.active = ?', 1)
                    ->joinLeft(array('i' => $this->getAdapter()->prefix('acl_inherit', 'xo')), 'r.name = i.parent')
                    ->where('i.child = ?', $role);
                    //->order(array('i.order'));
        $result = $select->query()->fetchAll();
        if (empty($result)) {
            return $parents;
        }
        /*
        foreach ($result as $row) {
            $parents[$row['name']] = $this->getAncestors($row['name']);
        }
        */
        foreach ($result as $row) {
            $parents += $this->getAncestors($row['name']);
            $parents[] = $row['name'];
        }

        return $parents;
    }
}
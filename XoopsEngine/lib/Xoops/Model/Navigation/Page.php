<?PHP
/**
 * XOOPS navigation page model
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

class Xoops_Model_Navigation_Page extends Xoops_Zend_Db_Model_Nest
{
    private $navigation = "";

    public function setNavigation($navigation)
    {
        if (!is_null($navigation)) {
            $this->navigation = $navigation;
        }
        return $this;
    }

    public function getNavigation()
    {
        return $this->navigation;
    }

    /**
     * Remove an item
     *
     * @param   mixed   $objective  page ID or {@link Xoops_Zend_Db_Table_Row_Node}
     * @param   bool    $recursive  Whether to delete all children nodes
     * @return   int    affected rows
     */
    public function ____remove($page, $recursive = false)
    {
        if (!($page instanceof Xoops_Zend_Db_Table_Row_Node)) {
            if (!$page = $this->findRow($page)) {
                return false;
            }
        }
        return parent::remove($page, $recursive);
    }
}
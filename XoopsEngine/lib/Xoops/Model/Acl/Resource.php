<?PHP
/**
 * XOOPS resource model
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

class Xoops_Model_Acl_Resource extends Xoops_Zend_Db_Model_Nest
{
    private $section;
    private $module;
    protected $_name = "acl_resource";

    public function setSection($section)
    {
        if (!is_null($section)) {
            $this->section = $section;
        }
        return $this;
    }

    public function getSection()
    {
        return $this->section;
    }

    public function setModule($module)
    {
        if (!is_null($module)) {
            $this->module = $module;
        }
        return $this;
    }

    public function getModule()
    {
        return $this->module;
    }

    /**
     * Get ancestors of a resource
     *
     * @param   mixed   $objective  resource ID or {@link Xoops_Zend_Db_Table_Row_Node}
     * @return  array   array of resources
     */
    public function getAncestors($resource, $cols = "name")
    {
        if (!($resource instanceof Xoops_Zend_Db_Table_Row_Node)) {
            if (!$resource = $this->findRow($resource)) {
                return false;
            }
        }
        $result = parent::getAncestors($resource, $cols);
        $parents = array();
        foreach ($result as $row) {
            $parents[] = (is_string($cols) && $cols != "*")
                            ? $row->$cols
                            : $row->toArray();
        }
        return $parents;
    }

    /**
     * Remove a resource
     *
     * @param   mixed   $objective  resource ID or {@link Xoops_Zend_Db_Table_Row_Node}
     * @param   bool    $recursive  Whether to delete all children nodes
     * @return   int     affected rows
     */
    public function remove($resource, $recursive = false)
    {
        if (!($resource instanceof Xoops_Zend_Db_Table_Row_Node)) {
            if (!$resource = $this->findRow($resource)) {
                return false;
            }
        }
        $resources = array();
        if (empty($recursive)) {
            //$resources[$resource->id] = $resource->module;
        } else {
            $resources = array();
            if (!$list = $this->getChildren($resource, array("id", "module"))) {
                return false;
            }
            foreach ($list as $row) {
                $resources[$row->id] = $row->module;
            }
        }
        $resources[$resource->id] = $resource->module;
        //Debug::e($resource->toArray());
        //Debug::e($resources);
        parent::remove($resource, $recursive);
        $modelRule = XOOPS::getModel("acl_rule");
        $modelRule->delete(array("section = ?" => $resource->section, "module = ?" => $resource->module, "resource IN (?)" => array_keys($resources)));
        return true;
    }
}
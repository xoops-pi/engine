<?PHP
/**
 * XOOPS rule model
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

class Xoops_Model_Acl_Rule extends Xoops_Zend_Db_Model
{
    private $section = "";
    //private $module;

    protected $_primary = "id";
    protected $_name = "acl_rule";

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

    /*
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
    */
    public function getRules($roles = array(), $resources = array(), $privilege = null)
    {
        $rows = array();

        $clause = new Xoops_Zend_Db_Clause("section = ?", $this->getSection());
        /*
        if ($module = $this->getModule()) {
            $clause->add("module = ?", $module);
        }
        */
        if (!empty($roles)) {
            if (count($roles) == 1) {
                $clause->add('role = ?', array_shift($roles));
            } else {
                $clause->add('role IN (?)', $roles);
            }
        }
        if (!empty($resources)) {
            if (count($resources) == 1) {
                $clause->add('resource = ?', array_shift($resources));
            } else {
                $clause->add('resource IN (?)', $resources);
            }
        }
        if (!is_null($privilege)) {
            $clause->add("privilege = ?", $privilege);
        }
        $select = $this->select()->Where($clause);
        $result = $select->query()->fetchAll();
        if (empty($result)) {
            return $rows;
        }
        $rows = $result;
        return $rows;
    }

    /**
     * Get resources to which a group of roles is allowed/denied to access a given resource privilege
     *
     * @param array     $roles
     * @param object    $clauseCall {@link Xoops_Zend_Db_Clause}
     * @param boolean   $allowed allowed or denied
     * @return array of resources
     */
    public function getResources($roles, $clauseCall = null, $allowed = true)
    {
        $clause = new Xoops_Zend_Db_Clause("section = ?", $this->getSection());
        if (count($roles) > 1) {
            $clause->add("role IN (?)", $roles);
        } else {
            $clause->add("role = ?", array_shift($roles));
        }
        if ($clauseCall instanceof Xoops_Zend_Db_Clause) {
            $clause->add($clauseCall);
        }
        $select = $this->select()->Where($clause)
                                ->from($this,
                                    array("resource",
                                        "denied" => "SUM(" . $this->getAdapter()->quoteIdentifier("deny") . ")")
                                    );
        if (!is_null($privilege)) {
            $clause->add("privilege = ?", $privilege);
        }
        $select->group("resource");
        // allowed
        if (!empty($allowed)) {
            $select->having("denied = 0");
        // denied
        } else {
            $select->having("denied > 0");
        }
        $resources = array();
        $rowset = $this->fetchAll($select);
        foreach ($rowset as $row) {
            $resources[] = $row->resource;
        }
        return $resources;

        // No privilege set
        if (empty($privilege)) {
            // allowed
            if (!empty($allowed)) {
                $select->having("denied = 0");
            // denied
            } else {
                $select->having("denied > 0");
            }
            $rowset = $this->fetchAll($select);
            foreach ($rowset as $row) {
                $resources[] = $row->resource;
            }
        // allowed with privilege
        } elseif (!empty($allowed)) {
            $select->having("denied = 0");
            $rowset = $this->fetchAll($select);
            foreach ($rowset as $row) {
                $resources[] = $row->resource;
            }
            if (empty($resources)) {
                return $resources;
            }
            $clause = new Xoops_Zend_Db_Clause("section = ?", $this->getSection());
            $clause->add("resource IN (?)", $resources);
            if (count($roles) > 1) {
                $clause->add("role IN (?)", $roles);
            } else {
                $clause->add("role = ?", array_shift($roles));
            }
            $clause->add("privilege = ?", $privilege);
            $clause->add("denied = ?", 0);
            $select = $this->select()->Where($clause)->from($this, "resource");
            $rowset = $this->fetchAll($select);
            $resources = array();
            foreach ($rowset as $row) {
                $resources[] = $row->resource;
            }
        // denied with privilege
        } else {
            $rowset = $this->fetchAll($select);
            $list = array();
            foreach ($rowset as $row) {
                if ($row->denied) {
                    $resources[] = $row->resource;
                } else {
                    $list[] = $row->resource;
                }
            }
            if (empty($list)) {
                return $resources;
            }
            $clause = new Xoops_Zend_Db_Clause("section = ?", $this->getSection());
            $clause->add("resource IN (?)", $list);
            if (count($roles) > 1) {
                $clause->add("role IN (?)", $roles);
            } else {
                $clause->add("role = ?", array_shift($roles));
            }
            $clause->add("privilege = ?", $privilege);
            $clause->add("denied = ?", 1);
            $select = $this->select()->Where($clause)->from($this, "resource");
            $rowset = $this->fetchAll($select);
            foreach ($rowset as $row) {
                $resources[] = $row->resource;
            }
        }

        return $resources;
    }

    /**
     * Get items of a specified allowed resource to which a group of roles is allowed/denied to access a given resource privilege
     *
     * @param array     $roles
     * @param string    $resource
     * @param object    $clauseCall {@link Xoops_Zend_Db_Clause}
     * @param boolean   $allowed allowed or denied
     * @return array of items
     */
    public function ____getItems($roles, $resource, $clauseCall = null, $allowed = true)
    {
        $clause = new Xoops_Zend_Db_Clause("section = ?", $this->getSection());
        $clause->add("resource = ?", $resource);
        /*
        if ($module = $this->getModule()) {
            $clause->add("module = ?", $module);
        }
        */
        if (count($roles) > 1) {
            $clause->add("role IN (?)", $roles);
        } else {
            $clause->add("role = ?", array_shift($roles));
        }
        if ($clauseCall instanceof Xoops_Zend_Db_Clause) {
            $clause->add($clauseCall);
        }
        $select = $this->select()->Where($clause)
                                ->from($this,
                                    array("item",
                                        "denied" => "SUM(" . $this->getAdapter()->quoteIdentifier("deny") . ")")
                                    );
        $select->group("item");
        if (!empty($allowed)) {
            $select->having("denied = 0");
        } else {
            $select->having("denied > 0");
        }
        /*
        $clause->group("item");
        if (!empty($allowed)) {
            $clause->having("denied = 0");
        } else {
            $clause->having("denied > 0");
        }
        */
        $result = $select->query()->fetchAll();
        $items = array();
        foreach ($result as $ret) {
            $items[] = $ret["item"];
        }
        return $items;
    }

    /**
     * Check if a group of roles is allowed/denied to access a given resource privilege
     *
     * @param object    $clause {@link Xoops_Zend_Db_Clause}
     * @return boolean
     */
    public function isAllowed($clauseCall = null)
    {
        $clause = new Xoops_Zend_Db_Clause("section = ?", $this->getSection());
        /*
        if ($module = $this->getModule()) {
            $clause->add("module = ?", $module);
        }
        */
        if ($clauseCall instanceof Xoops_Zend_Db_Clause) {
            $clause->add($clauseCall);
        }
        $select = $this->select()->Where($clause)
                                    ->from($this,
                                        array("denied" => "SUM(" . $this->getAdapter()->quoteIdentifier("deny") . ")")
                                        );
        $row = $this->fetchRow($select);
        if (empty($row) || $row->denied || is_null($row->denied)) {
            return false;
        }
        return true;
    }
}
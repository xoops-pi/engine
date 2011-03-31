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
 * @package         Db
 * @version         $Id$
 */

/**
 * XOOPS Nested Set Tree Model
 *
 * Managing Hierarchical Data with Nested Set Model
 * @see http://dev.mysql.com/tech-resources/articles/hierarchical-data.html
 */

abstract class Xoops_Zend_Db_Model_Nest extends Xoops_Zend_Db_Model
{
    //protected   $_primary   = "id";
    public      $id;
    public      $left       = "left";
    public      $right      = "right";
    public      $depth      = "depth";
    private static $validPostions = array("firstOf", "lastOf", "nextTo", "previousTo");

    /**
     * Zend_Db_Table_Row_Abstract class name.
     *
     * @var string
     */
    protected $_rowClass = 'Xoops_Zend_Db_Table_Row_Node';

    /**
     * Classname for rowset
     *
     * @var string
     */
    protected $_rowsetClass = 'Xoops_Zend_Db_Table_Rowset_Node';

    /**
     * Turnkey for initialization of a table object.
     * Calls other protected methods for individual tasks, to make it easier
     * for a subclass to override part of the setup logic.
     *
     * @return void
     */
    protected function _setup()
    {
        parent::_setup();
        $this->_setupPrimaryKey();
    }

    /**
     * setOptions()
     *
     * @param array $options
     * @return Zend_Db_Table_Abstract
     */
    public function setOptions(Array $options)
    {
        foreach (array("id", "left", "right", "depth") as $key) {
            if (isset($options[$key])) {
                $this->{$key} = (string) $options[$key];
                unset($options[$key]);
            }
        }
        parent::setOptions($options);

        return $this;
    }

    /**
     * Initialize primary key from metadata.
     * If $_primary is not defined, discover primary keys
     * from the information returned by describeTable().
     *
     * @return void
     * @throws Zend_Db_Table_Exception
     */
    protected function _setupPrimaryKey()
    {
        parent::_setupPrimaryKey();
        if (!$this->id) {
            $this->id = $this->_primary[1];
        }
    }

    /**
     * Initializes metadata.
     *
     * If metadata cannot be loaded from cache, adapter's describeTable() method is called to discover metadata
     * information. Returns true if and only if the metadata are loaded from cache.
     *
     * @return boolean
     * @throws Zend_Db_Table_Exception
     */
    protected function _setupMetadata()
    {
        if (!$this->left || !$this->right || !$this->depth) {
            require_once "Zend/Db/Table/Exception.php";
            throw new Zend_Db_Table_Exception('Column names "left", "right" and "depth" must be supplied.');
        }

        parent::_setupMetadata();

        if (count(array_intersect(array($this->left, $this->right, $this->depth), array_keys($this->_metadata))) < 3) {
            require_once "Zend/Db/Table/Exception.php";
            throw new Zend_Db_Table_Exception('Supplied "left", "right" or "depth" was not found.');
        }
    }

    public function quoteIdentifier($col)
    {
        return $this->getAdapter()->quoteIdentifier($this->$col);
    }

    /**#@+
     * Node operations
     */

    /**
     * Get extreme value of left or right
     *
     * @param   string  $side    node side, default as "right"
     * @param   mixed   $objective  target node ID or {@link Xoops_Zend_Db_Table_Row_Node}
     */
    private function getSideExtreme($side = "right", $objective = null)
    {
        $result = null;
        $side = ($side == "left") ? "left" : "right";
        if (!empty($objective)) {
            if ($objective instanceof Xoops_Zend_Db_Table_Row_Node) {
                $result = $objective->$side;
            } elseif (!$row = $this->findRow($objective)) {
                $result = $row->$side;
            } else {
                $result = false;
            }
            return $result;
        }

        if ($side == "left") {
            $column = $this->left;
            $operation = "MIN";
        } else {
            $column = $this->right;
            $operation = "MAX";
        }
        $select = $this->select()->from(
                    $this,
                    array("extreme" => $operation . "(" . $this->getAdapter()->quoteIdentifier($column) . ")")
        );
        if ($row = $this->fetchRow($select)) {
            $result = $row->extreme;
        }
        if (!$result) {
            $result = 0;
        }

        return $result;
    }

    /**
     * Get node param of a single pseudo node to be inserted
     *
     * @param   mixed   $objective  target node ID or {@link Xoops_Zend_Db_Table_Row_Node}
     * @param   string  $position   position to the target node, potential value: firstOf, lastOf, nextTo, previousTo
     * @return  array   postion     paramters: left, right
     */
    private function getPosition($objective = null, $position = "lastOf")
    {
        // Escape is position is invalid
        if (!in_array($position, self::$validPostions)) {
            return false;
        }

        // Escape if objectiveId is invalid
        $row = null;
        if ($objective instanceof Xoops_Zend_Db_Table_Row_Node) {
            $row = $objective;
        } elseif (!empty($objective)) {
            if (!$row = $this->findRow($objective)) {
                return false;
            }
        }

        $node = array("left" => 0, "right" => 0);
        // Root node
        if (empty($row)) {
            if ($position == "nextTo" || $position == "fistOf") {
                $node["left"] = $this->getSideExtreme("left", 0);
            } else {
                $node["left"] = $this->getSideExtreme("right", 0) + 1;
            }
            return $node;
        }

        // Next to the object
        if ($position == "nextTo") {
            $node["left"] = $row->right + 1;
        // Previous to the object
        } elseif ($position == "previousTo") {
            $node["left"] = $row->left;
        // Fist child of the object
        } elseif ($position == "firstOf") {
            $node["left"] = $row->left + 1;
        // Last child of the object
        } elseif ($position == "lastOf") {
            $node["left"] = $row->right;
        }
        //Debug::e(__METHOD__ . '::' . $row->id . ':' . $row->left . '-' . $row->right);
        //Debug::e($node);

        return $node;
    }

    /**
     * Shift a list of nodes
     *
     * @param    int        $left_start     starting value of node_left
     * @param    int        $increment      count of position increment
     * @param    int        $right_end      end value of right_end, if gt 0
     */
    private function shift($left_start, $increment, $right_end = 0)
    {
        if (!empty($right_end) && $right_end < $left_start) {
            return true;
        }

        // Get quoted identifier
        $left = $this->quoteIdentifier("left");
        $right = $this->quoteIdentifier("right");
        // Get operator and absolute increment value
        $operator = ($increment > 0) ? "+" : "-";
        $direction = ($increment > 0) ? "DESC" : "ASC";
        $value = abs($increment);

        foreach (array("left", "right") as $col) {
            $data = array(
                $this->$col => new Zend_Db_Expr("{$$col} {$operator} {$value}")
            );
            $where = array();
            $where[${$col} . " >= ?"] = $left_start;
            if (!empty($right_end)) {
                $where[${$col} . " <= ?"] = $right_end;
            }
            $where["order"] = "{$$col} {$direction}";
            $this->update($data, $where);
        }
        return true;
    }

    /**
     * Strip empty positions starting from a specific position
     *
     * @param    int        $start          starting value of node left or right
     */
    public function trim($start = 1, $leftVerified = false)
    {
        // Fetch the first node in valid range
        $select = $this->select()->where($this->quoteIdentifier("left") . " >= ?", $start)
                                ->orWhere($this->quoteIdentifier("right") . " >= ?", $start)
                                ->order($this->left . " ASC");
        if (!$rowRight = $this->fetchRow($select)) {
            return true;
        }
        // Detect empty positions on left side if not verified yet
        if (!$leftVerified) {
            $start = $rowRight->left;
            if ($start > 1) {
                // Find the first previous node
                $select = $this->select()->where($this->quoteIdentifier("left") . " < ?", $start)
                                        ->orWhere($this->quoteIdentifier("right") . " < ?", $start)
                                        ->order($this->right . " DESC");
                $rowLeft = $this->fetchRow($select);
                if (!$rowLeft) {
                    $start = 1;
                    //Debug::e("Left node not found, start changed to: " . $start);
                } else {
                    $start = (($rowLeft->right < $start) ? $rowLeft->right : $rowLeft->left) + 1;
                    //Debug::e("Left node found '{$rowLeft->left}, {$rowLeft->right}', start changed to: " . $start);
                }
            }
            // Shift if empty positions detected
            if ($shift = $start - $rowRight->left) {
                //Debug::e("Shift {$shift} starting from: " . $start);
                $this->shift($start, $shift);
                $rowRight->left = $start;
                $rowRight->right += $shift;
            }
        }
        if ($rowRight->depth == 0) {
            // Calulate children number v.s. right/left value to determine if empty positions exist
            $select = $this->select()->from($this->_name, array("count" => "COUNT(*)"))
                                    ->where($this->quoteIdentifier("left") . " >= ?", $rowRight->left);
            $row = $this->fetchRow($select);
            // children number equal to right/left value gap, no empty positions detected, exit
            $rightExtreme = $this->getSideExtreme();
            if ($row->count * 2 == $rightExtreme - $rowRight->left + 1) {
                return true;
            }
        }
        // Move on to next node if current node is a leaf
        if ($rowRight->right == $rowRight->left + 1) {
            //Debug::e("leaf: " . $rowRight->id);
            $this->trim($rowRight->right + 1, true);
            return;
        }

        $moveOn = true;
        // Calulate children number v.s. right/left value to determine if empty positions exist
        $select = $this->select()->from($this->_name, array("count" => "COUNT(*)"))
                                ->where($this->quoteIdentifier("right") . " < ?", $rowRight->right)
                                ->where($this->quoteIdentifier("left") . " > ?", $rowRight->left);
        $row = $this->fetchRow($select);
        // children number smaller than right/left value gap, empty positions detected, move on to first child
        if ($row->count * 2 < $rowRight->right - $rowRight->left - 1) {
            $moveOn = false;
            // Find last child in order to remove empty positions on right side
            $select = $this->select()->where($this->quoteIdentifier("right") . " < ?", $rowRight->right)
                                    ->order($this->right . " DESC");
            $rowChild = $this->fetchRow($select);
            if (!$rowChild) {
                $end = $rowRight->left + 1;
            } else {
                $end = $rowChild->right + 1;
            }
            // Shift if empty positions detected
            if ($shift = $end - $rowRight->right) {
                //Debug::e("Shift {$shift} starting from: " . $end);
                $this->shift($end, $shift);
                $rowRight->right += $shift;
                if ($row->count * 2 == $rowRight->right - $rowRight->left - 1) {
                    $moveOn = true;
                }
            }
        }

        if ($moveOn) {
            $this->trim($rowRight->right + 1, true);
        } else {
            $this->trim($rowRight->left + 1, true);
        }

        return;
    }

    public function calculate($start = 1)
    {
        $primary = $this->quoteIdentifier("id");
        $leftCol = $this->quoteIdentifier("left");
        $rightCol = $this->quoteIdentifier("right");
        $depthCol = $this->quoteIdentifier("depth");

        $node = "node";
        $child = "child";
        $nodeTable = $this->getAdapter()->quoteIdentifier($node);
        $childTable = $this->getAdapter()->quoteIdentifier($child);
        $select = $this->select()
            ->from(
                array($node => $this->_name),
                array(
                    $node . '.id',
                    $node . '.left',
                    $node . '.right',
                    'count' => new Zend_Db_Expr("(COUNT({$childTable}.{$primary}) * 2)"),
                    'gap' => new Zend_Db_Expr("({$nodeTable}.{$rightCol} - {$nodeTable}.{$leftCol} + 1)"),
                ))
            ->join(
                array($child => $this->_name),
                "({$childTable}.{$leftCol} BETWEEN {$nodeTable}.{$leftCol} AND {$nodeTable}.{$rightCol})",
                array()
                )
            //->where("{$nodeTable}.{$depthCol} = ?", 0)
            ->group($node . '.' . $this->id)
            ->having($this->getAdapter()->quoteIdentifier("gap") . " > " . $this->getAdapter()->quoteIdentifier("count"));
        if ($start > 1) {
            $select->where($nodeTable . '.' . $leftCol . ' >= ?', $start);
        }
        $rowset = $this->fetchAll($select);
        foreach ($rowset as $row) {
            //Debug::e("{$row->id}-'{$row->left}, {$row->right}'-{$row->count}-{$row->gap}");
            //$this->update(array($this->depth => $row->depth_cal), array($this->quoteIdentifier("id") . " = ?" => $row->id));
        }
    }

    public function reconcile($start = 1, $end = null)
    {
        $primary = $this->quoteIdentifier("id");
        $leftCol = $this->quoteIdentifier("left");
        $rightCol = $this->quoteIdentifier("right");
        $depthCol = $this->quoteIdentifier("depth");

        $node = "node";
        $parent = "parent";
        $nodeTable = $this->getAdapter()->quoteIdentifier($node);
        $parentTable = $this->getAdapter()->quoteIdentifier($parent);
        $select = $this->select()
            //->setIntegrityCheck(false)
            ->from(
                array($node => $this->_name),
                array(
                    $node . '.id',
                    $node . '.depth',
                    'depth_cal' => new Zend_Db_Expr("(COUNT({$parentTable}.{$primary}) - 1)")
                ))
            ->join(
                array($parent => $this->_name),
                "({$nodeTable}.{$leftCol} BETWEEN {$parentTable}.{$leftCol} AND {$parentTable}.{$rightCol})",
                array()
                )
            ->group($node . '.' . $this->id)
            ->having($nodeTable . '.' . $depthCol . " <> " . $this->getAdapter()->quoteIdentifier("depth_cal"));
        if ($start > 1) {
            $select->where($nodeTable . '.' . $leftCol . ' >= ?', $start);
        }
        if (!empty($end)) {
            $select->where($nodeTable . '.' . $rightCol . ' <= ?', $end);
        }
        $rowset = $this->fetchAll($select);
        foreach ($rowset as $row) {
            $this->update(array($this->depth => $row->depth_cal), array($this->quoteIdentifier("id") . " = ?" => $row->id));
        }
    }

    /*
    public function delete($where = null)
    {
        if (empty($where)) {
            return parent::delete();
        }
        $select = $this->select()->order("left");
        $select = $this->_where($select, $where);
        $rowset = $this->fetchAll($select);
        if (!$rowset) {
            return false;
        }
        $result = 0;
        foreach ($rowset as $row) {
            $result += $this->remove($row, true);
        }

        return $result;
    }
    */

    /**
     * Add a leaf node
     *
     * @param   array   $data       node data
     * @param   mixed   $objective  target node ID or {@link Xoops_Zend_Db_Table_Row_Node}
     * @param   string  $position   position to the target node, potential value: firstOf, lastOf, nextTo, previousTo
     * @return  mixed   The primary key of the row inserted.
     */
    public function add($data, $objective = null, $position = "lastOf")
    {
        $objective = empty($objective) ? null : $objective;
        $position = empty($position) ? "lastOf" : $position;
        //Debug::e(__METHOD__. ":{$objective}-{$position}");
        if (!$node = $this->getPosition($objective, $position)) {
            return false;
        }
        //Debug::e($node);
        $node_left = MAX(1, $node["left"], $node["right"]);
        if (!$this->shift($node_left, 2)) {
            return false;
        }
        $data = array_merge($data, array(
            $this->left     => $node_left,
            $this->right    => $node_left + 1
        ));
        if (!isset($data["depth"])) {
            $row = $this->createRow($data);
            $data["depth"] = $this->getDepth($row);
        }
        //Debug::e($data);
        //exit();
        //if ($objective) exit();
        return $this->insert($data);
    }

    /**
     * Remove a node
     *
     * @param   mixed   $objective  target node ID or {@link Xoops_Zend_Db_Table_Row_Node}
     * @param   bool    $recursive  Whether to delete all children nodes
     * @param   int     affected rows
     */
    public function remove($objective, $recursive = false)
    {
        if ($objective instanceof Xoops_Zend_Db_Table_Row_Node) {
            $row = $objective;
        } elseif (!$row = $this->findRow($objective)) {
            return false;
        }
        list($left, $right) = array($row->left, $row->right);
        if (!$result = parent::delete(array($this->quoteIdentifier("id") . " = ?" => $row->id))) {
            return false;
        }
        // Remove all children
        if ($recursive/* && !$row->isLeaf()*/) {
            // Get quoted identifier
            $where = array(
                $this->quoteIdentifier("left") . " > ?"    => $left,
                $this->quoteIdentifier("right") . " < ?"   => $right,
            );
            $result += parent::delete($where);
            // shift right hand nodes with width
            if (!$this->shift($right + 1, -1 * ($right - $left + 1))) {
                return false;
            }
        // Keep children
        } else {
            $data = array("depth" => new Zend_Db_Expr($this->quoteIdentifier("depth") . " - 1"));
            $where = array(
                $this->quoteIdentifier("left") . " > " . $left,
                $this->quoteIdentifier("right") . " > " . $right
            );
            $this->update($data, $where);

            if (!$this->shift($left + 1, -1, $right - 1)) {
                return false;
            }
            if (!$this->shift($right + 1, -2)) {
                return false;
            }
        }

        return $result;
    }

    /**
     * Move a node
     *
     * @param   mixed   $objective  target node ID or {@link Xoops_Zend_Db_Table_Row_Node}
     * @param   integer $reference  reference node ID or {@link Xoops_Zend_Db_Table_Row_Node}
     * @param   string  $position   position to the destination node, potential value: firstOf, lastOf, nextTo, previousTo
     */
    public function move($objective, $reference = null, $position = "lastOf")
    {
        if ($objective instanceof Xoops_Zend_Db_Table_Row_Node) {
            $row = $objective;
        } elseif (!$row = $this->findRow($objective)) {
            return false;
        }
        if ($reference instanceof Xoops_Zend_Db_Table_Row_Node) {
            //$row = $dest;
        } elseif (!$reference = $this->findRow($reference)) {
            return false;
        }
        if (!$node = $this->getPosition($reference, $position)) {
            return false;
        }

        $source = array(
            "left"  => $row->left,
            "right" => $row->right
        );

        $rightExtreme = $this->getSideExtreme();
        $incrementPlaceholder = $rightExtreme - $source["left"] + 1;
        if (!$this->shift($source["left"], $incrementPlaceholder, $source["right"])) {
            return false;
        }

        $increment = $row->right - $row->left + 1;
        if (!empty($node["left"])) {
            $dest = array(
                "left"  => $node["left"],
                "right" => $node["left"] + $increment - 1
            );
        } elseif (!empty($node["right"])) {
            $dest = array(
                "left"  => $node["right"] - $increment + 1,
                "right" => $node["right"]
            );
        } else {
            $dest = array(
                "left"  => 1,
                "right" => $increment
            );
        }
        if ($dest["left"] > $source["left"]) {
            if (!$this->shift($source["right"] + 1, -1 * $increment, $dest["left"] - 1)) {
                return false;
            }
            $dest["left"] += -1 * $increment;
        } else {
            if (!$this->shift($dest["left"], $increment, $source["left"] - 1)) {
                return false;
            }
        }

        $incrementPlaceholder = $dest["left"] - $rightExtreme - 1;
        if (!$this->shift($rightExtreme + 1, $incrementPlaceholder)) {
            return false;
        }
        $this->reconcile($dest["left"], $dest["left"] + $row->right - $row->left);
        return true;
    }

    /**
     * Calculate depth for a node
     *
     * @param   mixed   $objective  target node ID or {@link Xoops_Zend_Db_Table_Row_Node}
     */
    public function getDepth($objective)
    {
        if ($objective instanceof Xoops_Zend_Db_Table_Row_Node) {
            $row = $objective;
        } elseif (!$row = $this->findRow($objective)) {
            return false;
        }
        $select = $this->select()
                            ->from($this, array("depth" => "COUNT(*)"))
                            ->where($this->getAdapter()->quoteIdentifier($this->left) . " < ?", $row->left)
                            ->where($this->getAdapter()->quoteIdentifier($this->right) . " > ?", $row->right);
        if (!$result = $this->fetchRow($select)) {
            return false;
        }
        return $result->depth;
    }

    /**
     * Get root nodes
     *
     * @param   object  $clause     {@link Xoops_Zend_Db_Clause}
     */
    public function getRoots(Xoops_Zend_Db_Clause $clause = null)
    {
        $depth = $this->quoteIdentifier("depth");
        $select = $this->select()->where($depth . " = ?", 0)->order($this->left);
        if (!empty($clause)) {
            $select->where($clause);
        }
        //return $this->getAdapter()->fetchAssoc($select);
        return $this->fetchAll($select);
    }

    /**
     * Get ancestor nodes
     *
     * @param   mixed   $objective  target node ID or {@link Xoops_Zend_Db_Table_Row_Node}
     */
    public function getAncestors($objective, $cols = null)
    {
        if ($objective instanceof Xoops_Zend_Db_Table_Row_Node) {
            $row = $objective;
        } elseif (!$row = $this->findRow($objective)) {
            return false;
        }
        $select = $this->select()
                        ->where($this->getAdapter()->quoteIdentifier($this->left) . " <= ?", $row->left)
                        ->where($this->getAdapter()->quoteIdentifier($this->right) . " >= ?", $row->right);
        if (!empty($cols)) {
            $select->from($this, $cols);
        }
        $select->order($this->left . " ASC");
        if (!$result = $this->fetchAll($select)) {
            return false;
        }

        return $result;
    }

    /**
     * Get children nodes
     *
     * @param   mixed   $objective  target node ID or {@link Xoops_Zend_Db_Table_Row_Node}
     */
    public function getChildren($objective, $cols = null)
    {
        if ($objective instanceof Xoops_Zend_Db_Table_Row_Node) {
            $row = $objective;
        } elseif (!$row = $this->findRow($objective)) {
            return false;
        }
        $select = $this->select()
                        ->where($this->quoteIdentifier("left") . " >= ?", $row->left)
                        ->where($this->quoteIdentifier("right") . " <= ?", $row->right);
        if (!empty($cols)) {
            $select->from($this, $cols);
        }
        $select->order($this->left . " ASC");
        if (!$result = $this->fetchAll($select)) {
            return false;
        }

        return $result;
    }
    /**#@-*/

    /**#@+
     * Section operations
     */
    /**
     * Add a section from formulated array
     *
     * @param   array   $nodes      formulated array of nodes: left, right, ...
     * @param   mixed   $objective  target node ID or {@link Xoops_Zend_Db_Table_Row_Node}
     * @param   string  $position   position to the target node, potential value: firstOf, lastOf, nextTo, previousTo
     */
    public function graft($nodes, $objective = 0, $position = "lastOf")
    {
        if (empty($nodes) || !$node = $this->getPosition($objective, $position)) {
            return false;
        }
        $node_left = 1;
        if (isset($node["left"]) && $node["left"]) {
            $node_left = $node["left"];
        }
        if (isset($node["right"]) && $node["right"]) {
            $node_left = $node["right"];
        }
        if (!$this->shift($node_left, 2 * count($nodes))) {
            return false;
        }
        $increment = $node_left - 1;
        foreach ($nodes as $node) {
            $data = array_merge($node, array(
                "left"  => $node["left"] + $increment,
                "right" => $node["right"] + $increment,
            ));
            $this->insert($data);
        }
        return true;
    }

    /**
     * Convert a section from an adjacency array
     *
     * @param    array      $nodes          associative array of nodes: id, parent, tags ...
     * @param    array      $nodes_child    array of child IDs of current node
     * @param    int        $parent         parent ID
     * @param    int        $node_left      left of current node
     *
     */
    public function convertFromAdjacency(&$nodes, $nodes_child = null, $parent = 0, $node_left = 1)
    {
        // Initialize the children array
        if ($nodes_child === null) {
            $nodes_child = array();
            foreach (array_keys($nodes) as $key) {
                $idx = isset($nodes[$key]["parent"]) ? $nodes[$key]["parent"] : 0;
                $nodes_child[$idx][] = $key;
            }
        }
        if (isset($nodes[$parent])) {
            $nodes[$parent]["left"] = $node_left;
            $node_left++;
        }

        // Convert the children array recursively
        foreach ($nodes_child[$parent] as $id) {
            $node_left = $this->convertFromAdjacency($nodes, $nodes_child, $id, $node_left);
        }

        if (isset($nodes[$parent])) {
            $nodes[$parent]["right"] = $node_left + 1;
        }

        return $node_left + 1;
    }

    /**
     * Enumerate child nodes of a node
     *
     * @param   mixed   $objective  root node ID or {@link Xoops_Zend_Db_Table_Row_Node} or {@link Xoops_Zend_Db_Clause}
     * @param   array   $cols       columns to be fetched
     * @param   bool    $plain      result format, plain array or hirechical tree
     * @return  array   $ret        associative array of children
     *
     *                              Tree format:
     *                              [id] => array(          // int, node id
     *                                  //[depth] => {0-?}, // int, node depth
     *                                  [node]  => array(), // associative array, node data
     *                                  [child] => array(   // associative array, child nodes
     *                                      [id]    => array(   // int, node id
     *                                          //[depth]   => {0-?},   // int, node depth
     *                                          [node]    => array(),   // associative array, node data
     *                                          [child]   => array(     // associative array, child nodes
     *
     *                              plain format:
     *                              [id] => array(
     *                                  //[depth] => {0-?},     // int, node depth
     *                                  //[node]  => array(),   // associative array, child nodes
     *                              [id] => array(
     *                                  //[depth] => {0-?},     // int, node depth
     *                                  //[node]  => array(),   // associative array, child nodes
     */
    public function enumerate($objective = null, $cols = null, $plain = false)
    {
        $result = array();

        $root = null;
        $singleRoot = false;
        $select = $this->select();
        if (!empty($objective)) {
            if ($objective instanceof Xoops_Zend_Db_Clause) {
                $select->where($objective);
            } else {
                //$singleRoot = true;
                if ($objective instanceof Xoops_Zend_Db_Table_Row_Node) {
                    $row = $objective;
                } elseif (!$row = $this->findRow($objective)) {
                    return false;
                }
                $root = $row;
                $root_id = $root->id;
                $item = $row->toArray();
                if (empty($plain)) {
                    $ret[$root_id] = $item;
                } else {
                    $ret[$root_id] = $item;
                    $result[$root_id] = $item;
                }
                $stack = array();
                $select->where($this->quoteIdentifier("left") . " > ?", $row->left)
                        ->where($this->quoteIdentifier("right") . " < ?", $row->right);
            }
        }
        if (!empty($cols)) {
            $cols = (array) $cols;
            if (!in_array("*", $cols)) {
                if (!in_array($this->left, $cols)) {
                    $cols[] = $this->left;
                }
                if (!in_array($this->right, $cols)) {
                    $cols[] = $this->right;
                }
            }
            $select->from($this, $cols);
        }
        $select->order($this->left . " ASC");
        if (!$rowset = $this->fetchAll($select)) {
            return false;
        }

        // start with the root and an empty stack
        foreach ($rowset as $row) {
            // Initialize a tree or start a new tree when last tree finishes
            if (is_null($root) || $row->left > $root->right) {
                if (!empty($ret) && empty($plain)) {
                    $result += $ret;
                }
                unset($ret);
                $root = $row;
                $root_id = $root->id;
                $item = $row->toArray();
                if (empty($plain)) {
                    $ret[$root_id] = $item;
                } else {
                    $ret[$root_id] = $item;
                    $result[$root_id] = $item;
                }
                $stack = array();
                continue;
            }

            $parent =& $ret[$root_id];
            if (!empty($stack)) {
                // remove nodes with right smaller than current node, which means not ancestors anymore
                $count = count($stack);
                while ($count && $stack[$count - 1]["right"] < $row->right) {
                    array_pop($stack);
                    $count = count($stack);
                }
                if (($count_stack = count($stack)) > 0) {
                    for($i = 0; $i < $count_stack; $i ++) {
                        $parent =& $parent["child"][$stack[$i]["id"]];
                    }
                }
            }

            // add this node to the stack for next node
            $stack[] = array("id" => $row->id, "right" => $row->right);

            //continue;
            // store the node
            $item = $row->toArray();
            if (empty($plain)) {
                $parent["child"][$row->id] = $item;
            } else {
                $parent["child"][$row->id] = $item;
                $result[$row->id] = $item;
            }
        }

        if (empty($plain) && !$singleRoot && !empty($ret)) {
            $result += $ret;
        }
        return $result;
    }
    /**#@-*/
}
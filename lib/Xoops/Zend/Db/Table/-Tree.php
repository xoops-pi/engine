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
 * @copyright       The Xoops Engine http://sourceforge.net/projects/xoops/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @since           3.0
 * @category        Xoops_Zend
 * @package         Db
 * @version         $Id$
 */

abstract classs xoops_Zend_Db_Table_Tree extends xoops_Zend_Db_Model
{
    protected $_primary = "id";
    public $left        = "left";
    public $right       = "right";
    //protected $_depth = "depth";

    /**
     * Classname for row
     *
     * @var string
     */
    protected $_rowClass = 'xoops_Zend_Db_Table_Row_Node';

    /**
     * Classname for rowset
     *
     * @var string
     */
    protected $_rowsetClass = 'xoops_Zend_Db_Table_Rowset_Node';

    /**
     * setOptions()
     *
     * @param array $options
     * @return Zend_Db_Table_Abstract
     */
    public function setOptions(Array $options)
    {
        if (isset($options['left'])) {
            $this->left = (string) $options['left'];
            unset($options['left']);
        }
        if (isset($options['right'])) {
            $this->right = (string) $options['right'];
            unset($options['right']);
        }
        parent::setOptions($options);

        return $this;
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
        if (!$this->left || !$this->right) {
            require_once "Zend/Db/Table/Exception.php";
            throw new Zend_Db_Table_Exception('Both "left" and "right" column names must be supplied.');
        }

        parent::_setupMetadata();

        if (count(array_intersect(array($this->left, $this->right), array_keys($this->_metadata))) < 2) {
            require_once "Zend/Db/Table/Exception.php";
            throw new Zend_Db_Table_Exception('Supplied "left" and "right" were not found.');
        }
    }

}

/**
 * @see Zend_Db_Table_Row_Abstract
 */
require_once 'Zend/Db/Table/Row/Abstract.php';

class xoops_Zend_Db_Model_TreeNode extends Zend_Db_Table_Row_Abstract
{

    /**
     * Transform a column name from the user-specified form
     * to the physical form used in the database.
     * You can override this method in a custom Row class
     * to implement column name mappings, for example inflection.
     *
     * @param string $columnName Column name given.
     * @return string The column name after transformation applied (none by default).
     * @throws Zend_Db_Table_Row_Exception if the $columnName is not a string.
     */
    protected function _transformColumn($columnName)
    {
        if (!is_string($columnName)) {
            require_once 'Zend/Db/Table/Row/Exception.php';
            throw new Zend_Db_Table_Row_Exception('Specified column is not a string');
        }
        if ($columnName == "left") {
            $columnName = $this->getTable()->left;
        } elseif ($columnName == "right") {
            $columnName = $this->getTable()->right;
        }
        // Perform no transformation by default
        return $columnName;
    }
}

/**
 * Managing Hierarchical Data with Nested Set Model
 * Refer to http://dev.mysql.com/tech-resources/articles/hierarchical-data.html for relevant specifications
*/

define("NODE_RENDER_MODE_STRING",       0);
define("NODE_RENDER_MODE_ARRAY",        1);
define("NODE_RENDER_MODE_ARRAY_KEY",    2);
define("NODE_RENDER_MODE_ARRAY_GROUP",  3);

/**
 * Node
 *
 * @author D.J. (phppp)
 * @copyright copyright &copy; Xoops Project
 * @package module::tree
 *
 * {@link ArtObject}
 *
 */

class Node extends XoopsObject
{
    /**
     * Constructor
     *
     */
    function Node($keyName = "node_id", $identifierName = "node_name")
    {
        $this->initVar($keyName,             XOBJ_DTYPE_INT,     null, false);
        $this->initVar($identifierName,     XOBJ_DTYPE_TXTBOX,     "", false);        // Name for the node
        $this->initVar("node_left",         XOBJ_DTYPE_INT,     0, false);
        $this->initVar("node_right",         XOBJ_DTYPE_INT,     0, false);
        $this->initVar("node_depth",         XOBJ_DTYPE_INT,     0, false);
    }

    function isLeaf()
    {
        return $this->getVar("node_right") == $this->getVar("node_left") + 1;
    }

    function isRoot()
    {
        return $this->getVar("node_depth") == 0;
    }
}

/**
 * Node object handler class.
 * @package module::tree
 *
 * @author  D.J. (phppp)
 * @copyright copyright &copy; 2006 The XOOPS Project
 *
 * {@link ArtObjectHandler}
 *
 */

class TreeNodeHandler extends XoopsPersistableObjectHandler
{
    var $alias = array();

    /**
     * Constructor
     *
     * @param object    $db         reference to $xoopsDB; It could be forced to use $xoopsDB, but we would keep it for future possible multi-db connection support
     * @param string    $className    class name of the object
     * @param string    $table        table name
     * @param string    $keyName
     * @param string    $identifierName
     *
     **/
    function TreeNodeHandler(&$db, $className = "Node", $tableName = "", $keyName = "node_id", $identifierName = "node_name")
    {
        // Force them!
        //$keyName         = "node_id";
        //$identifierName     = "node_name";
        $this->XoopsPersistableObjectHandler($db,
                                $tableName,
                                $className        ? $className : "Node",
                                $keyName        ? $keyName : "node_id",
                                $identifierName    ? $identifierName : "node_name"
                                );
        if (empty($tableName)) {
            $this->setTable();
        }
    }

    function setTable($tableName = "")
    {
        $tableName = empty($tableName) ? "tree_node_".strtolower($this->className) : strtolower($tableName);
        $this->table = $this->db->prefix( $tableName );
    }

    function getTable($withPrefix = false)
    {
        return $withPrefix ? $this->table : substr($this->table, strlen($this->db->prefix()) + 1);
    }

    /*------------------------------------ Node operation ------------------------------------*/
    /**
     * Insert a node
     *
     * @param    object    $object    node
     * @param    bool    $force    force to update db
     */
    function insert(&$object, $force = true)
    {
        // Clean alias
        foreach ($this->alias as $internal => $external) {
            if ($object->vars[$external]["changed"] && !$object->vars[$internal]["changed"]) {
                $object->vars[$internal] = $object->vars[$external];
            }
            $this->destroyVars(array_values($this->alias));
        }
        return parent::insert($object, $force);
    }

    /**
     * Add a node
     *
     * @param    object    $object    node
     * @param    array    $param    node parameters for new node section: check self::getNodeParam()
     */
    function add(&$object, $param = array())
    {
        if ( !$node = $this->getNodeParam($param) ) {
            return false;
        }
        $node_left = MAX(1, @$node["node_left"], @$node["node_right"]);
        if (!$this->shift($node_left, "+", 2)) {
            return false;
        }
        $object->setVar("node_depth", $node["node_depth"]);
        $object->setVar("node_left", $node_left);
        $object->setVar("node_right", $node_left + 1);

        return $this->insert($object);
    }

    /**
     * Move a node
     *
     * @param    object    $object    node
     * @param    array    $param    node parameters for new node section: check self::getNodeParam()
     */
    function move(&$object, $param = array())
    {
        if ( !$node = $this->getNodeParam($param) ) {
            return false;
        }
        $orig_node_left = $object->getVar("node_left");
        $orig_node_right = $object->getVar("node_right");
        $increment = $orig_node_right - $orig_node_left;

        if (!empty($node["node_left"])) {
            $target_node_left = $node["node_left"];
               $target_node_right = $target_node_left + $increment;
           } elseif (!empty($node["node_right"])) {
               $target_node_right = $node["node_right"];
               $target_node_left = $target_node_right - $increment;
        } else {
            $target_node_left = 1;
               $target_node_right = $target_node_left + $increment;
        }

        if ($target_node_left > $orig_node_left) {
            $operator = "-";
            $left_start = $orig_node_left + $increment + 1;
            $right_end = $target_node_right;
        } else {
            $operator = "+";
            $left_start = $target_node_left;
            $right_end = $orig_node_left - 1;
        }

        if (!$this->shift($left_start, $operator, 2, $right_end)) {
            return false;
        }
        $object->setVar("node_depth", $target_node_depth);
        $object->setVar("node_left", $target_node_left);
        $object->setVar("node_right", $target_node_left + 1);

        return $this->insert($object);
    }

    /**
     * Get node param of a single pseudo node to be inserted
     *
     * @param    array    $param    node parameters for a single pseudo node object:
     *                        "left"        the node to the left of current node, if available
     *                        "right"        the node to the right of current node, if available
     *                        "parent"    parent node of current node, if available
     * @return    array    left, right, depth
     */
    function getNodeParam($param = array())
    {
        $node = array();
        // If "left" is set, the list is to be resorted
        if ( !empty($param["left"]) ) {
            if ( !$node_obj =& $this->get($param["left"]) ) {
                return $node;
            }
            $node["node_left"] = $node_obj->getVar("node_right") + 1;
            $node["node_depth"] = $node_obj->getVar("node_depth");
        // If "right" is set, the list is to be resorted
        } elseif ( !empty($param["right"]) ) {
            if ( !$node_obj =& $this->get($param["right"]) ) {
                return $node;
            }
            $node["node_right"] = $node_obj->getVar("node_left") - 1;
            $node["node_depth"] = $node_obj->getVar("node_depth");
        // If "parent" is set, the list is to be resorted
        } elseif ( !empty($param["parent"]) ) {
            if ( !$node_obj =& $this->get($param["parent"]) ) {
                return $node;
            }
            $node["node_right"] = $node_obj->getVar("node_right");
            $node["node_depth"] = $node_obj->getVar("node_depth") + 1;
        // Root node
        } else {
            $node["node_left"] = $this->getSideExtreme("right", 0) + 1;
            $node["node_depth"] = 0;
        }

        return $node;
    }

    /**
     * Get extreme value of node_left or node_right
     *
     * @param    string    $side    node side, default as "right"
     * @param    int        $node    parent node ID, 0 for root
     */
    function getSideExtreme($side = "right", $node = 0)
    {
        $side = ($side == "left") ? "left" : "right";
        $node = intval($node);
        if (!empty($node)) {
            $sql = "SELECT node_{$side} FROM {$this->table} WHERE {$this->keyName} = {$node}";
        } else {
            $sql = "SELECT ".( ($side == "left") ? "MIN" : "MAX" )."(node_{$side}) FROM {$this->table}";
        }
        if (!$result = $this->db->query($sql)) {
            return false;
        }
        list($node_side) = $this->db->fetchRow($result);

        return $node_side;
    }


    /**
     * Calculate depth for a node
     *
     * The method is supposed to be called very rarely, perhaps only in a synchronization case
     *
     * @param    object    $object    node
     */
    function calDepth(&$object)
    {
        $sql =     "    SELECT COUNT(*) ".
                "    FROM {$this->table} ".
                "    WHERE ".
                "        node_left < ". $object->getVar("node_left") .
                "        AND node_right > ". $object->getVar("node_right");
        if (!$result = $this->db->query($sql)) {
            return false;
        }
        list($node_depth) = $this->db->fetchRow($result);

        return $node_depth;
    }

    /**
     * Delete a node
     *
     * @param    object    $object    node
     */
    function delete(&$object, $force = true)
    {
        // Clean alias
        foreach ($this->alias as $internal => $external) {
            $object->vars[$internal] = $object->vars[$external];
        }
        return parent::delete($object, $force);
    }

    /**
     * Remove a node, with different options dealing with its children
     *
     * @param    object    $object    node
     * @param    bool    $delChildren    delete all children
     */
    function remove(&$object, $delChildren = false)
    {
        $node = $object->getValues(array($this->keyName, "node_left", "node_right", "node_depth"));
        if (!$this->delete($object)) return false;
        if ($delChildren) {
            if ( $children = $this->render($object, 0, NODE_RENDER_MODE_ARRAY_KEY) ) {
                $this->deleteAll(new Criteria($this->keyName, "(".implode(", ", array_keys($children)).")", "IN"));
                $children = null;
            }
            $this->shift($node["node_right"] + 1, "-", $node["node_right"] - $node["node_left"] + 1);
        } else {
            $this->shift($node["node_left"] + 1, "-", 1, $node["node_right"] - 1);
            $this->shift($node["node_right"] + 1, "-", 2);
        }

        return true;
    }

    /**
     * Shift a list of nodes
     *
     * @param    int        $left_start    starting value of node_left
     * @param    string    $operator    "+" or "-"
     * @param    int        $increment    count of position increment
     * @param    int        $right_end    end value of right_end, if gt 0
     */
    function shift($left_start, $operator, $increment, $right_end = 0)
    {
        $right_start = $left_start /*+ 1*/;
        $operator == "-" ? "-" : "+";
        $sql_node = "    UPDATE {$this->table} ".
                    "    SET ".
                    "        node_left = CASE WHEN node_left >= {$left_start} THEN node_left {$operator} {$increment} ELSE node_left END, ".
                    "        node_right = CASE WHEN (node_left >= {$left_start} OR node_right >= {$right_start}) THEN node_right {$operator} {$increment} ELSE node_right END".
                        ( empty($right_end) ? "":
                    "    WHERE node_right <= {$right_end}"
                        )
                    ;
        if (!$result = $this->db->queryF($sql_node)) {
            return false;
        }

        return true;
    }


    /**
     * Get ancestor nodes
     *
     * @param    mixed    $object        object or ID for current node
     * @param    array    $tags        fields to be fetched
     * @param    bool    $asObject
     */
    function &getAncestors($object, $tags = null, $asObject = false)
    {
        if (!is_object($object)) {
            $object =& $this->get($object);
        }
        $tags = !empty($tags) ? $tags : array($this->keyName, $this->identifierName, "node_depth");
        if (!in_array($this->keyName, $tags)) {
            $tags[] = $this->keyName;
        }
        $criteria = new CriteriaCompo(new Criteria("node_depth", $object->getVar("node_depth"), "<"));
        $criteria->add(new Criteria("node_left", $object->getVar("node_left"), "<"));
        $criteria->add(new Criteria("node_right", $object->getVar("node_right"), ">"));
        $criteria->setSort("node_left"); // Or to sort by node_depth ?
        $criteria->setOrder("ASC");
        $ancestors = $this->getAll($criteria, $tags, $asObject);

        return $ancestors;
    }

    /**
     * Build breadcrumbs for a node
     *
     * @param    int        $node            current node ID
     * @param    bool    $skipCurrent    exclude current site
     * @param    bool    $keyAsIndex        use object key as Index of result array
     * @return    array    associative array of sites: id, title
     */
    function buildBreadcrumbs($node_obj, $skipCurrent = false, $keyAsIndex = false)
    {
        $breadcrumbs = array();
        $ancestors = $this->getAncestors($node_obj);

        $i = 0;
        foreach($ancestors as $ancestor) {
            $breadcrumbs[ $keyAsIndex ? $ancestor[$this->keyName] : $i++ ] = array(
                                "id"    => $ancestor[$this->keyName],
                                "title"    => $ancestor[$this->identifierName],
                                "depth"    => $ancestor["node_depth"]
                                );
        }
        if (!$skipCurrent && is_object($node_obj)) {
            $breadcrumbs[ $keyAsIndex ? $node_obj->getVar($this->keyName) : $i++ ] = array(
                                "id"    => $node_obj->getVar($this->keyName),
                                "title"    => $node_obj->getVar($this->identifierName),
                                "depth"    => $node_obj->getVar("node_depth")
                                );
        }
        return $breadcrumbs;
    }

    /*------------------------------------ Section operation ------------------------------------*/
    /**
     * Remove a section
     *
     * @param    object    $object    root node of the section
     */
    function prune(&$object)
    {
        return $this->remove($object, true);
    }

    /**
     * Add a section from formulated array
     *
     * @param    array    $nodes    formulated array of nodes: node_name, node_left, node_right, node_depth, ...
     * @param    array    $param    node parameters for new node section: check self::getNodeParam()
     *
     */
    function graft($nodes, $param = array())
    {
        if ( empty($nodes) || !$node = $this->getNodeParam($param) ) {
            return false;
        }
        $node_left = MAX(1, @$node["node_left"], @$node["node_right"]);
        if (!$this->shift($node_left, "+", 2 * count($nodes))) {
            return false;
        }
        $side_increment = $node_left - 1;
        foreach ($nodes as $_node) {
            $object =& $this->create();
            $object->setVar("node_depth", $node["node_depth"] + $_node["node_depth"]);
            $object->setVar("node_left", $_node["node_left"] + $side_increment);
            $object->setVar("node_right", $_node["node_right"] + $side_increment);
            foreach (array_keys($object->vars) as $key) {
                if (isset($_node[$key]) && !in_array($key, array($this->keyName, "node_depth", "node_left", "node_right"))) {
                    $object->setVar($key, $_node[$key], true);
                }
            }
            $this->insert($object, true);
        }
        return true;
    }

    /**
     * Import a section from an adjacency array
     *
     * @param    array    $nodes    array of adjacency with node_id as key: id, pid, tags ...
     * @param    array    $param    node parameters for new node section: check self::getNodeParam()
     *
     */
    function import($nodes, $param = array())
    {
        if ( empty($nodes) ) {
            return false;
        }
        $this->convertFromAdjacency($nodes);

        return $this->graft($nodes, $param);
    }

    /**
     * Convert a section from an adjacency array
     *
     * @param    array    $nodes            array of nodes: id, pid, tags ...
     * @param    array    $nodes_child    array of child IDs of current node
     * @param    int        $parent            parent ID
     * @param    int        $node_left        node_left of current node
     *
     */
    function convertFromAdjacency(&$nodes, $nodes_child = null, $parent = 0, $node_left = 1)
    {
        // Initialize the children array
        if ($nodes_child === null) {
            $nodes_child = array();
            foreach(array_keys($nodes) as $key) {
                $nodes_child[intval( @$nodes[$key]["pid"] )][] = $key;
            }
        }
        if (isset($nodes[$parent])) {
            $nodes[$parent]["node_depth"] = empty($nodes[$parent]["pid"]) ? 0 : $nodes[ $nodes[$parent]["pid"] ]["node_depth"] + 1;
            $nodes[$parent]["node_left"] = $node_left;
            $node_left++;
        }

        // Convert the children array recursively
        if (isset($nodes_child[$parent])) {
            foreach ($nodes_child[$parent] as $id) {
                $node_left = $this->convertFromAdjacency($nodes, $nodes_child, $id, $node_left);
            }
        }

        if (isset($nodes[$parent])) {
            $nodes[$parent]["node_right"] = $node_left + 1;
        }

        return $node_left + 1;
    }

    /**
     * Enumerate child nodes of a node
     *
     * @param    mixed    $node    object of root node or null for whole tree
     * @param    int        $depth    depth of child nodes, 0 for all
     * @param    bool    $asObject
     * @param    array    $tags    fields to be fetched
     * @return    array    $ret    associative array of children
     *                            [node_id] =>
     *                                        [node]    => node object
     *                                        [child]    => children
     *                                                    [child_node_id]    =>
     *                                                        [node]    => node object
     *                                                        [child]    => children
     */
    function &enumerate($node = null, $depth = 0, $asObject = false, $tags = null)
    {
        $ret = null;

        $limit_left = "";
        $limit_depth = -1;
        if (is_object($node)) {
            $limit_left = ( $node->getVar("node_left") + 1 ). " AND " . ( $node->getVar("node_right") - 1 );
            if (!empty($depth)) {
                $limit_depth = $node->getVar("node_depth") + $depth;
            }
            $root_id = $node->getVar($this->keyName);
        } else {
            $root_id = 0;
            if (!empty($depth)) {
                $limit_depth = $depth -1;
            }
        }
        $selects = empty($tags) ? array("*") : array_unique( array_merge($tags, array($this->keyName, "node_right")) );

        $sql =     "    SELECT ". implode(", ", $selects) .
                "    FROM {$this->table}".
                "    WHERE 1=1 ".
                        (empty($limit_left) ? "" :
                "         AND node_left BETWEEN {$limit_left}"
                        ).
                        ($limit_depth < 0 ? "" :
                "         AND node_depth <= {$limit_depth}"
                        ).
                "    ORDER BY node_left ASC"
                        ;
        if (!$result = $this->db->query($sql)) {
            //xoops_error($this->db->error());
            return $ret;
        }

        // start with the root and an empty stack
        $ret[$root_id]["child"] = array();
        $stack = array();
        while ( $row = $this->db->fetchArray($result) ) {
            $parent =& $ret[$root_id];
            if ( !empty($stack) ) {
                // remove nodes with node_right smaller than current node, which means not ancestors anymore
                $count = count($stack);
                while ( $count && $stack[$count - 1]["node_right"] < $row["node_right"]) {
                    array_pop($stack);
                    $count = count($stack);
                }
                if ( ($count_stack = count($stack)) > 0 ) {
                    for($i = 0; $i < $count_stack; $i ++) {
//                        $parent =& $parent["child"][$stack[$i]["id"]]["node"];
                        $parent =& $parent["child"][$stack[$i]["id"]];
                    }
                }
            }

            // add this node to the stack
            $stack[] = array("id" => $row[$this->keyName], "node_right" => $row["node_right"]);

            //continue;
            // store the node
            $object =& $this->create(false);
            $keys = empty($tags) ? array_keys($object->vars) : $tags;
            foreach ($keys as $key) {
                $object->assignVar($key, $row[$key]);
            }
            if ($asObject){
//                $parent["child"][$row[$this->keyName]]["node"] = $object;
                $parent["child"][$row[$this->keyName]]["node"] = $object;
            } else {
//                $parent["child"][$row[$this->keyName]] = $object->getValues($tags);
                $parent["child"][$row[$this->keyName]] = $object->getValues($tags);
            }
        }

        return $ret;
    }

    /**
     * Display child nodes of a node
     *
     * @param    mixed    $node    object of root node or null for whole tree
     * @param    int        $depth    depth of child nodes, 0 for all
     * @param    bool    $mode    result mode:
     *                                        0 - nested string
     *                                        1 - associative array (node_id, node_name, node_depth); 2 - associative array, node_id as key
     *                                        2 - associative array, node_id as key
     *                                        3 - associative array, node_id as key, group by first level of children
     * @param    string    $indent    for child title
     * @return    mixed    $ret    nested string or associative array of children
     */
    function render($node = null, $depth = 0, $mode = NODE_RENDER_MODE_STRING, $indent = "--", $tags = array())
    {
        $ret = array();

        $limit_left = "";
        $limit_depth = -1;
        $root_depth = 0;
        if (is_object($node)) {
            $root_id = $node->getVar($this->keyName);
            $root_depth = $node->getVar("node_depth");
            $limit_left = ( $node->getVar("node_left") + 1 ). " AND " . ( $node->getVar("node_right") - 1);
        } else {
            $root_id = 0;
            $root_depth = -1;
        }
        if (!empty($depth)) {
            $limit_depth = $root_depth + $depth;
        }

        $selects = empty($tags) ? array("*") : array_unique( array_merge($tags, array($this->keyName, $this->identifierName, "node_depth")) );

        $sql =     "    SELECT ". implode(", ", $selects) .
                "    FROM {$this->table}".
                "    WHERE 1=1 ".
                        (empty($limit_left) ? "" :
                "         AND node_left BETWEEN {$limit_left}"
                        ).
                        ($limit_depth < 0 ? "" :
                "         AND node_depth <= {$limit_depth}"
                        ).
                "    ORDER BY node_left ASC"
                        ;
        if (!$result = $this->db->query($sql)) {
            return $ret;
        }

        $nest = "";
        $top_node = 0;

        while ( $row = $this->db->fetchArray($result) ) {
            $node_title = ( empty($indent) ? "" : @str_repeat($indent, $row["node_depth"] - $root_depth -1) . " " ) . htmlspecialchars($row[$this->identifierName]);

            if ( NODE_RENDER_MODE_STRING == $mode) {
                $nest .= $node_title."<br />";
                continue;
            }

            $object =& $this->create(false);
            $node_data = array( "id" => $row[$this->keyName], "title" => $node_title, "depth" => $row["node_depth"]);
            $keys = empty($tags) ? array_keys($object->vars) : $tags;
            foreach ($keys as $key) {
                $object->assignVar($key, $row[$key]);
                $node_data[$key] = $object->getVar($key);
            }

            switch($mode) {

            case NODE_RENDER_MODE_ARRAY:
                $ret[] = $node_data;
                break;

            case NODE_RENDER_MODE_ARRAY_GROUP:
                if ($row["node_depth"] == $root_depth + 1) {
                    $top_node = $row[$this->keyName];
                    $ret[ $row[$this->keyName] ] = $node_data;
                } else {
                    $ret[ $top_node ]["child"][$row[$this->keyName]] = $node_data;
                }
                break;

            case NODE_RENDER_MODE_ARRAY_KEY:
            default:
                $ret[ $row[$this->keyName] ] = $node_data;
                break;
            }
        }

        return empty($mode) ? $nest : $ret;
    }

    /**
     * Search child nodes ID of a node
     *
     * @param    string    $term    the term to be matched
     * @param    mixed    $node    object of root node or null for whole tree
     * @param    int        $depth    depth of child nodes, 0 for all
     * @param    array    $searchin    searching scope, fields
     * @param    string    $mode    matching mode, potential values: exact, like, regexp
     * @param    int        $limit
     * @param    int        $start
     * @return    array    $ret    array of child ID
     */
    function search($term, $node = null, $depth = 0, $searchin = array(), $mode = "like", $limit = null, $start = null, $criteria = null)
    {
        // Initialize parameters
        $limit_left = $limit_right = 0;
        $limit_depth = 0;
        $root_depth = 0;

        if (is_object($node)) {
            $limit_left = $node->getVar("node_left") + 1;
            $limit_right = $node->getVar("node_right") - 1;
            $root_depth = $node->getVar("node_depth");
        }
        if (!empty($depth)) {
            $limit_depth = $root_depth + $depth;
        }

        // parse searching scope
        if (empty($searchin) || !is_array($searchin)) {
            $searchin = array($this->identifierName);
        }

        // parse matching mode
        switch(strtolower($mode)) {
        case "exact":
            $operator = "=";
            break;
        case "regexp":
            $operator = "REGEXP";
            break;
        case "like":
        default:
            $operator = "LIKE";
            break;
        }

        // parse matching mode
        $term = $this->db->quoteString($term);

        // prepare searching query
        foreach ($searchin as $_search) {
            $searchs[] = "`{$_search}` {$operator} {$term}";
        }

        $sql =     "    SELECT " . $this->keyName.
                "    FROM {$this->table}".
                "    WHERE 1=1 ".
                        (empty($limit_left) ? "" :
                "         AND node_left BETWEEN {$limit_left} AND {$limit_right}"
                        ).
                        ($limit_depth <= 0 ? "" :
                "         AND node_depth <= {$limit_depth}"
                        ).
                "        AND (".implode(" OR ", $searchs).")".
                "    ORDER BY node_left ASC";

        // execute query
        if (!$result = $this->db->query($sql, $limit, $start)) {
            //xoops_error($this->db->error());
            return $ret;
        }

        // fetch result
        $ret = array();
        while ( list($node_id) = $this->db->fetchRow($result) ) {
            $ret[] = $node_id;
        }

        return array_unique($ret);
    }

}
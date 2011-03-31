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
 * @package         Session
 * @version         $Id$
 */

class Xoops_Zend_Session_SaveHandler_DbTable
    extends Zend_Session_SaveHandler_DbTable
{
    /**
     * The table name.
     *
     * @var string
     */
    protected $_name = "session";

    /**
     * Session table last modification time column
     *
     * @var string
     */
    protected $_modifiedColumn = "modified";

    /**
     * Session table lifetime column
     *
     * @var string
     */
    protected $_lifetimeColumn = "lifetime";

    /**
     * Session table data column
     *
     * @var string
     */
    protected $_dataColumn = "data";

    public function __construct($config)
    {
        $config_default = array(
            'name'              => 'session', //table name as per Zend_Db_Table
            'primary'           => array(
                'id',   //the sessionID given by PHP
            ),
            'primaryAssignment' => array(
                //you must tell the save handler which columns you
                //are using as the primary key. ORDER IS IMPORTANT
                'sessionId', //first column of the primary key is of the sessionID
            ),
            'modifiedColumn'    => 'modified',     //time the session should expire
            'dataColumn'        => 'data', //serialized data
            'lifetimeColumn'    => 'lifetime',     //end of life for a specific record
        );
        $config = array_merge($config_default, $config);
        parent::__construct($config);
    }

    /**
     * Initialize table and schema names
     *
     * @return void
     * @throws Zend_Session_SaveHandler_Exception
     */
    protected function _setupTableName()
    {
        if (empty($this->_name) && basename(($this->_name = session_save_path())) != $this->_name) {
            /**
             * @see Zend_Session_SaveHandler_Exception
             */
            require_once 'Zend/Session/SaveHandler/Exception.php';

            throw new Zend_Session_SaveHandler_Exception('session.save_path is a path and not a table name.');
        }

        if (strpos($this->_name, '.')) {
            list($this->_schema, $this->_name) = explode('.', $this->_name);
        }
        $this->_name = $this->getAdapter()->prefix($this->_name);
    }
}

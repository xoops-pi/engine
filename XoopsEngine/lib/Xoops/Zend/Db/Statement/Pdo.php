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

class Xoops_Zend_Db_Statement_Pdo extends Zend_Db_Statement_Pdo
{
    /**
     * Prepare a string SQL statement and create a statement object.
     *
     * @param string $sql
     * @return void
     * @throws Zend_Db_Statement_Exception
     */
    protected function _prepare($sql)
    {
        try {
            $this->_stmt = $this->_adapter->getConnection()->prepare($sql);
        } catch (PDOException $e) {
            require_once 'Zend/Db/Statement/Exception.php';
            throw new Zend_Db_Statement_Exception($e->getMessage() . ": " . $sql, $e->getCode(), $e);
        }
    }

    /**
     * Executes a prepared statement.
     *
     * @param array $params OPTIONAL Values to bind to parameter placeholders.
     * @return bool
     */
    public function execute(array $params = null)
    {
        /*
         * Simple case - no query profiler to manage.
         */
        if ($this->_queryId === null) {
            //return $this->_execute($params);
            try {
                return $this->_execute($params);
            } catch (PDOException $e) {
                // ?
            }
        }

        /*
         * Do the same thing, but with query profiler
         * management before and after the execute.
         */
        $prof = $this->_adapter->getProfiler();
        $qp = $prof->getQueryProfile($this->_queryId);
        if ($qp->hasEnded()) {
            $this->_queryId = $prof->queryClone($qp);
            $qp = $prof->getQueryProfile($this->_queryId);
        }
        if ($params !== null) {
            $qp->bindParams($params);
        } else {
            $qp->bindParams($this->_bindParam);
        }
        $qp->start($this->_queryId);
        $retval = null;
        try {
            $retval = $this->_execute($params);
        } catch (Zend_Db_Statement_Exception $e) {
            $qp->setException($e->getMessage());
            //throw new Zend_Db_Statement_Exception($e->getMessage(), (int) $e->getCode(), $e);
        }
        //$qp->setQuery($this->_stmt->queryString);
        $prof->queryEnd($this->_queryId);

        return $retval;
    }
}

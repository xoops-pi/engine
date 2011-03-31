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

class Xoops_Zend_Db_Profiler_Query extends Zend_Db_Profiler_Query
{
    /**
     * Exception message
     *
     * @var string
     */
    protected $_exception = '';

    /**
     * Set exception message
     *
     * @return string
     */
    public function setException($exception)
    {
        $this->_exception = $exception;
    }

    public function getException()
    {
        return $this->_exception;
    }

    /**
     * Set the original SQL text of the query.
     *
     * @return string
     */
    public function setQuery($query)
    {
        $this->_query = $query;
    }

    /**
     * Get the time that the query started from.
     *
     * @return float|false
     */
    public function getStartedTime()
    {
        return $this->_startedMicrotime;
    }
}
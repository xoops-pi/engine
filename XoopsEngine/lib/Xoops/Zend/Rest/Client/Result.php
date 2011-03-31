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
 * @package         Rest
 * @version         $Id$
 */

class Xoops_Zend_Rest_Client_Result extends Zend_Rest_Client_Result
{
    /**
     * Constructor
     *
     * @param string $data XML Result
     * @return void
     */
    public function __construct($data)
    {
        set_error_handler(array($this, 'handleXmlErrors'));
        $this->_sxml = simplexml_load_string($data);
        restore_error_handler();
        if ($this->_sxml === false) {
            if ($this->_errstr === null) {
                $message = "An error occured while parsing the REST response with simplexml.";
            } else {
                $message = "REST Response Error: " . $this->_errstr;
                $this->_errstr = null;
            }
            $data = "<?xml version='1.0' encoding='utf-8'?><error>" . strip_tags($message) . "</error>";
            $this->_sxml = simplexml_load_string($data);
        }
    }
}

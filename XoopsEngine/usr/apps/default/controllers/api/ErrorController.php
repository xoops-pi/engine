<?php
/**
 * Error controller for REST service
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
 * @category        Xoops_Module
 * @package         Default
 * @version         $Id$
 */

class Default_ErrorController extends Xoops_Zend_Controller_Action_Api
{
    public function __call($method, $args)
    {
        $message = "";
        $errors = $this->_getParam('error_handler');
        $error_type = "unknown";
        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                $error_type = "404";
                break;
            default:
                break;
        }
        if ($error_type == "404" || 404 == $errors->exception->getCode()) {
            $message = XOOPS::_('The page you requested was not found.');
        } elseif (403 == $errors->exception->getCode()) {
            $message = XOOPS::_('You are not allowed to access this page.');
        } else {
            //$message = XOOPS::_('An unexpected error occurred. Please try again later.');
            $message = XOOPS::_($errors->exception->getMessage());
        }
        $this->data = array(
            "title"         => "Error",
            "description"   => $message
        );
    }
}
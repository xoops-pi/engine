<?php
/**
 * Default error controller for feed
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

class Default_ErrorController extends Xoops_Zend_Controller_Action_Feed
{
    public function __call($method, $args)
    {
        $message = "";
        $errors = $this->_getParam('error_handler');

        $errorCode = $errors->exception->getCode();
        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                $errorCode = 404;
                break;
            default:
                break;
        }

        if (404 == $errorCode) {
            $message = XOOPS::_('The page you requested was not found.');
        } elseif (403 == $errorCode || 401 == $errorCode) {
            $message = XOOPS::_('You are not allowed to access this page.');
        } else {
            $message = XOOPS::_('An unexpected error occurred. Please try again later: ') . $errors->exception->getMessage();
        }
        $this->feed("title", "Error");
        $this->feed("description", $message);
    }
}
<?php
/**
 * User REST profile controller
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
 * @package         User
 * @version         $Id$
 */

class User_ProfileController extends Xoops_Zend_Controller_Action_Api
{
    public function getAction()
    {
        $this->data = array(
            "module"    => "user",
            "action"    => "get",
            "data"      => array(
                "sub"   => "SubOne",
                "baby"  => "ChildNode"
            )
        );
    }

    public function testAction()
    {
        $this->data = array(
            "module"    => "user",
            "action"    => "test",
            "data"      => $this->getRequest()->getParams()
        );
    }
}

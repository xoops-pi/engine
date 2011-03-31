<?php
/**
 * Widget service
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

class Default_WidgetController extends Xoops_Zend_Controller_Action_Api
{

    // Handle GET and return a specific resource item
    public function getAction()
    {
        //$this->format = $this->getRequest()->setParam("format", "html");
        $this->data = array(
            "title"         => "Widget",
            "description"   => "Widget created by " . __METHOD__
        );
    }

    public function __call($method, $args)
    {
        $this->format = $this->getRequest()->setParam("format", "html");
        $this->data = array(
            "title"         => "Widget",
            "description"   => "Widget created by " . __METHOD__
        );
    }
}
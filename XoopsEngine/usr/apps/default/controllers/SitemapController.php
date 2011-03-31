<?php
/**
 * Sitemap controller
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

class Default_SitemapController extends Xoops_Zend_Controller_Action
{
    public function indexAction()
    {
        $this->setTemplate("");
        $navigation = XOOPS::service("registry")->navigation->read("front", "default", "guest");
        $container = new Xoops_Zend_Navigation($navigation);
        $this->view->navigation($container);
        $sitemap = $this->view->navigation()->menu()->setUlClass("sitemap")->render();
        echo $sitemap;
    }
}
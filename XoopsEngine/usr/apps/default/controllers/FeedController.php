<?php
/**
 * Default feed controller
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

class Default_FeedController extends Xoops_Zend_Controller_Action
{
    public function indexAction()
    {
        $this->getFrontController()->setParam("section", "front");
        $this->getHelper("layout")->getLayoutInstance()->setLayout("layout");
        $this->setTemplate("feed.html");
        $modules = XOOPS::service('registry')->module->read();
        $moduleList = array();
        foreach ($modules as $dirname => $data) {
            if (empty($data["active"])) continue;
            $moduleList[$dirname] = $data["name"];
        }
        $modelPage = XOOPS::getModel("page");
        $select = $modelPage->select()->where("section = ?", "feed")
                                        ->where("module IN (?)", array_keys($moduleList))
                                        ->order(array("module", "controller", "action"));
        $rowset = $modelPage->fetchAll($select);
        $feedList = array();
        $types = array("rss", "atom");
        foreach ($rowset as $row) {
            $data = array();
            $data["title"] = $row->title;
            foreach ($types as $type) {
                $data["url"][$type] = $this->getFrontController()->getRouter()->assemble(
                        array(
                            "module"        => $row->module,
                            "controller"    => $row->controller ? $row->controller : null,
                            "action"        => $row->action ? $row->action : null,
                            "type"          => $type,
                        ),
                        "feed"
                );
            }
            $feedList[$row->module][] = $data;
        }
        $this->template->assign("feeds", $feedList);
        $this->template->assign("modules", $moduleList);
    }
}
<?php
/**
 * System feed index controller
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
 * @package         System
 * @version         $Id$
 */

class System_IndexController extends Xoops_Zend_Controller_Action_Feed
{
    public function indexAction()
    {
        $this->feed("title", XOOPS::_("What's new"));
        $model = Xoops::service('module')->getModel("update");
        $select = $model->select()->order("time DESC")->limit(10);
        $rowset = $model->fetchAll($select);
        foreach ($rowset as $row) {
            $entry = array();
            $entry["title"] = $row->title;
            $entry["description"] = $row->content;
            $entry["lastUpdate"] = $row->time;
            $entry["link"] = $this->getHref($row);
            $this->entry($entry);
        }
    }

    protected function getHref($row)
    {
        //global $xoops;

        $uri = "";
        if (isset($row->uri)) {
            $uri = $row->uri;
        } else {
            $uri = $this->getFrontController()->getRouter()->assemble(
                    array(
                        "module"        => $row->module,
                        "controller"    => $row->controller,
                        "action"        => $row->action,
                        "params"        => empty($row->params) ? array() : parse_str($row->params)
                    ),
                    $row->route ? $row->route : "default"
            );
        }

        return Xoops::url($uri, true);
    }
}
<?php
/**
 * User admin index (users) controller
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

class User_PendingController extends Xoops_Zend_Controller_Action
{
    public function indexAction()
    {
        $this->setTemplate("pending_list.html");
        $module = $this->getRequest()->getModuleName();
        $page = $this->getRequest()->getParam("page", 1);
        $configs = XOOPS::service("registry")->config->read("admin", $module);
        $itemCountPerPage = !empty($configs["items_per_page"]) ? $configs["items_per_page"] : 10;
        $userModel = XOOPS::getModel("user");

        $select = $userModel->select()->where("active = ?", 0);
        $paginator = Xoops_Zend_Paginator::factory($select);
        $paginator->setItemCountPerPage($itemCountPerPage);
        $paginator->setCurrentPageNumber($page);

        $view = $this->view;
        $editLink = function ($id, $action) use ($view)
        {
            $action = ($action == "delete") ? "delete" : "edit";
            $link = $view->url(
                array(
                    "action"        => $action,
                    "controller"    => "index",
                    "id"            => $id,
                ),
                "admin",
                false
            );
            return $link;
        };
        $viewLink = function ($id) use ($view)
        {
            $link = $view->url(
                array(
                    "user"      => $id,
                ),
                "user"
            );
            return $link;
        };
        $activateLink = function ($id) use ($view)
        {
            $link = $view->url(
                array(
                    "action"    => "activate",
                    "user"      => $id,
                ),
                "admin",
                false
            );
            return $link;
        };

        $offset = ($page - 1) * $itemCountPerPage;
        $users = array();
        $select = $userModel->select()->from($userModel, array("id", "identity", "name", "email"))->where("active = ?", 0)->order("id DESC")->limit($itemCountPerPage, $offset);
        $rowset = $userModel->fetchAll($select);
        foreach ($rowset as $row) {
            $users[$row->id] = $row->toArray();
            $users[$row->id]["edit"] = $editLink($row->id, "edit");
            $users[$row->id]["view"] = $viewLink($row->id);
            $users[$row->id]["delete"] = $editLink($row->id, "delete");
            $users[$row->id]["activate"] = $activateLink($row->id);
        }
        $this->template->assign("users", $users);
        $this->template->assign("paginator", $paginator);
    }

    public function activateAction()
    {
        $module = $this->getRequest()->getModuleName();
        if (!$id = $this->getRequest()->getParam("id", 0)) {
            $message = XOOPS::_("User ID is not specified.");
        }
        if (!$userRow = XOOPS::getModel("user")->findRow($id)) {
            $message = XOOPS::_("User recored is not found.");
        }
        $userRow->active = 1;
        $userRow->role = Xoops_Acl::MEMBER;
        $status = $userRow->save();
        if ($status) {
            $message = XOOPS::_("User is activated.");
        } else {
            $message = XOOPS::_("User is not activated.");
        }
        $urlOptions = array(
            'action'        => 'index',
            'controller'    => 'activate',
            'module'        => 'user',
            'route'         => 'admin',
            'reset'         => true,
        );
        $options = array("time" => 3, "message" => $message);
        $this->redirect($urlOptions, $options);
    }
}

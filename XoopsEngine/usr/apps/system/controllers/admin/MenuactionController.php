<?php
/**
 * System admin menu action controller
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

class System_MenuactionController extends Xoops_Zend_Controller_Action_Admin
{
    public function readAction()
    {
        $id = $this->getRequest()->getParam("id");
        if (empty($id)) {
            return false;
        }
        $modelPage = XOOPS::getModel("navigation_page");
        if (!$page = $modelPage->findRow($id)) {
            return false;
        }

        $result = array(
            "status"    => 1,
            "params"    => $page->toArray(),
        );
        if ($this->getRequest()->isXmlHttpRequest()) {
            echo json_encode($result);
        }
        return;
    }

    public function addAction()
    {
        $posts = $this->getRequest()->getPost();
        if (empty($posts["label"]) || empty($posts["navigation"])) {
            return false;
        }
        $id = empty($posts["id"]) ? 0 : intval($posts["id"]);
        unset($posts["id"]);
        $modelPage = XOOPS::getModel("navigation_page");
        /*
        if (!$parentNode = $modelPage->findRow($id)) {
            return false;
        }
        */
        $columnsPage = $modelPage->info("cols");
        $data = array();
        foreach ($posts as $col => $val) {
            if (in_array($col, $columnsPage)) {
                $data[$col] = $val;
            }
        }
        $data["custom"] = 1;
        //$data["label"] = $posts["label"];
        //$data["navigation"] = "admin";
        if (!$pageId = $modelPage->add($data, $id)) {
            return false;
        }

        $result = array(
            "status"    => 1,
            "params"    => array(
                "id"    => $pageId,
                "label" => $posts["label"],
            ),
        );
        if ($this->getRequest()->isXmlHttpRequest()) {
            echo json_encode($result);
        }
        XOOPS::service('registry')->navigation->flush();
        return;
    }

    public function editAction()
    {
        $posts = $this->getRequest()->getPost();
        if (empty($posts["id"]) || empty($posts["label"])) {
            return false;
        }
        $id = $posts["id"];
        unset($posts["id"]);
        $modelPage = XOOPS::getModel("navigation_page");
        if (!$page = $modelPage->findRow($id)) {
            return false;
        }
        $columnsPage = $modelPage->info("cols");
        $data = array();
        foreach ($posts as $col => $val) {
            if (in_array($col, $columnsPage)) {
                $data[$col] = $val;
            }
        }
        //$data["label"] = $posts["title"];
        if (false === $modelPage->update($data, array($modelPage->quoteIdentifier("id") . " = ?" => $id))) {
            return false;
        }

        $result = array(
            "status"    => 1,
            "params"    => array(
                "id"    => $id,
                "label" => $posts["label"],
            ),
        );
        if ($this->getRequest()->isXmlHttpRequest()) {
            echo json_encode($result);
        }
        XOOPS::service('registry')->navigation->flush();
        return;
    }

    public function renameAction()
    {
        $posts = $this->getRequest()->getPost();
        if (empty($posts["id"]) || empty($posts["label"])) {
            return false;
        }
        $modelPage = XOOPS::getModel("navigation_page");
        $data["label"] = $posts["label"];
        if (!$modelPage->update($data, array($modelPage->quoteIdentifier("id") . " = ?" => $posts["id"]))) {
            return false;
        }

        $result = array(
            "status"    => 1,
            "params"    => array(
                "id"        => $posts["id"],
                "label"     => $posts["label"],
            ),
        );
        if ($this->getRequest()->isXmlHttpRequest()) {
            echo json_encode($result);
        }
        XOOPS::service('registry')->navigation->flush();
        return;
    }

    public function moveAction()
    {
        $posts = $this->getRequest()->getPost();
        if (empty($posts["id"]) || empty($posts["reference"])) {
            return false;
        }
        $position = "lastOf";
        switch ($posts["type"]) {
        case "before":
            $postion = "previousTo";
            break;
        case "after":
            $postion = "nextTo";
            break;
        default:
            $postion = "lastOf";
            break;
        }
        $modelPage = XOOPS::getModel("navigation_page");
        if (!$modelPage->move($posts["id"], $posts["reference"], $postion)) {
            return false;
        }

        $result = array(
            "status"    => 1,
            "params"    => array(
                "id"        => $posts["id"],
                "reference" => $posts["reference"],
                "position"  => $postion,
            ),
        );
        if ($this->getRequest()->isXmlHttpRequest()) {
            echo json_encode($result);
        }
        XOOPS::service('registry')->navigation->flush();
        return;
    }

    public function deleteAction()
    {
        $id = $this->getRequest()->getPost("id");
        if (empty($id)) {
            return false;
        }
        $modelPage = XOOPS::getModel("navigation_page");
        if (!$page = $modelPage->findRow($id)) {
            return false;
        }
        if (!$result = $modelPage->remove($page, true)) {
            return false;
        }

        $result = array(
            "status"    => 1,
        );
        if ($this->getRequest()->isXmlHttpRequest()) {
            echo json_encode($result);
        }
        XOOPS::service('registry')->navigation->flush();
        return;
    }
}
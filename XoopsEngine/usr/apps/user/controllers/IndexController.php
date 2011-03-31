<?php
/**
 * User module index controller
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

/**
 * User's profile page
 */
class User_IndexController extends Xoops_Zend_Controller_Action
{
    public function  preDispatch()
    {
        if (!$this->getRequest()->getParam("user")) {
            $this->_helper->redirector('index', 'profile');
        }
    }

    public function indexAction()
    {
        $module = $this->getRequest()->getModuleName();
        $this->setTemplate("user_page.html");
        $configs = XOOPS::service("registry")->config->read($module, "account");

        $id = $this->getRequest()->getParam("user");
        $userRow = XOOPS::getModel("user")->findRow($id);
        if (!$userRow) {
            $this->_helper->redirector('index', 'profile');
        }

        $profileRow = $userRow->profile();
        if ($profileRow) {
            $metaList = $profileRow->display();
            $avatar = $metaList["avatar"];
        } else {
            $metaList = array();
            $avatar = "";
        }

        $profileMeta = XOOPS::service("registry")->handler("meta", $module)->read();
        foreach ($profileMeta as $keyCategory => &$category) {
            foreach ($category["meta"] as $keyMeta => &$meta) {
                if (!isset($metaList[$keyMeta])) {
                    unset($category["meta"][$keyMeta]);
                } else {
                    $category["meta"][$keyMeta]["value"] = $metaList[$keyMeta];
                }
            }
            if (empty($category["meta"])) {
                unset($profileMeta[$keyCategory]);
            }
        }

        $account = array(
            "id"        => $userRow->id,
            "identity"  => $userRow->identity,
            "name"      => $userRow->name,
            "avatar"    => $avatar,
        );
        $this->template->assign("account", $account);
        $this->template->assign("profile", $profileMeta);
    }
}

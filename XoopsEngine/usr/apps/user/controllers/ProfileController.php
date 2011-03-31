<?php
/**
 * User profile controller
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
 * User's own page
 */
class User_ProfileController extends Xoops_Zend_Controller_Action
{
    public function  preDispatch()
    {
        if (!XOOPS::registry("user")->id) {
            $this->_helper->redirector('index', 'login');//, null, array('route' => 'default'));
        }
    }

    public function indexAction()
    {
        $module = $this->getRequest()->getModuleName();
        $this->setTemplate("profile_view.html");
        $configs = XOOPS::service("registry")->config->read($module, "account");

        $router = $this->getFrontController()->getRouter();
        $operationLink = function ($op) use ($router, $module)
        {
            $action = "index";
            if ($op == "password") {
                $op = "edit";
                $action = "password";
            }
            if ($op == "email") {
                $op = "edit";
                $action = "email";
            }
            return $router->assemble(
                array(
                    "module"        => $module,
                    "controller"    => $op,
                    "action"        => $action,
                ),
                "default"
            );
        };

        $operations = array(
            "edit"      => array(
                "link"  => $operationLink("edit"),
                "title" => XOOPS::_("Edit"),
            ),
            "email"     => array(
                "link"  => $operationLink("email"),
                "title" => XOOPS::_("Email"),
            ),
            "password"  => array(
                "link"  => $operationLink("password"),
                "title" => XOOPS::_("Password"),
            ),
            "avatar"    => array(
                "link"  => $operationLink("avatar"),
                "title" => XOOPS::_("Avatar"),
            ),
        );
        if (!empty($configs["delete_enable"])) {
            $operations["delete"] = array(
                "link"  => $operationLink("delete"),
                "title" => XOOPS::_("Delete account"),
            );
        }

        $profileRow = XOOPS::getModel("user_profile")->findRow(XOOPS::registry("user")->id);
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
            "id"        => XOOPS::registry("user")->id,
            "identity"  => XOOPS::registry("user")->identity,
            "name"      => XOOPS::registry("user")->name,
            "avatar"    => $avatar,
        );
        $this->template->assign("account", $account);
        $this->template->assign("profile", $profileMeta);
        $this->template->assign("operations", $operations);
    }
}

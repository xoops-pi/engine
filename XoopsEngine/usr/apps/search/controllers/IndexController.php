<?php
/**
 * Search index controller
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
 * @package         Search
 * @version         $Id$
 */

class Search_IndexController extends Xoops_Zend_Controller_Action
{
    private $users = array();

    public function indexAction()
    {
        //global $xoopsModuleConfig;
        $module = $this->getRequest()->getModuleName();

        //$this->setTemplate("index.html");
        //$this->template->assign("query", $query);
        /**
         * page
         */
        $page = $this->getRequest()->getParam("p", 1);
        /**
         * Query term, delimited by spaces
         */
        $query = trim($this->getRequest()->getParam("q", ""));
        /**
         * Query term type, potential values: OR, AND, EXACT
         */
        $type = strtolower($this->getRequest()->getParam("t", "or"));
        /**
         * Content generator
         */
        $user = $this->getRequest()->getParam("u", "");
        /**
         * Full list of a module, only applicable to one module
         */
        $full = $this->getRequest()->getParam("f", 0);
        /**
         * Modules in which to be searched, delimited by hyphen
         */
        $modules = (array) $this->getRequest()->getParam("m", array());
        //$modules = empty($dirname) ? array() : explode("-", $dirname);

        $params = compact("query", "type", "user", "full", "modules");
        $searchForm = $this->getForm($params);
        $searchForm->assign($this->template);

        // items per module
        $itemNum = Xoops::registry("module")->config("item_permodule");
        // items per page
        $itemPage = Xoops::registry("module")->config("item_perpage");
        // minimum length of term
        $keyword_min = Xoops::registry("module")->config("keyword_min");

        if ($type == "exact") {
            $queries = array($query);
        } else {
            $queries = preg_split('/[\s,]+/', $query);
        }
        if ($keyword_min > 0 && !empty($queries)) {
            $queries = array_filter($queries,
                function ($term) use ($keyword_min) {
                    return (strlen($term) >= $keyword_min) ? true : false;
                });
        }

        $uid = 0;
        if (!empty($user)) {
            $userModel = XOOPS::getModel("user");
            if (is_numeric($user)) {
                $userObject = $userModel->findRow($user);
            } else {
                $select = $userModel->select()->where("identity = ?", $user);
                $userObject = $userModel->fetchRow($select);
            }
            $uid = is_object($userObject) ? $userObject->id : null;
        }

        $searchUrl = $this->getFrontController()->getRouter()->assemble(
            array(), "search"
        );
        $params = array(
            "q"    => $query,
            "f"    => 1
        );
        if (!empty($type) && $type != "or") {
            $params["t"] = $type;
        }
        if (!empty($user)) {
            $params["u"] = $user;
        }

        if (empty($full)) {
            $template = "index.html";
            $limit = $itemNum;
            $offset = 0;

            $results = array();
            $modulesSearch = XOOPS::service("registry")->search->read();
            $moduleList = XOOPS::service("registry")->module->read();
            foreach ($modulesSearch as $dirname => $search) {
                if (!empty($modules) && !in_array($dirname, $modules)) continue;
                if (!empty($search["file"])) {
                    include_once Xoops::service('module')->getPath($dirname) . "/" . $search["file"];
                }
                // App search class
                if (!empty($search["callback"])) {
                    list($searchClass, $searchMethod) = $search["callback"];
                    $searchClass::setModule($dirname);
                    $result = $searchClass::$searchMethod($queries, $type, $limit, $offset, $uid);
                // Legacy search function
                } elseif (!empty($search["func"])) {
                    $result = $search["func"]($queries, $type, $limit, $offset, $uid);
                }
                $results[$dirname]["list"] = $this->translateResult($result);
                $results[$dirname]["name"] = $moduleList[$dirname]["name"];
                $params["m"] = $dirname;
                $results[$dirname]["url"] = $searchUrl . "?" . http_build_query($params);
            }
        } else {
            $template = "index_full.html";
            $limit = $itemPage;
            $offset = ($page <= 1) ? 0 : ($page - 1) * $limit;

            $results = array();
            $modulesSearch = XOOPS::service("registry")->search->read();
            $moduleList = XOOPS::service("registry")->module->read();
            foreach ($modulesSearch as $dirname => $search) {
                if (!empty($modules) && !in_array($dirname, $modules)) continue;
                if (!empty($search["file"])) {
                    include_once Xoops::service('module')->getPath($dirname) . "/" . $search["file"];
                }
                if (!empty($search["callback"])) {
                    /*
                    list($searchClass, $searchMethod) = explode("::", $search["callback"]);
                    $searchClass = $dirname . "_" . $searchClass;
                    $result = $searchClass::$searchMethod($queries, $type, $limit, $offset, $uid);
                    */
                    list($searchClass, $searchMethod) = $search["callback"];
                    $searchClass::setModule($dirname);
                    $result = $searchClass::$searchMethod($queries, $type, $limit, $offset, $uid);
                } elseif (!empty($search["func"])) {
                    $result = $search["func"]($queries, $type, $limit, $offset, $uid);
                }
                $results["list"] = $this->translateResult($result);
                $results["name"] = $moduleList[$dirname]["name"];
                $params["m"] = $dirname;
                //$results["url"] = $searchUrl . "?" . http_build_query($params);
                if (count($result) >= $limit) {
                    $params["p"] = $page + 1;
                    $results["next"] = $searchUrl . "?" . http_build_query($params);
                }
                if ($page > 1) {
                    $params["p"] = $page - 1;
                    $results["previous"] = $searchUrl . "?" . http_build_query($params);
                }
                break;
            }
        }


        $this->setTemplate($template);
        $this->template->assign("results", $results);
        $this->template->assign("users", $this->loadUsers());
    }

    public function resultAction()
    {
        $this->_forward('index');
    }

    protected function translateResult($list)
    {
        $format = "Y-m-d H:i:s";
        foreach ($list as $key => &$data) {
            if (isset($data["time"])) {
                $data["time"] = date($format, $data["time"]);
            }
            if (!empty($data["uid"])) {
                $this->users[$data["uid"]] = 1;
            }
        }

        return $list;
    }

    protected function loadUsers()
    {
        if (empty($this->users)) {
            return array();
        }
        $userModel = XOOPS::getModel("user");
        $select = $userModel->select()->from($userModel, array("id", "name", "identity"))->where("id IN (?)", array_keys($this->users));
        $users = $userModel->getAdapter()->fetchAssoc($select);
        foreach ($users as $uid => &$user) {
            if (empty($user["name"])) {
                $user["name"] = $user["identity"];
            }
            $user["url"] = $this->getFrontController()->getRouter()->assemble(
                array("user" => $user["identity"]), "profile"
            );
        }

        return $users;
    }

    protected function getForm($params)
    {
        //include_once XOOPS::path('www') . '/class/xoopsformloader.php';
        Xoops_Legacy::autoload();

        // Form properties
        $formTitle = XOOPS::_("Search form");
        $formName = "xoopsSearch";
        $formAction = $this->getFrontController()->getRouter()->assemble(
            array(
                "module"        => $module,
                "controller"    => "search",
                "action"        => "index",
            ),
            "default"
        );
        $formMethod = "GET";
        // Create form
        $form = new XoopsThemeForm($formTitle, $formName, $formAction, $formMethod);

        // Query element
        $query = empty($params["query"]) ? "" : htmlspecialchars(stripslashes($params["query"]), ENT_QUOTES);
        $termElement = new XoopsFormText(XOOPS::_("Search terms"), "q", 30, 255, $query);
        if (Xoops::registry("module")->config('keyword_min') > 0) {
            $termElement->setDescription(sprintf(XOOPS::_("Terms shorter than %d will be ignored"), Xoops::registry("module")->config('keyword_min')));
        }
        $form->addElement($termElement);

        // Term type
        $type_select = new XoopsFormSelect(XOOPS::_("Type"), "t", $params["type"]);
        $type_select->addOptionArray(array("and" => XOOPS::_("All terms"), "or" => XOOPS::_("Any term"), "exact" => XOOPS::_("Same as typed")));
        $form->addElement($type_select);

        // User
        $user = empty($params["user"]) ? "" : htmlspecialchars(stripslashes($params["user"]), ENT_QUOTES);
        $form->addElement(new XoopsFormText(XOOPS::_("By user"), "u", 30, 64, $user));

        // Modules
        $modules = XOOPS::service("registry")->search->read();
        $mods_checkbox = new XoopsFormCheckBox(XOOPS::_("Search in modules"), "m[]", $params["modules"]);
        $mods_checkbox->columns = 3;
        foreach ($modules as $dirname => $data) {
            $mods_checkbox->addOption($dirname, $data["name"]);
        }
        $form->addElement($mods_checkbox);

        //$form->addElement(new XoopsFormHidden("f", $params["full"]));
        $form->addElement(new XoopsFormButton("", "submit", XOOPS::_("Search"), "submit"));

        return $form;
    }
}

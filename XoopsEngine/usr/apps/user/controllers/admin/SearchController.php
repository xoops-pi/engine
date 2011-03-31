<?php
/**
 * User search controller
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

class User_SearchController extends Xoops_Zend_Controller_Action
{
    protected function getSession()
    {
        return Xoops::service("session")->ns("xo" . md5(__FILE__));
    }

    public function indexAction()
    {
        $form = $this->getForm();
        $paramsSession = $this->getSession();
        $params = (array) $paramsSession->params;
        $form->setDefaults($params);

        $this->renderForm($form);
    }

    public function resultAction()
    {
        $this->setTemplate("search_result.html");
        $module = $this->getRequest()->getModuleName();
        $page = $this->getRequest()->getParam("page", 1);
        $configs = XOOPS::service("registry")->config->read("admin", $module);
        $itemCountPerPage = !empty($configs["items_per_page"]) ? $configs["items_per_page"] : 10;

        $userModel = XOOPS::getModel("user");
        $profileModel = XOOPS::getModel("user_profile");

        $paramsSession = $this->getSession();
        $data =& $paramsSession->params;
        if (!$data) {
            $params = $this->getRequest()->getParams();
            $form = $this->getForm();
            $values = $form->getValidValues($params);

            $data = array();
            if (!empty($values["order"]["custom"]) || !empty($values["order"]["id"])) {
                $data["order"] = $values["order"];
            }
            unset($values["order"]);
            foreach ($values as $key => $val) {
                if (!empty($val["operator"])) {
                    $data[$key] = $values[$key];
                }
            }
        }

        $colsUser = $userModel->info("cols");
        $colsProfile = $profileModel->info("cols");

        $params = array();
        if (!empty($data["order"]["custom"])) {
            foreach (explode(",", $data["order"]["custom"]) as $item) {
                list($key, $order) = explode(" ", $item, 2);
                if (in_array($key, $colsUser)) {
                    $params["order"][] = "u.{$key} " . $order;
                } elseif (in_array($key, $colsProfile)) {
                    $params["order"][] = "p.{$key} " . $order;
                    $params["profile"] = 1;
                }
            }
        }
        if (!empty($data["order"]["id"])) {
            $params["order"][] = "u.id " . $data["order"]["id"];
        }
        foreach ($data as $key => $val) {
            if (!empty($val["operator"])) {
                if (in_array($key, $colsUser)) {
                    $val["prefix"] = "u";
                } elseif (in_array($key, $colsProfile)) {
                    $val["prefix"] = "p";
                    $params["profile"] = 1;
                } else {
                    continue;
                }
                $params["params"][$key] = $val;
            }
        }

        $select = $userModel->getAdapter()->select()//->order("u.id ASC")
                    ->from(array("u" => $userModel->info("name")),
                        array("id", "identity", "name", "email", "active"));

        if (!empty($params["profile"])) {
            $select->join(array("p" => $profileModel->info("name")),
                            "p.user = u.id",
                            array());
        }
        $select->where("1 = 1");
        foreach ($params["params"] as $key => $param) {
            $func = ($param["operator"] == "OR") ? "orWhere" : "where";
            $select->$func($param["prefix"] . ".{$key} = ?", $param["value"]);
        }
        if (!empty($params["order"])) {
            $select->order($params["order"]);
        }

        $paginator = Xoops_Zend_Paginator::factory($select);
        $paginator->setItemCountPerPage($itemCountPerPage);
        $paginator->setCurrentPageNumber($page);
        $paginator->setParams(array());
        $itemList = $paginator->getCurrentItems();

        $view = $this->view;
        $editLink = function ($id, $action) use ($view, $module)
        {
            $action = ($action == "delete") ? "delete" : "edit";
            $link = $view->url(
                array(
                    "action"        => $action,
                    "controller"    => "index",
                    "module"        => $module,
                    "id"            => $id,
                ),
                "admin"
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

        $users = array();
        foreach ($itemList as $item) {
            $id = $item["id"];
            $users[$id] = $item;
            $users[$id]["edit"] = $editLink($id, "edit");
            $users[$id]["view"] = $viewLink($id);
            $users[$id]["delete"] = $editLink($id, "delete");
        }

        if (!empty($users)) {
            $roleModel = XOOPS::getModel("acl_role");
            $select = $roleModel->select();
            $roleList = $roleModel->getAdapter()->fetchPairs($select);

            $userRoleModel = XOOPS::getModel("acl_user");
            $select = $userRoleModel->select()->where("user IN (?)", array_keys($users));
            $rowset = $userRoleModel->fetchAll($select);
            foreach ($rowset as $row) {
                $users[$row->user]["role"] = $roleList[$row->role];
            }
        }

        $title = XOOPS::_("Search users");
        $this->template->assign("title", $title);
        $this->template->assign("users", $users);
        $this->template->assign("paginator", $paginator);
    }

    protected function renderForm($form)
    {
        $this->setTemplate("search_form.html");
        $form->assign($this->view);
        $title = XOOPS::_("Search user");
        $this->template->assign("title", $title);
        $this->getSession()->unsetAll();
    }

    public function getForm()
    {
        $module = $this->getRequest()->getModuleName();

        $this->view->headLink(array(
            "href"  => "form.css",
            "rel"   => "stylesheet",
            "type"  => "text/css"
        ));
        $action = $this->view->url(array(
                "action"        => "result",
                "controller"    => "search",
                "module"        => $module
            ),
            "admin"
        );
        $options = array(
            "name"      => "xoopsSearch",
            "action"    => $action,
            "method"    => "get",
        );
        $form = new Xoops_Zend_Form($options);

        $operatorOptions = array(
            //"label"         => "Operator",
            "multiOptions"  => array(
                ""      => XOOPS::_("Skip"),
                "AND"   => "AND",
                "OR"    => "OR",
            ),
            "separator"     => " ",
            "value"         => "",
        );

        $options = array(
            "label"    => "Username",
        );
        $compound = $form->createElement("Compound", "identity", $options);
        $form->addElement($compound);
        $compound->addElement("Radio", "operator", $operatorOptions);
        $options = array(
            //"label"         => "Username",
            "filters"       => array(
                "trim"      => array(
                    "filter"    => "StringTrim",
                ),
            ),
        );
        $compound->addElement("Text", "value", $options);

        $options = array(
            "label"    => "Full name",
        );
        $compound = $form->createElement("Compound", "name", $options);
        $form->addElement($compound);
        //$subform = new Xoops_Zend_Form_Compound($options);
        $compound->addElement("Radio", "operator", $operatorOptions);
        $options = array(
            //"label"         => "Full name",
            "filters"       => array(
                "trim"      => array(
                    "filter"    => "StringTrim",
                ),
            ),
        );
        $compound->addElement("Text", "value", $options);
        //$form->addSubform($subform, "name");

        $options = array(
            "label"    => "Email",
        );
        $compound = $form->createElement("Compound", "email", $options);
        $form->addElement($compound);
        //$subform = new Xoops_Zend_Form_Compound($options);
        $compound->addElement("Radio", "operator", $operatorOptions);
        $options = array(
            //"label"         => "Email",
            "filters"       => array(
                "trim"      => array(
                    "filter"    => "StringTrim",
                ),
                "lowercase" => array(
                    "filter"    => "StringToLower",
                ),
            ),
        );
        $compound->addElement("Text", "value", $options);
        //$form->addSubform($subform, "email");

        $options = array(
            "label"    => "Active",
        );
        $compound = $form->createElement("Compound", "active", $options);
        $form->addElement($compound);
        //$subform = new Xoops_Zend_Form_Compound($options);
        $compound->addElement("Radio", "operator", $operatorOptions);
        $options = array(
            "label"         => "Active",
        );
        $compound->addElement("Yesno", "value", $options);
        //$form->addSubform($subform, "active");

        //$form->addDisplayGroup(array("identity", "name", "email", "active"), "account", array("legend" => XOOPS::_("Account")));

        $profileMeta = XOOPS::service("registry")->handler("meta", $module)->read("search");
        foreach ($profileMeta as $keyCategory => $category) {
            foreach ($category["meta"] as $keyMeta => $meta) {
                $options = array(
                    "label"    => $meta["options"]["label"],
                );
                unset($meta["options"]["label"]);
                $compound = $form->createElement("Compound", $keyMeta, $options);
                $form->addElement($compound);
                //$subform = new Xoops_Zend_Form_Compound($options);
                $compound->addElement("Radio", "operator", $operatorOptions);

                $key = "value";
                $type = empty($meta["type"]) ? "text" : $meta["type"];
                $options = isset($meta["options"]) ? $meta["options"] : null;
                if (!empty($meta["module"])) {
                    $class = $meta["module"] . "_form_element_" . $type;
                    if (class_exists($class)) {
                        $element = new $class($key, $options);
                    } else {
                        $element = $compound->createElement("text", $key);
                    }
                } else {
                    $element = $compound->createElement($type, $key, $options);
                }
                $compound->addElement($element);
                //$form->addSubform($subform, $keyMeta);
            }
            if (!empty($category["meta"])) {
                $form->addDisplayGroup(array_keys($category["meta"]), $keyCategory, array("legend" => XOOPS::_($category["title"])));
            }
        }

        $options = array(
            "legend"    => "Sort",
        );
        //$compound = $form->createElement("Compound", "order", $options);
        //$form->addElement($compound);
        $subform = new Xoops_Zend_Form_SubForm($options);
        $options = array(
            "label"         => "Order by id",
            "multiOptions"  => array(
                ""      => XOOPS::_("Skip"),
                "ASC"   => "ASC",
                "DESC"  => "DESC",
            ),
            "separator"     => " ",
            "value"         => "",
        );
        $subform->addElement("Radio", "id", $options);
        $options = array(
            "label"         => "Custom sort",
                "rols"  => 50,
                "rows"  => 2,
            "filters"       => array(
                "trim"      => array(
                    "filter"    => "StringTrim",
                ),
            ),
        );
        $subform->addElement("Textarea", "custom", $options);
        $form->addSubform($subform, "order");

        $options = array(
            "label"     => "Query",
            "required"  => false,
            "ignore"    => true,
        );
        $form->addElement("submit", "save", $options);

        $form->setDescription("Search users");

        $form->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'dl', 'class' => 'xoops-form')),
            array('Description', array('placement' => 'prepend')),
            'Form'
        ));

        return $form;
    }
}

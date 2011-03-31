<?php
/**
 * User profile meta category admin controller
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

class User_CategoryController extends Xoops_Zend_Controller_Action
{
    public function indexAction()
    {
        $form = $this->getForm();
        $this->renderForm($form);
    }

    public function saveAction()
    {
        $module = $this->getRequest()->getModuleName();
        if (!$this->getRequest()->isPost()) {
            return $this->_helper->redirector('index');
        }

        $params = $this->getRequest()->getPost();
        $form = $this->getForm();
        if (!$form->isValid($params)) {
            return $this->renderForm($form);
        }

        $categoryList = $form->getValues();
        $modelCategory = Xoops::service('module')->getModel("category", "user");
        $rowset = $modelCategory->fetchAll(array());
        $message = array();
        $existList = array();
        foreach ($rowset as $row) {
            $key = $row->key;
            $existList[$key] = 1;
            if (!empty($categoryList[$key]["delete"])) {
                if (!$status = $row->delete()) {
                    $message[] = XOOPS::_("Category '{$row->key}' was not deleted.");
                } else {
                    $form->removeSubForm($key);
                    unset($existList[$key]);
                }
            } elseif (!$row->setFromArray($categoryList[$key])->save()) {
                $message[] = XOOPS::_("Category '{$key}' was not saved.");
            }
        }
        XOOPS::service("registry")->handler("meta", $module)->flush();
        $addList = array();
        if (empty($message) && !empty($categoryList["add"])) {
            $list = explode("\n", $categoryList["add"]);
            foreach ($list as $item) {
                list($key, $order, $title) = explode(" ", $item, 3);
                if (isset($existList[$key])) {
                    $message[] = XOOPS::_("Category key '{$key}' already taken.");
                    continue;
                }
                $addList[$key] = array(
                    "key"   => $key,
                    "title" => $title,
                    "order" => $order,
                );
            }
        }

        if (!empty($message)) {
            $form->addErrors($message);
            $form->addDecorators(array(
                'Errors',
            ));
            return $this->renderForm($form);
        }

        $message = XOOPS::_("Profile meta data saved.");
        $failedList = array();
        foreach ($addList as $key => $data) {
            $status = $modelCategory->insert($data);
            if (!$status) {
                $failedList[] = $key;
            }
        }
        if (!empty($failedList)) {
            $message = XOOPS::_("Profile meta data saved. But following meta were not added: ") . implode(" ", $failedList);
        }

        $urlOptions = array("action" => "index", "controller" => "category", "route" => "admin");
        $options = array("time" => 3, "message" => $message);
        $this->redirect($urlOptions, $options);
    }

    protected function renderForm($form)
    {
        $module = $this->getRequest()->getModuleName();
        $this->setTemplate("profile_category.html");
        $form->assign($this->view);
        $title = XOOPS::_("Profile meta category edit");
        $this->template->assign("title", $title);

        // Category list
        $modelCategory = Xoops::service('module')->getModel("category", "user");
        $select = $modelCategory->select()->order(array("order ASC", "id ASC"));
        $rowset = $modelCategory->fetchAll($select);
        $links_category = array();
        foreach ($rowset as $row) {
            $links_category[] = array(
                "link" => $this->view->url(array(
                        "action"        => "sort",
                        "controller"    => "category",
                        "module"        => $module,
                        "category"      => $row->key,
                    ),
                    "admin"
                ),
                "title" => $row->title,
            );
        }
        $links_category[] = array(
            "link" => $this->view->url(array(
                    "action"        => "sort",
                    "controller"    => "category",
                    "module"        => $module,
                    "category"      => "-",
                ),
                "admin"
            ),
            "title" => XOOPS::_("Hidden category"),
        );
        $this->template->assign("links_category", $links_category);
    }

    public function getForm()
    {
        $module = $this->getRequest()->getModuleName();

        // Category list
        $modelCategory = Xoops::service('module')->getModel("category", "user");
        $select = $modelCategory->select()->order(array("order ASC", "id ASC"));
        $rowset = $modelCategory->fetchAll($select);

        $this->view->headLink(array(
            "href"  => "form.css",
            "rel"   => "stylesheet",
            "type"  => "text/css"
        ));
        $action = $this->view->url(array(
                "action"        => "save",
                "controller"    => "category",
                "module"        => $module
            ),
            "admin"
        );
        $options = array(
            "name"      => "xoopsProfile",
            "action"    => $action,
            "method"    => "post",
        );
        $form = new Xoops_Zend_Form($options);

        $i = 0;
        foreach ($rowset as $row) {
            $options = array(
                "legend"    => $row->title . " (" . $row->key . ")",
            );
            $subform = new Xoops_Zend_Form_SubForm($options);
            $options = array(
                "label"         => "Title",
                "value"         => $row->title,
                "required"      => true,
                "filters"       => array(
                    "trim"      => array(
                        "filter"    => "StringTrim",
                    ),
                ),
            );
            $subform->addElement("text", "title", $options);

            $options = array(
                "label"         => "Order",
                "value"         => ($i++) * 10,
                "required"      => true,
                "filters"       => array(
                    "trim"      => array(
                        "filter"    => "StringTrim",
                    ),
                ),
            );
            $subform->addElement("text", "order", $options);

            $options = array(
                "label"         => "Delete",
                "multioptions"  => array(
                    "1"     => "",
                ),
            );
            $subform->addElement("MultiCheckbox", "delete", $options);

            $form->addSubform($subform, $row->key);
        }

        $options = array(
            "label"         => "Add categories",
            "description"   => "One category per line (delimited by space): key order title",
            "cols"      => 50,
            "rows"      => 5,
        );
        $form->addElement("Textarea", "add", $options);

        $options = array(
            "label"     => "Save",
            "required"  => false,
            "ignore"    => true,
        );
        $form->addElement("submit", "save", $options);

        $form->setDescription("Edit profile meta categories");
        $form->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'dl', 'class' => 'xoops-form')),
            array('Description', array('placement' => 'prepend')),
            'Form'
        ));

        return $form;
    }
}
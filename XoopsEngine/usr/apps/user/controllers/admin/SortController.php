<?php
/**
 * User profile meta sort admin controller
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

class User_SortController extends Xoops_Zend_Controller_Action
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

        $linkList = $form->getValues();
        $modelLink = Xoops::service('module')->getModel("meta_category", "user");
        $rowset = $modelLink->fetchAll(array());
        $message = array();
        foreach ($rowset as $row) {
            if (!isset($linkList[$row->meta])) {
                if (!$status = $row->delete()) {
                }
                continue;
            }
            $row->category = $linkList[$row->meta]["category"];
            $row->order = $linkList[$row->meta]["order"];
            $row->save();
            unset($linkList[$row->meta]);
        }
        foreach ($linkList as $key => $link) {
            $row = $modelLink->createRow($link);
            $row->meta = $key;
            $row->save();
        }
        XOOPS::service("registry")->handler("meta", $module)->flush();

        if (!empty($message)) {
            $form->addErrors($message);
            $form->addDecorators(array(
                'Errors',
            ));
            return $this->renderForm($form);
        }

        $message = XOOPS::_("Profile meta category saved.");
        $urlOptions = array("action" => "index", "controller" => "sort", "route" => "admin");
        $options = array("time" => 3, "message" => $message);
        $this->redirect($urlOptions, $options);
    }

    protected function renderForm($form)
    {
        $module = $this->getRequest()->getModuleName();
        $this->setTemplate("profile_sort.html");
        $form->assign($this->view);
        $title = XOOPS::_("Sort profile meta");
        $this->template->assign("title", $title);
    }

    public function getForm()
    {
        $module = $this->getRequest()->getModuleName();

        // Category list
        $modelCategory = Xoops::service('module')->getModel("category", "user");
        $select = $modelCategory->select()->from($modelCategory, array("key", "title"))->order(array("order ASC", "id ASC"));
        $categoryList = $modelCategory->getAdapter()->fetchAssoc($select);
        $categoryList["-"]["title"] = XOOPS::_("Hidden");

        // Meta list
        $modelMeta = XOOPS::getModel("user_meta");
        $select = $modelMeta->select()->from($modelMeta, array("key", "title"))->order(array("id ASC"));
        $metaList = $modelMeta->getAdapter()->fetchPairs($select);

        // meta-category list
        $modelLink = Xoops::service('module')->getModel("meta_category", "user");
        $select = $modelLink->select()->order(array("order ASC"));
        $rowset = $modelLink->fetchAll($select);

        $metaByCategory = $categoryList;
        foreach ($rowset as $row) {
            $categoryKey = $row->category;
            if (!isset($metaByCategory[$categoryKey]) || !isset($metaList[$row->meta])) {
                continue;
            }
            $metaByCategory[$categoryKey]["meta"][$row->meta] = array(
                "title" => $metaList[$row->meta],
                "order" => $row->order,
            );
            unset($metaList[$row->meta]);
        }
        foreach ($metaByCategory as $category => $list) {
            if (empty($list["meta"])) {
                unset($metaByCategory[$category]);
            }
        }
        if (!empty($metaList)) {
            $metaByCategory[""] = array(
                "title" => XOOPS::_("Not categorized"),
            );
            foreach ($metaList as $key => $meta) {
                $metaByCategory[""]["meta"][$key] = $metaList[$key];
            }
        }
        $categoryOptions = array();
        foreach ($categoryList as $key => $category) {
            $categoryOptions[$key] = $category["title"];
        }

        $this->view->headLink(array(
            "href"  => "form.css",
            "rel"   => "stylesheet",
            "type"  => "text/css"
        ));
        $action = $this->view->url(array(
                "action"        => "save",
                "controller"    => "sort",
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

        //$i = 0;
        foreach ($metaByCategory as $key => $category) {
            /*
            $options = array(
                "legend"    => $category["title"],
            );
            $subform = new Xoops_Zend_Form_SubForm($options);
            */
            $i = 0;
            foreach ($category["meta"] as $mk => $meta) {

                $options = array(
                    "legend"    => $category["title"] . " - " . $meta["title"] . " (" . $mk . ")",
                );
                $metaform = new Xoops_Zend_Form_SubForm($options);

                $options = array(
                    "label"         => "Category",
                    "value"         => $key,
                    "multiOptions"  => $categoryOptions,
                );
                $metaform->addElement("select", "category", $options);

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
                $metaform->addElement("text", "order", $options);

                $form->addSubform($metaform, $mk);
            }

            //$form->addSubform($subform);
        }

        $options = array(
            "label"     => "Save",
            "required"  => false,
            "ignore"    => true,
        );
        $form->addElement("submit", "save", $options);


        return $form;
    }
}
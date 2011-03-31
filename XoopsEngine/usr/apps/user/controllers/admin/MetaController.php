<?php
/**
 * User profile meta admin controller
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

class User_MetaController extends Xoops_Zend_Controller_Action
{
    public function indexAction()
    {
        $form = $this->getForm();
        $this->renderForm($form);
    }

    public function addAction()
    {
        $form = $this->getFormMeta();
        $this->renderFormMeta($form);
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

        $metaList = $form->getValues();
        $modelMeta = XOOPS::getModel("user_meta");
        $message = array();
        foreach ($metaList as $key => $data) {
            $row = $modelMeta->fetchRow(array($modelMeta->getAdapter()->quoteIdentifier("key") . " = ?" => $key));
            $metaActive = $row->active;
            if (!$row) {
                $message[] = XOOPS::_("Meta '{$key}' was not found.");
                continue;
            }
            if (!$row->module && !empty($metaList[$key]["delete"])) {
                /*
                if (!$status = $row->delete()) {
                    $message[] = XOOPS::_("Meta '{$key}' was not deleted.");
                } else {
                    Xoops_Installer_Module_User::dropField($key);
                }
                */
                Xoops_Installer_Module_User::dropField($key);
            } elseif (!$row->setFromArray($metaList[$key])->save()) {
                $message[] = XOOPS::_("Meta '{$key}' was not saved.");
            } elseif ($metaActive != $metaList[$key]["active"]) {
                if (!$metaList[$key]["active"]) {
                    Xoops_Installer_Module_User::deactivateField($key);
                } else {
                    Xoops_Installer_Module_User::activateField($key);
                }
            }
        }
        XOOPS::service("registry")->user->flush();
        XOOPS::service("registry")->handler("meta", $module)->flush();
        if (!empty($message)) {
            $form->addErrors($message);
            $form->addDecorators(array(
                'Errors',
            ));
            return $this->renderForm($form);
        }

        //$urlOptions = array("action" => "index", "controller" => "meta", "route" => "admin");
        $urlOptions = array("action" => "index", "reset" => false);
        $message = XOOPS::_("Profile meta data saved.");
        $options = array("time" => 3, "message" => $message);
        $this->redirect($urlOptions, $options);
    }

    public function savemetaAction()
    {
        $module = $this->getRequest()->getModuleName();
        if (!$this->getRequest()->isPost()) {
            return $this->_helper->redirector('index');
        }

        $params = $this->getRequest()->getPost();
        $form = $this->getFormMeta();
        if (!$form->isValid($params)) {
            return $this->renderFormMeta($form);
        }

        $parseConfig = function ($content)
        {
            $result = $content;
            if (!preg_match("/^[a-z0-9_]$/i", $content)) {
                $error = error_reporting(0);
                $result = parse_ini_string($content);
                error_reporting($error);
            }
            return $result;
        };

        $error = false;
        $message = array();
        foreach (array("edit", "admin", "options") as $key) {
            if (!empty($params[$key])) {
                if (false === ($params[$key] = $parseConfig($params[$key]))) {
                    $form->getElement($key)->addError(XOOPS::_("Invalid INI format"));
                    $error = true;
                }
            }
        }
        if (!$error) {
            $status = Xoops_Installer_Module_User::addField($params);
            if (!$status) {
                $message[] = XOOPS::_("Meta data was not saved");
            }
        }

        if ($message || $error) {
            $form->addErrors($message);
            $form->addDecorators(array(
                'Errors',
            ));
            return $this->renderFormMeta($form);
        }
        XOOPS::service("registry")->user->flush();
        XOOPS::service("registry")->handler("meta", $module)->flush();

        $urlOptions = array("action" => "index", "controller" => "meta", "route" => "admin");
        $message = XOOPS::_("Profile meta data saved.");
        $options = array("time" => 3, "message" => $message);
        $this->redirect($urlOptions, $options);
    }

    protected function renderForm($form)
    {
        $module = $this->getRequest()->getModuleName();
        $this->setTemplate("profile_meta.html");
        $form->assign($this->view);
        $title = XOOPS::_("Profile meta edit");
        $this->template->assign("title", $title);
        $url_addmeta = $this->view->url(array(
                "action"        => "add",
                "controller"    => "meta",
                "module"        => $module,
            ),
            "admin"
        );
        $this->template->assign("url_addmeta", $url_addmeta);
    }

    protected function renderFormMeta($form)
    {
        $module = $this->getRequest()->getModuleName();
        $this->setTemplate("profile_meta_add.html");
        $form->assign($this->view);
        $title = XOOPS::_("Add profile meta");
        $this->template->assign("title", $title);
        $url_addmeta = $this->view->url(array(
                "action"        => "add",
                "controller"    => "meta",
                "module"        => $module,
            ),
            "admin"
        );
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
                "action"        => "save",
                "controller"    => "meta",
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

        $modelMeta = XOOPS::getModel("user_meta");
        $select = $modelMeta->select()->order("id ASC");
        $rowset = $modelMeta->fetchAll($select);
        foreach ($rowset as $row) {
            $options = array(
                "legend"    => $row->title . " (" . $row->key . ")",
            );
            $subform = new Xoops_Zend_Form_SubForm($options);
            $options = array(
                "label"         => "Title",
                "value"         => $row->title,
                //"belongsTo"     => $row->key,
                "required"      => true,
                "filters"       => array(
                    "trim"      => array(
                        "filter"    => "StringTrim",
                    ),
                ),
            );
            $subform->addElement("text", "title", $options);

            $options = array(
                "label"         => "Required",
                "value"         => $row->required,
                //"belongsTo"     => $row->key,
            );
            $subform->addElement("Yesno", "required", $options);

            $options = array(
                "label"         => "Active",
                "value"         => $row->active,
                //"belongsTo"     => $row->key,
            );
            $subform->addElement("Yesno", "active", $options);

            if (!$row->module) {
                $options = array(
                    "label"         => "Delete",
                    //"belongsTo"     => $row->key,
                    "multioptions"  => array(
                        "1"     => "",
                    ),
                );
                $subform->addElement("MultiCheckbox", "delete", $options);
            }
            $form->addSubform($subform, $row->key);
        }
        $options = array(
            "label"     => "Save",
            "required"  => false,
            "ignore"    => true,
        );
        $form->addElement("submit", "save", $options);

        $form->setDescription("Edit profile meta");
        $form->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'dl', 'class' => 'xoops-form')),
            array('Description', array('placement' => 'prepend')),
            'Form'
        ));

        return $form;
    }

    public function getFormMeta()
    {
        $module = $this->getRequest()->getModuleName();
        $this->view->headLink(array(
            "href"  => "form.css",
            "rel"   => "stylesheet",
            "type"  => "text/css"
        ));
        $action = $this->view->url(array(
                "action"        => "savemeta",
                "controller"    => "meta",
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

        $options = array(
            "label"         => "Meta key",
            "required"      => true,
            "prefixPath"    => array(
                "validate"  => array(
                    //"App_User_Validate"      => Xoops::service('module')->getPath($module) . "/Validate",
                    "User_Validate"      => Xoops::service('module')->getPath($module) . "/Validate",
                ),
            ),
            "validators"    => array(
                "strlen"    => array(
                    "validator" => "StringLength",
                    "options"   => array(
                        "max"   => "64",
                    ),
                ),
                "duplicate"  => array(
                    "validator" => "MetaDuplicate",
                ),
            ),
            "filters"       => array(
                "trim"      => array(
                    "filter"    => "StringTrim",
                ),
            ),
        );
        $form->addElement("Text", "key", $options);

        $options = array(
            "label"         => "Meta title",
            "required"      => true,
            "filters"       => array(
                "trim"      => array(
                    "filter"    => "StringTrim",
                ),
            ),
        );
        $form->addElement("Text", "title", $options);

        $options = array(
            "label"         => "Attribute",
            "description"   => "Will be used to create corresponding field in profile table",
            "size"          => 50,
            "filters"       => array(
                "trim"      => array(
                    "filter"    => "StringTrim",
                ),
            ),
        );
        $form->addElement("Text", "attribute", $options);

        $options = array(
            "label"         => "View callback method",
            "description"   => "The method should already be available in module class/profile.php",
            "validators"    => array(
                "nameformat"    => array(
                    "validator" => "Alnum",
                ),
            ),
            "filters"       => array(
                "trim"      => array(
                    "filter"    => "StringTrim",
                ),
            ),
        );
        $form->addElement("Text", "view", $options);

        $options = array(
            "label"         => "Edit form element",
            "description"   => "Form element definition (INI format) or element type",
            "cols"      => 50,
            "rows"      => 5,
            "filters"       => array(
                "trim"      => array(
                    "filter"    => "StringTrim",
                ),
            ),
        );
        $form->addElement("Textarea", "edit", $options);

        $options = array(
            "label"         => "Edit form element by administrator",
            "description"   => "Form element definition (INI format) or element type",
            "cols"      => 50,
            "rows"      => 5,
            "filters"       => array(
                "trim"      => array(
                    "filter"    => "StringTrim",
                ),
            ),
        );
        $form->addElement("Textarea", "admin", $options);

        $options = array(
            "label"         => "Required item",
        );
        $form->addElement("Yesno", "required", $options);

        $options = array(
            "label"         => "Active",
        );
        $form->addElement("Yesno", "active", $options);

        $options = array(
            "label"     => "Save",
            "required"  => false,
            "ignore"    => true,
        );
        $form->addElement("submit", "save", $options);

        $form->setDescription("Add profile meta");
        $form->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'dl', 'class' => 'xoops-form')),
            array('Description', array('placement' => 'prepend')),
            'Form'
        ));

        return $form;
    }
}
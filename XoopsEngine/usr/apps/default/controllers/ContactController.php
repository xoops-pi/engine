<?php
/**
 * Default contact controller
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
 * @package         Default
 * @version         $Id$
 */

class Default_ContactController extends \Application\Controller
{
    public function indexAction()
    {
        $this->setTemplate('contact.html');
        $this->template->assign("message", XOOPS::_("XAE, the Xoops Application Engine"));
        $this->getForm()->assign($this->view);
    }

    public function submitAction()
    {
    }

    // login form
    public function getForm()
    {
        $module = $this->getRequest()->getModuleName();
        $this->view->headLink(array(
            "href"  => "form.css",
            "rel"   => "stylesheet",
            "type"  => "text/css"
        ));
        $action = $this->getFrontController()->getRouter()->assemble(array(
                "action"        => "submit",
                "controller"    => "contact",
                "module"        => $module
            ),
            "default"
        );
        $options = array(
            "name"      => 'contactForm',
            "action"    => $action,
            "method"    => "post",
        );
        $form = new Xoops_Zend_Form($options);

        // Visitor's name
        $options = array(
            "label"         => "Name",
            "required"      => true,
            "filters"       => array(
                "trim"      => array(
                    "filter"    => "StringTrim",
                ),
            ),
        );
        $form->addElement('text', 'name', $options);

        // Visitor's email
        $options = array(
            "label"         => "Email",
            "required"      => true,
            "validators"    => array(
                "email"     => array(
                    "validator" => "EmailAddress",
                    "options"   => array(
                        "domain"    => false,
                    ),
                ),
            ),
            "filters"       => array(
                "trim"      => array(
                    "filter"    => "StringTrim",
                ),
            ),
        );
        $form->addElement('text', 'email', $options);

        // Visitor's message
        // Use textarea element
        $options = array(
            "label"         => "Message",
            "value"         => "Type your message",
            "required"      => true,
            "html"          => true,
            "editor"        => array(
                //"type"      => "default", // ckeditor, cleditor, tinymce, xoops, etc.
                "config"    => array(
                    //"width"     => "500px",
                    //"rows"      => "10",
                ),
                // Enable upload
                //'upload'    => true,
                // Or provide with configs
                'upload'    => array(
                    //'enabled'   => true,
                    'path'      => '',
                ),
            ),
        );
        $form->addElement('textarea', 'message', $options);
        // Or use wysiwyg element
        /*
        $options = array(
            "label"         => "Message",
            "value"         => "Type your message",
            "required"      => true,
            "editor"        => array(
                //"type"      => "ckeditor", // ckeditor, cleditor, tinymce, default, etc.
                "config"    => array(
                    //"width"     => "500px",
                    //"rows"      => "10",
                ),
                // Enable upload
                //'upload'    => true,
                // Or provide with configs
                'upload'    => array(
                    //'enabled'   => true,
                    'path'      => '',
                ),
            ),
        );
        $form->addElement('wysiwyg', 'message', $options);
        */

        // Submit button
        $options = array(
            "label"     => "Submit",
            "required"  => false,
            "ignore"    => true,
        );
        $form->addElement("submit", "save", $options);

        $form->setDescription("Leave us a message");

        $form->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'dl', 'class' => 'xoops-form')),
            array('Description', array('placement' => 'prepend')),
            'Form'
        ));

        return $form;
    }
}
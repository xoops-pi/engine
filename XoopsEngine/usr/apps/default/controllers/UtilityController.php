<?php
/**
 * Default utility controller
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

class Default_UtilityController extends Xoops_Zend_Controller_Action
{
    public function redirectAction()
    {
        $params = $this->getRequest()->getParams();
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->view->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            echo json_encode($params);
        } else {
            $params['message'] = isset($params['message']) ? $params['message'] : XOOPS::_("Go back to previous page.");
            $params['time'] = isset($params['time']) ? $params['time'] : "0";
            $params['url'] = !empty($params['url']) ? $params['url'] : "/";

            $this->template->assign($params);
            $this->template->assign('lang_ifnotreload', sprintf(XOOPS::_("If the page is not reloaded, click <a href='%s'>the link</a> to load."), $params["url"]));
            $this->setTemplate('302.html', 'simple');
            $this->view->headMeta()->appendHttpEquiv('Refresh', $params["time"] . ';URL=' . $params["url"]);
        }

        $this->skipCache();
        $this->view->section = null;
        $this->setLayout("simple");
    }

    public function confirmAction()
    {
        //global $xoops;

        $formName = 'xoopsConfirm';
        //$params = $this->getRequest()->getParams();
        $message = $this->getRequest()->getParam("message", XOOPS::_("Are you sure to continue?"));
        //$form = $this->getRequest()->getParam('form', 'confirmForm');
        $action = $this->getRequest()->getParam('action', '');
        $method = $this->getRequest()->getParam('method', "post");
        $name = $this->getRequest()->getParam('name', "confirm");
        $options = $this->getRequest()->getParam('options', "1"); //array(1 => XOOPS::_("Yes"), 0 => XOOPS::_("No"));
        $hidden = $this->getRequest()->getParam('hidden', null);
        $goback = $this->getRequest()->getParam('goback', '');
        $goback = !empty($goback) ? "location.href='" . $goback . "'" : "go.back(-1)";
        $formOptions = array(
            "name"      => $formName,
            "action"    => $action,
            "method"    => $method,
        );
        $form = new Xoops_Zend_Form($formOptions);

        //$form = new Xoops_Zend_Form();
        //$form = new Zend_Form();
        //$form->setName("confirmForm")->setAction($action)->setMethod($method);
        //$form->addElement("hash", XOOPS::configs('identifier') . '_token', array('salt' => XOOPS::configs('salt')));
        //$hash = new Xoops_Zend_Form_Element_Hash();
        //$form->addElement($hash);

        if (is_array($options)) {
            $element = $form->createElement('radio', $name);
            $element->addMultiOptions($options)->setSeparator(' ')->setRequired(true);
            $form->addElement($element);
            //$form->addElement('radio', $name, array('required' => true, 'MultiOptions' => $options, 'separator' => ' '));
        } else {
            $form->addElement("hidden", $name, array('value' => $options));
        }
        if (!empty($hidden) && is_array($hidden)) {
            foreach ($hidden as $key => $val) {
                $form->addElement("hidden", $key, array('value' => $val));
            }
        }
        $submit = new Zend_Form_Element_Submit("confirm_submit");
        $submit->setOptions(array(
                'label'    => XOOPS::_("Confirm and continue"),
                'required' => false,
                'ignore'   => true,
            ));
        $form->addElement($submit);
        $cancel = new Zend_Form_Element_Button("confirm_cancel");
        $cancel->setOptions(array(
                'onClick' => $goback,
                'label'    => XOOPS::_("Cancel and go back"),
                'required' => false,
                'ignore'   => true,
            ));
        $form->addElement($cancel);
        $form->assign($this->view);
        $this->template->assign("confirmMessage", $message);
        //$this->template->assign("confirmForm", $form->render());

        $this->setTemplate('confirm.html');

        $this->skipCache();
    }

    /**
     * any path that is not catch by any of our actions
     * will be catched by this function by default
     * @param $methodName string
     * @param $args array
     */
    public function __call($methodName, $args)
    {
        //$this->template->assign("");
        $this->template->assign("error_title", XOOPS::_('The page you requested was not found.'));
        $this->setTemplate('404.html');
        $this->view->section = null;
        $this->setLayout("simple");
    }
}

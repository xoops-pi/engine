<?php
/**
 * System admin audit controller
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

class System_AuditController extends Xoops_Zend_Controller_Action_Admin
{
    public function indexAction()
    {
        $this->setTemplate("system/admin/audit_list.html");
        $module = $this->getRequest()->getModuleName();

        $model = XOOPS::getModel("audit");
        $select = $model->select()->order("time DESC")->limit(20);
        $itemSet = $model->fetchAll($select);
        $items = array();

        foreach ($itemSet as $item) {
            $item = $item->toArray();
            $item["time"] = date("Y-m-d H:i:s", $item["time"]);
            $items[] = $item;
        }

        $title = XOOPS::_("Audit Trail");
        $action = $this->view->url(array("action" => "index", "controller" => "audit", "module" => $module));
        $form = $this->getFormList("audit_form_list", $items, $title, $action);
        $form->assign($this->template);
    }

    private function getFormList($name, $items, $title, $action)
    {
        //include_once XOOPS::path('www') . '/class/xoopsformloader.php';
        Xoops_Legacy::autoload();

        $form = new XoopsFormGrid($title, $name, $action, 'post', true);
        $heads = array(
            XOOPS::_("No."),
            XOOPS::_("Time"),
            XOOPS::_("User"),
            XOOPS::_("IP"),
            XOOPS::_("Section"),
            XOOPS::_("Module"),
            XOOPS::_("Controller"),
            XOOPS::_("Action"),
            XOOPS::_("Method"),
            XOOPS::_("Memo"),
            XOOPS::_("Extra"),
        );
        $form->setHead($heads);
        $widths = array(
            2,
            10,
            10,
            10,
            5,
            5,
            5,
            5,
            5,
            20,
            20,
        );
        $form->setWidths($widths);

        $i = 0;
        foreach ($items as $item) {
            $ele = new XoopsFormElementRow(++$i);
            foreach (array("time", "user", "ip", "section", "module", "controller", "action", "method", "memo", "extra") as $key) {
                $label = new XoopsFormLabel("", $item[$key]);
                $ele->addElement($label);
                unset($label);
            }

            $form->addElement($ele);
            unset($ele);
        }
        //$form->addElement(new XoopsFormButton('', 'button', _GO, 'submit'));
        return $form;
    }
}
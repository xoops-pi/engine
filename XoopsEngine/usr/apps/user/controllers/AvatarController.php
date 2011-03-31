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

class User_AvatarController extends Xoops_Zend_Controller_Action
{
    const DEFAULT_AVATAR = "avatar.jpg";

    public function  preDispatch()
    {
        if (!XOOPS::registry("user")->id) {
            $this->_helper->redirector('index', 'login');
        }
    }

    public function indexAction()
    {
        $avatarId = "avatar-img";
        $profileRow = XOOPS::getModel("user_profile")->findRow(XOOPS::registry("user")->id);
        $avatar = $profileRow->avatar;
        if (substr($avatar, 0, 4) == "img/") {
            $avatar = substr($avatar, 4);
        } else {
            $avatar = self::DEFAULT_AVATAR;
        }
        $account = array(
            "id"        => XOOPS::registry("user")->id,
            "identity"  => XOOPS::registry("user")->identity,
            "name"      => XOOPS::registry("user")->name,
            "avatar"    => $profileRow->display("avatar"),
        );
        $this->setTemplate("Avatar_select.html");
        $form = $this->getSelectForm($avatar, $avatarId);
        $form->assign($this->view);
        $title = XOOPS::_("Select Avatar");
        $this->template->assign("title", $title);
        $this->template->assign("avatarid", $avatarId);
        $this->template->assign("account", $account);
    }

    public function saveAction()
    {
        $module = $this->getRequest()->getModuleName();
        if (!$this->getRequest()->isPost()) {
            return $this->_helper->redirector('index');
        }

        $avatar = $this->getRequest()->getPost("avatar");
        $profileRow = XOOPS::getModel("user_profile")->findRow(XOOPS::registry("user")->id);
        $profileRow->avatar = "img/" . $avatar;
        $profileRow->save();
        return $this->_helper->redirector('index');
    }

    protected function getSelectForm($avatar, $avatarId)
    {
        $module = $this->getRequest()->getModuleName();
        $avatarPath = XOOPS::path("img") . "/avatar/";
        $iterator = new DirectoryIterator($avatarPath);
        $avatarList = array();
        //$finfo = finfo_open(FILEINFO_MIME_TYPE);
        foreach ($iterator as $fileinfo) {
            if (!$fileinfo->isFile()) {
                continue;
            }
            //$mimeType = finfo_file($finfo, $fileinfo->getPathname());
            $mimeType = mime_content_type($fileinfo->getPathname());
            if (substr($mimeType, 0, 6) == "image/") {
                $avatarList[$fileinfo->getFilename()] = $fileinfo->getFilename();
            }
        }

        $this->view->headLink(array(
            "href"  => "form.css",
            "rel"   => "stylesheet",
            "type"  => "text/css"
        ));
        $action = $this->view->url(array(
                "action"        => "save",
                "controller"    => "avatar",
                "module"        => $module
            ),
            "default"
        );
        $options = array(
            "name"      => "xoopsAvatar",
            "action"    => $action,
            "method"    => "post",
        );
        $form = new Xoops_Zend_Form($options);

        $options = array(
            "label"         => "Select",
            "value"         => $avatar,
            "onChange"      => "javascript: document.getElementById('" . $avatarId . "').getElementsByTagName('img')[0].src='" . XOOPS::url("img") . "/avatar/' + this.value;",
            "value"         => empty($avatar) ? self::DEFAULT_AVATAR : $avatar,
            "multiOptions"  => $avatarList,
        );
        $form->addElement("Select", "avatar", $options);
        $form->addElement('submit', 'submit_upload', array('label' => 'Confirm'));
        return $form;
    }
}

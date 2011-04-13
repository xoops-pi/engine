<?php
/**
 * Demo module handler
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
 * @package         Demo
 * @version         $Id$
 */

class App_Demo_Module extends Xoops_Installer_Abstract
{
    public function preInstall(&$message)
    {
        $message = $this->message;
        $message[] = 'Called from ' . __METHOD__;
    }

    public function postInstall(&$message)
    {
        $message = $this->message;
        $message[] = 'Called from ' . __METHOD__;

        $model = Xoops::service('module')->getModel('test', $this->module->dirname);
        $data = array(
            'message'   => 'The app is installed on ' . date('Y-m-d H:i:s'),
        );
        $model->insert($data);
    }

    public function preUninstall(&$message)
    {
        $message = $this->message;
        $message[] = 'Called from ' . __METHOD__;
    }

    public function postUninstall(&$message)
    {
        $message = $this->message;
        $message[] = 'Called from ' . __METHOD__;
    }

    public function preUpdate(&$message)
    {
        $message = $this->message;
        $message[] = 'Called from ' . __METHOD__;
    }

    public function postUpdate(&$message)
    {
        $message = $this->message;
        $message[] = 'Called from ' . __METHOD__;

        $model = Xoops::service('module')->getModel('test', $this->module->dirname);
        $data = array(
            'message'   => 'The app is updated on ' . date('Y-m-d H:i:s'),
        );
        $model->insert($data);
    }
}
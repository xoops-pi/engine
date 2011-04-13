<?php
/**
 * Demo module config handler
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


class App_Demo_Configtest extends Xoops_Installer_Module_Abstract
{
    public function install(&$message)
    {
        $message = $this->message;
        $message[] = 'Called from ' . __METHOD__;
    }

    public function uninstall(&$message)
    {
        $message = $this->message;
        $message[] = 'Called from ' . __METHOD__;
    }

    public function update(&$message)
    {
        $message = $this->message;
        $message[] = 'Called from ' . __METHOD__;
    }

}
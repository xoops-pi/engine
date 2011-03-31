<?php
/**
 * XOOPS installer abstract
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Xoops Engine http://www.xoopsengine.org/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @since           3.0
 * @package         Xoops_Installer
 * @subpackage      Installer
 * @version         $Id$
 */

abstract class Xoops_Installer_Abstract
{
    protected $config = array();
    protected $module;
    protected $message = array();
    protected $version;
    protected $installer;

    public function __construct($config, $module, $version = null)
    {
        if (is_string($config)) {
            $config = Xoops_Config::load(Xoops::service('module')->getPath($module->dirname) . '/configs/' . $config);
        }
        $this->module = $module;
        $this->config = $config;
        $this->version = $version;
    }

    public function setInstaller($installer)
    {
        $this->installer = $installer;
    }

    public function install(&$message)
    {
    }

    public function update(&$message)
    {
    }

    public function uninstall(&$message)
    {
    }

    public function activate(&$message)
    {
    }

    public function deactivate(&$message)
    {
    }
}
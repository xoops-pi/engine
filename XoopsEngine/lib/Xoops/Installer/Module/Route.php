<?php
/**
 * XOOPS module route installer
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

class Xoops_Installer_Module_Route extends Xoops_Installer_Abstract
{
    public function install(&$message)
    {
        $module = $this->module->dirname;
        $message = $this->message;
        $status = true;

        if (empty($this->config)) {
            return;
        }

        $modelRoute = XOOPS::getModel("route");
        foreach ($this->config as $name => $route) {
            $data = array(
                "name"      => $name,
                "module"    => $module,
            );
            if (isset($route["priority"])) {
                $data["priority"] = $route["priority"];
                unset($route["priority"]);
            }
            $data["data"] = serialize($route);

            $status = $modelRoute->insert($data) * $status;
        }
        XOOPS::service('registry')->route->flush();

        return $status;
    }

    public function update(&$message)
    {
        $module = $this->module->dirname;
        $message = $this->message;
        $status = true;

        if (version_compare($this->version, $this->module->version, ">=")) {
            return true;
        }

        $modelRoute = XOOPS::getModel("route");
        $modelRoute->delete(array("module = ?" => $module));

        foreach ($this->config as $name => $route) {
            $data = array(
                "name"      => $name,
                "module"    => $module,
                "priority"  => 0,
            );
            if (isset($route["priority"])) {
                $data["priority"] = $route["priority"];
                unset($route["priority"]);
            }
            if (isset($route["section"])) {
                $data["section"] = $route["section"];
                unset($route["section"]);
            }
            $data["data"] = serialize($route);

            $status = $modelRoute->insert($data) * $status;
        }
        XOOPS::service('registry')->route->flush();

        return $status;
    }

    public function uninstall(&$message)
    {
        if (!is_object($this->module)) {
            return;
        }
        $module = $this->module->dirname;
        $message = $this->message;
        $status = true;

        $modelRoute = XOOPS::getModel("route");
        $modelRoute->delete(array("module = ?" => $module));
        XOOPS::service('registry')->route->flush();

        return true;
    }

    public function activate(&$message)
    {
        $module = $this->module->dirname;
        $message = $this->message;

        $modelRoute = XOOPS::getModel("route");
        $modelRoute->update(array("active" => 1), array('module = ?' => $module));
        XOOPS::service('registry')->route->flush();

        return true;
    }

    public function deactivate(&$message)
    {
        $module = $this->module->dirname;
        $message = $this->message;

        $modelRoute = XOOPS::getModel("route");
        $modelRoute->update(array("active" => 0), array('module = ?' => $module));
        XOOPS::service('registry')->route->flush();

        return true;
    }
}
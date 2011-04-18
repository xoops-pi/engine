<?php
/**
 * XOOPS module monitor installer
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

class Xoops_Installer_Module_Monitor extends Xoops_Installer_Abstract
{
    public function install(&$message)
    {
        $module = $this->module->dirname;
        $message = $this->message;
        $status = true;

        if (empty($this->config)) {
            return;
        }

        XOOPS::service('registry')->monitor->flush();
        $model = XOOPS::getModel("monitor");
        $data = $this->config;
        $classPrefix = (('app' == Xoops::service('module')->getType($module)) ? 'app' : 'module') . '\\' . ($this->module->parent ?: $module);
        $data['callback'] = $classPrefix . '\\' . $data['callback'];
        $data["module"] = $module;
        $status = $model->insert($data);

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
        XOOPS::service('registry')->monitor->flush();

        $model = XOOPS::getModel("monitor");
        $select = $model->select()->where("module = ?", $module);
        $row = $model->fetchRow($select);
        if (empty($this->config)) {
            if ($row) {
                $model->delete(array("id = ?" => $row->id));
            }
            return;
        }
        $data = $this->config;
        $classPrefix = (('app' == Xoops::service('module')->getType($module)) ? 'app' : 'module') . '\\' . ($this->module->parent ?: $module);
        $data['callback'] = $classPrefix . '\\' . $data['callback'];
        if ($row) {
            $status = $model->update($data, array("id = ?" => $row->id));
        } else {
            $status = $model->insert($data);
        }

        return $status;
    }

    public function uninstall(&$message)
    {
        if (!is_object($this->module)) {
            return;
        }

        $module = $this->module->dirname;
        $message = $this->message;

        $model = XOOPS::getModel("monitor");
        $model->delete(array("module = ?" => $module));
        XOOPS::service('registry')->monitor->flush();
        return;
    }
}
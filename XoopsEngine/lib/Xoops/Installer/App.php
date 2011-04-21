<?php
/**
 * XOOPS app and module installer
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

class Xoops_Installer_App
{
    protected $installer;

    public function __construct($installer)
    {
        $this->installer = $installer;
    }

    public function install($name)
    {
        $return = array();
        $message = array();
        $classPrefix = ('app' == Xoops::service('module')->getType($name) ? 'app' : 'module') . '_' . $name;

        Xoops_Zend_Db_File_Mysql::reset();
        //XOOPS::service('translate')->loadTranslation('install', $name);
        // Load configuration
        $config = $this->installer->loadConfig($name);
        $model = XOOPS::getModel("module");
        if (!empty($this->installer->options["dirname"])) {
            $moduleData = array(
                "name"      => empty($this->installer->options["name"]) ? $config['name'] : $this->installer->options["name"],
                "version"   => $config['version'],
                "dirname"   => $this->installer->options["dirname"],
                "parent"    => $name,
            );
        } else {
            $moduleData = array(
                "name"      => $config['name'],
                "version"   => $config['version'],
                "dirname"   => $name,
            );
        }

        $module = $model->createRow();
        $module->setFromArray($moduleData);
        // execute preInstall
        if (!empty($config['onInstall'])) {
            $class = $classPrefix . '_' . $config['onInstall'];
            if (class_exists($class) && method_exists($class, "preInstall")) {
                $instHandler = new $class($config, $module);
                $ret = $instHandler->preInstall($message);
                $return['preinstall'] = array('status' => $ret, "message" => $message);
                if (false === $ret) {
                    return $return;
                }
            }
        }

        // save module entry into database
        if (!$moduleId = $model->insert($moduleData)) {
            $return['module']['status'] = false;
            $return['module']['message'] = array("Module insert failed");
            return $return;
        }
        $module->id = $moduleId;
        $this->updateMeta();

        // process extensions
        $extensions = array();
        if (!empty($config['extensions'])) {
            $extensions = array_keys($config['extensions']);
        }
        $extensionList = array_unique(array_merge($this->loadExtensions(), $extensions));
        foreach ($extensionList as $extension) {
            $action = __FUNCTION__;
            if (!$extensionHandler = $this->installer->loadExtension($extension, $module)) continue;
            $ret = $extensionHandler->{$action}($message);
            $return[$extension] = array('status' => $ret, "message" => $message);
            if (false === $ret) {
                $model->delete(array("id = ?" => $module->id));
                return $return;
            }
        }

        // execute postInstall
        if (!empty($config['onInstall'])) {
            $class = $classPrefix . '_' . $config['onInstall'];
            if (class_exists($class) && method_exists($class, "postInstall")) {
                if (empty($instHandler)) {
                    $instHandler = new $class($config, $module);
                }
                $ret = $instHandler->postInstall($message);
                $return['postInstall'] = array('status' => $ret, "message" => $message);
            }
        }

        return $return;
    }

    public function update($name)
    {
        $return = array();
        $message = array();
        $classPrefix = ('app' == Xoops::service('module')->getType($name) ? 'app' : 'module') . '_' . $name;

        //$config =& $this->installer->config;
        $model = XOOPS::getModel("module");
        $module = $model->load($name);
        $config = $this->installer->loadConfig($module->parent ? $module->parent : $name);
        $oldVersion = $module->version;
        $moduleData = array(
            "version"   => $config['version'],
        );
        $module->version = $config['version'];

        // execute preUpdate
        if (!empty($config['onUpdate'])) {
            $class = $classPrefix . '_' . $config['onUpdate'];
            if (class_exists($class) && method_exists($class, "preUpdate")) {
                $instHandler = new $class($config, $module, $oldVersion);
                $ret = $instHandler->preUpdate($message);
                $return['preUpdate'] = array('status' => $ret, "message" => $message);
                if (false === $ret) {
                    return $return;
                }
            }
        }

        // save module entry into database
        $model->update($moduleData, array("id = ?" => $module->id));
        /*
        if (!$model->update($moduleData, array("id = ?" => $module->id))) {
            $return['module']['status'] = false;
            $return['module']['message'] = "Module update failed";
            return $return;
        }
        */

        // process extensions
        $extensions = array();
        if (!empty($config['extensions'])) {
            $extensions = array_keys($config['extensions']);
        }
        $extensionList = array_unique(array_merge($this->loadExtensions(), $extensions));
        foreach ($extensionList as $extension) {
            if (!$extensionHandler = $this->installer->loadExtension($extension, $module, null, $oldVersion)) continue;
            $action = __FUNCTION__;
            $ret = $extensionHandler->{$action}($message);
            $return[$extension] = array('status' => $ret, "message" => $message);
            if (false === $ret) {
                return $return;
            }
        }

        // execute postUpdate
        if (!empty($config['onUpdate'])) {
            $class = $classPrefix . '_' . $config['onUpdate'];
            if (class_exists($class) && method_exists($class, "postUpdate")) {
                if (empty($instHandler)) {
                    $instHandler = new $class($config, $module, $oldVersion);
                }
                $ret = $instHandler->postUpdate($message);
                $return['postUpdate'] = array('status' => $ret, "message" => $message);
            }
        }

        return $return;
    }

    public function unInstall($name)
    {
        $return = array();
        $message = array();
        $classPrefix = ('app' == Xoops::service('module')->getType($name) ? 'app' : 'module') . '_' . $name;

        $model = XOOPS::getModel("module");
        if (!$module = $model->load($name)) {
            $module = $model->createRow(array("dirname" => $name));
        }
        $config = $this->installer->loadConfig($module->parent ? $module->parent : $name);

        // execute preUninstall
        if (!empty($config['onUninstall'])) {
            $class = $classPrefix . '_' . $config['onUninstall'];
            if (class_exists($class) && method_exists($class, "preUninstall")) {
                $return['preUninstall']['status'] = true;
                $instHandler = new $class($config, $module);
                $ret = $instHandler->preUninstall($message);
                $return['preUninstall'] = array('status' => $ret, "message" => $message);
                if (false === $ret) {
                    return $return;
                }
            }
        }

        // remove module entity from database
        if (is_object($module) && $module->id && !$model->delete(array("id = ?" => $module->id))) {
            $return['module']['status'] = false;
            $return['module']['message'] = "Module delete failed";
            return $return;
        }
        $this->updateMeta();

        // process extensions
        $extensions = array();
        if (!empty($config['extensions'])) {
            $extensions = array_keys($config['extensions']);
        }
        $extensionList = array_unique(array_merge($this->loadExtensions(), $extensions));
        foreach ($extensionList as $extension) {
            if (!$extensionHandler = $this->installer->loadExtension($extension, $module)) continue;
            $action = __FUNCTION__;
            $ret = $extensionHandler->{$action}($message);
            $return[$extension] = array('status' => $ret, "message" => $message);
            if (false === $ret) {
                return $return;
            }
        }

        // execute postUninstall
        if (!empty($config['onUninstall'])) {
            $class = $classPrefix . '_' . $config['onUninstall'];
            if (class_exists($class) && method_exists($class, "postUninstall")) {
                if (empty($instHandler)) {
                    $instHandler = new $class($config, $module);
                }
                $ret = $instHandler->postUninstall($message);
                $return['postUninstall'] = array('status' => $ret, "message" => $message);
            }
        }

        return $return;
    }

    public function activate($name)
    {
        $return = array();
        $message = array();

        $model = XOOPS::getModel("module");
        $status = $model->update(array("active" => 1), array("dirname = ?" => $name));
        if (!$status) {
            $return['module']['status'] = false;
            $return['module']['message'] = "Module activation failed";
            return $return;
        }
        $this->updateMeta();
        $module = $model->load($name);
        $config = $this->installer->loadConfig($module->parent ? $module->parent : $name);

        // process extensions
        $extensions = array();
        if (!empty($config['extensions'])) {
            $extensions = array_keys($config['extensions']);
        }
        $extensionList = array_unique(array_merge($this->loadExtensions(), $extensions));
        foreach ($extensionList as $extension) {
            if (!$extensionHandler = $this->installer->loadExtension($extension, $module)) continue;
            $action = __FUNCTION__;
            $ret = $extensionHandler->{$action}($message);
            $return[$extension] = array('status' => $ret, "message" => $message);
            if (false === $ret) {
                return $return;
            }
        }
        return $return;
    }

    public function deactivate($name)
    {
        $return = array();
        $message = array();

        if ($name == "system") {
            $return['module']['status'] = false;
            $return['module']['message'] = "The module is not allowed to deactivate";
            return $return;
        }

        $model = XOOPS::getModel("module");
        $status = $model->update(array("active" => 0), array("dirname = ?" => $name));
        if (!$status) {
            $return['module']['status'] = false;
            $return['module']['message'] = "Module deactivation failed";
            return $return;
        }
        $this->updateMeta();
        $module = $model->load($name);
        $config = $this->installer->loadConfig($module->parent ? $module->parent : $name);

        // process extensions
        $extensions = array();
        if (!empty($config['extensions'])) {
            $extensions = array_keys($config['extensions']);
        }
        $extensionList = array_unique(array_merge($this->loadExtensions(), $extensions));
        foreach ($extensionList as $extension) {
            if (!$extensionHandler = $this->installer->loadExtension($extension, $module)) continue;
            $action = __FUNCTION__;
            $ret = $extensionHandler->{$action}($message);
            $return[$extension] = array('status' => $ret, "message" => $message);
            if (false === $ret) {
                return $return;
            }
        }
        return $return;
    }

    protected function loadExtensions()
    {
        $extensions = array();
        $iterator = new DirectoryIterator(__DIR__ . "/Module");
        foreach ($iterator as $fileinfo) {
            if (!$fileinfo->isFile()) {
                continue;
            }
            $fileName = $fileinfo->getFilename();
            if (!preg_match("/^([^\.]+)\.php$/", $fileName, $matches)) {
                continue;
            }
            $extension = strtolower($matches[1]);
            if ($extension == "config") {
                continue;
            }
            $extensions[] = $extension;
        }
        $extensions[] = "config";
        return $extensions;
    }

    protected function updateMeta()
    {
        XOOPS::persist()->clean();
        XOOPS::service("registry")->module->flush();
        $moduleList = XOOPS::service("registry")->module->read();
        foreach ($moduleList as $key => &$module) {
            $module = array(
                "directory" => empty($module["parent"]) ? $key : $module["parent"],
                "type"      => $module["type"],
                "active"    => $module["active"],
            );
        }

        //Xoops::persist()->clean();
        $configFile = Xoops::path('var') . '/etc/' . Xoops::service('module')->getMetaFile();
        clearstatcache();
        if (!file_exists($configFile)) {
            touch($configFile);
        } elseif (!is_writable($configFile)) {
            @chmod($configFile, intval('0777', 8));
        }
        Xoops_Config::write($configFile, $moduleList);
        @chmod($configFile, intval('0444', 8));
        clearstatcache();
        Xoops::service('module')->init(true);
        return true;
    }
}
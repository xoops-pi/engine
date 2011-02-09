<?php
/**
 * XOOPS legacy module installer
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       The Xoops Engine http://sourceforge.net/projects/xoops/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @since           3.0
 * @package         Xoops_Installer
 * @subpackage      Installer
 * @version         $Id$
 */

class Xoops_Installer_Legacy extends Xoops_Installer_App
{
    public function __construct($installer)
    {
        parent::__construct($installer);
        if (empty($GLOBALS['xoopsDB'])) {
            $options = array(
                "prefix"    => XOOPS::registry("db")->prefix(),
            );
            $GLOBALS['xoopsDB'] = new Xoops_Zend_Db_Legacy(XOOPS::registry("db"), $options);
        }
        if (empty($GLOBALS['xoopsConfig'])) {
            $GLOBALS['xoopsConfig'] = Xoops::config();
        }
    }

    public function install($name)
    {
        $return = array();
        $message = array();

        if (!$this->compat($message)) {
            $return['compat']['status'] = false;
            $return['compat']['message'] = $message;
            return $return;
        }
        Xoops_Zend_Db_File_Mysql::reset();
        //XOOPS::service('translate')->loadTranslation('modinfo', $name);
        // Load configuration
        $config = $this->installer->loadConfig($name);
        $model = XOOPS::getModel("module");
        $moduleData = array(
            "name"      => $config['name'],
            "version"   => $config['version'],
            "dirname"   => $name,
        );

        $module = $model->createRow();
        $module->setFromArray($moduleData);
        // execute preInstall
        if (!empty($config['onInstall'])) {
            include Xoops::service('module')->getPath($name) . '/' . $config['onInstall'];
            $func = "xoops_module_pre_install_{$name}";
            if (function_exists($func)) {
                // Initialize module
                $module_handler = XOOPS::getHandler('module');
                $moduleObject = $module_handler->create();
                $moduleObject->setVar('name', $config['name'], true);
                $moduleObject->setVar('version', $config['version'], true);
                $moduleObject->setVar('dirname', $name, true);

                $ret = $func($moduleObject);
                if (!$ret) {
                    $message[] = "xoops_module_pre_install_{$name} failed";
                } else {
                    $message[] = "xoops_module_pre_install_{$name} executed";
                }
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
            $func = "xoops_module_install_{$name}";
            if (function_exists($func)) {
                if (!isset($moduleObject)) {
                    // Initialize module
                    $module_handler = XOOPS::getHandler('module');
                    $moduleObject = $module_handler->create();
                    $moduleObject->setVar('name', $config['name'], true);
                    $moduleObject->setVar('version', $config['version'], true);
                    $moduleObject->setVar('dirname', $name, true);
                }

                $moduleObject->setVar("mid", $module->id);
                $ret = $func($moduleObject);
                if (!$ret) {
                    $message[] = "xoops_module_install_{$name} failed";
                } else {
                    $message[] = "xoops_module_install_{$name} executed";
                }
                $return['postinstall'] = array('status' => $ret, "message" => $message);
            }
        }

        return $return;
    }

    public function update($name)
    {
        $return = array();
        $message = array();

        if (!$this->compat($message)) {
            $return['compat']['status'] = false;
            $return['compat']['message'] = $message;
            return $return;
        }
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
            include Xoops::service('module')->getPath($name) . '/' . $config['onUpdate'];
            $func = "xoops_module_pre_update_{$name}";
            if (function_exists($func)) {
                // Initialize module
                $module_handler = XOOPS::getHandler('module');
                $moduleObject = $module_handler->getByDirname($name);
                $moduleObject->setVar('version', $config['version'], true);
                $ret = $func($moduleObject, $oldVersion);
                if (!$ret) {
                    $message[] = "xoops_module_pre_update_{$name} failed";
                } else {
                    $message[] = "xoops_module_pre_update_{$name} executed";
                }
                $return['preupdate'] = array('status' => $ret, "message" => $message);
                if (false === $ret) {
                    return $return;
                }
            }
        }

        // save module entry into database
        if (!$model->update($moduleData, array("id = ?" => $module->id))) {
            $return['module']['status'] = false;
            $return['module']['message'] = "Module update failed";
            return $return;
        }

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
            $return['postUpdate'] = $this->updatepre($module, $config, $instHandler);
            $func = "xoops_module_update_{$name}";
            if (function_exists($func)) {
                if (!isset($moduleObject)) {
                    // Initialize module
                    $module_handler = XOOPS::getHandler('module');
                    $moduleObject = $module_handler->getByDirname($name);
                    $moduleObject->setVar('version', $config['version'], true);
                }
                $ret = $func($moduleObject, $oldVersion);
                if (!$ret) {
                    $message[] = "xoops_module_update_{$name} failed";
                } else {
                    $message[] = "xoops_module_update_{$name} executed";
                }
                $return['postupdate'] = array('status' => $ret, "message" => $message);
            }
        }

        return $return;
    }

    public function unInstall($name)
    {
        $return = array();
        $message = array();

        if (!$this->compat($message)) {
            $return['compat']['status'] = false;
            $return['compat']['message'] = $message;
            return $return;
        }
        $model = XOOPS::getModel("module");
        if (!$module = $model->load($name)) {
            $module = $model->createRow(array("dirname" => $name));
        }
        $config = $this->installer->loadConfig($module->parent ? $module->parent : $name);

        // execute preUninstall
        if (!empty($config['onUninstall'])) {
            include Xoops::service('module')->getPath($name) . '/' . $config['onUninstall'];
            $func = "xoops_module_pre_uninstall_{$name}";
            if (function_exists($func)) {
                // Initialize module
                $module_handler = XOOPS::getHandler('module');
                if (!$moduleObject = $module_handler->getByDirname($name)) {
                    $moduleObject = $name;
                }
                $ret = $func($moduleObject);
                if (!$ret) {
                    $message[] = "xoops_module_pre_uninstall_{$name} failed";
                } else {
                    $message[] = "xoops_module_pre_uninstall_{$name} executed";
                }
                $return['preuninstall'] = array('status' => $ret, "message" => $message);
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
            $func = "xoops_module_uninstall_{$name}";
            if (function_exists($func)) {
                if (!isset($moduleObject)) {
                    // Initialize module
                    $module_handler = XOOPS::getHandler('module');
                    if (!$moduleObject = $module_handler->getByDirname($name)) {
                        $moduleObject = $name;
                    }
                }
                $ret = $func($moduleObject);
                if (!$ret) {
                    $message[] = "xoops_module_uninstall_{$name} failed";
                } else {
                    $message[] = "xoops_module_uninstall_{$name} executed";
                }
                $return['postuninstall'] = array('status' => $ret, "message" => $message);
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

        if (!$this->compat($message)) {
            $return['compat']['status'] = false;
            $return['compat']['message'] = $message;
            return $return;
        }
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

    /**
     * Checks database tables for legacy modules. Creates them if not available
     *
     * View is used for some tables including modules, users, etc., proposed by Chinese developer 'Simple'
     */
    protected function compat(& $message)
    {
        global $xoopsDB;

        // Check if legacy tables are set up
        $sql = "SHOW TABLES LIKE '" . $xoopsDB->prefix('groups') . "'";
        $count = Xoops::registry('db')->query($sql)->rowCount();
        if ($count > 0) return true;

        $onFailure = function ($cretedTables, $createdViews)
        {
            foreach ($createdTables as $ct) {
                $xoopsDB->query("DROP TABLE IF EXISTS " . $xoopsDB->prefix($ct));
            }
            foreach ($createdViews as $ct) {
                $xoopsDB->query("DROP VIEW IF EXISTS " . $xoopsDB->prefix($ct));
            }
            return false;
        };

        XOOPS::registry('application')->getBootstrap()->bootstrap('legacy');
        Xoops::service('translate')->loadTranslation('legacy', 'system');

        $status = 1;
        $createdViews = array();

        // Create legacy tables
        $sqlFile = Xoops::service('module')->getPath('system') . '/sql/mysql.legacy.sql';
        $status = $status * (int) $xoopsDB->queryFromFile($sqlFile, $logs);
        $createdTables = Xoops_Zend_Db_File_Mysql::getLogs("create");
        if (!$status) {
            $message += $logs;
            return $onFailure($cretedTables, $createdViews);
        }

        // Create legacy modules view
        $sql = "CREATE VIEW " . $xoopsDB->prefix('modules') . " AS SELECT * FROM " . Xoops::getModel('module')->info('name');
        $status = $status * (int) $xoopsDB->query($sql);
        $createdViews[] = 'modules';
        if (!$status) {
            $message[] = "View 'modules' is not created";
            return $onFailure($cretedTables, $createdViews);
        }

        // Create legacy configitem view
        $sql = "CREATE VIEW " . $xoopsDB->prefix('configitem') . " AS SELECT * FROM " . Xoops::getModel('config')->info('name');
        $status = $status * (int) $xoopsDB->query($sql);
        $createdViews[] = 'configitem';
        if (!$status) {
            $message[] = "View 'configitem' is not created";
            return $onFailure($cretedTables, $createdViews);
        }

        // Create legacy configcategory view
        $sql = "CREATE VIEW " . $xoopsDB->prefix('configcategory') . " AS SELECT * FROM " . Xoops::getModel('config_category')->info('name');
        $status = $status * (int) $xoopsDB->query($sql);
        $createdViews[] = 'configcategory';
        if (!$status) {
            $message[] = "View 'configcategory' is not created";
            return $onFailure($cretedTables, $createdViews);
        }

        // Create legacy configoption view
        $sql = "CREATE VIEW " . $xoopsDB->prefix('configoption') . " AS SELECT * FROM " . Xoops::getModel('config_option')->info('name');
        $status = $status * (int) $xoopsDB->query($sql);
        $createdViews[] = 'configoption';
        if (!$status) {
            $message[] = "View 'configoption' is not created";
            return $onFailure($cretedTables, $createdViews);
        }

        // Create legacy users view
        $sql = "CREATE VIEW " . $xoopsDB->prefix('users') . " AS SELECT " .
                "account.name as name, account.identity as uname, account.credential as pass, account.email as email, account.active as level, " .
                "legacy.* " .
                "FROM " . Xoops::getModel('user_account')->info('name') . " AS account " .
                "LEFT JOIN " . $xoopsDB->prefix('legacy_users') . " AS legacy on legacy.uid = account.id";
        $status = $status * (int) $xoopsDB->query($sql);
        $createdViews[] = 'users';
        if (!$status) {
            $message[] = "View 'users' is not created";
            return $onFailure($cretedTables, $createdViews);
        }

        // Create groups
        $groupList = array(
            array(
                'groupid'       => XOOPS_GROUP_ADMIN,
                'name'          => _SYSTEM_LEGACY_WEBMASTER,
            ),
            array(
                'groupid'       => XOOPS_GROUP_USERS,
                'name'          => _SYSTEM_LEGACY_MEMBER,
            ),
            array(
                'groupid'       => XOOPS_GROUP_ANONYMOUS,
                'name'          => _SYSTEM_LEGACY_ANONYMOUS,
            ),
        );
        foreach ($groupList as $groupData) {
            //$xoopsDB->queryF("INSERT INTO " . $xoopsDB->prefix("groups") . " (`groupid`, `name`) VALUES (" . $groupData['groupid'] . ", " . $xoopsDB->quote($groupData['name']) . ")");
        }
        /**/
        $group_handler = XOOPS::getHandler("group");
        foreach ($groupList as $groupData) {
            $group = $group_handler->create();
            $group->setVar("groupid", $groupData['groupid']);
            $group->setVar("name", $groupData['name']);
            $status = $status * (int) $group_handler->insert($group);
        }
        /**/
        if (!$status) {
            $message[] = "Group data are not created";
            return $onFailure($cretedTables, $createdViews);
        }

        // Smileys
        $smileyMap = array(
            ':-D'       => 'smil3dbd4d4e4c4f2.gif',
            ':-)'       => 'smil3dbd4d6422f04.gif',
            ':-('       => 'smil3dbd4d75edb5e.gif',
            ':-o'       => 'smil3dbd4d8676346.gif',
            ':-?'       => 'smil3dbd4d99c6eaa.gif',
            '8-)'       => 'smil3dbd4daabd491.gif',
            ':lol:'     => 'smil3dbd4dbc14f3f.gif',
            ':-x'       => 'smil3dbd4dcd7b9f4.gif',
            ':-P'       => 'smil3dbd4ddd6835f.gif',
            ':oops:'    => 'smil3dbd4df1944ee.gif',
            ':cry:'     => 'smil3dbd4e02c5440.gif',
            ':evil:'    => 'smil3dbd4e1748cc9.gif',
            ':roll:'    => 'smil3dbd4e29bbcc7.gif',
            ';-)'       => 'smil3dbd4e398ff7b.gif',
            ':pint:'    => 'smil3dbd4e4c2e742.gif',
            ':hammer:'  => 'smil3dbd4e5e7563a.gif',
            ':idea:'    => 'smil3dbd4e7853679.gif',
        );
        $i = 0;
        foreach ($smileyMap as $code => $image) {
            $status = $status * (int) $xoopsDB->queryF("INSERT INTO " . $xoopsDB->prefix("smiles") . " (`code`, `smile_url`, `emotion`) VALUES (" . $xoopsDB->quote($code) . ", " . $xoopsDB->quote($image) . ", " . $xoopsDB->quote(constant("_SYSTEM_LEGACY_SMILEY_" . (++$i))) . ")");
        }
        if (!$status) {
            $message[] = "Smiley data are not created";
            return $onFailure($cretedTables, $createdViews);
        }

        // ranks
        $rankList = array(
            array(
                'param'     => array(0, 20, 0),
                'image'     => 'rank3e632f95e81ca.gif'
            ),
            array(
                'param'     => array(21, 40, 0),
                'image'     => 'rank3dbf8e94a6f72.gif'
            ),
            array(
                'param'     => array(41, 70, 0),
                'image'     => 'rank3dbf8e9e7d88d.gif'
            ),
            array(
                'param'     => array(71, 150, 0),
                'image'     => 'rank3dbf8ea81e642.gif'
            ),
            array(
                'param'     => array(151, 10000, 0),
                'image'     => 'rank3dbf8eb1a72e7.gif'
            ),
            array(
                'param'     => array(0, 0, 1),
                'image'     => 'rank3dbf8edf15093.gif'
            ),
            array(
                'param'     => array(0, 0, 1),
                'image'     => 'rank3dbf8ee8681cd.gif'
            ),
        );
        $i = 0;
        foreach ($rankList as $rank) {
            $status = $status * (int) $xoopsDB->queryF("INSERT INTO " . $xoopsDB->prefix("ranks") . " (`rank_title`, `rank_min`, `rank_max`, `rank_special`, `rank_image`) VALUES (" . $xoopsDB->quote(constant("_SYSTEM_LEGACY_RANK_" . (++$i))) . ", " . $rank['param'][0] . ", " . $rank['param'][1] . ", " . $rank['param'][2] . ", " . $xoopsDB->quote($rank['image']) . ")");
        }
        if (!$status) {
            $message[] = "Rank data are not created";
            return $onFailure($cretedTables, $createdViews);
        }

        return $status;
    }
}
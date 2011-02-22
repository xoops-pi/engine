<?php
/**
 * Legacy module installer
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
 * @category        Xoops_Module
 * @package         Legacy
 * @version         $Id$
 */

class Module_Legacy_Installer extends Xoops_Installer_Abstract
{
    public function preInstall(&$message)
    {
        return $this->setupDb($message);
    }

    public function preUninstall(&$message)
    {
        $moduleCleaned = true;
        $modules = Xoops::service('module')->getMeta();
        foreach ($modules as $module) {
            if ($module['type'] == 'legacy') {
                $moduleCleaned = false;
                break;
            }
        }
        if (false === $moduleCleaned) {
            $message[] = Xoops::_("The module is not allowed for uninstallation. There are legacy modules dependent on it.");
        }

        return $moduleCleaned;
    }

    public function postUninstall(&$message)
    {
    }

    /**
     * Checks database tables for legacy modules. Creates them if not available
     *
     * View is used for some tables including modules, users, etc., proposed by Chinese developer 'Simple'
     */
    protected function setupDb(& $message)
    {
        global $xoopsDB;
        XOOPS::registry('application')->getBootstrap()->bootstrap('legacy');
        Xoops::service('translate')->loadTranslation('system', 'legacy');

        /*
        // Check if legacy tables are set up
        $sql = "SHOW TABLES LIKE '" . $xoopsDB->prefix('groups') . "'";
        $count = Xoops::registry('db')->query($sql)->rowCount();
        if ($count > 0) return true;
        */

        $onFailure = function ($cretedTables, $createdViews) use ($xoopsDB)
        {
            foreach ($createdTables as $ct) {
                $xoopsDB->query("DROP TABLE IF EXISTS " . $xoopsDB->prefix($ct));
            }
            foreach ($createdViews as $ct) {
                $xoopsDB->query("DROP VIEW IF EXISTS " . $xoopsDB->prefix($ct));
            }
            return false;
        };

        $status = 1;
        $createdViews = array();

        // Create legacy tables
        $sqlFile = Xoops::path('module') . '/legacy/sql/mysql.system.sql';
        $status = $status * Xoops_Zend_Db_File_Mysql::queryFile($sqlFile, $logs);
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

        // Create legacy users view
        $sql = "CREATE VIEW " . $xoopsDB->prefix('users') . " AS SELECT " .
                "account.id as uid, account.name as name, account.identity as uname, account.credential as pass, account.email as email, account.active as level, " .
                "legacy.* " .
                "FROM " . Xoops::getModel('user_account')->info('name') . " AS account " .
                "LEFT JOIN " . $xoopsDB->prefix('legacy_users') . " AS legacy on legacy.id = account.id";
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
                'name'          => _LEGACY_SYSTEM_WEBMASTER,
            ),
            array(
                'groupid'       => XOOPS_GROUP_USERS,
                'name'          => _LEGACY_SYSTEM_MEMBER,
            ),
            array(
                'groupid'       => XOOPS_GROUP_ANONYMOUS,
                'name'          => _LEGACY_SYSTEM_ANONYMOUS,
            ),
        );
        foreach ($groupList as $groupData) {
            //$xoopsDB->query("INSERT INTO " . $xoopsDB->prefix("groups") . " (`groupid`, `name`) VALUES (" . $groupData['groupid'] . ", " . $xoopsDB->quote($groupData['name']) . ")");
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
            $status = $status * (int) $xoopsDB->query("INSERT INTO " . $xoopsDB->prefix("smiles") . " (`code`, `smile_url`, `emotion`) VALUES (" . $xoopsDB->quote($code) . ", " . $xoopsDB->quote($image) . ", " . $xoopsDB->quote(constant("_LEGACY_SYSTEM_SMILEY_" . (++$i))) . ")");
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
            $status = $status * (int) $xoopsDB->query("INSERT INTO " . $xoopsDB->prefix("ranks") . " (`rank_title`, `rank_min`, `rank_max`, `rank_special`, `rank_image`) VALUES (" . $xoopsDB->quote(constant("_LEGACY_SYSTEM_RANK_" . (++$i))) . ", " . $rank['param'][0] . ", " . $rank['param'][1] . ", " . $rank['param'][2] . ", " . $xoopsDB->quote($rank['image']) . ")");
        }
        if (!$status) {
            $message[] = "Rank data are not created";
            return $onFailure($cretedTables, $createdViews);
        }

        return $status;
    }
}
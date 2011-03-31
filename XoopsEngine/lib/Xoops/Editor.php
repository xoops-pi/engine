<?php
/**
 * Form editor for Xoops Engine
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
 * @package         Xoops_Core
 * @since           3.0
 * @version         $Id$
 */

namespace Xoops;

class Editor
{
    public static function load($type = null, $options = array())
    {
        if (empty($type)) {
            $pref = \Xoops::service('registry')->config->read('', 'text');
            $type = $pref['editor'];
        }
        $type = $type ?: 'xoops';
        $editorClass = 'Editor\\' . ucfirst($type) . '\\Handler';
        if (!class_exists($editorClass, false)) {
            $editorFile = \Xoops::path('usr') . '/editors/' . $type . '/handler.php';
            if (file_exists($editorFile)) {
                include $editorFile;
            }
        }
        if (!class_exists($editorClass) || !is_subclass_of($editorClass, 'Xoops\\Editor\\AbstractEditor')) {
            $editorClass = 'Xoops\\Editor\\Xoops\\Handler';
        }
        $editor = new $editorClass($options);
        return $editor;
    }

    public static function getList()
    {
        $list = array('xoops' => 'Xoops Default Editor');
        $iterator = new \DirectoryIterator(\Xoops::path('usr') . '/editors');
        foreach ($iterator as $fileinfo) {
            if (!$fileinfo->isDir() || $fileinfo->isDot()) {
                continue;
            }
            $name = $fileinfo->getFilename();
            if (preg_match("/[^a-z0-9_]/i", $name)) {
                continue;
            }
            $configFile = $fileinfo->getPathname() . "/info.php";
            if (!file_exists($configFile)) {
                $list[$name] = $name;
                continue;
            }
            $info = include $configFile;
            if (!empty($info["disable"])) continue;
            if (!empty($info["name"])) {
                $list[$name] = $info["name"];
            }
        }

        return $list;
    }
}
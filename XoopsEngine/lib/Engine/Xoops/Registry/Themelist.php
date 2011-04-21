<?php
/**
 * XOOPS theme list registry
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
 * @package         Xoops_Core
 * @subpackage      Registry
 * @version         $Id$
 */

namespace Engine\Xoops\Registry;

class Themelist extends \Kernel\Registry
{
    /**
     * Load raw data
     *
     * @param   array   $options potential values for type: installed, active, inactive, install
     * @return  array   keys: dirname, name, (mid), weight, image, author, version
     */
    protected function loadDynamic($options)
    {
        $type = isset($options['type']) ? $options['type'] : 'installed';
        $themes = array();
        if ($type == 'install' || $type == 'deploy') {
            $skipList = ($type == 'deploy') ? array() : array_keys(self::read('installed'));
            $themePath = ($type == 'deploy') ? 'usr/themes' : 'theme';
            $iterator = new \DirectoryIterator(\Xoops::path($themePath));
            foreach ($iterator as $fileinfo) {
                if (!$fileinfo->isDir() || $fileinfo->isDot()) {
                    continue;
                }
                $themeName = $fileinfo->getFilename();
                if (in_array($themeName, $skipList) || preg_match("/[^a-z0-9_]/i", $themeName)) {
                    continue;
                }
                $configFile = $fileinfo->getPathname() . "/info.php";
                if (!file_exists($configFile)) {
                    continue;
                }
                $themeInfo = include $configFile;
                if (!empty($themeInfo["disable"])) continue;
                if (empty($themeInfo["name"])) {
                    $themeInfo["name"] = $themeName;
                }
                if (empty($themeInfo["screenshot"])) {
                    $themeInfo["screenshot"] = 'img/images/theme.png';
                } else {
                    $themeInfo["screenshot"] = $themePath . '/' . $themeName . '/' . $themeInfo["screenshot"];
                }
                $themes[$themeName] = $themeInfo;
            }
            return $themes;
        }

        $model = \Xoops::getModel("theme");
        $select = $model->select();
        if ($type == 'active') {
            $select->where('active = ?', 1);
        } elseif ($type == 'inactive') {
            $select->where('active = ?', 0);
        }
        $select->order(array("parent", "order"));
        $rowset = $model->fetchAll($select);
        foreach ($rowset as $row) {
            $dirname = $row->dirname;
            $themes[$dirname] = $row->toArray();
            if (empty($themes[$dirname]["screenshot"])) {
                $themes[$dirname]["screenshot"] = 'img/images/theme.png';
            } else {
                $themes[$dirname]["screenshot"] = 'theme/' . $dirname . '/' . $themes[$dirname]["screenshot"];
            }
        }

        return $themes;
    }

    protected function loadData(&$options = array())
    {
        if (isset($options['type']) && ($options['type'] == 'install' || $options['type'] == 'deploy')) {
            return $this->loadDynamic($options);
        }
        if (false === ($data = $this->loadCache($options))) {
            $data = $this->loadDynamic($options);
            $this->saveCache($data, $options);
        }
        return $data;
    }

    public function read($type = 'installed')
    {
        $options = compact('type');
        return $this->loadData($options);
    }

    public function create($type = 'installed')
    {
        self::delete($type);
        self::read($type);
        return true;
    }

    public function delete($type = null)
    {
        $options = compact('type');
        return $this->cache->clean('matchingTag', self::createTags($options));
    }

    public function flush($type = null)
    {
        return self::delete($type);
    }
}
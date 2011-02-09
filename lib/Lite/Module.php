<?php
/**
 * Lite module handler
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
 * @package         Xoops_Core
 * @version         $Id$
 */

class Lite_Module extends \Kernel\Module
{
    /**
     * Load model data
     *
     * @return
     */
    public function loadModel()
    {
        $this->model = new stdClass;
        $this->model->dirname = $this->dirname;
        $this->model->active = 1;

        return $this->model;
    }

    /**
     * Read config data from storage
     */
    public function readConfig($category)
    {
        $persistKey = "config.module." . $this->dirname;
        $configs = XOOPS::persist()->load($persistKey);
        if (!is_array($confgis)) {
            $configFile = XOOPS::path("var") . "/etc/module." . $this->dirname . ".ini";
            if (file_exists($configFile)) {
                $configs = parse_ini_file($configFile, true);
            } else {
                $configs = array();
            }
            XOOPS::persist()->save($configs, $persistKey);
        }
        $category = $category ?: "general";
        return isset($configs[$category]) ? $configs[$category] : array();
    }
}
<?php
/**
 * System admin theme controller
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
 * @package         System
 * @version         $Id$
 */

/**
 * A complete theme set should include following files:
 *
 * Folder and file skeleton:
 * REQUIRED:
 *  layout.html - complete layout template: header, footer, body, blocks, navigation
 *  simple.html - simplified layout: header, footer, body
 *  empty.html - empty layout with body only
 *  admin.html - backoffice layout
 *  paginator.html - Paginator template
 * OPTIONAL:
 *  navigation.html - generic navigation template, referenced by layout.html
 *
 * Stylesheet files:
 * REQUIRED:
 *  style.css - main css file
 *  form.css - generic form css file
 * OPTIONAL:
 *  default/scripts/redirect.css - css file for redirecting page
 *  default/scripts/exception.css - css file for error pages
 *  default/images/loading_indicator.jpg - Indicator image for redirecting page
 */

/**
 * Theme installer was suggested by zhuzhenghui and ezsky during a gathering at Cava Cafe.
 * I am trying to build demo-like installer based on module installer and hope other developers will complete it.
 */
class System_ThemeController extends Xoops_Zend_Controller_Action_Admin
{
    public function init()
    {
        $this->skipCache();
    }

    public function indexAction()
    {
        $themes = XOOPS::service('registry')->themelist->read('active');
        foreach ($themes as $key => &$theme) {
            $theme["upgrade"] = $this->checkUpgrade($key, $theme["version"]);
        }
        $this->template->assign("themes_active", $themes);
        $themes = XOOPS::service('registry')->themelist->read('inactive');
        foreach ($themes as $key => &$theme) {
            $theme["upgrade"] = $this->checkUpgrade($key, $theme["version"]);
        }
        $this->template->assign("themes_inactive", $themes);
        $this->setTemplate("theme_list.html");
    }

    public function availableAction()
    {
        $themes = XOOPS::service('registry')->themelist->read('install');
        foreach ($themes as $key => &$theme) {
            $theme["upgrade"] = $this->checkUpgrade($key, $theme["version"]);
        }
        $this->template->assign("themes_install", $themes);

        $iterator = new DirectoryIterator(XOOPS::path("usr/themes"));
        $pathThemes = XOOPS::path("theme");
        $themes = array();
        foreach ($iterator as $fileinfo) {
            if (!$fileinfo->isDir()) {
                continue;
            }
            $name = $fileinfo->getFilename();
            if (file_exists($pathThemes . "/" . $name)) {
                continue;
            }
            $configFile = $fileinfo->getPathname() . "/info.php";
            if (!file_exists($configFile)) {
                continue;
            }
            $themes[$name] = include $configFile;
            //$themes[$name]["screenshot"] = Xoops::url('usr/themes') . '/' . $key . '/' . $themes[$name]["screenshot"];
        }
        $this->template->assign("themes_deploy", $themes);

        $this->setTemplate("theme_available.html");
    }

    public function updateAction()
    {
        $dirname = $this->getRequest()->getParam("dirname");
        $model = XOOPS::getModel("theme");
        $select = $model->select()->where("dirname = ?", $dirname);
        $row = $model->fetchRow($select);

        $configFile = XOOPS::path("theme/" . $dirname . "/info.php");
        $themes = XOOPS::service('registry')->themelist->read('installed');
        $data = isset($themes[$dirname]) ? $themes[$dirname] : null;
        if (!file_exists($configFile)) {
            $message = sprintf(XOOPS::_("The theme '%s' is not found."), $dirname);
        } else {
            $data = include $configFile;
            if (version_compare($row->version, $data["version"])) {
                $data["update"] = time();
                $columns = $model->info("cols");
                foreach ($columns as $col) {
                    if (isset($data[$col]) && $data[$col] != $row->$col) {
                        $row->$col = $data[$col];
                    }
                }
                $row->save();
            }
            $message = sprintf(XOOPS::_("The theme '%s' is updated."), $dirname);
        }

        XOOPS::service('registry')->theme->flush();
        XOOPS::service('registry')->themelist->flush();

        XOOPS::registry('view')->getEngine()->clearTemplate('', $dirname);
        XOOPS::registry('view')->getEngine()->clearCache('', $dirname);
        $url = array("action" => "index", "route" => "admin", "reset" => true);
        $options = array("time" => 5, "message" => $message);
        $this->redirect($url, $options);
    }

    public function upgradeAction()
    {
        $dirname = $this->getRequest()->getParam("dirname");

        if (!$status = $this->upgradeTheme($dirname)) {
            $message = XOOPS::_("Theme files are not copied correctly, please copy theme files manually and try again.");
        } else {
            $message = XOOPS::_("Theme files are copied correctly.");
        }
        XOOPS::service('registry')->themelist->flush();

        $url = array("action" => "index", "route" => "admin", "reset" => true);
        $options = array("time" => 5, "message" => $message);
        $this->redirect($url, $options);
    }

    public function synchronizeAction()
    {
        $dirname = $this->getRequest()->getParam("dirname");

        $stats = array();
        if (!$status = $this->synchronizeTheme($dirname, $stats)) {
            $message = XOOPS::_("Theme files are not synchronized correctly, please copy theme files manually and try again.");
        } else {
            $message = XOOPS::_("Theme files are synchronized correctly.");
        }
        XOOPS::service('registry')->themelist->flush();

        $url = array("action" => "index", "route" => "admin", "reset" => true);
        $options = array("time" => 5, "message" => $message);
        $this->redirect($url, $options);
    }

    public function activateAction()
    {
        $dirname = $this->getRequest()->getParam("dirname");

        $model = XOOPS::getModel("theme");
        $model->update(array("active" => 1), array("dirname = ?" => $dirname));
        XOOPS::service('registry')->theme->flush();
        XOOPS::service('registry')->themelist->flush();

        $url = array("action" => "index", "route" => "admin", "reset" => true);
        $options = array("time" => 5, "message" => XOOPS::_("The theme '{$dirname}' is activated."));
        $this->redirect($url, $options);
    }

    public function deactivateAction()
    {
        $dirname = $this->getRequest()->getParam("dirname");
        if ("default" == $dirname) {
            $message = XOOPS::_("The theme 'default' is protected.");
        } else {
            $model = XOOPS::getModel("theme");
            $model->update(array("active" => 0), array("dirname = ?" => $dirname));
            $message = sprintf(XOOPS::_("The theme '%s' is deactivated."), $dirname);
            XOOPS::service('registry')->theme->flush();
            XOOPS::service('registry')->themelist->flush();
        }

        XOOPS::registry('view')->getEngine()->clearTemplate('', $dirname);
        XOOPS::registry('view')->getEngine()->clearCache('', $dirname);

        $url = array("action" => "index", "route" => "admin", "reset" => true);
        $options = array("time" => 5, "message" => $message);
        $this->redirect($url, $options);
    }

    public function installAction()
    {
        $dirname = $this->getRequest()->getParam("dirname");
        $themes = XOOPS::service('registry')->themelist->read('install');
        $data = isset($themes[$dirname]) ? $themes[$dirname] : null;
        if (empty($data)) {
            $message = sprintf(XOOPS::_("The theme '%s' is not found."), $dirname);
        } elseif (empty($data["parent"]) && $files = $this->checkFiles($dirname)) {
            $message = XOOPS::_("Files missing: ") . implode(" ", $files);
        } else {
            $data["dirname"] = $dirname;
            $data["active"] = 1;
            $data["update"] = time();
            $model = XOOPS::getModel("theme");
            $columns = $model->info("cols");
            foreach ($data as $col => $val) {
                if (!in_array($col, $columns)) {
                    unset($data[$col]);
                }
            }

            $model->insert($data);
            $message = sprintf(XOOPS::_("The theme '%s' is installed."), $dirname);
            XOOPS::service('registry')->theme->flush();
            XOOPS::service('registry')->themelist->flush();
        }

        $options = array("time" => 5, "message" => $message);
        $url = array("action" => "index", "route" => "admin", "reset" => true);
        $this->redirect($url, $options);
    }

    public function uninstallAction()
    {
        $dirname = $this->getRequest()->getParam("dirname");
        if ("default" == $dirname) {
            $message = XOOPS::_("The theme 'default' is protected.");
        } else {
            $model = XOOPS::getModel("theme");
            $model->delete(array("dirname = ?" => $dirname));
            $message = sprintf(XOOPS::_("The theme '$s' is uninstalled."), $dirname);
            XOOPS::service('registry')->theme->flush();
            XOOPS::service('registry')->themelist->flush();
        }

        XOOPS::registry('view')->getEngine()->clearTemplate('', $dirname);
        XOOPS::registry('view')->getEngine()->clearCache('', $dirname);

        $url = array("action" => "index", "route" => "admin", "reset" => true);
        $options = array("time" => 5, "message" => $message);
        $this->redirect($url, $options);
    }

    public function downloadAction()
    {
        $description = XOOPS::_("You can find themes from below links:");
        $list = array(
            "http://directory.xoopsengine.org/" => XOOPS::_("Xoops Engine Repository"),
            "http://support.xoopsengine.org"    => XOOPS::_("Xoops Engine Supports"),
            "http://dev.xoopsengine.org"        => XOOPS::_("Xoops Engine Extensions Development Forge")
        );
        $this->template->assign("description", $description);
        $this->template->assign("list", $list);
    }

    public function readmeAction()
    {
        $title = XOOPS::_("A theme controls the layout in XOOPS system");
        $this->template->assign("title", $title);
    }

    public function __call($method, $args)
    {
        $this->forward("index");
    }

    protected function checkFiles($theme)
    {
        $fileList = array(
            "layout.html",      // complete layout template: header, footer, body, blocks, navigation
            "simple.html",      // simplified layout: header, footer, body
            "empty.html",       // empty layout with body only
            "admin.html",       // backoffice layout
            "paginator.html",   // Paginator template
            "comment.html",     // Comment template
            "notification.html",// Notification form template
            "style.css",        // main css file
            "form.css",         // generic form css file
        );
        $path = XOOPS::path("theme/" . $theme);

        $missingFiles = array();
        foreach ($fileList as $file) {
            if (!file_exists($path . "/" . $file)) {
                $missingFiles[] = $file;
            }
        }

        return $missingFiles;
    }

    protected function checkUpgrade($theme, $version)
    {
        $status = false;
        $configFile = XOOPS::path("usr") . "/themes/" . $theme . "/info.php";
        if (file_exists($configFile)) {
            $config = include $configFile;
            $status = version_compare($version, $config["version"]);
        }
        return $status;
    }

    protected function upgradeTheme($theme)
    {
        $themes = XOOPS::service('registry')->themelist->read('install');
        $themes = array_merge($themes, XOOPS::service('registry')->themelist->read('installed'));
        $list = array();
        $this->getChildren($theme, $themes, $list);
        $themeFailed = array();
        array_unshift($list, $theme);
        $stats = array();
        foreach ($list as $key) {
            if (!$this->synchronizeTheme($key, $stats)) {
                $themeFailed[] = $key;
            }
        }

        return empty($themeFailed) ? true : false;
    }

    protected function synchronizeTheme($theme, &$stats = array())
    {
        $source = $theme;
        $target = $theme;
        $stats = $this->copyFolder($source, $target);
        $config = include $source . "/info.php";
        if (!empty($config["parent"])) {
            $statsParent = $this->copyFolder($config["parent"], $target, $source);
            $stats["files"]["copied"] += $statsParent["files"]["copied"];
            $stats["files"]["failed"] += $statsParent["files"]["copied"];
            $stats["folders"]["created"] += $statsParent["files"]["copied"];
            $stats["folders"]["failed"] += $statsParent["files"]["copied"];
        }
        $status = (0 == $stats["files"]["failed"] + $stats["folders"]["failed"]) ? true : false;
        return $status;
    }

    protected function getChildren($theme, $list, &$children)
    {
        foreach ($list as $key => $data) {
            if ($data["parent"] == $theme) {
                $children[] = $key;
                $this->getChildren($key, $list, $children);
            }
        }
    }

    protected function copyFolder($sourceTheme, $targetTheme, $exclude = null)
    {
        $files = array(
            "copied"    => 0,
            "failed"    => 0,
        );
        $folders  = array(
            "created"   => 0,
            "failed"    => 0,
        );

        $source = XOOPS::path("usr") . "/themes/" . $sourceTheme;
        if (!is_dir($source)) {
            $source = XOOPS::path("theme") . "/" . $sourceTheme;
        }
        $target = XOOPS::path("theme") . "/" . $targetTheme;
        if (strcmp($source, $target)) {
            if (!is_dir($target)) {
                mkdir($target, 0777);
            }
            $directory  = new RecursiveDirectoryIterator($source);
            $iterator   = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::SELF_FIRST);
            foreach ($iterator as $filename => $fileinfo) {
                $subPath = substr($filename, strlen($source));
                $targetFile = $target . $subPath;
                if ($fileinfo->isFile()) {
                    if (!is_null($exclude) && file_exists($exclude . $subPath)) {
                        continue;
                    }
                    if (file_exists($targetFile)) {
                        $targetHash = md5(file_get_contents($targetFile));
                        $sourceHash = md5(file_get_contents($filename));
                        if ($targetHash == $sourceHash) {
                            continue;
                        }
                        chmod($targetFile, 0777);
                    }
                    if (copy($filename, $targetFile)) {
                        $files["copied"]++;
                    } else {
                        $files["failed"]++;
                    }
                } elseif ($fileinfo->isDir()) {
                    if (!is_dir($targetFile)) {
                        if (mkdir($targetFile, 0777)) {
                            $folders["created"]++;
                        } else {
                            $folders["failed"]++;
                        }
                    } else {
                        chmod($targetFile, 0777);
                    }
                }
            }

            $directory  = new RecursiveDirectoryIterator($target);
            $iterator   = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::SELF_FIRST);
            foreach ($iterator as $filename => $fileinfo) {
                if ($fileinfo->isFile()) {
                    chmod($filename, 0666);
                } elseif ($fileinfo->isDir()) {
                    chmod($filename, 0755);
                }
            }
        }

        return array("files" => $files, "folders" => $folders);
    }
}
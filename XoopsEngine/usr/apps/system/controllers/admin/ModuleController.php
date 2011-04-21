<?php
/**
 * System admin module controller
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
 * @category        Xoops_Module
 * @package         System
 * @version         $Id$
 */

// TODO: move to preference
define("DISABLE_REMOTE_VERIFY", 1);

class System_ModuleController extends Xoops_Zend_Controller_Action_Admin
{
    private $restClient;
    private static $xoopsEngineUri = "http://api.xoopsengine.org/app/module";

    public function init()
    {
        $this->skipCache();
    }

    public function indexAction()
    {
        $modules = XOOPS::service('registry')->modulelist->read('active');

        foreach ($modules as $dirname => &$moduleData) {
            $this->remoteVerify($dirname, $moduleData);
        }

        if (!empty($modules)) {
            $this->view->headLink(array(
                "href"  => "form.css",
                "rel"   => "stylesheet",
                "type"  => "text/css"
            ));
            $options = array(
                "name"      => "modules_active",
                "action"    => $this->view->url(array("action" => "save", "controller" => "module")),
            );
            $form = new Xoops_Zend_Form($options);

            $options = array(
                "border"    => "2",
                "frame"     => "hsides",
                "rules"     => "groups",
            );
            $table = new Xoops_Table($options);

            $options = array(
                "valign"    => "top",
                // TRs
                "elements"  => array(
                    // TR
                    array(
                        // TDs
                        "options"   => array(
                            "elements"  => array(
                                // TD
                                "Logo",
                                "Name",
                                "New name",
                                "Version",
                                "Status",
                                "Synchronize",
                                "Update",
                                "Deactivate",
                                "Clone",
                            ),
                        ),
                    ),
                ),
            );
            $table->addThead($options);

            foreach ($modules as $dirname => $data) {
                $options = array(
                    "elements" => array(
                        $this->view->htmlImage($data["logo"], $data["name"], array("width" => "120px")),
                        $data["name"],
                        $form->createElement("text", $dirname . "_name", array("value" => $data["name"])),
                        $this->view->text(
                            $data["version"] . (
                                empty($data["upgrade"]) ? "" : "<br /><a href='" . $data["download"] . "' rel='external' title='" . XOOPS::_("Upgrade") . "'>" . XOOPS::_("Upgrade") . "</a>"
                            )
                        ),
                        $this->view->text(
                            (empty($data["parent"]) ? "" : $data["parent"] . "<br />") . $data["status"]
                        ),
                        $this->view->htmlLink(
                            $this->view->url(array("action" => "synchronize", "controller" => "module", "dirname" => $dirname)),
                            XOOPS::_("Synchronize"),
                            XOOPS::_("Synchronize")
                        ),
                        $this->view->htmlLink(
                            $this->view->url(array("action" => "update", "controller" => "module", "dirname" => $dirname)),
                            XOOPS::_("Update"),
                            XOOPS::_("Update")
                        ),
                        $this->view->htmlLink(
                            $this->view->url(array("action" => "deactivate", "controller" => "module", "dirname" => $dirname)),
                            XOOPS::_("Deactivate"),
                            XOOPS::_("Deactivate")
                        ),
                        (!empty($data['parent']) || $data['type'] != 'app') ? "" :
                        $this->view->htmlLink(
                            $this->view->url(array("action" => "clone", "controller" => "module", "parent" => $dirname)),
                            XOOPS::_("Clone"),
                            XOOPS::_("Clone")
                        ),
                    ),
                );
                $table->addRow($options);
            }

            $form->addElement("table", "table", array("table" => $table));

            $options = array(
                "label"     => "Confirm",
                "required"  => false,
                "ignore"    => true,
            );
            $form->addElement("submit", "save", $options);
            $form->assign($this->view);
        }

        //$modules = XOOPS::service('registry')->modulelist->flush();
        $modules = XOOPS::service('registry')->modulelist->read('inactive');
        foreach ($modules as $dirname => &$data) {
            $this->remoteVerify($dirname, $data);
        }
        $this->template->assign("modules_inactive", $modules);

        $this->setTemplate("module_list.html");
    }

    public function saveAction()
    {
        $dirname = $this->getRequest()->getParam("dirname");
        $modules = XOOPS::service('registry')->modulelist->read('active');
        $posts = $this->getRequest()->getPost();

        $model = XOOPS::getModel("module");
        foreach ($modules as $dirname => $moduleData) {
            if (!empty($posts[$dirname . "_name"]) && strcmp($posts[$dirname . "_name"], $moduleData["name"])) {
                $model->update(array("name" => $posts[$dirname . "_name"]), array("id = ?" => $moduleData["id"]));
            }
        }

        $modules = XOOPS::service('registry')->modulelist->flush();
        $modules = XOOPS::service('registry')->module->flush();
        $url = array("action" => "index", "route" => "admin", "reset" => true);
        $options = array("time" => 3, "message" => XOOPS::_("Module names are updated."));
        $this->redirect($url, $options);
    }

    public function availableAction()
    {
        XOOPS::service('registry')->modulelist->flush();
        $modules = XOOPS::service('registry')->modulelist->read('install');
        foreach ($modules as $dirname => &$data) {
            $this->remoteVerify($dirname, $data);
        }
        $this->template->assign("modules_install", $modules);

        $this->setTemplate("module_available.html");
    }

    public function updateAction()
    {
        $dirname = $this->getRequest()->getParam("dirname");
        $ret = Xoops_Installer::instance()->update($dirname);
        $message = '';
        if (!$ret) {
            $message = Xoops_Installer::instance()->getMessage() ?: XOOPS::_("The module '{$dirname}' is not updated.");
        } else {
            $parent = Xoops::service('module')->getDirectory($dirname);
            $stats = array();
            if (!$status = $this->synchronizeResource($parent, $dirname, $stats)) {
                $message = XOOPS::_("Resource files are not synchronized correctly, please copy resource files manually and try again.");
            }
        }
        $message = $message ?: XOOPS::_("The module '{$dirname}' is updated.");

        //XOOPS::service("event")->trigger("module_update", $dirname);
        $url = array(/*"controller" => "module", */"action" => "index", "route" => "admin", "reset" => true);
        $options = array("time" => 5, "message" => $message);
        $this->redirect($url, $options);
    }

    public function activateAction()
    {
        $dirname = $this->getRequest()->getParam("dirname");
        $ret = Xoops_Installer::instance()->activate($dirname);

        $parent = Xoops::service('module')->getDirectory($dirname);
        $stats = array();
        if (!$status = $this->synchronizeResource($parent, $dirname, $stats)) {
            $message = XOOPS::_("Resource files are not synchronized correctly, please copy resource files manually and try again.");
        } else {
            $message = XOOPS::_("Resource files are synchronized correctly.");
        }

        $url = array("action" => "index", "route" => "admin", "reset" => true);
        $options = array("time" => 5, "message" => $message);
        $this->redirect($url, $options);
    }

    public function deactivateAction()
    {
        $dirname = $this->getRequest()->getParam("dirname");
        $ret = Xoops_Installer::instance()->deactivate($dirname);

        $message = '';
        if (!$ret) {
            $message = Xoops_Installer::instance()->getMessage() ?: XOOPS::_("The module '{$dirname}' is not deactivated.");
        }
        $message = $message ?: XOOPS::_("The module '{$dirname}' is deactivated.");

        $url = array("action" => "index", "route" => "admin", "reset" => true);
        $options = array("time" => 5, "message" => $message);
        $this->redirect($url, $options);
    }

    public function installAction()
    {
        $dirname = $this->getRequest()->getParam("dirname");
        $ret = Xoops_Installer::instance()->install($dirname);
        $message = '';
        if (!$ret) {
            $message = Xoops_Installer::instance()->getMessage() ?: XOOPS::_("The module '{$dirname}' is not installed.");
        } else {
            $stats = array();
            if (!$status = $this->synchronizeResource($dirname, $dirname, $stats)) {
                $message = XOOPS::_("Resource files are not synchronized correctly, please copy resource files manually and try again.");
            }
        }
        $message = $message ?: XOOPS::_("The module '{$dirname}' is installed.");

        //XOOPS::service("event")->trigger("module_install", $dirname);
        $url = array("action" => "index", "route" => "admin", "reset" => true);
        $options = array("time" => 5, "message" => $message);
        $this->redirect($url, $options);
    }

    public function uninstallAction()
    {
        $dirname = $this->getRequest()->getParam("dirname");
        $ret = Xoops_Installer::instance()->uninstall($dirname);

        $message = '';
        if (!$ret) {
            $message = Xoops_Installer::instance()->getMessage() ?: XOOPS::_("The module '{$dirname}' is not uninstalled.");
        }
        $message = $message ?: XOOPS::_("The module '{$dirname}' is uninstalled.");

        $url = array("action" => "index", "route" => "admin", "reset" => true);
        $options = array("time" => 5, "message" => $message);
        $this->redirect($url, $options);
    }

    public function cloneAction()
    {
        $form = $this->getForm();
        if ($this->getRequest()->isPost()) {
            $posts = $this->getRequest()->getPost();
            if ($form->isValid($posts)) {

                $values = $form->getValues();
                $dirname = $values["parent"];
                $ret = Xoops_Installer::instance()->install($dirname, $values);

                $message = '';
                if (!$ret) {
                    $message = Xoops_Installer::instance()->getMessage() ?: XOOPS::_("The module '{$dirname}' is not cloned.");
                } else {
                    $stats = array();
                    if (!$status = $this->synchronizeResource($dirname, $values["dirname"], $stats)) {
                        $message = XOOPS::_("Resource files are not synchronized correctly, please copy resource files manually and try again.");
                    }
                }
                $message = $message ?: XOOPS::_("The module '{$dirname}' is cloned.");

                $url = array("action" => "index", "route" => "admin", "reset" => true);
                $options = array("time" => 5, "message" => $message);
                $this->redirect($url, $options);
                return;
            }
        }
        $form->assign($this->view);
        $this->setTemplate("module_clone.html");
    }

    public function synchronizeAction()
    {
        $dirname = $this->getRequest()->getParam("dirname");

        $stats = array();
        if (!$status = $this->synchronizeResource($dirname, $dirname, $stats)) {
            $message = XOOPS::_("Resource files are not synchronized correctly, please copy resource files manually and try again.");
        } else {
            $message = XOOPS::_("Resource files are synchronized correctly.");
        }

        $url = array("action" => "index", "route" => "admin", "reset" => true);
        $options = array("time" => 5, "message" => $message);
        $this->redirect($url, $options);
    }

    public function downloadAction()
    {
        $description = XOOPS::_("You can find modules from below links:");
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
        if ($this->getRequest()->isPost()) {
        }
        $title = XOOPS::_("A module is a fully-functioning element in XOOPS system");
        $this->template->assign("title", $title);
    }

    public function ____call($method, $args)
    {
        $this->forward("index");
    }

    protected function getForm()
    {
        $parent = $this->getRequest()->getParam("parent");

        $this->view->headLink(array(
            "href"  => "form.css",
            "rel"   => "stylesheet",
            "type"  => "text/css"
        ));
        $options = array(
            "name"      => "moduleClone",
            "action"    => $this->view->url(array("action" => "clone", "controller" => "module")),
        );
        $form = new Xoops_Zend_Form($options);

        $options = array(
            "label"         => "Module name",
            "required"      => true,
            "filters"       => array(
                "trim"      => array(
                    "filter"    => "StringTrim",
                ),
            ),
        );
        $form->addElement("text", "name", $options);

        $options = array(
            "label"         => "Module key (dirname)",
            "required"      => true,
            "prefixPath"    => array(
                "validate"  => array(
                    //"prefix"    => "App_System_Validate",
                    "prefix"    => "System_Validate",
                    "path"      => Xoops::service('module')->getPath("system") . "/Validate",
                ),
            ),
            "filters"       => array(
                "trim"      => array(
                    "filter"    => "StringTrim",
                ),
            ),
            "validators"    => array(
                "duplicate"  => array(
                    "validator" => "ModuleDuplicate",
                ),
            ),
        );
        $form->addElement("text", "dirname", $options);

        $form->addElement("hidden", "parent", array("value" => $parent));

        $options = array(
            "label"     => "Confirm",
            "required"  => false,
            "ignore"    => true,
        );
        $form->addElement("submit", "save", $options);

        return $form;
    }

    /**
     * Fetch module information from Xoops Engine
     *
     * @param string    $dirname Module dirname
     * @param string    $email Author email
     * @return array    associative array:
     *                      status:  potential values: 0 - third-party; 1 - official; 2 - registered
     *                      version: latest version
     *                      download: download URL
     */
    protected function fetchModule($dirname, $email = "")
    {
        if (!isset($this->restClient)) {
            $this->restClient = new Xoops_Zend_Rest_Client(self::$xoopsEngineUri);
        }
        $this->restClient->dirname($dirname);
        /*
        if (!empty($appkey)) {
            $this->restClient->appkey($appkey);
        } else {
            $this->restClient->appkey("Invalid");
        }
        */
        $restResult = array();
        $restResponse = $this->restClient->get();
        if ($restResponse->isSuccess()) {
            $restEmail = $restResponse->email();
            if (!$restEmail || $restEmail == $email) {
                $restResult = array(
                    "status"    => $restResponse->status(),
                    "version"   => $restResponse->version(),
                    "download"  => $restResponse->download()
                );
            }
        }
        return $restResult;
    }

    protected function remoteVerify($dirname, &$data)
    {
        if (defined("DISABLE_REMOTE_VERIFY") && DISABLE_REMOTE_VERIFY > 0) {
            $result = array();
        } else {
            $result = $this->fetchModule($dirname, $data["email"]);
        }
        $data["status"] = XOOPS::_("Unknown");
        $data["upgrade"] = 0;
        $data["download"] = self::$xoopsEngineUri;
        if (!empty($result)) {
            $data["upgrade"] = version_compare($result["version"], $data["version"], "gt");
            $data["download"] = !empty($result["download"]) ? $result["download"] : $data["download"];
            switch ($result["status"]) {
                case 0:
                    $data["status"] = XOOPS::_("Third-party");
                    break;
                case 1:
                    $data["status"] = XOOPS::_("Official");
                    break;
                case 2:
                    $data["status"] = XOOPS::_("Registered");
                    break;
                default:
                    $data["status"] = XOOPS::_("Unknown");
                    break;
            }
        }
    }

    protected function synchronizeResource($source, $target, &$stats = array())
    {
        $stats = $this->copyFolder($source, $target);
        $status = (0 == $stats["files"]["failed"] + $stats["folders"]["failed"]) ? true : false;
        return $status;
    }

    protected function copyFolder($sourceFolder, $targetFolder)
    {
        $files = array(
            "copied"    => 0,
            "failed"    => 0,
        );
        $folders  = array(
            "created"   => 0,
            "failed"    => 0,
        );

        $source = XOOPS::path("app") . "/" . $sourceFolder;
        $target = XOOPS::path("www") . "/usr/apps/" . $targetFolder;
        if (!is_dir($source)) {
            return false;
        }
        if (!is_dir($target)) {
            if (!mkdir($target, 0777, true)) {
                return false;
            }
        }
        $subfolders = array("resources");
        if (strcmp($sourceFolder, $targetFolder)) {
            //$subfolders[] = "templates";
        }

        foreach ($subfolders as $folder) {
            if (!is_dir($source . "/" . $folder)) {
                continue;
            }
            if (!is_dir($target . "/" . $folder)) {
                mkdir($target . "/" . $folder, 0777);
            }
            $directory  = new RecursiveDirectoryIterator($source . "/" . $folder);
            $iterator   = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::SELF_FIRST);
            foreach ($iterator as $filename => $fileinfo) {
                $subPath = substr($filename, strlen($source));
                $targetFile = $target . $subPath;
                if ($fileinfo->isFile()) {
                    if (file_exists($targetFile)) {
                        $targetHash = md5(file_get_contents($targetFile));
                        $sourceHash = md5(file_get_contents($filename));
                        if ($targetHash == $sourceHash) {
                            continue;
                        }
                        if (!is_writable($targetFile)) {
                            @chmod($targetFile, 0777);
                        }
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
                        if (!is_writable($targetFile)) {
                            @chmod($targetFile, 0777);
                        }
                    }
                }
            }
        }
        $directory  = new RecursiveDirectoryIterator($target);
        $iterator   = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $filename => $fileinfo) {
            if (!$fileinfo->isWritable()) {
                continue;
            }
            if ($fileinfo->isFile()) {
                @chmod($filename, 0644);
            } elseif ($fileinfo->isDir()) {
                @chmod($filename, 0755);
            }
        }

        return array("files" => $files, "folders" => $folders);
    }
}
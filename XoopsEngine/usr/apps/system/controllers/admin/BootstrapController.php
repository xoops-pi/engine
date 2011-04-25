<?php
/**
 * System admin bootstrap controller
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

class System_BootstrapController extends Xoops_Zend_Controller_Action_Admin
{
    const FORM_NAME = "xoopsBootstrap";

    // permission check, only root users are allowed
    public function  preDispatch()
    {
        // root user
        if (XOOPS::registry("user")->id == 0) {
            return;
        }

        $urlOptions = array("action" => "index", "controller" => "index", "route" => "admin");
        $message = XOOPS::_("You are not allowed to access this area.");
        $options = array("time" => 3, "message" => $message);
        $this->redirect($urlOptions, $options);
    }

    // Bootstrap file list
    public function indexAction()
    {
        $module = $this->getRequest()->getModuleName();
        $category = $this->getRequest()->getParam("category");

        $files = array("general" => array(
                ".htaccess" => "htaccess",
                "boot.php"  => "bootfile",
            ),
        );
        $iterator = new DirectoryIterator(XOOPS::path("lib/boot"));
        $pattern = "/^(engine|hosts)[\.](.*)" . preg_quote(".ini.php") . "$/";
        foreach ($iterator as $fileinfo) {
            if (!$fileinfo->isFile()) {
                continue;
            }
            $fileName = $fileinfo->getFilename();
            if (!preg_match($pattern, $fileName, $matches)) {
                continue;
            }
            $type = "boot";
            $files[$type][$fileName] = $matches[1] . '.' . $matches[2];
        }
        $iterator = new DirectoryIterator(XOOPS::path("var/etc"));
        $pattern = "/^((bootstrap|service|resource|registry)[\.])?([^-]+)(.*)" . preg_quote(".ini.php") . "$/";
        foreach ($iterator as $fileinfo) {
            if (!$fileinfo->isFile()) {
                continue;
            }
            $fileName = $fileinfo->getFilename();
            if (!preg_match($pattern, $fileName, $matches)) {
                continue;
            }
            $type = empty($matches[2]) ? "misc" : $matches[2];
            $files[$type][$fileName] = $matches[3];
        }
        $list = array(
            'general'       => array(),
            'boot'          => array(),
            'bootstrap'     => array(),
            'service'       => array(),
            'resource'      => array(),
            'registry'      => array(),
            'misc'          => array(),
        );
        if (!empty($category)) {
            $list = array($category => array());
        }
        foreach ($list as $key => $val) {
            if (empty($files[$key])) {
                unset($list[$key]);
                continue;
            }

            foreach ($files[$key] as $file => $item) {
                $params = array(
                    "module"        => $module,
                    "controller"    => "bootstrap",
                    "action"        => $key,
                );
                if ($key == "general") {
                    $params["action"] = $item;
                } else {
                    $params["item"] = $item;
                }
                $list[$key][$file] = $this->getFrontController()->getRouter()->assemble(
                    $params,
                    "admin"
                );
            }

        }

        $title = XOOPS::_("Bootstrap Configurations");
        $message = XOOPS::_("We advise you to be careful with this section. Please don't change the files if you are not an experienced user.");
        $this->template->assign("bootstraps", $list);
        $this->template->assign("title", $title);
        $this->template->assign("message", $message);
    }

    // .htaccess management
    public function htaccessAction()
    {
        $module = $this->getRequest()->getModuleName();
        $file = XOOPS::path("www/.htaccess");
        $action = $this->getFrontController()->getRouter()->assemble(
            array(
                "module"        => $module,
                "controller"    => "bootstrap",
                "action"        => "htaccess"
            ),
            "admin"
        );
        $form = $this->getForm($file, $action);

        if (!$this->getRequest()->isPost()) {
            return $this->renderForm($form);
        }

        return $this->process($form, $file);
    }

    // boot.php management
    public function bootfileAction()
    {
        $module = $this->getRequest()->getModuleName();
        $file = XOOPS::path("www/boot.php");
        $action = $this->getFrontController()->getRouter()->assemble(
            array(
                "module"        => $module,
                "controller"    => "bootstrap",
                "action"        => "bootfile"
            ),
            "admin"
        );
        $form = $this->getForm($file, $action);

        if (!$this->getRequest()->isPost()) {
            return $this->renderForm($form);
        }

        return $this->process($form, $file);
    }

    // boot settings management
    public function bootAction()
    {
        $module = $this->getRequest()->getModuleName();
        $item = $this->getRequest()->getParam("item");
        if (empty($item)) {
            $this->forward("index", null, null, array("category" => "boot"));
            return;
        }
        $file = XOOPS::path("lib") . "/boot/" . $item . ".ini.php";
        $action = $this->getFrontController()->getRouter()->assemble(
            array(
                "module"        => $module,
                "controller"    => "bootstrap",
                "action"        => "boot",
                "item"          => $item,
            ),
            "admin"
        );
        $form = $this->getForm($file, $action);

        if (!$this->getRequest()->isPost()) {
            return $this->renderForm($form);
        }

        return $this->process($form, $file);
    }

    // application bootstrap management
    public function bootstrapAction()
    {
        $module = $this->getRequest()->getModuleName();
        $item = $this->getRequest()->getParam("item");
        if (empty($item)) {
            $this->forward("index", null, null, array("category" => "bootstrap"));
            return;
        }
        $file = XOOPS::path("var/etc/bootstrap.{$item}.ini.php");
        $action = $this->getFrontController()->getRouter()->assemble(
            array(
                "module"        => $module,
                "controller"    => "bootstrap",
                "action"        => "bootstrap",
                "item"          => $item,
            ),
            "admin"
        );
        $form = $this->getForm($file, $action);

        if (!$this->getRequest()->isPost()) {
            return $this->renderForm($form);
        }

        return $this->process($form, $file);
    }

    // service bootstrap management
    public function serviceAction()
    {
        $module = $this->getRequest()->getModuleName();
        $item = $this->getRequest()->getParam("item");
        if (empty($item)) {
            $this->forward("index", null, null, array("category" => "service"));
            return;
        }
        $file = XOOPS::path("var/etc/service.{$item}.ini.php");
        $action = $this->getFrontController()->getRouter()->assemble(
            array(
                "module"        => $module,
                "controller"    => "bootstrap",
                "action"        => "service",
                "item"          => $item,
            ),
            "admin"
        );
        $form = $this->getForm($file, $action);

        if (!$this->getRequest()->isPost()) {
            return $this->renderForm($form);
        }

        return $this->process($form, $file);
    }

    // resource bootstrap management
    public function resourceAction()
    {
        $module = $this->getRequest()->getModuleName();
        $item = $this->getRequest()->getParam("item");
        if (empty($item)) {
            $this->forward("index", null, null, array("category" => "resource"));
            return;
        }
        $file = XOOPS::path("var/etc/resource.{$item}.ini.php");
        $action = $this->getFrontController()->getRouter()->assemble(
            array(
                "module"        => $module,
                "controller"    => "bootstrap",
                "action"        => "resource",
                "item"          => $item,
            ),
            "admin"
        );
        $form = $this->getForm($file, $action);

        if (!$this->getRequest()->isPost()) {
            return $this->renderForm($form);
        }

        return $this->process($form, $file);
    }

    // registry bootstrap management
    public function registryAction()
    {
        $module = $this->getRequest()->getModuleName();
        $item = $this->getRequest()->getParam("item");
        if (empty($item)) {
            $this->forward("index", null, null, array("category" => "registry"));
            return;
        }
        $file = XOOPS::path("var/etc/registry.{$item}.ini.php");
        $action = $this->getFrontController()->getRouter()->assemble(
            array(
                "module"        => $module,
                "controller"    => "bootstrap",
                "action"        => "registry",
                "item"          => $item,
            ),
            "admin"
        );
        $form = $this->getForm($file, $action);

        if (!$this->getRequest()->isPost()) {
            return $this->renderForm($form);
        }

        return $this->process($form, $file);
    }

    // Misc bootstrap management
    public function miscAction()
    {
        $module = $this->getRequest()->getModuleName();
        $item = $this->getRequest()->getParam("item");
        if (empty($item)) {
            $this->forward("index", null, null, array("category" => "misc"));
            return;
        }
        $file = XOOPS::path("var/etc/{$item}.ini.php");
        $action = $this->getFrontController()->getRouter()->assemble(
            array(
                "module"        => $module,
                "controller"    => "bootstrap",
                "action"        => "misc",
                "item"          => $item,
            ),
            "admin"
        );
        $form = $this->getForm($file, $action);

        if (!$this->getRequest()->isPost()) {
            return $this->renderForm($form);
        }

        return $this->process($form, $file);
    }

    /**
     * INI file edit form
     *
     * Look forward to an INI editor
     */
    public function getForm($file, $action)
    {
        $module = $this->getRequest()->getModuleName();
        $this->view->headLink(array(
            "href"  => "form.css",
            "rel"   => "stylesheet",
            "type"  => "text/css"
        ));
        $options = array(
            "name"      => self::FORM_NAME,
            "action"    => $action,
            "method"    => "post",
        );
        $form = new Xoops_Zend_Form($options);

        //clearstatcache();
        $options = array(
            "label"     => "File content",
            "required"  => true,
            "value"     => file_get_contents($file),
            "description"   => $file,
            "wrap"      => "off",
            "filters"       => array(
                "trim"      => array(
                    "filter"    => "StringTrim",
                ),
            ),
        );
        $form->addElement("Textarea", "content", $options);

        $options = array(
            "label"     => "Submit",
            "required"  => false,
            "ignore"    => true,
        );
        $form->addElement("submit", "save", $options);

        //$fileExtension = substr($file, strrpos($file, ".") + 1);
        if (substr($file, -8) == ".ini.php") {
            $description = XOOPS::_("For ini file syntax, please refer to http://www.php.net/manual/en/function.parse-ini-file.php");
            $form->setDescription($description);
        } elseif (basename($file) == ".htaccess") {
            $description = XOOPS::_("For .htaccess tutorial, please refer to http://httpd.apache.org/docs/2.2/howto/htaccess.html");
            $form->setDescription($description);
        }

        $form->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'dl', 'class' => 'xoops-form')),
            array('Description', array('placement' => 'prepend')),
            'Form'
        ));

        return $form;
    }

    // Render form
    protected function renderForm($form)
    {
        $this->setTemplate("bootstrap_form.html");
        $form->assign($this->view);
        $title = XOOPS::_("Bootstrap configuration");
        $this->template->assign("title", $title);
    }

    // process submission
    protected function process($form, $filePath)
    {
        $module = $this->getRequest()->getModuleName();
        $posts = $this->getRequest()->getPost();
        if (!$form->isValid($posts)) {
            return $this->renderForm($form);
        }

        $error = false;
        if (!is_writable($filePath)) {
            @chmod($filePath, 0777);
        }
        if (!$file = fopen($filePath, "w")) {
            $error = true;
        } else {
            if (substr($filePath, -8) == ".ini.php" && !preg_match('/^;[\s]?<\?php[\s]+__halt_compiler[\s]?\([\s]?\)[\s]?;/i', $posts['content'])) {
                $posts['content'] = ';<?php __halt_compiler();' . PHP_EOL . PHP_EOL . $posts['content'];
            }
            if (fwrite($file, $posts['content']) === false) {
                $error = true;
            }
            fclose($file);
        }
        @chmod($filePath, 0644);
        if ($error) {
            $errorMessage = XOOPS::_("There is error occurred. Please resubmit the form or manually copy the following content to file '{$file}'.");
            $form->addError($errorMessage);
            $form->addDecorators(array(
                'Errors',
            ));
            return $this->renderForm($form);
        }

        Xoops::persist()->clean();

        $urlOptions = $this->getFrontController()->getRouter()->assemble(
            array(
                "module"        => $module,
                "controller"    => "bootstrap",
                "action"        => "index"
            ),
            "admin"
        );
        $message = XOOPS::_("The file is updated successfully.");
        $options = array("time" => 3, "message" => $message);
        $this->redirect($urlOptions, $options);
    }
}
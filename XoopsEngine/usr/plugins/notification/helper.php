<?php
/**
 * Zend Framework for Xoops Engine
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
 * @category        Xoops_Plugin
 * @package         Notification
 * @version         $Id$
 */

class Plugin_Notification_Helper extends \Application\Plugin
{
    /**
     * @var string
     */
    //protected $template;
    //const DEFAULT_TEMPLATE = "notification.html";
    /**
     * @var boolean
     */
    public $active = true;

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function skip()
    {
        $this->active = false;
        return $this;
    }

    public function getCategories($options = array())
    {
        $request = $this->getRequest();
        $module = $request->getModuleName();
        $categoryList = array();
        // Load active category list from cache
        if ($data = XOOPS::service("registry")->notification->read($module)) {
            $controller = empty($options["controller"]) ? $request->getControllerName() : $options["controller"];
            $action = empty($options["action"]) ? $request->getActionName() : $options["action"];
            $key = $controller . '-' . $action;
            if (isset($data[$key])) {
                $categoryList = $data[$key];
            }
        }
        //Debug::e($data);

        return $categoryList;
    }

    public function translateCategories($categories)
    {
        if (empty($categories)) {
            return $categories;
        }
        $request = $this->getRequest();
        XOOPS::service("translate")->loadTranslation("notification", $request->getModuleName());
        foreach ($categories as $key => &$category) {
            $category["item"] = $request->getParam($category["param"], 0);
            $category["title"] = XOOPS::_($category["title"]);
        }

        //Debug::e($categories);
        return $categories;
    }

    /**
     * @param  Zend_Controller_Request_Abstract $request
     * @return void
     */
    public function preDispatch()
    {
        $request = $this->getRequest();
        $moduleDirname = $request->getModuleName();
        if ($moduleDirname == XOOPS::registry('frontController')->getDefaultModule()) {
            $this->skip();
            return;
        }

        $response = $this->getResponse();
        if ($response->isException() || $response->isRedirect()) {
            $this->skip();
        }
        return;
    }

    /**
     * postDispatch() helper hook -- check for comments
     *
     * @return void
     */
    public function postDispatch()
    {
        if (!$this->active) {
            return;
        }
        $this->render();

        return;
    }

    public function render($template = "", $options = array())
    {
        // BAD design, multiple notifications are not covered.
        if (!$categories = $this->getCategories()) {
            return;
        }
        $configs = XOOPS::service("plugin")->loadConfig("notification");
        $options = array_merge($configs, $options);
        $view = $this->getActionController()->view;
        $engine = $view->getEngine();
        $layout = $view->getHelper("layout")->getLayout();
        $template = !empty($options["template"]) ? $options["template"] : $view->resourcePath("notification.html", true);
        $expire = isset($options["cache_expire"]) ? $options["cache_expire"] : 0;
        $engine->caching = empty($expire) ? 0 : 2;
        $engine->cache_lifetime = $expire;

        /* @see: Smarty::_get_auto_filename() */
        //$smarty_compile_dir_sep =  $engine->use_sub_dirs ? DIRECTORY_SEPARATOR : '^';
        $smarty_compile_dir_sep =  '-';
        $cacheId = $item->id . $smarty_compile_dir_sep . $page;
        $compileId = $engine->generateCompileId($layout->getTheme(), $this->getRequest()->getModuleName());

        if (!$engine->is_cached($template, $cacheId, $compileId)) {
            XOOPS::service("translate")->loadTranslation("main", "plugin:notification");
            $engine->assign("list", $this->translateCategories($categories));
            $engine->assign("caption", XOOPS::_("_PLUGIN_NOTIFICATION_FORM_CAPTION"));
            $engine->assign("description", XOOPS::_("_PLUGIN_NOTIFICATION_FORM_DESCRIPTION"));
            $form = array(
                "action"    => "",
                "method"    => "post",
                "category"  => array(
                    "name"  => "category",
                ),
                "item"      => array(
                    "name"  => "item",
                ),
                "return"    => array(
                    "name"  => "return_url",
                ),
            );
            $engine->assign("form", $form);
        } else {
            XOOPS::service('logger')->log("Notifiation form is cached", 'debug');
        }
        $notification = $engine->fetch($template, $cacheId, $compileId);

        $this->getResponse()->appendBody(
            $notification,
            $layout->getExtensionKey()
        );
    }
    /**
     * Trigger notification action
     *
     * @param string        $event  event name or category
     * @param object|array  $object object or array
     * @return boolean
     */
    public function trigger($event, $object = null)
    {
        $request = $this->getRequest();
        $module = $request->getModuleName();

        $modelCategory = Xoops::service('plugin')->getModel("notification_category");
        $adapter = $modelCategory->getAdapter();
        $row = $modelCategory->fetchRow(array(
            $modelCategory->getAdapter()->quoteIdentifier("module") . " = ?" => $module,
            $modelCategory->getAdapter()->quoteIdentifier("name") . " = ?" => $event
        ));
        if (!$row) {
            return false;
        }
        if ($row->translate) {
            XOOPS::service("translate")->loadTranslation($row->translate, $module);
        }

        // Load callback
        if ($row->callback) {
            $class = ucfirst($module) . "_Notification";
            if (!method_exists($class, $row->callback)) {
                return false;
            }
            $content = call_user_func(array($class, $row->callback), $object);
        // Use content template
        } else {
            $content = $row->content;
            // Translate the content
            if (!empty($content) && $row->translate) {
                $content = XOOPS::_($content);
            }
        }

        /**
         * Following is just for demonstrating how a notification is handled. More details should be considered:
         * 1. Massive subscribers
         * 2. Cron
         * 3. Mailing
         */
        $options = XOOPS::service("plugin")->loadConfig("notification");
        $method = empty($options["method"]) ? "message" : $options["method"];
        $interval = empty($options["iteration_interval"]) ? 100000 : $options["iteration_interval"];
        $limit = empty($options["items_iteration"]) ? 100 : $options["items_iteration"];
        $offset = 0;
        $modelNotification = Xoops::service('plugin')->getModel("notification_subscription");
        $select = $modelNotification->select();
        set_time_limit(0);
        while (1) {
            $select->limit($limit, $offset);
            $rowset = $modelNotification->fetchAll($select);
            foreach ($rowset as $row) {
                //Send notification to user of $row->user


            }
            if ($rowset->count() < $limit) {
                break;
            }
            $offset += $limit;
            usleep($interval);
        }

        return true;
    }
}
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
 * @package         Comment
 * @version         $Id$
 */

class Plugin_Comment_Helper extends \Application\Plugin
{
    /**
     * @var string
     */
    protected $template;
    const DEFAULT_TEMPLATE = "comment.html";
    /**
     * @var boolean
     */
    public $active = true;

    /**
     * @var boolean
     */
    protected $rendered = false;

    /**
     * @var array   category data: id, key, param, template, expire
     */
    protected $category;

    /**
     * @var {@Xoops_Zend_DB_Row}
     */
    protected $item;

    /**
     * @var {@Xoops_Zend_Controller_Plugin_Comment}
     */
    //protected $plugin;

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function setTemplate($template)
    {
        if ($this->active) {
            $this->template = $this->translateTemplate($template);
        }
        return $this;
    }

    protected function translateTemplate($template)
    {
        if (!empty($template) && false === strpos($template, ":")) {
            $module = $this->getRequest()->getModuleName();
            $template = ((Xoops::service('module')->getType($module) == "legacy") ? "module" : "app") . ":" . $module . "/" . $template;
        }

        return $template;
    }

    public function getTemplate()
    {
        return $this->template;
    }

    public function skip()
    {
        $this->active = false;
        return $this;
    }

    public function disable()
    {
        if ($this->active && $item = $this->getItem()) {
            if (!$item->disabled) {
                $item->disabled = 1;
                $item->save();
            }
        }
        return $this;
    }

    public function enable()
    {
        if ($this->active && $item = $this->getItem()) {
            if ($item->disabled) {
                $item->disabled = 0;
                $item->save();
            }
        }
        return $this;
    }

    public function getCount()
    {
        if ($this->active && $item = $this->getItem()) {
            return $item->active;
        }

        return null;
    }

    public function update()
    {
        if ($this->active && $item = $this->getItem()) {
            $item->updated = time();
            $item->save();
        }

        return $this;
    }

    public function getCategory($options = array())
    {
        if ($this->active && !isset($this->category)) {
            $request = $this->getRequest();
            $module = $request->getModuleName();
            $category = array();
            // Load active category list from cache
            if ($data = XOOPS::service("registry")->comment->read($module)) {
                // category key is specified
                if (!empty($options["key"])) {
                    foreach ($data as $key => $categoryData) {
                        if ($categoryData["key"] == $options["key"]) {
                            $category = $categoryData;
                            break;
                        }
                    }
                // get category from controller and action
                } else {
                    $controller = empty($options["controller"]) ? $request->getControllerName() : $options["controller"];
                    $action = empty($options["action"]) ? $request->getActionName() : $options["action"];
                    $key = $controller . '-' . $action;
                    if (isset($data[$key])) {
                        $category = $data[$key];
                    }
                }
            }
            $this->category = $category;
        }
        return $this->category;
    }

    public function getItem($options = array())
    {
        if ($this->active && !isset($this->item)) {
            $request = $this->getRequest();
            $module = $request->getModuleName();
            $category = $this->getCategory($options);
            $this->item = false;

            // Category is valid
            if ($category) {
                $param = !isset($options["param"]) ? $request->getParam($category["param_item"], 0) : $options["param"];
                $model = Xoops::service('plugin')->getModel("comment_item");
                // Fetch item
                $item = $model->fetchRow(array("category = ?" => $category["id"], "param = ?" => $param));
                // Create item if not exists
                if (!$item) {
                    $data = array("category" => $category["id"], "param" => $param, "module" => $module, "active" => 1);
                    $item = $model->createRow($data);
                    $item->save();
                }
                $this->item = $item;
            }
        }
        return $this->item;
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
        if (!$this->active || $this->rendered) {
            return;
        }
        $this->render();

        return;
    }

    public function render($template = "", $options = array())
    {
        if (!$item = $this->getItem($options)) {
            return;
        }
        if ($item->disabled) {
            return;
        }
        $this->rendered = true;
        $configs = XOOPS::service("plugin")->loadConfig("comment");
        $options = array_merge($configs, $options);
        $view = $this->getActionController()->view;
        $engine = $view->getEngine();
        $layout = $view->getHelper("layout")->getLayout();
        $category = $this->getCategory();
        $template = !empty($template)
                    ? $template
                    : ($this->getTemplate()
                        ? $this->getTemplate()
                        : (!empty($category["template"])
                            ? $this->translateTemplate($category["template"])
                            : static::DEFAULT_TEMPLATE));
        $template = $view->resourcePath($template, true);

        $expire = isset($options["cache_expire"]) ? $options["cache_expire"] : $category["expire"];
        $pageParam = !empty($options["param_page"])
                    ? $options["param_page"]
                    : (!empty($category["param_page"])
                        ? $category["param_page"]
                        : "cp");
        $page = !empty($options["page"]) ? $options["page"] : $this->getRequest()->getParam($pageParam, 1);
        $limit = !empty($options["items_perpage"])
                    ? $options["items_perpage"]
                    : (!empty($category["items_perpage"])
                        ? $category["items_perpage"]
                        : 20);

        $engine->caching = empty($expire) ? 0 : 2;
        $engine->cache_lifetime = $expire;

        /* @see: Smarty::_get_auto_filename() */
        //$smarty_compile_dir_sep =  $engine->use_sub_dirs ? DIRECTORY_SEPARATOR : '^';
        $smarty_compile_dir_sep =  '-';
        $cacheId = $item->id . $smarty_compile_dir_sep . $page;
        $compileId = $engine->generateCompileId($layout->getTheme(), $this->getRequest()->getModuleName());

        //Debug::e($engine->caching);
        //Debug::e($engine->cache_lifetime);
        //Debug::e($template);
        //Debug::e($cacheId);
        //Debug::e($compileId);
        if (!$engine->is_cached($template, $cacheId, $compileId)) {
            $model = Xoops::service('plugin')->getModel("comment_post");
            $order = (isset($options["display_order"]) && "asc" == $options["display_order"]) ? "ASC" : "DESC";
            $select = $model->select()->where("item = ?", $item->id)->where("active = ?", 1)->order("id " . $order);

            $paginator = Xoops_Zend_Paginator::factory($select);
            $paginator->setItemCountPerPage($limit);
            $paginator->setCurrentPageNumber($page);
            $paginator->setParams(array());
            $paginator->setPageParam($pageParam);
            $itemList = $paginator->getCurrentItems();
            $engine->assign("list", $itemList);
            $form = array(
                "action"    => "",
                "method"    => "post",
                "comment"   => array(
                    "name"  => "comment",
                ),
                "item"      => array(
                    "name"  => "item",
                    "value" => $item->id,
                ),
            );
            $engine->assign("form", $form);
            XOOPS::service("translate")->loadTranslation("main", "plugin:comment");
            $engine->assign("total_comments", sprintf(XOOPS::_("_PLUGIN_COMMENT_TOTAL"), $paginator->getTotalItemCount()));
            $engine->assign("submit_comment", XOOPS::_("_PLUGIN_COMMENT_SUBMIT_COMMENT"));
        } else {
            XOOPS::service('logger')->log("Comment is cached", 'debug');
        }
        $comment = $engine->fetch($template, $cacheId, $compileId);

        $this->getResponse()->appendBody(
            $comment,
            $layout->getExtensionKey()
        );
    }

    public function clearCache($itemId = null)
    {
        if (empty($itemId)) {
            if (!$item = $this->getItem()) {
                return;
            }
            if ($item->disabled) {
                return;
            }
            $itemId = $item->id;
        }
        $cacheId = $itemId;
        $engine = $this->getActionController()->view->getEngine();
        $status = $engine->clearCacheByCacheId($cacheId);
        //Debug::e(__METHOD__ .':'. $cacheId . '->' . $status);

        return $status;
    }
}
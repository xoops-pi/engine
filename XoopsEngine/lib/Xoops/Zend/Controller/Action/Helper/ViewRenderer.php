<?PHP
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
 * @copyright       Xoops Engine http://www.xoopsengine.org/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @since           3.0
 * @category        Xoops_Zend
 * @package         Controller
 * @version         $Id$
 */

class Xoops_Zend_Controller_Action_Helper_ViewRenderer extends Zend_Controller_Action_Helper_ViewRenderer
{
    /**
     * View script suffix
     * @var string
     */
    protected $_viewSuffix      = 'html';

    /**
     * Template file
     * @var string
     */
    protected $template = null;
    /**
     * Template data
     * @var {@Smarty_data}
     */
    protected $data = null;

    protected $viewScript = null;

    protected $cached = false;

    public function getView()
    {
        if (null === $this->view) {
            $this->initView();
        }

        return $this->view;
    }

    public function isCached()
    {
        return $this->cached;
    }

    public function setCached($cached = true)
    {
        $this->cached = (bool) $cached;
        return $this;
    }

    public function setTemplate($template = '')
    {
        $this->template = $template;
        return $this;
    }

    public function getTemplate($isTranslated = false)
    {
        if (!$isTranslated) {
            return $this->template;
        }
        //return $this->view->getScriptPath($this->getViewScript());
        return $this->getViewScript();
    }

    /**
     * creates a data object
     *
     * @param object $parent next higher level of Smarty variables
     * @returns object data object
     */
    public function createData($parent = null)
    {
        $this->data = $this->view->getEngine()->createData($parent, $this->view->getEngine());
        return $this->data;
    }

    public function getData()
    {
        return $this->data;
    }

    /**
     * Initialize the view object
     *
     * $options may contain the following keys:
     * - neverRender - flag dis/enabling postDispatch() autorender (affects all subsequent calls)
     * - noController - flag indicating whether or not to look for view scripts in subdirectories named after the controller
     * - noRender - flag indicating whether or not to autorender postDispatch()
     * - responseSegment - which named response segment to render a view script to
     * - scriptAction - what action script to render
     * - viewBasePathSpec - specification to use for determining view base path
     * - viewScriptPathSpec - specification to use for determining view script paths
     * - viewScriptPathNoControllerSpec - specification to use for determining view script paths when noController flag is set
     * - viewSuffix - what view script filename suffix to use
     *
     * @param  string $path
     * @param  string $prefix
     * @param  array  $options
     * @throws Zend_Controller_Action_Exception
     * @return void
     */
    public function initView($path = null, $prefix = null, array $options = array())
    {
        if (null === $this->view) {
            $this->setView(new Xoops_Zend_View($options));
        }

        // Reset some flags every time
        $options['noController'] = (isset($options['noController'])) ? $options['noController'] : false;
        $options['noRender']     = (isset($options['noRender'])) ? $options['noRender'] : false;
        $this->_scriptAction     = null;
        $this->_responseSegment  = null;

        // Set options first; may be used to determine other initializations
        $this->_setOptions($options);

        // Register view with action controller (unless already registered)
        if ((null !== $this->_actionController) && (null === $this->_actionController->view)) {
            $this->_actionController->view       = $this->view;
            $this->_actionController->viewSuffix = $this->_viewSuffix;
        }
    }

    /**
     * Get a view script based on an action and/or other variables
     *
     * Uses values found in current request if no values passed in $vars.
     *
     * If {@link $_noController} is set, uses {@link $_viewScriptPathNoControllerSpec};
     * otherwise, uses {@link $_viewScriptPathSpec}.
     *
     * @param  string $action
     * @param  array  $vars
     * @return string
     */
    public function getViewScript($action = null, array $vars = array())
    {
        if (!isset($this->viewScript)) {

            /*
            // Accept legacy content template
            if (!isset($vars['template']) && $template_main = XOOPS::registry('template_main')) {
                if (false === strpos($template_main, ':')) {
                    $template_main = 'legacy:' . $template_main;
                } elseif ("db:" == substr($template_main, 0, 3)) {
                    $template_main = 'legacy:' . substr($template_main, 3);
                }
                $vars['template'] = $template_main;
            }
            */

            // Accept custom template set in action
            if (!isset($vars['template']) && isset($this->template)) {
                $vars['template'] = $this->template;
            } else {
                $request = $this->getRequest();
                if ((null === $action) && (!isset($vars['action']))) {
                    $action = $this->getScriptAction();
                    if (null === $action) {
                        $action = $request->getActionName();
                    }
                    $vars['action'] = $action;
                } elseif (null !== $action) {
                    $vars['action'] = $action;
                }
            }

            $this->viewScript = $this->_translateSpec($vars);
        }

        return $this->viewScript;
    }

    /**
     * Generate script path based on provided vars to be portable with Xoops_Zend_Layout::resourcePath()
     *
     * Allowed variables are:
     * - :moduleDir - current module directory
     * - :module - current module name
     * - :controller - current controller name
     * - :action - current action name
     * - :suffix - view script file suffix
     *
     * @param  array $vars
     * @return string
     */
    protected function _translateSpec(array $vars = array())
    {
        // File name prepended with resource type, for db:file.name, file:file.name or app:file.name
        // Or full path under WIN: C:\Path\To\Template
        if (!empty($vars['template']) && false !== strpos($vars['template'], ":")) {
            return $vars['template'];
        }
        // Do not use template, direct echo
        if (isset($vars['template']) && empty($vars['template'])) {
            return $vars['template'];
        }

        $request = $this->getRequest();
        if (empty($vars['module'])) {
            $vars['module'] = $request->getModuleName();
        }
        $module     = null;
        $location   = null;
        // If template located in a specified module folder
        if (!empty($vars['template']) && false !== strpos($vars['template'], "/")) {
            list($module, $file) = explode("/", $vars['template'], 2);
        } else {
            $module = !empty($vars['module']) ? $vars['module'] : $request->getModuleName();
            // If template is not specified, generate from module/controller/action
            if (empty($vars['template'])) {
                if (empty($vars['controller'])) {
                    $vars['controller'] = $request->getControllerName();
                }
                if (empty($vars['action'])) {
                    $vars['action'] = $request->getActionName();
                }
                if (empty($vars['suffix'])) {
                    $vars['suffix'] = $this->getViewSuffix();
                }
                //$vars['template'] = $vars['controller'] . (empty($vars['action']) ? "" : "_" . $vars['action']) . "." . $vars['suffix'];
                $file = $vars['controller'] . (empty($vars['action']) ? "" : "_" . $vars['action']) . "." . $vars['suffix'];
            } else {
                $file = $vars['template'];
            }
            $section = isset($vars['section']) ? $vars['section'] : $this->getFrontController()->getParam("section");
            // Prepend section if not front
            if ($section != "front" && $module != $this->getFrontController()->getDefaultModule()) {
                $file = $section . "/" . $file;
            }
        }
        if ($module == $this->getFrontController()->getDefaultModule()) {
            $location = "app";
        } else {
            $type = Xoops::service('module')->getType($module);
            // Set path according to module type
            $location = ("legacy" != $type) ? "app" : "module";
        }

        /*
        // If template is a file located in current module folder
        if (false === strpos($vars['template'], "/")) {
            $file = $vars['template'];
        }
        // If template is a file located in another module folder
        else {
        }
        // If default module, specify its path to avoid "section"
        if ($request->getModuleName() == $this->getFrontController()->getDefaultModule()) {
            $folder = $request->getModuleName();
            $location = "app";
        } else {
            $type = Xoops::service('module')->getType($vars['module']);
            // Set path according to module type
            $location = ("legacy" != $type) ? "app" : "module";
            $folder = $vars['module'];
            $section = $this->getFrontController()->getParam("section");
            // Prepend section if not front
            if ($section != "front") {
                $vars['template'] = $section . "/" . $vars['template'];
            }
        }
        */
        //$translated = $location . "/" . $folder . "/templates/" . strtolower($vars['template']);
        $translated = $location . "/" . $module . "/" . strtolower($file);
        //$extension = substr($translated, strrpos($translated, '.') + 1);
        $suffix = strtolower(pathinfo($translated, PATHINFO_EXTENSION));
        switch ($suffix) {
            case "phtml":
                $format = "zend";
                break;
            case "php":
                $format = "php";
                break;
            case "html":
            default:
                $format = "file";
                break;
        }
        $translated = $format . ":" . $translated;
        return $translated;
    }

    /**
     * Render a view script (optionally to a named response segment)
     *
     * Sets the noRender flag to true when called.
     *
     * @param  string $script
     * @param  string $name
     * @return void
     */
    public function renderScript($script, $name = null)
    {
        if (null === $name) {
            $name = $this->getResponseSegment();
        }

        $this->getResponse()->appendBody(
            $this->view->render($script),
            $name
        );

        $this->setNoRender();
    }

    /**
     * Check if action content cached
     *
     * Determine if we have a cache hit. If so, return the response; else,
     * start caching.
     *
     * @return void
     */
    public function preDispatch()
    {
        $request = $this->getRequest();
        $response = $this->getResponse();
        $layout = $this->view->getHelper('layout')->getLayout();
        $this->setCached(false);

        // Return early if forward detected
        if (!$request->isDispatched()) {
            return;
        }
        // Register global variables to view template
        $layout->initView();

        /**#@+
         * Process cache check for controller content
         */
        //Skip cache
        //if request is not GET
        if (!$request->isGet()
            // Or exception occured
            || $response->isException()
            // Or request is inside default module
            //|| XOOPS::registry('frontController')->getDefaultModule() != $request->getModuleName()
            ) {
            $layout->skipCache();
            return false;
        }
        XOOPS::registry('profiler')->start("Action");

        $this->contextSwitch();
        $cacheInfo = $layout->plugin->loadCacheInfo();

        if (!$this->renderCache($cacheInfo)) {
            return;
        }

        // Set flag to cached to notify postDispatch method
        $this->setCached(true);
        // Set request to not dispatched to avoid execution of controller action. It is not a reliable solution but so far there is not formal solution of skipping controller action
        $request->setDispatched(false);
        XOOPS::service('logger')->log("Action is cached", 'debug');
        /**#@-*/
    }

    /**
     * postDispatch - auto render a view
     *
     * Only autorenders if:
     * - _noRender is false
     * - action controller is present
     * - request has not been re-dispatched (i.e., _forward() has not been called)
     * - response is not a redirect
     *
     * @return void
     */
    public function postDispatch()
    {
        // Restore request dispatched status to resume formal helpers and plugins
        if ($this->isCached()) {
            $this->getRequest()->setDispatched(true);
        }

        /*
        if (empty($this->template) && empty($this->data)) {
            $this->setNoRender();
        }
        */

        if ($this->_shouldRender()) {
            $this->contextSwitch();
            $template = $this->view->getEngine();
            $layout = $this->view->getHelper('layout')->getLayout();

            if ($layout->skipCache) {
                $template->caching = 0;
            }

            $template->setCompileId(
                $layout->getTheme(),
                $this->getRequest()->getModuleName()
            );
            $this->render();
        }
    }


    /**
     * Attempt to load content from template cache if cache is valid
     *
     * @param  array $cacheInfo
     * @retrun boolean  true if cache is load
     */
    public function renderCache($cacheInfo)
    {
        if (empty($cacheInfo)) {
            return false;
        }
        $cache = $cacheInfo;
        $template = $this->view->getEngine();
        $layout = $this->view->getHelper('layout')->getLayout();
        $request = $this->getRequest();

        if (empty($cache['expire']) || $layout->skipCache) {
            $template->caching = 0;
        } else {
            $template->caching = 1;
            $template->cache_lifetime = $cache['expire'];
            $template->setCacheId($cache['cache_id']);
        }
        $template->setCompileId($layout->getTheme(), $request->getModuleName());

        if (empty($cache['template']) || !$template->is_cached($cache['template'])) {
            //Debug::e("Not cached");
            return false;
        }
        if (!$content = $template->fetch($cache['template'])) {
            //Debug::e("Not cached");
            return false;
        }

        // Content is cached, load the content and send to response
        $name = $this->getResponseSegment();
        $this->getResponse()->appendBody(
            $content,
            $name
        );
        // Skip following render to avoid duplicated rendering
        $this->setNoRender();

        return true;
    }

    protected function contextSwitch()
    {
        $request = $this->getRequest();
        // Set template to empty for AJAX response
        if ($request->isXmlHttpRequest()
            || $request->isFlashRequest()
            ) {
            $this->view->getHelper('layout')->getLayout()->setLayout("empty");
        }
    }
}
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

class Xoops_Zend_Controller_Action extends Zend_Controller_Action
{
    /**
     * Template data object
     * @see Smarty_Data
     */
    public $template = null;

    protected $acl;

    /**
     * Class constructor
     *
     * The request and response objects should be registered with the
     * controller, as should be any additional optional arguments; these will be
     * available via {@link getRequest()}, {@link getResponse()}, and
     * {@link getInvokeArgs()}, respectively.
     *
     * When overriding the constructor, please consider this usage as a best
     * practice and ensure that each is registered appropriately; the easiest
     * way to do so is to simply call parent::__construct($request, $response,
     * $invokeArgs).
     *
     * After the request, response, and invokeArgs are set, the
     * {@link $_helper helper broker} is initialized.
     *
     * Finally, {@link init()} is called as the final action of
     * instantiation, and may be safely overridden to perform initialization
     * tasks; as a general rule, override {@link init()} instead of the
     * constructor to customize an action controller's instantiation.
     *
     * @param Zend_Controller_Request_Abstract $request
     * @param Zend_Controller_Response_Abstract $response
     * @param array $invokeArgs Any additional invocation arguments
     * @return void
     */
    public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array())
    {
        XOOPS::service('profiler')->start(__CLASS__);
        //parent::__construct($request, $response, $invokeArgs);
        //$this->_helper->getPluginLoader()->addPrefixPath('Xoops_Zend_Controller_Action_Helper', 'Xoops/Zend/Controller/Action/Helper/');
        $this->setRequest($request)
             ->setResponse($response)
             ->_setInvokeArgs($invokeArgs);
        $this->_helper = new Xoops_Zend_Controller_Action_HelperBroker($this);
        $this->init();
        $this->loadView();
    }

    protected function loadView()
    {
        if ($this->getInvokeArg('noViewRenderer')) {
            $this->view = $this->initView();
        } else {
            $viewRenderer = $this->getHelper("viewRenderer");
            $this->view = $viewRenderer->getView();
        }
        $this->template = $this->view->getEngine();
        //$this->template = $viewRenderer->view->getEngine()->createData();
        //$this->getFrontController()->setParam('noViewRenderer', false);
    }

    /**
     * Initialize View object
     *
     * Initializes {@link $view} if not otherwise a Zend_View_Interface.
     *
     * @return Zend_View_Interface
     */
    public function initView()
    {
        if (!$this->getInvokeArg('noViewRenderer') && $this->_helper->hasHelper('viewRenderer')) {
            return $this->view;
        }

        if (isset($this->view) && ($this->view instanceof Zend_View_Interface)) {
            return $this->view;
        }

        $this->view = Xoops::registry('view') ?: new Xoops_Zend_View(array());

        return $this->view;
    }

    /**
     * Set view template and canvas template
     *
     * @param   string  $viewTemplate
     * @param   string  $canvasTemplate
     * @return void
     */
    protected function setTemplate($viewTemplate = '', $canvasTemplate = '')
    {
        if (isset($viewTemplate)) {
            $this->_helper->viewRenderer->setTemplate($viewTemplate);
        }
        // To be deprecated
        if (!empty($canvasTemplate)) {
            $this->_helper->layout->setLayout($canvasTemplate);
        }
    }

    /**
     * Set layout (canvas) template
     *
     * @param   string  $layout
     * @return void
     */
    protected function setLayout($layout = '')
    {
        $this->_helper->layout->setLayout($layout);
    }

    /**
     * Set theme set
     *
     * @param   string  $theme
     * @return void
     */
    protected function setTheme($theme = '')
    {
        $this->_helper->layout->setTheme($theme);
    }

    /**
     * Set page cache level
     *
     * @param   string  $level  potential values: user, role, language, public by default
     * @return  void
     */
    protected function cacheLevel($level = null)
    {
        return $this->_helper->layout->cacheLevel($level);
    }

    /**
     * Skip page cache
     *
     * @return  void
     */
    protected function skipCache($flag = true)
    {
        $this->_helper->layout->skipCache($flag);
    }

    /**
     * confirm and proceed to another URL
     *
     * @param array $options Options to be used when confirming:
     *          string      message     Confirmation prompting message to display on confirmation window
     *
     *          [string      form        Name for confirmation form, default as 'confirmForm']
     *          [string      action      Action URI for confirmation form]
     *          string      method      Method for confirmation form, default as 'post'
     *
     *          string      name        Name for confirmation select element
     *          int|array   options     MultiOptions for select element, default as 1 for Yesno
     *
     *          array       hidden      Appended data, associative array
     *
     * @param string|array      $url        URL or options to assemble a URL as confirmation form action
     * @param string|array|bool $urlGoback  Options to be used when generating goback url, true for generating automatically
     * @return void
     */
    public function confirm(array $options = array(), $url, $goback = true)
    {
        $this->skipCache();
        $this->_helper->confirm->confirm($options, $url, $goback);
    }

    /**
     * Redirect to another URL
     *
     * Proxies to {@link Zend_Controller_Action_Helper_Redirector::gotoUrl()}.
     *
     * @param string|array $url url or options to assemble a url
     * @param array $options Options to be used when redirecting
     * @return void
     */
    public function redirect($url, array $options = array())
    {
        $this->skipCache();
        $this->_helper->redirector->redirect($url, $options);
    }

    /**
     * Forward to another controller/action.
     *
     * It is important to supply the unformatted names, i.e. "article"
     * rather than "ArticleController".  The dispatcher will do the
     * appropriate formatting when the request is received.
     *
     * If only an action name is provided, forwards to that action in this
     * controller.
     *
     * If an action and controller are specified, forwards to that action and
     * controller in this module.
     *
     * Specifying an action, controller, and module is the most specific way to
     * forward.
     *
     * A fourth argument, $params, will be used to set the request parameters.
     * If either the controller or module are unnecessary for forwarding,
     * simply pass null values for them before specifying the parameters.
     *
     * @param string $action
     * @param string $controller
     * @param string $module
     * @param array $params
     * @return void
     */
    public function forward($action, $controller = null, $module = null, array $params = null)
    {
        $this->skipCache();
        $this->_forward($action, $controller, $module, $params);
    }

    /**
     * Load a model from an application directory
     *
     * Model class file is located in /apps/[app]/models/example.php
     * with class name [app]_model_example
     *
     * @param string $name
     * @param string|null $app
     * @return object {@Xoops_Zend_Db_Model}
     */
    public function getModel($name, $app = null)
    {
        if (empty($app)) {
            $app = $this->getRequest()->getModuleName();
        }
        return Xoops::service('module')->getModel($name, $app);
    }

    public function getAcl()
    {
        if (!isset($this->acl)) {
            $this->acl = new Xoops_Acl();
            $this->acl->setSection("module")
                        ->setModule($this->getRequest()->getModuleName());
        }
    }

    public function plugin($key)
    {
        $plugin = Xoops::service('plugin')->load($key);
        return $plugin;
    }
}
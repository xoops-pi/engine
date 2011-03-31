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
 * @copyright       Xoops Engine http://www.xoopsengine.org/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @since           3.0
 * @category        Xoops_Zend
 * @package         Controller
 * @version         $Id$
 */

class Xoops_Zend_Controller_Plugin_Translate extends Zend_Controller_Plugin_Abstract
{
    /**
     * @var array
     */
    private $options;

    /**
     * Constructor: initialize translate
     *
     * @param  array|Zend_Config $options
     * @return void
     * @throws Exception
     */
    public function __construct($options)
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }
        if (!is_array($options)) {
            $options = array();
        }

        $this->options = $options;
    }

    /**
     * Load system translation
     *
     * @param  Zend_Controller_Request_Abstract $request
     * @return void
     */
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
        // Initialize translate
        $options = isset($this->options) ? $this->options : array();
        $options['disableNotices'] = isset($options['disableNotices']) ? $options['disableNotices'] : true;
        $options['tag'] = isset($options['tag']) ? $options['tag'] : 'Xoops_Translate';
        $load = array();
        if (isset($options['load'])) {
            $load = $options['load'];
            unset($options['load']);
        }
        $options['adapter'] = isset($options['adapter']) ? $options['adapter'] : 'legacy';

        //Debug::e($options);
        $translate = Xoops::service('translate', $options);
        /*
        $translate = new Xoops_Zend_Translate($options);
        XOOPS::registry("translate", $translate);
        Zend_Registry::set("Zend_Translate", $translate);
        */
        if (XOOPS::registry("frontController")->getDispatcher()->getDefaultModule() == $request->getModuleName()) {
            return;
        }

        foreach ($load as $data => $loader) {
            $loader = is_array($loader) ?: array();
            $_options = !isset($loader['options']) ? array() : $loader['options'];
            $_locale = empty($loader['locale']) ? null : $loader['locale'];
            $translate->loadTranslation($data, "", $_locale, $_options);
        }
    }

    /**
     * Predispatch to load module translation
     *
     * @param  Zend_Controller_Request_Abstract $request
     * @return void
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        if (XOOPS::registry("frontController")->getDispatcher()->getDefaultModule() == $request->getModuleName()) {
            return;
        }
        $moduleInfo = Xoops::service('module')->loadInfo($request->getModuleName());
        $moduleTranslate = !empty($moduleInfo['translate'])
                            ? $moduleInfo['translate']
                            : (empty($this->options['module']) ? array() : $this->options['module']);

        if (empty($moduleTranslate['data']) || "info" == $moduleTranslate['data']) {
            return;
        }
        XOOPS::service("translate")->loadTranslation($moduleTranslate['data'], $request->getModuleName());
        return;
    }
}
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
 * @category        Xoops_Zend
 * @package         Layout
 * @version         $Id$
 */

class Xoops_Zend_Layout_Controller_Plugin_Layout extends Zend_Layout_Controller_Plugin_Layout
{
    //private $cacheKey;
    private static $cacheInfo;

    /**
     * preDispatch() plugin hook -- set cache plag for layout
     *
     * @param  Zend_Controller_Request_Abstract $request
     * @return void
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $cacheInfo = $this->loadCacheInfo(true);
        if (empty($cacheInfo) || empty($cacheInfo["expire"])) {
            $this->getLayout()->skipCache();
        }
    }

    /**
     * postDispatch() plugin hook -- render layout
     *
     * @param  Zend_Controller_Request_Abstract $request
     * @return void
     */
    public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
        $viewRenderer = XOOPS::registry('viewRenderer');
        if ($viewRenderer->getNeverRender()) {
            return;
        }

        $layout = $this->getLayout();
        $helper = $this->getLayoutActionHelper();

        // Return early if forward detected
        if (!$request->isDispatched()
            || ($layout->getMvcSuccessfulActionOnly()
                && (!empty($helper) && !$helper->isActionControllerSuccessful())))
        {
            return;
        }

        // Return early if layout has been disabled
        if (!$layout->isEnabled()) {
            return;
        }

        $response = $this->getResponse();
        $layout->setContent($response);
        XOOPS::Service('profiler')->stop("Action");

        //Skip cache storage
        //If response is redirected
        if ($response->isRedirect()
            // Or exception occured
            || $response->isException()
            // Or key is not set
            //|| (null === $this->cacheKey)
            // Or action content is cached
            || $viewRenderer->isCached()
        ) {

        //Otherwise, save cache info data
        } else {
            $cache = array("template" => $viewRenderer->getTemplate(true));
            $this->saveCacheInfo($cache);
        }

        $fullContent = null;
        $obStartLevel = ob_get_level();
        try {
            $fullContent = $layout->assemble($request);
            //$fullContent = $layout->render();
            $response->setBody($fullContent);
        } catch (Exception $e) {
            while (ob_get_level() > $obStartLevel) {
                $fullContent .= ob_get_clean();
            }

            $request->setParam('layoutFullContent', $fullContent);
            $request->setParam('layoutContent', $layout->content);
            $response->setBody(null);
            throw $e;
        }
    }

    public function loadCacheInfo($reload = false)
    {
        $layout = $this->getLayout();
        if ($layout->skipCache) {
            return false;
        }

        if (isset(static::$cacheInfo) && !$reload) {
            return static::$cacheInfo;
        }
        $cacheInfo = $this->readPageCache();
        if (empty($cacheInfo)) {
            return false;
        }

        $request = $this->getRequest();
        // raw cache key
        $rawCacheKey = md5($request->getPathInfo());
        $cacheKey = Xoops_Zend_Cache::generateId($rawCacheKey, $cacheInfo['level']);

        $cache = $layout->cache()->read($cacheKey, $cacheInfo['level']);
        $cache = empty($cache) ? $cacheInfo : array_merge($cache, $cacheInfo);
        //$cache['expire'] = $cacheInfo['expire'];
        //$cache['level'] = $cacheInfo['level'];
        $cache['cache_id'] = $cacheKey;
        static::$cacheInfo = $cache;

        //Debug::e(__METHOD__);
        //Debug::e($cache);

        return static::$cacheInfo;
    }

    public function saveCacheInfo($cache)
    {
        $layout = $this->getLayout();

        //Skip cache storage if cache is skipped
        if ($layout->skipCache) {
            return false;
        }

        if (empty($cache['template'])) {
            $cache['template'] = "app/system/dummy.html";
        }
        $cacheInfo = $this->loadCacheInfo();
        //Debug::e(__METHOD__);
        //Debug::e($cache);
        if ($cacheInfo) {
            $layout->cache()->write($cache, $cacheInfo['cache_id'], $cacheInfo['expire'], $cacheInfo['level']);
        }

        // if template is not set, we need save the content to smarty cache
        if ($cache['template'] != "system/dummy.html") {
            return true;
        }
        $template = $layout->getView()->getEngine();
        if ($cacheInfo) {
            $template->caching = 1;
            $template->cache_lifetime = $cacheInfo['expire'];
        } else {
            $template->caching = 0;
        }
        $content = $layout->{$layout->getContentKey()};
        $template->assign("dummy_content", $content);
        $template->fetch("system/dummy.html", $cacheInfo['cache_id']);
    }

    private function readPageCache()
    {
        $cacheInfo = false;
        $request = $this->getRequest();
        list($module, $controller, $action) = array($request->getModuleName(), $request->getControllerName(), $request->getActionName());
        // section ?
        $info = XOOPS::service('registry')->cache->read(XOOPS::registry("frontController")->getParam("section"), $module);
        if (empty($info)) {
            return $cacheInfo;
        }
        if (isset($info["{$module}-{$controller}-{$action}"])) {
            $cacheInfo = $info["{$module}-{$controller}-{$action}"];
        } elseif (isset($info["{$module}-{$controller}"])) {
            $cacheInfo = $info["{$module}-{$controller}"];
        } elseif (isset($info["{$module}"])) {
            $cacheInfo = $info["{$module}"];
        } else {
            return $cacheInfo;
        }
        if (empty($cacheInfo['level'])) {
            $level = $this->getLayout()->cacheLevel();
            //if (!empty($level)) {
                $cacheInfo['level'] = $level;
            //}
        }
        return $cacheInfo;
    }
}
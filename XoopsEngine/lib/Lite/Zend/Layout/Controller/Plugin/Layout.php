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
 * @copyright       The Xoops Engine http://sourceforge.net/projects/xoops/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @since           3.0
 * @category        Xoops_Zend
 * @package         Layout
 * @version         $Id$
 */

class Lite_Zend_Layout_Controller_Plugin_Layout extends Zend_Layout_Controller_Plugin_Layout
{
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

        XOOPS::service("profiler")->start(__METHOD__);
        $response = $this->getResponse();
        $layout->setContent($response);
        XOOPS::registry('profiler')->stop("Action");

        /*
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
        */

        $fullContent = null;
        $obStartLevel = ob_get_level();
        try {
            $fullContent = $layout->assemble($request);
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
        XOOPS::service("profiler")->stop(__METHOD__);
    }
}
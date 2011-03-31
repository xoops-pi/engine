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
 * @package         View
 * @version         $Id$
 */

class Xoops_Zend_View_Helper_Navigation_Breadcrumbs
    extends Zend_View_Helper_Navigation_Breadcrumbs
{
    // Render methods:

    /**
     * Renders breadcrumbs by chaining 'a' elements with the separator
     * registered in the helper
     *
     * @param  Zend_Navigation_Container $container  [optional] container to
     *                                               render. Default is to
     *                                               render the container
     *                                               registered in the helper.
     * @return string                                helper output
     */
    public function renderStraight(Zend_Navigation_Container $container = null)
    {
        if (null === $container) {
            $container = $this->getContainer();
        }

        $invisibleSetting = $this->getRenderInvisible();
        $this->setRenderInvisible(true);

        // find deepest active
        if (!$active = $this->findActive($container)) {
            return '';
        }
        $this->setRenderInvisible($invisibleSetting);

        $active = $active['page'];

        if ($active->isVisible(false)) {
            // put the deepest active page last in breadcrumbs
            if ($this->getLinkLast()) {
                $html = $this->htmlify($active);
            } else {
                $html = $active->getLabel();
                if ($this->getUseTranslator() && $t = $this->getTranslator()) {
                    $html = $t->translate($html);
                }
                $html = $this->view->escape($html);
            }
        } else {
            $html = "";
        }

        // walk back to root
        while ($parent = $active->getParent()) {
            if ($parent instanceof Zend_Navigation_Page) {
                // prepend crumb to html
                $html = $this->htmlify($parent)
                      . (strlen($html)
                            ? (
                              $this->getSeparator()
                              . $html)
                            : ""
                      );
            }

            if ($parent === $container) {
                // at the root of the given container
                break;
            }

            $active = $parent;
        }

        return strlen($html) ? $this->getIndent() . $html : '';
    }
}

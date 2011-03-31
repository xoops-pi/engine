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


/**
 * Helper for setting and retrieving script elements for HTML head section
 * @see Zend_View_Helper_HeadScript
 *
 * <code>
 * XOOPS::registry('view')->headScript('file', 'filename', 'append', array(), 'text/javascript');
 * XOOPS::registry('view')->headScript('script', 'source content', 'append', array(), 'text/javascript');
 * </code>
 */

class Xoops_Zend_View_Helper_HeadScript extends Zend_View_Helper_HeadScript
{
    /**
     * Registry key for placeholder
     * @var string
     */
    protected $_regKey = 'Xoops_Zend_View_Helper_HeadScript';
    /**
     * Create script HTML
     *
     * @param  string $type
     * @param  array $attributes
     * @param  string $content
     * @param  string|int $indent
     * @return string
     */
    public function itemToString($item, $indent, $escapeStart, $escapeEnd)
    {
        $attrString = '';
        if (!empty($item->attributes)) {
            foreach ($item->attributes as $key => $value) {
                if (!$this->arbitraryAttributesAllowed()
                    && !in_array($key, $this->_optionalAttributes))
                {
                    continue;
                }
                if ('defer' == $key) {
                    $value = 'defer';
                }
                if ('src' == $key) {
                    $value = $this->view->resourcePath($value);
                    if (empty($value)) continue;
                    $value = XOOPS::url($value);
                }
                $attrString .= sprintf(' %s="%s"', $key, ($this->_autoEscape) ? $this->_escape($value) : $value);
            }
        }

        $type = ($this->_autoEscape) ? $this->_escape($item->type) : $item->type;
        $html  = '<script type="' . $type . '"' . $attrString . '>';
        if (!empty($item->source)) {
              $html .= PHP_EOL . $indent . '    ' . $escapeStart . PHP_EOL . $item->source . $indent . '    ' . $escapeEnd . PHP_EOL . $indent;
        }
        $html .= '</script>';

        if (isset($item->attributes['conditional'])
            && !empty($item->attributes['conditional'])
            && is_string($item->attributes['conditional']))
        {
            $html = $indent . '<!--[if ' . $item->attributes['conditional'] . ']> ' . $html . '<![endif]-->';
        } else {
            $html = $indent . $html;
        }

        return $html;
    }
}

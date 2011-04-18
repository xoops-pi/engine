<?PHP
/**
 * XOOPS block model
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
 * @package         Xoops_Model
 * @version         $Id$
 */

class Xoops_Model_Block extends Xoops_Zend_Db_Model
{
    protected $_primary = "id";

    /**
     * Classname for row
     *
     * @var string
     */
    //protected $_rowClass = 'Xoops_Model_Block_Row';

    /**
     * return the content of the block for output
     *
     * @param array $block
     * @return mixed
     */
    public function buildBlock($block, $configs = array())
    {
        $isCustom = empty($block["module"]) ? true : false;
        // Module-generated block
        if (!$isCustom) {
            $render = $block["render"];
            $renderClass = '';
            if ($render) {
                list($renderClass, $method) = explode('::', $render);
            } elseif ($block["show_func"]) {
                $func = $block["show_func"];
                if (!function_exists($func)) {
                    include_once Xoops::path(Xoops::service('module')->getPath($block['module']) . '/blocks/' . $block['func_file']);
                }
            }
            if (empty($func) && empty($render)) {
                $result = false;
            } else {
                XOOPS::service('translate')->loadTranslation('blocks', $block['module']);
                $options = empty($block['options']) ? array() : unserialize($block['options']);
                if (!empty($configs) && is_array($configs)) {
                    $options = array_merge($options, $configs);
                }
                if ($render && class_exists($renderClass)) {
                    $result = $renderClass::$method($options, $block['module']);
                } else {
                    $options['module'] = $block['module'];
                    $result = $func($options);
                }
            }
        // Custom block or block compound
        } else {
            switch ($block['type']) {
                case 'C':
                    $options = empty($block['options']) ? array() : unserialize($block['options']);
                    if (!empty($configs) && is_array($configs)) {
                        $options = array_merge($options, $configs);
                    }
                    $renderClass = 'App\\System\\Block\\' . ucfirst($block['style']);
                    $renderClass::setModel($this);
                    $renderClass::setOptions($options);
                    $renderClass::setCompound($block['id']);
                    $result = array(
                        'content'   => $renderClass::render(),
                        'options'   => $renderClass::getOptions(),
                    );
                    break;
                case 'H':
                    $content = $block['content'];
                    $result = str_replace('{X_SITEURL}', XOOPS::url('www'), $content);
                    break;
                case 'T':
                default:
                    $content = Xoops\Security::escape($block['content']);
                    $result = str_replace('{X_SITEURL}', XOOPS::url('www'), $content);
                    break;
            }
        }

        return $result;
    }
}
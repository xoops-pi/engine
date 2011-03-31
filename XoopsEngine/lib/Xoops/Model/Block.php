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
            $func = $block["show_func"];
            if (empty($func)) {
                $result = false;
            } else {
                XOOPS::service('translate')->loadTranslation('blocks', $block['module']);
                if (!function_exists($func)) {
                    include_once Xoops::service('module')->getPath($block['module']) . '/blocks/' . $block['func_file'];
                }
                /*
                if (empty($block["show_func"])) {
                    $options = unserialize($block['options']);
                } else {
                    $options = explode('|', $block['options']);
                }
                */
                $options = empty($block['options']) ? array() : unserialize($block['options']);
                if (!empty($configs) && is_array($configs)) {
                    $options = array_merge($options, $configs);
                }
                $options['module'] = $block['module'];
                $result = $func($options);
            }
        // Custom block
        } else {
            switch ($block['type']) {
                case 'H':
                    $content = $block['content'];
                    break;
                case 'T':
                default:
                    $content = Xoops\Security::escape($block['content']);
                    break;
            }
            $result = str_replace('{X_SITEURL}', XOOPS::url('www'), $content);
        }

        return $result;
    }
}
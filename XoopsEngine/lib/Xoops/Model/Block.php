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
 * @copyright       The Xoops Engine http://sourceforge.net/projects/xoops/
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
     * return the content of the block for output
     *
     * @TODO: This method should be put at contoller layer
     *
     * @param array $block
     * @param string $format potential value:
     * <ul>
     * <li>S: for display
     * <li>E: for edit
     * <li>default: raw
     * </ul>
     * @return mixed
     */
    public function buildBlock($block, $format = 'S', $configs = array())
    {
        //global $xoops;

        // System-generated block
        if (empty($block["type"])) {
            switch (strtoupper($format)) {
            case 'E':
                $func = $block["edit_func"];
                break;
            case 'S':
            default:
                $func = $block["show_func"];
                break;
            }
            if (empty($func)) {
                $result = false;
            } else {
                XOOPS::service('translate')->loadTranslation('blocks', $block['module']);
                if (!function_exists($func)) {
                    //$info = XOOPS::service('registry')->module->read($block['module']);
                    include_once Xoops::service('module')->getPath($block['module']) . '/blocks/' . $block['func_file'];
                    //include_once XOOPS::path($info['path'] . '/' . $block['module'] . '/blocks/' . $block['func_file']);
                }
                $options = explode('|', $block['options']);
                if (!empty($configs) && is_array($configs)) {
                    $options = array_merge($options, $configs);
                }
                $options['module'] = $block['module'];
                $result = $func($options);
            }
        // Custom block
        } else {
            switch (strtoupper($format)) {
            case 'S':
                switch ($block['type']) {
                case 'H':
                    $content = $block['content'];
                    break;
                case 'P':
                    ob_start();
                    echo eval($block['content']);
                    $content = ob_get_contents();
                    ob_end_clean();
                    //$content = str_replace('{X_SITEURL}', $xoops->url('www'), $content);
                    break;
                case 'S':
                    Xoops_Legacy::autoload();
                    $myts = MyTextSanitizer::getInstance();
                    //$content = str_replace('{X_SITEURL}', $xoops->url('www'), $block['content']);
                    $content = $myts->displayTarea($block['content'], 0, 1);
                    break;
                default:
                    Xoops_Legacy::autoload();
                    $myts = MyTextSanitizer::getInstance();
                    //$content = str_replace('{X_SITEURL}', $xoops->url('www'), $this->getVar('content', 'N'));
                    $content = $myts->displayTarea($block['content'], 0, 0);
                    break;
                }
                $result["content"] = str_replace('{X_SITEURL}', XOOPS::url('www'), $content);
                break;
            case 'E':
                $result = Xoops\Security::escape($block['content']);
                break;
            default:
                $result = $block['content'];
                break;
            }
        }

        return $result;
    }
}
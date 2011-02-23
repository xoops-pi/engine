<?php
/**
 * TextSanitizer extension
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code 
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       The XOOPS Project http://sourceforge.net/projects/xoops/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @since           2.3.0
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @version         $Id: wiki.php 2007 2008-08-30 13:13:01Z phppp $
 * @package         class
 * @subpackage      textsanitizer
 */

    
class MytsWiki extends MyTextSanitizerExtension
{
    function encode($textarea_id)
    {
        $config = parent::loadConfig(dirname(__FILE__));
        $code = "<img src='{$this->image_path}/wiki.gif' alt='" . _XOOPS_FORM_ALTWIKI . "' onclick='xoopsCodeWiki(\"{$textarea_id}\",\"" . htmlspecialchars(_XOOPS_FORM_ENTERWIKITERM, ENT_QUOTES) . "\");'  onmouseover='style.cursor=\"hand\"'/>&nbsp;";
        $javascript = <<<EOH
            function xoopsCodeWiki(id, enterWikiPhrase){
                if (enterWikiPhrase == null) {
                    enterWikiPhrase = "Enter the word to be linked to Wiki:";
                }
                var selection = xoopsGetSelect(id);
                if (selection.length > 0) {
                    var text = selection;
                }else {
                    var text = prompt(enterWikiPhrase, "");
                }
                var domobj = xoopsGetElementById(id);
                if ( text != null && text != "" ) {
                    var result = "[[" + text + "]]";
                    xoopsInsertText(domobj, result);
                }
                domobj.focus();
            }
EOH;
        return array($code, $javascript);
    }
    
    function load(&$ts) 
    {
        $ts->patterns[] = "/\[\[([^\]]*)\]\]/esU";
        $ts->replacements[] = __CLASS__."::decode( '\\1' )"; 
    }
    
    function decode($text)
    {
        $config = parent::loadConfig( dirname(__FILE__) );
        if ( empty($text) || empty($config['link']) ) return $text;
        $charset = !empty($config['charset']) ? $config['charset'] : "UTF-8";
        xoops_load('xoopslocal');
        $ret = "<a href='" . sprintf( $config['link'], urlencode( XoopsLocal::convert_encoding($text, $charset) ) ) . "' rel='external' title=''>{$text}</a>";
        return $ret;
    }
}
?>
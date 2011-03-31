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

class Xoops_Zend_View_Helper_JQuery extends Zend_View_Helper_Placeholder_Container_Standalone
{
    /**
     * Registry key for placeholder
     * @var string
     */
    protected $_regKey = 'Xoops_Zend_View_Helper_JQuery';
    protected static $rootLoaded;

    public function jQuery($options = null)
    {
        //$version = isset($options["version"]) ? $options["version"] : null;
        $jQuery_root = "img/ajax/jquery";
        $options = (array) $options;
        if (empty(static::$rootLoaded)) {
            static::$rootLoaded = true;
            if (!in_array("jquery.min.js", $options)) {
                array_unshift($options, "jquery.min.js");
            }
        }
        foreach ($options as $file) {
            //$file = empty($file) ? "jquery.min.js" : $file;
            $fileExtension = substr($file, strrpos( $file, '.' ) + 1);
            if ($fileExtension == "css") {
                $this->view->headLink(array(
                    "href"  => $jQuery_root . "/" . $file,
                    "rel"   => "stylesheet",
                    "type"  => "text/css",
                ));
            } else {
                $this->view->headScript("file", $jQuery_root . "/" . $file);
            }
        }
        return $this;
    }
}
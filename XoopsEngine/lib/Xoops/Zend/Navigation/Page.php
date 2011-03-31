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
 * @package         Navigation
 * @version         $Id$
 */

abstract class Xoops_Zend_Navigation_Page extends Zend_Navigation_Page
{
    /**
     * Whether this page should be considered active
     *
     * @var bool
     */
    protected $_active = null;

    /**
     * Factory for Zend_Navigation_Page classes
     *
     * A specific type to construct can be specified by specifying the key
     * 'type' in $options. If type is 'uri' or 'mvc', the type will be resolved
     * to Zend_Navigation_Page_Uri or Zend_Navigation_Page_Mvc. Any other value
     * for 'type' will be considered the full name of the class to construct.
     * A valid custom page class must extend Zend_Navigation_Page.
     *
     * If 'type' is not given, the type of page to construct will be determined
     * by the following rules:
     * - If $options contains either of the keys 'action', 'controller',
     *   'module', or 'route', a Zend_Navigation_Page_Mvc page will be created.
     * - If $options contains the key 'uri', a Zend_Navigation_Page_Uri page
     *   will be created.
     *
     * @param  array|Zend_Config $options  options used for creating page
     * @return Zend_Navigation_Page        a page instance
     * @throws Zend_Navigation_Exception   if $options is not array/Zend_Config
     * @throws Zend_Exception              if 'type' is specified and
     *                                     Zend_Loader is unable to load the
     *                                     class
     * @throws Zend_Navigation_Exception   if something goes wrong during
     *                                     instantiation of the page
     * @throws Zend_Navigation_Exception   if 'type' is given, and the specified
     *                                     type does not extend this class
     * @throws Zend_Navigation_Exception   if unable to determine which class
     *                                     to instantiate
     */
    public static function factory($options)
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }

        if (!is_array($options)) {
            require_once 'Zend/Navigation/Exception.php';
            throw new Zend_Navigation_Exception(
                'Invalid argument: $options must be an array or Zend_Config');
        }

        if (isset($options['type'])) {
            $type = $options['type'];
            if (is_string($type) && !empty($type)) {
                switch (strtolower($type)) {
                    case 'mvc':
                        $type = 'Xoops_Zend_Navigation_Page_Mvc';
                        break;
                    case 'uri':
                        $type = 'Xoops_Zend_Navigation_Page_Uri';
                        break;
                    default:
                        $type = 'Xoops_Zend_Navigation_Page_Misc';
                        break;
                }

                if (!class_exists($type)) {
                }

                $page = new $type($options);
                if (!$page instanceof Zend_Navigation_Page) {
                    require_once 'Zend/Navigation/Exception.php';
                    throw new Zend_Navigation_Exception(sprintf(
                            'Invalid argument: Detected type "%s", which ' .
                            'is not an instance of Zend_Navigation_Page',
                            $type));
                }
                return $page;
            }
        }

        $hasUri = !empty($options['uri']);
        $hasMvc = !empty($options['action']) || !empty($options['controller']) ||
                  //!empty($options['module']) ||
                  !empty($options['route']);

        if ($hasUri) {
            //require_once 'Zend/Navigation/Page/Uri.php';
            //return new Zend_Navigation_Page_Uri($options);
            $class = "Xoops_Zend_Navigation_Page_Uri";
            return new $class($options);
        } elseif ($hasMvc) {
            //Debug::e($options["label"]);
            //Debug::e($options);

            $class = "Xoops_Zend_Navigation_Page_Mvc";
            return new $class($options);
        } else {
            //Debug::e($options);
            $class = "Xoops_Zend_Navigation_Page_Misc";
            return new $class($options);
            /*
            require_once 'Zend/Navigation/Exception.php';
            throw new Zend_Navigation_Exception(
                'Invalid argument: Unable to determine class to instantiate');
            */
        }
    }
}

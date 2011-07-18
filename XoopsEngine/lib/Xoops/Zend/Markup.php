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
 * @copyright       Xoops Engine http://www.xoopsengine.org/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @since           3.0
 * @category        Xoops_Zend
 * @package         Db
 * @version         $Id$
 */

class Xoops_Zend_Markup extends Zend_Markup
{
    protected $markups = array();
    protected $renderers = array();

    /**
     * Disable instantiation of Xoops_Zend_Markup
     */
    private function __construct() { }

    /**
     * Factory pattern
     *
     * @param  string $parser
     * @param  string $renderer
     * @param  array $options
     * @return Zend_Markup_Renderer_RendererAbstract
     */
    public static function factory($parser, $renderer = 'Html', array $options = array())
    {
        if (!empty($parser)) {
            $parserClass   = 'Zend_Markup_Parser_' . ucfirst($parser);
            if (class_exists('Xoops_' . $parserClass)) {
                $parserClass = 'Xoops_' . $parserClass;
            }
            $parser            = new $parserClass();
            $options['parser'] = $parser;
        }

        $rendererClass   = 'Zend_Markup_Renderer_' . ucfirst($renderer);
        if (class_exists('Xoops_' . $rendererClass)) {
            $rendererClass = 'Xoops_' . $rendererClass;
        }
        $renderer = new $rendererClass($options);

        return $renderer;
    }

    public static function addRenderer($renderer, array $options = array())
    {
        $parser = '';
        if (isset($options['parser'])) {
            $parser = $options['parser'];
            unset($options['parser']);
        }
        static::renderers[strtolower($renderer)] = static::factory($parser, $renderer, $options)
    }

    /**
     * Add a new markup to a renderer
     *
     * @param string $renderer
     * @param string $markup
     * @param string $type
     * @param array $options
     */
    public static function addMarkup($renderer, $markup, $type, array $options = array())
    {
        $renderer = static::getRenderer($renderer);
        if (!$renderer) {
            return false;
        }
        if (is_string($type)) {
            $type = constant('Zend_Markup_Renderer_RendererAbstract::TYPE_' . strtoupper($type));
        }
        $renderer->addMarkup($markup, $type, $options);
        return true;
    }

    public static function getRenderer($renderer)
    {
        return isset(static::renderers[strtolower($renderer)]) ? static::renderers[strtolower($renderer)] : null;
    }
}
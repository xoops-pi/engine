<?php
/**
 * Block compound abstract
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
 * @category        Xoops_Module
 * @package         System
 * @version         $Id$
 */

namespace App\System;

abstract class Blockcompound
{
    static protected $template = '';
    static protected $compound;
    static protected $view;
    static protected $model;
    static protected $options = array();

    public static function setView($view)
    {
        static::$view = $view;
    }

    public static function getView()
    {
        return static::$view ?: \Xoops::registry('view');
    }

    public static function setOptions($options)
    {
        static::$options = array_merge(static::$options, $options);
    }

    public static function getOptions()
    {
        return static::$options;
    }

    public static function setCompound($compound)
    {
        static::$compound = $compound;
    }

    public static function setModel($model)
    {
        static::$model = $model;
    }

    public static function getTemplate()
    {
        return static::$template;
    }

    public static function getElements($options = array())
    {
        $elements = array();
        $modelCompound = \Xoops::getModel('block_compound');
        $where = array('compound = ?' => static::$compound);
        $order = array('order ASC');
        $select = $modelCompound->select()->from($modelCompound, 'block')->where('compound = ?', static::$compound)->order($order);
        $blocks = $modelCompound->getAdapter()->fetchCol($select);

        if (empty($blocks)) {
            return $elements;
        }
        $modelBlock = \Xoops::getModel('block');
        $rows = $modelBlock->fetchAll(array('id IN (?)' => $blocks));
        foreach ($rows as $row) {
            if (!$row->active) {
                continue;
            }
            $elements[$row->id] = static::getView()->Block($row, $options);
        }
        return $elements;
    }

    public static function render($options)
    {
        return;
    }

    public static function buildOptions($form)
    {
        return $form;
    }
}
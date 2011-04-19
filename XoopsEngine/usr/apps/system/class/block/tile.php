<?php
/**
 * Block compound tile style
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

namespace App\System\Block;

class Tile extends \App\System\Blockcompound
{
    static protected $options = array(
        'blocks_per_row'    => 1,
        'merge'             => 0,
    );

    static protected $template = 'compound_tile.html';

    public static function render()
    {
        $options = static::getOptions();
        $isMerged = empty($options['merge']) ? true : false;

        $elements = static::getElements();

        return $elements;
    }

    public static function buildOptions($form)
    {
        $options = static::getOptions();

        // Number of blocks on one row
        $opt = array(
            'label'         => 'Blocks per row',
            'value'         => $options['blocks_per_row'] ?: 1,
        );
        $form->addElement('Text', 'blocks_per_row', $opt);

        // To merge contents?
        $opt = array(
            'label'         => 'Merge blocks',
            'value'         => $options['merge'],
        );
        $form->addElement('Checkbox', 'merge', $opt);

        return $form;
    }
}
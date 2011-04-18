<?php
/**
 * Block compound cascade style
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

class Cascade extends \App\System\Blockcompound
{
    static protected $options = array(
        'switch_mode'   => 'auto',  // Potental values: auto, hover, click
        'switch_time'   => '3',       // Time interval for time switch, in seconds
    );

    static protected $template = 'compound_cascade.html';

    public static function render()
    {
        //$options = static::getOptions();
        //$isMerged = empty($options['merge']) ? true : false;

        $elements = static::getElements();

        return $elements;
    }

    public static function buildOptions($form)
    {
        $options = static::getOptions();

        // Blocks on one row
        $opt = array(
            'label'         => 'Switch mode',
            'value'         => $options['switch_mode'],
            'multiOptions'  => array(
                'auto'  => 'Auto switch',
                'hover' => 'Mouse hover',
                'click' => 'On click',
            ),
        );
        $form->addElement('Select', 'switch_mode', $opt);

        // Time interval for auto mode
        $opt = array(
            'label'         => 'Time interval for auto mode',
            'value'         => $options['switch_time'],
            'multiOptions'  => array(
                '1'     => '1 second',
                '2'     => '2 seconds',
                '3'     => '3 seconds',
                '5'     => '5 seconds',
                '10'    => '10 seconds',
            ),
        );
        $form->addElement('Select', 'switch_time', $opt);

        return $form;
    }
}
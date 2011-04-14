<?php
/**
 * Demo module block specs
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
 * @package         Demo
 * @version         $Id$
 */

// Block list
return array(
    // Block with options and template
    'block-a'   => array(
        'title'         => _DEMO_MI_BLOCK_FIRST,
        'description'   => _DEMO_MI_BLOCK_FIRST_DESC,
        'render'        => "block::blocka",
        'template'      => 'block-a.html',
        'options'       => array(
            // text option
            'first' => array(
                'title'         => "_DEMO_MB_OPTION_FIRST",
                'description'   => "_DEMO_MB_OPTION_FIRST_DESC",
                'edit'          => "text",
                'filter'        => "string",
                'default'       => "Demo option 1",
            ),
            // Yes or No option
            'second'    => array(
                'title'         => "_DEMO_MB_OPTION_SECOND",
                'description'   => "_DEMO_MB_OPTION_SECOND_DESC",
                'edit'          => "yesno",
                'filter'        => "number_int",
                'default'       => 0
            ),
        ),
    ),
    // Block with custom options and template
    'block-b'   => array(
        'title'         => _DEMO_MI_BLOCK_SECOND,
        'description'   => _DEMO_MI_BLOCK_SECOND_DESC,
        'render'        => "block::blockb",
        'template'      => 'block-b.html',
        'options'       => array(
            // select option
            'third' => array(
                'title'         => "_DEMO_MB_OPTION_THIRD",
                'description'   => "_DEMO_MB_OPTION_THIRD_DESC",
                'edit'          => "select",
                'filter'        => "string",
                'default'       => "one",
                'options'       => array(
                    "_DEMO_MB_OPTION_THIRD_ONE"     => "one",
                    "_DEMO_MB_OPTION_THIRD_TWO"     => "two",
                    "_DEMO_MB_OPTION_THIRD_THREE"   => "three"
                )
            ),
            // module custom field option, defined in app/demo/class/form/element/choose.php
            'fourth'    => array(
                'title'         => "_DEMO_MB_OPTION_FOURTH",
                'description'   => "_DEMO_MB_OPTION_FOURTH_DESC",
                'edit'          => array('module' => 'demo', 'type' => 'choose'),
                'filter'        => "string",
                'default'       => "",
            ),
        ),
    ),
    // Block with mixed options and template
    'block-c'   => array(
        'file'          => "blocks.php",
        'title'         => _DEMO_MI_BLOCK_THIRD,
        'description'   => _DEMO_MI_BLOCK_THIRD_DESC,
        'show_func'     => "demo_block_show",
        'edit_func'     => "demo_block_edit",
        'template'      => 'block-c.html',
        'options'       => '31|threetwo'
    ),
    // Simple block w/o option, no template
    'block-d'   => array(
        'title'         => _DEMO_MI_BLOCK_FOURTH,
        'description'   => _DEMO_MI_BLOCK_FOURTH_DESC,
        'render'        => "block::random",
    ),
);
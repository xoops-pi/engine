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
 * @package         Cache
 * @version         $Id$
 */

class Xoops_Zend_Cache_Backend_File extends Zend_Cache_Backend_File
{
    public function __construct(array $options = array())
    {
        if (!isset($options['cache_dir'])) {
            $options['cache_dir'] = XOOPS::path('var') . '/cache/system';
        }
        if (!isset($options['file_name_prefix'])) {
            $options['file_name_prefix'] = XOOPS::config('identifier') . '_cache';
        }
        parent::__construct($options);
    }
}
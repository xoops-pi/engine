<?php
/**
 * Demo module monitor class
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

namespace App\Demo;

class Monitor
{
    /**
     * Returns monitoring data for admin dashboard
     *
     * @param string $module dirname for module
     * @param string $redirect redirect URI after callback
     * @return array associative array of monitoring items: title, data, callback url
     */
    public static function index($module = null, $redirect = null)
    {
        \Xoops::service('translate')->loadTranslation('monitor', $module);
        $data = array();
        $data[] = array(
            'message'   => \Xoops::_('_DEMO_MT_INSTRUCTION_AVAILABLE'),
        );
        $model = \Xoops::service('module')->getModel('test', $module);
        $select = $model->select()->where('active = ?', 1);
        $count = $model->fetchAll($select)->count();
        if ($count > 0) {
            $data[] = array(
                'message'   => sprintf(\Xoops::_('_DEMO_MT_TASKS_PENDING'), $count),
                'callback'  => \XOOPS::registry("frontController")->getRouter()->assemble(
                    array(
                        'module'        => $module,
                        'controller'    => 'monitor',
                        'action'        => 'reset',
                        'redirect'      => $redirect,
                    ),
                    'admin'
                ),
            );
        }

        return $data;
    }
}
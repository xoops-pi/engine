<?php
/**
 * See the enclosed file license.txt for licensing information.
 * If you did not receive this file, get it at http://www.fsf.org/copyleft/gpl.html
 *
 * @copyright       Xoops Engine http://www.xoopsengine.org
 * @license         BSD License
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @package         installer
 * @since           3.0
 * @version         $Id$
 */

$pages = array(
    'presetting'        => array(
                        'title' => _INSTALL_PAGE_PRESETTING,
                        'desc'  => _INSTALL_PAGE_PRESETTING_DESC),
    'directive'         => array(
                        'title' => _INSTALL_PAGE_DIRECTIVE,
                        'desc'  => _INSTALL_PAGE_DIRECTIVE_DESC),
    'database'          => array(
                        'title' => _INSTALL_PAGE_DATABASE,
                        'desc'  => _INSTALL_PAGE_DATABASE_DESC),
    'admin'             => array(
                        'title' => _INSTALL_PAGE_ADMIN,
                        'desc'  => _INSTALL_PAGE_ADMIN_DESC),
    'finish'            => array(
                        'title' => _INSTALL_PAGE_FINISH,
                        'desc'  => _INSTALL_PAGE_FINISH_DESC,
                        'hide'  => true,
                        ),
);

return $pages;
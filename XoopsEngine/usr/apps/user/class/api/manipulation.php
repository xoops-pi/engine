<?php
/**
 * User module API class: user manipulation
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
 * @package         User
 * @version         $Id$
 */

namespace App\User;

class Api\Manipulation
{
    /**
     * Create a user and populate corresponding profile/data
     *
     * @param array $data   associative array of user data
     * @param array $message message list
     * @return mixed    user ID on success; false on failure
     */
    public function create($data, &$message = null)
    {
        return \App\UserGateway::create($data, $message);
    }

    /**
     * Update a user
     *
     * @param array $data   associative array of user data
     * @param array $message message list
     * @return boolean
     */
    public function update($data, &$message = null)
    {
        return \App\UserGateway::update($data, $message);
    }

    /**
     * Delete a user
     *
     * @param int   $id   user ID
     * @param array $message message list
     * @return boolean
     */
    public function delete($id, &$message = null)
    {
        return \App\UserGateway::delete($id, $message);
    }

    /**
     * Save user's data
     *
     * It is recommended to use User_Api_Manipulation::create or User_Api_Manipulation::update explicitly
     *
     * @param array $data   associative array of user data
     * @param array $message message list
     * @return mixed    user ID on success; false on failure
     */
    public function save($data, &$message = null)
    {
        return \App\UserGateway::save($data, $message);
    }
}
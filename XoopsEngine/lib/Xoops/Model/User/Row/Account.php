<?php
/**
 * XOOPS user account model row
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
 * @package         Xoops_Model
 * @version         $Id$
 */

class Xoops_Model_User_Row_Account extends Xoops_Zend_Db_Table_Row
{
    /**
     * Allows pre-insert logic to be applied to row.
     * Subclasses may override this method.
     *
     * @return void
     */
    protected function _insert()
    {
        if (isset($this->_modifiedFields["credential"])) {
            $this->createCredential();
        }
    }

    /**
     * Allows pre-update logic to be applied to row.
     * Subclasses may override this method.
     *
     * @return void
     */
    protected function _update()
    {
        if (isset($this->_modifiedFields["credential"])) {
            $this->createCredential();
        }
    }

    /**
     * Create salt for credential hash
     */
    protected function createSalt()
    {
        $this->salt = uniqid(mt_rand(), 1);
    }

    /**
     * Create salt and transform credential upon raw data
     *
     * @param string    $credential     Credential
     * @param string    $salt           Salt
     */
    protected function createCredential()
    {
        $this->createSalt();
        $this->credential = $this->transformCredential($this->credential);
    }

    /**
     * Transform credential upon raw data
     *
     * @param string    $credential     Credential
     * @param string    $salt           Salt
     * @return string treated credential value
     */
    public function transformCredential($credential)
    {
        $credential = md5($this->salt . $credential);
        return $credential;
    }
}

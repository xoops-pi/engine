<?PHP
/**
 * XOOPS audit trail model
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
/**
 * <ul>Database structure
 *      <li>`section`: varchar(64), front or admin</li>
 *      <li>`module`: varchar(64)</li>
 *      <li>`controller`: varchar(64)</li>
 *      <li>`action`: varchar(64)</li>
 *      <li>`method`: varchar(64)</li>
 *      <li>`time`: int(10), time of the event</li>
 *      <li>`user`: varchar(64), username</li>
 *      <li>`ip`: varchar(15), IP of the operator</li>
 *      <li>`memo`: varchar(255), custom information</li>
 *      <li>`extra`: varchar(255), extra information</li>
 * </ul>
 */

class Xoops_Model_Audit extends Xoops_Zend_Db_Model
{
    protected $_primary = "id";
}
<?PHP
/**
 * XOOPS system update model
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
 *      <li>`uri`: varchar(255)</li>
 *      <li>`time`: int(10), time of the record</li>
 *      <li>`content`: text, extra information</li>
 * </ul>
 */

class Xoops_Model_Update extends Xoops_Zend_Db_Model
{
    protected $_name = "system_update";

    public function insert($data)
    {
        if (!isset($data["time"])) {
            $data["time"] = time();
        }
        if (isset($data["params"]) && is_array($data["params"])) {
            $data["params"] = http_build_query($data["params"]);
        }

        return parent::insert($data);
    }
}
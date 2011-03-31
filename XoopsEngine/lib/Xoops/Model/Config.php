<?PHP
/**
 * XOOPS config model
 *
 * The config utilizes {@link http://en.wikipedia.org/wiki/Entity-attribute-value_model EAV model}
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

class Xoops_Model_Config extends Xoops_Zend_Db_Model
{
    protected $_primary = "id";

    /**
     * Classname for row
     *
     * @var string
     */
    protected $_rowClass = 'Xoops_Model_Config_Row';
}
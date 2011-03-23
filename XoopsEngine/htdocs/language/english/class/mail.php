<?php
/**
 * XOOPS mail locale handler
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Xoops Engine http://www.xoopsengine.org
 * @license         http://www.opensource.org/licenses/bsd-license.php BSD liscense
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @since           3.0
 * @package         xoops_Core
 * @version         $Id$
 */

class Xoops_English_Mail extends Xoops_Zend_Mail_Locale
{
    /**
     * Locale handler
     */
    protected static $locale = "english";

    /**
     * Public constructor
     *
     * @param string $charset
     */
    public function __construct($charset = null)
    {
        $this->_charset = $charset;
    }
}
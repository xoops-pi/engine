<?php
/**
 * XOOPS registry std class
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
 * @package         Xoops_Core
 * @subpackage      Registry
 * @version         $Id$
 */

namespace Engine\Xoops\Registry;

class Std extends \Kernel\Registry
{
    public function setKey($key)
    {
        $this->registry_key = "";
        return $this;
    }

    public function read()
    {
    }

    public function create()
    {
    }

    public function delete($options = array())
    {
        return $this->cache->clean('matchingTag', self::createTags($options));
    }

    public function flush($options = array())
    {
        return $this->delete($options);
    }
}
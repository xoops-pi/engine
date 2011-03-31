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
 * @package         Controller
 * @version         $Id$
 */

class Xoops_Zend_Controller_Action_Helper_Audit extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * @var Xoops_Zend_Controller_Plugin_Audit
     */
    protected $auditPlugin;

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct($auditPlugin = null)
    {
        $this->auditPlugin = $auditPlugin;
    }

    public function log()
    {
        call_user_func_array(array($this->getAuditPlugin(), "log"), func_get_args());
    }

    /**
     * Returns the audit Plugin object
     *
     * @return Xoops_Zend_Controller_Plugin_Audit
     */
    public function getAuditPlugin()
    {
        if (null === $this->auditPlugin) {
            $front = XOOPS::registry('frontController');
            if (!$front->hasPlugin('Xoops_Zend_Controller_Plugin_Audit')) {
                $front->registerPlugin(new Xoops_Zend_Controller_Plugin_Audit());
            }
            $this->auditPlugin = $front->getPlugin('Xoops_Zend_Controller_Plugin_Audit');
        }

        return $this->auditPlugin;
    }
}
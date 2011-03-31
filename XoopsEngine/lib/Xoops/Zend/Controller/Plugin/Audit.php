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

class Xoops_Zend_Controller_Plugin_Audit extends Zend_Controller_Plugin_Abstract
{
    protected $audit;
    private $request;
    private $isLogged = false;
    private $params = null;
    /**
     * Options for recording:
     * users - specific users to be logged
     * ips - specific IPs to be logged
     * roles - specific roles to be logged
     * pages - specific pages to be logged
     * methods - specific request methods to be logged
     */
    protected $options = array();

    /**
     * Constructor
     *
     * @param array $options
     * @return void
     */
    public function __construct($options = array())
    {
        if (is_array($options)) {
            $this->options = $options;
        }
    }

    /**
     * Sets the audit object
     *
     * @param object {@link Xoops_model_audit}
     * @return void
     */
    public function setAudit(Xoops_model_audit $audit)
    {
        $this->audit = $audit;
    }

    /**
     * Returns the audit object
     *
     * @return {@link Xoops_model_audit}
     */
    public function getAudit()
    {
        if (!isset($this->audit)) {
            $this->audit = XOOPS::getModel("audit");
        }

        return $this->audit;
    }

    /**
     * preDispatch
     *
     * @return void
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        if ($this->isLogged) return;

        $this->isLogged = true;
        $this->request = $request;
        $this->response = XOOPS::registry("frontController")->getResponse();
        $this->log();
    }

    /**
     * postDispatch
     *
     * Currently we can not use postDispatch due improper usage of action::redirect
     *
     * @return void
     */
    public function ____postDispatch(Zend_Controller_Request_Abstract $request)
    {
        $this->request = $request;
        $this->response = XOOPS::registry("frontController")->getResponse();
        $this->log();
    }

    /**
     * Logging audit trail
     *
     * <url> Columns to record
     *      <li>`section`: varchar(64), front or admin</li>
     *      <li>`module`: varchar(64)</li>
     *      <li>`controller`: varchar(64)</li>
     *      <li>`action`: varchar(64)</li>
     *      <li>`time`: int(10), time of the event</li>
     *      <li>`user`: varchar(64), username</li>
     *      <li>`ip`: varchar(15), IP of the operator</li>
     *      <li>`memo`: varchar(255), custom information</li>
     *      <li>`extra`: varchar(255), extra information</li>
     * </ul>
     */
    public function log($memo = "")
    {
        if (!empty($this->options["disable"]) || $this->response->isRedirect() || $this->response->isException()) {
            return;
        }
        $params = $this->getParams();
        if ($params === false) {
            return;
        }

        $data = array(
            "time"  => time(),
            "memo"  => $memo
        );
        $data = array_merge($params, $data);
        $this->getAudit()->insert($data);
    }

    private function getParams()
    {
        if (!is_null($this->params)) {
            return $this->params;
        }
        $data = array();
        if (!empty($this->options["roles"])) {
            if (!in_array(XOOPS::registry("user")->role, $this->options["roles"])) {
                $this->params = false;
                return $this->params;
            }
        }
        $data["user"] = XOOPS::registry("user")->identity;
        if (!empty($this->options["users"])) {
            if (!in_array($data["user"], $this->options["users"])) {
                $this->params = false;
                return $this->params;
            }
        }
        $data["ip"] = $this->request->getClientIp();
        if (!empty($this->options["ips"])) {
            $segs = explode(".", $data["ip"]);
            if (!in_array($segs[0] . ".*", $this->options["ips"])
                && !in_array($segs[0] . "." . $segs[1] . ".*", $this->options["ips"])
                && !in_array($segs[0] . "." . $segs[1] . "." . $segs[2] . ".*", $this->options["ips"])
                && !in_array($segs[0] . "." . $segs[1] . "." . $segs[2] . "." . $segs[3], $this->options["ips"])
            ) {
                $this->params = false;
                return $this->params;
            }
        }
        $data["method"] = $this->request->getMethod();
        if (!empty($this->options["methods"])) {
            if (!in_array($data["method"], $this->options["methods"])) {
                $this->params = false;
                return $this->params;
            }
        }
        $data["module"] = $this->request->getModuleName();
        $data["controller"] = $this->request->getControllerName();
        $data["action"] = $this->request->getActionName();
        if (!empty($this->options["pages"])) {
            if (!in_array($data["module"], $this->options["pages"])
                && !in_array($data["module"] . "-" . $data["controller"], $this->options["pages"])
                && !in_array($data["module"] . "-" . $data["controller"] . "-" . $data["action"], $this->options["pages"])
            ) {
                $this->params = false;
                return $this->params;
            }
        }
        $data["section"] = XOOPS::registry("frontController")->getParam("section");
        $data["extra"] = $this->request->isPost()
                            ? $this->request->getRawBody()
                            : $this->request->getRequestUri();

        $this->params = $data;
        return $this->params;
    }
}
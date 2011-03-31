<?PHP
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

class Xoops_Zend_Controller_Action_Api extends Xoops_Zend_Controller_Action
{
    //protected $format;
    protected $data = array();

    public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array())
    {
        parent::__construct($request, $response, $invokeArgs);
        $this->setTemplate("");
    }

    public function postDispatch()
    {
        $this->format = $this->getRequest()->getParam("format", "xml");

        if ($this->getResponse()->getHttpResponseCode() != 200) {
            switch ($this->getRequest()->getParam("action")) {
                case "post":
                    $this->getResponse()->setHttpResponseCode(201);
                    break;
                case "put":
                    //$this->getResponse()->setHttpResponseCode(201);
                    break;
                case "delete":
                    $this->getResponse()->setHttpResponseCode(204);
                    break;
                default:
                    break;
            }
        }

        $domObject = $this->getDom($this->data);
        switch (strtoupper($this->format)) {
            case "JSON":
                $this->getResponse()->setHeader("Content-Type", "application/json")->setBody(Zend_Json::fromXML($domObject->saveXML()));
                break;
            case "HTML":
                $this->getResponse()->setHeader("Content-Type", "text/plain")->setBody($domObject->saveHTML());
                break;
            case "XML":
            default:
                $contentType = (Xoops::config('environment') == 'production') ? "text/xml" : "text/plain";
                $this->getResponse()->setHeader("Content-Type", $contentType)->setBody($domObject->saveXML());
                break;
        }
    }

    protected function getDom($data)
    {
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->formatOutput = true;
        $rootElement = $doc->createElement("response");
        $doc->appendChild($rootElement);
        foreach ($data as $key => $value) {
            $currentElement = $doc->createElement($key);
            if (!is_array($value)) {
                $currentElement->appendChild($doc->createTextNode($value));
                $rootElement->appendChild($currentElement);
            } else {
                $this->xmlHelper($doc, $rootElement, $currentElement, $value);
            }
        }
        return $doc;
    }

    protected function xmlHelper($doc, $rootElement, $currentElement, $value)
    {
        if (is_array($value)) {
            foreach ($value as $key => $val) {
                if (is_array($val)) {
                    $this->xmlHelper($doc, $rootElement, $currentElement, $val);
                } else {
                    $element = $doc->createElement($key);
                    $element->appendChild($doc->createTextNode($val));
                    $currentElement->appendChild($element);
                    $rootElement->appendChild($currentElement);
                }
            }
        } else {
            $currentElement->appendChild($doc->createTextNode($value));
            $rootElement->appendChild($currentElement);
        }
    }


    protected function denied()
    {
        $this->getResponse()->setHttpResponseCode(403);
        $this->data = 'denied';
    }

    // Handle GET and return a list of resources
    public function indexAction()
    {
        $this->denied();
    }

    // Handle GET and return a specific resource item
    public function getAction()
    {
        $this->denied();
    }

    // Handle POST requests to create a new resource item
    public function postAction()
    {
        $this->denied();
    }

    // Handle PUT requests to update a specific resource item
    public function putAction()
    {
        $this->denied();
    }

    // Handle DELETE requests to delete a specific item
    public function deleteAction()
    {
        $this->denied();
    }
}
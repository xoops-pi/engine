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

class Xoops_Zend_Controller_Action_Xml extends Xoops_Zend_Controller_Action
{
    protected $xml;
    protected $domObject;
    //protected $elements;

    public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array())
    {
        parent::__construct($request, $response, $invokeArgs);
        $this->setTemplate("");
    }

    protected function createElement()
    {
        $element = new stdClass();
        return $element;
    }

    public function __get($name)
    {
        $resturn = null;
        if ($name == "xml") {
            if (!isset($this->xml)) {
                $this->domObject = new DOMDocument('1.0', 'UTF-8');
                $this->domObject->formatOutput = true;
            }

            $return = $this->domObject;
        }

        return $resturn;
    }

    public function preDispatch()
    {
        $this->xml = new stdClass();
    }

    public function postDispatch()
    {
        $this->domObject = new DOMDocument('1.0', 'UTF-8');
        $this->domObject->formatOutput = true;
        //Debug::e($this->domObject);
        $this->appendElement($this->xml);
        /*
        $reflection = new ReflectionObject($this->xml);
        $properties = $reflection->getProperties();
        foreach ($properties as $property) {
            $element = $this->appendElement(
                $property->getName(),
                $property->getValue($this->xml)
            );
            $this->domObject->appendChild($property->getName(), $element);
        }
        */
        echo $this->domObject->saveXML();
    }

    protected function appendElement($name, $value = null)
    {
        if (is_object($name)) {
            $reflection = new ReflectionObject($name);
            $properties = $reflection->getProperties();
            foreach ($properties as $property) {
                $element = $this->appendElement(
                    $property->getName(),
                    $property->getValue($name)
                );
                $this->domObject->appendChild($element);
            }
        } elseif (is_object($value)) {
            $element = $this->domObject->createElement($name);
            $reflection = new ReflectionObject($value);
            $properties = $reflection->getProperties();
            foreach ($properties as $property) {
                $element->appendChild(
                    $this->appendElement(
                        $property->getName(),
                        $property->getValue($value)
                    )
                );
            }
        } else {
            //Debug::e("{$name} - {$value}");
            $element = $this->domObject->createElement(
                $name,
                (string) $value
            );
        }

        return $element;
    }
}
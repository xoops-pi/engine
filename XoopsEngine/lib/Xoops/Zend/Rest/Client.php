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
 * @copyright       Xoops Engine http://www.xoopsengine.org
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @since           3.0
 * @category        Xoops_Zend
 * @package         Rest
 * @version         $Id$
 */

class Xoops_Zend_Rest_Client extends Zend_Rest_Client
{
    /**
     * Method call overload
     *
     * Allows calling REST actions as object methods; however, you must
     * follow-up by chaining the request with a request to an HTTP request
     * method (post, get, delete, put):
     * <code>
     * $response = $rest->sayHello('Foo', 'Manchu')->get();
     * </code>
     *
     * Or use them together, but in sequential calls:
     * <code>
     * $rest->sayHello('Foo', 'Manchu');
     * $response = $rest->get();
     * </code>
     *
     * @param string $method Method name
     * @param array $args Method args
     * @return Zend_Rest_Client_Result|Zend_Rest_Client Zend_Rest_Client if using
     * a remote method, Zend_Rest_Client_Result if using an HTTP request method
     */
    public function __call($method, $args)
    {
        $methods = array('post', 'get', 'delete', 'put');

        if (in_array(strtolower($method), $methods)) {
            if (!isset($args[0])) {
                $args[0] = $this->_uri->getPath();
            }
            $this->_data['rest'] = 1;
            $data = array_slice($args, 1) + $this->_data;
            // Since Zend_Http_Client is encapulated in such a way that its exception handling is not able to customize, we have to use the following tricky solution to silent exceptions
            try {
                $response = $this->{'rest' . $method}($args[0], $data);
                $responseData = $response->getBody();
            } catch (Zend_Http_Client_Adapter_Exception $e) {
                $message = $e->getMessage();
                $responseData = "<?xml version='1.0' encoding='utf-8'?><error>" . strip_tags($message) . "</error>";
            } catch (Zend_Http_Client_Exception $e) {
                $message = $e->getMessage();
                $responseData = "<?xml version='1.0' encoding='utf-8'?><error>" . strip_tags($message) . "</error>";
            }
            $this->_data = array();//Initializes for next Rest method.
            return new Xoops_Zend_Rest_Client_Result($responseData);
        } else {
            // More than one arg means it's definitely a Zend_Rest_Server
            if (sizeof($args) == 1) {
                // Uses first called function name as method name
                if (!isset($this->_data['method'])) {
                    $this->_data['method'] = $method;
                    $this->_data['arg1']  = $args[0];
                }
                $this->_data[$method]  = $args[0];
            } else {
                $this->_data['method'] = $method;
                if (sizeof($args) > 0) {
                    foreach ($args as $key => $arg) {
                        $key = 'arg' . $key;
                        $this->_data[$key] = $arg;
                    }
                }
            }
            return $this;
        }
    }
}

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

class Xoops_Zend_Controller_Action_Feed extends Xoops_Zend_Controller_Action
{
    //protected $type;
    protected $feed;
    protected $defaults;

    /**
     * Class constructor
     *
     * The request and response objects should be registered with the
     * controller, as should be any additional optional arguments; these will be
     * available via {@link getRequest()}, {@link getResponse()}, and
     * {@link getInvokeArgs()}, respectively.
     *
     * When overriding the constructor, please consider this usage as a best
     * practice and ensure that each is registered appropriately; the easiest
     * way to do so is to simply call parent::__construct($request, $response,
     * $invokeArgs).
     *
     * After the request, response, and invokeArgs are set, the
     * {@link $_helper helper broker} is initialized.
     *
     * Finally, {@link init()} is called as the final action of
     * instantiation, and may be safely overridden to perform initialization
     * tasks; as a general rule, override {@link init()} instead of the
     * constructor to customize an action controller's instantiation.
     *
     * @param Zend_Controller_Request_Abstract $request
     * @param Zend_Controller_Response_Abstract $response
     * @param array $invokeArgs Any additional invocation arguments
     * @return void
     */
    public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array())
    {
        parent::__construct($request, $response, $invokeArgs);
        //$this->setTemplate("");
    }

    protected function loadView()
    {
    }

    public function preDispatch()
    {
        //global $xoops, $xoopsConfig;

        $this->feed = array(
                'title'       => Xoops::config("sitename"), //'title of the feed', //required
                'link'        => Xoops::url("www", true), //'canonical url to the feed', //required
                'lastUpdate'  => null, //'timestamp of the update date', // optional
                'published'   => null, //'timestamp of the publication date', //optional
                'charset'     => "UTF-8", //'charset', // required
                'description' => null, //'short description of the feed', //optional
                'author'      => null, //'author/publisher of the feed', //optional
                'email'       => null, //'email of the author', //optional
                'webmaster'   => null, //'email address for person responsible for technical issues' // optional, ignored if atom is used
                'copyright'   => null, //'copyright notice', //optional
                'image'       => null, //'url to image', //optional
                'generator'   => "XOOPS Feed", //'generator', // optional
                'language'    => XOOPS::config('language'), //'language the feed is written in', // optional
                'ttl'         => null, //'how long in minutes a feed can be cached before refreshing', // optional, ignored if atom is used
                'rating'      => null, //'The PICS rating for the channel.', // optional, ignored if atom is used
                );
        $this->defaults = array(
            "cloud" => array(
                'domain'            => null, //'domain of the cloud, e.g. rpc.sys.com' // required
                'port'              => null, //'port to connect to' // optional, default to 80
                'path'              => null, //'path of the cloud, e.g. /RPC2 //required
                'registerProcedure' => null, //'procedure to call, e.g. myCloud.rssPleaseNotify' // required
                'protocol'          => null, //'protocol to use, e.g. soap or xml-rpc' // required
                ), //a cloud to be notified of updates // optional, ignored if atom is used
            "textInput" => array(
                'title'       => null, //'the label of the Submit button in the text input area' // required,
                'description' => null, //'explains the text input area' // required
                'name'        => null, //'the name of the text object in the text input area' // required
                'link'        => null, //'the URL of the CGI script that processes text input requests' // required
                ), // a text input box that can be displayed with the feed // optional, ignored if atom is used
            "skipHours" => array(
                null, //'hour in 24 format', // e.g 13 (1pm)
                // up to 24 rows whose value is a number between 0 and 23
                ), // Hint telling aggregators which hours they can skip // optional, ignored if atom is used
            'skipDays '   => array(
                null, //'a day to skip', // e.g Monday
                // up to 7 rows whose value is a Monday, Tuesday, Wednesday, Thursday, Friday, Saturday or Sunday
                ), // Hint telling aggregators which days they can skip // optional, ignored if atom is used
            "itunes"    => array(
                'author'       => null, //'Artist column' // optional, default to the main author value
                'owner'        => array(
                                  'name' => null, //'name of the owner' // optional, default to main author value
                                  'email' => null, //'email of the owner' // optional, default to main email value
                                  ), // Owner of the podcast // optional
                'image'        => null, //'album/podcast art' // optional, default to the main image value
                'subtitle'     => null, //'short description' // optional, default to the main description value
                'summary'      => null, //'longer description' // optional, default to the main description value
                'block'        => null, //'Prevent an episode from appearing (yes|no)' // optional
                'category'     => array(
                                array('main' => null, //'main category', // required
                                      'sub'  => null, //'sub category' // optional
                                  ),
                                  // up to 3 rows
                                  ), // 'Category column and in iTunes Music Store Browse' // required
                'explicit'     => null, //'parental advisory graphic (yes|no|clean)' // optional
                'keywords'     => null, //'a comma separated list of 12 keywords maximum' // optional
                'new-feed-url' => null, //'used to inform iTunes of new feed URL location' // optional
                ), // Itunes extension data // optional, ignored if atom is used
            "entry"     => array(
                'title'        => null, //'title of the feed entry', //required
                'link'         => null, //'url to a feed entry', //required
                'description'  => null, //'short version of a feed entry', // only text, no html, required
                'guid'         => null, //'id of the article, if not given link value will used', //optional
                'content'      => null, //'long version', // can contain html, optional
                'lastUpdate'   => null, //'timestamp of the publication date', // optional
                'comments'     => null, //'comments page of the feed entry', // optional
                'commentRss'   => null, //'the feed url of the associated comments', // optional
                ),
            'entry-extra'  => array(
                'source'  => array(
                    'title' => null, //'title of the original source' // required,
                    'url' => null, //'url of the original source' // required
                    ), // original source of the feed entry // optional
                'category'    => array(
                    'term' => null, //'first category label' // required,
                    'scheme' => null, //'url that identifies a categorization scheme' // optional
                    ), // list of the attached categories // optional
                'enclosure'    => array(
                    'url' => null, //'url of the linked enclosure' // required
                    'type' => null, //'mime type of the enclosure' // optional
                    'length' => null, //'length of the linked content in octets' // optional
                    ), // list of the enclosures of the feed entry // optional
            ),
        );
    }

    public function postDispatch()
    {
        $type = $this->getRequest()->getParam("type", "rss");
        $feed = Zend_Feed::importArray($this->feed, $type);
        echo $feed->saveXML();
    }

    protected function feed($var, $val = null)
    {
        if (is_array($var)) {
            foreach ($var as $key => $val) {
                $this->setFeed($key, $val);
            }
        } else {
            $this->setFeed($var, $val);
        }

        return $this;
    }

    protected function setFeed($var, $val)
    {
        if (array_key_exists($var, $this->feed)) {
            $this->feed[$var] = $val;
        } elseif (array_key_exists($var, $this->defaults)) {
            foreach ($val as $key => $value) {
                $this->feed[$var][$key] = $value;
            }
        }

        return $this;
    }

    protected function entry($data)
    {
        $entry = $this->defaults["entry"];
        $extra = $this->defaults["entry-extra"];
        foreach ($data as $var => $val) {
            if (array_key_exists($var, $entry)) {
                $entry[$var] = $val;
            } elseif (array_key_exists($var, $extra)) {
                $entry[$var] = array_merge($extra[$var], $val);
            }
        }
        //Debug::e($data);
        //Debug::e($entry);
        $this->feed["entries"][] = $entry;

        return $this;
    }
}
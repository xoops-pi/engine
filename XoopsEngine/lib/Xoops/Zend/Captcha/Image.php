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
 * @package         Captcha
 * @version         $Id$
 */

/**
 * @todo    inline base64 image
 * @see     http://en.wikipedia.org/wiki/Data_URI_scheme
 * @see     http://www.codeproject.com/KB/aspnet/captchanet.aspx
 * Browser limitations:
    The image is encoded in base64 (i.e. 7 bit characters).
    This means the image is typically 30% larger than the original bit map.

    Not all browsers will display inline base64 images
        - most importantly IE 7 and earlier will not display them
        - IE8 and later will display the images and have a limited of 32kb
        - Firefox and Safari are said to have done so for much longer, but many are limited to 4kb images
 */

class Xoops_Zend_Captcha_Image extends Zend_Captcha_Image
{
    /**
     * Section to determine what session storage is used
     * Front end session uses different storage from backend (or section of "admin")
     */
    protected $section = "";

    protected $refreshUrl = "usr/captcha/image.php";
    //protected $callback;

    /**
     * Constructor
     *
     * @param  array|Zend_Config $options
     * @return void
     */
    public function __construct($options = null)
    {
        $options = empty($options) ? array() : $options;
        // Set options
        if (is_array($options)) {
            $this->setOptions($options);
        } else if ($options instanceof Zend_Config) {
            $this->setConfig($options);
        }
    }

    /**
     * Set object state from options array
     *
     * @param  array $options
     * @return Zend_Form_Element
     */
    public function setOptions($options = null)
    {
        $fontDir = XOOPS::path('img/captcha/fonts');
        $options_default = array(
            "wordLen"   => 6,
            'width'     => 150,
            'height'    => 50,
            "font"      => XOOPS::path('img/captcha/fonts/Vera.ttf'),
            "imgDir"    => XOOPS::path('upload/captcha'),
            "imgUrl"    => XOOPS::url('upload/captcha'),
        );
        $options = array_merge($options_default, $options);
        if (false === strpos($options["font"], "/")) {
            $options["font"] = XOOPS::path('img/captcha/fonts/' . $options["font"]);
        }
        foreach ($options as $key => $value) {
            $this->setOption($key, $value);
        }
        return $this;
    }

    /**
     * Get session object
     *
     * @return Zend_Session_Namespace
     */
    public function getSession()
    {
        if (!isset($this->_session) || (null === $this->_session)) {
            $id = $this->getId();
            $this->_session = Xoops::service("session")->ns('captcha_' . $id);
            $this->_session->setExpirationHops(1, null, true);
            $this->_session->setExpirationSeconds($this->getTimeout());
            //Xoops::persist()->save(__METHOD__ . ' ' . session_id() . '<br />' . Debug::render($_SESSION), 'captcha-session-image');
            //Debug::e($id);
            //Debug::e($this->_session);
        }
        return $this->_session;
    }

    /**
     * Display the captcha
     *
     * @param Zend_View_Interface $view
     * @param mixed $element
     * @return string
     */
    public function render(Zend_View_Interface $view = null, $element = null)
    {
        //$captchaScript = "var captcha_id = '" . $element->getId() . "'; var callback = '" . $this->callback . "';";
        //$view->headScript("script", $captchaScript);
        //$view->jQuery("jquery.min.js");
        //$view->headScript("file", 'img/captcha/scripts/captcha.js');

        $id = $this->getId();
        //$id = $this->encodeId($id);
        $imgId = is_string($element) ? $element : $element->getId();
        return "<img width=\"" . $this->getWidth() . "\" height=\"" . $this->getHeight() . "\" alt=\"" . $this->getImgAlt() . "\"" .
            //" src=\"" . $this->getImgUrl() . $this->getId() . $this->getSuffix() . "\"" .
            " src=\"" . $this->getRefreshUrl(array('id' => $id)) . "\"" .
            " style='cursor: pointer; vertical-align: middle;'" .
            " onclick=\"this.src='" . $this->getRefreshUrl(array('id' => $id)) . "&amp;refresh='+Math.random()\"  style='cursor: pointer; vertical-align: middle;'" .
            " id=\"" . $imgId . "-image\"" .
            " /><br />";
    }

    /*
    public function setCallback($callback)
    {
        $this->callback = $callback;
    }
    */

    public function setSection($section)
    {
        $this->section = $section;
    }

    /**
     * Generate captcha
     *
     * @return string captcha ID
     */
    public function generate()
    {
        //$count = Xoops::persist()->load('captcha-count');
        //Xoops::persist()->save(intval($count) + 1, 'captcha-count');
        if(!$this->_keepSession) {
            $this->_session = null;
        }
        $id = $this->_generateRandomId();
        $this->_setId($id);
        //$word = $this->_generateWord();
        //$this->_setWord($word);
        //Xoops::persist()->save(Xoops::persist()->load('captcha') . ' => ' . $word, 'captcha');
        //Xoops::persist()->save(__METHOD__ . ' sessId:' . session_id() . ' captchaID: ' . $id . '<br />' . Debug::render($_SESSION), 'captcha-session-generate');
        return $id;
    }

    protected function getRefreshUrl($params = array())
    {
        if ($this->section) {
            $params['section'] = $this->section;
        }
        $url = Xoops::url('www') . '/' . $this->refreshUrl;
        if (!empty($params)) {
            $url .= '?' . http_build_query($params, '', '&amp;');
        }

        return $url;
    }

    /*
    protected function encodeId($id)
    {
        return base64_encode(Xoops::config('identifier') . $id);
    }

    protected function decodeId($id)
    {
        return substr(base64_decode($id), strlen(Xoops::config('identifier')));
    }
    */

    public function createImage($id)
    {
        //$id = $this->decodeId($id);
        if(!$this->_keepSession) {
            $this->_session = null;
        }
        //$id = $this->_generateRandomId();
        $this->_setId($id);
        $word = $this->_generateWord();
        $this->_setWord($word);
        //Xoops::persist()->save(Xoops::persist()->load('captcha') . ' => ' . $word, 'captcha');

        if (!extension_loaded("gd")) {
            require_once 'Zend/Captcha/Exception.php';
            throw new Zend_Captcha_Exception("Image CAPTCHA requires GD extension");
        }

        if (!function_exists("imagepng")) {
            require_once 'Zend/Captcha/Exception.php';
            throw new Zend_Captcha_Exception("Image CAPTCHA requires PNG support");
        }

        if (!function_exists("imageftbbox")) {
            require_once 'Zend/Captcha/Exception.php';
            throw new Zend_Captcha_Exception("Image CAPTCHA requires FT fonts support");
        }

        $font = $this->getFont();

        if (empty($font)) {
            require_once 'Zend/Captcha/Exception.php';
            throw new Zend_Captcha_Exception("Image CAPTCHA requires font");
        }

        $w     = $this->getWidth();
        $h     = $this->getHeight();
        $fsize = $this->getFontSize();

        //$img_file   = $this->getImgDir() . $id . $this->getSuffix();
        if(empty($this->_startImage)) {
            $img        = imagecreatetruecolor($w, $h);
        } else {
            $img = imagecreatefrompng($this->_startImage);
            if(!$img) {
                require_once 'Zend/Captcha/Exception.php';
                throw new Zend_Captcha_Exception("Can not load start image");
            }
            $w = imagesx($img);
            $h = imagesy($img);
        }
        $text_color = imagecolorallocate($img, 0, 0, 0);
        $bg_color   = imagecolorallocate($img, 255, 255, 255);
        imagefilledrectangle($img, 0, 0, $w-1, $h-1, $bg_color);
        $textbox = imageftbbox($fsize, 0, $font, $word);
        $x = ($w - ($textbox[2] - $textbox[0])) / 2;
        $y = ($h - ($textbox[7] - $textbox[1])) / 2;
        imagefttext($img, $fsize, 0, $x, $y, $text_color, $font, $word);

       // generate noise
        for ($i=0; $i<$this->_dotNoiseLevel; $i++) {
           imagefilledellipse($img, mt_rand(0,$w), mt_rand(0,$h), 2, 2, $text_color);
        }
        for($i=0; $i<$this->_lineNoiseLevel; $i++) {
           imageline($img, mt_rand(0,$w), mt_rand(0,$h), mt_rand(0,$w), mt_rand(0,$h), $text_color);
        }

        // transformed image
        $img2     = imagecreatetruecolor($w, $h);
        $bg_color = imagecolorallocate($img2, 255, 255, 255);
        imagefilledrectangle($img2, 0, 0, $w-1, $h-1, $bg_color);
        // apply wave transforms
        $freq1 = $this->_randomFreq();
        $freq2 = $this->_randomFreq();
        $freq3 = $this->_randomFreq();
        $freq4 = $this->_randomFreq();

        $ph1 = $this->_randomPhase();
        $ph2 = $this->_randomPhase();
        $ph3 = $this->_randomPhase();
        $ph4 = $this->_randomPhase();

        $szx = $this->_randomSize();
        $szy = $this->_randomSize();

        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                $sx = $x + (sin($x*$freq1 + $ph1) + sin($y*$freq3 + $ph3)) * $szx;
                $sy = $y + (sin($x*$freq2 + $ph2) + sin($y*$freq4 + $ph4)) * $szy;

                if ($sx < 0 || $sy < 0 || $sx >= $w - 1 || $sy >= $h - 1) {
                    continue;
                } else {
                    $color    = (imagecolorat($img, $sx, $sy) >> 16)         & 0xFF;
                    $color_x  = (imagecolorat($img, $sx + 1, $sy) >> 16)     & 0xFF;
                    $color_y  = (imagecolorat($img, $sx, $sy + 1) >> 16)     & 0xFF;
                    $color_xy = (imagecolorat($img, $sx + 1, $sy + 1) >> 16) & 0xFF;
                }
                if ($color == 255 && $color_x == 255 && $color_y == 255 && $color_xy == 255) {
                    // ignore background
                    continue;
                } elseif ($color == 0 && $color_x == 0 && $color_y == 0 && $color_xy == 0) {
                    // transfer inside of the image as-is
                    $newcolor = 0;
                } else {
                    // do antialiasing for border items
                    $frac_x  = $sx-floor($sx);
                    $frac_y  = $sy-floor($sy);
                    $frac_x1 = 1-$frac_x;
                    $frac_y1 = 1-$frac_y;

                    $newcolor = $color    * $frac_x1 * $frac_y1
                              + $color_x  * $frac_x  * $frac_y1
                              + $color_y  * $frac_x1 * $frac_y
                              + $color_xy * $frac_x  * $frac_y;
                }
                imagesetpixel($img2, $x, $y, imagecolorallocate($img2, $newcolor, $newcolor, $newcolor));
            }
        }

        // generate noise
        for ($i=0; $i<$this->_dotNoiseLevel; $i++) {
            imagefilledellipse($img2, mt_rand(0,$w), mt_rand(0,$h), 2, 2, $text_color);
        }
        for ($i=0; $i<$this->_lineNoiseLevel; $i++) {
           imageline($img2, mt_rand(0,$w), mt_rand(0,$h), mt_rand(0,$w), mt_rand(0,$h), $text_color);
        }

        //imagepng($img2, $img_file);
        imagedestroy($img);
        //imagedestroy($img2);
        return $img2;
    }
}
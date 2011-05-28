<?php
/**
 * Xoops Engine Setup Controller Abstract
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
 * @credits         Skalpa Keo <skalpa@xoops.org>
 * @since           3.0
 * @package         Setup
 * @version         $Id$
 */

namespace Xoops\Setup\Controller;

abstract class AbstractController
{
    protected $content = '';
    protected $headContent = '';
    protected $footContent = '';
    protected $wizard;
    protected $request;
    protected $page;
    protected $hasBootstrap = false;
    protected $hasForm = false;
    //protected $hasHelp = false;
    //protected $hasAjax = false;
    protected $status = 0; // 1 - proceed; -1 - pending; 0 - regular

    public function __construct(\Xoops\Setup\Wizard $wizard)
    {
        $this->wizard = $wizard;
        if ($this->hasBootstrap) {
            define('XOOPS_BOOTSTRAP', "setup");
            include dirname($wizard->getRoot()) . '/boot.php';
        } else {
            defined('XOOPS_BOOTSTRAP') or define('XOOPS_BOOTSTRAP', false);
        }
        $this->request = $wizard->getRequest();
        $this->init();
    }

    protected function init()
    {
        return;
    }

    public function headContent()
    {
        return $this->headContent;
    }

    public function footContent()
    {
        return $this->footContent;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function hasHelp()
    {
        return $this->hasHelp ? true : false;
    }

    public function hasForm()
    {
        return $this->hasForm ? true : false;
    }

    public function hasBootstrap()
    {
        return $this->hasBootstrap ? true : false;
    }

    public function hasAjax()
    {
        return $this->hasAjax ? true : false;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function indexAction() {}
}
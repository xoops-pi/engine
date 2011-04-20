<?php
/**
 * Xoops Engine Editor Default
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
 * @package         Xoops_Editor
 * @version         $Id$
 */


namespace Editor\Ckeditor;

class Handler extends \Xoops\Editor\AbstractEditor
{
    protected $configFile = 'editor.ckeditor.ini.php';

    /**
     * Renders editor contents
     *
     * @param  Zend_View_Abstract $view
     * @return string
     */
    public function render(\Zend_View_Interface $view)
    {
        if (!isset($this->config['language'])) {
            $this->config['language'] = \Xoops::config('locale');
        }

        //include_once __DIR__ . '/editor/ckeditor.php';
        include_once __DIR__ . '/ckeditor.php';
        $basePath = \Xoops::url('img') . '/editors/ckeditor/';
        $ckEditor = new CKEditor($basePath);
        $ckEditor->returnOutput = true;
        if ($this->attribs) {
            $ckEditor->textareaAttributes = array_merge($ckEditor->textareaAttributes, $this->attribs);
        }
        if (!empty($this->id)) {
            $ckEditor->textareaAttributes['id'] = $this->id;
        }


        $this->setupFinder($ckEditor);

        return $ckEditor->editor($this->name, $this->value, $this->config);
    }

    protected function setupFinder($ckEditor, $config = array())
    {
        if (null === $this->upload || false === $this->upload) {
            //\Xoops::service('session')->namespaceUnset('ckfinder');
            return;
        }
        $session =& \Xoops::service('session')->ckfinder;
        $session->role = (\Xoops::registry('user')->role == 'admin') ? 'admin'
                    : ((!isset($this->upload['enabled']) || !empty($this->upload['enabled'])) ? 'user' : '*');
        $session->path = empty($this->upload['path']) ? "ckfinder" : $this->upload['path'];
        include_once \Xoops::path('www') . '/usr/editors/ckfinder/ckfinder.php';

        $basePath = \Xoops::url('www') . '/usr/editors/ckfinder/';
        \CKFinder::SetupCKEditor($ckEditor, $basePath);
    }
}
<?php
/**
 * Block edit form
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
 * @category        Xoops_Module
 * @package         System
 * @version         $Id$
 */

class App_System_Form_BlockEdit extends Xoops_Zend_Form
{
    /**
     * Form metadata and attributes
     * @var array
     */
    protected $_attribs = array(
        'name'      => 'blockEdit',
        'method'    => 'post',
    );

    /**
     * Form legend
     * @var string
     */
    protected $_legend = 'Block Edit';

    // Add elements
    protected function build()
    {
        $block = $this->getAttrib('block');
        $this->removeAttrib('block');
        $module = $block['module'];
        $modulePath = Xoops::service('module')->getPath($module);

        // Block unique name
        $options = array(
            'label'         => 'Name',
            'value'         => $block['name'],
            'prefixPath'    => array(
                'validate'  => array(
                    'System_Validate'      => Xoops::path('app') . '/system/Validate',
                ),
            ),
            'validators'    => array(
                'duplicate'  => array(
                    'validator' => 'BlockNameDuplicate',
                ),
            ),
            'filters'       => array(
                'trim'      => array(
                    'filter'    => 'StringTrim',
                ),
            ),
            'Description'   => 'Be unique or empty',
        );
        $this->addElement('Text', 'name', $options);

        // Title and display
        $options = array(
            'label'             => 'Title',
            'elementsBelongTo'  => false,
        );
        $titleCompound = $this->createElement('Compound', 'title-compound', $options);

        // Title/Caption
        $options = array(
            //'label'         => 'Title',
            'value'         => $block['title'],
            'required'      => true,
            'filters'       => array(
                'trim'      => array(
                    'filter'    => 'StringTrim',
                ),
            ),
        );
        $titleCompound->addElement('Text', 'title', $options);

        // To hide title?
        $options = array(
            'label'         => 'Hide',
            'value'         => $block['title_hidden'],
        );
        $titleCompound->addElement('Checkbox', 'title_hidden', $options);
        $this->addElement($titleCompound);

        // Link
        $options = array(
            'label'         => 'Link URL',
            'value'         => $block['link'],
            'filters'       => array(
                'trim'      => array(
                    'filter'    => 'StringTrim',
                ),
            ),
        );
        $this->addElement('Text', 'link', $options);

        // Display style
        $options = array(
            'label'         => 'Display style',
            'value'         => $block['style'],
        );
        //$element = new App_System_Form_Element_Blockstyle('style', $options);
        $this->addElement(array('system', 'Blockstyle'), 'style', $options);

        // Cache expiration time
        $options = array(
            'label'         => 'Cache expire',
            'value'         => $block['cache_expire'],
        );
        $this->addElement('CacheExpire', 'cache_expire', $options);

        XOOPS::service('translate')->loadTranslation('blocks', $module);
        $blockOptions = is_array($block['options']) ? $block['options'] : unserialize($block['options']);
        // Legacy function
        if (!empty($block['edit_func'])) {
            include_once $modulePath . '/blocks/' . $block['func_file'];
            //$options = explode('|', $block['options']);
            $blockOptions['module'] = $module;
            $func = $block['edit_func'];
            $content = $func($blockOptions);
            $options = array(
                'label'         => 'Options',
                'value'         => $content,
            );
            $this->addElement('Note', 'opts', $options);
        // New format
        } else/*if (!empty($blockOptions))*/ {
            $modelOption = Xoops::getModel('block_option');

            $select = $modelOption->select()
                            ->where('block = ?', $block['root'] ?: $block['id'])
                            ->order('order ASC');
            $opts = $modelOption->fetchAll($select);
            if ($opts->count()) {
                $optionForm = new Xoops_Zend_Form_SubForm();
                foreach ($opts as $option) {
                    $keyOption = $option->name;
                    $options = array(
                        'label'         => $option->title,
                        'value'         => isset($blockOptions[$keyOption]) ? $blockOptions[$keyOption] : '',
                        'module'        => $module,
                    );
                    if ($multiOptions = $option->options) {
                        $options['multiOptions'] = $multiOptions;
                    }
                    $edit = $option->edit;
                    if (!empty($edit)) {
                        if (!is_array($edit)) {
                            $edit = array("type" => $edit);
                        }
                    } else {
                        $edit = array();
                    }
                    $type = empty($edit["type"]) ? "text" : strtolower($edit["type"]);
                    //$optionForm->addElement($type, $option['name'], $options);

                    if (!empty($edit["module"])) {
                        $class = ('app' == Xoops::service('module')->getType($edit["module"]) ? 'App' : 'Module') . "_" . ucfirst($edit["module"]) . "_Form_Element_" . ucfirst($type);
                        if (class_exists($class)) {
                            $element = new $class($keyOption, $options);
                        } else {
                            $element = $optionForm->createElement("text", $keyOption, $options);
                        }
                    } else {
                        $element = $optionForm->createElement($type, $keyOption, $options);
                    }
                    $optionForm->addElement($element);

                }
                $this->addSubForm($optionForm, 'options');
            }
        }

        $this->addElement('Hidden', 'id', $block['id']);
        $this->addElement('Hidden', 'root', $block['root']);
        $this->addElement('Hidden', 'module', $block['module']);
        $options = array(
            'label'     => 'Submit',
            'required'  => false,
            'ignore'    => true,
        );
        $this->addElement('submit', 'save', $options);

        $this->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'dl', 'class' => 'xoops-form')),
            array('Description', array('placement' => 'prepend')),
            'Form'
        ));

        return $this;
    }
}
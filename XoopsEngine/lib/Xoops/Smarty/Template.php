<?php
/**
 * XOOPS SMARTY template
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
 * @package         Xoops_Smarty
 * @version         $Id$
 */

class Xoops_Smarty_Template extends Smarty_Internal_Template
{
    /**#@+
     * The two parameters are defined as private by Smarty_Internal_Template, thus we declare the two parameters as protected
     */
    protected $templateTimestamp = null;
    protected $isExisting = null;
    /**#@-/

    /**
     * Returns the template source code
     *
     * The template source is being read by the actual resource handler
     *
     * @return string the template source
     */
    public function getTemplateSource ()
    {
        if ($this->template_source === null) {
            if (!$this->resource_object->getTemplateSource($this)) {
                $this->template_source = "";
                trigger_error("Unable to read template {$this->resource_type} '{$this->resource_name}'");
            }
        }
        return $this->template_source;
    }

    /**
     * Returns if the  template is existing
     *
     * The status is determined by the actual resource handler
     *
     * @return boolean true if the template exists
     */
    public function isExisting($error = false)
    {
        if ($this->isExisting === null) {
            $this->isExisting = $this->resource_object->isExisting($this);
        }
        if (!$this->isExisting && $error) {
            trigger_error("Unable to load template {$this->resource_type} '{$this->resource_name}'");
        }
        return $this->isExisting;
    }

    /**
     * get system filepath to template
     */
    public function buildTemplateFilepath($file = null)
    {
        if ($file == null) {
            $file = $this->resource_name;
        }
        // Use custom template handler function to locate the template
        if (!empty($this->smarty->default_template_handler_func)) {
            $_return = call_user_func_array($this->smarty->default_template_handler_func,
                array($this->resource_type, $file, &$this->template_source, &$this->templateTimestamp, $this));

            if (is_string($_return)) {
                return $_return;
            } elseif ($_return === true) {
                return $file;
            }
        }
        // Look up in template_dir
        foreach((array)$this->smarty->template_dir as $_template_dir) {
            if (strpos('/\\', substr($_template_dir, -1)) === false) {
                $_template_dir .= DIRECTORY_SEPARATOR;
            }

            $_filepath = $_template_dir . $file;
            if (file_exists($_filepath))
                return $_filepath;
        }
        if (file_exists($file)) return $file;
        // no tpl file found
        return false;
    }
}
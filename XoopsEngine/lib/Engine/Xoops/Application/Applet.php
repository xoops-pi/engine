<?php
/**
 * Application applet abstract
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
 * @package         Kernel
 * @since           3.0
 * @version         $Id$
 */

namespace Application;

class Applet
{
    protected $options = array();

    /**
     * Constructor
     *
     * @param  null|array $options
     * @return void
     */
    public function __construct($options = null)
    {
        if (!empty($options)) {
            $this->setOptions($options);
        }
    }

    /**
     * Configure the applet
     *
     * @param  array|Traversable $options
     * @return Applet
     */
    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Renders content
     *
     * @return string
     */
    public function render()
    {
        return;
    }
}
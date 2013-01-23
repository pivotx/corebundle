<?php

/**
 * This file is part of the PivotX Core bundle
 *
 * (c) Marcel Wouters / Two Kings <marcel@twokings.nl>
 */

namespace PivotX\Component\Twig;


/**
 * Themed Twig Loader
 *
 * @author Marcel Wouters <marcel@twokings.nl>
 *
 * @api
 */
class WebdebugLoader extends \Twig_Loader_Filesystem
{
    protected static $templates = array();

    protected $loader;

    public function __construct($loader)
    {
        $this->loader = $loader;
    }

    public static function getTemplates()
    {
        return self::$templates;
    }

    protected function findTemplate($template)
    {
        $file = $this->loader->findTemplate($template);

        $found = false;
        foreach(self::$templates as $tpl) {
            if ($tpl['template'] == $template) {
                $found = true;
            }
        }

        if (!$found) {
            self::$templates[] = array('template' => $template, 'file' => $file);
        }

        return $file;
    }

    public function __call($name, $args)
    {
        return call_user_func_array(array($this->loader,$name), $args);
    }
}

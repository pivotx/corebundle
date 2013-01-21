<?php

namespace PivotX\Component\Twig;


class Test
{
    /**
     * Return true if we're called from a Twig_Template->getAttribute call
     *
     * @return boolean
     */
    public static function isTwigReturn()
    {
        $trace = debug_backtrace();
        foreach($trace as $tr) {
            if ((isset($tr['class'])) && ($tr['class'] == 'Twig_Template') && ($tr['function'] == 'getAttribute')) {
                return true;
            }
        }
        return false;
    }
}
?>

<?php
/**
 * Standard auto-formatting conversion
 */

namespace PivotX\Component\Formats\Formats;

use \PivotX\Component\Formats\AbstractFormat;

class AutoFormat extends AbstractFormat
{
    /**
     * PivotX Autoformatter
     */
    public function format($in, $arguments = array())
    {
        if (is_null($in)) {
            return '-';
        }

        if (is_object($in)) {
            if (method_exists($in, '__toString')) {
                return $in->__toString();
            }

            switch (get_class($in)) {
                case 'DateTime':
                    $fmt = '%a %e %b %Y, %H:%M';
                    if (count($arguments) >= 1) {
                        switch ($arguments[0]) {
                            case 'readable':
                                $fmt = '%a %e %b %Y, %H:%M';
                                break;
                            case 'technical':
                                $fmt = '%F %T';
                                break;
                        }
                    }
                    return strftime($fmt, $in->getTimestamp());
                    break;

                default:
                    if ($in instanceof \PivotX\CoreBundle\Entity\EmbedResource) {
                        if ($arguments === false) {
                            $arguments = array();
                        }
                        return new \Twig_Markup(
                            call_user_func_array(array($in, 'getHtml'), $arguments),
                            'utf-8');
                    }
                    return 'object class:'.get_class($in);
            }
        }
        else if (is_scalar($in)) {
            return $in;
        }

        return 'Unknown source';
    }
}

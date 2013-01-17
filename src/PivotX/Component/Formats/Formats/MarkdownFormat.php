<?php
/**
 * Standard markdown conversion
 */

namespace PivotX\Component\Formats\Formats;

use \PivotX\Component\Formats\AbstractFormat;

include_once dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/Resources/lib/php_markdown_1.0.1p/markdown.php';

class MarkdownFormat extends AbstractFormat
{
    /**
     * PivotX Autoformatter
     */
    public function format($in, $arguments = array())
    {
        return new \Twig_Markup(Markdown($in), 'utf-8');
    }
}

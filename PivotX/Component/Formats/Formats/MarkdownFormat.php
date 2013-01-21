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
        static $parser = null;

        if (is_null($parser)) {
            $parser = new \Markdown_Parser;
        }

        return new \Twig_Markup($parser->transform($in), 'utf-8');
    }
}

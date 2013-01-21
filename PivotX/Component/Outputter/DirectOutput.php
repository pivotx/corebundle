<?php

/**
 * This file is part of the PivotX Core bundle
 *
 * (c) Marcel Wouters / Two Kings <marcel@twokings.nl>
 *
 *
 *
 * Rewritten non-assetic compatible version
 *
 */

namespace PivotX\Component\Outputter;

/**
 * An output stores
 *
 * @author Marcel Wouters <marcel@twokings.nl>
 *
 * @api
 */
class DirectOutput extends Output
{
    protected $content = false;
    protected $type;
    protected $debuggable = false;
    protected $routing_service = false;

    /**
     * Constructor
     *
     * @param string $content  Content to output later
     * @param string $type     Type of the content
     */
    public function __construct($content, $type = self::TYPE_HTML)
    {
        $this->setContent($content, $type);
    }

    /**
     */
    public function allowConcat()
    {
        return false;
    }

    /**
     */
    public function getHtml($temp_directory, $routing_service)
    {
        $output = '';

        switch ($this->type) {
            case 'text/javascript':
                $output .= '<script type="text/javascript">'."\n".$this->content."\n</script>\n";
                break;

            case 'text/x-javascript-src':
                $output .= '<script type="text/javascript" src="'.$this->content.'"></script>'."\n";
                break;

            case 'text/css':
                $output .= '<style type="text/css">'."\n".$this->content."\n</style>\n";
                break;

            case 'text/x-css-href':
                $output .= '<link rel="stylesheet" type="text/css" href="'.$this->content.'" />';
                break;

            case 'text/x-less-href':
                $output .= '<link rel="stylesheet" type="text/less" href="'.$this->content.'" />';
                break;

            case 'text/html':
                $output .= $this->content;
                break;
        }

        return $output;
    }
}

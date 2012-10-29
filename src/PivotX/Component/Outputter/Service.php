<?php

/**
 * This file is part of the PivotX Core bundle
 *
 * (c) Marcel Wouters / Two Kings <marcel@twokings.nl>
 */

namespace PivotX\Component\Outputter;

use Symfony\Component\HttpKernel\Log\LoggerInterface;

/**
 * An Outputter Service
 * 
 * @author Marcel Wouters <marcel@twokings.nl>
 *
 * @api
 */
class Service
{
    private $logger;
    private $kernel;
    private $groups;

    // these locations are available by default and PivotX makes sure these work
    const HEAD_START = 'headStart';
    const TITLE_AFTER = 'titleAfter';
    const HEAD_END = 'headEnd';
    const BODY_START = 'bodyStart';
    const BODY_END = 'bodyEnd';

    public function __construct(LoggerInterface $logger = null, \AppKernel $kernel)
    {
        $this->logger = $logger;
        $this->kernel = $kernel;

        $this->groups = array(
            self::HEAD_START => array(),
            self::TITLE_AFTER => array(),
            self::HEAD_END => array(),
            self::BODY_START => array(),
            self::BODY_END => array()
        );
    }

    protected function getOutputterDirectory()
    {
        $directory = $this->kernel->getCacheDir().'/outputter';

        if (!is_dir($directory)) {
            @mkdir($directory, 0777);
            @chmod($directory, 0777);
        }

        return $directory;
    }

    /**
     * Return all the outputs as html for a group
     * 
     * @param string $group   webresource group to return
     * @return string         html of the resources
     */
    public function getOutputs($group)
    {
        $html = '';

        $temp_directory = $this->getOutputterDirectory();

        if (isset($this->groups[$group])) {
            // @todo only works when included in HTML (and in the html part)
            $html .= "\n\t\t".'<!-- output group: ['.$group.'] -->'."\n";

            $groupoutput = '';
            foreach($this->groups[$group] as $output) {
                $groupoutput .= $output->getHtml($temp_directory);
            }

            $html .= $groupoutput;

            // @todo only works when included in HTML (and in the html part)
            $html .= "\t\t".  '<!-- /output group: ['.$group.'] -->'."\n";
        }

        return new \Twig_Markup($html, 'utf-8');
    }

    public function addOutput($group, Output $output)
    {
        if (!isset($this->groups[$group])) {
            return false;
        }

        $this->groups[$group][] = $output;

        return true;
    }

    public function concatOutputs($in_outputs)
    {
        $out_outputs = array();

        $previous_type = false;
        $type_contents = array();
        foreach($in_outputs as $output) {
            if (($previous_type !== false) && ($previous_type != $output->getType())) {
                $out_outputs[] = new Output($type_contents, $previous_type);
                $type_contents = array();
            }

            $previous_type   = $output->getType();
            $type_contents[] = $output->getContent();
        }

        if (count($type_contents) > 0) {
            $out_outputs[] = new Output($type_contents, $previous_type);
        }

        return $out_outputs;
    }
}

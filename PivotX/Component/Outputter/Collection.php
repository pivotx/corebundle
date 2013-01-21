<?php

/**
 * This file is part of the PivotX Core bundle
 *
 * (c) Marcel Wouters / Two Kings <marcel@twokings.nl>
 */

namespace PivotX\Component\Outputter;


class Collection
{
    private $groups;
    private $output_directory;
    private $routing_service;

    // these locations are available by default and PivotX makes sure these work
    const NONE = 'none';
    const HEAD_START = 'headStart';
    const TITLE_AFTER = 'titleAfter';
    const HEAD_END = 'headEnd';
    const BODY_START = 'bodyStart';
    const BODY_END = 'bodyEnd';

    public function __construct($output_directory, $routing_service)
    {
        $this->output_directory = $output_directory;
        $this->routing_service  = $routing_service;

        $this->groups = array(
            self::HEAD_START => array(),
            self::TITLE_AFTER => array(),
            self::HEAD_END => array(),
            self::BODY_START => array(),
            self::BODY_END => array()
        );
    }

    /**
     * Return all the outputs as html for a group
     * 
     * @param string $group   webresource group to return
     * @return string         html of the resources
     */
    public function getGroup($group)
    {
        $html = '';

        $temp_directory = $this->output_directory;

        if (isset($this->groups[$group])) {
            // @todo only works when included in HTML (and in the html part)
            $html .= "\n\t\t".'<!-- output group: ['.$group.'] -->'."\n";

            $outputs = $this->groups[$group];

            $outputs = $this->concat($outputs);

            $groupoutput = '';
            foreach($outputs as $output) {
                $groupoutput .= $output->getHtml($temp_directory, $this->routing_service);
            }

            $html .= $groupoutput;

            // @todo only works when included in HTML (and in the html part)
            $html .= "\t\t".  '<!-- /output group: ['.$group.'] -->'."\n";
        }

        return new \Twig_Markup($html, 'utf-8');
    }

    public function add($group, Output $output)
    {
        if (!isset($this->groups[$group])) {
            return false;
        }

        $this->groups[$group][] = $output;

        return true;
    }

    /**
     * Concatenate output together if possible
     *
     * @param array $in_outputs   ungrouped Output's
     * @return array              grouped Output's
     */
    public function concat($in_outputs)
    {
        $out_outputs = array();

        //echo "in#".count($in_outputs)."<br/>\n";

        $previous_type  = false;
        $previous_debug = false;
        $type_contents  = array();
        foreach($in_outputs as $output) {
            if (!$output->allowConcat()) {
                $out_outputs[] = $output;
                continue;
            }

            if (($previous_type !== false) && (($previous_type != $output->getType()) || ($previous_debug != $output->shouldBeDebuggable()))) {
                if (count($type_contents) > 0) {
                    $new_output = new Output($type_contents, $previous_type);
                    if ($previous_debug) {
                        $new_output->allowDebugging();
                    }
                    $out_outputs[] = $new_output;
                    $type_contents = array();
                }
            }

            $previous_type   = $output->getType();
            $previous_debug  = $output->shouldBeDebuggable();

            if ($output->shouldBeDebuggable()) {
                $out_outputs[] = $output;
            }
            else {
                $type_contents[] = $output->getContent();
            }
        }

        if (count($type_contents) > 0) {
            $new_output    = new Output($type_contents, $previous_type);
            if ($previous_debug) {
                $new_output->allowDebugging();
            }
            $out_outputs[] = $new_output;
        }

        //echo "out#".count($out_outputs)."<br/><br/>\n";

        return $out_outputs;
    }
}

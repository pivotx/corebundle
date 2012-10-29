<?php

/**
 * This file is part of the PivotX Core bundle
 *
 * (c) Marcel Wouters / Two Kings <marcel@twokings.nl>
 */

namespace PivotX\Component\Webresourcer;

use Assetic\Asset\AssetCollection;
use Assetic\Asset\FileAsset;
use Assetic\Asset\GlobAsset;
use PivotX\Component\Outputter\Service as OutputterService;
use PivotX\Component\Outputter\Output as Output;

/**
 * A Webresource describe a set of resources that are bundled together.
 *
 * @author Marcel Wouters <marcel@twokings.nl>
 *
 * @api
 */
class Webresource
{
    protected $identifier;
    protected $version;
    protected $dependencies;
    protected $provides;

    protected $debuggable = false;

    protected $resources;
    protected $output_groups;


    /**
     * Constructor
     *
     * @param string $identifier    The identifier
     * @param string $version       The version provided
     * @param array  $dependencies  Other identifier/versions which are required
     * @param string $provides      The provides identifier (by default equals the identifier)
     */
    public function __construct($identifier, $version, array $dependencies = array(), $provides = false)
    {
        $this->setIdentifier($identifier);
        $this->setVersion($version);
        $this->setDependencies($dependencies);
        if ($provides !== false) {
            $this->setProvides($provides);
        }
        else {
            $this->setProvides($identifier);
        }

        $this->output_groups = array(
            OutputterService::HEAD_START => array(),
            OutputterService::TITLE_AFTER => array(),
            OutputterService::HEAD_END => array(),
            OutputterService::BODY_START => array(),
            OutputterService::BODY_END => array()
        );
    }

    /**
     * Set the identifier
     */
    public function setIdentifier($identifier)
    {
        $this->indentifier = $identifier;
    }

    /**
     * Set the version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * Set the dependencies
     */
    public function setDependencies($dependencies)
    {
        $this->dependencies = $dependencies;
    }

    /**
     * Set the provides
     */
    public function setProvides($provides)
    {
        $this->provides = $provides;
    }

    /**
     * Don't compress or concat files
     */
    public function allowDebugging()
    {
        $this->debuggable = true;
    }

    /**
     */
    protected function addOutput($group, Output $output)
    {
        if (!isset($this->output_groups[$group])) {
            // @todo do something!
            return false;
        }

        $this->output_groups[$group][] = $output;

        return true;
    }

    /**
     */
    public function finalizeOutput(OutputterService $outputterservice)
    {
        foreach($this->output_groups as $group => $outputs) {
            if (count($outputs) > 0) {
                if ($this->debuggable) {
                    $_outputs = $outputterservice->concatOutputs($outputs);
                }
                else {
                    $_outputs = $outputs;
                }

                foreach($_outputs as $output) {
                    $outputterservice->addOutput($group, $output);
                }
            }
        }
    }
}

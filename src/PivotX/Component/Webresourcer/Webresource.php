<?php

/**
 * This file is part of the PivotX Core bundle
 *
 * (c) Marcel Wouters / Two Kings <marcel@twokings.nl>
 */

namespace PivotX\Component\Webresourcer;

use PivotX\Component\Outputter\Service as OutputterService;
use PivotX\Component\Outputter\Collection as OutputterCollection;
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
    protected $enabled;
    protected $variant;
    protected $identifier;
    protected $description;
    protected $version;
    protected $dependencies;
    protected $provides;

    protected $debuggable = false;

    protected $resources;
    protected $variants;


    /**
     * Constructor
     *
     * @param string $identifier    The identifier
     * @param string $version       The version provided
     * @param array  $dependencies  Other identifier/versions which are required
     * @param string $provides      The provides identifier (by default equals the identifier) or identifiers
     */
    public function __construct($identifier, $version, array $dependencies = array(), $provides = false)
    {
        $this->setEnabled(false);
        $this->setIdentifier($identifier);
        $this->setVersion($version);
        $this->setDependencies($dependencies);
        if ($provides !== false) {
            $this->setProvides($provides);
        }
        else {
            $this->setProvides($identifier);
        }

        $this->variant = false;

        $this->variants = array();
    }

    /**
     * Set enabled
     */
    public function setEnabled($enabled = true)
    {
        $this->enabled = $enabled;
    }

    /**
     * Enable variant
     */
    public function setVariant($variant)
    {
        $this->variant = $variant;
    }

    /**
     * Get enabled status
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set the identifier
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * Get the identifier
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Set the description
     */
    public function setDescription($description)
    {
        $this->description = $description;
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
     * Get dependencies
     */
    public function getDependencies()
    {
        return $this->dependencies;
    }

    /**
     * Set the provides
     */
    public function setProvides($provides)
    {
        if (is_array($provides)) {
            $this->provides = $provides;
        }
        else {
            $this->provides = array($provides);
        }
    }

    /**
     * Get the provides
     * 
     * @return array    an array of everything this resource provides
     */
    public function getProvides()
    {
        return $this->provides;
    }

    /**
     * Search this resource if it provides the identifier
     *
     * @param string $identifier
     * @return boolean              true if resource provides identifier
     */
    public function searchProvides($identifier)
    {
        if (in_array($identifier, $this->provides)) {
            return true;
        }

        return false;
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
    protected function addVariant($variant)
    {
        if (!isset($this->variants[$variant])) {
            $this->variants[$variant] = array(
                OutputterCollection::HEAD_START => array(),
                OutputterCollection::TITLE_AFTER => array(),
                OutputterCollection::HEAD_END => array(),
                OutputterCollection::BODY_START => array(),
                OutputterCollection::BODY_END => array()
            );
        }
    }

    /**
     */
    protected function addOutput($variant, $group, Output $output)
    {
        if (!isset($this->variants[$variant])) {
            // @todo do something!
            return false;
        }
        if (!isset($this->variants[$variant][$group])) {
            // @todo do something!
            return false;
        }

        $this->variants[$variant][$group][] = $output;

        return true;
    }

    /**
     * Finalize all the outputs
     *
     * @param OutputterService $outputterservice   service to output to
     * @param boolean $force_debugging             if debugging is forced
     */
    public function finalizeOutput(OutputterService $outputterservice, $force_debugging = false)
    {
        if ($this->variant !== false) {
            $variant = $this->variant;
        }
        else {
            $variant = 'regular';
        }

        if (isset($this->variants[$variant])) {
            foreach($this->variants[$variant] as $group => $outputs) {
                if (count($outputs) > 0) {
                    foreach($outputs as $output) {
                        if (($this->debuggable) || ($force_debugging)) {
                            $output->allowDebugging();
                        }
                        $outputterservice->addOutput($group, $output);
                    }
                }
            }
        }
    }
}

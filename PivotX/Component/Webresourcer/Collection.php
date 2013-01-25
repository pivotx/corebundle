<?php

/**
 * This file is part of the PivotX Core bundle
 *
 * (c) Marcel Wouters / Two Kings <marcel@twokings.nl>
 */

namespace PivotX\Component\Webresourcer;

/**
 * A collection of webresources
 */

class Collection
{
    protected $webresources;
    protected $webresource_weights;

    /**
     */
    public function __construct()
    {
        $this->webresources        = array();
        $this->webresource_weights = array();
    }

    /**
     * Add a webresource
     *
     * @param Webresource     $webresource
     * @return Webresource    the $webresource self
     */
    public function add(Webresource $webresource, $is_theme = false)
    {
        $this->webresources[] = $webresource;

        $weight = 1000;
        if ($is_theme) {
            $weight = 2000;
        }

        $provides = $webresource->getProvides();
        foreach($provides as $provide) {
            $this->webresource_weights[$provide] = $weight;
        }

        return $webresource;
    }

    /**
     */
    private function find($identifier)
    {
        $search_identifier = $identifier;
        $variant           = false;
        if (($pos = strpos($identifier, '/')) !== false) {
            $search_identifier = substr($identifier, 0, $pos);
            $variant           = substr($identifier, $pos+1);
        }

        $options = array();
        foreach($this->webresources as &$webresource) {
            if ($webresource->searchProvides($search_identifier)) {
                if ($webresource->getEnabled()) {
                    // we can quickly exit because we already found an activated webresource
                    return $webresource;
                }

                $options[] = $webresource;
            }
        }

        // @later in the future we could make a better choice than 'the first option'
        if (count($options) > 0) {
            $options[0]->setEnabled();
            $options[0]->setVariant($variant);
            return $options[0];
        }

        return false;
    }

    /**
     * Idiot draft version of dependency activation
     */
    public function activate($identifier)
    {
        $dependencies = array($identifier);

        $first              = true;
        $loop_break_counter = 100;
        while ((count($dependencies) > 0) && ($loop_break_counter > 0)) {
            $dependency = array_shift($dependencies);

            $webresource = $this->find($dependency);
            if ($webresource === false) {
                // @later throw exception?
                return false;
            }

            $diff     = $first ? 1 : -1;
            $provides = $webresource->getProvides();
            foreach($provides as $provide) {
                $this->webresource_weights[$provide] += $diff;
            }
            $first = false;

            $webresource->setEnabled();

            $subdependencies = $webresource->getDependencies();
            foreach($subdependencies as $subdependency) {
                $subwebresource = $this->find($subdependency);
                if ($subwebresource !== false) {
                    $dependencies[] = $subdependency;
                }
            }

            $loop_break_counter--;
        }

        if ($loop_break_counter == 0) {
            // @later we have a missing dependency, throw exception?
            return false;
        }

        return true;
    }

    /**
     * Finalize webresources to an outputter
     */
    public function finalize($outputter, $force_debugging = false)
    {
        // filter resources
        $webresources = array();
        foreach($this->webresources as $webresource) {
            if ($webresource->getEnabled()) {
                $webresources[] = $webresource;
            }
        }

        $webresource_weights = $this->webresource_weights;
        usort($webresources, function(&$a, &$b) use ($webresource_weights){
            $w_a = 2000;
            $provides = $a->getProvides();
            foreach($provides as $provide) {
                if ($webresource_weights[$provide] < $w_a) {
                    $w_a = $webresource_weights[$provide];
                }
            }

            $w_b = 2000;
            $provides = $b->getProvides();
            foreach($provides as $provide) {
                if ($webresource_weights[$provide] < $w_b) {
                    $w_b = $webresource_weights[$provide];
                }
            }

            if ($w_a < $w_b) {
                return -1;
            }
            if ($w_a > $w_b) {
                return +1;
            }
            return 0;
        });

        // output them
        foreach($webresources as $webresource) {
            $webresource->finalizeOutput($outputter, $force_debugging);
        }

        return true;
    }
}

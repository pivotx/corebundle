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
    /**
     * Add a webresource
     *
     * @param Webresource $webresource
     * @return boolean                     true if successful
     */
    public function add(Webresource $webresource)
    {
        $this->webresources[] = $webresource;

        return true;
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

        // @todo in the future we could make a better choice than 'the first option'
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

        $loop_break_counter = 100;
        while ((count($dependencies) > 0) && ($loop_break_counter > 0)) {
            $dependency = array_shift($dependencies);

            $webresource = $this->find($dependency);
            if ($webresource === false) {
                // @todo can't find dependency
                echo 'Can\'t find['.$dependency.']<br/>';
                return false;
            }

            $webresource->setEnabled();

            $subdependencies = $webresource->getDependencies();
            foreach($subdependencies as $subdependency) {
                $subwebresource = $this->find($subdependency);
                if (($subwebresource !== false) && (!$subwebresource->getEnabled())) {
                    $dependencies[] = $subdependency;
                }
            }

            $loop_break_counter--;
        }

        if ($loop_break_counter == 0) {
            // @todo we have a missing dependency
            return false;
        }

        return true;
    }

    /**
     * Finalize webresources to an outputter
     */
    public function finalize($outputter)
    {
        // filter resources
        $webresources = array();
        foreach($this->webresources as $webresource) {
            if ($webresource->getEnabled()) {
                $webresources[] = $webresource;
            }
        }

        // @todo sort resources

        // output them
        foreach($webresources as $webresource) {
            $webresource->finalizeOutput($outputter);
        }

        return true;
    }
}

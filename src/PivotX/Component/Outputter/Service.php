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
    private $collection;

    public function __construct(LoggerInterface $logger = null, \AppKernel $kernel)
    {
        $this->logger = $logger;
        $this->kernel = $kernel;

        $this->collection = new Collection($this->getOutputterDirectory());
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
        $html = $this->collection->getGroup($group);

        return new \Twig_Markup($html, 'utf-8');
    }

    public function addOutput($group, Output $output)
    {
        return $this->collection->add($group, $output);
    }

    /**
     * @todo this method should no longer be called
     *
     * Concatenate output together if possible
     *
     * @param array $in_outputs   ungrouped Output's
     * @return array              grouped Output's
     */
    public function concatOutputs($in_outputs)
    {
        return $this->collection->concat($in_outputs);
    }
}

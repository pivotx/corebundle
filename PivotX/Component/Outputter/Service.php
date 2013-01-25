<?php

/**
 * This file is part of the PivotX Core bundle
 *
 * (c) Marcel Wouters / Two Kings <marcel@twokings.nl>
 */

namespace PivotX\Component\Outputter;

use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Debug\Stopwatch;
use PivotX\Component\Routing\Service as RoutingService;

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
    private $routing_service;
    private $stopwatch;

    private $collection;

    public function __construct(LoggerInterface $logger = null, \AppKernel $kernel, RoutingService $routing_service, Stopwatch $stopwatch = null)
    {
        $this->logger          = $logger;
        $this->kernel          = $kernel;
        $this->routing_service = $routing_service;
        $this->stopwatch       = $stopwatch;

        $this->collection = new Collection($this->getOutputterDirectory(), $this->routing_service);
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

    protected function getPublicDirectory($site = 'none', $version = null)
    {
        $directory = dirname($this->kernel->getRootDir()).'/web/outputter';
        if (!is_dir($directory)) {
            @mkdir($directory, 0777);
            @chmod($directory, 0777);
        }

        if (!is_null($site)) {
            $directory .= '/' . $site;
            if (!is_dir($directory)) {
                @mkdir($directory, 0777);
                @chmod($directory, 0777);
            }
        }

        if (!is_null($version)) {
            $directory .= '/' . $version;
            if (!is_dir($directory)) {
                @mkdir($directory, 0777);
                @chmod($directory, 0777);
            }
        }

        return $directory;
    }

    /**
     * Finalize all outputs and return the actual output for the html
     */
    public function finalizeAllOutputs($site, $version = null)
    {
        $groups = array(
            Collection::HEAD_START,
            Collection::TITLE_AFTER,
            Collection::HEAD_END,
            Collection::BODY_START,
            Collection::BODY_END
        );

        if (!is_null($this->stopwatch)) {
            $sw = $this->stopwatch->start('get all output', 'outputter');
        }

        $directory = $this->getPublicDirectory($site, $version);
        foreach($groups as $group) {
            $html = $this->collection->getGroup($group, $directory, $site, $version);

            $data[$group] = $html;
        }

        if (!is_null($this->stopwatch)) {
            $sw->stop();
        }

        return $data;
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

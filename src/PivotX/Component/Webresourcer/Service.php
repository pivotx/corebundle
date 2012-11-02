<?php

/**
 * This file is part of the PivotX Core bundle
 *
 * (c) Marcel Wouters / Two Kings <marcel@twokings.nl>
 */

namespace PivotX\Component\Webresourcer;

use Symfony\Component\HttpKernel\Log\LoggerInterface;
use PivotX\Component\Outputter\Service as OutputterService;

/**
 * A Webresourcer Service
 * 
 * A service on top of (or besides)  assetic to easily manage various webresources.
 * Activating a `webresource' automatically includes all necessary 
 * resources: images, stylesheets, scripts and later possibly other stuff. 
 * 
 * @todo make it work at all ;)
 * @todo add dependency management
 *
 * @author Marcel Wouters <marcel@twokings.nl>
 *
 * @api
 */
class Service
{
    private $logger;
    private $webresources;
    private $kernelservice;
    private $outputterservice;


    public function __construct(LoggerInterface $logger = null, \AppKernel $kernelservice, OutputterService $outputterservice)
    {
        $this->logger           = $logger;
        $this->kernelservice    = $kernelservice;
        $this->outputterservice = $outputterservice;

        DirectoryWebresource::setKernelService($this->kernelservice);
    }

    /**
     * Add a webresource
     */
    public function addWebresource(Webresource $webresource)
    {
        $this->webresources[] = $webresource;

        return true;
    }

    /**
     * Finalize webresources to the outputter
     */
    public function finalizeWebresources()
    {
        // @todo sort resources

        // output them
        foreach($this->webresources as $webresource) {
            $webresource->finalizeOutput($this->outputterservice);
        }
    }
}

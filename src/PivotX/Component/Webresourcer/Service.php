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
    private $collection;


    public function __construct(LoggerInterface $logger = null, \AppKernel $kernelservice, OutputterService $outputterservice)
    {
        $this->logger           = $logger;
        $this->kernelservice    = $kernelservice;
        $this->outputterservice = $outputterservice;

        DirectoryWebresource::setKernelService($this->kernelservice);

        $this->collection = new Collection();
    }

    /**
     * Add a webresource
     */
    public function addWebresource(Webresource $webresource)
    {
        return $this->collection->add($webresource);
    }

    /**
     * Add all webresources (inactivated) from a directory
     */
    public function addWebresourcesFromDirectory($directory_reference)
    {
        if (preg_match('|^@([^/]+)(.+)$|', $directory_reference, $match)) {
            $directory = $this->kernelservice->locateResource($directory_reference);
        }
        else {
            $directory = $directory_reference;
        }

        $files = scandir($directory);
        foreach($files as $file) {
            $full = $directory . '/' . $file;

            if (substr($file, 0, 1) != '.') {
                $this->collection->add(new DirectoryWebresource($full));
            }
        }
    }

    /**
     * Activate a webresource and enable all dependencies
     */
    public function activateWebresource($identifier)
    {
        return $this->collection->activate($identifier);
    }

    /**
     * Finalize webresources to the outputter
     */
    public function finalizeWebresources()
    {
        return $this->collection->finalize($this->outputterservice);
    }
}

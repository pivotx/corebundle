<?php

/**
 * This file is part of the PivotX Core bundle
 *
 * (c) Marcel Wouters / Two Kings <marcel@twokings.nl>
 */

namespace PivotX\Component\Webresourcer;

use PivotX\Component\Outputter\Service as OutputterService;
use PivotX\Component\Outputter\Output as Output;

/**
 * A Webresource describe a set of resources that are bundled together.
 *
 * @author Marcel Wouters <marcel@twokings.nl>
 *
 * @api
 */
class DirectoryWebresource extends Webresource
{
    protected static $kernelservice = null;

    public function __construct($directory_reference)
    {
        parent::__construct(false, false, array(), false);

        $this->doDirectorySetup($directory_reference);

    }

    /**
     * Internal call to set the Kernel Service for locating resources
     */
    public static function setKernelService($service)
    {
        self::$kernelservice = $service;
    }

    private function getActualDirectory($directory_reference)
    {
        if (preg_match('|^@([^/]+)(.+)$|', $directory_reference, $match)) {
            $bundlename = $match[1];

            return self::$kernelservice->locateResource($directory_reference);
        }

        return $directory_reference;
    }

    private function getFileInfo($filename)
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        $group = false;
        $type  = false;
        $ext   = false;
        switch ($extension) {
            case 'js':
                $group = OutputterService::BODY_END;
                $type  = Output::TYPE_SCRIPT_SRC;
                $ext   = 'js';
                break;
            case 'css':
                $group = OutputterService::HEAD_END;
                $type  = Output::TYPE_LINK_HREF;
                $ext   = 'css';
                break;
            case 'less':
                $group = OutputterService::HEAD_END;
                $type  = Output::TYPE_LINK_LESS_HREF;
                $ext   = 'less';
                break;
        }

        return array($group, $type, $ext);
    }

    private function doDirectorySetupViaScanning($directory)
    {
        $identifier   = false;
        $version      = false;
        $dependencies = array();
        $provides     = false;

        $basename = basename($directory);

        $files = scandir($directory);
        foreach($files as $file) {
            $full = $directory . '/' . $file;

            if ((substr($file, 0, 1) != '.') && (is_dir($full))) {
                $subfiles = scandir($full);

                foreach($subfiles as $subfile) {
                    if (substr($subfile, 0, 1) == '.') {
                        continue;
                    }

                    list($group, $type, $ext) = $this->getFileInfo($subfile);

                    $subfull = $full . '/' . $subfile;
                    if (is_file($subfull)) {
                        $output = new Output($subfull, $type);
                        $this->addOutput($group, $output);
                    }

                    if (($version === false) && (preg_match('|^'.$basename.'[-.](.+?)([.]min)?[.]'.$ext.'|', $subfile, $match))) {
                        $version = $match[1];
                    }
                }
            }
        }


        $identifier = $basename;
        if ($version === false) {
            $version = '0.0.1';
        }

        $this->setIdentifier($identifier);
        $this->setVersion($version);
        $this->setDependencies($dependencies);
        $this->setProvides($identifier);
    }

    /**
     * @todo no error checking
     */
    private function doDirectorySetupViaJson($filename, $directory)
    {
        $data = json_decode(file_get_contents($filename), true);

        if (is_null($data)) {
            // @todo throw error
            die('Cannot JSON-decode "'.$filename.'"!');
        }

        if (isset($data['identifier'])) {
            $this->setIdentifier($data['identifier']);
            $this->setProvides($data['identifier']);
        }
        if (isset($data['version'])) {
            $this->setVersion($data['version']);
        }
        if (isset($data['dependencies'])) {
            $this->setDependencies($data['dependencies']);
        }
        if (isset($data['provides'])) {
            $this->setProvides($identifier);
        }

        if (isset($data['files'])) {
            $files = $data['files'];

            foreach($files as $file) {
                list($group, $type, $ext) = $this->getFileInfo($file);

                if (strstr($file, '*') === false) {
                    $full = $directory . '/' . $file;
                    if (is_file($full)) {
                        $output = new Output($full, $type);
                        $this->addOutput($group, $output);
                    }
                }
                else {
                    foreach(glob($directory.'/'.$file) as $gfile) {
                        $output = new Output($gfile, $type);
                        $this->addOutput($group, $output);
                    }
                }
            }
        }
    }

    private function doDirectorySetup($directory_reference)
    {
        $directory = $this->getActualDirectory($directory_reference);

        if (is_file($directory)) {
            $this->doDirectorySetupViaJson($directory, dirname($directory));
        }
        else if (file_exists($directory.'/webresource.json')) {
            $this->doDirectorySetupViaJson($directory.'/webresource.json', $directory);
        }
        else {
            $this->doDirectorySetupViaScanning($directory);
        }
    }
}

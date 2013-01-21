<?php

/*
 * This file is part of the PivotX package.
 */

namespace PivotX\Component\Siteoptions;

use Doctrine\Bundle\DoctrineBundle\Registry;
use PivotX\Doctrine\Configuration\YamlConfiguration;
use Symfony\Component\Yaml\Yaml;


/**
 * This is the setup/update for siteoptions
 *
 * @author Marcel Wouters <marcel@twokings.nl>
 */
class Setup
{
    private $doctrine;
    private $siteoptions_service;

    /**
     * Constructor.
     *
     * @param RegistryInterface $registry A RegistryInterface instance
     */
    public function __construct(Registry $doctrine, $siteoptions_service)
    {
        //echo 'PivotX Doctrine Warmer'."\n";

        $this->doctrine = $doctrine;
        $this->siteoptions_service = $siteoptions_service;
    }

    /**
     */
    private function getSiteoptionsSuggestionsFilename($name)
    {
        return dirname(dirname(dirname(dirname(__FILE__)))).'/PivotX/CoreBundle/Resources/suggestions/siteoptions.'.$name.'.yaml';
    }

    /**
     * Add all missing backend options
     *
     * @param string $site  options site to set
     * @return boolean      true if some translations have been added
     */
    private function updateOptions($site)
    {
        $filename = $this->getSiteoptionsSuggestionsFilename($site)
        $siteoptions = \Symfony\Component\Yaml\Yaml::parse($filename);

        foreach($siteoptions as $group => $values) {
            foreach($values as $key => $keyvalues) {
                $value          = '';
                $mediatype      = 'text/plain';
                $autoload       = false;
                $human_editable = false;

                if (!is_array($keyvalues)) {
                    $value = $keyvalues;
                }
                else {
                    if (isset($keyvalues['value'])) {
                        $value = $keyvalues['value'];
                    }
                    if (isset($keyvalues['mediatype'])) {
                        $mediatype = $keyvalues['mediatype'];
                    }
                    if (isset($keyvalues['autoload'])) {
                        $autoload = $keyvalues['autoload'];
                    }
                    if (isset($keyvalues['human_editable'])) {
                        $human_editable = $keyvalues['human_editable'];
                    }
                }

                switch ($mediatype) {
                    case 'application/json':
                        $value = json_encode($value);
                        break;
                }

                $this->siteoptions_service->suggestValue($group, $key, $site, $value, $mediatype, $autoload, $human_editable);
            }
        }

        return true;
    }

    /**
     * Add all missing backend options
     *
     * @return boolean    true if some translations have been added
     */
    public function updateBackendOptions()
    {
        $this->updateOptions('pivotx-backend');
        $this->updateOptions('all');
    }
}

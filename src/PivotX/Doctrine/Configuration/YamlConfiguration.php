<?php

/*
 * @todo not needed yet
 * This file is part of the PivotX package.
 */

namespace PivotX\Doctrine\Configuration;

use Symfony\Component\Yaml\Yaml;


/**
 * YamlConfiguration
 *
 * @author Marcel Wouters <marcel@twokings.nl>
 */
class YamlConfiguration extends Configuration
{

    public function __construct($filename)
    {
        $array = \Symfony\Component\Yaml\Yaml::parse($filename);

        $this->parseArray($array);
    }

    protected function parseArray($array)
    {
        $this->clearConfiguration();

        // read auto_entity definitions and parse them into ->fields
        $instance = null;
        foreach($array as $entity) {
            foreach($entity['fields'] as $field => $definition) {
                if (isset($definition['auto_entity'])) {
                    foreach($definition['auto_entity'] as $feature => $config) {
                        if (!isset($this->features[$feature])) {
                            $this->features[$feature] = array();
                        }

                        $this->features[$feature][] = array($field, $config);
                    }
                }
            }

            if (isset($entity['auto_entity'])) {
                foreach($entity['auto_entity'] as $feature => $config) {
                    if (!isset($this->features[$feature])) {
                        $this->features[$feature] = array();
                    }

                    $this->features[$feature][] = array(null, $config);
                }
            }
        }

        /*
        echo 'Source:'."\n";
        var_dump($array);
        echo "\n".'Features'."\n";
        var_dump($this->features);
        echo "\n\n";
        //*/
    }
}

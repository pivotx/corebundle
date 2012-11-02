<?php

/*
 * This file is part of the PivotX package.
 */

namespace PivotX\Doctrine\Configuration;



/**
 * 
 *
 * @author Marcel Wouters <marcel@twokings.nl>
 */
class Configuration
{
    protected $features;

    /**
     */
    protected function clearConfiguration()
    {
        $this->features = array();
    }

    /**
     * Return the feature class based on name
     *
     * @todo only works for PivotX stuff now
     */
    protected function getFeatureClass($name)
    {
        $classpath = ucfirst($name);
        if (substr($name, 0, 7) == 'pivotx_') {
            $classpath = 'PivotX\\'.ucfirst(substr($name, 7));
        }

        $class = '\\PivotX\\Doctrine\\Feature\\'.$classpath.'\\EntityConfiguration';
        if (class_exists($class)) {
            return new $class;
        }

        return false;
    }

    /**
     */
    public function getFeatures()
    {
        return $this->features;
    }
}

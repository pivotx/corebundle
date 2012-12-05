<?php

namespace PivotX\Doctrine\Feature\Timesliceable;


class ObjectRepository implements \PivotX\Doctrine\Entity\EntityRepository
{
    private $fields = null;
    private $metaclassdata = null;

    public function __construct(array $fields, $metaclassdata)
    {
        $this->fields        = $fields;
        $this->metaclassdata = $metaclassdata;
    }

    /**
     * Get feature methods independent of field configuration
     */
    public function getPropertyMethodsForEntity($config)
    {
        $methods = array();

        $methods['addGeneratedViews'] = 'generateAddGeneratedViews';

        return $methods;
    }

    /**
     * Get feature methods dependent on field configuration
     */
    public function getPropertyMethodsForField($field, $config)
    {
        return array();
    }

    public function generateAddGeneratedViews($classname, $config)
    {
        return <<<THEEND
    /**
     * Add generated views
     * 
%comment%
     */
    public function addGeneratedViews()
    {
        // do nothing yet
    }
THEEND;
    }
}


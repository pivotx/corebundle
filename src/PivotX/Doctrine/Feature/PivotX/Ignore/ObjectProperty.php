<?php

namespace PivotX\Doctrine\Feature\PivotX\Ignore;


class ObjectProperty implements \PivotX\Doctrine\Entity\EntityProperty
{
    private $fields = null;
    private $metaclassdata = null;

    public function __construct(array $fields, $metaclassdata)
    {
        $this->fields        = $fields;
        $this->metaclassdata = $metaclassdata;
    }

    public function getPropertyMethodsForField($field, $config)
    {
        $methods = array();

        $methods['getCrudConfiguration_'.$field] = 'generateGetCrudConfiguration';

        return $methods;
    }

    public function generateGetCrudConfiguration($classname, $field, $config)
    {
        return <<<THEEND
    /**
     * Return the CRUD field configuration
     * 
%comment%
     */
    public function getCrudConfiguration_$field()
    {
        return array(
            'name' => '$field',
            'type' => false
        );
    }
THEEND;
    }
}


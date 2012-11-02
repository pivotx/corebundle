<?php

namespace PivotX\Doctrine\Feature\PivotX\Mediatype;


class ObjectProperty implements \PivotX\Doctrine\Entity\EntityProperty
{
    private $fields = null;

    public function __construct(array $fields)
    {
        $this->fields = $fields;
    }

    public function getPropertyMethodsForField($field)
    {
        $methods = array();

        $methods['getCrudConfiguration_'.$field] = 'generateGetCrudConfigurationMedia';

        return $methods;
    }

    public function generateGetCrudConfigurationMedia($classname, $field, $config)
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
            'type' => 'choice',
            'choices' => array(
                'text/x-line' => 'Single line',
                'text/plain' => 'Multiple lines',
                'text/html' => 'HTML',
                'text/xml' => 'XML',
                'text/x-yaml' => 'YAML',
                'application/json' => 'JSON',
            )
        );
    }
THEEND;
    }
}


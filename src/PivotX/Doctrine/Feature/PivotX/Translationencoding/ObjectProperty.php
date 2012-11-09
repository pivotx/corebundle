<?php

namespace PivotX\Doctrine\Feature\PivotX\Translationencoding;


class ObjectProperty implements \PivotX\Doctrine\Entity\EntityProperty
{
    private $fields = null;

    public function __construct(array $fields)
    {
        $this->fields = $fields;
    }

    public function getPropertyMethodsForField($field, $config)
    {
        $methods = array();

        $methods['getCrudConfiguration_'.$field] = 'generateGetCrudConfigurationEncoding';

        return $methods;
    }

    public function generateGetCrudConfigurationEncoding($classname, $field, $config)
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
                'utf-8' => 'text/UTF-8',
                'utf-8/html' => 'html/UTF-8'
            )
        );
    }
THEEND;
    }
}


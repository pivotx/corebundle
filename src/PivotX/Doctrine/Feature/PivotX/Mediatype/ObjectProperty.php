<?php

namespace PivotX\Doctrine\Feature\PivotX\Mediatype;


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

        switch ($config['type']) {
            case 'mediatype':
                $methods['getCrudConfiguration_'.$field] = 'generateGetCrudConfigurationMedia';
                break;
            case 'value':
                $methods['getCrudConfiguration_'.$field] = 'generateGetCrudConfigurationValue';
                break;
        }

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

                'x-value/boolean' => 'Boolean value',
            )
        );
    }
THEEND;
    }

    public function generateGetCrudConfigurationValue($classname, $field, $config)
    {
        $mediatype_field = null;
        foreach($this->fields as $lfield) {
            if ($lfield[1]['type'] == 'mediatype') {
                $mediatype_field = $lfield[0];
            }
        }

        // @todo throw an exception?
        if (is_null($mediatype_field)) {
            return '';
        }

        return <<<THEEND
    /**
     * Return the CRUD field configuration
     * 
%comment%
     */
    public function getCrudConfiguration_$field()
    {
        \$config = array(
            'name' => '$field',
            'type' => 'textarea'
        );

        switch (\$this->$mediatype_field) {
            case 'x-value/boolean':
                \$config['type'] = 'choice';
                \$config['choices'] = array(
                    '0' => 'no',
                    '1' => 'yes'
                );
                break;

            case 'text/x-line':
                break;

            case 'text/plain':
            case 'text/html':
            case 'text/xml':
            case 'text/x-yaml':
            case 'application/json':
                \$config['type'] = 'textarea';
                break;
        }

        return \$config;
    }
THEEND;
    }
}


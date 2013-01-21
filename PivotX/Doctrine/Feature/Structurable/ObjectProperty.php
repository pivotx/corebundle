<?php

namespace PivotX\Doctrine\Feature\Structurable;


class ObjectProperty implements \PivotX\Doctrine\Entity\EntityProperty
{
    private $fields = null;
    private $metaclassdata = null;

    public function __construct(array $fields, $metaclassdata)
    {
        $this->fields        = $fields;
        $this->metaclassdata = $metaclassdata;
    }

    public function getPropertyMethodsForEntity($config)
    {
        $methods = array();

        $methods['getCrudTableConfiguration'] = 'generateGetCrudTableConfiguration';

        return $methods;
    }

    public function getPropertyMethodsForField($field, $config)
    {
        $methods = array();

        foreach($this->fields as $lfield) {
            if ($lfield[0] == $field) {
                switch ($lfield[1]['kind']) {
                    case 'order':
                        $methods['getCrudConfiguration_'.$field] = 'generateGetCrudConfigurationOrder';
                        break;
                    case 'parent':
                        break;
                }
            }
        }

        return $methods;
    }

    public function generateGetCrudTableConfiguration($classname, $config)
    {
        return <<<THEEND
    /**
     * Return the CRUD table configuration
     * 
     * @PivotX\Internal       internal use only
%comment%
     */
    public function getCrudTableConfiguration()
    {
        return array(
            'type' => 'sortable'
        );
    }
THEEND;
    }

    public function generateGetCrudConfigurationOrder($classname, $field, $config)
    {
        return <<<THEEND
    /**
     * Return the CRUD field configuration
     * 
     * @PivotX\Internal       internal use only
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

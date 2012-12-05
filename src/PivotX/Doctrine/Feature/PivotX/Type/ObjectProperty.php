<?php

namespace PivotX\Doctrine\Feature\PivotX\Type;


class ObjectProperty implements \PivotX\Doctrine\Entity\EntityProperty
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
        return array();
    }

    /**
     * Get feature methods dependent on field configuration
     */
    public function getPropertyMethodsForField($field, $config)
    {
        $methods = array();

        $methods['getCrudConfiguration_'.$field] = 'generateGetCrudConfiguration';

        return $methods;
    }

    public function generateGetCrudConfiguration($classname, $field, $config)
    {
        $type = $config['type'];

        $more = '';
        if (isset($config['choices'])) {
            $more .= ",\n";
            $more .= "           'choices' => array(\n";
            foreach($config['choices'] as $key => $value) {
                $more .= "               '$key' => '$value',\n";
            }
            $more  = substr($more, 0, -2) . "\n";
            $more .= "           )\n";
            $more  = rtrim($more);
        }

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
            'type' => '$type'$more
        );
    }
THEEND;
    }
}


<?php

namespace PivotX\Doctrine\Feature\PivotX\Backendresource;


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
        return <<<THEEND
    /**
     * Return the CRUD field configuration
     * 
     * @PivotX\Internal       internal use only
%comment%
     */
    public function getCrudConfiguration_$field()
    {
        \$file_info = array(
            'valid' => true,
            'mimetype' => '',
            'size' => 0,
            'name' => ''
        );
        \$file_info['json'] = json_encode(\$file_info);

        return array(
            'name' => '$field',
            'type' => 'backend_resource',
            'arguments' => array(
                'attr' => array('multiple' => false),
                'files' => array(\$file_info)
            )
        );
    }
THEEND;
    }
}


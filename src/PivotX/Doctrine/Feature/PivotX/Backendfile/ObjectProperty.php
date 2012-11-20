<?php

namespace PivotX\Doctrine\Feature\PivotX\Backendfile;


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
        \$file_info = array(
            'valid' => true,
            'mimetype' => \$this->media_type,
            'size' => \$this->filesize,
            'name' => \$this->filename
        );
        \$file_info['json'] = json_encode(\$file_info);

        return array(
            'name' => '$field',
            'type' => 'backend_file',
            'arguments' => array(
                'attr' => array('multiple' => false),
                'files' => array(\$file_info)
            )
        );
    }
THEEND;
    }
}


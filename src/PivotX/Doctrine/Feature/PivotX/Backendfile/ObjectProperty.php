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
        $methods['preRemove_'.$field]            = 'generatePreRemove';

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

    public function generatePreRemove($classname, $field, $config)
    {
        return <<<THEEND
    /**
     * Remove the actual file
     * 
     * @PivotX\Internal       internal use only
%comment%
     */
    public function preRemove_$field()
    {
        \$filename = \$this->getRealFilename();
        if (file_exists(\$filename)) {
            @unlink(\$filename);
        }
    }
THEEND;
    }
}


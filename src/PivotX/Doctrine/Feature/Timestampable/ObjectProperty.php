<?php

namespace PivotX\Doctrine\Feature\Timestampable;


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

        foreach($this->fields as $lfield) {
            if ($lfield[0] == $field) {
                switch ($lfield[1]['on']) {
                    case 'create':
                        $methods['prePersist_'.$field] = 'generatePrePersistOnCreate';
                        break;
                    case 'update':
                        $methods['prePersist_'.$field] = 'generatePrePersistOnUpdate';
                        $methods['preUpdate_'.$field] = 'generatePreUpdateOnUpdate';
                        break;
                }
            }
        }

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

    public function generatePrePersistOnCreate($classname, $field, $config)
    {
        return <<<THEEND
    /**
     * PrePersist the creation timestamp
     * 
%comment%
     */
    public function prePersist_$field()
    {
        if (is_null(\$this->$field)) {
            \$this->$field = new \\DateTime;
        }
    }
THEEND;
    }

    public function generatePrePersistOnUpdate($classname, $field, $config)
    {
        return <<<THEEND
    /**
     * PrePersist the update timestamp
     * 
%comment%
     */
    public function prePersist_$field()
    {
        \$this->$field = new \\DateTime;
    }
THEEND;
    }

    public function generatePreUpdateOnUpdate($classname, $field, $config)
    {
        return <<<THEEND
    /**
     * PrePersist the update timestamp
     * 
%comment%
     */
    public function preUpdate_$field()
    {
        \$this->$field = new \\DateTime;
    }
THEEND;
    }
}


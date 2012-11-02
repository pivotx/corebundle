<?php

namespace PivotX\Doctrine\Feature\Timestampable;


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

        $methods['getCrudConfiguration_'.$field] = 'generateGetCrudConfiguration';

        foreach($this->fields as $lfield) {
            if ($lfield[0] == $field) {
                switch ($lfield[1]['on']) {
                    case 'create':
                        $methods['setPrePersist_'.$field] = 'generateSetPrePersistOnCreate';
                        break;
                    case 'update':
                        $methods['setPrePersist_'.$field] = 'generateSetPrePersistOnUpdate';
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

    public function generateSetPrePersistOnCreate($classname, $field, $config)
    {
        return <<<THEEND
    /**
     * PrePersist the creation timestamp
     * 
%comment%
     */
    public function setPrePersist_$field()
    {
        if (is_null(\$this->$field)) {
            \$this->$field = new \\DateTime;
        }
    }
THEEND;
    }

    public function generateSetPrePersistOnUpdate($classname, $field, $config)
    {
        return <<<THEEND
    /**
     * PrePersist the update timestamp
     * 
%comment%
     */
    public function setPrePersist_$field()
    {
        \$this->$field = new \\DateTime;
    }
THEEND;
    }
}


<?php

namespace PivotX\Doctrine\Feature\Genericable;


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

        $methods['getGenericTitle'] = 'generateGenericTitle';

        return $methods;
    }

    public function getPropertyMethodsForField($field, $config)
    {
        $methods = array();

        return $methods;
    }

    public function generateGenericTitle($classname, $config)
    {
        $title_field = false;

        // @todo read actual configuration

        if ($title_field === false) {
            foreach($this->metaclassdata->fieldMappings as $name => $data) {
                if (in_array($name, array('title', 'name', 'email'))) {
                    $title_field = $name;
                    break;
                }
            }
        }

        if ($title_field === false) {
            // @todo
            $title_field = 'id';
        }

        return <<<THEEND
    /**
     * Returns the generic title for this object
     *
%comment%
     */
    public function getGenericTitle()
    {
        return \$this->$title_field;
    }

THEEND;
    }
}


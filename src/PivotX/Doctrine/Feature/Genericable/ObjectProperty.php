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

        $methods['getGenericTitle']       = 'generateGenericTitle';
        $methods['getGenericDescription'] = 'generateGenericDescription';

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

        if (isset($config['title'])) {
            $title_field = $config['title'];
        }

        if ($title_field === false) {
            // @todo
            $title_field = 'id';
        }

        $code = 'return \'\';';
        if ($title_field != false) {
            $code = 'return $this->'.$title_field.';';
        }

        return <<<THEEND
    /**
     * Returns the generic title for this object
     *
%comment%
     */
    public function getGenericTitle()
    {
        $code
    }

THEEND;
    }

    public function generateGenericDescription($classname, $config)
    {
        $description_field = false;

        if (isset($config['description'])) {
            $description_field = $config['description'];
        }

        $code = 'return \'\';';
        if ($description_field != false) {
            $code = 'return $this->'.$description_field.';';
        }

        return <<<THEEND
    /**
     * Returns the generic description for this object
     *
%comment%
     */
    public function getGenericDescription()
    {
        $code
    }

THEEND;
    }
}


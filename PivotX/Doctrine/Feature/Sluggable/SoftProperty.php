<?php

namespace PivotX\Doctrine\Feature\Sluggable;


class SoftProperty implements \PivotX\Doctrine\Entity\SoftProperty
{
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function modifyOrmField($field, $definition)
    {
        if (isset($definition['arguments'])) {
            $field['auto_entity']['sluggable']['format'] = $definition['arguments'];
        }
        return $field;
    }
}

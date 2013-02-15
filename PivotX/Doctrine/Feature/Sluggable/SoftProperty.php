<?php

namespace PivotX\Doctrine\Feature\Sluggable;


class SoftProperty implements \PivotX\Doctrine\Entity\SoftProperty
{
    private $entity;

    public function __construct($entity)
    {
        $this->entity = $entity;
    }

    public function modifyOrmField($field, $definition)
    {
        if ($definition->hasArguments()) {
            $field['auto_entity']['sluggable']['format'] = $definition->getArguments();
        }
        return $field;
    }
}

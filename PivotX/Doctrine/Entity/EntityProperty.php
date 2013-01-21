<?php

namespace PivotX\Doctrine\Entity;

interface EntityProperty
{
    /**
     * Get feature methods independent of field configuration
     */
    public function getPropertyMethodsForEntity($config);

    /**
     * Get feature methods dependent on field configuration
     */
    public function getPropertyMethodsForField($field, $config);
}

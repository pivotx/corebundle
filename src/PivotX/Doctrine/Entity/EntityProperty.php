<?php

namespace PivotX\Doctrine\Entity;

interface EntityProperty
{
    public function getPropertyMethodsForField($field, $config);
}

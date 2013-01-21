<?php

namespace PivotX\Doctrine\Entity;

interface SoftProperty
{
    public function __construct($config);

    public function modifyOrmField($field, $definition);
}

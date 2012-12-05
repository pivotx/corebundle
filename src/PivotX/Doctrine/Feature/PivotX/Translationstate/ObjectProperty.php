<?php

namespace PivotX\Doctrine\Feature\PivotX\Translationstate;


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

        $methods['getCrudConfiguration_'.$field] = 'generateGetCrudConfigurationState';

        return $methods;
    }

    public function generateGetCrudConfigurationState($classname, $field, $config)
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
            'type' => 'choice',
            'choices' => array(
                self::STATE_VALID => 'valid',
                self::STATE_SUGGESTED => 'suggested value',
                self::STATE_AUTO_TECHNICAL => 'auto-filled with key name logic',
                self::STATE_AUTO_LOREM => 'auto-filled with lorem ipsum',
                self::STATE_AUTO_REUSED => 'old value is reused',
                self::STATE_OLD => 'old value, allow reuse',
                self::STATE_OLD_LOCKED => 'old value, don\'t allow reuse'
            )
        );
    }
THEEND;
    }
}


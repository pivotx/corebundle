<?php

namespace PivotX\Doctrine\Feature\Passwordable;


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

        foreach($this->fields as $lfield) {
            if ($lfield[0] == $field) {
                switch ($lfield[1]['type']) {
                    case 'salt':
                        $methods['getCrudConfiguration_'.$field] = 'generateGetCrudConfigurationSalt';
                        break;
                    case 'password':
                        $methods['getCrudConfiguration_'.$field] = 'generateGetCrudConfigurationPassword';
                        $methods['setEncoderFactory_'.$field] = 'generateSetEncoderFactory';
                        // @todo should also create setPasswd
                        break;
                }
            }
        }

        return $methods;
    }

    public function generateGetCrudConfigurationSalt($classname, $field, $config)
    {
        return <<<THEEND
    /**
     * Return the CRUD field configuration
     * 
     * @PivotX\Internal       internal use only
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

    public function generateGetCrudConfigurationPassword($classname, $field, $config)
    {
        $first_name  = $field;
        $second_name = $field.'_repeat';
        return <<<THEEND
    /**
     * Return the CRUD field configuration
     * 
     * @PivotX\Internal       internal use only
%comment%
     */
    public function getCrudConfiguration_$field()
    {
        return array(
            'name' => '$field',
            'type' => 'repeated',
            'arguments' => array(
                'type' => 'password',
                'first_name' => '$first_name',
                'second_name' => '$second_name'
            ),
            'setencoderfactory' => 'setEncoderFactory_$field'
        );
    }
THEEND;
    }

    public function generateSetEncoderFactory($classname, $field, $config)
    {
        return <<<THEEND
    /**
     * Set the encoder factory
     * 
     * @PivotX\Internal       internal use only
%comment%
     */
    public function setEncoderFactory_$field(\$encoder_factory)
    {
        \$this->encoder_factory_$field = \$encoder_factory;
    }
THEEND;
    }
}


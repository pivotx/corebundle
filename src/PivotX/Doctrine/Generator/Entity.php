<?php
namespace PivotX\Doctrine\Generator;


/**
 * This is an entity-generator for a YAML defined entity.
 */
class Entity
{
    private $metaclassdata = null;
    private $feature_configuration = null;

    public function __construct($metaclassdata, $feature_configuration)
    {
        $this->metaclassdata         = $metaclassdata;
        $this->feature_configuration = $feature_configuration;
    }

    /**
     * Get the feature generator class
     * 
     * @todo should do a proper lookup
     */
    public function getFeatureGeneratorClass($feature)
    {
        $classpath = ucfirst($feature);
        if (substr($feature, 0, 7) == 'pivotx_') {
            $classpath = 'PivotX\\'.ucfirst(substr($feature, 7));
        }

        $class = '\\PivotX\\Doctrine\\Feature\\'.$classpath.'\\ObjectProperty';
        if (class_exists($class)) {
            return $class;
        }

        return null;
    }

    public function getUpdatedCode($code)
    {
        $features = $this->feature_configuration->getFeatures();

        $field_generators = array();
        foreach($features as $feature => $fields) {
            $generator_class = $this->getFeatureGeneratorClass($feature);
            $generator       = null;

            if (!is_null($generator_class)) {
                $generator = new $generator_class($fields, $this->metaclassdata);

                $field_generators[] = array($generator, null, null);

                foreach($fields as $field) {
                    $field_generators[] = array($generator, $field[0], $field[1]);
                }
            }
        }

        $add_methods     = array();
        $remove_methods  = array();
        $classname       = $this->metaclassdata->name;
        $entity_class    = new $classname();
        foreach($field_generators as $field_generator) {
            $generator = $field_generator[0];
            $field     = $field_generator[1];
            $config    = $field_generator[2];

            if (is_null($field)) {
                $methods = $generator->getPropertyMethodsForEntity($config);
            }
            else {
                $methods = $generator->getPropertyMethodsForField($field, $config);
            }
            foreach($methods as $name => $method) {
                if (is_null($field)) {
                    $args = array($classname, $config);
                }
                else {
                    $args = array($classname, $field, $config);
                }

                $generate_method = false;
                if (!method_exists($entity_class, $name)) {
                    $generate_method = true;
                }
                else {
                    // check method version (for now, we always regenerate)
                    $generate_method  = true;
                }

                if ($generate_method) {
                    // method doesn't exist, add it
                    $method_code = call_user_func_array(array($generator, $method),$args)."\n";

                    $add_methods[$name] = $method_code;
                }
            }
        }

        $default_comment = Code::getDefaultComment();

        $new_code = Code::mangleClass($entity_class, $code, $add_methods, $remove_methods);

        return $new_code;
    }
}
?>

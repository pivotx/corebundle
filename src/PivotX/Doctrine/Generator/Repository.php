<?php
namespace PivotX\Doctrine\Generator;


/**
 * This is an repository-generator for a YAML defined entity.
 */
class Repository
{
    private $entity_manager = null;
    private $metaclassdata = null;
    private $feature_configuration = null;

    public function __construct($entity_manager, $metaclassdata, $feature_configuration)
    {
        $this->entity_manager        = $entity_manager;
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

        $class = '\\PivotX\\Doctrine\\Feature\\'.$classpath.'\\ObjectRepository';
        if (class_exists($class)) {
            return $class;
        }

        return null;
    }

    private function generateAddGeneratedViews($comment, $code)
    {
        $method_code = <<<THEEND
    /**
     * Add generated views
     * 
$comment
     */
    public function addGeneratedViews(\\PivotX\\Component\\Views\\Service \$service, \$prefix)
    {
$code
    }
THEEND;

        return $method_code;
    }

    public function getUpdatedCode($code)
    {
        $features = $this->feature_configuration->getFeatures();

        $field_generators = array();
        foreach($features as $feature => $fields) {
            $generator_class = $this->getFeatureGeneratorClass($feature);
            $generator       = null;

            if (!is_null($generator_class)) {
                echo 'Repository generator for feature "'.$feature.'"'."\n";
                $generator = new $generator_class($fields, $this->metaclassdata);

                $field_generators[] = array($generator, null, null);

                foreach($fields as $field) {
                    $field_generators[] = array($generator, $field[0], $field[1]);
                }
            }
            else {
                echo 'There is no Repository generator for feature "'.$feature.'"'."\n";
            }
        }

        $add_methods     = array();
        $remove_methods  = array();
        $classname       = str_replace('\\Entity\\','\\Model\\', $this->metaclassdata->name).'Repository';

        if (!class_exists($classname)) {
            return $code;
        }


        // add methods and code as returned by the feature configurations

        $repository_class     = new $classname($this->entity_manager, $this->metaclassdata);
        $generated_views_code = '';
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
            foreach($methods as $name => $_method) {
                if (is_null($field)) {
                    $args = array($classname, $config);
                }
                else {
                    $args = array($classname, $field, $config);
                }
                if (is_array($_method)) {
                    $method = $_method[0];
                    $_args  = $_method;
                    array_shift($_args);
                    foreach($_args as $a) {
                        $args[] = $a;
                    }
                }
                else {
                    $method = $_method;
                }

                $generate_method = false;
                if (!method_exists($repository_class, $name)) {
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
            $generated_views_code .= $generator->getViewsForEntity($config);
        }

        $default_comment = Code::getDefaultComment();

        $add_methods['addGeneratedViews'] = $this->generateAddGeneratedViews($default_comment, $generated_views_code);

        $new_code = Code::mangleClass($repository_class, $code, $add_methods, $remove_methods);

        return $new_code;
    }
}
?>

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


    public function cleanEmptyLines($code_lines, $remove_line_code)
    {
        $removed  = 0;
        $cleaning = false;
        for($i=2; $i < count($code_lines); $i++) {
            if ($cleaning) {
                if ((trim($code_lines[$i]) == '') || ($code_lines[$i] == $remove_line_code)) {
                    $code_lines[$i] = $remove_line_code;
                    $removed++;
                }
                else {
                    $cleaning = false;
                }
            }
            else if (((trim($code_lines[$i-2]) == '') || ($code_lines[$i-2] == $remove_line_code)) && 
                     ((trim($code_lines[$i-1]) == '') || ($code_lines[$i-1] == $remove_line_code)) &&
                     ((trim($code_lines[$i  ]) == '') || ($code_lines[$i  ] == $remove_line_code))) {
                $code_lines[$i-1] = $remove_line_code;
                $code_lines[$i  ] = $remove_line_code;
                $removed++;
                $removed++;
                $cleaning         = true;
            }
        }

        return $code_lines;
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
                $generator = new $generator_class($fields, $this->metaclassdata);

                foreach($fields as $field) {
                    $field_generators[] = array($generator, $field[0], $field[1]);
                }
            }
        }

        $generated_on = date('Y-m-d, H:i:s');
        $default_comment = <<<THEEND
     * @author PivotX Generator
     *
     * Generated on $generated_on
THEEND;

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
                    $args[] = $_method[1];
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

                    $method_code = str_replace('%comment%', $default_comment, $method_code);

                    $add_methods[$name] = $method_code;
                }
            }
            $generated_views_code .= $generator->getViewsForEntity($config);
        }
        $add_methods['addGeneratedViews'] = $this->generateAddGeneratedViews($default_comment, $generated_views_code);


        /**
         * now we are actually going to update the code
         *
         * to remove lines we first replace them with a dummy comment.
         */

        $reflclass = new \reflectionclass($repository_class);

        $code_lines = explode("\n", $code);
        $remove_line_code = '// remove this line';

        foreach($remove_methods as $method) {
            // @todo do something here
        }

        $line = $reflclass->getendline() - 1;
        while (($line > 0) && (trim($code_lines[$line]) == '')) {
            $code_lines[$line] = $remove_line_code;
            $line--;
        }

        $new_code = '';
        foreach($add_methods as $method => $code) {
            if (method_exists($repository_class, $method)) {
                $reflmethod = new \reflectionmethod($repository_class, $method);

                $start_line = $reflmethod->getstartline() - 1;
                $end_line   = $reflmethod->getendline();

                $doccomment = $reflmethod->getdoccomment();
                if ($doccomment !== false) {
                    $start_line -= count(explode("\n", $doccomment));
                }

                for($line=$start_line; $line < $end_line; $line++) {
                    $code_lines[$line] = $remove_line_code;
                }
            }

            if ($new_code != '') {
                $new_code .= "\n";
            }
            $new_code .= $code;
        }

        array_splice($code_lines, $reflclass->getendline()-1, 0, array($new_code));

        $code_lines = $this->cleanEmptyLines($code_lines, $remove_line_code);

        $lines = array();
        foreach($code_lines as $line) {
            if ($line != $remove_line_code) {
                $lines[] = $line;
            }
        }

        return implode("\n", $lines);
    }
}
?>

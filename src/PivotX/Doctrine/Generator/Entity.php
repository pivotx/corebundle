<?php
namespace PivotX\Doctrine\Generator;


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
                $generator = new $generator_class($fields);

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
        $classname       = $this->metaclassdata->name;
        $entity_class    = new $classname();
        foreach($field_generators as $field_generator) {
            $generator = $field_generator[0];
            $field     = $field_generator[1];
            $config    = $field_generator[2];

            $methods = $generator->getPropertyMethodsForField($field);
            foreach($methods as $name => $method) {
                $args = array($classname, $field, $config);

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

                    $method_code = str_replace('%comment%', $default_comment, $method_code);

                    $add_methods[$name] = $method_code;
                }
            }
        }


        /**
         * now we are actually gonna update the code
         *
         * to remove lines we first replace them with a dummy comment.
         */

        $reflclass = new \reflectionclass($entity_class);

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
            if (method_exists($entity_class, $method)) {
                $reflmethod = new \reflectionmethod($entity_class, $method);

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

        $lines = array();
        foreach($code_lines as $line) {
            if ($line != $remove_line_code) {
                $lines[] = $line;
            }
        }

        return implode("\n", $lines);
    }

    public function __getUpdatedCode($code)
    {
        $features   = $this->feature_configuration->getFeatures();
        $generators = array();
        foreach($features as $feature => $fields) {
            $generator_class = $this->getFeatureGeneratorClass($feature);

            if (!is_null($generator_class)) {
                //echo 'update for feature '.$feature.", generator $generator_class\n";
                $generators[] = array(new $generator_class($fields), $fields);
            }
            else {
                // @todo throw exception here because feature doesn't exist?
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
        $classname       = $this->metaclassdata->name;
        $entity_class    = new $classname();
        foreach($generators as $generator) {
            $fields = $generator->getPropertyMethodsPerField();
            foreach($fields as $field => $methods) {
                foreach($methods as $name => $method) {
                    //echo "field $field, name $name, methodcall $method\n";

                    $args = array($classname);

                    $generate_method = false;
                    if (!method_exists($entity_class, $name)) {
                        $generate_method = true;
                    }
                    else {
                        // check method version (for now, we always regenerate)
                        $generate_method  = true;
                    }

                    if ($generate_method) {
                        echo "adding $method\n";
                        // method doesn't exist, add it
                        $method_code = call_user_func_array(array($generator, $method),$args)."\n";

                        $method_code = str_replace('%comment%', $default_comment, $method_code);

                        $add_methods[$name] = $method_code;
                    }
                }
            }
        }


        /**
         * now we are actually gonna update the code
         *
         * to remove lines we first replace them with a dummy comment.
         */

        $reflclass = new \reflectionclass($entity_class);

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
            if (method_exists($entity_class, $method)) {
                $reflmethod = new \reflectionmethod($entity_class, $method);

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

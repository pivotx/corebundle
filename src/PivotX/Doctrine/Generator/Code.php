<?php
namespace PivotX\Doctrine\Generator;


/**
 * This is our code mangler
 *
 * Note: we use Reflection so we need to let PHP parse the class
 */
class Code
{
    /**
     * Get the default comment to add
     *
     * @todo we should add phpdoc tags before every line
     * @todo we should add a tag to allow code to be fixated
     *
     * @return string partial php comment
     */
    public static function getDefaultComment()
    {
        $generated_on = date('Y-m-d H:i:s');
        $comment = <<<THEEND
     * @PivotX\UpdateDate     $generated_on
     * @PivotX\AutoUpdateCode code will be updated by PivotX
     * @author                PivotX Generator
THEEND;

        return $comment;
    }

    /**
     * Make sure the correct 'use' statement has been added
     *
     * @internal
     */
    private static function fixUseStatement($code_lines)
    {
        $annotation_statement = 'use PivotX\Doctrine\Annotation as PivotX;';

        $namespace_line = false;
        $last_use_line  = false;
        $use_statements = array();

        for($cnt=0; $cnt < count($code_lines); $cnt++) {
            if (preg_match('/^ *namespace /', $code_lines[$cnt])) {
                $namespace_line = $cnt;
            }
            if (preg_match('/^ *use /', $code_lines[$cnt])) {
                $use_statements[] = $code_lines[$cnt];
                $last_use_line    = $cnt;
            }
        }

        $add = true;
        foreach($use_statements as $statement) {
            if (trim($statement) == $annotation_statement) {
                $add = false;
            }
        }

        if ($add) {
            if ($last_use_line !== false) {
                array_splice($code_lines, $last_use_line + 1, 0, array($annotation_statement));
            }
            else if ($namespace_line !== false) {
                array_splice($code_lines, $namespace_line + 1, 0, array('', $annotation_statement));
            }
        }

        return $code_lines;
    }

    /**
     * Strip more than 2 empty lines
     *
     * @internal
     */
    private static function cleanEmptyLines($code_lines, $remove_line_code)
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

    /**
     * Mangle a class with updated methods and possible remove other methods
     *
     * @return string the updated code
     */
    public static function mangleClass($classname, $code, $add_methods, $remove_methods = array())
    {
        $default_comment = self::getDefaultComment();

        $reflclass = new \reflectionclass($classname);

        $code_lines = explode("\n", $code);
        $remove_line_code = '// remove this line';

        foreach($remove_methods as $method) {
            // @todo do something here
        }


        // ignore and later remove empty lines in the end of the code

        $line = $reflclass->getendline() - 1;
        while (($line > 0) && (trim($code_lines[$line]) == '')) {
            $code_lines[$line] = $remove_line_code;
            $line--;
        }

        $new_code = '';
        foreach($add_methods as $method => $code) {
            if (method_exists($classname, $method)) {
                $reflmethod = new \reflectionmethod($classname, $method);

                $start_line = $reflmethod->getstartline() - 1;
                $end_line   = $reflmethod->getendline();

                $doccomment = $reflmethod->getdoccomment();
                if ($doccomment !== false) {
                    $start_line -= count(explode("\n", $doccomment));

                    if (strstr($doccomment, '@PivotX\\AutoUpdateCode') === false) {
                        // we are NOT updating this code
                        continue;
                    }
                }

                for($line=$start_line; $line < $end_line; $line++) {
                    $code_lines[$line] = $remove_line_code;
                }
            }

            $code = str_replace('%comment%', $default_comment, $code);

            if ($new_code != '') {
                $new_code .= "\n";
            }
            $new_code .= $code;
        }

        array_splice($code_lines, $reflclass->getendline()-1, 0, array($new_code));



        // remove too many empty lines

        $code_lines = self::cleanEmptyLines($code_lines, $remove_line_code);



        // check for the correct namespace 'use' statement

        $code_lines = self::fixUseStatement($code_lines);


        // clean the code

        $lines = array();
        foreach($code_lines as $line) {
            if ($line != $remove_line_code) {
                $lines[] = $line;
            }
        }

        return implode("\n", $lines);
    }
}

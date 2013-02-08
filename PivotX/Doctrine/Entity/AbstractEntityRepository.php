<?php

namespace PivotX\Doctrine\Entity;


abstract class AbstractEntityRepository implements EntityRepository
{
    /**
     */
    protected function buildView($name, $string_args, $description, $full_args = null)
    {
        $long_description = '';

        $long_description .= '<h4>Description</h4>';
        $long_description .= '<p>'.$description.'.</p>';

        if (!is_null($full_args)) {
            if (count($full_args) > 0) {
                $long_description .= '<h4>Available arguments</h4>';
                $long_description .= '<dl>';
                foreach($full_args as $k => $v) {
                    $long_description .= '<dt>'.$k.'</dt>';
                    if (!is_array($v)) {
                        $long_description .= '<dd>'.$v.'</dd>';
                    }
                    else {
                        $long_description .= '<dd>'.$v[0].'</dd>';
                        $long_description .= '<dd>Arguments example: <code>{ \''.$k.'\': '.$v[1].' }</code></dd>';
                    }
                }
                $long_description .= '</dl>';
            }
        }
        else {
            $args = eval('return '.$string_args.';');

            if (count($args) > 0) {
                $long_description .= '<h4>Available arguments</h4>';
                $long_description .= '<dl>';
                foreach($args as $k => $v) {
                    $long_description .= '<dt>'.$k.'</dt>';

                    if (is_null($v)) {
                        $long_description .= '<dd>Default value: <strong>no default</strong>';
                        $long_description .= '<dd>Arguments example: <code>{ \''.$k.'\': 1 }</code></dd>';
                    }
                    else {
                        $long_description .= '<dd>Default value: '.$v.'</dd>';
                    }
                }
                $long_description .= '</dl>';
            }
        }

        return array(
            $name,
            $string_args,
            $description,
            $long_description
        );
    }

    /**
     */
    protected function generateViewsCode($views)
    {
        $code = '';

        $code .= "\t\t".'$repository = $this;'."\n";

        foreach($views as $view) {
            $name = $view[0];
            $args = $view[1];
            $desc = $view[2];
            $long = $view[3];

            $long = str_replace('"', '\\"', $long);

            /*
            // add view directly
            $code .= "\t\t".'$view = new \\PivotX\\Doctrine\\Repository\\Views\\findTemplate($this, \''.$name.'\', '.$args.', $prefix.\'/'.$name.'\', \''.$desc.'\', \'PivotX/Core\', array($prefix, \'returnMore\'));'."\n";
            $code .= "\t\t".'$view->setLongDescription("'.$long.'");'."\n";
            $code .= "\t\t".'$service->registerView($view);'."\n";
             */

            // add view through proxy
            $code .= <<<THEEND

        \$view = new \\PivotX\\Component\\Views\\ViewProxy(\$prefix.'/$name', function() use (\$prefix, \$repository) {
            \$pview = new \\PivotX\Doctrine\\Repository\\Views\\findTemplate(\$repository, '$name', $args, \$prefix.'/$name', '$desc', 'PivotX/Core', array(\$prefix, 'returnMore'));
            \$pview->setLongDescription("$long");
            return \$pview;
        });
        \$service->registerView(\$view);

THEEND;
        }

        return $code;
    }
}

<?php

/**
 * This file is part of the PivotX Core bundle
 *
 * (c) Marcel Wouters / Two Kings <marcel@twokings.nl>
 */

namespace PivotX\Component\Twig;

/**
 * Twig Query interface
 *
 * @author Marcel Wouters <marcel@twokings.nl>
 *
 * @api
 */
class Loadlistnode extends \Twig_Node
{
    public function __construct($name, $list, $arguments, $lineno, $tag = null)
    {
        parent::__construct(array(), array('name' => $name, 'list' => $list, 'with_arguments' => $arguments), $lineno, $tag);
    }

    public function compile(\Twig_Compiler $compiler)
    {
        $compiler
            ->addDebugInfo($this)
            ->write('$context[\''.$this->getAttribute('name').'\'] = ')
            ->write('\PivotX\Component\Lists\Lists::loadList("'.$this->getAttribute('list').'");')
            ;

        /*
        $with_arguments = $this->getAttribute('with_arguments');
        if (is_array($with_arguments) && (count($with_arguments) > 0)) {
            $compiler
                ->addDebugInfo($this)
                ->write('$context[\''.$this->getAttribute('name').'\']->addWithArguments('.var_export($with_arguments,true).');')
                ;

        }
         */
    }
}

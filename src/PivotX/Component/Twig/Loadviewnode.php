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
class Loadviewnode extends \Twig_Node
{
    public function __construct($name, $view, $with_attributes, $lineno, $tag = null)
    {
        parent::__construct(array('with_attributes'=>$with_attributes), array('name' => $name, 'view' => $view), $lineno, $tag);
    }

    public function compile(\Twig_Compiler $compiler)
    {
        $compiler
            ->addDebugInfo($this)
            ->write('$context[\''.$this->getAttribute('name').'\'] = ')
            ->write('\PivotX\Component\Views\Views::loadView("'.$this->getAttribute('view').'");')
            ;

        if (!is_null($this->getNode('with_attributes'))) {
            $compiler
                ->addDebugInfo($this)
                ->write('$context[\''.$this->getAttribute('name').'\']->addWithArguments(')
                ->subcompile($this->getNode('with_attributes'))
                ->write(');')
                ;

        }
    }
}

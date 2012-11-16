<?php

/**
 * This file is part of the PivotX Core bundle
 *
 * (c) Marcel Wouters / Two Kings <marcel@twokings.nl>
 */

namespace PivotX\Component\Twig;

/**
 * Twig PivotX Loadview
 *
 * @author Marcel Wouters <marcel@twokings.nl>
 *
 * @api
 */
class Loadlist extends \Twig_TokenParser
{
    protected function convertToViewArguments(\Twig_Node_Expression_Array $array)
    {
        $arguments = array();

        foreach(array_chunk($array->getIterator()->getArrayCopy(), 2) as $pair) {
            if (count($pair) == 2) {
                $key   = $pair[0]->getAttribute('value');
                $value = $pair[1]->getAttribute('value');   // @todo support for multiple types

                $arguments[$key] = $value;
            }
        }

        return $arguments;
    }

    public function parse(\Twig_Token $token)
    {
        $lineno = $token->getLine();
        $arguments = array();

        $listexpr = $this->parser->getExpressionParser()->parseExpression();
        if ($listexpr->hasAttribute('value')) {
            $list = $listexpr->getAttribute('value');
        }
        else if ($listexpr->hasAttribute('name')) {
            // @todo make this work
            //$name = $listexpr->getAttribute('name');
        }
        $name = preg_replace('|[^a-z0-9]+|','_', $list);

        $asexpr = null;
        if ($this->parser->getStream()->test(\Twig_Token::NAME_TYPE, 'as')) {
            $this->parser->getStream()->next();
            $asexpr = $this->parser->getExpressionParser()->parseExpression();

            $name = $asexpr->getAttribute('name');
        }

        if ($this->parser->getStream()->test(\Twig_Token::NAME_TYPE, 'with')) {
            $this->parser->getStream()->next();
            $withexpr = $this->parser->getExpressionParser()->parseHashExpression();

            $arguments = $this->convertToViewArguments($withexpr);

            //echo '<pre>With:<br/>'; var_dump($arguments); echo '</pre>';
        }

        $this->parser->getStream()->expect(\Twig_Token::BLOCK_END_TYPE);

        return new Loadlistnode($name, $list, $arguments, $lineno, $this->getTag());
    }

    public function getTag()
    {
        return 'loadList';
    }
}

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
    public function parse(\Twig_Token $token)
    {
        $lineno = $token->getLine();

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

        $exclude_root = false;
        if ($this->parser->getStream()->test(\Twig_Token::NAME_TYPE, 'excludeRoot')) {
            $this->parser->getStream()->next();

            $exclude_root = true;
        }

        $this->parser->getStream()->expect(\Twig_Token::BLOCK_END_TYPE);

        return new Loadlistnode($name, $list, $exclude_root, $lineno, $this->getTag());
    }

    public function getTag()
    {
        return 'loadList';
    }
}

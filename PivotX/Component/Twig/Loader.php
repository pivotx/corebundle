<?php

/**
 * This file is part of the PivotX Core bundle
 *
 * (c) Marcel Wouters / Two Kings <marcel@twokings.nl>
 */

namespace PivotX\Component\Twig;


/**
 * Themed Twig Loader
 *
 * @author Marcel Wouters <marcel@twokings.nl>
 *
 * @api
 */
class FilesystemLoader extends \Symfony\Bundle\TwigBundle\Loader
{
    protected function findTemplate($template)
    {
        if (strpos($template, '#') !== false) {
            die('My kind of include');
        }

        return parent::findTemplate($template);
    }
}

<?php

/**
 * This file is part of the PivotX Core bundle
 *
 * (c) Marcel Wouters / Two Kings <marcel@twokings.nl>
 */

namespace PivotX\Component\Translations;


/**
 * PivotX Inflector
 *
 * Sort of English inflector.
 *
 * @author Marcel Wouters <marcel@twokings.nl>
 *
 * @api
 */
class Inflector
{
    public static function pluralize($text)
    {
        $last_char = substr($text, -1);
        
        if ($last_char == 'y') {
            return substr($text, 0, -1) . 'ies';
        }

        return $text . 's';

		// met vim toegevoegd
    }
}


<?php
/**
 * This file is part of the PivotX Core bundle
 *
 * (c) Marcel Wouters / Two Kings <marcel@twokings.nl>
 */

namespace PivotX\Component\Lists;


class UrlItem extends Item
{
    private $url = null;

    public function __construct($name, $url)
    {
        parent::__construct($name);

        $this->url = $url;
    }

    public function getLink()
    {
        return $this->url;
    }
}

<?php
/**
 * This file is part of the PivotX Core bundle
 *
 * (c) Marcel Wouters / Two Kings <marcel@twokings.nl>
 */

namespace PivotX\Component\Lists;


/**
 * PivotX Breadcrumb
 *
 * @author Marcel Wouters <marcel@twokings.nl>
 *
 * @api
 */
class Breadcrumb
{
    private $item;
    private $last;
    private $forced_item = false;

    public function __construct(ItemInterface $item, $last = false)
    {
        $this->item = $item;
        $this->last = $last;
    }

    public function setLast()
    {
        $this->last = true;
    }

    public function isLast()
    {
        return $this->last;
    }

    public function getLabel()
    {
        return $this->item->getName();
    }

    public function hasLink()
    {
        return method_exists($this->item, 'getLink');
    }

    public function getLink()
    {
        if ($this->hasLink()) {
            return $this->item->getLink();
        }
        return null;
    }

    public function getClasses()
    {
        $classes = array();

        return implode(' ', $classes);
    }

    public function getAttribute($name, $default = null)
    {
        return $this->item->getAttribute($name, $default);
    }
}

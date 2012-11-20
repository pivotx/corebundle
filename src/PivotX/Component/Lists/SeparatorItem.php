<?php
/**
 * This file is part of the PivotX Core bundle
 *
 * (c) Marcel Wouters / Two Kings <marcel@twokings.nl>
 */

namespace PivotX\Component\Lists;


class SeparatorItem extends Item
{
    private static $counter = 0;

    public function __construct()
    {
        self::$counter++;

        parent::__construct(sprintf('separator-%d', self::$counter));

        $this->resetInSitemap();
        $this->resetBreadcrumb();
    }

    public function isActive()
    {
        return false;
    }

    public function getMenuClasses()
    {
        return array('divider');
    }

    public function getLabel()
    {
        return new \Twig_MarkUp('', 'utf-8');
    }
}

<?php
/**
 * This file is part of the PivotX Core bundle
 *
 * (c) Marcel Wouters / Two Kings <marcel@twokings.nl>
 */

namespace PivotX\Component\Lists;


/**
 * PivotX Menu
 *
 * @author Marcel Wouters <marcel@twokings.nl>
 *
 * @api
 */
class MenuItem
{
    private $item;
    private $depth;


    /**
     * a forced_item is never a menu to the outside
     * a forced_item never gets the class 'active' when it's activebyproxy
     */
    private $forced_item = false;

    public function __construct(ItemInterface $item, $depth = 0)
    {
        $this->item  = $item;
        $this->depth = $depth;
    }

    /**
     * For merged menu items, we force the status 'not ismenu'
     */
    public function setForcedItem()
    {
        $this->forced_item = true;
    }

    public function isMenu()
    {
        if ($this->forced_item) {
            return false;
        }

        if (!$this->item->isItemsHolder() && ($this->item->countItems() > 0)) {
            // we still need to check if the items are actually available for in a menu
            foreach($this->item->getItems() as $item) {
                if ($item->isEnabled() && $item->isInMenu()) {
                    return true;
                }
            }

            return false;
        }

        return false;
    }

    /**
     * Return true if this menu items should not be in the sitemap
     */
    public function isInSitemap()
    {
        return $this->item->isInSitemap();
    }

    public function getMenu()
    {
        return new Menu($this->item, $this->depth+1);
    }

    public function getLabel()
    {
        if (method_exists($this->item, 'getLabel')) {
            return $this->item->getLabel();
        }
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

        if (method_exists($this->item, 'getMenuClasses')) {
            $classes = array_merge($classes, $this->item->getMenuClasses());
        }

        if ($this->forced_item && (!$this->item->isActive() && $this->item->isActiveByProxy())) {
            // no active class
        }
        else if ($this->item->isActive() || $this->item->isActiveByProxy()) {
            $classes[] = 'active';
        }

        return implode(' ', $classes);
    }

    public function getAttribute($name, $default = null)
    {
        return $this->item->getAttribute($name, $default);
    }
}

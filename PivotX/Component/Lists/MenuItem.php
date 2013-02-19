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
    private $security_context;
    private $classes;


    /**
     * a forced_item is never a menu to the outside
     * a forced_item never gets the class 'active' when it's activebyproxy
     */
    private $forced_item = false;

    public function __construct(ItemInterface $item, $depth = 0, $security_context = null)
    {
        $this->item             = $item;
        $this->depth            = $depth;
        $this->security_context = $security_context;
        $this->classes          = array();
    }

    /**
     * For merged menu items, we force the status 'not ismenu'
     */
    public function setForcedItem()
    {
        $this->forced_item = true;
    }

    /**
     * Add a class
     */
    public function addClass($class)
    {
        $this->classes[] = $class;
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
        return new Menu($this->item, $this->depth+1, $this->security_context);
    }

    public function getLabel()
    {
        if (method_exists($this->item, 'getLabel')) {
            $label = $this->item->getLabel();
            if (!is_null($label)) {
                return $label;
            }
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

    private function _getClasses()
    {
        $classes = array();

        if (method_exists($this->item, 'getMenuClasses')) {
            $classes = array_merge($classes, $this->item->getMenuClasses());
        }

        if (count($this->classes) > 0) {
            $classes = array_merge($classes, $this->classes);
        }

        if ($this->forced_item && (!$this->item->isActive() && $this->item->isActiveByProxy())) {
            // no active class
        }
        else if ($this->item->isActive() || $this->item->isActiveByProxy()) {
            $classes[] = 'active';
        }

        return $classes;
    }

    public function getClasses()
    {
        return implode(' ', $this->_getClasses());
    }

    public function hasClass($class)
    {
        return in_array($class, $this->_getClasses());
    }

    public function isActive()
    {
        return $this->item->isActive();
    }

    public function getAttribute($name, $default = null)
    {
        return $this->item->getAttribute($name, $default);
    }
}

<?php
/**
 * This file is part of the PivotX Core bundle
 *
 * (c) Marcel Wouters / Two Kings <marcel@twokings.nl>
 */

namespace PivotX\Component\Lists;


class Item implements ItemInterface
{
    protected $name;
    protected $label;
    protected $enabled;
    protected $role;

    protected $parent_item;
    protected $items;
    protected $itemsholder;

    protected $inmenu;
    protected $breadcrumb;
    protected $insitemap;
    protected $active;
    protected $activebyproxy;

    public function __construct($name)
    {
        $this->name        = $name;
        $this->label       = null;
        $this->enabled     = true;
        $this->role        = null;;

        $this->parent_item = null;
        $this->items       = array();
        $this->itemsholder = false;

        $this->inmenu        = true;
        $this->breadcrumb    = true;
        $this->insitemap     = true;
        $this->active        = false;
        $this->activebyproxy = false;
    }

    /**
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     */
    public function setLabel($label = null)
    {
        $this->label = $label;

        return $this;
    }

    /**
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     */
    public function setEnabled($enabled = true)
    {
        $this->enabled = $enabled;
        return $this;
    }

    /**
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     */
    public function isGrantedByContext($security_context)
    {
        $role = $this->getRole();
        if (is_null($role)) {
            return true;
        }
        if (is_null($security_context)) {
            return false;
        }
        return $security_context->isGranted($role);
    }

    /**
     */
    public function setRole($role)
    {
        $this->role = $role;
    }


    /**
     * Get the parent item
     */
    public function getParentItem()
    {
        return $this->parent_item;
    }

    /**
     * Set the parent item
     */
    public function setParentItem(ItemInterface $item)
    {
        $this->parent_item = $item;
        return $this->parent_item;
    }

    /**
     * Get the items for this item
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Return the number of items
     */
    public function countItems()
    {
        return count($this->items);
    }

    /**
     * Add an item for this item
     *
     * @param ItemInterface $item   the item to add
     * @return ItemInterface        the added item
     */
    public function addItem(ItemInterface $item)
    {
        $this->items[] = $item;
        if ($this->isItemsHolder()) {
            $this->items[count($this->items)-1]->setParentItem($this->getParentItem());
        }
        else {
            $this->items[count($this->items)-1]->setParentItem($this);
        }
        return $this->items[count($this->items)-1];
    }

    /**
     * Set the items as a submenu and not as a itemsholder
     */
    public function setAsItemsholder()
    {
        $this->itemsholder = true;
        return $this;
    }

    /**
     * If this item contains other items and itself isn't an actual item
     */
    public function isItemsHolder()
    {
        return $this->itemsholder;
    }

    /**
     * This item is not in a menu
     */
    public function resetInMenu()
    {
        $this->inmenu = false;
    }

    /**
     * Return true if this item should be in a menu
     */
    public function isInMenu()
    {
        return $this->inmenu;
    }

    /**
     * This item is no breadcrumb
     */
    public function resetBreadcrumb()
    {
        $this->breadcrumb = false;
    }

    /**
     * Return true if this item is a breadcrumb item
     */
    public function isBreadcrumb()
    {
        return $this->breadcrumb;
    }

    /**
     * This item is not in the sitemap
     */
    public function resetInSitemap()
    {
        $this->insitemap = false;
    }

    /**
     * Return true if this item should be in the sitemap
     */
    public function isInSitemap()
    {
        return $this->insitemap;
    }

    /**
     * Internal call to set the active state in the complete menu tree
     */
    public function resetActiveStates()
    {
        $this->activebyproxy = false;

        foreach($this->items as $item) {
            $item->resetActiveStates();

            if ($item->isActive() || $item->isActiveByProxy()) {
                $this->activebyproxy = true;
                break;
            }
        }
    }

    /**
     * If the item itself is the active item
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * If the item itself or one of the items below it is active
     */
    public function isActiveByProxy()
    {
        return $this->activebyproxy;
    }

    /**
     * Get an attribute
     */
    public function getAttribute($name, $default = null)
    {
        if (isset($this->attributes[$name])) {
            return $this->attributes[$name];
        }
        return $default;
    }

    /**
     * Set an attribute
     */
    public function setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;
        return $this;
    }
}

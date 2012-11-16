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
class Menu
{
    private $item;
    private $depth;
    private $merge_self_with_items;

    public function __construct(ItemInterface $item, $depth = 0)
    {
        $this->item  = $item;
        $this->depth = $depth;

        $this->merge_self_with_items = false;
        if ($depth == 0) {
            $this->merge_self_with_items = true;
        }
    }

    /**
     */
    public function getDepth()
    {
        return $this->depth;
    }

    /**
     * Get the classes for the menu
     */
    public function getClasses()
    {
        $classes   = array();
        $classes[] = 'menulevel-'.$this->depth;

        return implode(' ', $classes);
    }

    /**
     * Get all the items for a root_item (merges items from itemsholders)
     */
    private function getActualItems($root_item, $purpose = 'menu')
    {
        $items = array();

        foreach($root_item->getItems() as $item) {
            if (!$item->isEnabled()) {
                continue;
            }
            if (($purpose == 'menu') && (!$item->isInMenu())) {
                continue;
            }

            if ($item->isItemsHolder()) {
                $items = array_merge($items, $this->getActualItems($item, $purpose));
            }
            else {
                if ($purpose == 'menu') {
                    $items[] = new MenuItem($item, $this->depth);
                }
                else {
                    $items[] = $item;
                }
            }
        }

        return $items;
    }

    /**
     */
    public function getItem()
    {
        return new MenuItem($this->item, 0);
    }

    /**
     * Return the items of the item
     */
    public function getItems()
    {
        $items = array();

        if ($this->merge_self_with_items) {
            // on this level we automatically merge the parent with it's children

            $items[] = new MenuItem($this->item, $this->depth);
            $items[0]->setForcedItem();
        }

        $items = array_merge($items, $this->getActualItems($this->item, 'menu'));

        return $items;
    }

    /**
     * Get the actual breadcrumbs
     */
    private function getActualBreadcrumbs(ItemInterface $root_item)
    {
        $crumbs = array();

        if ($root_item->isBreadcrumb()) {
            $crumbs[] = new Breadcrumb($root_item);
        }

        $items = $this->getActualItems($root_item, 'breadcrumb');
        foreach($items as $item) {
            if ($item->isActiveByProxy()) {
                if ($item->countItems() > 0) {
                    $crumbs = array_merge($crumbs, $this->getActualBreadcrumbs($item));
                }
                else {
                    if ($item->isBreadcrumb()) {
                        $crumbs[] = new Breadcrumb($item);
                    }
                }
            }
            else if ($item->isActive()) {
                if ($item->isBreadcrumb()) {
                    $crumbs[] = new Breadcrumb($item);
                }
            }
        }

        return $crumbs;
    }

    /**
     * Get the breadcrumbs
     */
    public function getBreadcrumbs()
    {
        $crumbs = array();

        $crumbs = array_merge($crumbs, $this->getActualBreadcrumbs($this->item));

        if (count($crumbs) > 0) {
            $crumbs[count($crumbs)-1]->setLast();
        }

        return $crumbs;
    }
}

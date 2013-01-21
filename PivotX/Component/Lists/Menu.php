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
    private $security_context;

    public function __construct(ItemInterface $item, $depth = 0, $security_context = null)
    {
        $this->item  = $item;
        $this->depth = $depth;
        $this->security_context = $security_context;

        $this->merge_self_with_items = false;
        if ($depth == 0) {
            $this->merge_self_with_items = true;
        }
    }

    /**
     * Don't merge self with items
     */
    public function excludeRoot()
    {
        $this->merge_self_with_items = false;
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

            if (is_null($this->security_context)) {
                echo 'HELP MY SECURITY HAS LEFT ME';
            }
            if (!$item->isGrantedByContext($this->security_context)) {
                continue;
            }

            if ($item->isItemsHolder()) {
                $items = array_merge($items, $this->getActualItems($item, $purpose));
            }
            else {
                if ($purpose == 'menu') {
                    $items[] = new MenuItem($item, $this->depth, $this->security_context);
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
        // @todo no security context role check
        return new MenuItem($this->item, 0, $this->security_context);
    }

    /**
     * Return the items of the item
     */
    public function getItems()
    {
        $items = array();

        if ($this->merge_self_with_items) {
            // on this level we automatically merge the parent with it's children

            // @todo no security context role check
            $items[] = new MenuItem($this->item, $this->depth, $this->security_context);
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
            if (!$item->isGrantedByContext($this->security_context)) {
                continue;
            }

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

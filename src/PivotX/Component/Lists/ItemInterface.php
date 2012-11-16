<?php
/**
 * This file is part of the PivotX Core bundle
 *
 * (c) Marcel Wouters / Two Kings <marcel@twokings.nl>
 */

namespace PivotX\Component\Lists;


/**
 * This interfaces describes an item
 * 
 * An item can contain one or more actual menu items
 */
interface ItemInterface
{
    /**
     * Shared item methods
     */

    /**
     * Get the name of the item
     */
    public function getName();

    /**
     * If the item is enabled
     */
    public function isEnabled();


    /**
     * Structural item methods
     */

    /**
     * Get the parent item
     */
    public function getParentItem();

    /**
     * Set the parent item
     */
    public function setParentItem(ItemInterface $item);

    /**
     * Get the items for this item
     */
    public function getItems();

    /**
     * Add a subitem for this item
     */
    public function addItem(ItemInterface $item);

    /**
     * If this item contains other items and itself isn't an actual item
     */
    public function isItemsHolder();


    /**
     * Shared menu item attribute methods
     */

    /**
     * Return true if this item is visible in a menu
     */
    public function isInMenu();

    /**
     * Return true if this item is a breadcrumb item
     */
    public function isBreadcrumb();

    /**
     * If the item itself is the active item
     */
    public function isActive();

    /**
     * If the item itself or one of the items below it is active
     */
    public function isActiveByProxy();


    /**
     * Other item methods
     */

    /**
     * Get an attribute
     */
    public function getAttribute($name);

    /**
     * Set an attribute
     */
    public function setAttribute($name, $value);

}

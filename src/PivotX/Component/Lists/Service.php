<?php
/**
 * This file is part of the PivotX Core bundle
 *
 * (c) Marcel Wouters / Two Kings <marcel@twokings.nl>
 */

namespace PivotX\Component\Lists;

use Symfony\Component\HttpKernel\Log\LoggerInterface;
use PivotX\Component\Siteoptions\Service as SiteoptionsService;
use PivotX\Component\Routing\Service as RoutingService;
use PivotX\Component\Translations\Service as TranslationsService;

/**
 * PivotX Lists Service
 *
 * @author Marcel Wouters <marcel@twokings.nl>
 *
 * @api
 */
class Service
{
    private $logger;
    private $siteoptions;

    private $lists;

    public function __construct(LoggerInterface $logger = null, SiteoptionsService $siteoptions, RoutingService $routing, TranslationsService $translations)
    {
        $this->logger      = $logger;
        $this->siteoptions = $siteoptions;

        // @todo remove this when we have another solution
        // inject ourselves and the logger into the Views component
        \PivotX\Component\Lists\Lists::setServices($this, $logger);

        //\PivotX\Component\Lists\PxItem::setTranslationsService($translations);
        RouteItem::setRoutingService($routing);
    }

    /**
     * Add an item to the service
     * 
     * @param string $name           name of item
     * @param ItemInterface $item    item to add
     * @param boolean $persistent    if the item is persistent
     * @return ItemInterface         added item
     */
    public function addItem($name, ItemInterface $item, $persistent = true)
    {
        // we should do it either here or wait until findItem is called..
        $item->resetActiveStates();

        if ($persistent) {
            $this->persistent_items[$name] = $item;

            return $this->persistent_items[$name];
        }

        $this->transient_items[$name] = $item;

        return $this->transient_items[$name];
    }

    /**
     * Find a specific item
     *
     * @param string $name   name of the item
     * @return ItemInterface item if found, otherwise null
     */
    public function findItem($name)
    {
        if (isset($this->transient_items[$name])) {
            return $this->transient_items[$name];
        }
        if (isset($this->persistent_items[$name])) {
            return $this->persistent_items[$name];
        }

        return null;
    }

    /**
     * Return all persistent lists
     * 
     * @return array    array of ListInterfaces
     */
    public function getItems()
    {
        return $this->persistent_items;
    }
}

<?php
/**
 * This file is part of the PivotX Core bundle
 *
 * (c) Marcel Wouters / Two Kings <marcel@twokings.nl>
 */

namespace PivotX\Component\Lists;

use Symfony\Component\HttpKernel\Log\LoggerInterface;


/**
 * PivotX Lists
 *
 * @author Marcel Wouters <marcel@twokings.nl>
 *
 * @api
 */
class Lists
{
    private static $lists_service;
    private static $logger;

    public function __construct()
    {
    }

    public static function setServices(Service $lists_service, LoggerInterface $logger)
    {
        self::$lists_service = $lists_service;
        self::$logger        = $logger;
    }

    public static function loadList($name, $arguments = array())
    {
        $item = self::$lists_service->findItem($name);

        $menu = new Menu($item, 0);

        return $menu;
    }
}

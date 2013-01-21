<?php
/**
 * This file is part of the PivotX Core bundle
 *
 * (c) Marcel Wouters / Two Kings <marcel@twokings.nl>
 */

namespace PivotX\Component\Lists;

use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\Security\Core\SecurityContext;


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
    private static $security_context;

    public function __construct()
    {
    }

    public static function setServices(Service $lists_service, LoggerInterface $logger, SecurityContext $security_context)
    {
        self::$lists_service    = $lists_service;
        self::$logger           = $logger;
        self::$security_context = $security_context;
    }

    public static function loadList($name, $arguments = array())
    {
        $item = self::$lists_service->findItem($name);

        $menu = new Menu($item, 0, self::$security_context);

        return $menu;
    }
}

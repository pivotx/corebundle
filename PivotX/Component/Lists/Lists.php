<?php
/**
 * This file is part of the PivotX Core bundle
 *
 * (c) Marcel Wouters / Two Kings <marcel@twokings.nl>
 */

namespace PivotX\Component\Lists;

use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpKernel\Debug\Stopwatch;


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
    private static $stopwatch;

    public function __construct()
    {
    }

    public static function setServices(Service $lists_service, LoggerInterface $logger, SecurityContext $security_context, Stopwatch $stopwatch = null)
    {
        self::$lists_service    = $lists_service;
        self::$logger           = $logger;
        self::$security_context = $security_context;
        self::$stopwatch        = $stopwatch;
    }

    public static function loadList($name, $arguments = array())
    {
        $item = self::$lists_service->findItem($name);

        $menu = new Menu($item, 0, self::$security_context, self::$stopwatch);

        return $menu;
    }
}

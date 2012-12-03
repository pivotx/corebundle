<?php

/**
 * This file is part of the PivotX Core bundle
 *
 * (c) Marcel Wouters / Two Kings <marcel@twokings.nl>
 */

namespace PivotX\Component\Views;

use Symfony\Component\HttpKernel\Log\LoggerInterface;


class Views
{
    private static $views_service = false;
    private static $logger = false;

    public static function setServices(\PivotX\Component\Views\Service $service, LoggerInterface $logger)
    {
        self::$views_service = $service;
        self::$logger        = $logger;
    }

    public static function loadView($name, $arguments = null)
    {
        $view = self::$views_service->findView($name);

        if (is_null($view)) {
            // @todo should this be just a warning?
            self::$logger->warn('Call for loadView "'.$name.'"  - view not found');
            return new EmptyView();
        }

        self::$logger->info('Call for loadView "'.$name.'" - view found');

        if (!is_null($arguments)) {
            $view->setArguments($arguments);
        }

        return $view;
    }
}

<?php

/*
 * This file is part of the PivotX package.
 */

namespace PivotX\Component\Siteoptions;


/**
 * This is the routing updater
 *
 * @author Marcel Wouters <marcel@twokings.nl>
 */
class Routing
{
    private $siteoptions;

    public function __construct($siteoptions)
    {
        $this->siteoptions = $siteoptions;
    }

    private function buildSiteRouteStart()
    {
        $routes = array();

        return $routes;
    }

    private function buildSiteRouteEnd()
    {
        $routes = array();

        return $routes;
    }

    private function combineRoutes($site)
    {
        $routes = array();

        $keys = explode("\n", $this->siteoptions->getValue('routing.keys', array(), $site));
        foreach($keys as $key) {
            $routes = array_merge(
                $routes,
                $this->siteoptions->getValue($key, array(), $site)
            );
        }

        return $routes;
    }

    public function compileSiteRoutes($site)
    {
        $routes = array();

        $routes = array_merge($routes, $this->buildSiteRouteStart());
        $routes = array_merge($routes, $this->combineRoutes($site));
        $routes = array_merge($routes, $this->buildSiteRouteEnd());


        $routeprefixes = array(
            array(
                'filter' => array(
                    'target' => 'desktop',
                    'site' => $site,
                    'language' => 'en'
                ),
                'prefix' => 'http://%request.host%/'
            )
        );

        $routing = array(
            'routeprefixes' => $routeprefixes,
            'routes' => $routes
        );

        $this->siteoptions->set(
            'routing.compiled',
            json_encode($routing),
            'application/json',
            true,
            false,
            $site
        );
    }
}

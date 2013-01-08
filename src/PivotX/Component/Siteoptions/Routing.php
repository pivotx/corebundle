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

    private function buildSiteRouteStart($site)
    {
        $routes = array();

        return $routes;
    }

    private function buildSiteRouteEnd($site)
    {
        $routes = array();

        $routes[] = array(
            'filter' => array(
                'target' => false,
                'site' => $site,
                'language' => false,
            ),
            'pattern' => '_resource/{options}/{publicid}',
            'public' => 'resource/{options}/{publicid}',
            'defaults' => array(
                '_controller' => 'CoreBundle:GenericResource:getDownload',
                '_http_status' => 404
            ),
            'requirements' => array(
                'publicid' => '[a-z0-9_.-]+',
                'options' => '[a-z0-9_./-]+'
            )
        );
        $routes[] = array(
            'filter' => array(
                'target' => false,
                'site' => $site,
                'language' => false,
            ),
            'pattern' => '_http/404',
            'public' => 'page-not-found',
            'defaults' => array(
                '_controller' => 'CoreBundle:DefaultFront:showError',
                '_http_status' => 404
            )
        );

        return $routes;
    }

    private function combineRoutes($site)
    {
        $routes = array();

        $keys = explode("\n", $this->siteoptions->getValue('routing.keys', '', $site));
        foreach($keys as $key) {
            $routes = array_merge(
                $routes,
                $this->siteoptions->getValue($key, array(), $site)
            );
        }

        return $routes;
    }

    private function compileRoutePrefixes($site)
    {
        $routeprefixes = array();

        $config = $this->siteoptions->getValue('routing.setup', array(), $site);
        if (count($config) == 0) {
            // @todo maybe should throw an error?
            return $routeprefixes;
        }

        foreach($config['targets'] as $target) {
            foreach($config['languages'] as $language) {
                $hosts = explode("\n", trim($config['hosts'][$target['name']][$language['name']]));

                foreach($hosts as $host) {
                    $host = trim($host);
                    if (($host != '') && (substr($host, -1) == '/')) {
                        $routeprefixes[] = array(
                            'filter' => array(
                                'target' => $target['name'],
                                'site' => $site,
                                'language' => $language['name']
                            ),
                            'prefix' => $host
                        );
                    }
                }
            }
        }


        return $routeprefixes;
    }

    public function compileSiteRoutes($site)
    {
        $routeprefixes = $this->compileRoutePrefixes($site);

        $routes = array();

        $routes = array_merge($routes, $this->buildSiteRouteStart($site));
        $routes = array_merge($routes, $this->combineRoutes($site));
        $routes = array_merge($routes, $this->buildSiteRouteEnd($site));

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

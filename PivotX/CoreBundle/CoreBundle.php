<?php

namespace PivotX\CoreBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CoreBundle extends Bundle
{
    const VERSION = '4.0.0';

    public function boot()
    {
        //echo "Boot bundle..\n";

        try {
            // initialise the internal siteoptions cache
            // @todo someday this should be cachewarmed

            $routing_service     = $this->container->get('pivotx.routing');
            $siteoptions_service = $this->container->get('pivotx.siteoptions');
            if ($siteoptions_service->initAutoloadsToCache()) {
                $sites = explode("\n", $siteoptions_service->getValue('config.sites', '', 'all'));
                foreach($sites as $site) {
                    if ($site != 'pivotx-backend') {
                        $routing = $siteoptions_service->getValue('routing.compiled', array(), $site);

                        if (count($routing) > 0) {
                            $routing_service->loadCompiledRoutes($routing);
                        }
                    }
                }
            }

            $this->loadRepositoryViews($this->container);
        }
        catch (\InvalidArgumentException $e) {
        }
    }

    public function shutdown()
    {
        //echo "Shutdown bundle..\n";
    }


    /**
     * We make this as late as possible
     *
     * @todo rewrite this to only load relevant view when requested
     */
    protected function loadRepositoryViews($container)
    {
        static $loaded = false;

        if ($loaded === false) {
            $loaded = true;

            $views_service    = $container->get('pivotx.views');
            $doctrine_service = $container->get('doctrine');

            foreach ($doctrine_service->getEntityManagers() as $em) {
                $classes = $em->getMetadataFactory()->getAllMetadata();
                foreach($classes as $class) {
                    //echo "Class: ".$class->name."<br/>\n";

                    $parts = explode('\\', $class->name);
                    $name  = end($parts);

                    $repository = $doctrine_service->getRepository($class->name);
                    if (is_object($repository)) {
                        //echo 'Repository: '.get_class($repository)."<br/>\n";
                        if (method_exists($repository,'addDefaultViews')) {
                            //echo "Adding defaults<br/>\n";
                            $repository->addDefaultViews($views_service,$name);
                        }
                    }

                    if (method_exists($class->name, 'setActivityService')) {
                        $cl = $class->name;
                        $cl::setActivityService($this->container->get('pivotx.activity'));
                    }
                    if (method_exists($class->name, 'setRoutingService')) {
                        $cl = $class->name;
                        $cl::setRoutingService($this->container->get('pivotx.routing'));
                    }
                }
            }
        }
    }
}

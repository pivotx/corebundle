<?php

namespace PivotX\CoreBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CoreBundle extends Bundle
{
    public function boot()
    {
        //echo "Boot bundle..\n";

        try {
            $views_service = $this->container->get('pivotx.views');

            //$service->load($fname);


            // add all Views from all Doctrine repositories

            $doctrine_service = $this->container->get('doctrine');
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
                }
            }


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
        }
        catch (\InvalidArgumentException $e) {
        }
    }

    public function shutdown()
    {
        //echo "Shutdown bundle..\n";
    }
}

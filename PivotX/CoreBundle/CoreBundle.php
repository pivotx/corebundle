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
        }
        catch (\InvalidArgumentException $e) {
        }
    }

    public function shutdown()
    {
        //echo "Shutdown bundle..\n";
    }
}

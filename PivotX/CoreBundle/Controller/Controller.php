<?php

namespace PivotX\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use PivotX\Component\Referencer\Reference;
use PivotX\Component\Webresourcer\DirectoryWebresource;


class Controller extends \Symfony\Bundle\FrameworkBundle\Controller\Controller
{
    /**
     * Return default HTML context parameters
     */
    public function getDefaultHtmlContext()
    {
        $context = array(
            'html' => array(
                'title' => 'My Website',
                'meta' => array(
                    'charset' => 'utf-8'
                )
            ),
            'routing' => array(
                'site' => false,
                'target' => false,
                'language' => false,
                'arguments' => array()
            )
        );

        $routing    = $this->container->get('pivotx.routing');
        $routematch = $routing->getLatestRouteMatch();
        if (!is_null($routematch)) {
            $filter = $routematch->getRoutePrefix()->getFilter();
            $context['routing'] = array(
                'site' => $filter['site'],
                'target' => $filter['target'],
                'language' => $filter['language'],
                'arguments' => $routematch->getArguments(),
                'request' => array(
                    'full_ref' => $routematch->buildReference()->buildTextReference(),
                    'local_ref' => $routematch->buildReference()->buildLocalTextReference()
                )
            );
        }

        return $context;
    }

    /**
     * Call forward() but use a reference as input
     *
     * @param $link either a string (textreference) or an actual Reference
     */
    public function forwardByReference($link)
    {
        $routing    = $this->get('pivotx.routing');
        $routesetup = $routing->getRouteSetup();

        $routematch = null;
        if ($link instanceof Reference) {
            $routematch = $routesetup->matchReference($link, true);
        }
        else {
            $reference = new \PivotX\Component\Referencer\Reference(null, $link);

            $routematch = $routesetup->matchReference($reference, true);
        }

        $parameters = array();
        $controller = false;
        if (!is_null($routematch)) {
            $parameters = $routematch->getAttributes();

            if (isset($parameters['_controller'])) {
                $controller = $parameters['_controller'];
            }

            $this->getRequest()->attributes->add($parameters);
        }

        if (($controller !== false) && ($parameters !== false)) {
            return $this->forward($controller, $parameters);
        }

        return null;
    }

    /**
     * Build the webresources
     */
    protected function buildWebresources($site, $allow_debugging = false)
    {
        $kernel       = $this->get('kernel');
        $siteoptions  = $this->get('pivotx.siteoptions');
        $routing      = $this->get('pivotx.routing');
        $logger       = null;
        $stopwatch    = null;
        if ($this->container->has('logger')) {
            $logger = $this->container->get('logger');
        }
        if ($this->container->has('stopwatch')) {
            $stopwatch = $this->container->get('stopwatch');
        }

        $theme_json = $siteoptions->getValue('themes.active', null, $site);
        if (is_null($theme_json)) {
            // cannot continue, nothing configured
            return null;
        }
        $theme_path = dirname($theme_json);


        $webresourcer = new \PivotX\Component\Webresourcer\Service($logger, $kernel);
        $outputter    = new \PivotX\Component\Outputter\Service($logger, $kernel, $routing, $stopwatch);

        $directories = $siteoptions->getValue('webresources.directory', array(), $site);
        foreach($directories as $directory) {
            $webresourcer->addWebresourcesFromDirectory($directory);
        }


        try {
            $webresource = $webresourcer->addWebresource(new DirectoryWebresource($theme_json));
            if ($siteoptions->getValue('themes.debug', false)) {
                $webresource->allowDebugging();
            }
            $webresourcer->activateWebresource($webresource->getIdentifier());

            // finalization

            $webresourcer->finalizeWebresources($outputter, $allow_debugging);
            $groups = $outputter->finalizeAllOutputs($site, $allow_debugging ? 'debug' : 'production');
            $this->get('pivotx.siteoptions')->set('outputter.groups.'.($allow_debugging?'debug':'production'), json_encode($groups), 'application/json', true, false, $site);
        }
        catch (\InvalidArgumentException $e) {
            $this->get('pivotx.activity')
                ->administrativeMessage(
                    null,
                    'Unable to load theme file <strong>:theme</strong>',
                    array(
                        'theme' => $theme_json
                    )
                )
                ->mostImportant()
                ->log()
                ;
        }

        return $webresourcer;
    }

    protected function runOnce()
    {
        $stopwatch = $this->container->get('debug.stopwatch', \Symfony\Component\DependencyInjection\ContainerInterface::NULL_ON_INVALID_REFERENCE);
        $sw        = null;
        if (!is_null($stopwatch)) {
            $sw = $stopwatch->start('core runonce', 'controller');
        }

        $request = $this->getRequest();
        $site    = $request->attributes->get('_site', null);


        if ($this->get('kernel')->isDebug()) {
            // on my machine this is 80-100ms
            $this->buildWebresources($site, true);
        }

        $theme_json = $this->get('pivotx.siteoptions')->getValue('themes.active', null, $site);
        if (!is_null($theme_json)) {
            $path = dirname($theme_json);
            try {
                $realpath = $this->get('kernel')->locateResource($path);
                if (is_dir($realpath) && is_dir($realpath.'/twig')) {
                    $this->get('twig.loader')->addPath($realpath . '/twig');
                }
                else {
                    $this->get('pivotx.activity')
                        ->administrativeMessage(
                            null,
                            'Cannot find twig templates at <strong>:twig</strong>',
                            array(
                                'twig' => $path.'/twig'
                            )
                        )
                        ->mostImportant()
                        ->log()
                        ;
                }
            }
            catch (\InvalidArgumentException $e) {
                $this->get('pivotx.activity')
                    ->administrativeMessage(
                        null,
                        'Unable to load theme <strong>:path</strong>',
                        array(
                            'path' => $path
                        )
                    )
                    ->mostImportant()
                    ->log()
                    ;
            }
        }

        if (!is_null($sw)) {
            $sw->stop();
        }

        return null;
    }

    public function render($view, array $parameters = array(), Response $response = null)
    {
        static $run_once = false;
        static $stop_response = false;

        // @note could be better
        if ($run_once === false) {
            $run_once = true;

            $once_response = $this->runOnce();

            if ((!is_null($once_response)) && ($once_response instanceof Response)) {
                $stop_response = $once_response;
                return $once_response;
            }
        }

        // don't understand, but sometimes we get here when were supposed to stop already
        if ($stop_response !== false) {
            return $stop_response;
        }

        if (count($parameters) == 0) {
            die('No parameters given. Aborting.');
            // @todo maybe we should not do this (because not always necessary)
            $parameters = $this->getDefaultHtmlContext();
        }

        if (is_null($view)) {
            $request = $this->getRequest();
            $view    = $request->get('_view');
        }
        if (is_null($view)) {
            $view = 'CoreBundle:Errors:unconfigured.html.twig';

            $parameters['debug'] = $this->get('kernel')->isDebug();
        }

        //echo '<hr/><pre>'; var_dump($parameters); echo '</pre><hr/>';

        if (is_array($view)) {
            foreach($view as $_view) {
                try {
                    $actual_response = parent::render($_view, $parameters, $response);

                    $this->get('pivotx.siteoptions')->logCachePerformance();

                    return $actual_response;
                }
                catch (\InvalidArgumentException $e) {
                }
            }
            throw new \InvalidArgumentException('Cannot find any of the given templates.');
        }

        $actual_response = parent::render($view, $parameters, $response);

        $this->get('pivotx.siteoptions')->logCachePerformance();

        return $actual_response;
    }

    public function anyAction(Request $request)
    {
        return $this->render(null);
    }
}

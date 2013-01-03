<?php

namespace PivotX\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use PivotX\Component\Referencer\Reference;


class Controller extends \Symfony\Bundle\FrameworkBundle\Controller\Controller
{
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

        //echo '<pre>'; var_dump($context); echo '</pre>';

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

    protected function runOnce()
    {
        $webresourcer = $this->container->get('pivotx.webresourcer');
        $webresourcer->finalizeWebresources();

        return null;
    }

    public function render($view, array $parameters = array(), Response $response = null)
    {
        static $run_once = false;

        // @note could be better
        if ($run_once === false) {
            $run_once = true;

            $once_response = $this->runOnce();

            if ((!is_null($once_response)) && ($once_response instanceof Response)) {
                return $once_response;
            }
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
            $view = 'CoreBundle:Default:unconfigured.html.twig';

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

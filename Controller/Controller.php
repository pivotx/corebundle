<?php

namespace PivotX\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


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

    protected function runOnce()
    {
        $webresourcer = $this->container->get('pivotx.webresourcer');
        $webresourcer->finalizeWebresources();
    }

    public function render($view, array $parameters = array(), Response $response = null)
    {
        static $run_once = false;

        // @note could be better
        if ($run_once === false) {
            $run_once = true;

            $this->runOnce();
        }


        if (is_null($view)) {
            $request = $this->getRequest();
            $view    = $request->get('_view');
        }
        if (is_null($view)) {
            $view = 'CoreBundle:Default:unconfigured.html.twig';
        }

        if (count($parameters) == 0) {
            $parameters = $this->getDefaultHtmlContext();
        }

        //echo '<hr/><pre>'; var_dump($parameters); echo '</pre><hr/>';

        //$parameters['html'] = $parameters;

        if (is_array($view)) {
            foreach($view as $_view) {
                try {
                    return parent::render($_view, $parameters, $response);
                }
                catch (\InvalidArgumentException $e) {
                }
            }
            throw new \InvalidArgumentException('Cannot find any of the given templates.');
        }

        return parent::render($view, $parameters, $response);
    }

    public function anyAction(Request $request)
    {
        return $this->render(null);
    }
}

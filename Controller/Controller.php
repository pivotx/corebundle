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
            )
        );

        return $context;
    }

    public function OLD_render($view, array $parameters = array(), Response $response = null)
    {
        if (is_null($view)) {
            $request = $this->getRequest();
            $view    = $request->get('_view');
        }
        if (is_null($view)) {
            $view = 'CoreBundle:Default:unconfigured.html.twig';
        }

        if (!isset($parameters['frontend'])) {
            $parameters['frontend'] = array();
        }
        if (!isset($parameters['frontend']['title'])) {
            $parameters['frontend']['title'] = 'PivotX';
        }
        if (!isset($parameters['frontend']['meta'])) {
            $parameters['frontend']['meta'] = array();
        }
        if (!isset($parameters['frontend']['meta']['charset'])) {
            $parameters['frontend']['meta']['charset'] = 'utf-8';
        }
        if (!isset($parameters['frontend']['security'])) {
            $parameters['frontend']['security'] = array ( 'logged' => false );
        }
        else if (!isset($parameters['frontend']['security']['logged'])) {
            $parameters['frontend']['security']['logged'] = false;
        }

        // @todo should not be here
        $webresourcer = $this->container->get('webresourcer');
        $outputter    = $this->container->get('outputter');
        $webresourcer->finalizeOutput($outputter);

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

    public function render($view, array $parameters = array(), Response $response = null)
    {
        static $run_once = false;

        if (is_null($view)) {
            $request = $this->getRequest();
            $view    = $request->get('_view');
        }
        if (is_null($view)) {
            $view = 'CoreBundle:Default:unconfigured.html.twig';
        }

        // @todo ahum, not the way it's supposed to be
        if ($run_once === false) {
            $webresourcer = $this->container->get('pivotx.webresourcer');
            $webresourcer->finalizeWebresources();

            $run_once = true;
        }

        if (true) {
            // @todo array_merge or variant?
            $context = $this->getDefaultHtmlContext();
            foreach($context as $k => $v) {
                if (!isset($parameters[$k])) {
                    $parameters[$k] = $v;
                }
            }
        }

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

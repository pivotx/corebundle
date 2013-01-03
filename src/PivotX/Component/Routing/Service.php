<?php

/**
 * This file is part of the PivotX Core bundle
 *
 * (c) Marcel Wouters / Two Kings <marcel@twokings.nl>
 */

namespace PivotX\Component\Routing;

use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Yaml\Yaml;
use PivotX\Component\Referencer\Reference;

/**
 * PivotX Routing Service
 *
 * @todo terrible implementation currently!
 *
 * @author Marcel Wouters <marcel@twokings.nl>
 *
 * @api
 */
class Service
{
    private $logger;

    private $routesetup;
    private $latest_routematch;

    public function __construct(LoggerInterface $logger = null, $file = false)
    {
        $this->logger     = $logger;
        $this->routesetup = new RouteSetup();

        $this->latest_routematch = null;

        if ($file === false) {
            // @todo should be different
            $fname = dirname(dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))))).'/app/config/pivotxrouting.yml';
            if (file_exists($fname)) {
                $this->load($fname);
            }
        }
    }

    public function load($fname)
    {
        $this->logger->info('Loading PivotX Routefile '.$fname);

        // @todo this is really wrong
        $config = Yaml::parse($fname);

        if (!$this->processArrayConfig($config, true)) {
            $this->logger->info('PivotX routing failure, file: '.$fname);
        }
    }

    public function processTextConfig($text)
    {
        $config = Yaml::parse($text);

        if (!$this->processArrayConfig($config, true)) {
            $this->logger->info('PivotX routing failure, text input');
        }
    }

    public function replaceMacros($in)
    {
        if (preg_match_all('|%([^%]+)%|', $in, $matches) > 0) {
            $replacements = array();
            for($i=0; $i < count($matches[0]); $i++) {
                switch ($matches[1][$i]) {
                    case 'request.host':
                        // @todo we should get either the kernel or the kernel's container
                        if (isset($_SERVER['HTTP_HOST'])) {
                            $replacements[$matches[0][$i]] = $_SERVER['HTTP_HOST'];
                        }
                        break;
                }
            }

            return strtr($in, $replacements);
        }

        return $in;
    }

    public function loadCompiledRoutes($routing)
    {
        $this->processArrayConfig($routing, false);

        return true;
    }

    /**
     * Process array styled routing configuration
     *
     * @param array $config    routing data
     * @param boolean $strict  fail if routing information is not complete
     * @return boolean         true if routing information was ok
     */

    private function processArrayConfig($config, $strict = false)
    {
        if (!isset($config['targets']) || !is_array($config['targets'])) {
            if ($strict) {
                echo '<pre>'; var_dump($config); echo '</pre>';
                // @todo throw exception
                $this->logger->err('No targets defined in route configuration.');
                return false;
            }
            $config['targets'] = array();
        }
        if (!isset($config['sites']) || !is_array($config['sites'])) {
            if ($strict) {
                // @todo throw exception
                $this->logger->err('No sites defined in route configuration.');
                return false;
            }
            $config['sites'] = array();
        }
        if (!isset($config['languages']) || !is_array($config['languages'])) {
            if ($strict) {
                // @todo throw exception
                $this->logger->err('No languages defined in route configuration.');
                return false;
            }
            $config['languages'] = array();
        }

        foreach($config['targets'] as $targeta) {
            if (!isset($targeta['name']) || !isset($targeta['description'])) {
                // @todo throw exception
                $this->logger->err('No name and description defined for a target.');
                return false;
            }

            $this->routesetup->addTarget(new Target($targeta['name'],$targeta['description']));
        }
        foreach($config['sites'] as $sitea) {
            if (!isset($sitea['name']) || !isset($sitea['description'])) {
                // @todo throw exception
                $this->logger->err('No name and description defined for a site.');
                return false;
            }

            $this->routesetup->addSite(new Site($sitea['name'],$sitea['description']));
        }
        foreach($config['languages'] as $languagea) {
            if (!isset($languagea['name']) || !isset($languagea['description']) || !isset($languagea['locale'])) {
                // @todo throw exception
                $this->logger->err('No name, description and locale defined for a language.');
                return false;
            }

            $this->routesetup->addLanguage(new Language($languagea['name'],$languagea['description'],$languagea['locale']));
        }

        $routeprefixes = new RoutePrefixes($this->routesetup);
        foreach($config['routeprefixes'] as $routeprefixa) {
            if (!isset($routeprefixa['filter']) || !isset($routeprefixa['filter']['target']) || !isset($routeprefixa['filter']['site']) || !isset($routeprefixa['filter']['language'])) {
                // @todo throw exception
                $this->logger->err('Missing target, site or language for routeprefix/filter.');
                return false;
            }
            if (!isset($routeprefixa['prefix'])) {
                // @todo throw exception
                $this->logger->err('Missing prefix for routeprefix.');
                return false;
            }
            $filter      = array ( 'target' => $routeprefixa['filter']['target'], 'site' => $routeprefixa['filter']['site'], 'language' => $routeprefixa['filter']['language'] );
            $prefix      = $this->replaceMacros($routeprefixa['prefix']);
            $aliases     = array();
            if (isset($routeprefixa['aliases'])) {
                $aliases = $routeprefixa['aliases'];
                if (!is_array($aliases)) {
                    $aliases = array($aliases);
                }
            }
            $routeprefix = new RoutePrefix($prefix,$aliases);

            $routeprefixes->add($filter,$routeprefix);
        }

        $routecollection = new RouteCollection($this->routesetup);
        foreach($config['routes'] as $routea) {
            if (!isset($routea['filter']) || !isset($routea['filter']['target']) || !isset($routea['filter']['site']) || !isset($routea['filter']['language'])) {
                // @todo throw exception
                $this->logger->err('Missing target, site and language for route.');
                return false;
            }
            if (!isset($routea['pattern']) || !isset($routea['public'])) {
                // @todo throw exception
                $this->logger->err('Missing pattern and public for route.');
                return false;
            }
            $requirements = array();
            $defaults     = array();
            if (isset($routea['requirements']) && is_array($routea['requirements'])) {
                $requirements = $routea['requirements'];
            }
            if (isset($routea['defaults']) && is_array($routea['defaults'])) {
                $defaults = $routea['defaults'];
            }
            $route = new Route(
                $routea['pattern'], $routea['public'],
                $requirements,
                $defaults
            );

            $routecollection->add(
                $routea['filter'],
                $route
            );
        }

        return true;
    }

    public function setLatestRouteMatch(RouteMatch $routematch)
    {
        $this->latest_routematch = $routematch;
    }

    public function getLatestRouteMatch()
    {
        return $this->latest_routematch;
    }

    /**
     * Build URL
     * 
     * Build an URL from a textual references.
     *
     * Option "absolute":
     * By default this is false, unless the buildUrl is not called
     * within the context of a Request, then it's true.
     *
     * Option "canonical":
     * If true we always return original routeprefixes.
     * Note: in theory it doesn't have to be absolute.
     * @todo not working atm
     * 
     * @param string $text      textual reference
     * @param array $arguments  query arguments to add to the url
     * @param array $options    various options:
     *                          - absolute   return an absolute url,
     *                                        default is false
     *                          - canonical  return the canonical url,
     *                                        default is false
     */
    public function buildUrl($text, $arguments = array(), $options = array())
    {
        if (!isset($options['absolute'])) {
            $options['absolute'] = false;
        }
        if (!isset($options['canonical'])) {
            $options['canonical'] = false;
        }

        /*
        if (is_null($this->latest_routematch)) {
            $options['absolute'] = true;
        }
         */

        //echo get_class($this->latest_routematch); var_dump($options);

        $url = null;
//        if (!$options['absolute']) {
        if (!is_null($this->latest_routematch)) {
            $url = $this->latest_routematch->buildOtherUrl($text, $options);
        }

        //var_dump($text); var_dump($url);

        if (is_null($url)) {
            $url = $this->routesetup->buildUrl($text);
        }

        // @todo we created absolute url's for too easy
        // @todo for now we insert app_dev.php when the current URI has it
        if (strpos($_SERVER['REQUEST_URI'],'app_dev.php')) {
            $url = str_replace('twokings.eu/','twokings.eu/app_dev.php/',$url);
        }

        //$this->logger->info('Text "'.$text.'", url "'.$url.'"');

        if (is_array($arguments) && (count($arguments) > 0)) {
            $anchor = false;
            if (isset($arguments['#'])) {
                $anchor = $arguments['#'];
                unset($arguments['#']);
            }
            if (count($arguments) > 0) {
                $query = http_build_query($arguments);
                if (strpos($url, '?') === false) {
                    $url .= '?' . $query;
                }
                else {
                    $url .= '&' . $query;
                }
            }
            if ($anchor !== false) {
                $url .= '#' . $anchor;
            }
        }

        return $url;
    }

    public function getRouteSetup()
    {
        return $this->routesetup;
    }
}

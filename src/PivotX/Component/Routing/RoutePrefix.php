<?php

/**
 * This file is part of the PivotX Core bundle
 *
 * (c) Marcel Wouters / Two Kings <marcel@twokings.nl>
 */

namespace PivotX\Component\Routing;

use PivotX\Component\Referencer\Reference;

/**
 * RoutePrefix is a base url, used for the highest level of routing
 *
 * @author Marcel Wouters <marcel@twokings.nl>
 *
 * @api
 */
class RoutePrefix
{
    /**
     * Prefix
     */
    private $prefix = false;

    /**
     * Aliases
     */
    private $aliases = array();

    /**
     * Filter for this RoutePrefix
     */
    private $filter = array();

    /**
     * The latest actual match from a matchUrl call
     */
    private $latest_match = false;

    /**
     * Constructor.
     *
     * @param string $name    canonical prefix
     * @param array  $aliases other prefixes
     */
    public function __construct($prefix, array $aliases = array(), array $filter = array())
    {
        $this->setPrefix($prefix);
        $this->setAliases($aliases);
        $this->setFilter($filter);
    }

    /**
     * Set the prefix
     *
     * This method implements a fluent interface.
     *
     * @todo throw exception if prefix is empty,false,etc?
     *
     * @param string $prefix The prefix
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * Get the prefix
     *
     * @return string The prefix
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Set aliases
     *
     * This method implements a fluent interface.
     *
     * @param array $aliases The aliases
     */
    public function setAliases($aliases)
    {
        $this->aliases = $aliases;

        return $this;
    }

    /**
     * Get aliases
     *
     * @return array The aliases
     */
    public function getAliases()
    {
        return $this->aliases;
    }

    /**
     * Is url matched by an alias
     *
     * @param string $url URL to search for
     * @return boolean    true if an alias matches
     */
    public function isAlias($url)
    {
        foreach($this->aliases as $alias) {
            if (strpos($url,$alias) === 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * Set the filter
     *
     * This method implements a fluent interface.
     *
     * @param array $filter The filter
     */
    public function setFilter(array $filter = array())
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * Get the filter
     *
     * @return array The filter
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * Return the Route part of the full URL
     *
     * Only works if the URL is matched by this RoutePrefix
     *
     * @param string $url URL to match against
     * @return string     stripped URL, or false if not found
     */
    public function getRouteUrl($url)
    {
        if (strpos($url,$this->prefix) === 0) {
            return str_replace($this->prefix,'',$url);
        }

        foreach($this->aliases as $alias) {
            if (strpos($url,$alias) === 0) {
                return str_replace($alias,'',$url);
            }
        }

        return false;
    }

    /**
     * 
     */
    protected function matchPossibleMacros($regex, $url)
    {
        if (substr($regex, 0, 1) == substr($regex, -1, 1)) {
            // is regex

            if (preg_match($regex, $url, $match)) {
                return $match[1];
            }
        }

        return false;
    }

    /**
     * Try to match an URL to this prefix
     *
     * @param string $url URL to match against
     * @return boolean    true if prefix or alias matches
     */
    public function matchUrl($url)
    {
        if (strpos($url,$this->prefix) === 0) {
            $this->latest_match = $this->prefix;
            return true;
        }

        foreach($this->aliases as $alias) {
            if (strpos($url,$alias) === 0) {
                $this->latest_match = $alias;
                return true;
            }
        }

        if (($matchUrl = $this->matchPossibleMacros($this->prefix, $url)) !== false) {
            $this->aliases[]    = $matchUrl;
            $this->latest_match = $matchUrl;
            return true;
        }

        // @todo %any% not working completely
        if (strpos($this->prefix, '%any%') !== false) {
            if (preg_match('|https?://([^/]+)|', $url, $match)) {
                $host   = $match[1];
                $prefix = str_replace('%any%', $host, $this->prefix);

                if (strpos($url, $prefix) === 0) {
                    $this->latest_match = $prefix;
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Try to match a Reference to this prefix
     *
     * @param Reference $reference Reference to match against
     * @return boolean             true if prefix matches
     */
    public function matchReference(Reference $reference)
    {
        $filter = $reference->getRouteFilter();

        return $this->matchFilter($filter);
    }

    /**
     * Try to match a filter to this prefix
     *
     * Filter to match is always a simplified filter.
     *
     * @param array $filter Simplified filter to match against
     * @return boolean      true if filter matched
     */
    public function matchFilter(array $filter)
    {
        $keys = array('site', 'target', 'language');

        foreach($keys as $key) {
            if (($filter[$key] !== false)  && ($filter[$key] != $this->filter[$key])) {
                return false;
            }
            /*
            if ($filter[$key] !== false) {
                if (!in_array($filter[$key],$this->filter[$key])) {
                    return false;
                }
            }
            */
        }

        return true;
    }

    /**
     * Build the URL prefix
     *
     * Return the URL prefix.
     *
     * @return string the URL part
     */
    public function buildUrl()
    {
        return $this->getPrefix();
    }

    /**
     * Get the latest matched prefix, alias or the 
     * default prefix if none matched.
     */
    public function getLatestMatchPrefix()
    {
        if ($this->latest_match !== false) {
            return $this->latest_match;
        }
        return $this->getPrefix();
    }
}

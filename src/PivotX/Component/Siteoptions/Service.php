<?php

/**
 * This file is part of the PivotX Core bundle
 *
 * (c) Marcel Wouters / Two Kings <marcel@twokings.nl>
 */

namespace PivotX\Component\Siteoptions;

use PivotX\Component\Routing\Service as RoutingService;
use Doctrine\Bundle\DoctrineBundle\Registry;
use PivotX\CoreBundle\Entity\SiteOption;

/**
 * PivotX Siteoptions Service
 *
 * @author Marcel Wouters <marcel@twokings.nl>
 *
 * @api
 */
class Service
{
    private $pivotx_routing = false;
    private $doctrine_registry = false;
    private $entity_manager = false;
    private $entity_class = false;

    /**
     * If true, we don't flush after every ->persist()
     */
    private $in_transaction = false;

    public function __construct(RoutingService $pivotx_routing, Registry $doctrine_registry)
    {
        $this->pivotx_routing    = $pivotx_routing;
        $this->doctrine_registry = $doctrine_registry;

        $this->entity_manager = $this->doctrine_registry->getEntityManager();

        $this->determineEntityClass();
    }

    /**
     * Begin 'transaction'
     *
     * Beware!
     * This is not a real transaction, but just a way to
     * to not flush everytime after a SiteOption update.
     * Normally only used when in a setup-call.
     */
    public function beginTrans()
    {
        $this->in_transaction = true;
    }

    /**
     * Commit 'transaction'
     *
     * This actually just disabled our 'transaction' and
     * flushes Doctrine
     */
    public function commitTrans()
    {
        $this->in_transaction = false;
        $this->entity_manager->flush();
    }

    /**
     * Determine which translationtext entity we should use
     */
    private function determineEntityClass()
    {
        $ems = $this->doctrine_registry->getEntityManagers();
        foreach ($ems as $em) {
            $classes = $em->getMetadataFactory()->getAllMetadata();
            foreach($classes as $class) {
                $_p = explode('\\',$class->name);
                $base_class = $_p[count($_p)-1];

                if ($base_class == 'SiteOption') {
                    $this->entity_class = $class->name;
                }
            }
        }
    }

    /**
     * Convert key/filter to individual items
     * 
     * @param string $key      key
     * @param array $filter    PivotXRouting Filter, if null use latest RouteMatch
     * @return array           array of strings (groupname, name, site, language)
     */
    private function decodeKeyFilter($key, $filter = null)
    {
        $site     = null;
        $language = null;

        if (!is_null($filter)) {
            if (preg_match('/&(site|s)=([^&]+)/', '&'.$filter, $match)) {
                $site = $match[1];
            }
        }

        if (is_null($site) || is_null($language)) {
            $routematch = $this->pivotx_routing->getLatestRouteMatch();
            if (!is_null($routematch)) {
                $filter = $routematch->getRoutePrefix()->getFilter();
                if (is_null($site)) {
                    $site = $filter['site'];
                }
            }
        }

        $pos = strpos($key, '.');
        if ($pos !== false) {
            $groupname = substr($key, 0, $pos);
            $name      = substr($key, $pos+1);
        }
        else {
            $groupname = 'common';
            $name      = $key;
        }

        return array($groupname, $name, $site);
    }

    /**
     * Find the entity of the key/filter
     *
     * @param string $key         key to search for
     * @param string $groupname
     * @param string $name
     * @param string $site
     * @return                    translation entity, null if not found
     */
    private function findEntity($groupname, $name, $site)
    {
        $arguments = array(
            'groupname' => $groupname,
            'name' => $name
        );
        if (!is_null($site)) {
            $arguments['sitename'] = $site;
        }

        $siteoption = $this->doctrine_registry->getRepository($this->entity_class)->findOneBy($arguments);

        return $siteoption;
    }

    /**
     * Get a particular siteoption
     *
     * @param string $key       key to search for
     * @param array $filter     pivotxrouting filter, if null use latest routematch
     * @return SiteOption       siteoption object
     */
    public function getSiteOption($key, $filter = null)
    {
        list($groupname, $name, $site) = $this->decodeKeyFilter($key, $filter);

        return $this->findEntity($groupname, $name, $site);
    }

    /**
     * Get a particular siteoption value
     *
     * @param string $key       key to search for
     * @param mixed $default    default to return when no such option exists
     * @param array $filter     pivotxrouting filter, if null use latest routematch
     * @return mixed            value, decoded if encoded
     */
    public function getValue($key, $default = null, $filter = null)
    {
        list($groupname, $name, $site) = $this->decodeKeyFilter($key, $filter);

        $siteoption = $this->findEntity($groupname, $name, $site);

        if (is_null($siteoption)) {
            return $default;
        }

        switch ($siteoption->getMediatype()) {
            case 'application/json':
                return json_decode($siteoption->getValue());
                break;
        }

        return $siteoption->getValue();
    }

    /**
     * Set value for a specific key
     *
     * Default arguments will not be changed for existing keys.
     * For new keys, the following .real. defaults will be used:
     * - mediatype      text/plain
     * - autoload       false
     * - human_editable true
     */
    public function set($key, $value, $mediatype = null, $autoload = null, $human_editable = null, $filter = null)
    {
        list($groupname, $name, $site) = $this->decodeKeyFilter($key, $filter);

        $siteoption = $this->findEntity($groupname, $name, $site);

        if (is_null($siteoption)) {
            $siteoption = new $this->entity_class;

            $siteoption->setSitename($site);
            $siteoption->setGroupname($groupname);
            $siteoption->setName($name);

            if (is_null($mediatype)) {
                $mediatype = 'text/plain';
            }
            $siteoption->setMediatype($mediatype);
            if (is_null($autoload)) {
                $autoload = false;
            }
            if (is_null($human_editable)) {
                $autoload = true;
            }
        }

        if (!is_null($mediatype)) {
            $siteoption->setMediatype($mediatype);
        }
        if (!is_null($autoload)) {
            $siteoption->setAutoload($autoload);
        }
        if (!is_null($human_editable)) {
            $siteoption->setHumanEditable($human_editable);
        }

        $siteoption->setValue($value);

        $this->entity_manager->persist($siteoption);

        if (!$this->in_transaction) {
            $this->entity_manager->flush();
        }
    }

    /**
     * Suggest a value for a specific key
     */
    public function suggestValue($groupname, $name, $site, $value, $mediatype = 'text/plain', $autoload = false, $human_editable = false)
    {
        $siteoption = $this->findEntity($groupname, $name, $site);

        if (is_null($siteoption)) {
            $siteoption = new $this->entity_class;

            $siteoption->setSitename($site);
            $siteoption->setGroupname($groupname);
            $siteoption->setName($name);
            $siteoption->setMediatype($mediatype);
            $siteoption->setValue($value);
            $siteoption->setAutoload($autoload);
            $siteoption->setHumanEditable($human_editable);

            $this->entity_manager->persist($siteoption);

            if (!$this->in_transaction) {
                $this->entity_manager->flush();
            }
        }
    }
}

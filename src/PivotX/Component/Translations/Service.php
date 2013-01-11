<?php

/**
 * This file is part of the PivotX Core bundle
 *
 * (c) Marcel Wouters / Two Kings <marcel@twokings.nl>
 */

namespace PivotX\Component\Translations;

use PivotX\Component\Routing\Service as RoutingService;
use Doctrine\Bundle\DoctrineBundle\Registry;
use PivotX\CoreBundle\Entity\TranslationText;

/**
 * PivotX Translation Service
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
     * Local code cache
     */
    private $cache = false;

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

        $this->cache = array();
    }

    /**
     * Begin 'transaction'
     *
     * Beware!
     * This is not a real transaction, but just a way to
     * to not flush everytime after a TranslationText update.
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

                if ($base_class == 'TranslationText') {
                    $this->entity_class = $class->name;
                }
            }
        }
    }

    /**
     * Initialize translation cache
     */
    private function initializeCache($site, $language)
    {
        if (isset($this->cache[$site])) {
            if (isset($this->cache[$site][$language])) {
                return;
            }
            $this->cache[$site] = array($language => array());
        }
        else {
            $this->cache = array($site => array($language => array()));
        }

        // @todo this could be an even very efficient query
        $translations = $this->doctrine_registry->getRepository($this->entity_class)->findBy(array(
            'sitename' => $site
        ));

        $method = 'getText'.ucfirst($language);
        if (!method_exists($this->entity_class, $method)) {
            $this->logger->warn('Unsupported language "'.$language.'" requested');
            return;
        }

        $tr = array();
        foreach($translations as $translation) {
            $tr[$translation->getGroupname().'.'.$translation->getName()] = $translation->$method();
        }

        $this->cache[$site][$language] = $tr;
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
            if (preg_match('/&?(site|s)=([^&]+)/', '&'.$filter, $match)) {
                $site = $match[2];
            }
            if (preg_match('/&?(language|l)=([^&]+)/', '&'.$filter, $match)) {
                $language = $match[2];
            }
        }

        if (is_null($site) || is_null($language)) {
            $routematch = $this->pivotx_routing->getLatestRouteMatch();
            if (!is_null($routematch)) {
                $filter = $routematch->getRoutePrefix()->getFilter();
                if (is_null($site)) {
                    $site = $filter['site'];
                }
                if (is_null($language)) {
                    $language = $filter['language'];
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

        return array($groupname, $name, $site, $language);
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

        $translationtext = $this->doctrine_registry->getRepository($this->entity_class)->findOneBy($arguments);

        return $translationtext;
    }

    /**
     * @param string $key       key to search for
     * @param array $filter     pivotxrouting filter, if null use latest routematch
     */
    public function isTranslatedAutomagically($key, $filter = null)
    {
        list($groupname, $name, $site, $language) = $this->decodeKeyFilter($key, $filter);

        $translationtext = $this->findEntity($groupname, $name, $site);

        if (is_null($translationtext)) {
            return false;
        }

        if ($translationtext->getState() > TranslationText::STATE_SUGGESTED) {
            return false;
        }

        return true;
    }

    /**
     * Translate key to readable text
     *
     * @param string $key       key to search for
     * @param array $filter     pivotxrouting filter, if null use latest routematch
     * @param string $encoding  return a specific encoding, if null use 'raw'
     * @param array $macros     macros to replace within text
     * @return string           readable text
     */
    public function translate($key, $filter = null, $encoding = null, $macros = array())
    {
        if (is_null($encoding)) {
            $encoding = 'raw';
        }

        list($groupname, $name, $site, $language) = $this->decodeKeyFilter($key, $filter);

        $this->initializeCache($site, $language);
        if (isset($this->cache[$site]) && isset($this->cache[$site][$language])) {
            if (isset($this->cache[$site][$language][$key])) {
                $readable_text = $this->cache[$site][$language][$key];
                if (is_array($macros) && (count($macros) > 0)) {
                    $readable_text = strtr($readable_text, $macros);
                }
                return $readable_text;
            }
        }


        $translationtext = $this->findEntity($groupname, $name, $site);

        if (is_null($translationtext)) {
            $this->setTexts(
                $groupname, $name, $site,
                'utf-8',
                array(
                    'nl' => $key,
                    'en' => $key
                ),
                TranslationText::STATE_AUTO_TECHNICAL
            );
        }

        $method = 'getText'.ucfirst($language);
        if (method_exists($translationtext,$method)) {
            $readable_text = $translationtext->$method();
        }
        else {
            $readable_text = $key;
        }

        if (is_array($macros) && (count($macros) > 0)) {
            $readable_text = strtr($readable_text, $macros);
        }

        return $readable_text;
    }

    /**
     * Set text for a specific key
     */
    public function setTexts($groupname, $name, $site, $encoding = null, $texts = array(), $state = TranslationText::STATE_SUGGESTED)
    {
        if (is_null($encoding)) {
            $encoding = 'utf-8';
        }

        $translationtext = new $this->entity_class;

        $translationtext->setSitename($site);
        $translationtext->setGroupname($groupname);
        $translationtext->setName($name);
        $translationtext->setEncoding($encoding);
        /*
        // @todo when Timestampable works this should be removed
        $translationtext->setDateCreated(new \DateTime());
        $translationtext->setDateModified(new \DateTime());
         */
        $translationtext->setState($state);

        // @todo should auto-detect languages here
        $translationtext->setTextNl($texts['nl']);
        $translationtext->setTextEn($texts['en']);

        $this->entity_manager->persist($translationtext);

        if (!$this->in_transaction) {
            $this->entity_manager->flush();
        }
    }

    /**
     * Suggest a text for a specific key
     */
    public function suggestTexts($groupname, $name, $site, $encoding = null, $texts = array())
    {
        $translationtext = $this->findEntity($groupname, $name, $site);

        if (is_null($translationtext)) {
            $this->setTexts($groupname, $name, $site, $encoding, $texts, TranslationText::STATE_SUGGESTED);
        }
        else if ($translationtext->getState() > TranslationText::STATE_SUGGESTED) {
            $translationtext->setState(TranslationText::STATE_SUGGESTED);
            $translationtext->setTextNl($texts['nl']);
            $translationtext->setTextEn($texts['en']);

            $this->entity_manager->persist($translationtext);

            if (!$this->in_transaction) {
                $this->entity_manager->flush();
            }
        }
    }
}

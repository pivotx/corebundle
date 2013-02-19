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
use Symfony\Component\HttpKernel\Kernel;

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
    private $kernel = false;

    /**
     * Local code cache
     */
    private $cache = false;

    /**
     * If true, we don't flush after every ->persist()
     */
    private $in_transaction = false;

    public function __construct(RoutingService $pivotx_routing, Registry $doctrine_registry, Kernel $kernel)
    {
        $this->pivotx_routing    = $pivotx_routing;
        $this->doctrine_registry = $doctrine_registry;
        $this->kernel            = $kernel;

        $this->entity_manager = $this->doctrine_registry->getEntityManager();

        $this->determineEntityClass();

        $this->cache = array();

        $cacheDir = $this->kernel->getCacheDir();
        if (is_file($cacheDir.'/translations.php')) {
            $this->cache = require($cacheDir.'/translations.php');
        }
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

        $this->flushCaches();
    }

    /**
     * Flush the caches
     *
     * Flush the entityManager buffer
     * Warmup the translation cache
     */
    private function flushCaches()
    {
        $this->entity_manager->flush();

        $cachewarmer = new \PivotX\Component\Translations\CacheWarmer($this->doctrine_registry);
        $cachewarmer->warmUp($this->kernel->getCacheDir());
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
            $this->cache[$site][$language] = array();
        }
        else {
            $this->cache = array($site => array($language => array()));
        }

        $translations = $this->doctrine_registry->getRepository($this->entity_class)->findBy(array(
            'sitename' => $site
        ));

        $method = 'getText'.ucfirst($language);
        if (!method_exists($this->entity_class, $method)) {
            die('Unsupported language "'.$language.'" requested');
            return;
        }

        $tr = array();
        foreach($translations as $translation) {
            $tr[$translation->getGroupname().'.'.$translation->getName()] = array($translation->getEncoding(), $translation->$method());
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
     * Has the translation been automatic and not pre-defined?
     *
     * @param string $key       key to search for
     * @param array $filter     pivotxrouting filter, if null use latest routematch
     * @return boolean          true if magically translated
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
     */
    private function outputConvert($in, $in_encoding, $output_type)
    {
        if ($output_type == 'twig') {
            if ($in_encoding == 'utf-8/html') {
                return new \Twig_Markup($in, 'utf-8');
            }
        }
        return $in;
    }

    /**
     * Translate key to readable text
     *
     * @param string $key         key to search for
     * @param array $filter       pivotxrouting filter, if null use latest routematch
     * @param string $output_type return in a specific output type
     *                            - plain   output as-is
     *                            - twig    output twig-safe
     * @param array $macros       macros to replace within text
     * @return string             readable text
     */
    public function translate($key, $filter = null, $output_type = null, $macros = array())
    {
        if (is_null($output_type)) {
            $output_type = 'plain';
        }

        list($groupname, $name, $site, $language) = $this->decodeKeyFilter($key, $filter);

        $sw = null;
        if ($this->kernel->getContainer()->has('debug.stopwatch')) {
            $sw = $this->kernel->getContainer()->get('debug.stopwatch')->start('initializeCache', 'translateService');
        }
        $this->initializeCache($site, $language);
        if (!is_null($sw)) {
            $sw->stop();
        }

        if (isset($this->cache[$site]) && isset($this->cache[$site][$language])) {
            if (isset($this->cache[$site][$language][$key])) {
                list($encoding, $readable_text) = $this->cache[$site][$language][$key];
                if (is_array($macros) && (count($macros) > 0)) {
                    $readable_text = strtr($readable_text, $macros);
                }
                return $this->outputConvert($readable_text, $encoding, $output_type);
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

        $method   = 'getText'.ucfirst($language);
        $encoding = 'utf-8';
        if (method_exists($translationtext,$method)) {
            $readable_text = $translationtext->$method();
            $encoding      = $translationtext->getEncoding();
        }
        else {
            $readable_text = $key;
        }

        if (is_array($macros) && (count($macros) > 0)) {
            $readable_text = strtr($readable_text, $macros);
        }

        return $this->outputConvert($readable_text, $encoding, $output_type);
    }

    private function updateTexts($translationtext, $texts)
    {
        $changes = false;

        $methods = get_class_methods($translationtext);
        foreach($methods as $method) {
            if (strtolower(substr($method, 0, 7)) == 'settext') {
                $lang    = substr($method, 7);
                $lowlang = strtolower($lang);

                $getmethod = 'getText'.$lang;

                $curtext = null;
                if (method_exists($translationtext, $getmethod)) {
                    $curtext = $translationtext->$getmethod();
                }

                if (isset($texts[$lowlang])) {
                    if ((is_null($curtext)) || ($curtext != $texts[$lowlang])) {
                        $translationtext->$method($texts[$lowlang]);
                        $changes = true;
                    }
                }
                else {
                    if (is_null($curtext)) {
                        $translationtext->$method('');
                        $changes = true;
                    }
                }
            }
        }

        return $changes;
    }

    /**
     * Set text for a specific key
     */
    public function setTexts($groupname, $name, $site, $encoding = null, $texts = array(), $state = TranslationText::STATE_SUGGESTED)
    {
        if (is_null($encoding)) {
            $encoding = 'utf-8';
        }

        $translationtext = $this->findEntity($groupname, $name, $site);

        if (is_null($translationtext)) {
            $translationtext = new $this->entity_class;
        }

        $translationtext->setSitename($site);
        $translationtext->setGroupname($groupname);
        $translationtext->setName($name);
        $translationtext->setEncoding($encoding);
        $translationtext->setState($state);

        $this->updateTexts($translationtext, $texts);

        $this->entity_manager->persist($translationtext);

        if (!$this->in_transaction) {
            $this->flushCaches();
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

            $this->updateTexts($translationtext, $texts);

            $this->entity_manager->persist($translationtext);

            if (!$this->in_transaction) {
                $this->entity_manager->flush();
            }
        }
    }
}

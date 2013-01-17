<?php
namespace PivotX\Doctrine\Generator;


/**
 * This is the default entities routing generator.
 */
class Routing
{
    private $siteoptions;
    private $translations;

    public function __construct($siteoptions, $translations)
    {
        $this->siteoptions  = $siteoptions;
        $this->translations = $translations;
    }

    /**
     * Update routing for specified site/target/language and entity
     */
    private function buildSpecificRoutes($site, $target, $language, $name)
    {
        $routes = array();

        // @todo we're just assuming the entity has a slug... 
        $pattern = $name . '/' . '{slug}';
        $public  = $this->translations->translate($name.'.common.singular_slug',  'site='.$site.'&language='.$language).'/{slug}';
        $view    = '';
        if ($public != '') {
            $routes[] = array(
                'filter' => array(
                    'target' => $target,
                    'site' => $site,
                    'language' => $language
                ),
                'pattern' => $pattern,
                'public' => $public,
                'defaults' => array(
                    '_controller' => 'CoreBundle:DefaultFront:showEntityBySlug',
                    '_entity' => $name,
                    //'_view' => $view
                ),
                'requirements' => array(
                    'slug' => '[a-z]+[a-z0-9-]*'
                )
            );
        }

        $pattern = $name . '/' . '{id}';
        $public  = $this->translations->translate($name.'.common.singular_slug', 'site='.$site.'&language='.$language).'/{id}';
        $view    = '';
        if ($public != '') {
            $routes[] = array(
                'filter' => array(
                    'target' => $target,
                    'site' => $site,
                    'language' => $language
                ),
                'pattern' => $pattern,
                'public' => $public,
                'defaults' => array(
                    '_controller' => 'CoreBundle:DefaultFront:showEntityById',
                    '_entity' => $name,
                    //'_view' => $view
                ),
                'requirements' => array(
                    'id' => '[0-9]+'
                )
            );
        }

        return $routes;
    }

    /**
     * Return all configured sites
     */
    public function getSites()
    {
        $_sites = explode("\n", $this->siteoptions->getValue('config.sites', array(), 'all'));

        $sites = array();
        foreach($_sites as $site) {
            if ($site != 'pivotx-backend') {
                $sites[] = $site;
            }
        }

        return $sites;
    }

    /**
     * Return all configured languages for a site
     */
    public function getLanguagesForSite($site)
    {
        return $this->siteoptions->getValue('routing.languages', array(), $site);
    }

    /**
     * Return all configured targets for a site
     */
    public function getTargetsForSite($site)
    {
        return $this->siteoptions->getValue('routing.targets', array(), $site);
    }

    /**
     * Build routes for a specific site/entity
     *
     * @param string $site   site name
     * @param string $name   entity name
     * @return array         specific routes
     */
    private function buildRoutes($site, $name)
    {
        $routes = array();
        $name   = strtolower($name);

        $languages = $this->getLanguagesForSite($site);

        foreach($languages as $language) {
            $targets = $this->getTargetsForSite($site);

            foreach($targets as $target) {
                $routes = array_merge($routes, $this->buildSpecificRoutes(
                    $site, $target['name'], $language['name'],
                    $name
                ));
            }
        }

        return $routes;
    }

    /**
     */
    public function updateRoutes($name)
    {
        $name = strtolower($name);

        $sites = $this->getSites();
        foreach($sites as $site) {
            $routes = $this->buildRoutes($site, $name);

            $this->siteoptions->set(
                'routing.entity.'.$name,
                json_encode($routes),
                'application/json',
                false, false,
                $site
            );

            $keys = explode("\n", $this->siteoptions->getValue('routing.keys', '', $site));
            if ((!in_array('routing.entity.'.$name, $keys)) &&
                (!in_array('!routing.entity.'.$name, $keys))) {
                $keys[] = 'routing.entity.'.$name;

                $this->siteoptions->set(
                    'routing.keys',
                    trim(implode("\n", $keys)),
                    'text/plain',
                    false, true,
                    $site
                );
            }
        }
    }
}

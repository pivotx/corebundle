<?php

namespace PivotX\CoreBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class CoreExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        //$loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader = new XmlFileLoader($container, new FileLocator(dirname(dirname(dirname(__DIR__))).'/Resources/config'));
        $loader->load('services.xml');

        //echo 'Loading services in PivotX\CoreBundle\DependencyInjection\PivotXCoreExtension..'."\n";

        $this->addClassesToCompile(array(
            //'PivotX\\CoreBundle\\Controller\\Controller',

            'PivotX\\Doctrine\\Repository\\AutoEntityRepository',

            'PivotX\\Component\\Siteoptions\\Service',
            'PivotX\\Component\\Translations\\Service',
            'PivotX\\Component\\Activity\\Service',

            'PivotX\\Component\\Formats\\Service',
            'PivotX\\Component\\Formats\\FormatInterface',
            'PivotX\\Component\\Formats\\AbstractFormat',
            'PivotX\\Component\\Formats\\Formats\\AutoFormat',
            //'PivotX\\Component\\Formats\\Formats\\MarkdownFormat',

            'PivotX\\Component\\Routing\\Service',
            'PivotX\\Component\\Routing\\Language',
            'PivotX\\Component\\Routing\\Route',
            'PivotX\\Component\\Routing\\RouteCollection',
            'PivotX\\Component\\Routing\\RouteMatch',
            'PivotX\\Component\\Routing\\RoutePrefix',
            'PivotX\\Component\\Routing\\RoutePrefixes',
            'PivotX\\Component\\Routing\\RouteSetup',
            'PivotX\\Component\\Routing\\Site',
            'PivotX\\Component\\Routing\\Target',

            'PivotX\\Component\\Views\\Service',
            'PivotX\\Component\\Views\\ViewInterface',
            'PivotX\\Component\\Views\\AbstractView',
            'PivotX\\Component\\Views\\ArrayView',

            'PivotX\\Component\\Lists\\Service',
            'PivotX\\Component\\Lists\\ItemInterface',
            'PivotX\\Component\\Lists\\Item',
            'PivotX\\Component\\Lists\\UrlItem',
            'PivotX\\Component\\Lists\\Lists',

            'PivotX\\CoreBundle\\Entity\\ActivityLog',
            'PivotX\\CoreBundle\\Entity\\EmbedResource',
            'PivotX\\CoreBundle\\Entity\\GenericResource',
            'PivotX\\CoreBundle\\Entity\\LocalEmbedResource',
            'PivotX\\CoreBundle\\Entity\\SiteOption',
            'PivotX\\CoreBundle\\Entity\\TranslationText',
            'PivotX\\CoreBundle\\Entity\\User',
        ));
    }
}

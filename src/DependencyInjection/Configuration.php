<?php

namespace Loconox\EntityRoutingBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('loconox_entity_routing');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('entity_manager')->cannotBeEmpty()->defaultValue('default')->end()
                ->arrayNode('class')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('slug')->cannotBeEmpty()->defaultValue('Loconox\\EntityRoutingBundle\\Entity\\Slug')->end()
                    ->end()
                ->end()
                ->arrayNode('router')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('resource')->cannotBeEmpty()->defaultValue('kernel::loadRoutes')->end()
                        ->scalarNode('type')->cannotBeEmpty()->defaultValue('service')->end()
                    ->end()
            ->end();

        return $treeBuilder;
    }
}

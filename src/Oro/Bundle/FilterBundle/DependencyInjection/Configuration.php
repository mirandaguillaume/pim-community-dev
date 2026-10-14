<?php

namespace Oro\Bundle\FilterBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    final public const string DEFAULT_LAYOUT = '@OroFilter/Filter/layout.js.twig';

    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('oro_filter');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('twig')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('layout')
                            ->cannotBeEmpty()
                            ->defaultValue(self::DEFAULT_LAYOUT)
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}

<?php

namespace Oro\Bundle\FilterBundle\Grid\Extension;

use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    final public const FILTERS_KEY = 'filters';
    final public const FILTERS_PATH = '[filters]';
    final public const COLUMNS_PATH = '[filters][columns]';
    final public const DEFAULT_FILTERS_PATH = '[filters][default]';

    /**
     * @param $types
     * @param mixed[] $types
     */
    public function __construct(protected $types) {}

    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $builder = new TreeBuilder('filters');

        $builder->getRootNode()
            ->children()
                ->arrayNode('columns')
                    ->prototype('array')
                        ->ignoreExtraKeys()
                        ->children()
                            ->scalarNode(FilterUtility::TYPE_KEY)
                                ->isRequired()
                                ->validate()
                                ->ifNotInArray($this->types)
                                    ->thenInvalid('Invalid filter type "%s"')
                                ->end()
                            ->end()
                            ->scalarNode(FilterUtility::DATA_NAME_KEY)->isRequired()->end()
                            ->enumNode(FilterUtility::CONDITION_KEY)
                                ->values([FilterUtility::CONDITION_AND, FilterUtility::CONDITION_OR])
                            ->end()
                            ->booleanNode(FilterUtility::BY_HAVING_KEY)->end()
                            ->booleanNode(FilterUtility::ENABLED_KEY)->end()
                            ->scalarNode('feature_flag')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('default')
                        ->prototype('array')
                            ->prototype('variable')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $builder;
    }
}

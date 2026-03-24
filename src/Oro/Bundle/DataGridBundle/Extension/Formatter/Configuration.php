<?php

namespace Oro\Bundle\DataGridBundle\Extension\Formatter;

use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\PropertyInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    final public const string DEFAULT_TYPE = 'field';
    final public const string DEFAULT_FRONTEND_TYPE = PropertyInterface::TYPE_STRING;

    final public const string TYPE_KEY = 'type';
    final public const string COLUMNS_KEY = 'columns';
    final public const string OTHER_COLUMNS_KEY = 'other_columns';
    final public const string PROPERTIES_KEY = 'properties';

    /**
     * @param        $types
     * @param string $root
     * @param mixed[] $types
     */
    public function __construct(protected $types, protected $root)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $builder = new TreeBuilder($this->root);

        $builder->getRootNode()
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->ignoreExtraKeys()
                ->children()
                    ->scalarNode(self::TYPE_KEY)
                        ->defaultValue(self::DEFAULT_TYPE)
                        ->validate()
                        ->ifNotInArray($this->types)
                            ->thenInvalid('Invalid property type "%s"')
                        ->end()
                    ->end()
                    // just validate type if node exist
                    ->scalarNode(PropertyInterface::FRONTEND_TYPE_KEY)->defaultValue(self::DEFAULT_FRONTEND_TYPE)->end()
                    ->scalarNode('label')->end()
                    ->booleanNode('editable')->defaultFalse()->end()
                    ->booleanNode('renderable')->defaultTrue()->end()
                ->end()
            ->end();

        return $builder;
    }
}

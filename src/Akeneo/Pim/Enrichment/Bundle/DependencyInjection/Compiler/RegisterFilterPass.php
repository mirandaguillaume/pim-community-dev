<?php

namespace Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass to register tagged filters to the chained filter
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterFilterPass implements CompilerPassInterface
{
    /** @staticvar string The registry id */
    final public const string REGISTRY_ID = 'pim_catalog.filter.chained';

    /** @staticvar string */
    final public const string COLLECTION_FILTER_TAG = 'pim_catalog.filter.collection';

    /** @staticvar string */
    final public const string OBJECT_FILTER_TAG = 'pim_catalog.filter.object';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::REGISTRY_ID)) {
            return;
        }

        $registryDefinition = $container->getDefinition(self::REGISTRY_ID);

        foreach ($container->findTaggedServiceIds(self::COLLECTION_FILTER_TAG) as $serviceId => $attributes) {
            foreach ($attributes as $attribute) {
                $registryDefinition->addMethodCall(
                    'addCollectionFilter',
                    [
                        new Reference($serviceId),
                        $attribute['type'],
                    ]
                );
            }
        }

        foreach ($container->findTaggedServiceIds(self::OBJECT_FILTER_TAG) as $serviceId => $attributes) {
            foreach ($attributes as $attribute) {
                $registryDefinition->addMethodCall('addObjectFilter', [new Reference($serviceId), $attribute['type']]);
            }
        }
    }
}

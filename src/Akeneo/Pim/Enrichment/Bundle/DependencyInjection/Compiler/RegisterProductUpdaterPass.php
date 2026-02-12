<?php

namespace Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Register product updaters
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterProductUpdaterPass implements CompilerPassInterface
{
    /** @staticvar */
    final public const SETTER_REGISTRY = 'pim_catalog.updater.setter.registry';

    /** @staticvar */
    final public const SETTER_TAG = 'pim_catalog.updater.setter';

    /** @staticvar */
    final public const COPIER_REGISTRY = 'pim_catalog.updater.copier.registry';

    /** @staticvar */
    final public const COPIER_TAG = 'pim_catalog.updater.copier';

    /** @staticvar */
    final public const ADDER_REGISTRY = 'pim_catalog.updater.adder.registry';

    /** @staticvar */
    final public const ADDER_TAG = 'pim_catalog.updater.adder';

    /** @staticvar */
    final public const REMOVER_REGISTRY = 'pim_catalog.updater.remover.registry';

    /** @staticvar */
    final public const REMOVER_TAG = 'pim_catalog.updater.remover';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $this->registerSetters($container);
        $this->registerCopiers($container);
        $this->registerAdders($container);
        $this->registerRemovers($container);
    }

    protected function registerSetters(ContainerBuilder $container)
    {
        $registry = $container->getDefinition(self::SETTER_REGISTRY);
        $setters = $container->findTaggedServiceIds(self::SETTER_TAG);

        foreach (array_keys($setters) as $setterId) {
            $registry->addMethodCall('register', [new Reference($setterId)]);
        }
    }

    protected function registerCopiers(ContainerBuilder $container)
    {
        $registry = $container->getDefinition(self::COPIER_REGISTRY);
        $copiers = $container->findTaggedServiceIds(self::COPIER_TAG);

        foreach (array_keys($copiers) as $copierId) {
            $registry->addMethodCall('register', [new Reference($copierId)]);
        }
    }

    protected function registerAdders(ContainerBuilder $container)
    {
        $registry = $container->getDefinition(self::ADDER_REGISTRY);
        $adders = $container->findTaggedServiceIds(self::ADDER_TAG);

        foreach (array_keys($adders) as $adderId) {
            $registry->addMethodCall('register', [new Reference($adderId)]);
        }
    }

    protected function registerRemovers(ContainerBuilder $container)
    {
        $registry = $container->getDefinition(self::REMOVER_REGISTRY);
        $removers = $container->findTaggedServiceIds(self::REMOVER_TAG);

        foreach (array_keys($removers) as $removerId) {
            $registry->addMethodCall('register', [new Reference($removerId)]);
        }
    }
}

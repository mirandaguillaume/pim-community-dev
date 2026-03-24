<?php

namespace Akeneo\Tool\Bundle\VersioningBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterVersionPurgerAdvisorPass implements CompilerPassInterface
{
    final public const int DEFAULT_PRIORITY = 100;

    final public const string REGISTRY_ID = 'pim_versioning.purger.version';

    final public const string ADVISOR_TAG_NAME = 'pim_versioning.purger.advisor';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::REGISTRY_ID)) {
            return;
        }

        $registryDefinition = $container->getDefinition(self::REGISTRY_ID);

        $taggedServices = $container->findTaggedServiceIds(self::ADVISOR_TAG_NAME);

        $services = [];

        foreach ($taggedServices as $serviceId => $tags) {
            foreach ($tags as $tag) {
                $priority = $tag['priority'] ?? self::DEFAULT_PRIORITY;
                $services[$priority][] = $serviceId;
            }
        }

        ksort($services);

        foreach ($services as $serviceIds) {
            foreach ($serviceIds as $serviceId) {
                $registryDefinition->addMethodCall('addVersionPurgerAdvisor', [new Reference($serviceId)]);
            }
        }
    }
}

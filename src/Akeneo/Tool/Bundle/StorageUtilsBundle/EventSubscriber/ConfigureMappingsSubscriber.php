<?php

namespace Akeneo\Tool\Bundle\StorageUtilsBundle\EventSubscriber;

use Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\MappingsOverrideConfiguratorInterface;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LoadClassMetadataEventArgs;

/**
 * Configure the mappings of the metadata classes.
 *
 * @author    Julien Janvier <jjanvier@gmail.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[AsDoctrineListener(event: Events::loadClassMetadata, priority: 100)]
class ConfigureMappingsSubscriber
{
    protected \Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\MappingsOverrideConfiguratorInterface $configurator;

    protected array $mappingOverrides;

    public function __construct(MappingsOverrideConfiguratorInterface $configurator, array $mappingOverrides)
    {
        $this->configurator = $configurator;
        $this->mappingOverrides = $mappingOverrides;
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        $this->configurator->configure(
            $eventArgs->getClassMetadata(),
            $this->mappingOverrides,
            $eventArgs->getObjectManager()->getConfiguration()
        );
    }
}

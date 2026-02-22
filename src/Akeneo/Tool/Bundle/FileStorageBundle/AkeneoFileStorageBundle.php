<?php

namespace Akeneo\Tool\Bundle\FileStorageBundle;

use Akeneo\Tool\Bundle\FileStorageBundle\DependencyInjection\Compiler\ResolveDoctrineTargetModelPass;
use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Akeneo File Storage Bundle.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AkeneoFileStorageBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container
            ->addCompilerPass(new ResolveDoctrineTargetModelPass());

        $mappings = [
            'Akeneo\Tool\Component\FileStorage\Model' => realpath(__DIR__.'/../../Component/FileStorage/Model'),
        ];

        $container->addCompilerPass(
            DoctrineOrmMappingsPass::createAttributeMappingDriver(
                $mappings,
                ['doctrine.orm.entity_manager'],
                false
            )
        );
    }
}

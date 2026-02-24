<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle;

use Akeneo\Pim\Structure\Bundle\DependencyInjection\Compiler\RegisterReferenceDataConfigurationsPass;
use Akeneo\Pim\Structure\Bundle\DependencyInjection\Compiler\ResolveDoctrineTargetModelPass;
use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author    Arnaud Langlade <arnaud.langlade@gmail.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AkeneoPimStructureBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container): void
    {
        $container
            ->addCompilerPass(new ResolveDoctrineTargetModelPass())
            ->addCompilerPass(new RegisterReferenceDataConfigurationsPass())
        ;

        $productMappings = [
            'Akeneo\Pim\Structure\Component\Model' => dirname(__DIR__) . '/Component/Model'
        ];

        $container->addCompilerPass(
            DoctrineOrmMappingsPass::createAttributeMappingDriver(
                array_keys($productMappings),
                array_values($productMappings),
                ['doctrine.orm.entity_manager'],
                false
            )
        );
    }
}

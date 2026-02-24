<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Symfony;

use Akeneo\Category\Infrastructure\Symfony\DependencyInjection\CompilerPass\RegisterCategoryItemCounterPass;
use Akeneo\Category\Infrastructure\Symfony\DependencyInjection\CompilerPass\RegisterPreviewGeneratorPass;
use Akeneo\Category\Infrastructure\Symfony\DependencyInjection\CompilerPass\ResolveDoctrineTargetModelPass;
use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author    Griffins
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AkeneoCategoryBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container
            ->addCompilerPass(new ResolveDoctrineTargetModelPass())
            ->addCompilerPass(new RegisterCategoryItemCounterPass())
            ->addCompilerPass(new RegisterPreviewGeneratorPass())
        ;

        $mappings = [
            'Akeneo\Category\Infrastructure\Component\Model' => dirname(__DIR__).'/Component/Model',
        ];

        $container->addCompilerPass(
            DoctrineOrmMappingsPass::createAttributeMappingDriver(
                $mappings,
                array_values($mappings),
                ['doctrine.orm.entity_manager'],
                false,
            ),
        );
    }
}

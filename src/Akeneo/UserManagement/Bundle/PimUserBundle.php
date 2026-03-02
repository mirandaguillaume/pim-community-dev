<?php

namespace Akeneo\UserManagement\Bundle;

use Akeneo\UserManagement\Bundle\DependencyInjection\Compiler\RegisterCommandsThatNeedUserSystemPass;
use Akeneo\UserManagement\Bundle\DependencyInjection\Compiler\ResolveDoctrineTargetModelPass;
use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimUserBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ResolveDoctrineTargetModelPass());

        $productMappings = [
            'Akeneo\UserManagement\Component\Model' => dirname(__DIR__) . '/Component/Model',
        ];

        $container->addCompilerPass(
            DoctrineOrmMappingsPass::createAttributeMappingDriver(
                array_keys($productMappings),
                array_values($productMappings),
                ['doctrine.orm.entity_manager'],
                false
            )
        );

        $container->addCompilerPass(new RegisterCommandsThatNeedUserSystemPass());
    }
}

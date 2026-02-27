<?php

namespace Akeneo\Tool\Bundle\VersioningBundle;

use Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler\RegisterSerializerPass;
use Akeneo\Tool\Bundle\VersioningBundle\DependencyInjection\Compiler\RegisterUpdateGuessersPass;
use Akeneo\Tool\Bundle\VersioningBundle\DependencyInjection\Compiler\RegisterVersionPurgerAdvisorPass;
use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Pim Versioning Bundle
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AkeneoVersioningBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container
            ->addCompilerPass(new RegisterSerializerPass('pim_versioning.serializer'))
            ->addCompilerPass(new RegisterUpdateGuessersPass())
            ->addCompilerPass(new RegisterVersionPurgerAdvisorPass());

        $versionMappings = [
            'Akeneo\Tool\Component\Versioning\Model' => dirname(__DIR__, 2) . '/Component/Versioning/Model',
        ];

        $container->addCompilerPass(
            DoctrineOrmMappingsPass::createAttributeMappingDriver(
                array_keys($versionMappings),
                array_values($versionMappings),
                ['doctrine.orm.entity_manager'],
                false
            )
        );
    }
}

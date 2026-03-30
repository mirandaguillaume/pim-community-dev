<?php

namespace spec\Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product;

use Akeneo\Pim\Enrichment\Component\Product\Repository\GroupRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\ConfiguratorInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\RequestStack;

class ContextConfiguratorSpec extends ObjectBehavior
{
    public function let(
        ProductRepositoryInterface $repository,
        RequestParameters $requestParams,
        UserContext $userContext,
        ObjectManager $objectManager,
        GroupRepositoryInterface $productGroupRepository,
        RequestStack $requestStack
    ) {
        $this->beConstructedWith(
            $repository,
            $requestParams,
            $userContext,
            $objectManager,
            $productGroupRepository,
            $requestStack
        );
    }

    public function it_is_a_configurator()
    {
        $this->shouldImplement(ConfiguratorInterface::class);
    }
}

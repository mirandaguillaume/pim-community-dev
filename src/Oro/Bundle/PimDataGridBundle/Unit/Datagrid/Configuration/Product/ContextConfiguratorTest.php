<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product;

use Akeneo\Pim\Enrichment\Component\Product\Repository\GroupRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\ConfiguratorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product\ContextConfigurator;
use Symfony\Component\HttpFoundation\RequestStack;

class ContextConfiguratorTest extends TestCase
{
    private ProductRepositoryInterface|MockObject $repository;
    private RequestParameters|MockObject $requestParams;
    private UserContext|MockObject $userContext;
    private ObjectManager|MockObject $objectManager;
    private GroupRepositoryInterface|MockObject $productGroupRepository;
    private RequestStack|MockObject $requestStack;
    private ContextConfigurator $sut;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ProductRepositoryInterface::class);
        $this->requestParams = $this->createMock(RequestParameters::class);
        $this->userContext = $this->createMock(UserContext::class);
        $this->objectManager = $this->createMock(ObjectManager::class);
        $this->productGroupRepository = $this->createMock(GroupRepositoryInterface::class);
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->sut = new ContextConfigurator(
            $this->repository,
            $this->requestParams,
            $this->userContext,
            $this->objectManager,
            $this->productGroupRepository,
            $this->requestStack
        );
    }

    public function test_it_is_a_configurator(): void
    {
        $this->assertInstanceOf(ConfiguratorInterface::class, $this->sut);
    }
}

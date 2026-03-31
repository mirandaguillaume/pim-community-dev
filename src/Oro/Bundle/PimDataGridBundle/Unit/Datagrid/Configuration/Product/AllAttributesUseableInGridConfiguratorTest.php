<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product;

use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\ConfiguratorInterface;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product\AllAttributesUseableInGridConfigurator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;

class AllAttributesUseableInGridConfiguratorTest extends TestCase
{
    private AttributeRepositoryInterface|MockObject $attributeRepository;
    private UserContext|MockObject $userContext;
    private RequestParameters|MockObject $requestParams;
    private RequestStack|MockObject $requestStack;
    private AllAttributesUseableInGridConfigurator $sut;

    protected function setUp(): void
    {
        $this->attributeRepository = $this->createMock(AttributeRepositoryInterface::class);
        $this->userContext = $this->createMock(UserContext::class);
        $this->requestParams = $this->createMock(RequestParameters::class);
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->sut = new AllAttributesUseableInGridConfigurator($this->attributeRepository, $this->userContext, $this->requestParams, $this->requestStack);
    }

    public function test_it_is_a_datagrid_configurator(): void
    {
        $this->assertInstanceOf(ConfiguratorInterface::class, $this->sut);
    }
}

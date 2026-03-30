<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Oro\Bundle\PimDataGridBundle\Datasource;

use Oro\Bundle\PimDataGridBundle\Datasource\DatasourceAdapterResolver;
use PHPUnit\Framework\TestCase;

class DatasourceAdapterResolverTest extends TestCase
{
    private DatasourceAdapterResolver $sut;

    protected function setUp(): void
    {
        $this->sut = new DatasourceAdapterResolver('orm_adapter_class', 'product_orm_adapter_class');
        $this->sut->addProductDatasource('pim_datasource_product');
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(DatasourceAdapterResolver::class, $this->sut);
    }

    public function test_it_returns_an_orm_adapter_class_for_default_datasource_when_orm_support_is_enabled(): void
    {
        $this->assertSame('orm_adapter_class', $this->sut->getAdapterClass('pim_datasource_default'));
    }

    public function test_it_returns_a_product_orm_adapter_class_for_product_datasource_when_orm_support_is_enabled(): void
    {
        $this->assertSame('product_orm_adapter_class', $this->sut->getAdapterClass('pim_datasource_product'));
    }

    public function test_it_returns_an_orm_adapter_class_for_smart_datasource_when_orm_support_is_enabled(): void
    {
        $this->assertSame('orm_adapter_class', $this->sut->getAdapterClass('pim_smart_datasource'));
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Oro\Bundle\PimFilterBundle\Filter\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Akeneo\Pim\Structure\Component\Repository\AssociationTypeRepositoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\FilterBundle\Filter\BooleanFilter;
use Oro\Bundle\PimDataGridBundle\Datagrid\Request\RequestParametersExtractorInterface;
use Oro\Bundle\PimFilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\PimFilterBundle\Filter\Product\IsAssociatedFilter;
use Oro\Bundle\PimFilterBundle\Filter\ProductFilterUtility;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormFactoryInterface;

class IsAssociatedFilterTest extends TestCase
{
    private FormFactoryInterface|MockObject $factory;
    private ProductFilterUtility|MockObject $utility;
    private RequestParametersExtractorInterface|MockObject $extractor;
    private AssociationTypeRepositoryInterface|MockObject $assocRepository;
    private ProductRepositoryInterface|MockObject $productRepository;
    private IsAssociatedFilter $sut;

    protected function setUp(): void
    {
        $this->factory = $this->createMock(FormFactoryInterface::class);
        $this->utility = $this->createMock(ProductFilterUtility::class);
        $this->extractor = $this->createMock(RequestParametersExtractorInterface::class);
        $this->assocRepository = $this->createMock(AssociationTypeRepositoryInterface::class);
        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);
        $this->sut = new IsAssociatedFilter($this->factory, $this->utility, $this->extractor, $this->assocRepository, $this->productRepository);
    }

    public function test_it_is_an_oro_choice_filter(): void
    {
        $this->assertInstanceOf(BooleanFilter::class, $this->sut);
    }

    public function test_it_applies_a_filter_on_product_when_its_in_an_expected_association(): void
    {
        $datasource = $this->createMock(FilterDatasourceAdapterInterface::class);
        $assocType = $this->createMock(AssociationTypeInterface::class);
        $productOwner = $this->createMock(ProductInterface::class);
        $productAssociatedOne = $this->createMock(ProductInterface::class);
        $productAssociatedTwo = $this->createMock(ProductInterface::class);

        $this->extractor->method('getDatagridParameter')->with('_parameters', [])->willReturn([]);
        $this->extractor->method('getDatagridParameter')->with('associationType')->willReturn(1);
        $assocType->method('getCode')->willReturn('XSELL');
        $this->assocRepository->method('findOneBy')->with($this->anything())->willReturn($assocType);
        $this->extractor->method('getDatagridParameter')->with('product')->willReturn(11);
        $this->productRepository->method('find')->with(11)->willReturn($productOwner);
        $productOwner->method('getAssociatedProducts')->with('XSELL')->willReturn(new ArrayCollection([$productAssociatedOne, $productAssociatedTwo]));
        $productAssociatedOne->method('getId')->willReturn(12);
        $productAssociatedTwo->method('getId')->willReturn(13);
        $this->utility->expects($this->once())->method('applyFilter')->with($datasource, 'id', 'IN', ['12', '13']);
        $this->sut->apply($datasource, ['type' => null, 'value' => 1]);
    }
}

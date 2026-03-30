<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\Infrastructure\Query\Elasticsearch;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FilterRegistryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderOptionsResolverInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\SorterRegistryInterface;
use Akeneo\Pim\Enrichment\Product\Domain\PQB\ProductQueryBuilderInterface;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Query\Elasticsearch\ProductQueryBuilderAdapter;
use Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\Category\GetGrantedCategoryCodes;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use Akeneo\Test\Pim\Enrichment\Product\Helper\FeatureHelper;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class ProductQueryBuilderAdapterTest extends TestCase
{
    private AttributeRepositoryInterface|MockObject $attributeRepository;
    private FilterRegistryInterface|MockObject $filterRegistry;
    private SorterRegistryInterface|MockObject $sorterRegistry;
    private ProductQueryBuilderOptionsResolverInterface|MockObject $optionResolver;
    private FeatureFlags|MockObject $featureFlags;
    private UserRepositoryInterface|MockObject $userRepository;
    private ProductQueryBuilderAdapter $sut;

    protected function setUp(): void
    {
        $this->attributeRepository = $this->createMock(AttributeRepositoryInterface::class);
        $this->filterRegistry = $this->createMock(FilterRegistryInterface::class);
        $this->sorterRegistry = $this->createMock(SorterRegistryInterface::class);
        $this->optionResolver = $this->createMock(ProductQueryBuilderOptionsResolverInterface::class);
        $this->featureFlags = $this->createMock(FeatureFlags::class);
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->sut = new ProductQueryBuilderAdapter(
            $this->attributeRepository,
            $this->filterRegistry,
            $this->sorterRegistry,
            $this->optionResolver,
            $this->featureFlags,
            $this->userRepository,
            null
        );
        $this->optionResolver->method('resolve')->with(['locale' => null, 'scope'  => null])->willReturn(['locale' => null, 'scope'  => null]);
        $this->featureFlags->method('isEnabled')->with('permission')->willReturn(false);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ProductQueryBuilderAdapter::class, $this->sut);
        $this->assertInstanceOf(ProductQueryBuilderInterface::class, $this->sut);
    }

    public function test_it_builds_the_query(): void
    {
        $fieldFilter = $this->createMock(FieldFilterInterface::class);

        $this->filterRegistry->expects($this->once())->method('getFieldFilter')->with('entity_type', Operators::EQUALS)->willReturn($fieldFilter);
        $this->assertSame(['_source' => ['id', 'identifier', 'document_type']], $this->sut->buildQuery(null));
    }

    public function test_it_builds_the_query_with_a_user(): void
    {
        $fieldFilter = $this->createMock(FieldFilterInterface::class);

        $this->filterRegistry->expects($this->once())->method('getFieldFilter')->with('entity_type', Operators::EQUALS)->willReturn($fieldFilter);
        $this->assertSame(['_source' => ['id', 'identifier', 'document_type']], $this->sut->buildQuery(1));
    }

    public function test_it_builds_the_query_with_a_search_after(): void
    {
        $fieldFilter = $this->createMock(FieldFilterInterface::class);

        $this->filterRegistry->expects($this->once())->method('getFieldFilter')->with('entity_type', Operators::EQUALS)->willReturn($fieldFilter);
        $uuid = Uuid::uuid4();
        $this->assertSame(['_source' => ['id', 'identifier', 'document_type'], 'search_after' => ['product_' . $uuid->toString()]], $this->sut->buildQuery(null, $uuid));
    }

    public function test_it_adds_permission_filters_and_builds_the_query(): void
    {
        $fieldFilter1 = $this->createMock(FieldFilterInterface::class);
        $fieldFilter2 = $this->createMock(FieldFilterInterface::class);
        $user = $this->createMock(UserInterface::class);

        FeatureHelper::skipSpecTestWhenPermissionFeatureIsNotActivated();
        $getGrantedCategoryCodes->beADoubleOf(GetGrantedCategoryCodes::class);
        $this->sut = new ProductQueryBuilderAdapter(
            $this->attributeRepository,
            $this->filterRegistry,
            $this->sorterRegistry,
            $this->optionResolver,
            $this->featureFlags,
            $this->userRepository,
            $getGrantedCategoryCodes
        );
        $this->optionResolver->method('resolve')->with(['locale' => null, 'scope'  => null])->willReturn(['locale' => null, 'scope'  => null]);
        $this->featureFlags->method('isEnabled')->with('permission')->willReturn(true);
        $this->userRepository->method('findOneBy')->with(['id' => 1])->willReturn($user);
        $user->method('getGroupsIds')->willReturn([100, 200, 300]);
        $getGrantedCategoryCodes->forGroupIds([100, 200, 300])->willReturn(['print', 'suppliers']);
        $this->filterRegistry->expects($this->once())->method('getFieldFilter')->with('entity_type', Operators::EQUALS)->willReturn($fieldFilter1);
        $this->filterRegistry->expects($this->once())->method('getFieldFilter')->with('categories', Operators::IN_LIST_OR_UNCLASSIFIED)->willReturn($fieldFilter2);
        $this->assertSame(['_source' => ['id', 'identifier', 'document_type']], $this->sut->buildQuery(1));
    }
}

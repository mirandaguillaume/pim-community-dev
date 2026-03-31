<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Oro\Bundle\PimDataGridBundle\Normalizer\InternalApi;

use Akeneo\UserManagement\Component\Model\UserInterface;
use Oro\Bundle\PimDataGridBundle\Entity\DatagridView;
use Oro\Bundle\PimDataGridBundle\Normalizer\InternalApi\DatagridViewNormalizer;
use PHPUnit\Framework\TestCase;

class DatagridViewNormalizerTest extends TestCase
{
    private DatagridViewNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new DatagridViewNormalizer();
    }

    public function test_it_is_a_normalizer(): void
    {
        $this->assertInstanceOf(\Symfony\Component\Serializer\Normalizer\NormalizerInterface::class, $this->sut);
    }

    public function test_it_supports_json_format(): void
    {
        $view = $this->createMock(DatagridView::class);

        $this->assertSame(true, $this->sut->supportsNormalization($view, 'internal_api'));
        $this->assertSame(false, $this->sut->supportsNormalization($view, 'structured'));
    }

    public function test_it_supports_datagrid_view(): void
    {
        $view = $this->createMock(DatagridView::class);

        $this->assertSame(true, $this->sut->supportsNormalization($view, 'internal_api'));
        $this->assertSame(false, $this->sut->supportsNormalization(new \stdClass(), 'internal_api'));
    }

    public function test_it_normalizes_a_datagrid_view(): void
    {
        $view = $this->createMock(DatagridView::class);
        $user = $this->createMock(UserInterface::class);

        $user->method('getId')->willReturn(666);
        $view->method('getId')->willReturn(42);
        $view->method('getOwner')->willReturn($user);
        $view->method('getLabel')->willReturn('Cameras');
        $view->method('getType')->willReturn('public');
        $view->method('getDatagridAlias')->willReturn('product-grid');
        $view->method('getColumns')->willReturn(['sku', 'name', 'brand']);
        $view->method('getFilters')->willReturn('i=1&p=10&s%5Bupdated%5D=1&f%5Bfamily%5D%5Bvalue%5D%5B%5D=mugs');
        $view->method('getOrder')->willReturn('sku,name,brand');
        $result = $this->sut->normalize($view, 'standard');
        $this->assertSame([
                    'id'             => 42,
                    'owner_id'       => 666,
                    'label'          => 'Cameras',
                    'type'           => 'public',
                    'datagrid_alias' => 'product-grid',
                    'columns'        => ['sku', 'name', 'brand'],
                    'filters'        => 'i=1&p=10&s%5Bupdated%5D=1&f%5Bfamily%5D%5Bvalue%5D%5B%5D=mugs',
                ], $result);
        // Verify types explicitly
        $this->assertIsInt($result['id']);
        $this->assertIsInt($result['owner_id']);
        $this->assertIsString($result['label']);
        $this->assertIsString($result['type']);
        $this->assertIsString($result['datagrid_alias']);
        $this->assertIsArray($result['columns']);
        $this->assertIsString($result['filters']);
    }
}

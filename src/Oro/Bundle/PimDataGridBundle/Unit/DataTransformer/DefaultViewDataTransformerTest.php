<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Oro\Bundle\PimDataGridBundle\DataTransformer;

use Akeneo\UserManagement\Component\Model\UserInterface;
use Oro\Bundle\PimDataGridBundle\DataTransformer\DefaultViewDataTransformer;
use Oro\Bundle\PimDataGridBundle\Entity\DatagridView;
use Oro\Bundle\PimDataGridBundle\Repository\DatagridViewRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DefaultViewDataTransformerTest extends TestCase
{
    private DatagridViewRepositoryInterface|MockObject $datagridViewRepo;
    private DefaultViewDataTransformer $sut;

    protected function setUp(): void
    {
        $this->datagridViewRepo = $this->createMock(DatagridViewRepositoryInterface::class);
        $this->sut = new DefaultViewDataTransformer($this->datagridViewRepo);
    }

    public function test_it_transforms_the_given_user(): void
    {
        $julia = $this->createMock(UserInterface::class);
        $productView = $this->createMock(DatagridView::class);

        $this->datagridViewRepo->method('getDatagridViewAliasesByUser')->with($julia)->willReturn(['product-grid', 'category']);
        $julia->method('getDefaultGridView')->willReturnMap([
            ['product-grid', $productView],
            ['category', null],
        ]);
        $this->assertSame($julia, $this->sut->transform($julia));
    }
}

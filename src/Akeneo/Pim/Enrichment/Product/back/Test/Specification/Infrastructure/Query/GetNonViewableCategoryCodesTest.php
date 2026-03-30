<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\Infrastructure\Query;

use Akeneo\Pim\Enrichment\Category\API\Query\GetViewableCategories;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetCategoryCodes;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetNonViewableCategoryCodes as GetNonViewableCategoryCodesInterface;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Query\GetNonViewableCategoryCodes;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class GetNonViewableCategoryCodesTest extends TestCase
{
    private GetCategoryCodes|MockObject $getCategoryCodes;
    private GetViewableCategories|MockObject $getViewableCategories;
    private GetNonViewableCategoryCodes $sut;

    protected function setUp(): void
    {
        $this->getCategoryCodes = $this->createMock(GetCategoryCodes::class);
        $this->getViewableCategories = $this->createMock(GetViewableCategories::class);
        $this->sut = new GetNonViewableCategoryCodes($this->getCategoryCodes, $this->getViewableCategories);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(GetNonViewableCategoryCodes::class, $this->sut);
        $this->assertInstanceOf(GetNonViewableCategoryCodesInterface::class, $this->sut);
    }

    public function test_it_returns_non_viewable_category_codes_for_a_list_of_product_identifiers(): void
    {
        $productUuid1 = Uuid::uuid4();
        $productUuid2 = Uuid::uuid4();
        $productUuid3 = Uuid::uuid4();
        $this->getCategoryCodes->method('fromProductUuids')->with([$productUuid1, $productUuid2, $productUuid3])->willReturn([
                        $productUuid1->toString() => ['categoryA', 'categoryB', 'categoryC'],
                        $productUuid2->toString() => ['categoryA', 'categoryD', 'categoryE'],
                    ]);
        $this->getViewableCategories->method('forUserId')->with(['categoryA', 'categoryB', 'categoryC', 'categoryD', 'categoryE'], 10)->willReturn(['categoryA', 'categoryB', 'categoryC', 'categoryD']);
        $this->sut->fromProductUuids([$productUuid1, $productUuid2, $productUuid3], 10)
                    ->shouldreturn([
                        $productUuid1->toString() => [],
                        $productUuid2->toString() => ['categoryE'],
                    ]);
    }
}

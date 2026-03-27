<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Application\Filter;

use Akeneo\Category\Application\Filter\CategoryEditAclFilter;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CategoryEditAclFilterTest extends TestCase
{
    private SecurityFacade|MockObject $securityFacade;
    private CategoryEditAclFilter $sut;

    protected function setUp(): void
    {
        $this->securityFacade = $this->createMock(SecurityFacade::class);
        $this->sut = new CategoryEditAclFilter($this->securityFacade);
    }

    public function testItFiltersAttributesDataWhenNotGranted(): void
    {
        $this->securityFacade->method('isGranted')->with('pim_enrich_product_category_edit_attributes')->willReturn(false);
        $this->assertSame([
            'code' => 'a_code',
            'labels' => [
                'en_US' => 'A code',
            ],
        ], $this->sut->filterCollection($this->getData()));
    }

    public function testItDoesNotFiltersAttributesDataWhenGranted(): void
    {
        $data = $this->getData();
        $this->securityFacade->method('isGranted')->with('pim_enrich_product_category_edit_attributes')->willReturn(true);
        $this->assertSame($data, $this->sut->filterCollection($data));
    }

    private function getData(): array
    {
        return [
            'code' => 'a_code',
            'labels' => [
                'en_US' => 'A code',
            ],
            'values' => [
                'text_value|uuid|en_US' => [
                    'data' => 'a text value',
                    'locale' => 'en_US',
                    'attribute_code' => 'text_value|uuid',
                ],
            ],
        ];
    }
}

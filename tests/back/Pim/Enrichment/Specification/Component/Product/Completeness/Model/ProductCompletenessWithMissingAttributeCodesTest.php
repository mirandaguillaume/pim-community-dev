<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Completeness\Model;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodes;
use PHPUnit\Framework\TestCase;

class ProductCompletenessWithMissingAttributeCodesTest extends TestCase
{
    private ProductCompletenessWithMissingAttributeCodes $sut;

    protected function setUp(): void
    {
    }

    public function test_it_returns_the_count_of_missing_attributes(): void
    {
        $this->sut = new ProductCompletenessWithMissingAttributeCodes('ecommerce',
                    'fr_FR',
                    30,
                    ['name', 'brand', 'description', 'picture']);
        $this->assertSame(4, $this->sut->missingAttributesCount());
    }
}

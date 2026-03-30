<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\API\Command\UserIntent;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\CategoryUserIntent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\RemoveCategories;
use PHPUnit\Framework\TestCase;

class RemoveCategoriesTest extends TestCase
{
    private RemoveCategories $sut;

    protected function setUp(): void
    {
        $this->sut = new RemoveCategories(['categoryA', 'categoryB']);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(RemoveCategories::class, $this->sut);
        $this->assertInstanceOf(CategoryUserIntent::class, $this->sut);
        $this->assertSame(['categoryA', 'categoryB'], $this->sut->categoryCodes());
    }

    public function test_it_requires_non_empty_array(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new RemoveCategories([]);
    }

    public function test_it_requires_non_empty_values_in_array(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new RemoveCategories(['']);
    }

    public function test_it_requires_string_values_in_the_array(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new RemoveCategories(['test', 42]);
    }
}

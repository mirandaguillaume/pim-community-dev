<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\API\Command\UserIntent;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ChangeParent;
use PHPUnit\Framework\TestCase;

class ChangeParentTest extends TestCase
{
    private ChangeParent $sut;

    protected function setUp(): void
    {
    }

    public function test_it_can_be_constructed_with_parent_code(): void
    {
        $this->sut = new ChangeParent('test_product_model');
        $this->assertTrue(is_a(ChangeParent::class, ChangeParent::class, true));
        $this->assertSame('test_product_model', $this->sut->parentCode());
    }

    public function test_it_throws_an_exception_if_parent_code_is_an_empty_string(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ChangeParent('');
    }
}

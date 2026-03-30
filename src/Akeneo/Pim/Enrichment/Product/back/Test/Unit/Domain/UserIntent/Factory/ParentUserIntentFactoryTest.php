<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\Domain\UserIntent\Factory;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ChangeParent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ConvertToSimpleProduct;
use Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\ParentUserIntentFactory;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PHPUnit\Framework\TestCase;

class ParentUserIntentFactoryTest extends TestCase
{
    private ParentUserIntentFactory $sut;

    protected function setUp(): void
    {
        $this->sut = new ParentUserIntentFactory();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ParentUserIntentFactory::class, $this->sut);
    }

    public function test_it_returns_change_parent(): void
    {
        $this->assertEquals([new ChangeParent('new_parent')], $this->sut->create('parent', 'new_parent'));
    }

    public function test_it_returns_convert_to_simple_product(): void
    {
        $this->assertEquals([new ConvertToSimpleProduct()], $this->sut->create('parent', null));
        $this->assertEquals([new ConvertToSimpleProduct()], $this->sut->create('parent', ''));
    }

    public function test_it_throws_an_exception_if_data_is_not_valid(): void
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->create('parent', 12);
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\Domain\UserIntent\Factory;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ValueUserIntent;
use Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\CategoriesUserIntentFactory;
use Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\UserIntentFactory;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PHPUnit\Framework\TestCase;

class CategoriesUserIntentFactoryTest extends TestCase
{
    private CategoriesUserIntentFactory $sut;

    protected function setUp(): void
    {
        $this->sut = new CategoriesUserIntentFactory();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(CategoriesUserIntentFactory::class, $this->sut);
        $this->assertInstanceOf(UserIntentFactory::class, $this->sut);
    }

    public function test_it_returns_category_user_intent(): void
    {
        $this->assertEquals([new SetCategories(['categoryA', 'categoryA'])], $this->sut->create('categories', ['categoryA', 'categoryA']));
    }

    public function test_it_returns_empty_set_categories_user_intent(): void
    {
        $this->assertEquals([new SetCategories([])], $this->sut->create('categories', []));
    }

    public function test_it_throws_an_exception_if_data_is_not_valid(): void
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->create('categories', 'categoryA');
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->create('categories', null);
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Infrastructure\Registry;

use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Infrastructure\Registry\FindCategoryAdditionalPropertiesRegistry;
use Akeneo\Category\ServiceApi\Handler\CategoryAdditionalPropertiesFinder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FindCategoryAdditionalPropertiesRegistryTest extends TestCase
{
    private CategoryAdditionalPropertiesFinder|MockObject $unsupportedFinder;
    private CategoryAdditionalPropertiesFinder|MockObject $supportedFinder;
    private FindCategoryAdditionalPropertiesRegistry $sut;

    protected function setUp(): void
    {
        $this->unsupportedFinder = $this->createMock(CategoryAdditionalPropertiesFinder::class);
        $this->supportedFinder = $this->createMock(CategoryAdditionalPropertiesFinder::class);
        $this->sut = new FindCategoryAdditionalPropertiesRegistry([
            $this->supportedFinder,
            $this->unsupportedFinder,
        ]);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(FindCategoryAdditionalPropertiesRegistry::class, $this->sut);
    }

    public function test_it_executes_only_supported_finder_for_category(): void
    {
        $category = $this->createMock(Category::class);

        $this->unsupportedFinder->method('isSupportedAdditionalProperties')->willReturn(false);
        $this->unsupportedFinder->expects($this->never())->method('execute');
        $this->supportedFinder->method('isSupportedAdditionalProperties')->willReturn(true);
        $this->supportedFinder->expects($this->once())->method('execute')->with($category);
        $this->sut->forCategory($category);
    }

    public function test_it_executes_only_supported_finder_for_categories(): void
    {
        $category = $this->createMock(Category::class);

        $this->unsupportedFinder->method('isSupportedAdditionalProperties')->willReturn(false);
        $this->unsupportedFinder->expects($this->never())->method('execute');
        $this->supportedFinder->method('isSupportedAdditionalProperties')->willReturn(true);
        $this->supportedFinder->expects($this->once())->method('execute')->with($category);
        $this->sut->forCategories([$category]);
    }
}

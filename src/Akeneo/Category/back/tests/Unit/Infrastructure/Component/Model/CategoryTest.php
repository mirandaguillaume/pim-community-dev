<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Infrastructure\Component\Model;

use Akeneo\Category\Infrastructure\Component\Model\Category;
use Akeneo\Category\Infrastructure\Component\Model\CategoryTranslation;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryTest extends TestCase
{
    private Category $sut;

    protected function setUp(): void
    {
        $this->sut = new Category();
    }

    public function testItIsInitializable(): void
    {
        $this->assertInstanceOf(Category::class, $this->sut);
    }

    public function testItGetsATranslationEvenIfTheLocaleCaseIsWrong(): void
    {
        $translationEn = $this->createMock(CategoryTranslation::class);

        $translationEn->method('getLocale')->willReturn('EN_US');
        $this->sut->addTranslation($translationEn);
        $this->assertSame($translationEn, $this->sut->getTranslation('en_US'));
    }
}

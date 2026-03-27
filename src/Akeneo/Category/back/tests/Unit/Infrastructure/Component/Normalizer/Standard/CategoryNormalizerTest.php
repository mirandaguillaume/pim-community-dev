<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Infrastructure\Component\Normalizer\Standard;

use Akeneo\Category\Infrastructure\Component\Model\CategoryInterface;
use Akeneo\Category\Infrastructure\Component\Normalizer\Standard\CategoryNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\DateTimeNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\TranslationNormalizer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CategoryNormalizerTest extends TestCase
{
    private TranslationNormalizer|MockObject $translationNormalizer;
    private DateTimeNormalizer|MockObject $dateTimeNormalizer;
    private CategoryNormalizer $sut;

    protected function setUp(): void
    {
        $this->translationNormalizer = $this->createMock(TranslationNormalizer::class);
        $this->dateTimeNormalizer = $this->createMock(DateTimeNormalizer::class);
        $this->sut = new CategoryNormalizer($this->translationNormalizer, $this->dateTimeNormalizer);
    }

    public function testItIsInitializable(): void
    {
        $this->assertInstanceOf(CategoryNormalizer::class, $this->sut);
    }

    public function testItIsANormalizer(): void
    {
        $this->assertInstanceOf(NormalizerInterface::class, $this->sut);
    }

    public function testItSupportsStandardNormalization(): void
    {
        $category = $this->createMock(CategoryInterface::class);

        $this->assertSame(true, $this->sut->supportsNormalization($category, 'standard'));
        $this->assertSame(false, $this->sut->supportsNormalization(new \stdClass(), 'standard'));
        $this->assertSame(false, $this->sut->supportsNormalization($category, 'xml'));
        $this->assertSame(false, $this->sut->supportsNormalization($category, 'json'));
    }

    public function testItNormalizesCategory(): void
    {
        $category = $this->createMock(CategoryInterface::class);

        $updated = new \DateTime('2016-06-14T13:12:50');
        $category->method('getCode')->willReturn('my_code');
        $category->method('getParent')->willReturn(null);
        $category->method('getUpdated')->willReturn($updated);
        $this->translationNormalizer->method('normalize')->with($category, 'standard', [])->willReturn([]);
        $this->dateTimeNormalizer->method('normalize')->with($updated, null)->willReturn('2016-06-14T13:12:50+01:00');
        $this->assertSame([
            'code' => 'my_code',
            'parent' => null,
            'updated' => '2016-06-14T13:12:50+01:00',
            'labels' => [],
        ], $this->sut->normalize($category));
    }

    public function testItNormalizesCategoryWithParent(): void
    {
        $category = $this->createMock(CategoryInterface::class);
        $parent = $this->createMock(CategoryInterface::class);

        $updated = new \DateTime('2016-06-14T13:12:50');
        $category->method('getCode')->willReturn('my_code');
        $category->method('getParent')->willReturn($parent);
        $parent->method('getCode')->willReturn('my_parent');
        $category->method('getUpdated')->willReturn($updated);
        $this->translationNormalizer->method('normalize')->with($category, 'standard', [])->willReturn([]);
        $this->dateTimeNormalizer->method('normalize')->with($updated, null)->willReturn('2016-06-14T13:12:50+01:00');
        $this->assertSame([
            'code' => 'my_code',
            'parent' => 'my_parent',
            'updated' => '2016-06-14T13:12:50+01:00',
            'labels' => [],
        ], $this->sut->normalize($category));
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Infrastructure\Component\Normalizer\Versioning;

use Akeneo\Category\Infrastructure\Component\Classification\Model\CategoryInterface;
use Akeneo\Category\Infrastructure\Component\Normalizer\Versioning\CategoryNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning\TranslationNormalizer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CategoryNormalizerTest extends TestCase
{
    private CategoryNormalizer|MockObject $categoryNormalizerStandard;
    private TranslationNormalizer|MockObject $translationNormalizer;
    private CategoryNormalizer $sut;

    protected function setUp(): void
    {
        $this->categoryNormalizerStandard = $this->createMock(CategoryNormalizer::class);
        $this->translationNormalizer = $this->createMock(TranslationNormalizer::class);
        $this->sut = new CategoryNormalizer($this->categoryNormalizerStandard, $this->translationNormalizer);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(CategoryNormalizer::class, $this->sut);
    }

    public function test_it_is_a_normalizer(): void
    {
        $this->assertInstanceOf(NormalizerInterface::class, $this->sut);
    }

    public function test_it_supports_category_normalization_into_flat(): void
    {
        $clothes = $this->createMock(CategoryInterface::class);

        $this->assertSame(false, $this->sut->supportsNormalization($clothes, 'flat'));
        $this->assertSame(false, $this->sut->supportsNormalization($clothes, 'csv'));
        $this->assertSame(false, $this->sut->supportsNormalization($clothes, 'json'));
        $this->assertSame(false, $this->sut->supportsNormalization($clothes, 'xml'));
    }

    public function test_it_normalizes_category(): void
    {
        $clothes = $this->createMock(CategoryInterface::class);

        $this->translationNormalizer->method('supportsNormalization')->willReturn(true);
        $this->translationNormalizer->method('normalize')->willReturn([
            'label-en_US' => 'My category',
        ]);
        $this->categoryNormalizerStandard->method('supportsNormalization')->with($clothes, 'standard')->willReturn(true);
        $this->categoryNormalizerStandard->method('normalize')->with($clothes, 'standard', [])->willReturn([
            'code'   => 'clothes',
            'parent' => 'Master catalog',
            'labels' => [
                'en_US' => 'My category',
            ],
        ]);
        $this->assertSame([
            'code'        => 'clothes',
            'parent'      => 'Master catalog',
            'label-en_US' => 'My category',
        ], $this->sut->normalize($clothes));
    }
}

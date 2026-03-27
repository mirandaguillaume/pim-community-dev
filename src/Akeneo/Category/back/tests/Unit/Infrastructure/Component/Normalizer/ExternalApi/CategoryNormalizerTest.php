<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Infrastructure\Component\Normalizer\ExternalApi;

use Akeneo\Category\Infrastructure\Component\Classification\Model\CategoryInterface;
use Akeneo\Category\Infrastructure\Component\Manager\PositionResolverInterface;
use Akeneo\Category\Infrastructure\Component\Normalizer\ExternalApi\CategoryNormalizer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CategoryNormalizerTest extends TestCase
{
    private NormalizerInterface|MockObject $stdNormalizer;
    private PositionResolverInterface|MockObject $positionResolver;
    private CategoryNormalizer $sut;

    protected function setUp(): void
    {
        $this->stdNormalizer = $this->createMock(NormalizerInterface::class);
        $this->positionResolver = $this->createMock(PositionResolverInterface::class);
        $this->sut = new CategoryNormalizer($this->stdNormalizer, $this->positionResolver);
    }

    public function testItIsInitializable(): void
    {
        $this->assertInstanceOf(CategoryNormalizer::class, $this->sut);
    }

    public function testItSupportsACategory(): void
    {
        $category = $this->createMock(CategoryInterface::class);

        $this->assertSame(false, $this->sut->supportsNormalization(new \stdClass(), 'whatever'));
        $this->assertSame(false, $this->sut->supportsNormalization(new \stdClass(), 'external_api'));
        $this->assertSame(false, $this->sut->supportsNormalization($category, 'whatever'));
        $this->assertSame(true, $this->sut->supportsNormalization($category, 'external_api'));
    }

    public function testItNormalizesACategory(): void
    {
        $category = $this->createMock(CategoryInterface::class);

        $data = ['code' => 'my_category', 'labels' => []];
        $this->stdNormalizer->method('normalize')->with($category, 'standard', [])->willReturn($data);
        $normalizedCategory = $this->sut->normalize($category, 'external_api', []);
        $this->assertArrayHasKey('labels', $normalizedCategory);
    }

    public function testItNormalizesACategoryWithPosition(): void
    {
        $category = $this->createMock(CategoryInterface::class);

        $aPosition = 1;
        $context = ['with_position'];
        $data = ['code' => 'my_category', 'labels' => []];
        $this->stdNormalizer->method('normalize')->with($category, 'standard', $context)->willReturn($data);
        $this->positionResolver->method('getPosition')->with($category)->willReturn($aPosition);
        $normalizedCategory = $this->sut->normalize($category, 'external_api', $context);
        $this->assertArrayHasKey('labels', $normalizedCategory);
        $this->assertArrayHasKey('position', $normalizedCategory);
        $this->assertSame($aPosition, $normalizedCategory['position']);
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Normalizer\InternalApi;

use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Akeneo\Pim\Structure\Component\Normalizer\InternalApi\AssociationTypeNormalizer;
use Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AssociationTypeNormalizerTest extends TestCase
{
    private NormalizerInterface|MockObject $normalizer;
    private VersionManager|MockObject $versionManager;
    private NormalizerInterface|MockObject $versionNormalizer;
    private AssociationTypeNormalizer $sut;

    protected function setUp(): void
    {
        $this->normalizer = $this->createMock(NormalizerInterface::class);
        $this->versionManager = $this->createMock(VersionManager::class);
        $this->versionNormalizer = $this->createMock(NormalizerInterface::class);
        $this->sut = new AssociationTypeNormalizer(
            $this->normalizer,
            $this->versionManager,
            $this->versionNormalizer,
        );
    }

    public function test_it_adds_the_attribute_id_to_the_normalized_association_type(): void
    {
        $associationType = $this->createMock(AssociationTypeInterface::class);

        $this->normalizer->method('normalize')->with($associationType, 'standard', [])->willReturn(['code' => 'variant']);
        $associationType->method('getId')->willReturn(12);
        $this->versionManager->method('getOldestLogEntry')->willReturn(null);
        $this->versionManager->method('getNewestLogEntry')->willReturn(null);
        $this->assertSame([
                            'code' => 'variant',
                            'meta' => [
                                'id' => 12,
                                'form' => "pim-association-type-edit-form",
                                'model_type' => "association_type",
                                'created' => null,
                                'updated' => null,
                            ],
                        ], $this->sut->normalize($associationType, 'internal_api', []));
    }

    public function test_it_supports_association_types_and_internal_api(): void
    {
        $associationType = $this->createMock(AssociationTypeInterface::class);

        $this->assertSame(true, $this->sut->supportsNormalization($associationType, 'internal_api'));
        $this->assertSame(false, $this->sut->supportsNormalization($associationType, 'json'));
        $this->assertSame(false, $this->sut->supportsNormalization(new \stdClass(), 'internal_api'));
    }
}

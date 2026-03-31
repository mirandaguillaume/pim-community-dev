<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Oro\Bundle\PimDataGridBundle\Normalizer;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Structure\Component\Model\VariantAttributeSetInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\PimDataGridBundle\Normalizer\FamilyVariantNormalizer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class FamilyVariantNormalizerTest extends TestCase
{
    private NormalizerInterface|MockObject $translationNormalizer;
    private FamilyVariantNormalizer $sut;

    protected function setUp(): void
    {
        $this->translationNormalizer = $this->createMock(NormalizerInterface::class);
        $this->sut = new FamilyVariantNormalizer($this->translationNormalizer);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(FamilyVariantNormalizer::class, $this->sut);
    }

    public function test_it_is_a_normalizer(): void
    {
        $this->assertInstanceOf(NormalizerInterface::class, $this->sut);
    }

    public function test_it_supports_datagrid_format_and_family_variant(): void
    {
        $familyVariant = $this->createMock(FamilyVariantInterface::class);

        $this->assertSame(true, $this->sut->supportsNormalization($familyVariant, 'datagrid'));
        $this->assertSame(false, $this->sut->supportsNormalization($familyVariant, 'other_format'));
        $this->assertSame(false, $this->sut->supportsNormalization(new \stdClass(), 'other_format'));
        $this->assertSame(false, $this->sut->supportsNormalization(new \stdClass(), 'datagrid'));
    }

    public function test_it_normalizes_a_family_variant(): void
    {
        $familyVariant = $this->createMock(FamilyVariantInterface::class);
        $shoe = $this->createMock(FamilyInterface::class);

        $this->translationNormalizer->method('normalize')->with($familyVariant, 'standard', [])->willReturn([
                    'en_US' => 'Size',
                ]);
        $familyVariant->method('getId')->willReturn(12);
        $familyVariant->method('getCode')->willReturn('shoes_by_size');
        $familyVariant->method('getFamily')->willReturn($shoe);
        $shoe->method('getCode')->willReturn('shoe');
        $familyVariant->method('getVariantAttributeSets')->willReturn(new ArrayCollection());
        $this->assertSame([
                    'id'                => 12,
                    'familyCode'        => 'shoe',
                    'familyVariantCode' => 'shoes_by_size',
                    'label'             => 'shoes_by_size',
                    'level_1'           => '',
                    'level_2'           => '',
                ], $this->sut->normalize($familyVariant, 'datagrid'));
    }

    public function test_it_normalizes_variant_attribute_set_axes(): void
    {
        $familyVariant = $this->createMock(FamilyVariantInterface::class);
        $shoe = $this->createMock(FamilyInterface::class);
        $attrSet1 = $this->createMock(VariantAttributeSetInterface::class);
        $axis1 = $this->createMock(AttributeInterface::class);

        $this->translationNormalizer->method('normalize')->willReturn(['en_US' => 'Size']);
        $familyVariant->method('getId')->willReturn(12);
        $familyVariant->method('getCode')->willReturn('shoes_by_size');
        $familyVariant->method('getFamily')->willReturn($shoe);
        $shoe->method('getCode')->willReturn('shoe');

        $attrSet1->method('getLevel')->willReturn(1);
        $attrSet1->method('getAxes')->willReturn(new ArrayCollection([$axis1]));
        $axis1->method('getLabel')->willReturn('Size');

        $familyVariant->method('getVariantAttributeSets')->willReturn(new ArrayCollection([$attrSet1]));

        $result = $this->sut->normalize($familyVariant, 'datagrid');
        $this->assertSame('Size', $result['level_1']);
        $this->assertSame('', $result['level_2']);
    }
}

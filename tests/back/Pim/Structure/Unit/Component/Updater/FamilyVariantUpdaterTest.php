<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Updater;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Structure\Component\Updater\FamilyVariantUpdater;
use Akeneo\Tool\Component\Localization\TranslatableUpdater;
use Akeneo\Tool\Component\StorageUtils\Exception\ImmutablePropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyVariantUpdaterTest extends TestCase
{
    private TranslatableUpdater|MockObject $translationUpdater;
    private IdentifiableObjectRepositoryInterface|MockObject $familyRepository;
    private IdentifiableObjectRepositoryInterface|MockObject $attributeRepository;
    private FamilyVariantUpdater $sut;

    protected function setUp(): void
    {
        $this->translationUpdater = $this->createMock(TranslatableUpdater::class);
        $this->familyRepository = $this->createMock(IdentifiableObjectRepositoryInterface::class);
        $this->attributeRepository = $this->createMock(IdentifiableObjectRepositoryInterface::class);
        $this->sut = new FamilyVariantUpdater(
            $this->createMock(SimpleFactoryInterface::class),
            $this->translationUpdater,
            $this->familyRepository,
            $this->attributeRepository,
        );
    }

    public function test_it_is_an_object_updater(): void
    {
        $this->assertInstanceOf(ObjectUpdaterInterface::class, $this->sut);
    }

    public function test_it_rejects_an_object_that_is_not_a_family_variant(): void
    {
        $this->expectException(InvalidObjectException::class);

        $this->sut->update(new \stdClass(), []);
    }

    public function test_it_rejects_an_unknown_field(): void
    {
        $familyVariant = $this->createMock(FamilyVariantInterface::class);

        $this->expectException(UnknownPropertyException::class);

        $this->sut->update($familyVariant, ['not_a_field' => 'value']);
    }

    public function test_it_sets_the_code(): void
    {
        $familyVariant = $this->createMock(FamilyVariantInterface::class);
        $familyVariant->expects($this->once())->method('setCode')->with('a_variant');

        $this->sut->update($familyVariant, ['code' => 'a_variant']);
    }

    public function test_it_rejects_a_non_string_code(): void
    {
        $familyVariant = $this->createMock(FamilyVariantInterface::class);

        $this->expectException(InvalidPropertyTypeException::class);

        $this->sut->update($familyVariant, ['code' => 42]);
    }

    public function test_it_delegates_labels_to_the_translation_updater(): void
    {
        $familyVariant = $this->createMock(FamilyVariantInterface::class);
        $labels = ['en_US' => 'A variant'];
        $this->translationUpdater->expects($this->once())->method('update')->with($familyVariant, $labels);

        $this->sut->update($familyVariant, ['labels' => $labels]);
    }

    public function test_it_rejects_a_family_that_does_not_exist(): void
    {
        $familyVariant = $this->createMock(FamilyVariantInterface::class);
        $this->familyRepository->method('findOneByIdentifier')->with('unknown_family')->willReturn(null);

        $this->expectException(InvalidPropertyException::class);

        $this->sut->update($familyVariant, ['family' => 'unknown_family']);
    }

    public function test_it_sets_the_family_when_it_exists(): void
    {
        $familyVariant = $this->createMock(FamilyVariantInterface::class);
        $family = $this->createMock(FamilyInterface::class);
        $this->familyRepository->method('findOneByIdentifier')->with('shoes')->willReturn($family);
        $familyVariant->expects($this->once())->method('setFamily')->with($family);

        $this->sut->update($familyVariant, ['family' => 'shoes']);
    }

    public function test_it_updates_the_axes_and_attributes_for_a_new_family_variant(): void
    {
        $familyVariant = $this->createMock(FamilyVariantInterface::class);
        $familyVariant->method('getId')->willReturn(null);
        $axisAttribute = $this->createMock(AttributeInterface::class);
        $valueAttribute = $this->createMock(AttributeInterface::class);
        $this->attributeRepository->method('findOneByIdentifier')->willReturnMap([
            ['size', $axisAttribute],
            ['color', $valueAttribute],
        ]);
        $familyVariant->expects($this->once())->method('updateAxesForLevel')->with(1, [$axisAttribute]);
        $familyVariant->expects($this->once())->method('updateAttributesForLevel')->with(1, [$valueAttribute]);

        $this->sut->update($familyVariant, [
            'variant_attribute_sets' => [
                ['level' => 1, 'axes' => ['size'], 'attributes' => ['color']],
            ],
        ]);
    }

    public function test_it_refuses_to_reduce_the_number_of_levels_of_an_existing_family_variant(): void
    {
        $familyVariant = $this->createMock(FamilyVariantInterface::class);
        $familyVariant->method('getId')->willReturn(42);
        $familyVariant->method('getNumberOfLevel')->willReturn(1);

        $this->expectException(ImmutablePropertyException::class);

        $this->sut->update($familyVariant, [
            'variant_attribute_sets' => [
                ['level' => 1], ['level' => 2],
            ],
        ]);
    }
}

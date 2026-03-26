<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Unit\Application\Generate\Property;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception\UnableToTruncateException;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception\UndefinedNomenclatureException;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Generate\Property\GenerateFamilyHandler;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Generate\Property\PropertyProcessApplier;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Conditions;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Delimiter;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGenerator;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorCode;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorId;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\LabelCollection;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\NomenclatureDefinition;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\ProductProjection;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\AutoNumber;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FamilyProperty;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\Process;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\PropertyInterface;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Structure;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Target;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\TextTransformation;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\FamilyNomenclatureRepository;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\ReferenceEntityNomenclatureRepository;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\SimpleSelectNomenclatureRepository;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GenerateFamilyHandlerTest extends TestCase
{
    private FamilyNomenclatureRepository|MockObject $familyNomenclatureRepository;
    private SimpleSelectNomenclatureRepository|MockObject $simpleSelectNomenclatureRepository;
    private GetAttributes|MockObject $getAttributes;
    private ReferenceEntityNomenclatureRepository|MockObject $referenceEntityNomenclatureRepository;
    private GenerateFamilyHandler $sut;

    protected function setUp(): void
    {
        $this->familyNomenclatureRepository = $this->createMock(FamilyNomenclatureRepository::class);
        $this->simpleSelectNomenclatureRepository = $this->createMock(SimpleSelectNomenclatureRepository::class);
        $this->getAttributes = $this->createMock(GetAttributes::class);
        $this->referenceEntityNomenclatureRepository = $this->createMock(ReferenceEntityNomenclatureRepository::class);
        $this->sut = new GenerateFamilyHandler(new PropertyProcessApplier(
            $this->familyNomenclatureRepository,
            $this->simpleSelectNomenclatureRepository,
            $this->getAttributes,
            $this->referenceEntityNomenclatureRepository,
        ));
    }

    public function test_it_should_support_only_family_property(): void
    {
        $this->assertSame(FamilyProperty::class, $this->sut->getPropertyClass());
    }

    public function test_it_should_throw_exception_when_invoked_with_something_else_than_family_property(): void
    {
        $autoNumber = AutoNumber::fromNormalized([
            'type' => AutoNumber::type(),
            'numberMin' => 0,
            'digitsMin' => 1,
        ]);
        $identifierGenerator = $this->getIdentifierGenerator($autoNumber);
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->__invoke(
            $autoNumber,
            $identifierGenerator,
            new ProductProjection(true, null, [], []),
            'AKN-',
        );
    }

    public function test_it_should_return_family_code_without_truncate(): void
    {
        $family = FamilyProperty::fromNormalized([
            'type' => FamilyProperty::type(),
            'process' => [
                'type' => 'no',
            ],
        ]);
        $identifierGenerator = $this->getIdentifierGenerator($family);
        $this->assertSame('familyCode', $this->sut->__invoke(
            $family,
            $identifierGenerator,
            $this->getProductProjection('familyCode'),
            'AKN-'
        ));
    }

    public function test_it_should_return_family_code_with_truncate(): void
    {
        $family = FamilyProperty::fromNormalized([
            'type' => FamilyProperty::type(),
            'process' => [
                'type' => 'truncate',
                'operator' => Process::PROCESS_OPERATOR_LTE,
                'value' => 3,
            ],
        ]);
        $identifierGenerator = $this->getIdentifierGenerator($family);
        $this->assertSame('fam', $this->sut->__invoke(
            $family,
            $identifierGenerator,
            $this->getProductProjection('familyCode'),
            'AKN-'
        ));
    }

    public function test_it_should_return_family_code_with_truncate_and_smaller_family_code(): void
    {
        $family = FamilyProperty::fromNormalized([
            'type' => FamilyProperty::type(),
            'process' => [
                'type' => 'truncate',
                'operator' => Process::PROCESS_OPERATOR_LTE,
                'value' => 3,
            ],
        ]);
        $identifierGenerator = $this->getIdentifierGenerator($family);
        $this->assertSame('fa', $this->sut->__invoke(
            $family,
            $identifierGenerator,
            $this->getProductProjection('fa'),
            'AKN-'
        ));
    }

    public function test_it_should_throw_an_error_if_family_code_is_too_small(): void
    {
        $family = FamilyProperty::fromNormalized([
            'type' => FamilyProperty::type(),
            'process' => [
                'type' => 'truncate',
                'operator' => Process::PROCESS_OPERATOR_EQ,
                'value' => 4,
            ],
        ]);
        $identifierGenerator = $this->getIdentifierGenerator($family);
        $this->expectException(UnableToTruncateException::class);
        $this->sut->__invoke(
            $family,
            $identifierGenerator,
            $this->getProductProjection('fam'),
            'AKN-',
        );
    }

    public function test_it_should_not_throw_an_error_if_family_code_is_exactly_the_right_length(): void
    {
        $family = FamilyProperty::fromNormalized([
            'type' => FamilyProperty::type(),
            'process' => [
                'type' => 'truncate',
                'operator' => Process::PROCESS_OPERATOR_EQ,
                'value' => 3,
            ],
        ]);
        $identifierGenerator = $this->getIdentifierGenerator($family);
        $this->assertSame('fam', $this->sut->__invoke(
            $family,
            $identifierGenerator,
            $this->getProductProjection('fam'),
            'AKN-'
        ));
    }

    public function test_it_should_throw_an_error_if_family_nomenclature_doesnt_exist(): void
    {
        $family = FamilyProperty::fromNormalized([
            'type' => FamilyProperty::type(),
            'process' => [
                'type' => Process::PROCESS_TYPE_NOMENCLATURE,
            ],
        ]);
        $identifierGenerator = $this->getIdentifierGenerator($family);
        $this->familyNomenclatureRepository->expects($this->once())->method('get')->willReturn(null);
        $this->expectException(UndefinedNomenclatureException::class);
        $this->sut->__invoke(
            $family,
            $identifierGenerator,
            $this->getProductProjection('familyCode'),
            'AKN-',
        );
    }

    public function test_it_should_throw_an_error_if_family_nomenclature_doesnt_have_value_and_no_flag_generate_if_empty(): void
    {
        $family = FamilyProperty::fromNormalized([
            'type' => FamilyProperty::type(),
            'process' => [
                'type' => Process::PROCESS_TYPE_NOMENCLATURE,
            ],
        ]);
        $identifierGenerator = $this->getIdentifierGenerator($family);
        $this->familyNomenclatureRepository->expects($this->once())->method('get')->willReturn(null);
        $this->expectException(UndefinedNomenclatureException::class);
        $this->sut->__invoke(
            $family,
            $identifierGenerator,
            $this->getProductProjection('familyCode'),
            'AKN-',
        );
    }

    public function test_it_should_throw_an_error_if_family_nomenclature_is_too_small(): void
    {
        $family = FamilyProperty::fromNormalized([
            'type' => FamilyProperty::type(),
            'process' => [
                'type' => Process::PROCESS_TYPE_NOMENCLATURE,
            ],
        ]);
        $identifierGenerator = $this->getIdentifierGenerator($family);
        $nomenclatureFamily = new NomenclatureDefinition('=', 3, false, ['familyCode' => 'ab']);
        $this->familyNomenclatureRepository->expects($this->once())->method('get')->willReturn($nomenclatureFamily);
        $this->expectException(UnableToTruncateException::class);
        $this->sut->__invoke(
            $family,
            $identifierGenerator,
            $this->getProductProjection('familyCode'),
            'AKN-',
        );
    }

    public function test_it_should_throw_an_error_if_family_nomenclature_is_too_long(): void
    {
        $family = FamilyProperty::fromNormalized([
            'type' => FamilyProperty::type(),
            'process' => [
                'type' => Process::PROCESS_TYPE_NOMENCLATURE,
            ],
        ]);
        $identifierGenerator = $this->getIdentifierGenerator($family);
        $nomenclatureFamily = new NomenclatureDefinition('<=', 3, false, ['familyCode' => 'abcd']);
        $this->familyNomenclatureRepository->expects($this->once())->method('get')->willReturn($nomenclatureFamily);
        $this->expectException(UnableToTruncateException::class);
        $this->sut->__invoke(
            $family,
            $identifierGenerator,
            $this->getProductProjection('familyCode'),
            'AKN-',
        );
    }

    public function test_it_should_return_family_code_with_valid_nomenclature_value(): void
    {
        $family = FamilyProperty::fromNormalized([
            'type' => FamilyProperty::type(),
            'process' => [
                'type' => Process::PROCESS_TYPE_NOMENCLATURE,
            ],
        ]);
        $identifierGenerator = $this->getIdentifierGenerator($family);
        $nomenclatureFamily = new NomenclatureDefinition('<=', 3, false, ['familyCode' => 'abc']);
        $this->familyNomenclatureRepository->expects($this->once())->method('get')->willReturn($nomenclatureFamily);
        $this->assertSame('abc', $this->sut->__invoke(
            $family,
            $identifierGenerator,
            $this->getProductProjection('familyCode'),
            'AKN-'
        ));
    }

    public function test_it_should_return_family_code_with_empty_nomenclature_value_and_flag_generate_if_empty(): void
    {
        $family = FamilyProperty::fromNormalized([
            'type' => FamilyProperty::type(),
            'process' => [
                'type' => Process::PROCESS_TYPE_NOMENCLATURE,
            ],
        ]);
        $identifierGenerator = $this->getIdentifierGenerator($family);
        $nomenclatureFamily = new NomenclatureDefinition('<=', 3, true, []);
        $this->familyNomenclatureRepository->expects($this->once())->method('get')->willReturn($nomenclatureFamily);
        $this->assertSame('fam', $this->sut->__invoke(
            $family,
            $identifierGenerator,
            $this->getProductProjection('familyCode'),
            'AKN-'
        ));
    }

    private function getProductProjection(string $familyCode): ProductProjection
    {
        return new ProductProjection(true, $familyCode, [], []);
    }

    private function getIdentifierGenerator(PropertyInterface $property): IdentifierGenerator
    {
        return new IdentifierGenerator(
            IdentifierGeneratorId::fromString('d556e59e-d46c-465e-863d-f4a39d0b7485'),
            IdentifierGeneratorCode::fromString('my_generator'),
            Conditions::fromArray([]),
            Structure::fromArray([$property]),
            LabelCollection::fromNormalized(['en_US' => 'MyGenerator']),
            Target::fromString('sku'),
            Delimiter::fromString(null),
            TextTransformation::fromString('no'),
        );
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Unit\Application\Generate\Property;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception\UnableToTruncateException;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception\UndefinedAttributeException;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception\UndefinedNomenclatureException;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception\UnexpectedAttributeTypeException;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Generate\Property\PropertyProcessApplier;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\NomenclatureDefinition;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FamilyProperty;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\Process;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\ReferenceEntityProperty;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\FamilyNomenclatureRepository;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\ReferenceEntityNomenclatureRepository;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\SimpleSelectNomenclatureRepository;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PropertyProcessApplierTest extends TestCase
{
    private const TARGET = 'sku';
    private const PREFIX = 'AKN-';
    private const SIMPLE_SELECT_ATTRIBUTE_CODE = 'size';
    private const REF_ENTITY_ATTRIBUTE_CODE = 'brand';

    private FamilyNomenclatureRepository|MockObject $familyNomenclatureRepository;
    private SimpleSelectNomenclatureRepository|MockObject $simpleSelectNomenclatureRepository;
    private GetAttributes|MockObject $getAttributes;
    private ReferenceEntityNomenclatureRepository|MockObject $referenceEntityNomenclatureRepository;
    private PropertyProcessApplier $sut;

    protected function setUp(): void
    {
        $this->familyNomenclatureRepository = $this->createMock(FamilyNomenclatureRepository::class);
        $this->simpleSelectNomenclatureRepository = $this->createMock(SimpleSelectNomenclatureRepository::class);
        $this->getAttributes = $this->createMock(GetAttributes::class);
        $this->referenceEntityNomenclatureRepository = $this->createMock(ReferenceEntityNomenclatureRepository::class);
        $this->sut = new PropertyProcessApplier($this->familyNomenclatureRepository,
            $this->simpleSelectNomenclatureRepository,
            $this->getAttributes,
            $this->referenceEntityNomenclatureRepository,);
    }

    public function test_it_should_return_code_without_truncate(): void
    {
        $this->assertSame('familyCode', $this->sut->apply(
                    Process::fromNormalized([
                        'type' => 'no',
                    ]),
                    FamilyProperty::TYPE,
                    'familyCode',
                    self::TARGET,
                    self::PREFIX,
                ));
    }

    public function test_it_should_return_code_with_truncate(): void
    {
        $this->assertSame('fam', $this->sut->apply(
                    Process::fromNormalized([
                        'type' => 'truncate',
                        'operator' => Process::PROCESS_OPERATOR_LTE,
                        'value' => 3,
                    ]),
                    FamilyProperty::TYPE,
                    'familyCode',
                    self::TARGET,
                    self::PREFIX,
                ));
    }

    public function test_it_should_return_code_with_truncate_and_smaller_code(): void
    {
        $this->assertSame('fa', $this->sut->apply(
                    Process::fromNormalized([
                        'type' => 'truncate',
                        'operator' => Process::PROCESS_OPERATOR_LTE,
                        'value' => 3,
                    ]),
                    FamilyProperty::TYPE,
                    'fa',
                    self::TARGET,
                    self::PREFIX,
                ));
    }

    public function test_it_should_throw_an_error_if_code_is_too_small(): void
    {
        $this->expectException(UnableToTruncateException::class);
        $this->sut->apply(Process::fromNormalized([
                            'type' => 'truncate',
                            'operator' => Process::PROCESS_OPERATOR_EQ,
                            'value' => 4,
                        ]),
                        FamilyProperty::TYPE,
                        'fam',
                        self::TARGET,
                        self::PREFIX,);
    }

    public function test_it_should_not_throw_an_error_if_code_is_exactly_the_right_length(): void
    {
        $this->assertSame('fam', $this->sut->apply(
                    Process::fromNormalized([
                        'type' => 'truncate',
                        'operator' => Process::PROCESS_OPERATOR_EQ,
                        'value' => 3,
                    ]),
                    FamilyProperty::TYPE,
                    'fam',
                    self::TARGET,
                    self::PREFIX
                ));
    }

    public function test_it_should_throw_an_error_if_nomenclature_doesnt_exist(): void
    {
        $this->familyNomenclatureRepository->expects($this->once())->method('get')->willReturn(null);
        $this->expectException(UndefinedNomenclatureException::class);
        $this->sut->apply(Process::fromNormalized([
                            'type' => Process::PROCESS_TYPE_NOMENCLATURE,
                        ]),
                        FamilyProperty::TYPE,
                        'familyCode',
                        self::TARGET,
                        self::PREFIX,);
    }

    public function test_it_should_throw_an_error_if_nomenclature_doesnt_have_value_and_no_flag_generate_if_empty(): void
    {
        $this->familyNomenclatureRepository->expects($this->once())->method('get')->willReturn(null);
        $this->expectException(UndefinedNomenclatureException::class);
        $this->sut->apply(Process::fromNormalized([
                            'type' => Process::PROCESS_TYPE_NOMENCLATURE,
                        ]),
                        FamilyProperty::TYPE,
                        'familyCode',
                        self::TARGET,
                        self::PREFIX,);
    }

    public function test_it_should_throw_an_error_if_nomenclature_is_too_small(): void
    {
        $nomenclature = new NomenclatureDefinition('=', 3, false, ['familyCode' => 'ab']);
        $this->familyNomenclatureRepository->expects($this->once())->method('get')->willReturn($nomenclature);
        $this->expectException(UnableToTruncateException::class);
        $this->sut->apply(Process::fromNormalized([
                            'type' => Process::PROCESS_TYPE_NOMENCLATURE,
                        ]),
                        FamilyProperty::TYPE,
                        'familyCode',
                        self::TARGET,
                        self::PREFIX,);
    }

    public function test_it_should_throw_an_error_if_nomenclature_is_too_long(): void
    {
        $nomenclature = new NomenclatureDefinition('<=', 3, false, ['familyCode' => 'abcd']);
        $this->familyNomenclatureRepository->expects($this->once())->method('get')->willReturn($nomenclature);
        $this->expectException(UnableToTruncateException::class);
        $this->sut->apply(Process::fromNormalized([
                            'type' => Process::PROCESS_TYPE_NOMENCLATURE,
                        ]),
                        FamilyProperty::TYPE,
                        'familyCode',
                        self::TARGET,
                        self::PREFIX,);
    }

    public function test_it_should_return_code_with_valid_nomenclature_value(): void
    {
        $nomenclature = new NomenclatureDefinition('<=', 3, false, ['familyCode' => 'abc']);
        $this->familyNomenclatureRepository->expects($this->once())->method('get')->willReturn($nomenclature);
        $this->assertSame('abc', $this->sut->apply(
                    Process::fromNormalized([
                        'type' => Process::PROCESS_TYPE_NOMENCLATURE,
                    ]),
                    FamilyProperty::TYPE,
                    'familyCode',
                    self::TARGET,
                    self::PREFIX,
                ));
    }

    public function test_it_should_return_simple_select_code_with_valid_nomenclature_value(): void
    {
        $simpleSelectAttribute = new Attribute(
                    self::SIMPLE_SELECT_ATTRIBUTE_CODE,
                    AttributeTypes::OPTION_SIMPLE_SELECT,
                    [],
                    false,
                    false,
                    null,
                    null,
                    null,
                    '',
                    [],
                    false,
                    []
                );
        $this->getAttributes->expects($this->once())->method('forCode')->with(self::SIMPLE_SELECT_ATTRIBUTE_CODE)->willReturn($simpleSelectAttribute);
        $nomenclature = new NomenclatureDefinition('<=', 3, false, ['l' => 'lar']);
        $this->simpleSelectNomenclatureRepository->expects($this->once())->method('get')->with(self::SIMPLE_SELECT_ATTRIBUTE_CODE)->willReturn($nomenclature);
        $this->assertSame('lar', $this->sut->apply(
                    Process::fromNormalized([
                        'type' => Process::PROCESS_TYPE_NOMENCLATURE,
                    ]),
                    self::SIMPLE_SELECT_ATTRIBUTE_CODE,
                    'l',
                    self::TARGET,
                    self::PREFIX,
                ));
    }

    public function test_it_should_return_code_with_empty_nomenclature_value_and_flag_generate_if_empty(): void
    {
        $nomenclature = new NomenclatureDefinition('<=', 3, true, []);
        $this->familyNomenclatureRepository->expects($this->once())->method('get')->willReturn($nomenclature);
        $this->assertSame('fam', $this->sut->apply(
                    Process::fromNormalized([
                        'type' => Process::PROCESS_TYPE_NOMENCLATURE,
                    ]),
                    FamilyProperty::TYPE,
                    'familyCode',
                    self::TARGET,
                    self::PREFIX,
                ));
    }

    public function test_it_should_return_reference_entity_code_with_valid_nomenclature_value(): void
    {
        $nomenclature = new NomenclatureDefinition('<=', 3, false, ['blue' => 'bl']);
        $refEntityAttribute = new Attribute(
                    self::REF_ENTITY_ATTRIBUTE_CODE,
                    AttributeTypes::REFERENCE_ENTITY_SIMPLE_SELECT,
                    [],
                    false,
                    false,
                    null,
                    null,
                    null,
                    '',
                    [],
                    false,
                    []
                );
        $this->getAttributes->expects($this->once())->method('forCode')->with(self::REF_ENTITY_ATTRIBUTE_CODE)->willReturn($refEntityAttribute);
        $this->referenceEntityNomenclatureRepository->expects($this->once())->method('get')->with(self::REF_ENTITY_ATTRIBUTE_CODE)->willReturn($nomenclature);
        $this->assertSame('bl', $this->sut->apply(
                    Process::fromNormalized([
                        'type' => Process::PROCESS_TYPE_NOMENCLATURE,
                    ]),
                    self::REF_ENTITY_ATTRIBUTE_CODE,
                    'blue',
                    self::TARGET,
                    self::PREFIX,
                ));
    }

    public function test_it_should_throw_an_error_if_property_attribute_code_does_not_exists(): void
    {
        $this->getAttributes->expects($this->once())->method('forCode')->with(self::REF_ENTITY_ATTRIBUTE_CODE)->willReturn(null);
        $this->expectException(UndefinedAttributeException::class);
        $this->sut->apply(Process::fromNormalized([
                            'type' => Process::PROCESS_TYPE_NOMENCLATURE,
                        ]),
                        self::REF_ENTITY_ATTRIBUTE_CODE,
                        'value',
                        self::TARGET,
                        self::PREFIX,);
    }

    public function test_it_should_throw_an_error_if_property_attribute_type_is_not_expected(): void
    {
        $unexpectedAttribute = new Attribute(
                    'unexpectedAttribute',
                    AttributeTypes::TEXT,
                    [],
                    false,
                    false,
                    null,
                    null,
                    null,
                    '',
                    [],
                    false,
                    []
                );
        $this->getAttributes->expects($this->once())->method('forCode')->with(self::REF_ENTITY_ATTRIBUTE_CODE)->willReturn($unexpectedAttribute);
        $this->expectException(UnexpectedAttributeTypeException::class);
        $this->sut->apply(Process::fromNormalized([
                            'type' => Process::PROCESS_TYPE_NOMENCLATURE,
                        ]),
                        self::REF_ENTITY_ATTRIBUTE_CODE,
                        'value',
                        self::TARGET,
                        self::PREFIX,);
    }
}

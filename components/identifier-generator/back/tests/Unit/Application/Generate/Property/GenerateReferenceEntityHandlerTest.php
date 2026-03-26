<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Unit\Application\Generate\Property;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception\UnableToTruncateException;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Generate\Property\GenerateReferenceEntityHandler;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Generate\Property\PropertyProcessApplier;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Conditions;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Delimiter;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGenerator;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorCode;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorId;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\LabelCollection;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\ProductProjection;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\AutoNumber;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\Process;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\PropertyInterface;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\ReferenceEntityProperty;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\SimpleSelectProperty;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Structure;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Target;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\TextTransformation;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\FamilyNomenclatureRepository;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\ReferenceEntityNomenclatureRepository;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\SimpleSelectNomenclatureRepository;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Test\Pim\Enrichment\Product\Helper\FeatureHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GenerateReferenceEntityHandlerTest extends TestCase
{
    private FamilyNomenclatureRepository|MockObject $familyNomenclatureRepository;
    private SimpleSelectNomenclatureRepository|MockObject $simpleSelectNomenclatureRepository;
    private GetAttributes|MockObject $getAttributes;
    private ReferenceEntityNomenclatureRepository|MockObject $referenceEntityNomenclatureRepository;
    private GenerateReferenceEntityHandler $sut;

    protected function setUp(): void
    {
        $this->familyNomenclatureRepository = $this->createMock(FamilyNomenclatureRepository::class);
        $this->simpleSelectNomenclatureRepository = $this->createMock(SimpleSelectNomenclatureRepository::class);
        $this->getAttributes = $this->createMock(GetAttributes::class);
        $this->referenceEntityNomenclatureRepository = $this->createMock(ReferenceEntityNomenclatureRepository::class);
        $this->sut = new GenerateReferenceEntityHandler(new PropertyProcessApplier(
                $this->familyNomenclatureRepository,
                $this->simpleSelectNomenclatureRepository,
                $this->getAttributes,
                $this->referenceEntityNomenclatureRepository,
            ));
    }

    public function test_it_should_support_only_reference_entity_property(): void
    {
        $this->assertSame(ReferenceEntityProperty::class, $this->sut->getPropertyClass());
    }

    public function test_it_should_throw_exception_when_invoked_with_something_else_than_reference_entity_property(): void
    {
        $autoNumber = AutoNumber::fromNormalized([
                    'type' => AutoNumber::type(),
                    'numberMin' => 0,
                    'digitsMin' => 1,
                ]);
        $identifierGenerator = $this->getIdentifierGenerator($autoNumber);
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->__invoke($autoNumber,
                        $identifierGenerator,
                        new ProductProjection(true, null, [], []),
                        'AKN-',);
    }

    public function test_it_should_return_reference_entity_code_without_truncate(): void
    {
        $referenceEntity = ReferenceEntityProperty::fromNormalized([
                    'type' => ReferenceEntityProperty::type(),
                    'attributeCode' => 'color',
                    'process' => [
                        'type' => 'no',
                    ],
                    'scope' => null,
                    'locale' => null,
                ]);
        $identifierGenerator = $this->getIdentifierGenerator($referenceEntity);
        $this->assertSame('purple', $this->sut->__invoke(
                    $referenceEntity,
                    $identifierGenerator,
                    $this->getProductProjection(['color-<all_channels>-<all_locales>' => 'purple']),
                    'AKN-'
                ));
    }

    public function test_it_should_return_reference_entity_code_without_truncate_for_specific_locale_and_scope(): void
    {
        $referenceEntity = ReferenceEntityProperty::fromNormalized([
                    'type' => ReferenceEntityProperty::type(),
                    'attributeCode' => 'color',
                    'process' => [
                        'type' => 'no',
                    ],
                    'scope' => 'ecommerce',
                    'locale' => 'fr_FR',
                ]);
        $identifierGenerator = $this->getIdentifierGenerator($referenceEntity);
        $this->assertSame('violet', $this->sut->__invoke(
                    $referenceEntity,
                    $identifierGenerator,
                    $this->getProductProjection([
                        'color-ecommerce-fr_FR' => 'violet',
                        'color-mobile-fr_FR' => 'violet_shiny',
                        'color-ecommerce-en_US' => 'purple',
                        'color-mobile-en_US' => 'shiny_purple',
                    ]),
                    'AKN-'
                ));
    }

    public function test_it_should_return_reference_entity_value_with_truncate(): void
    {
        $referenceEntity = ReferenceEntityProperty::fromNormalized([
                    'type' => ReferenceEntityProperty::type(),
                    'attributeCode' => 'color',
                    'process' => [
                        'type' => 'truncate',
                        'operator' => Process::PROCESS_OPERATOR_LTE,
                        'value' => 3,
                    ],
                    'scope' => null,
                    'locale' => null,
                ]);
        $identifierGenerator = $this->getIdentifierGenerator($referenceEntity);
        $this->assertSame('pur', $this->sut->__invoke(
                    $referenceEntity,
                    $identifierGenerator,
                    $this->getProductProjection(['color-<all_channels>-<all_locales>' => 'purple']),
                    'AKN-'
                ));
    }

    public function test_it_should_return_reference_entity_value_with_truncate_and_smaller_value(): void
    {
        $referenceEntity = ReferenceEntityProperty::fromNormalized([
                    'type' => ReferenceEntityProperty::type(),
                    'attributeCode' => 'color',
                    'process' => [
                        'type' => 'truncate',
                        'operator' => Process::PROCESS_OPERATOR_LTE,
                        'value' => 3,
                    ],
                    'scope' => null,
                    'locale' => null,
                ]);
        $identifierGenerator = $this->getIdentifierGenerator($referenceEntity);
        $this->assertSame('pu', $this->sut->__invoke(
                    $referenceEntity,
                    $identifierGenerator,
                    $this->getProductProjection(['color-<all_channels>-<all_locales>' => 'pu']),
                    'AKN-'
                ));
    }

    public function test_it_should_throw_an_error_if_reference_entity_value_is_too_small(): void
    {
        $referenceEntity = ReferenceEntityProperty::fromNormalized([
                    'type' => ReferenceEntityProperty::type(),
                    'attributeCode' => 'color',
                    'process' => [
                        'type' => 'truncate',
                        'operator' => Process::PROCESS_OPERATOR_EQ,
                        'value' => 4,
                    ],
                    'scope' => null,
                    'locale' => null,
                ]);
        $identifierGenerator = $this->getIdentifierGenerator($referenceEntity);
        $this->expectException(UnableToTruncateException::class);
        $this->sut->__invoke($referenceEntity,
                        $identifierGenerator,
                        $this->getProductProjection(['color-<all_channels>-<all_locales>' => 'pur']),
                        'AKN-',);
    }

    public function test_it_should_not_throw_an_error_if_reference_entity_value_is_exactly_the_right_length(): void
    {
        $referenceEntity = ReferenceEntityProperty::fromNormalized([
                    'type' => ReferenceEntityProperty::type(),
                    'attributeCode' => 'color',
                    'process' => [
                        'type' => 'truncate',
                        'operator' => Process::PROCESS_OPERATOR_EQ,
                        'value' => 3,
                    ],
                    'scope' => null,
                    'locale' => null,
                ]);
        $identifierGenerator = $this->getIdentifierGenerator($referenceEntity);
        $this->assertSame('pur', $this->sut->__invoke(
                    $referenceEntity,
                    $identifierGenerator,
                    $this->getProductProjection(['color-<all_channels>-<all_locales>' => 'pur']),
                    'AKN-'
                ));
    }

    private function getProductProjection(array $productValues): ProductProjection
    {
            return new ProductProjection(true, 'accessories', $productValues, []);
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

<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Unit\Domain\Model;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Conditions;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Enabled;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Delimiter;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGenerator;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorCode;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorId;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\LabelCollection;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\ProductProjection;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FamilyProperty;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FreeText;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Structure;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Target;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\TextTransformation;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IdentifierGeneratorTest extends TestCase
{
    private IdentifierGenerator $sut;

    protected function setUp(): void
    {
        $identifierGeneratorId = IdentifierGeneratorId::fromString('2038e1c9-68ff-4833-b06f-01e42d206002');
        $identifierGeneratorCode = IdentifierGeneratorCode::fromString('abcdef');
        $freeText = FreeText::fromString('abc');
        $family = FamilyProperty::fromNormalized(['type' => 'family', 'process' => ['type' => 'no']]);
        $enabled = Enabled::fromBoolean(true);
        $structure = Structure::fromArray([$freeText, $family]);
        $conditions = Conditions::fromArray([$enabled]);
        $label = LabelCollection::fromNormalized(['fr' => 'Générateur']);
        $delimiter = Delimiter::fromString('-');
        $target = Target::fromString('sku');
        $textTransformation = TextTransformation::fromString('no');

        $this->sut = new IdentifierGenerator(
            $identifierGeneratorId,
            $identifierGeneratorCode,
            $conditions,
            $structure,
            $label,
            $target,
            $delimiter,
            $textTransformation,
        );
    }

    public function test_it_is_an_identifier_generator(): void
    {
        $this->assertInstanceOf(IdentifierGenerator::class, $this->sut);
    }

    public function test_it_can_instantiated_with_null_value_delimiter(): void
    {
        $identifierGeneratorId = IdentifierGeneratorId::fromString('2038e1c9-68ff-4833-b06f-01e42d206002');
        $identifierGeneratorCode = IdentifierGeneratorCode::fromString('abcdef');
        $conditions = Conditions::fromArray([]);
        $freeText = FreeText::fromString('abc');
        $structure = Structure::fromArray([$freeText]);
        $label = LabelCollection::fromNormalized(['fr' => 'Générateur']);
        $target = Target::fromString('sku');
        $delimiter = Delimiter::fromString(null);
        $textTransformation = TextTransformation::fromString('no');
        $this->sut = new IdentifierGenerator(
            $identifierGeneratorId,
            $identifierGeneratorCode,
            $conditions,
            $structure,
            $label,
            $target,
            $delimiter,
            $textTransformation,
        );
        $this->assertInstanceOf(IdentifierGenerator::class, $this->sut);
        $this->assertNull($this->sut->delimiter()->asString());
    }

    public function test_it_returns_an_indentifier_generator_id(): void
    {
        $this->assertEquals(IdentifierGeneratorId::fromString('2038e1c9-68ff-4833-b06f-01e42d206002'), $this->sut->id());
    }

    public function test_it_returns_an_indentifier_generator_code(): void
    {
        $this->assertEquals(IdentifierGeneratorCode::fromString('abcdef'), $this->sut->code());
    }

    public function test_it_returns_a_delimiter(): void
    {
        $this->assertEquals(Delimiter::fromString('-'), $this->sut->delimiter());
    }

    public function test_it_sets_a_delimiter(): void
    {
        $this->assertSame('-', $this->sut->delimiter()->asString());
        $result = $this->sut->withDelimiter(Delimiter::fromString('='));
        $this->assertSame('=', $result->delimiter()->asString());
        $this->assertNotSame($this->sut, $result);
        $this->assertSame('-', $this->sut->delimiter()->asString());
    }

    public function test_it_returns_a_target(): void
    {
        $this->assertEquals(Target::fromString('sku'), $this->sut->target());
    }

    public function test_it_sets_a_target(): void
    {
        $this->assertSame('sku', $this->sut->target()->asString());
        $result = $this->sut->withTarget(Target::fromString('gtin'));
        $this->assertSame('gtin', $result->target()->asString());
        $this->assertNotSame($this->sut, $result);
        $this->assertSame('sku', $this->sut->target()->asString());
    }

    public function test_it_returns_a_conditions(): void
    {
        $this->assertEquals(Conditions::fromArray([Enabled::fromBoolean(true)]), $this->sut->conditions());
    }

    public function test_it_returns_a_structure(): void
    {
        $this->assertEquals(Structure::fromArray([
            FreeText::fromString('abc'),
            FamilyProperty::fromNormalized(['type' => 'family', 'process' => ['type' => 'no']]),
        ]), $this->sut->structure());
    }

    public function test_it_sets_a_structure(): void
    {
        $previousStructure = Structure::fromArray([
            FreeText::fromString('abc'),
            FamilyProperty::fromNormalized(['type' => 'family', 'process' => ['type' => 'no']]),
        ]);
        $updatedStructure = Structure::fromArray([
            FreeText::fromString('def'),
            FamilyProperty::fromNormalized(['type' => 'family', 'process' => ['type' => 'truncate', 'operator' => '=', 'value' => 3]]),
        ]);
        $this->assertEquals($previousStructure, $this->sut->structure());
        $result = $this->sut->withStructure($updatedStructure);
        $this->assertEquals($updatedStructure, $result->structure());
        $this->assertNotSame($this->sut, $result);
        $this->assertEquals($previousStructure, $this->sut->structure());
    }

    public function test_it_returns_a_labels_collection(): void
    {
        $this->assertEquals(LabelCollection::fromNormalized(['fr' => 'Générateur']), $this->sut->labelCollection());
    }

    public function test_it_sets_a_labels_collection(): void
    {
        $previousLabelCollection = LabelCollection::fromNormalized(['fr' => 'Générateur']);
        $updatedLabelCollection = LabelCollection::fromNormalized([
            'fr' => 'Générateur',
            'en' => 'generator',
        ]);
        $this->assertEquals($previousLabelCollection, $this->sut->labelCollection());
        $result = $this->sut->withLabelCollection($updatedLabelCollection);
        $this->assertEquals($updatedLabelCollection, $result->labelCollection());
        $this->assertNotSame($this->sut, $result);
        $this->assertEquals($previousLabelCollection, $this->sut->labelCollection());
    }

    public function test_it_can_be_normalized(): void
    {
        $this->assertSame([
            'uuid' => '2038e1c9-68ff-4833-b06f-01e42d206002',
            'code' => 'abcdef',
            'conditions' => [
                [
                    'type' => 'enabled',
                    'value' => true,
                ],
            ],
            'structure' => [
                [
                    'type' => 'free_text',
                    'string' => 'abc',
                ], [
                    'type' => 'family',
                    'process' => [
                        'type' => 'no',
                    ],
                ],
            ],
            'labels' => [
                'fr' => 'Générateur',
            ],
            'target' => 'sku',
            'delimiter' => '-',
            'text_transformation' => 'no',
        ], $this->sut->normalize());
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Unit\Infrastructure\Repository;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Exception\CouldNotFindIdentifierGeneratorException;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Conditions;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Delimiter;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGenerator;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorCode;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorId;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\LabelCollection;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FreeText;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Structure;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Target;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\TextTransformation;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\IdentifierGeneratorRepository;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Repository\InMemoryIdentifierGeneratorRepository;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryIdentifierGeneratorRepositoryTest extends TestCase
{
    private InMemoryIdentifierGeneratorRepository $sut;

    protected function setUp(): void
    {
        $this->sut = new InMemoryIdentifierGeneratorRepository();
    }

    public function test_it_is_an_identifier_generator_repository(): void
    {
        $this->assertInstanceOf(IdentifierGeneratorRepository::class, $this->sut);
    }

    public function test_it_can_save_identifier_generators(): void
    {
        $identifierGenerator = new IdentifierGenerator(
                    IdentifierGeneratorId::fromString('2038e1c9-68ff-4833-b06f-01e42d206002'),
                    IdentifierGeneratorCode::fromString('abcdef'),
                    Conditions::fromArray([]),
                    Structure::fromArray([FreeText::fromString('abc')]),
                    LabelCollection::fromNormalized(['fr' => 'Générateur']),
                    Target::fromString('sku'),
                    Delimiter::fromString('-'),
                    TextTransformation::fromString('no'),
                );
        $this->sut->save($identifierGenerator);
        $this->assertEquals([
                    $identifierGenerator,
                ], $this->sut->generators);
        $identifierGenerator2 = new IdentifierGenerator(
                    IdentifierGeneratorId::fromString('2038e1c9-68ff-4833-b06f-01e42d206002'),
                    IdentifierGeneratorCode::fromString('fedcba'),
                    Conditions::fromArray([]),
                    Structure::fromArray([FreeText::fromString('abc')]),
                    LabelCollection::fromNormalized(['fr' => 'Générateur']),
                    Target::fromString('sku'),
                    Delimiter::fromString('-'),
                    TextTransformation::fromString('no'),
                );
        $this->sut->save($identifierGenerator2);
        $this->assertEquals([
                    $identifierGenerator,
                    $identifierGenerator2,
                ], $this->sut->generators);
    }

    public function test_it_can_update_identifier_generators(): void
    {
        $identifierGenerator = new IdentifierGenerator(
                    IdentifierGeneratorId::fromString('2038e1c9-68ff-4833-b06f-01e42d206002'),
                    IdentifierGeneratorCode::fromString('abcdef'),
                    Conditions::fromArray([]),
                    Structure::fromArray([FreeText::fromString('abc')]),
                    LabelCollection::fromNormalized(['fr' => 'Générateur']),
                    Target::fromString('sku'),
                    Delimiter::fromString('-'),
                    TextTransformation::fromString('no'),
                );
        $this->sut->save($identifierGenerator);
        $this->assertEquals([
                    $identifierGenerator,
                ], $this->sut->generators);
        $identifierGenerator2 = new IdentifierGenerator(
                    IdentifierGeneratorId::fromString('2038e1c9-68ff-4833-b06f-01e42d206002'),
                    IdentifierGeneratorCode::fromString('abcdef'),
                    Conditions::fromArray([]),
                    Structure::fromArray([FreeText::fromString('abc update')]),
                    LabelCollection::fromNormalized(['fr' => 'Générateur update']),
                    Target::fromString('sku'),
                    Delimiter::fromString('='),
                    TextTransformation::fromString('no'),
                );
        $this->sut->update($identifierGenerator2);
        $this->assertEquals([
                    $identifierGenerator2,
                ], $this->sut->generators);
    }

    public function test_it_can_retrieve_an_identifier_generator_with_its_code(): void
    {
        $identifierGenerator = new IdentifierGenerator(
                    IdentifierGeneratorId::fromString('2038e1c9-68ff-4833-b06f-01e42d206002'),
                    IdentifierGeneratorCode::fromString('aabbcc'),
                    Conditions::fromArray([]),
                    Structure::fromArray([FreeText::fromString('abc')]),
                    LabelCollection::fromNormalized(['fr' => 'Générateur']),
                    Target::fromString('sku'),
                    Delimiter::fromString('-'),
                    TextTransformation::fromString('no'),
                );
        $this->sut->save($identifierGenerator);
        $this->assertEquals($identifierGenerator, $this->sut->get('aabbcc'));
    }

    public function test_it_can_retrieve_an_identifier_generator_with_its_code_while_ignoring_case(): void
    {
        $identifierGenerator = new IdentifierGenerator(
                    IdentifierGeneratorId::fromString('2038e1c9-68ff-4833-b06f-01e42d206002'),
                    IdentifierGeneratorCode::fromString('aAbBcC'),
                    Conditions::fromArray([]),
                    Structure::fromArray([FreeText::fromString('abc')]),
                    LabelCollection::fromNormalized(['fr' => 'Générateur']),
                    Target::fromString('sku'),
                    Delimiter::fromString('-'),
                    TextTransformation::fromString('no'),
                );
        $this->sut->save($identifierGenerator);
        $this->assertEquals($identifierGenerator, $this->sut->get('AabbCC'));
    }

    public function test_it_returns_null_if_identifier_generator_is_not_found(): void
    {
        $this->expectException(CouldNotFindIdentifierGeneratorException::class);
        $this->sut->get('unknown');
    }

    public function test_it_counts_identifier_generators(): void
    {
        $this->assertSame(0, $this->sut->count());
        $identifierGenerator = new IdentifierGenerator(
                    IdentifierGeneratorId::fromString('2038e1c9-68ff-4833-b06f-01e42d206002'),
                    IdentifierGeneratorCode::fromString('aabbcc'),
                    Conditions::fromArray([]),
                    Structure::fromArray([FreeText::fromString('abc')]),
                    LabelCollection::fromNormalized(['fr' => 'Générateur']),
                    Target::fromString('sku'),
                    Delimiter::fromString('-'),
                    TextTransformation::fromString('no'),
                );
        $this->sut->save($identifierGenerator);
        $this->assertSame(1, $this->sut->count());
    }

    public function test_it_can_delete_an_identifier_generator_while_ignoring_case(): void
    {
        $identifierGenerator = new IdentifierGenerator(
                    IdentifierGeneratorId::fromString('2038e1c9-68ff-4833-b06f-01e42d206002'),
                    IdentifierGeneratorCode::fromString('aabbcc'),
                    Conditions::fromArray([]),
                    Structure::fromArray([FreeText::fromString('abc')]),
                    LabelCollection::fromNormalized(['fr' => 'Générateur']),
                    Target::fromString('sku'),
                    Delimiter::fromString('-'),
                    TextTransformation::fromString('no'),
                );
        $this->sut->save($identifierGenerator);
        $this->assertSame(1, $this->sut->count());
        $this->sut->delete('unknown_code');
        $this->assertSame(1, $this->sut->count());
        $this->sut->delete('aABbcC');
        $this->assertSame(0, $this->sut->count());
    }

    public function test_it_can_retrieve_all_identifiers_generators(): void
    {
        $identifierGenerator = new IdentifierGenerator(
                    IdentifierGeneratorId::fromString('2038e1c9-68ff-4833-b06f-01e42d206002'),
                    IdentifierGeneratorCode::fromString('aabbcc'),
                    Conditions::fromArray([]),
                    Structure::fromArray([FreeText::fromString('abc')]),
                    LabelCollection::fromNormalized(['fr' => 'Générateur']),
                    Target::fromString('sku'),
                    Delimiter::fromString('-'),
                    TextTransformation::fromString('no'),
                );
        $this->sut->save($identifierGenerator);
        $this->assertEquals([$identifierGenerator], $this->sut->getAll());
    }

    public function test_it_can_reorder_generators_while_ignoring_case(): void
    {
        $identifierGenerator1 = new IdentifierGenerator(
                    IdentifierGeneratorId::fromString('2038e1c9-68ff-4833-b06f-01e42d206002'),
                    IdentifierGeneratorCode::fromString('abcdef'),
                    Conditions::fromArray([]),
                    Structure::fromArray([FreeText::fromString('abc')]),
                    LabelCollection::fromNormalized(['fr' => 'Générateur']),
                    Target::fromString('sku'),
                    Delimiter::fromString('-'),
                    TextTransformation::fromString('no'),
                );
        $this->sut->save($identifierGenerator1);
        $identifierGenerator2 = new IdentifierGenerator(
                    IdentifierGeneratorId::fromString('2038e1c9-68ff-4833-b06f-01e42d206002'),
                    IdentifierGeneratorCode::fromString('fedcba'),
                    Conditions::fromArray([]),
                    Structure::fromArray([FreeText::fromString('abc')]),
                    LabelCollection::fromNormalized(['fr' => 'Générateur']),
                    Target::fromString('sku'),
                    Delimiter::fromString('-'),
                    TextTransformation::fromString('no'),
                );
        $this->sut->save($identifierGenerator2);
        $this->assertSame([$identifierGenerator1, $identifierGenerator2], $this->sut->getAll());
        $this->sut->reorder(['fEdcBa', 'abcdef']);
        $this->assertSame([$identifierGenerator2, $identifierGenerator1], $this->sut->getAll());
    }
}

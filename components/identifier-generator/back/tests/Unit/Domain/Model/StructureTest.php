<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Unit\Domain\Model;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Family;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\AutoNumber;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FamilyProperty;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FreeText;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\PropertyInterface;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Structure;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class StructureTest extends TestCase
{
    private Structure $sut;

    protected function setUp(): void
    {
        $freeText = FreeText::fromString('ABC');
        $autoNumber = AutoNumber::fromValues(5, 2);
        $family = FamilyProperty::fromNormalized(['type' => 'family', 'process' => ['type' => 'no']]);
        $this->sut = Structure::fromArray([$freeText, $autoNumber, $family]);
    }

    public function test_it_is_a_structure(): void
    {
        $this->assertInstanceOf(Structure::class, $this->sut);
    }

    public function test_it_throws_an_exception_when_an_empty_array(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Structure::fromArray([]);
    }

    public function test_it_throws_an_exception_when_an_array_value_is_not_an_property(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Structure::fromArray([5, '']);
    }

    public function test_it_has_properties_values(): void
    {
        $properties = $this->sut->getProperties();
        $this->assertCount(3, $properties);
        $this->assertInstanceOf(PropertyInterface::class, $properties[0]);
        $this->assertInstanceOf(PropertyInterface::class, $properties[1]);
        $this->assertInstanceOf(PropertyInterface::class, $properties[2]);
    }

    public function test_it_normalize_a_structure(): void
    {
        $this->assertSame([
            [
                'type' => 'free_text',
                'string' => 'ABC',
            ], [
                'type' => 'auto_number',
                'numberMin' => 5,
                'digitsMin' => 2,
            ], [
                'type' => 'family',
                'process' => [
                    'type' => 'no',
                ],
            ],
        ], $this->sut->normalize());
    }

    public function test_it_creates_from_normalized(): void
    {
        $this->assertEquals(Structure::fromArray([
            FreeText::fromString('CBA'),
            AutoNumber::fromValues(5, 6),
        ]), Structure::fromNormalized([
            [
                'type' => 'free_text',
                'string' => 'CBA',
            ],
            [
                'type' => 'auto_number',
                'numberMin' => 5,
                'digitsMin' => 6,
            ],
        ]));
    }

    public function test_it_should_get_implicit_conditions(): void
    {
        $this->assertEquals([
            Family::fromNormalized(['type' => 'family', 'operator' => 'NOT EMPTY']),
        ], $this->sut->getImplicitConditions());
    }
}

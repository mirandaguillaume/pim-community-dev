<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\Domain\StandardFormat\Validator;

use Akeneo\Pim\Enrichment\Product\Domain\StandardFormat\Validator\QuantifiedAssociationsStructureValidator;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PHPUnit\Framework\TestCase;

class QuantifiedAssociationsStructureValidatorTest extends TestCase
{
    private QuantifiedAssociationsStructureValidator $sut;

    protected function setUp(): void
    {
        $this->sut = new QuantifiedAssociationsStructureValidator();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(QuantifiedAssociationsStructureValidator::class, $this->sut);
    }

    public function test_it_throws_when_not_array(): void
    {
        $field = 'quantified_associations';
        $data = null;
        $this->expectException(InvalidPropertyTypeException::arrayExpected(
            $field,
            QuantifiedAssociationsStructureValidator::class,
            $data
        ));
        $this->sut->validate($field, $data);
    }

    public function test_it_accepts_numeric_association_type_codes(): void
    {
        $field = 'quantified_associations';
        $data = [
                    '1234' => [],
                ];
        $this->sut->shouldNotThrow()->during('validate', [$field, $data]);
    }

    public function test_it_throws_when_association_type_values_is_not_an_array(): void
    {
        $field = 'quantified_associations';
        $data = [
                    'PACK' => 'foo',
                ];
        $this->expectException(InvalidPropertyTypeException::validArrayStructureExpected(
            $field,
            '"PACK" should contain an array',
            QuantifiedAssociationsStructureValidator::class,
            $data
        ));
        $this->sut->validate($field, $data);
    }

    public function test_it_throws_when_quantified_link_type_is_not_a_string(): void
    {
        $field = 'quantified_associations';
        $data = [
                    'PACK' => [
                        0 => [],
                    ],
                ];
        $this->expectException(InvalidPropertyTypeException::validArrayStructureExpected(
            $field,
            'entity type in "PACK" should be a string',
            QuantifiedAssociationsStructureValidator::class,
            $data
        ));
        $this->sut->validate($field, $data);
    }

    public function test_it_throws_when_quantified_links_is_not_an_array(): void
    {
        $field = 'quantified_associations';
        $data = [
                    'PACK' => [
                        'products' => 'foo',
                    ],
                ];
        $this->expectException(InvalidPropertyTypeException::validArrayStructureExpected(
            $field,
            '"PACK[products]" should contain an array',
            QuantifiedAssociationsStructureValidator::class,
            $data
        ));
        $this->sut->validate($field, $data);
    }

    public function test_it_throws_when_quantified_links_is_not_a_sequential_array(): void
    {
        $field = 'quantified_associations';
        $data = [
                    'PACK' => [
                        'products' => [
                            'foo' => [],
                        ],
                    ],
                ];
        $this->expectException(InvalidPropertyTypeException::validArrayStructureExpected(
            $field,
            '"PACK[products]" should contain an array',
            QuantifiedAssociationsStructureValidator::class,
            $data
        ));
        $this->sut->validate($field, $data);
    }

    public function test_it_throws_when_quantified_link_has_no_identifier(): void
    {
        $field = 'quantified_associations';
        $data = [
                    'PACK' => [
                        'products' => [
                            ['quantity' => 3],
                        ],
                    ],
                ];
        $this->expectException(InvalidPropertyTypeException::validArrayStructureExpected(
            $field,
            'a quantified association should contain the key "identifier"',
            QuantifiedAssociationsStructureValidator::class,
            $data
        ));
        $this->sut->validate($field, $data);
    }

    public function test_it_throws_when_quantified_link_has_no_quantity(): void
    {
        $field = 'quantified_associations';
        $data = [
                    'PACK' => [
                        'products' => [
                            ['identifier' => 'foo'],
                        ],
                    ],
                ];
        $this->expectException(InvalidPropertyTypeException::validArrayStructureExpected(
            $field,
            'a quantified association should contain the key "quantity"',
            QuantifiedAssociationsStructureValidator::class,
            $data
        ));
        $this->sut->validate($field, $data);
    }

    public function test_it_throws_when_quantified_link_identifier_is_not_a_string(): void
    {
        $field = 'quantified_associations';
        $data = [
                    'PACK' => [
                        'products' => [
                            ['identifier' => 1, 'quantity' => 3],
                        ],
                    ],
                ];
        $this->expectException(InvalidPropertyTypeException::validArrayStructureExpected(
            $field,
            'a quantified association should contain a valid identifier',
            QuantifiedAssociationsStructureValidator::class,
            $data
        ));
        $this->sut->validate($field, $data);
    }

    public function test_it_throws_when_quantified_link_quantity_is_not_an_integer(): void
    {
        $field = 'quantified_associations';
        $data = [
                    'PACK' => [
                        'products' => [
                            ['identifier' => 'foo', 'quantity' => 'bar'],
                        ],
                    ],
                ];
        $this->expectException(InvalidPropertyTypeException::validArrayStructureExpected(
            $field,
            'a quantified association should contain a valid quantity',
            QuantifiedAssociationsStructureValidator::class,
            $data
        ));
        $this->sut->validate($field, $data);
    }

    public function test_it_does_no_throws_when_valid(): void
    {
        $field = 'quantified_associations';
        $data = [
                    'PACK' => [
                        'products' => [
                            ['identifier' => 'foo', 'quantity' => 3],
                        ],
                    ],
                ];
        $this->sut->shouldNotThrow()->during(
            'validate',
            [$field, $data]
        );
    }
}

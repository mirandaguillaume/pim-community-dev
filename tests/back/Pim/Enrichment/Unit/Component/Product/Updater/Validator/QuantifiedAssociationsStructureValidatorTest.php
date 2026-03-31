<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Updater\Validator;

use Akeneo\Pim\Enrichment\Component\Product\Updater\Validator\QuantifiedAssociationsStructureValidator;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Validator\QuantifiedAssociationsStructureValidatorInterface;
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

    public function test_it_is_a_quantified_associations_structure_validator(): void
    {
        $this->assertInstanceOf(QuantifiedAssociationsStructureValidatorInterface::class, $this->sut);
    }

    public function test_it_throws_when_not_array(): void
    {
        $field = 'quantified_associations';
        $data = null;
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->validate($field, $data);
    }

    public function test_it_accepts_numeric_association_type_codes(): void
    {
        $field = 'quantified_associations';
        $data = [
                    '1234' => [],
                ];
        $this->sut->validate($field, $data);
        $this->addToAssertionCount(1);
    }

    public function test_it_throws_when_association_type_values_is_not_an_array(): void
    {
        $field = 'quantified_associations';
        $data = [
                    'PACK' => 'foo',
                ];
        $this->expectException(InvalidPropertyTypeException::class);
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
        $this->expectException(InvalidPropertyTypeException::class);
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
        $this->expectException(InvalidPropertyTypeException::class);
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
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->validate($field, $data);
    }

    public function test_it_throws_when_quantified_link_has_no_identifier_and_no_uuid(): void
    {
        $field = 'quantified_associations';
        $data = [
                    'PACK' => [
                        'products' => [
                            ['quantity' => 3],
                        ],
                    ],
                ];
        $this->expectException(InvalidPropertyTypeException::class);
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
        $this->expectException(InvalidPropertyTypeException::class);
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
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->validate($field, $data);
    }

    public function test_it_throws_when_quantified_link_uuid_is_not_a_string(): void
    {
        $field = 'quantified_associations';
        $data = [
                    'PACK' => [
                        'products' => [
                            ['uuid' => 1, 'quantity' => 3],
                        ],
                    ],
                ];
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->validate($field, $data);
    }

    public function test_it_throws_when_quantified_link_uuid_is_not_a_valid_uuid(): void
    {
        $field = 'quantified_associations';
        $data = [
                    'PACK' => [
                        'products' => [
                            ['uuid' => 'invalid_uuid', 'quantity' => 3],
                        ],
                    ],
                ];
        $this->expectException(InvalidPropertyTypeException::class);
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
        $this->expectException(InvalidPropertyTypeException::class);
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
        $this->sut->validate($field, $data);
        $this->addToAssertionCount(1);
    }
}

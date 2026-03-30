<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Connector\ArrayConverter;

use Akeneo\Tool\Component\Connector\Exception\DataArrayConversionException;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Tool\Component\Connector\ArrayConverter\InvalidDataItemConverter;

class InvalidDataItemConverterTest extends TestCase
{
    private InvalidDataItemConverter $sut;

    protected function setUp(): void
    {
        $this->sut = new InvalidDataItemConverter();
    }

    public function test_it_can_not_convert_because_of_multi_dimensional_array(): void
    {
        $item = [
                    'string_key' => 'effeacef4848484',
                    'array_key' => [
                        'short' => ['foo'],
                        'long' => ['foobar'],
                    ],
                ];
        $this->expectException(DataArrayConversionException::class);
        $this->sut->convert($item);
    }

    public function test_it_can_not_convert_because_of_object(): void
    {
        $item = [
                    'string_key' => 'effeacef4848484',
                    'object_key' => new \stdClass(),
                ];
        $this->expectException(DataArrayConversionException::class);
        $this->sut->convert($item);
    }

    public function test_it_converts_to_a_string_array(): void
    {
        $myObject = new FakeObject();
        $myObject->property = 'objectValue';
        $item = [
                    'string_key' => 'effeacef4848484',
                    'array_key' => ['short' => 'foo', 'long' => 'foobar'],
                    'date_key' => new \DateTime('2019-08-29'),
                    'numeric_key' => 666,
                    'null_key' => null,
                    'object_key' => $myObject,
                ];
        $convertedItem = [
                    'string_key' => 'effeacef4848484',
                    'array_key' => 'foo,foobar',
                    'date_key' => '2019-08-29',
                    'numeric_key' => '666',
                    'null_key' => '',
                    'object_key' => 'objectValue',
                ];
        $this->assertSame($convertedItem, $this->sut->convert($item));
    }
}

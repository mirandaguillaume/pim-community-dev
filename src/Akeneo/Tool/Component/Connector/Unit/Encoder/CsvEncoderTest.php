<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Connector\Encoder;

use Akeneo\Tool\Component\Connector\Encoder\CsvEncoder;
use PHPUnit\Framework\TestCase;

class CsvEncoderTest extends TestCase
{
    private CsvEncoder $sut;

    protected function setUp(): void
    {
        $this->sut = new CsvEncoder();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(CsvEncoder::class, $this->sut);
    }

    public function test_it_is_a_encoder(): void
    {
        $this->assertInstanceOf(\Symfony\Component\Serializer\Encoder\EncoderInterface::class, $this->sut);
    }

    public function test_it_is_csv_encoder(): void
    {
        $this->assertSame(true, $this->sut->supportsEncoding('csv'));
        $this->assertSame(false, $this->sut->supportsEncoding('json'));
    }

    public function test_it_encodes_data_in_csv(): void
    {
        $this->assertSame("foo,\"\"\"bar\"\"\"\n", $this->sut->encode(
            [
                        'code' => 'foo',
                        'name' => '"bar"',
                    ],
            'csv',
            [
                        'delimiter' => ',',
                        'enclosure' => '"',
                    ]
        ));
        $this->assertSame("foo;\"\"\"bar\"\"\"\n", $this->sut->encode(
            [
                        'code' => 'foo',
                        'name' => '"bar"',
                    ],
            'csv',
            [
                        'delimiter' => null,
                        'enclosure' => null,
                    ]
        ));
        $this->assertSame("foo;\"\"\"bar\"\"\"\n", $this->sut->encode(
            [
                        'code' => 'foo',
                        'name' => '"bar"',
                    ],
            'csv',
            [
                        'delimiter' => ';',
                        'enclosure' => '"',
                    ]
        ));
        $this->assertSame("foo;\"bar\"\n", $this->sut->encode(
            [
                        'code' => 'foo',
                        'name' => '"bar"',
                    ],
            'csv',
            [
                        'delimiter' => ';',
                        'enclosure' => '\'',
                    ]
        ));
        $this->assertSame("foo;\"bar\"\n", $this->sut->encode(
            [
                        'code' => 'foo',
                        'name' => '"bar"',
                    ],
            'csv',
            [
                        'delimiter' => null,
                        'enclosure' => '\'',
                    ]
        ));
        $this->assertSame("foo,\"\"\"bar\"\"\"\n", $this->sut->encode(
            [
                        'code' => 'foo',
                        'name' => '"bar"',
                    ],
            'csv',
            [
                        'delimiter' => ',',
                        'enclosure' => null,
                    ]
        ));
        $this->assertSame("foo;bar\nbaz;buz\n", $this->sut->encode(
            [
                        ['name' => 'foo', 'code' => 'bar'],
                        ['name' => 'baz', 'code' => 'buz'],
                    ],
            'csv'
        ));
        $this->assertSame("\n", $this->sut->encode(
            [],
            'csv'
        ));
    }

    public function test_it_encodes_header(): void
    {
        $this->assertSame("name;code\nfoo;bar\nbaz;buz\n", $this->sut->encode(
            [
                        ['name' => 'foo', 'code' => 'bar'],
                        ['name' => 'baz', 'code' => 'buz'],
                    ],
            'csv',
            [
                        'withHeader' => true,
                    ]
        ));
        $this->assertSame("foo;bar\nbaz;buz\n", $this->sut->encode(
            [
                        ['name' => 'foo', 'code' => 'bar'],
                        ['name' => 'baz', 'code' => 'buz'],
                    ],
            'csv',
            [
                        'withHeader' => false,
                    ]
        ));
    }

    public function test_it_throws_exception_when_data_are_invalid(): void
    {
        $this->expectException('\InvalidArgumentException');
        $this->sut->encode(null, 'csv');
        $this->expectException('\InvalidArgumentException');
        $this->sut->encode(false, 'csv');
        $this->expectException('\InvalidArgumentException');
        $this->sut->encode(true, 'csv');
        $this->expectException('\InvalidArgumentException');
        $this->sut->encode('foo', 'csv');
        $this->expectException('\InvalidArgumentException');
        $this->sut->encode(1, 'csv');
    }
}

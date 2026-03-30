<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Api\Converter;

use Akeneo\Tool\Component\Api\Converter\MeasureFamilyConverter;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use PHPUnit\Framework\TestCase;

class MeasureFamilyConverterTest extends TestCase
{
    private MeasureFamilyConverter $sut;

    protected function setUp(): void
    {
        $this->sut = new MeasureFamilyConverter();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(MeasureFamilyConverter::class, $this->sut);
    }

    public function test_it_is_a_converter(): void
    {
        $this->assertInstanceOf(ArrayConverterInterface::class, $this->sut);
    }

    public function test_it_converts_a_measure_family(): void
    {
        $item = [
                    'family_code' => 'area',
                    'units' => [
                        'standard' => 'SQUARE_METER',
                        'units' => [
                            'SQUARE_MILLIMETER' => [
                                'convert' => [['mul' => '0.000001']],
                                'symbol' => 'cm²',
                            ],
                        ],
                    ],
                ];
        $convertedItem = [
                    "code" => $item['family_code'],
                    "standard" => $item['units']['standard'],
                    "units" => [
                        [
                            'code' => 'SQUARE_MILLIMETER',
                            'convert' => ['mul' => '0.000001'],
                            'symbol' => 'cm²',
                        ],
                    ],
                ];
        $this->assertSame($convertedItem, $this->sut->convert($item));
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\MeasureBundle\Controller\ExternalApi\JsonSchema;

use Akeneo\Tool\Bundle\MeasureBundle\Controller\ExternalApi\JsonSchema\MeasurementFamilyListValidator;
use PHPUnit\Framework\TestCase;

class MeasurementFamilyListValidatorTest extends TestCase
{
    private MeasurementFamilyListValidator $sut;

    protected function setUp(): void
    {
        $this->sut = new MeasurementFamilyListValidator();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(MeasurementFamilyListValidator::class, $this->sut);
    }

    public function test_it_returns_the_errors_of_an_invalid_list_of_measurement_families(): void
    {
        $measurementFamilyList = [['not a object'], 'not an array'];
        $errors = $this->sut->validate($measurementFamilyList);
        $this->assertIsArray($errors);
        $this->assertCount(3, $errors);
    }

    public function test_it_returns_an_empty_array_if_the_list_of_measurement_families_is_valid(): void
    {
        $measurementFamilyList = [
                    [
                        'code' => 'kilogram',
                    ],
                    [
                        'code' => 'dyson',
                    ],
                ];
        $this->assertSame([], $this->sut->validate($measurementFamilyList));
    }
}

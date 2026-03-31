<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\MeasureBundle\Controller\ExternalApi\JsonSchema;

use Akeneo\Tool\Bundle\MeasureBundle\Controller\ExternalApi\JsonSchema\MeasurementFamilyCommonStructureValidator;
use PHPUnit\Framework\TestCase;

class MeasurementFamilyCommonStructureValidatorTest extends TestCase
{
    private MeasurementFamilyCommonStructureValidator $sut;

    protected function setUp(): void
    {
        $this->sut = new MeasurementFamilyCommonStructureValidator();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(MeasurementFamilyCommonStructureValidator::class, $this->sut);
    }

    public function test_it_returns_all_the_errors_of_invalid_measurement_family_properties(): void
    {
        $measurement = [
                    'values' => null,
                    'foo' => 'bar',
                ];
        $errors = $this->sut->validate($measurement);
        $this->assertIsArray($errors);
        $this->assertCount(1, $errors);
    }

    public function test_it_returns_an_empty_array_if_all_the_required_measurement_family_properties_are_valid(): void
    {
        $measurementFamily = [
                    'code' => 'custom_metric_1',
                ];
        $this->assertSame([], $this->sut->validate($measurementFamily));
    }
}

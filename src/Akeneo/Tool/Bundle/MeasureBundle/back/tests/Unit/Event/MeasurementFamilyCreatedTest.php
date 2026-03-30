<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\MeasureBundle\Event;

use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamilyCode;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Tool\Bundle\MeasureBundle\Event\MeasurementFamilyCreated;

class MeasurementFamilyCreatedTest extends TestCase
{
    private MeasurementFamilyCreated $sut;

    protected function setUp(): void
    {
    }

    public function test_it_is_created_with_a_measurement_family_code(): void
    {
        $measurementFamilyCode = MeasurementFamilyCode::fromString('weight');
        $this->sut = new MeasurementFamilyCreated($measurementFamilyCode);
        $this->assertSame($measurementFamilyCode, $this->sut->getMeasurementFamilyCode());
    }
}

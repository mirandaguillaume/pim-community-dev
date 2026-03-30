<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\MeasureBundle\Event;

use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamilyCode;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Tool\Bundle\MeasureBundle\Event\MeasurementFamilyUpdated;

class MeasurementFamilyUpdatedTest extends TestCase
{
    private MeasurementFamilyUpdated $sut;

    protected function setUp(): void
    {
    }

    public function test_it_is_created_with_a_measurement_family_code(): void
    {
        $measurementFamilyCode = MeasurementFamilyCode::fromString('weight');
        $this->sut = new MeasurementFamilyUpdated($measurementFamilyCode);
        $this->assertSame($measurementFamilyCode, $this->sut->getMeasurementFamilyCode());
    }
}

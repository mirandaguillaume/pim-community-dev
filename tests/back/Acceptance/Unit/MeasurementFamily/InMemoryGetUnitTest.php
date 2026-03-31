<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Akeneo\Test\Acceptance\MeasurementFamily;

use Akeneo\Test\Acceptance\MeasurementFamily\InMemoryGetUnit;
use Akeneo\Test\Acceptance\MeasurementFamily\InMemoryMeasurementFamilyRepository;
use Akeneo\Tool\Bundle\MeasureBundle\Exception\MeasurementFamilyNotFoundException;
use Akeneo\Tool\Bundle\MeasureBundle\Model\LabelCollection;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamily;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamilyCode;
use Akeneo\Tool\Bundle\MeasureBundle\Model\Operation;
use Akeneo\Tool\Bundle\MeasureBundle\Model\Unit;
use Akeneo\Tool\Bundle\MeasureBundle\Model\UnitCode;
use Akeneo\Tool\Bundle\MeasureBundle\ServiceApi\GetUnit;
use Akeneo\Tool\Bundle\MeasureBundle\ServiceApi\Unit as PublicUnit;
use PHPUnit\Framework\TestCase;

class InMemoryGetUnitTest extends TestCase
{
    private InMemoryGetUnit $sut;

    protected function setUp(): void
    {
        $this->sut = new InMemoryGetUnit();
    }

}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\MeasureBundle\Provider;

use Akeneo\Tool\Bundle\MeasureBundle\Model\LabelCollection;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamily;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamilyCode;
use Akeneo\Tool\Bundle\MeasureBundle\Model\Operation;
use Akeneo\Tool\Bundle\MeasureBundle\Model\Unit;
use Akeneo\Tool\Bundle\MeasureBundle\Model\UnitCode;
use Akeneo\Tool\Bundle\MeasureBundle\Persistence\MeasurementFamilyRepositoryInterface;
use Akeneo\Tool\Bundle\MeasureBundle\Provider\LegacyMeasurementAdapter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Tool\Bundle\MeasureBundle\Provider\LegacyMeasurementProvider;
use Symfony\Component\Yaml\Yaml;

class LegacyMeasurementProviderTest extends TestCase
{
    private MeasurementFamilyRepositoryInterface|MockObject $measurementFamilyRepository;
    private LegacyMeasurementAdapter|MockObject $legacyMeasurementAdapter;
    private LegacyMeasurementProvider $sut;

    protected function setUp(): void
    {
        $this->measurementFamilyRepository = $this->createMock(MeasurementFamilyRepositoryInterface::class);
        $this->legacyMeasurementAdapter = $this->createMock(LegacyMeasurementAdapter::class);
        $this->sut = new LegacyMeasurementProvider($this->measurementFamilyRepository, $this->legacyMeasurementAdapter);
    }

    public function test_it_returns_the_measurement_families(): void
    {
        $measurementFamily = MeasurementFamily::create(
            MeasurementFamilyCode::fromString('Area'),
            LabelCollection::fromArray(['en_US' => 'Area', 'fr_FR' => 'Surface']),
            UnitCode::fromString('SQUARE_MILLIMETER'),
            [
                        Unit::create(
                            UnitCode::fromString('SQUARE_MILLIMETER'),
                            LabelCollection::fromArray(['en_US' => 'Square millimeter', 'fr_FR' => 'Millimètre carré']),
                            [
                                Operation::create('mul', '1'),
                            ],
                            'mm²',
                        ),
                        Unit::create(
                            UnitCode::fromString('SQUARE_CENTIMETER'),
                            LabelCollection::fromArray(['en_US' => 'Square centimeter', 'fr_FR' => 'Centimètre carré']),
                            [Operation::create('mul', '0.0001'),Operation::create('add', '4'),],
                            'cm²',
                        ),
                    ]
        );
        $measurementFamilies = [$measurementFamily];
        $legacyMeasurements = ['legacy measurements'];
        $this->measurementFamilyRepository->method('all')->willReturn($measurementFamilies);
        $this->legacyMeasurementAdapter->method('adapts')->with($measurementFamily)->willReturn($legacyMeasurements);
        $this->assertSame($legacyMeasurements, $this->sut->getMeasurementFamilies());
    }
}

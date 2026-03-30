<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\MeasureBundle\Application\SaveMeasurementFamily;

use Akeneo\Tool\Bundle\MeasureBundle\Application\SaveMeasurementFamily\SaveMeasurementFamilyCommand;
use Akeneo\Tool\Bundle\MeasureBundle\Application\SaveMeasurementFamily\SaveMeasurementFamilyHandler;
use Akeneo\Tool\Bundle\MeasureBundle\Model\LabelCollection;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamily;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamilyCode;
use Akeneo\Tool\Bundle\MeasureBundle\Model\Operation;
use Akeneo\Tool\Bundle\MeasureBundle\Model\Unit;
use Akeneo\Tool\Bundle\MeasureBundle\Model\UnitCode;
use Akeneo\Tool\Bundle\MeasureBundle\Persistence\MeasurementFamilyRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Webmozart\Assert\Assert;

/**
 * @author    Valentin Dijkstra <valentin.dijkstra@akeneo.com>
 * @copyright 2020 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class SaveMeasurementFamilyHandlerTest extends TestCase
{
    private MeasurementFamilyRepositoryInterface|MockObject $measurementFamilyRepository;
    private EventDispatcherInterface|MockObject $eventDispatcher;
    private SaveMeasurementFamilyHandler $sut;

    protected function setUp(): void
    {
        $this->measurementFamilyRepository = $this->createMock(MeasurementFamilyRepositoryInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->sut = new SaveMeasurementFamilyHandler($this->measurementFamilyRepository, $this->eventDispatcher);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(SaveMeasurementFamilyHandler::class, $this->sut);
    }

    public function test_it_creates_and_saves_a_new_measurement_family(): void
    {
        $saveMeasurementFamilyCommand = $this->createMock(SaveMeasurementFamilyCommand::class);

        $saveMeasurementFamilyCommand->code = 'Area';
        $saveMeasurementFamilyCommand->labels = ['en_US' => 'Area', 'fr_FR' => 'Surface'];
        $saveMeasurementFamilyCommand->standardUnitCode = 'SQUARE_MILLIMETER';
        $saveMeasurementFamilyCommand->units = [
                    [
                        'code' => 'SQUARE_MILLIMETER',
                        'labels' => ['en_US' => 'Square millimeter', 'fr_FR' => 'Millimètre carré'],
                        'convert_from_standard' => [[
                            'operator' => 'mul',
                            'value' => '1',
                        ]],
                        'symbol' => 'mm²',
                    ],
                    [
                        'code' => 'SQUARE_CENTIMETER',
                        'labels' => ['en_US' => 'Square centimeter', 'fr_FR' => 'Centimètre carré'],
                        'convert_from_standard' => [[
                            'operator' => 'mul',
                            'value' => '0.0001',
                        ]],
                        'symbol' => 'cm²',
                    ],
                ];
        $expectedArea = MeasurementFamily::create(
            MeasurementFamilyCode::fromString('Area'),
            LabelCollection::fromArray(['en_US' => 'Area', 'fr_FR' => 'Surface']),
            UnitCode::fromString('SQUARE_MILLIMETER'),
            [
                        Unit::create(
                            UnitCode::fromString('SQUARE_MILLIMETER'),
                            LabelCollection::fromArray(['en_US' => 'Square millimeter', 'fr_FR' => 'Millimètre carré']),
                            [Operation::create('mul', '1')],
                            'mm²',
                        ),
                        Unit::create(
                            UnitCode::fromString('SQUARE_CENTIMETER'),
                            LabelCollection::fromArray(['en_US' => 'Square centimeter', 'fr_FR' => 'Centimètre carré']),
                            [Operation::create('mul', '0.0001')],
                            'cm²',
                        ),
                    ]
        );
        $this->measurementFamilyRepository->expects($this->once())->method('save')->with($this->callback(function ($area) use ($expectedArea): bool {
            Assert::eq($expectedArea, $area);
            return true;
        }));
        $this->sut->handle($saveMeasurementFamilyCommand);
    }
}

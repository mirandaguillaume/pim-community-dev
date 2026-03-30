<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Analytics;

use Akeneo\Tool\Component\Analytics\ChainedDataCollector;
use Akeneo\Tool\Component\Analytics\DataCollectorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ChainedDataCollectorTest extends TestCase
{
    private ChainedDataCollector $sut;

    protected function setUp(): void
    {
        $this->sut = new ChainedDataCollector();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ChainedDataCollector::class, $this->sut);
    }

    public function test_it_aggregates_data_from_registered_collectors(): void
    {
        $collectorOne = $this->createMock(DataCollectorInterface::class);
        $collectorTwo = $this->createMock(DataCollectorInterface::class);
        $collectorThree = $this->createMock(DataCollectorInterface::class);
        $defaultTypeCollector = $this->createMock(DataCollectorInterface::class);

        $collectorOne->method('collect')->willReturn(['data_one' => 'one']);
        $collectorTwo->method('collect')->willReturn(['data_two' => 'two', 'data_three' => 'three']);
        $collectorThree->method('collect')->willReturn(['data_four' => 'four']);
        $defaultTypeCollector->method('collect')->willReturn(['data_five' => 'five']);
        $this->sut->addCollector($collectorOne, 'type1');
        $this->sut->addCollector($collectorTwo, 'type2');
        $this->sut->addCollector($collectorThree, 'type2');
        $this->sut->addCollector($defaultTypeCollector);
        $this->assertSame(['data_one' => 'one'], $this->sut->collect('type1'));
        $this->assertSame(['data_two' => 'two', 'data_three' => 'three', 'data_four' => 'four'], $this->sut->collect('type2'));
        $this->assertSame(['data_five' => 'five'], $this->sut->collect(ChainedDataCollector::DEFAULT_COLLECTOR_TYPE));
    }
}

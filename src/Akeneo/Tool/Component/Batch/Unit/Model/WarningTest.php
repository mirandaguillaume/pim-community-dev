<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Batch\Model;

use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Model\Warning;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class WarningTest extends TestCase
{
    private StepExecution|MockObject $stepExecution;
    private Warning $sut;

    protected function setUp(): void
    {
        $this->stepExecution = $this->createMock(StepExecution::class);
        $this->sut = new Warning(
            $this->stepExecution,
            'my reason',
            ['myparam' => 'mavalue'],
            ['myitem' => 'myvalue']
        );
    }

    public function test_it_provides_a_step_execution(): void
    {
        $this->assertSame($this->stepExecution, $this->sut->getStepExecution());
    }

    public function test_it_provides_array_format(): void
    {
        $this->assertSame([
                        'reason' => 'my reason',
                        'reasonParameters' => [
                            'myparam' => 'mavalue',
                        ],
                        'item' => [
                            'myitem' => 'myvalue',
                        ],
                    ], $this->sut->toArray());
    }
}

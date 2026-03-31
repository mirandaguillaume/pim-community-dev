<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\Command\MigrateToUuid\Utils;

use Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid\MigrateToUuidStep;
use Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid\Utils\LogContext;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LogContextTest extends TestCase
{
    private MigrateToUuidStep|MockObject $step;
    private LogContext $sut;

    protected function setUp(): void
    {
        $this->step = $this->createMock(MigrateToUuidStep::class);
        $this->sut = new LogContext($this->step);
        $this->step->method('getName')->willReturn('myStepName');
        $this->step->method('getStatus')->willReturn('myStepStatus');
        $this->step->method('getDuration')->willReturn(5.123);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(LogContext::class, $this->sut);
    }
}

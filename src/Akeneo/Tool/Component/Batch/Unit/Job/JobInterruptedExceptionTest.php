<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Batch\Job;

use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Job\JobInterruptedException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class JobInterruptedExceptionTest extends TestCase
{
    private JobInterruptedException $sut;

    protected function setUp(): void
    {
    }

    public function test_it_provides_the_original_status_when_built_with_this_status(): void
    {
        $status = $this->createMock(BatchStatus::class);

        $this->sut = new JobInterruptedException(
            'my_job_interupted_exception',
            0,
            null,
            $status
        );
        $this->assertSame($status, $this->sut->getStatus());
    }

    public function test_it_provides_a_stopped_status_when_built_without_any_status(): void
    {
        $this->sut = new JobInterruptedException(
            'my_job_interupted_exception',
            0,
            null
        );
        $this->assertInstanceOf(BatchStatus::class, $this->sut->getStatus());
        $this->assertSame(BatchStatus::STOPPED, $this->sut->getStatus()->getValue());
    }
}

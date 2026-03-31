<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\BatchQueue\Queue;

use Akeneo\Tool\Component\BatchQueue\Queue\JobExecutionMessageInterface;
use Akeneo\Tool\Component\BatchQueue\Queue\UiJobExecutionMessage;
use PHPUnit\Framework\TestCase;

class UiJobExecutionMessageTest extends TestCase
{
    private UiJobExecutionMessage $sut;

    protected function setUp(): void
    {
        $this->sut = UiJobExecutionMessage::createJobExecutionMessage(
            1,
            ['option1' => 'value1'],
        );
    }

    public function test_it_is_a_job_message(): void
    {
        $this->assertInstanceOf(JobExecutionMessageInterface::class, $this->sut);
    }
}

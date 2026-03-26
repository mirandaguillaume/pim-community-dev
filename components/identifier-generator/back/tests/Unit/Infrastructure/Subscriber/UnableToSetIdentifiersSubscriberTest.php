<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Unit\Infrastructure\Subscriber;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception\UnableToSetIdentifierException;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Validation\Error;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Validation\ErrorList;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Event\UnableToSetIdentifierEvent;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Subscriber\UnableToSetIdentifiersSubscriber;
use Akeneo\Tool\Component\Batch\Event\StepExecutionEvent;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Model\Warning;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UnableToSetIdentifiersSubscriberTest extends TestCase
{
    private JobRepositoryInterface|MockObject $jobRepository;
    private UnableToSetIdentifiersSubscriber $sut;

    protected function setUp(): void
    {
        $this->jobRepository = $this->createMock(JobRepositoryInterface::class);
        $this->sut = new UnableToSetIdentifiersSubscriber($this->jobRepository);
    }

    public function test_it_should_write_warnings(): void
    {
        $stepExecutionEvent = $this->createMock(StepExecutionEvent::class);
        $stepExecution = $this->createMock(StepExecution::class);

        $stepExecutionEvent->method('getStepExecution')->willReturn($stepExecution);
        $this->jobRepository->expects($this->once())->method('addWarnings')->with(
            $stepExecution,
            $this->callback(function (array $warnings) use ($stepExecution): bool {
                if (\count($warnings) !== 2) {
                    return false;
                }
                $this->assertInstanceOf(Warning::class, $warnings[0]);
                $this->assertInstanceOf(Warning::class, $warnings[1]);

                return true;
            })
        );
        $this->sut->storeEvent(new UnableToSetIdentifierEvent(new UnableToSetIdentifierException(
            'AKN-2000',
            'sku', new ErrorList([new Error('Error 1')])
        )));
        $this->sut->storeEvent(new UnableToSetIdentifierEvent(new UnableToSetIdentifierException(
            'TOTO-2012',
            'ean', new ErrorList([new Error('Error 2')])
        )));
        $this->sut->writeWarnings($stepExecutionEvent);
    }
}

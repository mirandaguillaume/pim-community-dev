<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Connector\Step;

use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Connector\Item\CharsetValidator;
use Akeneo\Tool\Component\Connector\Step\ValidatorStep;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ValidatorStepTest extends TestCase
{
    private EventDispatcherInterface|MockObject $dispatcher;
    private JobRepositoryInterface|MockObject $jobRepository;
    private CharsetValidator|MockObject $validator;
    private ValidatorStep $sut;

    protected function setUp(): void
    {
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->jobRepository = $this->createMock(JobRepositoryInterface::class);
        $this->validator = $this->createMock(CharsetValidator::class);
        $this->sut = new ValidatorStep('aName', $this->dispatcher, $this->jobRepository, $this->validator);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf('\\' . \Akeneo\Tool\Component\Connector\Step\ValidatorStep::class, $this->sut);
    }

    public function test_it_is_a_step(): void
    {
        $this->assertInstanceOf('\\' . \Akeneo\Tool\Component\Batch\Step\StepInterface::class, $this->sut);
        $this->assertInstanceOf('\\' . \Akeneo\Tool\Component\Batch\Step\AbstractStep::class, $this->sut);
    }
}

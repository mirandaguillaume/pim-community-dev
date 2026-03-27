<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Infrastructure\Job;

use Akeneo\Category\Application\Command\CleanCategoryEnrichedValuesByChannelOrLocale\CleanCategoryEnrichedValuesByChannelOrLocaleCommand;
use Akeneo\Category\Infrastructure\Bus\CommandBus;
use Akeneo\Category\Infrastructure\Job\CleanCategoriesEnrichedValuesTasklet;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;

class CleanCategoriesEnrichedValuesTaskletTest extends TestCase
{
    private CommandBus|MockObject $commandBus;
    private CleanCategoriesEnrichedValuesTasklet $sut;

    protected function setUp(): void
    {
        $this->commandBus = $this->createMock(CommandBus::class);
        $this->sut = new CleanCategoriesEnrichedValuesTasklet($this->commandBus);
    }

    public function testItIsInitializable(): void
    {
        $this->assertInstanceOf(TaskletInterface::class, $this->sut);
        $this->assertInstanceOf(CleanCategoriesEnrichedValuesTasklet::class, $this->sut);
    }

    public function testItDispatchesACommandMessageToCleanCategoryEnrichedValuesByChannelOrLocale(): void
    {
        $stepExecution = $this->createMock(StepExecution::class);

        $jobParameters = new JobParameters([
            'channel_code' => 'ecommerce',
            'locales_codes' => ['en_US', 'fr_FR'],
        ]);
        $command = new CleanCategoryEnrichedValuesByChannelOrLocaleCommand(
            'ecommerce',
            ['en_US', 'fr_FR'],
        );
        $envelope = new Envelope($command);
        $stepExecution->method('getJobParameters')->willReturn($jobParameters);
        $this->commandBus->expects($this->once())->method('dispatch')->with($command)->willReturn($envelope);
        $this->sut->setStepExecution($stepExecution);
        $this->sut->execute();
    }
}

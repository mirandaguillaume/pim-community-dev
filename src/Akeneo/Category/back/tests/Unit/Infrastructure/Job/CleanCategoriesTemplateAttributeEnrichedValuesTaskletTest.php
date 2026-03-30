<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Infrastructure\Job;

use Akeneo\Category\Application\Command\CleanCategoryTemplateAttributeAndEnrichedValues\CleanCategoryTemplateAttributeAndEnrichedValuesCommand;
use Akeneo\Category\Infrastructure\Bus\CommandBus;
use Akeneo\Category\Infrastructure\Job\CleanCategoriesTemplateAttributeEnrichedValuesTasklet;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CleanCategoriesTemplateAttributeEnrichedValuesTaskletTest extends TestCase
{
    private CommandBus|MockObject $commandBus;
    private CleanCategoriesTemplateAttributeEnrichedValuesTasklet $sut;

    protected function setUp(): void
    {
        $this->commandBus = $this->createMock(CommandBus::class);
        $this->sut = new CleanCategoriesTemplateAttributeEnrichedValuesTasklet($this->commandBus);
    }

    public function testItIsInitializable(): void
    {
        $this->assertInstanceOf(TaskletInterface::class, $this->sut);
        $this->assertInstanceOf(CleanCategoriesTemplateAttributeEnrichedValuesTasklet::class, $this->sut);
    }

    public function testItDispatchesACommandMessageToCleanCategoryEnrichedValuesByTemplateUuid(): void
    {
        $stepExecution = $this->createMock(StepExecution::class);

        $templateUuid = '2115af0f-f0b0-435e-aa86-9880eaad677e';
        $attributeUuid = 'c87c8b3c-5642-425c-a3b7-8dd5bc503e67';
        $jobParameters = new JobParameters([
            'template_uuid' => $templateUuid,
            'attribute_uuid' => $attributeUuid,
        ]);
        $command = new CleanCategoryTemplateAttributeAndEnrichedValuesCommand(
            $templateUuid,
            $attributeUuid,
        );
        $envelope = new Envelope($command);
        $stepExecution->expects($this->once())->method('getJobParameters')->willReturn($jobParameters);
        $this->commandBus->expects($this->once())->method('dispatch')->with($command)->willReturn($envelope);
        $this->sut->setStepExecution($stepExecution);
        $this->sut->execute();
    }
}

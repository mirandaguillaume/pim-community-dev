<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Attribute\Job;

use Akeneo\Pim\Structure\Component\Attribute\Job\DeleteAttributesTasklet;
use Akeneo\Pim\Structure\Component\Exception\CannotRemoveAttributeException;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Item\TrackableTaskletInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\SearchableRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class DeleteAttributesTaskletTest extends TestCase
{
    private SearchableRepositoryInterface|MockObject $attributeRepository;
    private RemoverInterface|MockObject $attributeRemover;
    private TranslatorInterface|MockObject $translator;
    private DeleteAttributesTasklet $sut;

    protected function setUp(): void
    {
        $this->attributeRepository = $this->createMock(SearchableRepositoryInterface::class);
        $this->attributeRemover = $this->createMock(RemoverInterface::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->sut = new DeleteAttributesTasklet($this->attributeRepository,
            $this->attributeRemover,
            $this->translator,);
    }

    public function test_it_is_a_tasklet(): void
    {
        $this->assertInstanceOf(DeleteAttributesTasklet::class, $this->sut);
        $this->assertInstanceOf(TaskletInterface::class, $this->sut);
    }

    public function test_it_track_processed_items(): void
    {
        $this->assertInstanceOf(TrackableTaskletInterface::class, $this->sut);
        $this->assertSame(true, $this->sut->isTrackable());
    }

    public function test_it_throws_an_exception_if_step_execution_is_not_set(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->execute();
    }

    public function test_it_deletes_attributes(): void
    {
        $stepExecution = $this->createMock(StepExecution::class);
        $jobParameters = $this->createMock(JobParameters::class);

        $this->sut->setStepExecution($stepExecution);
        $filters = [
            'search' => 'attribute',
            'options' => [],
        ];
        $attribute1 = new Attribute();
        $attribute2 = new Attribute();
        $attribute3 = new Attribute();
        $stepExecution->method('getJobParameters')->willReturn($jobParameters);
        $jobParameters->method('get')->with('filters')->willReturn($filters);
        $this->attributeRepository->method('findBySearch')
            ->with('attribute', [])
            ->willReturn([$attribute1, $attribute2, $attribute3]);
        $stepExecution->expects($this->once())->method('setTotalItems')->with(3);

        $addSummaryInfoCalls = [];
        $stepExecution->expects($this->exactly(2))->method('addSummaryInfo')
            ->willReturnCallback(function (string $key, int $value) use (&$addSummaryInfoCalls) {
                $addSummaryInfoCalls[] = [$key, $value];
            });

        $this->attributeRemover->expects($this->exactly(3))->method('remove');
        $stepExecution->expects($this->exactly(3))->method('incrementSummaryInfo')->with('deleted_attributes');
        $stepExecution->expects($this->exactly(3))->method('incrementProcessedItems');
        $this->sut->execute();

        $this->assertContains(['deleted_attributes', 0], $addSummaryInfoCalls);
        $this->assertContains(['skipped_attributes', 0], $addSummaryInfoCalls);
    }

    public function test_it_catches_attribute_removal_exceptions(): void
    {
        $stepExecution = $this->createMock(StepExecution::class);
        $jobParameters = $this->createMock(JobParameters::class);
        $attribute1 = $this->createMock(Attribute::class);

        $this->sut->setStepExecution($stepExecution);
        $filters = [
            'search' => 'a',
            'options' => [],
        ];
        $attribute1->method('getCode')->willReturn('attribute_1');
        $stepExecution->method('getJobParameters')->willReturn($jobParameters);
        $jobParameters->method('get')->with('filters')->willReturn($filters);
        $this->attributeRepository->method('findBySearch')
            ->with('a', [])
            ->willReturn([$attribute1]);
        $stepExecution->expects($this->once())->method('setTotalItems')->with(1);

        $addSummaryInfoCalls = [];
        $stepExecution->expects($this->exactly(2))->method('addSummaryInfo')
            ->willReturnCallback(function (string $key, int $value) use (&$addSummaryInfoCalls) {
                $addSummaryInfoCalls[] = [$key, $value];
            });

        $this->attributeRemover->method('remove')
            ->with($attribute1)
            ->willThrowException(new CannotRemoveAttributeException('an error'));
        $this->translator->method('trans')->with('an error', [])->willReturn('an error');
        $stepExecution->expects($this->once())->method('addWarning');
        $stepExecution->expects($this->once())->method('incrementSummaryInfo')->with('skipped_attributes');
        $stepExecution->expects($this->once())->method('incrementProcessedItems');
        $this->sut->execute();

        $this->assertContains(['deleted_attributes', 0], $addSummaryInfoCalls);
        $this->assertContains(['skipped_attributes', 0], $addSummaryInfoCalls);
    }
}

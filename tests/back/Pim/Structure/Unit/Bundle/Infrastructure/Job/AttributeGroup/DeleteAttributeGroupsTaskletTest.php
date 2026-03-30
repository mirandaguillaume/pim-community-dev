<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Bundle\Infrastructure\Job\AttributeGroup;

use Akeneo\Pim\Structure\Bundle\Infrastructure\Job\AttributeGroup\DeleteAttributeGroupsTasklet;
use Akeneo\Pim\Structure\Component\Exception\AttributeGroupOtherCannotBeRemoved;
use Akeneo\Pim\Structure\Component\Model\AttributeGroup;
use Akeneo\Pim\Structure\Component\Repository\AttributeGroupRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Item\TrackableTaskletInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Job\JobStopper;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DeleteAttributeGroupsTaskletTest extends TestCase
{
    private AttributeGroupRepositoryInterface|MockObject $attributeGroupRepository;
    private RemoverInterface|MockObject $attributeGroupRemover;
    private EntityManagerClearerInterface|MockObject $cacheClearer;
    private JobRepositoryInterface|MockObject $jobRepository;
    private JobStopper|MockObject $jobStopper;
    private StepExecution|MockObject $stepExecution;
    private JobParameters|MockObject $jobParameters;
    private DeleteAttributeGroupsTasklet $sut;

    protected function setUp(): void
    {
        $this->attributeGroupRepository = $this->createMock(AttributeGroupRepositoryInterface::class);
        $this->attributeGroupRemover = $this->createMock(RemoverInterface::class);
        $this->cacheClearer = $this->createMock(EntityManagerClearerInterface::class);
        $this->jobRepository = $this->createMock(JobRepositoryInterface::class);
        $this->jobStopper = $this->createMock(JobStopper::class);
        $this->stepExecution = $this->createMock(StepExecution::class);
        $this->jobParameters = $this->createMock(JobParameters::class);
        $this->sut = new DeleteAttributeGroupsTasklet($this->attributeGroupRepository,
            $this->attributeGroupRemover,
            $this->cacheClearer,
            $this->jobRepository,
            $this->jobStopper,
            3);
        $this->stepExecution->method('getJobParameters')->willReturn($this->jobParameters);
        $this->jobStopper->method('isStopping')->with($this->stepExecution)->willReturn(false);
        $this->sut->setStepExecution($this->stepExecution);
    }

    public function test_it_is_a_tasklet(): void
    {
        $this->assertInstanceOf(DeleteAttributeGroupsTasklet::class, $this->sut);
        $this->assertInstanceOf(TaskletInterface::class, $this->sut);
    }

    public function test_it_track_processed_items(): void
    {
        $this->assertInstanceOf(TrackableTaskletInterface::class, $this->sut);
        $this->assertSame(true, $this->sut->isTrackable());
    }

    public function test_it_deletes_attribute_groups(): void
    {
        $filters = [
                    'codes' => ['attribute_group_1', 'attribute_group_2', 'attribute_group_3'],
                ];
        $attributeGroup1 = new AttributeGroup();
        $attributeGroup2 = new AttributeGroup();
        $attributeGroup3 = new AttributeGroup();
        $this->stepExecution->method('getJobParameters')->willReturn($this->jobParameters);
        $this->jobParameters->method('get')->with('filters')->willReturn($filters);
        $this->attributeGroupRepository->method('findBy')->with(['code' => ['attribute_group_1', 'attribute_group_2', 'attribute_group_3']])->willReturn([$attributeGroup1, $attributeGroup2, $attributeGroup3]);
        $this->stepExecution->expects($this->once())->method('setTotalItems')->with(3);
        $this->stepExecution->expects($this->once())->method('addSummaryInfo')->with('deleted_attribute_groups', 0);
        $this->stepExecution->expects($this->once())->method('addSummaryInfo')->with('skipped_attribute_groups', 0);
        $this->attributeGroupRemover->expects($this->once())->method('remove')->with($attributeGroup1);
        $this->stepExecution->expects($this->once())->method('incrementSummaryInfo')->with('deleted_attribute_groups');
        $this->stepExecution->expects($this->once())->method('incrementProcessedItems');
        $this->attributeGroupRemover->expects($this->once())->method('remove')->with($attributeGroup2);
        $this->stepExecution->expects($this->once())->method('incrementSummaryInfo')->with('deleted_attribute_groups');
        $this->stepExecution->expects($this->once())->method('incrementProcessedItems');
        $this->attributeGroupRemover->expects($this->once())->method('remove')->with($attributeGroup3);
        $this->stepExecution->expects($this->once())->method('incrementSummaryInfo')->with('deleted_attribute_groups');
        $this->stepExecution->expects($this->once())->method('incrementProcessedItems');
        $this->sut->execute();
    }

    public function test_it_catches_attribute_group_removal_exceptions(): void
    {
        $filters = ['codes' => ['attribute_group_1']];
        $attributeGroup = new AttributeGroup();
        $attributeGroup->setCode('attribute_group_1');
        $this->jobParameters->method('get')->with('filters')->willReturn($filters);
        $this->attributeGroupRepository->method('findBy')->with(['code' => ['attribute_group_1']])->willReturn([$attributeGroup]);
        $this->stepExecution->expects($this->once())->method('setTotalItems')->with(1);
        $this->stepExecution->expects($this->once())->method('addSummaryInfo')->with('deleted_attribute_groups', 0);
        $this->stepExecution->expects($this->once())->method('addSummaryInfo')->with('skipped_attribute_groups', 0);
        $this->attributeGroupRemover->method('remove')->with($attributeGroup)->willThrowException(AttributeGroupOtherCannotBeRemoved::create());
        $this->stepExecution->expects($this->once())->method('addWarning')->with('pim_enrich.attribute_group.remove.attribute_group_other_cannot_be_removed',
                    [],
                    new DataInvalidItem(['code' => 'attribute_group_1']));
        $this->stepExecution->expects($this->once())->method('incrementSummaryInfo')->with('skipped_attribute_groups');
        $this->stepExecution->expects($this->once())->method('incrementProcessedItems');
        $this->sut->execute();
    }

    public function test_it_batch_attribute_group_deletion(): void
    {
        $filters = ['codes' => ['attribute_group_1', 'attribute_group_2', 'attribute_group_3', 'attribute_group_4']];
        $attributeGroup1 = new AttributeGroup();
        $attributeGroup2 = new AttributeGroup();
        $attributeGroup3 = new AttributeGroup();
        $attributeGroup4 = new AttributeGroup();
        $this->jobParameters->method('get')->with('filters')->willReturn($filters);
        $this->attributeGroupRepository->expects($this->once())->method('findBy')->with(['code' => ['attribute_group_1', 'attribute_group_2', 'attribute_group_3']])->willReturn([$attributeGroup1, $attributeGroup2, $attributeGroup3]);
        $this->attributeGroupRepository->expects($this->once())->method('findBy')->with(['code' => ['attribute_group_4']])->willReturn([$attributeGroup4]);
        $this->stepExecution->expects($this->once())->method('setTotalItems')->with(4);
        $this->stepExecution->expects($this->once())->method('addSummaryInfo')->with('deleted_attribute_groups', 0);
        $this->stepExecution->expects($this->once())->method('addSummaryInfo')->with('skipped_attribute_groups', 0);
        $this->attributeGroupRemover->expects($this->once())->method('remove')->with($attributeGroup1);
        $this->attributeGroupRemover->expects($this->once())->method('remove')->with($attributeGroup2);
        $this->attributeGroupRemover->expects($this->once())->method('remove')->with($attributeGroup3);
        $this->attributeGroupRemover->expects($this->once())->method('remove')->with($attributeGroup4);
        $this->stepExecution->expects($this->never())->method('incrementSummaryInfo')->with('skipped_attribute_groups');
        $this->stepExecution->expects($this->exactly(4))->method('incrementSummaryInfo')->with('deleted_attribute_groups');
        $this->stepExecution->expects($this->exactly(4))->method('incrementProcessedItems');
        $this->sut->execute();
    }
}

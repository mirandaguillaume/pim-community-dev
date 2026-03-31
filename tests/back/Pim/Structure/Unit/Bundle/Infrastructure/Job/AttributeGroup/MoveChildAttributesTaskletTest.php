<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Bundle\Infrastructure\Job\AttributeGroup;

use Akeneo\Pim\Structure\Bundle\Infrastructure\Job\AttributeGroup\MoveChildAttributesTasklet;
use Akeneo\Pim\Structure\Component\Exception\UserFacingError;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Item\TrackableTaskletInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Job\JobStopper;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MoveChildAttributesTaskletTest extends TestCase
{
    private AttributeRepositoryInterface|MockObject $attributeRepository;
    private ObjectUpdaterInterface|MockObject $attributeUpdater;
    private BulkSaverInterface|MockObject $attributeSaver;
    private EntityManagerClearerInterface|MockObject $cacheClearer;
    private JobRepositoryInterface|MockObject $jobRepository;
    private JobStopper|MockObject $jobStopper;
    private StepExecution|MockObject $stepExecution;
    private JobParameters|MockObject $jobParameters;
    private MoveChildAttributesTasklet $sut;

    protected function setUp(): void
    {
        $this->attributeRepository = $this->createMock(AttributeRepositoryInterface::class);
        $this->attributeUpdater = $this->createMock(ObjectUpdaterInterface::class);
        $this->attributeSaver = $this->createMock(BulkSaverInterface::class);
        $this->cacheClearer = $this->createMock(EntityManagerClearerInterface::class);
        $this->jobRepository = $this->createMock(JobRepositoryInterface::class);
        $this->jobStopper = $this->createMock(JobStopper::class);
        $this->stepExecution = $this->createMock(StepExecution::class);
        $this->jobParameters = $this->createMock(JobParameters::class);
        $this->sut = new MoveChildAttributesTasklet($this->attributeRepository,
            $this->attributeUpdater,
            $this->attributeSaver,
            $this->cacheClearer,
            $this->jobRepository,
            $this->jobStopper,
            3);
        $this->stepExecution->method('getJobParameters')->willReturn($this->jobParameters);
        $this->jobStopper->method('isStopping')->willReturn(false);
        $this->sut->setStepExecution($this->stepExecution);
    }

    public function test_it_is_a_tasklet(): void
    {
        $this->assertInstanceOf(MoveChildAttributesTasklet::class, $this->sut);
        $this->assertInstanceOf(TaskletInterface::class, $this->sut);
    }

    public function test_it_track_processed_items(): void
    {
        $this->assertInstanceOf(TrackableTaskletInterface::class, $this->sut);
        $this->assertSame(true, $this->sut->isTrackable());
    }

    public function test_it_move_attributes(): void
    {
        $filters = [
            'codes' => ['attribute_group_1', 'attribute_group_2'],
        ];
        $replacementAttributeGroupCode = 'attribute_group_3';

        $this->jobParameters->method('get')
            ->willReturnCallback(function (string $key) use ($filters, $replacementAttributeGroupCode) {
                return match ($key) {
                    'filters' => $filters,
                    'replacement_attribute_group_code' => $replacementAttributeGroupCode,
                };
            });

        $attribute1 = new Attribute();
        $attribute1->setCode('attribute_1');
        $attribute2 = new Attribute();
        $attribute2->setCode('attribute_2');
        $attribute3 = new Attribute();
        $attribute3->setCode('attribute_3');

        $getAttributesCalls = 0;
        $this->attributeRepository->method('getAttributesByGroups')
            ->willReturnCallback(function () use (&$getAttributesCalls, $attribute1, $attribute2, $attribute3) {
                $getAttributesCalls++;
                if ($getAttributesCalls === 1) {
                    return [$attribute1, $attribute2, $attribute3];
                }
                return [];
            });

        $this->stepExecution->expects($this->once())->method('addSummaryInfo')->with('moved_attributes', 0);

        $isFirstCall = true;
        $this->attributeUpdater->expects($this->exactly(3))->method('update')
            ->willReturnCallback(function ($attribute, $data) use (&$isFirstCall) {
                $this->assertSame(['group' => 'attribute_group_3'], $data);
                if ($isFirstCall) {
                    $isFirstCall = false;
                    throw new class ('an_error', []) extends UserFacingError {
                        public function __construct(private readonly string $key, private readonly array $params) {
                            parent::__construct($key);
                        }
                        public function translationKey(): string { return $this->key; }
                        public function translationParameters(): array { return $this->params; }
                    };
                }
            });

        $this->stepExecution->expects($this->exactly(3))->method('incrementProcessedItems');
        $this->stepExecution->expects($this->exactly(2))->method('incrementSummaryInfo')->with('moved_attributes');
        $this->stepExecution->expects($this->once())->method('addWarning');
        $this->attributeSaver->expects($this->once())->method('saveAll');
        $this->sut->execute();
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\BatchQueue\Factory;

use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\BatchQueue\Factory\JobExecutionMessageFactory;
use Akeneo\Tool\Component\BatchQueue\Queue\DataMaintenanceJobExecutionMessage;
use Akeneo\Tool\Component\BatchQueue\Queue\ExportJobExecutionMessage;
use Akeneo\Tool\Component\BatchQueue\Queue\ImportJobExecutionMessage;
use Akeneo\Tool\Component\BatchQueue\Queue\UiJobExecutionMessage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class JobExecutionMessageFactoryTest extends TestCase
{
    private JobExecutionMessageFactory $sut;

    protected function setUp(): void
    {
        $this->sut = new JobExecutionMessageFactory(
            [
                UiJobExecutionMessage::class => ['mass_edit', 'mass_delete'],
                ImportJobExecutionMessage::class => ['import'],
                ExportJobExecutionMessage::class => ['export', 'quick_export'],
            ],
            DataMaintenanceJobExecutionMessage::class
        );
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(JobExecutionMessageFactory::class, $this->sut);
    }

    public function test_it_builds_an_ui_job_execution_message(): void
    {
        $jobInstance = $this->createMock(JobInstance::class);

        $jobInstance->method('getType')->willReturn('mass_delete');
        $jobExecutionMessage = $this->sut->buildFromJobInstance($jobInstance, 1, []);
        $this->assertInstanceOf(UiJobExecutionMessage::class, $jobExecutionMessage);
        $this->assertSame(1, $jobExecutionMessage->getJobExecutionId());
        $this->assertNull($jobExecutionMessage->getTenantId());
    }

    public function test_it_builds_an_export_job_execution_message(): void
    {
        $jobInstance = $this->createMock(JobInstance::class);

        $jobInstance->method('getType')->willReturn('quick_export');
        $jobExecutionMessage = $this->sut->buildFromJobInstance($jobInstance, 2, []);
        $this->assertInstanceOf(ExportJobExecutionMessage::class, $jobExecutionMessage);
        $this->assertSame(2, $jobExecutionMessage->getJobExecutionId());
    }

    public function test_it_builds_a_backend_job_execution_message(): void
    {
        $jobInstance = $this->createMock(JobInstance::class);

        $jobInstance->method('getType')->willReturn('other');
        $jobExecutionMessage = $this->sut->buildFromJobInstance($jobInstance, 3, []);
        $this->assertInstanceOf(DataMaintenanceJobExecutionMessage::class, $jobExecutionMessage);
        $this->assertSame(3, $jobExecutionMessage->getJobExecutionId());
    }

    public function test_it_builds_an_ui_job_execution_message_from_normalized(): void
    {
        $jobExecutionMessage = $this->sut->buildFromNormalized(
            [
                        'id' => '30e8008d-48dc-4430-97e1-6f67a5c420e9',
                        'job_execution_id' => 10,
                        'created_time' => '2021-03-08T15:37:23+01:00',
                        'updated_time' => null,
                        'options' => ['option1' => 'value1'],
                    ],
            UiJobExecutionMessage::class
        );
        $this->assertInstanceOf(UiJobExecutionMessage::class, $jobExecutionMessage);
        $this->assertEquals(Uuid::fromString('30e8008d-48dc-4430-97e1-6f67a5c420e9'), $jobExecutionMessage->getId());
        $this->assertSame(10, $jobExecutionMessage->getJobExecutionId());
        $this->assertEquals(new \DateTime('2021-03-08T15:37:23+01:00'), $jobExecutionMessage->getCreateTime());
        $this->assertNull($jobExecutionMessage->getUpdatedTime());
        $this->assertSame(['option1' => 'value1'], $jobExecutionMessage->getOptions());
    }

    public function test_it_builds_an_import_job_execution_message_from_normalized(): void
    {
        $jobExecutionMessage = $this->sut->buildFromNormalized(
            [
                        'id' => 'a57380fc-ee3b-4bd2-94e6-c3ead13c32a7',
                        'job_execution_id' => 10,
                        'created_time' => '2021-03-08T15:37:23+01:00',
                        'updated_time' => '2021-03-09T15:37:23+01:00',
                        'options' => ['option1' => 'value1'],
                    ],
            ImportJobExecutionMessage::class
        );
        $this->assertInstanceOf(ImportJobExecutionMessage::class, $jobExecutionMessage);
        $this->assertEquals(Uuid::fromString('a57380fc-ee3b-4bd2-94e6-c3ead13c32a7'), $jobExecutionMessage->getId());
        $this->assertSame(10, $jobExecutionMessage->getJobExecutionId());
        $this->assertEquals(new \DateTime('2021-03-08T15:37:23+01:00'), $jobExecutionMessage->getCreateTime());
        $this->assertEquals(new \DateTime('2021-03-09T15:37:23+01:00'), $jobExecutionMessage->getUpdatedTime());
        $this->assertSame(['option1' => 'value1'], $jobExecutionMessage->getOptions());
    }

    public function test_it_builds_a_backend_job_execution_message_from_normalized(): void
    {
        $jobExecutionMessage = $this->sut->buildFromNormalized(
            [
                        'id' => 'a57380fc-ee3b-4bd2-94e6-c3ead13c32a7',
                        'job_execution_id' => 10,
                        'created_time' => '2021-03-08T15:37:23+01:00',
                        'updated_time' => '2021-03-09T15:37:23+01:00',
                        'options' => [],
                    ],
            null
        );
        $this->assertInstanceOf(DataMaintenanceJobExecutionMessage::class, $jobExecutionMessage);
        $this->assertEquals(Uuid::fromString('a57380fc-ee3b-4bd2-94e6-c3ead13c32a7'), $jobExecutionMessage->getId());
        $this->assertSame(10, $jobExecutionMessage->getJobExecutionId());
        $this->assertEquals(new \DateTime('2021-03-08T15:37:23+01:00'), $jobExecutionMessage->getCreateTime());
        $this->assertEquals(new \DateTime('2021-03-09T15:37:23+01:00'), $jobExecutionMessage->getUpdatedTime());
        $this->assertSame([], $jobExecutionMessage->getOptions());
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Batch\Updater;

use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\UserManagement\UpsertRunningUser;
use Akeneo\Tool\Component\Batch\Clock\ClockInterface;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Job\JobParametersFactory;
use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Updater\JobInstanceUpdater;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class JobInstanceUpdaterTest extends TestCase
{
    private JobInstance|MockObject $jobInstance;
    private JobParametersFactory|MockObject $jobParametersFactory;
    private JobRegistry|MockObject $jobRegistry;
    private UpsertRunningUser|MockObject $upsertRunningUser;
    private ClockInterface|MockObject $clock;
    private JobInstanceUpdater $sut;

    protected function setUp(): void
    {
        $this->jobInstance = $this->createMock(JobInstance::class);
        $this->jobParametersFactory = $this->createMock(JobParametersFactory::class);
        $this->jobRegistry = $this->createMock(JobRegistry::class);
        $this->upsertRunningUser = $this->createMock(UpsertRunningUser::class);
        $this->clock = $this->createMock(ClockInterface::class);
        $this->sut = new JobInstanceUpdater($this->jobParametersFactory, $this->jobRegistry, $this->upsertRunningUser, $this->clock);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(JobInstanceUpdater::class, $this->sut);
    }

    public function test_it_is_object_updater(): void
    {
        $this->assertInstanceOf(ObjectUpdaterInterface::class, $this->sut);
    }

    public function test_it_updates_an_job_instance(): void
    {
        $job = $this->createMock(JobInterface::class);
        $jobParameters = $this->createMock(JobParameters::class);

        $this->jobInstance->method('getJobName')->willReturn('fixtures_currency_csv');
        $this->jobInstance->method('getRawParameters')->willReturn(['storage' => ['type' => 'local', 'file_path' => 'currencies.csv']]);
        $this->jobRegistry->method('get')->with('fixtures_currency_csv')->willReturn($job);
        $this->jobParametersFactory->method('create')->with($job, ['storage' => ['type' => 'local', 'file_path' => 'currencies.csv']])->willReturn($jobParameters);
        $jobParameters->method('all')->willReturn(['storage' => ['type' => 'local', 'file_path' => 'currencies.csv']]);
        $this->jobInstance->method('isScheduled')->willReturn(false);
        $this->jobInstance->expects($this->once())->method('setJobName')->with('fixtures_currency_csv');
        $this->jobInstance->expects($this->once())->method('setCode')->with('fixtures_currency_csv');
        $this->jobInstance->expects($this->once())->method('setConnector')->with('Data fixtures');
        $this->jobInstance->expects($this->once())->method('setLabel')->with('Currencies data fixtures');
        $this->jobInstance->expects($this->once())->method('setRawParameters')->with(['storage' => ['type' => 'local', 'file_path' => 'currencies.csv']]);
        $this->jobInstance->expects($this->once())->method('setType')->with('type');
        $this->sut->update($this->jobInstance, [
                    'connector' => 'Data fixtures',
                    'alias' => 'fixtures_currency_csv',
                    'label' => 'Currencies data fixtures',
                    'type' => 'type',
                    'configuration' => [
                        'storage' => ['type' => 'local', 'file_path' => 'currencies.csv'],
                    ],
                    'code' => 'fixtures_currency_csv',
                ]);
    }

    public function test_it_updates_automation_setup_date_when_cron_expression_is_updated(): void
    {
        $currentAutomation = [
                    'cron_expression' => '0 */8 * * *',
                ];
        $this->jobInstance->method('isScheduled')->willReturn(false);
        $this->jobInstance->method('getAutomation')->willReturn($currentAutomation);
        $this->clock->method('now')->willReturn(\DateTimeImmutable::createFromFormat(\DateTimeImmutable::ATOM, '2022-12-27T07:00:00+00:00'));
        $expectedUpdatedAutomation = [
                    'cron_expression' => '0 */4 * * *',
                    'setup_date' => '2022-12-27T07:00:00+00:00',
                    'last_execution_date' => null,
                ];
        $this->jobInstance->expects($this->once())->method('setAutomation')->with($expectedUpdatedAutomation);
        $this->sut->update($this->jobInstance, [
                    'automation' => [
                        'cron_expression' => '0 */4 * * *',
                    ],
                ]);
    }

    public function test_it_does_not_update_automation_setup_date_when_cron_expression_is_not_updated(): void
    {
        $currentAutomation = [
                    'cron_expression' => '0 */4 * * *',
                    'setup_date' => '2022-12-27T07:00:00+00:00',
                    'last_execution_date' => null,
                ];
        $this->jobInstance->method('isScheduled')->willReturn(false);
        $this->jobInstance->method('getAutomation')->willReturn($currentAutomation);
        $this->clock->expects($this->never())->method('now');
        $expectedNotUpdatedAutomation = [
                    'cron_expression' => '0 */4 * * *',
                    'setup_date' => '2022-12-27T07:00:00+00:00',
                    'last_execution_date' => null,
                ];
        $this->jobInstance->expects($this->once())->method('setAutomation')->with($expectedNotUpdatedAutomation);
        $this->sut->update($this->jobInstance, [
                    'automation' => [
                        'cron_expression' => '0 */4 * * *',
                    ],
                ]);
    }

    public function test_it_does_nothing_when_automation_is_null(): void
    {
        $currentAutomation = null;
        $this->jobInstance->method('isScheduled')->willReturn(false);
        $this->jobInstance->method('getAutomation')->willReturn($currentAutomation);
        $this->jobInstance->expects($this->once())->method('setAutomation')->with($currentAutomation);
        $this->sut->update($this->jobInstance, [
                    'automation' => null,
                ]);
    }

    public function test_it_throws_an_exception_if_it_is_not_a_job_instance(): void
    {
        $this->expectException(InvalidObjectException::class);
        $this->sut->update(new \stdClass(), []);
    }

    public function test_it_upserts_an_user_when_job_is_scheduled(): void
    {
        $automation = [
                    'cron_expression' => '0 */8 * * *',
                    'setup_date' => '2022-12-27T07:00:00+00:00',
                    'last_execution_date' => null,
                    'running_user_groups' => ['IT Support'],
                ];
        $this->jobInstance->method('getCode')->willReturn('xlsx_product_import');
        $this->jobInstance->expects($this->once())->method('setScheduled')->with(true);
        $this->jobInstance->expects($this->once())->method('setAutomation')->with($automation);
        $this->jobInstance->method('isScheduled')->willReturn(true);
        $this->jobInstance->method('getAutomation')->willReturn($automation);
        $this->upsertRunningUser->expects($this->once())->method('execute')->with('xlsx_product_import', ['IT Support']);
        $this->sut->update($this->jobInstance, [
                    'scheduled' => true,
                    'automation' => $automation,
                ]);
    }

    public function test_it_does_not_upsert_an_user_when_job_is_not_scheduled(): void
    {
        $automation = [
                    'cron_expression' => '0 */8 * * *',
                    'setup_date' => '2022-12-27T07:00:00+00:00',
                    'last_execution_date' => null,
                    'running_user_groups' => ['IT Support'],
                ];
        $this->jobInstance->expects($this->once())->method('setScheduled')->with(false);
        $this->jobInstance->expects($this->once())->method('setAutomation')->with($automation);
        $this->jobInstance->method('isScheduled')->willReturn(false);
        $this->jobInstance->method('getAutomation')->willReturn($automation);
        $this->clock->method('now')->willReturn(\DateTimeImmutable::createFromFormat(\DateTimeImmutable::ATOM, '2022-12-27T07:00:00+00:00'));
        $this->upsertRunningUser->expects($this->never())->method('execute')->with('xlsx_product_import', ['IT Support']);
        $this->sut->update($this->jobInstance, [
                    'scheduled' => false,
                    'automation' => $automation,
                ]);
    }
}

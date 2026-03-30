<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Batch\Job;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\UndefinedJobException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Tool\Component\Batch\Job\JobRegistry;

class JobRegistryTest extends TestCase
{
    private FeatureFlags|MockObject $featureFlags;
    private JobInterface|MockObject $referenceEntityJob;
    private JobInterface|MockObject $assetJob;
    private JobInterface|MockObject $productExportJob;
    private JobRegistry $sut;

    protected function setUp(): void
    {
        $this->featureFlags = $this->createMock(FeatureFlags::class);
        $this->referenceEntityJob = $this->createMock(JobInterface::class);
        $this->assetJob = $this->createMock(JobInterface::class);
        $this->productExportJob = $this->createMock(JobInterface::class);
        $this->sut = new JobRegistry($this->featureFlags);
        $this->referenceEntityJob->method('getName')->willReturn('reference_entity_job');
        $this->assetJob->method('getName')->willReturn('asset_manager_job');
        $this->productExportJob->method('getName')->willReturn('product_export_job');
        $this->featureFlags->method('isEnabled')->with('asset_manager')->willReturn(true);
        $this->featureFlags->method('isEnabled')->with('reference_entity')->willReturn(false);
        $this->sut->register($this->referenceEntityJob, 'import', 'connector_1', 'reference_entity');
        $this->sut->register($this->assetJob, 'import', 'connector_2', 'asset_manager');
        $this->sut->register($this->productExportJob, 'export', 'connector_2');
    }

    public function test_it_gets_a_job_activated_through_feature_flag(): void
    {
        $this->assertSame($this->assetJob, $this->sut->get('asset_manager_job'));
    }

    public function test_it_gets_a_job_even_it_is_disabled_through_feature_flag_to_allow_icecat_job_installation(): void
    {
        $this->assertSame($this->referenceEntityJob, $this->sut->get('reference_entity_job'));
    }

    public function test_it_return_if_a_job_is_activated_to_make_it_visible_or_not_in_the_process_tracker_for_example(): void
    {
        $this->assertSame(true, $this->sut->isEnabled('asset_manager_job'));
    }

    public function test_it_return_if_a_job_is_disabled_to_make_it_invisible_or_not_in_the_process_tracker_for_example(): void
    {
        $this->assertSame(false, $this->sut->isEnabled('reference_entity_job'));
    }

    public function test_it_throws_an_exception_when_checking_if_an_non_existent_job_is_activated_or_not(): void
    {
        $this->expectException(UndefinedJobException::class);
        $this->sut->isEnabled('foo');
    }

    public function test_it_gets_a_job_when_no_feature_flag_configured_for_it(): void
    {
        $this->assertSame($this->productExportJob, $this->sut->get('product_export_job'));
    }

    public function test_it_throws_an_exception_when_getting_a_non_existing_job(): void
    {
        $this->expectException(UndefinedJobException::class);
        $this->sut->get('foo');
    }

    public function test_it_gets_all_activated_jobs_through_feature_flags(): void
    {
        $this->assertSame(['asset_manager_job' => $this->assetJob, 'product_export_job' => $this->productExportJob], $this->sut->all());
    }

    public function test_it_gets_all_by_type(): void
    {
        $this->assertSame(['asset_manager_job' => $this->assetJob], $this->sut->allByType('import'));
    }

    public function test_it_gets_all_by_type_group_by_connector(): void
    {
        $this->assertSame(['connector_2' => ['asset_manager_job' => $this->assetJob]], $this->sut->allByTypeGroupByConnector('import'));
    }

    public function test_it_gets_connectors(): void
    {
        $this->assertSame(['asset_manager_job' => 'connector_2'], $this->sut->getConnectors('import'));
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Platform\Unit\Bundle\ImportExportBundle\Domain;

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\ResolveScheduledJobRunningUsername;
use PHPUnit\Framework\TestCase;

class ResolveScheduledJobRunningUsernameTest extends TestCase
{
    private ResolveScheduledJobRunningUsername $sut;

    protected function setUp(): void
    {
        $this->sut = new ResolveScheduledJobRunningUsername();
    }

    public function test_it_resolves_running_username_from_job_code(): void
    {
        $this->assertSame('job_automated_my_job', $this->sut->fromJobCode('my_job'));
    }
}

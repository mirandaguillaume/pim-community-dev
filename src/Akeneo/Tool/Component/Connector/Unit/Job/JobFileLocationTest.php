<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Connector\Job;

use Akeneo\Tool\Component\Connector\Job\JobFileLocation;
use PHPUnit\Framework\TestCase;

class JobFileLocationTest extends TestCase
{
    private JobFileLocation $sut;

    protected function setUp(): void
    {
    }

    public function test_it_is_built_for_local(): void
    {
        $this->sut = new JobFileLocation('/my/path/to/local/file.csv', false);
        $this->assertSame(false, $this->sut->isRemote());
        $this->assertSame('/my/path/to/local/file.csv', $this->sut->path());
        $this->assertSame('/my/path/to/local/file.csv', $this->sut->url());
    }

    public function test_it_is_built_for_remote(): void
    {
        $this->sut = new JobFileLocation('/my/path/to/remote/file.csv', true);
        $this->assertSame(true, $this->sut->isRemote());
        $this->assertSame('/my/path/to/remote/file.csv', $this->sut->path());
        $this->assertSame('pim_remote:///my/path/to/remote/file.csv', $this->sut->url());
    }

    public function test_it_is_built_from_local_location_url(): void
    {
        $this->sut = JobFileLocation::parseUrl('/my/path/to/local/file.csv');
        $this->assertSame(false, $this->sut->isRemote());
        $this->assertSame('/my/path/to/local/file.csv', $this->sut->path());
    }

    public function test_it_is_built_from_remote_location_url(): void
    {
        $this->sut = JobFileLocation::parseUrl('pim_remote:///my/path/to/remote/file.csv');
        $this->assertSame(true, $this->sut->isRemote());
        $this->assertSame('/my/path/to/remote/file.csv', $this->sut->path());
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Connector\Job\JobParameters\ConstraintCollectionProvider;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Akeneo\Tool\Component\Connector\Job\JobParameters\ConstraintCollectionProvider\SimpleCsvImport;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SimpleCsvImportTest extends TestCase
{
    private SimpleCsvImport $sut;

    protected function setUp(): void
    {
        $this->sut = new SimpleCsvImport(['my_supported_job_name']);
    }

    public function test_it_is_a_provider(): void
    {
        $this->assertInstanceOf(ConstraintCollectionProviderInterface::class, $this->sut);
    }

    public function test_it_provides_constraints_collection(): void
    {
        $collection = $this->sut->getConstraintCollection();
        $fields = $collection->fields;
        $this->assertCount(9, $fields);
        $this->assertArrayHasKey('storage', $fields);
        $this->assertArrayHasKey('delimiter', $fields);
        $this->assertArrayHasKey('enclosure', $fields);
        $this->assertArrayHasKey('withHeader', $fields);
        $this->assertArrayHasKey('escape', $fields);
        $this->assertArrayHasKey('uploadAllowed', $fields);
        $this->assertArrayHasKey('invalid_items_file_format', $fields);
        $this->assertArrayHasKey('users_to_notify', $fields);
        $this->assertArrayHasKey('is_user_authenticated', $fields);
    }

    public function test_it_supports_a_job(): void
    {
        $job = $this->createMock(JobInterface::class);

        $job->method('getName')->willReturn('my_supported_job_name');
        $this->assertSame(true, $this->sut->supports($job));
    }
}

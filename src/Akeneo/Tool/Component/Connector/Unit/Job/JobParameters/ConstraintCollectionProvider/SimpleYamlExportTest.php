<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Connector\Job\JobParameters\ConstraintCollectionProvider;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Tool\Component\Connector\Job\JobParameters\ConstraintCollectionProvider\SimpleYamlExport;

class SimpleYamlExportTest extends TestCase
{
    private SimpleYamlExport $sut;

    protected function setUp(): void
    {
        $this->sut = new SimpleYamlExport(['my_supported_job_name']);
    }

    public function test_it_is_a_provider(): void
    {
        $this->assertInstanceOf(ConstraintCollectionProviderInterface::class, $this->sut);
    }

    public function test_it_provides_constraints_collection(): void
    {
        $collection = $this->getConstraintCollection();
        $collection->shouldReturnAnInstanceOf(\Symfony\Component\Validator\Constraints\Collection::class);
        $fields = $collection->fields;
        $fields->shouldHaveCount(3);
        $fields->shouldHaveKey('storage');
        $fields->shouldHaveKey('users_to_notify');
        $fields->shouldHaveKey('is_user_authenticated');
    }

    public function test_it_supports_a_job(): void
    {
        $job = $this->createMock(JobInterface::class);

        $job->method('getName')->willReturn('my_supported_job_name');
        $this->assertSame(true, $this->sut->supports($job));
    }
}

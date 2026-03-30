<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Connector\Job\JobParameters\DefaultValuesProvider;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Tool\Component\Connector\Job\JobParameters\DefaultValuesProvider\SimpleYamlImport;

class SimpleYamlImportTest extends TestCase
{
    private SimpleYamlImport $sut;

    protected function setUp(): void
    {
        $this->sut = new SimpleYamlImport(['my_supported_job_name']);
    }

    public function test_it_is_a_provider(): void
    {
        $this->assertInstanceOf(DefaultValuesProviderInterface::class, $this->sut);
    }

    public function test_it_provides_default_values(): void
    {
        $this->assertSame([
                        'storage' => [
                            'type' => 'none',
                        ],
                        'uploadAllowed' => true,
                        'invalid_items_file_format' => 'yaml',
                        'users_to_notify' => [],
                        'is_user_authenticated' => false,
                    ], $this->sut->getDefaultValues());
    }

    public function test_it_supports_a_job(): void
    {
        $job = $this->createMock(JobInterface::class);

        $job->method('getName')->willReturn('my_supported_job_name');
        $this->assertSame(true, $this->sut->supports($job));
    }
}

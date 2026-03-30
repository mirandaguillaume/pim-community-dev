<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Batch\Job;

use Akeneo\Tool\Component\Batch\Job\UndefinedJobParameterException;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Tool\Component\Batch\Job\JobParameters;

class JobParametersTest extends TestCase
{
    private JobParameters $sut;

    protected function setUp(): void
    {
    }

    public function test_it_contains_a_parameter(): void
    {
        $this->sut = new JobParameters(['filePath' => '/tmp/myfile.csv']);
        $this->assertSame(true, $this->sut->has('filePath'));
    }

    public function test_it_does_not_contain_a_parameter(): void
    {
        $this->sut = new JobParameters(['filePath' => '/tmp/myfile.csv']);
        $this->assertSame(false, $this->sut->has('enclosure'));
    }

    public function test_it_is_countable(): void
    {
        $this->sut = new JobParameters([]);
        $this->assertTrue(is_a(JobParameters::class, '\Countable', true));
    }

    public function test_it_is_iterable(): void
    {
        $this->sut = new JobParameters([]);
        $this->assertTrue(is_a(JobParameters::class, '\IteratorAggregate', true));
    }

    public function test_it_counts_the_number_of_parameters(): void
    {
        $this->sut = new JobParameters(['filePath' => '/tmp/myfile.csv', 'enclosure' => '"']);
        $this->assertSame(2, $this->sut->count());
    }

    public function test_it_provides_a_parameter_value(): void
    {
        $this->sut = new JobParameters(['filePath' => '/tmp/myfile.csv', 'enclosure' => '"']);
        $this->assertSame('/tmp/myfile.csv', $this->sut->get('filePath'));
    }

    public function test_it_provides_all_parameter_values_as_array(): void
    {
        $this->sut = new JobParameters(['filePath' => '/tmp/myfile.csv', 'enclosure' => '"']);
        $this->assertSame(['filePath' => '/tmp/myfile.csv', 'enclosure' => '"'], $this->sut->all());
    }

    public function test_it_throws_undefined_job_parameter_exception_when_accessing_undefined_parameter(): void
    {
        $this->sut = new JobParameters(['filePath' => '/tmp/myfile.csv', 'enclosure' => '"']);
        $this->expectException(UndefinedJobParameterException::class);

        $this->expectExceptionMessage('Parameter "undefinedKey" is undefined');
        $this->sut->get('undefinedKey');
    }
}

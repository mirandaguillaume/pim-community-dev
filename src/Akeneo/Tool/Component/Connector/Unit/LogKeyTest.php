<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Connector;

use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Tool\Component\Connector\LogKey;

class LogKeyTest extends TestCase
{
    private LogKey $sut;

    protected function setUp(): void
    {
    }

    public function test_it_fails_when_log_file_is_empty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new LogKey(new JobExecution());
    }

    public function test_it_is_a_key_built_from_a_job_execution(): void
    {
        $importInstance = new JobInstance(null, JobInstance::TYPE_IMPORT, 'csv_import');
        $importExecution = (new JobExecution())
                    ->setJobInstance($importInstance)
                    ->setLogFile(__FILE__)
        ;
        $this->sut = new LogKey($importExecution);
        // normally we should have something like 'import/csv_import/ID/log/LogKeySpec.php'
        // but the ID is created by the ORM, we have no control on ID, no way to set it
        $this->__toString()->shouldBe('import/csv_import//log/LogKeySpec.php');
    }
}

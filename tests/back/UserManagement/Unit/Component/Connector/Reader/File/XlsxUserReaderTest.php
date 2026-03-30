<?php

declare(strict_types=1);

namespace Akeneo\Test\UserManagement\Unit\Component\Connector\Reader\File;

use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\Reader\File\FileIteratorFactory;
use Akeneo\Tool\Component\Connector\Reader\File\FileIteratorInterface;
use Akeneo\Tool\Component\Connector\Reader\File\FileReaderInterface;
use Akeneo\UserManagement\Component\Connector\Reader\File\XlsxUserReader;
use PHPUnit\Framework\TestCase;

class XlsxUserReaderTest extends TestCase
{
    private XlsxUserReader $sut;

    protected function setUp(): void
    {
        $this->sut = new XlsxUserReader();
    }

}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\Item\MassEdit;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Item\MassEdit\TemporaryFileCleaner;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PHPUnit\Framework\TestCase;

class TemporaryFileCleanerTest extends TestCase
{
    private TemporaryFileCleaner $sut;

    protected function setUp(): void
    {
        $this->sut = new TemporaryFileCleaner();
    }

}

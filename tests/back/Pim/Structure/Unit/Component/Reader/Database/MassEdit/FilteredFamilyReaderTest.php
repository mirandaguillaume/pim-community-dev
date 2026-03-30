<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Reader\Database\MassEdit;

use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Reader\Database\MassEdit\FilteredFamilyReader;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PHPUnit\Framework\TestCase;

class FilteredFamilyReaderTest extends TestCase
{
    private FilteredFamilyReader $sut;

    protected function setUp(): void
    {
        $this->sut = new FilteredFamilyReader();
    }

}

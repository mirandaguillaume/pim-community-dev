<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Bundle\ReferenceData\RequirementChecker;

use Akeneo\Pim\Enrichment\Component\Product\Model\ReferenceDataInterface;
use Akeneo\Pim\Structure\Bundle\ReferenceData\RequirementChecker\ReferenceDataInterfaceChecker;
use Akeneo\Pim\Structure\Component\Model\ReferenceDataConfigurationInterface;
use PHPUnit\Framework\TestCase;

class ReferenceDataInterfaceCheckerTest extends TestCase
{
    private ReferenceDataInterfaceChecker $sut;

    protected function setUp(): void
    {
        $this->sut = new ReferenceDataInterfaceChecker();
    }

}

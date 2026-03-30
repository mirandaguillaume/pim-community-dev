<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Bundle\ReferenceData\RequirementChecker;

use Akeneo\Pim\Structure\Bundle\ReferenceData\RequirementChecker\ReferenceDataNameChecker;
use Akeneo\Pim\Structure\Component\Model\ReferenceDataConfigurationInterface;
use PHPUnit\Framework\TestCase;

class ReferenceDataNameCheckerTest extends TestCase
{
    private ReferenceDataNameChecker $sut;

    protected function setUp(): void
    {
        $this->sut = new ReferenceDataNameChecker();
    }

}

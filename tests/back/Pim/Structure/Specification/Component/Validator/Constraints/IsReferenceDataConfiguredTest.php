<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Structure\Component\Validator\Constraints\IsReferenceDataConfigured;
use PHPUnit\Framework\TestCase;

class IsReferenceDataConfiguredTest extends TestCase
{
    private IsReferenceDataConfigured $sut;

    protected function setUp(): void
    {
        $this->sut = new IsReferenceDataConfigured();
    }

}

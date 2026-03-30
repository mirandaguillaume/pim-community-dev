<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Range;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;

class RangeTest extends TestCase
{
    private Range $sut;

    protected function setUp(): void
    {
        $this->sut = new Range();
    }

}

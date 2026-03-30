<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\IsString;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;

class IsStringTest extends TestCase
{
    private IsString $sut;

    protected function setUp(): void
    {
        $this->sut = new IsString();
    }

}

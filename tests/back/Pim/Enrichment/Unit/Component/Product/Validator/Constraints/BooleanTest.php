<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Boolean;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;

class BooleanTest extends TestCase
{
    private Boolean $sut;

    protected function setUp(): void
    {
        $this->sut = new Boolean();
    }

}

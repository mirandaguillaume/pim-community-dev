<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Structure\Component\Validator\Constraints\ValidDateRange;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;

class ValidDateRangeTest extends TestCase
{
    private ValidDateRange $sut;

    protected function setUp(): void
    {
        $this->sut = new ValidDateRange();
    }

}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Structure\Component\Validator\Constraints\ValidNumberRange;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;

class ValidNumberRangeTest extends TestCase
{
    private ValidNumberRange $sut;

    protected function setUp(): void
    {
        $this->sut = new ValidNumberRange();
    }

}

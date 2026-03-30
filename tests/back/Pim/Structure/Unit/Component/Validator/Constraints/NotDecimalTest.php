<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Structure\Component\Validator\Constraints\NotDecimal;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;

class NotDecimalTest extends TestCase
{
    private NotDecimal $sut;

    protected function setUp(): void
    {
        $this->sut = new NotDecimal();
    }

}

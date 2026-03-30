<?php

declare(strict_types=1);

namespace Akeneo\Test\Channel\Unit\Infrastructure\Component\Validator\Constraint;

use Akeneo\Channel\Infrastructure\Component\Validator\Constraint\ConversionUnits;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;

class ConversionUnitsTest extends TestCase
{
    private ConversionUnits $sut;

    protected function setUp(): void
    {
        $this->sut = new ConversionUnits();
    }

}

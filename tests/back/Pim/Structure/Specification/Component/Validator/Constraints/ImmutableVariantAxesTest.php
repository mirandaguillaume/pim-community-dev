<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Structure\Component\Validator\Constraints\ImmutableVariantAxes;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;

class ImmutableVariantAxesTest extends TestCase
{
    private ImmutableVariantAxes $sut;

    protected function setUp(): void
    {
        $this->sut = new ImmutableVariantAxes();
    }

}

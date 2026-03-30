<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Structure\Component\Validator\Constraints\NullProperties;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;

class NullPropertiesTest extends TestCase
{
    private NullProperties $sut;

    protected function setUp(): void
    {
        $this->sut = new NullProperties();
    }

}

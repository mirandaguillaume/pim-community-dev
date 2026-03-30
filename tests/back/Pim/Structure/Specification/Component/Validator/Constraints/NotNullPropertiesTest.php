<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Structure\Component\Validator\Constraints\NotNullProperties;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;

class NotNullPropertiesTest extends TestCase
{
    private NotNullProperties $sut;

    protected function setUp(): void
    {
        $this->sut = new NotNullProperties();
    }

}

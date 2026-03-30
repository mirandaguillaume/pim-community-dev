<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Structure\Component\Validator\Constraints\FamilyVariant;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;

class FamilyVariantTest extends TestCase
{
    private FamilyVariant $sut;

    protected function setUp(): void
    {
        $this->sut = new FamilyVariant();
    }

}

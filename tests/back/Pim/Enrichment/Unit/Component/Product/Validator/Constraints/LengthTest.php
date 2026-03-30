<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Length;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Length as BaseLength;

class LengthTest extends TestCase
{
    private Length $sut;

    protected function setUp(): void
    {
        $this->sut = new Length(['max' => 5]);
    }

}

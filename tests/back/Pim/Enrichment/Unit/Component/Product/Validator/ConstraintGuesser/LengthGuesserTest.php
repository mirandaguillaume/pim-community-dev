<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Validator\ConstraintGuesser;

use Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesserInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesser\LengthGuesser;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Length;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PHPUnit\Framework\TestCase;

class LengthGuesserTest extends TestCase
{
    private LengthGuesser $sut;

    protected function setUp(): void
    {
        $this->sut = new LengthGuesser();
    }

}

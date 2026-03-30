<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Validator\ConstraintGuesser;

use Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesserInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesser\RangeGuesser;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Range;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PHPUnit\Framework\TestCase;

class RangeGuesserTest extends TestCase
{
    private RangeGuesser $sut;

    protected function setUp(): void
    {
        $this->sut = new RangeGuesser();
    }

}

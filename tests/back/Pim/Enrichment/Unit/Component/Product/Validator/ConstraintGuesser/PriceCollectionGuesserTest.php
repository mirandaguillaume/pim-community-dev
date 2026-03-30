<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Validator\ConstraintGuesser;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductPriceInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesserInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesser\PriceCollectionGuesser;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Currency;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\IsNumeric;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\NotDecimal;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Range;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Type;

class PriceCollectionGuesserTest extends TestCase
{
    private PriceCollectionGuesser $sut;

    protected function setUp(): void
    {
        $this->sut = new PriceCollectionGuesser();
    }

}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Validator;

use Akeneo\Pim\Enrichment\Component\Product\Exception\AlreadyExistingAxisValueCombinationException;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Validator\UniqueAxesCombinationSet;
use Akeneo\Pim\Enrichment\Component\Product\Value\IdentifierValue;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\FamilyVariant;
use PHPUnit\Framework\TestCase;

class UniqueAxesCombinationSetTest extends TestCase
{
    private UniqueAxesCombinationSet $sut;

    protected function setUp(): void
    {
        $this->sut = new UniqueAxesCombinationSet();
    }

}

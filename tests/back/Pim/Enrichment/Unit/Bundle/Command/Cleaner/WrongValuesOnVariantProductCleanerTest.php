<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\Command\Cleaner;

use Akeneo\Pim\Enrichment\Bundle\Command\Cleaner\WrongValuesOnVariantProductCleaner;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use PHPUnit\Framework\TestCase;

class WrongValuesOnVariantProductCleanerTest extends TestCase
{
    private WrongValuesOnVariantProductCleaner $sut;

    protected function setUp(): void
    {
        $this->sut = new WrongValuesOnVariantProductCleaner();
    }

}

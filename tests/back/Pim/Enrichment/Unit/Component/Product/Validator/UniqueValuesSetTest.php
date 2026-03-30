<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Validator;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\UniqueValuesSet;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class UniqueValuesSetTest extends TestCase
{
    private UniqueValuesSet $sut;

    protected function setUp(): void
    {
        $this->sut = new UniqueValuesSet();
    }

}

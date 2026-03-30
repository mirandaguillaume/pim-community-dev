<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Factory;

use Akeneo\Pim\Enrichment\Component\Product\Factory\ReadValueCollectionFactory;
use Akeneo\Pim\Enrichment\Component\Product\Factory\WriteValueCollectionFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use PHPUnit\Framework\TestCase;

class WriteValueCollectionFactoryTest extends TestCase
{
    private WriteValueCollectionFactory $sut;

    protected function setUp(): void
    {
        $this->sut = new WriteValueCollectionFactory();
    }

}

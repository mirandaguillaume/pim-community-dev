<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Updater\Adder;

use Akeneo\Pim\Enrichment\Component\Product\Updater\Adder\AdderRegistry;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Adder\AttributeAdderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Adder\FieldAdderInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use PHPUnit\Framework\TestCase;

class AdderRegistryTest extends TestCase
{
    private AdderRegistry $sut;

    protected function setUp(): void
    {
        $this->sut = new AdderRegistry();
    }

}

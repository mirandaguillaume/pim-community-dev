<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Updater\Remover;

use Akeneo\Pim\Enrichment\Component\Product\Updater\Remover\AttributeRemoverInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Remover\FieldRemoverInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Remover\RemoverRegistry;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Remover\RemoverRegistryInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use PHPUnit\Framework\TestCase;

class RemoverRegistryTest extends TestCase
{
    private RemoverRegistry $sut;

    protected function setUp(): void
    {
        $this->sut = new RemoverRegistry();
    }

}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Updater\Copier;

use Akeneo\Pim\Enrichment\Component\Product\Updater\Copier\AttributeCopierInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Copier\CopierRegistry;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Copier\FieldCopierInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use PHPUnit\Framework\TestCase;

class CopierRegistryTest extends TestCase
{
    private CopierRegistry $sut;

    protected function setUp(): void
    {
        $this->sut = new CopierRegistry();
    }

}

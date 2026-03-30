<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Localization\Presenter;

use Akeneo\Pim\Enrichment\Component\Product\Localization\Presenter\PresenterRegistry;
use Akeneo\Pim\Enrichment\Component\Product\Localization\Presenter\PresenterRegistryInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\Localization\Presenter\PresenterInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PHPUnit\Framework\TestCase;

class PresenterRegistryTest extends TestCase
{
    private PresenterRegistry $sut;

    protected function setUp(): void
    {
        $this->sut = new PresenterRegistry();
    }

}

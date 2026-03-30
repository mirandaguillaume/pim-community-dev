<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Localization\Localizer;

use Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer\LocalizerRegistry;
use Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer\LocalizerRegistryInterface;
use Akeneo\Tool\Component\Localization\Localizer\LocalizerInterface;
use PHPUnit\Framework\TestCase;

class LocalizerRegistryTest extends TestCase
{
    private LocalizerRegistry $sut;

    protected function setUp(): void
    {
        $this->sut = new LocalizerRegistry();
    }

}

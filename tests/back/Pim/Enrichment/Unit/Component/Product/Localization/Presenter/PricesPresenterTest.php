<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Localization\Presenter;

use Akeneo\Pim\Enrichment\Component\Product\Localization\Presenter\PricesPresenter;
use Akeneo\Tool\Component\Localization\Factory\NumberFactory;
use PHPUnit\Framework\TestCase;

class PricesPresenterTest extends TestCase
{
    private PricesPresenter $sut;

    protected function setUp(): void
    {
        $this->sut = new PricesPresenter();
    }

}

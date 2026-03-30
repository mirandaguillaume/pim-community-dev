<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\Writer;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\TranslatedColumnPresenter;
use PHPUnit\Framework\TestCase;

class TranslatedColumnPresenterTest extends TestCase
{
    private TranslatedColumnPresenter $sut;

    protected function setUp(): void
    {
        $this->sut = new TranslatedColumnPresenter();
    }

}

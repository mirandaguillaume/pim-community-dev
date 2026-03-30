<?php

declare(strict_types=1);

namespace Akeneo\Test\Channel\Unit\Infrastructure\Component\ArrayConverter\StandardToFlat;

use Akeneo\Channel\Infrastructure\Component\ArrayConverter\StandardToFlat\Currency;
use PHPUnit\Framework\TestCase;

class CurrencyTest extends TestCase
{
    private Currency $sut;

    protected function setUp(): void
    {
        $this->sut = new Currency();
    }

}

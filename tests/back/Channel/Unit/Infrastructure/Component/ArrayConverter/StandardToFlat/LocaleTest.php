<?php

declare(strict_types=1);

namespace Akeneo\Test\Channel\Unit\Infrastructure\Component\ArrayConverter\StandardToFlat;

use Akeneo\Channel\Infrastructure\Component\ArrayConverter\StandardToFlat\Locale;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use PHPUnit\Framework\TestCase;

class LocaleTest extends TestCase
{
    private Locale $sut;

    protected function setUp(): void
    {
        $this->sut = new Locale();
    }

}

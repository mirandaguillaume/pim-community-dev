<?php

declare(strict_types=1);

namespace Akeneo\Test\Channel\Unit\Infrastructure\Component\Model;

use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Channel\Infrastructure\Component\Model\Locale;
use PHPUnit\Framework\TestCase;

class LocaleTest extends TestCase
{
    private Locale $sut;

    protected function setUp(): void
    {
        $this->sut = new Locale();
    }

}

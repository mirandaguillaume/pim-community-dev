<?php

declare(strict_types=1);

namespace Akeneo\Test\Channel\Unit\Infrastructure\Component\ArrayConverter\StandardToFlat;

use Akeneo\Channel\Infrastructure\Component\ArrayConverter\StandardToFlat\Channel;
use PHPUnit\Framework\TestCase;

class ChannelTest extends TestCase
{
    private Channel $sut;

    protected function setUp(): void
    {
        $this->sut = new Channel();
    }

}

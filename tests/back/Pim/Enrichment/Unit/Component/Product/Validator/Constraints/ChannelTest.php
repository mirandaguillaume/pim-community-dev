<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Channel;
use PHPUnit\Framework\TestCase;

class ChannelTest extends TestCase
{
    private Channel $sut;

    protected function setUp(): void
    {
        $this->sut = new Channel();
    }

}

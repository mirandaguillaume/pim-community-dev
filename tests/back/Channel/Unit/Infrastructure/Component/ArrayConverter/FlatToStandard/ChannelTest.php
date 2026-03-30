<?php

declare(strict_types=1);

namespace Akeneo\Test\Channel\Unit\Infrastructure\Component\ArrayConverter\FlatToStandard;

use Akeneo\Channel\Infrastructure\Component\ArrayConverter\FlatToStandard\Channel;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker;
use PHPUnit\Framework\TestCase;

class ChannelTest extends TestCase
{
    private Channel $sut;

    protected function setUp(): void
    {
        $this->sut = new Channel();
    }

}

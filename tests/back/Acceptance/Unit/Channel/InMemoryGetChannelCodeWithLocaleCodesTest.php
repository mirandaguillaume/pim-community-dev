<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Akeneo\Test\Acceptance\Channel;

use Akeneo\Channel\Infrastructure\Component\Model\Channel;
use Akeneo\Channel\Infrastructure\Component\Model\Locale;
use Akeneo\Channel\Infrastructure\Component\Query\PublicApi\GetChannelCodeWithLocaleCodesInterface;
use Akeneo\Test\Acceptance\Channel\InMemoryChannelRepository;
use Akeneo\Test\Acceptance\Channel\InMemoryGetChannelCodeWithLocaleCodes;
use PHPUnit\Framework\TestCase;

class InMemoryGetChannelCodeWithLocaleCodesTest extends TestCase
{
    private InMemoryGetChannelCodeWithLocaleCodes $sut;

    protected function setUp(): void
    {
        $this->sut = new InMemoryGetChannelCodeWithLocaleCodes();
    }

}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Channel\Unit\Infrastructure\Component\Query\PublicApi\Cache;

use Akeneo\Channel\Infrastructure\Component\Query\PublicApi\Cache\CachedChannelExistsWithLocale;
use Akeneo\Channel\Infrastructure\Component\Query\PublicApi\ChannelExistsWithLocaleInterface;
use Akeneo\Channel\Infrastructure\Component\Query\PublicApi\GetChannelCodeWithLocaleCodesInterface;
use PHPUnit\Framework\TestCase;

class CachedChannelExistsWithLocaleTest extends TestCase
{
    private CachedChannelExistsWithLocale $sut;

    protected function setUp(): void
    {
        $this->sut = new CachedChannelExistsWithLocale();
    }

}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Channel\Unit\Infrastructure\EventListener;

use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Channel\Infrastructure\Component\Query\PublicApi\ChannelExistsWithLocaleInterface;
use Akeneo\Channel\Infrastructure\EventListener\ClearCacheSubscriber;
use Akeneo\Tool\Component\StorageUtils\Cache\CachedQueryInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\GenericEvent;

class ClearCacheSubscriberTest extends TestCase
{
    private ClearCacheSubscriber $sut;

    protected function setUp(): void
    {
        $this->sut = new ClearCacheSubscriber();
    }

}

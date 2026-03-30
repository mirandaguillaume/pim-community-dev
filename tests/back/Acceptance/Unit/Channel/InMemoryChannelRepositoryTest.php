<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Test\Acceptance\Channel;

use Akeneo\Channel\Infrastructure\Component\Model\Channel;
use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Channel\Infrastructure\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Test\Acceptance\Channel\InMemoryChannelRepository;

class InMemoryChannelRepositoryTest extends TestCase
{
    private InMemoryChannelRepository $sut;

    protected function setUp(): void
    {
        $this->sut = new InMemoryChannelRepository();
    }

    private function createChannel(string $code): ChannelInterface
    {
        $channel = new Channel();
        $channel->setCode($code);
    
        return $channel;
    }
}

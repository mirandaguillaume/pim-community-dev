<?php

declare(strict_types=1);

namespace Akeneo\Test\Channel\Unit\Infrastructure\EventListener;

use Akeneo\Channel\Infrastructure\Component\Exception\LinkedChannelException;
use Akeneo\Channel\Infrastructure\Component\Model\CurrencyInterface;
use Akeneo\Channel\Infrastructure\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Infrastructure\EventListener\CurrencyDisablingSubscriber;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\GenericEvent;

class CurrencyDisablingSubscriberTest extends TestCase
{
    private CurrencyDisablingSubscriber $sut;

    protected function setUp(): void
    {
        $this->sut = new CurrencyDisablingSubscriber();
    }

    public function test_it_is_an_event_subscriber(): void
    {
        $this->assertInstanceOf(CurrencyDisablingSubscriber::class, $this->sut);
    }

    public function test_it_does_not_throw_when_this_is_not_a_currency(): void
    {
        $event = $this->createMock(GenericEvent::class);
        $notACurrency = $this->createMock(StdClass::class);

        $event->method('getSubject')->willReturn($notACurrency);
        $this->sut->shouldNotThrow(new LinkedChannelException('You cannot disable a currency linked to a channel.'))
                    ->during('checkChannelLink', [$event]);
    }

    public function test_it_does_not_throw_when_currency_is_not_saved(): void
    {
        $event = $this->createMock(GenericEvent::class);
        $currency = $this->createMock(CurrencyInterface::class);

        $event->method('getSubject')->willReturn($currency);
        $currency->method('getId')->willReturn(null);
        $this->sut->shouldNotThrow(new LinkedChannelException('You cannot disable a currency linked to a channel.'))
                    ->during('checkChannelLink', [$event]);
    }

    public function test_it_does_not_throw_when_currency_is_activated(): void
    {
        $event = $this->createMock(GenericEvent::class);
        $currency = $this->createMock(CurrencyInterface::class);

        $event->method('getSubject')->willReturn($currency);
        $currency->method('getId')->willReturn(42);
        $currency->method('isActivated')->willReturn(true);
        $this->sut->shouldNotThrow(new LinkedChannelException('You cannot disable a currency linked to a channel.'))
                    ->during('checkChannelLink', [$event]);
    }

    public function test_it_does_not_throw_when_currency_is_unused(): void
    {
        $event = $this->createMock(GenericEvent::class);
        $currency = $this->createMock(CurrencyInterface::class);

        $event->method('getSubject')->willReturn($currency);
        $currency->method('getId')->willReturn(42);
        $currency->method('isActivated')->willReturn(false);
        $channelRepository->getChannelCountUsingCurrency($currency)->willReturn(0);
        $this->sut->shouldNotThrow(new LinkedChannelException('You cannot disable a currency linked to a channel.'))
                    ->during('checkChannelLink', [$event]);
    }

    public function test_it_throws_linked_channel_exception(): void
    {
        $event = $this->createMock(GenericEvent::class);
        $currency = $this->createMock(CurrencyInterface::class);

        $event->method('getSubject')->willReturn($currency);
        $currency->method('getId')->willReturn(42);
        $currency->method('isActivated')->willReturn(false);
        $channelRepository->getChannelCountUsingCurrency($currency)->willReturn(1);
        $this->expectException(new LinkedChannelException('You cannot disable a currency linked to a channel.'));
        $this->sut->checkChannelLink($event);
    }
}

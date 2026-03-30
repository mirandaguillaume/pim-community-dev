<?php

declare(strict_types=1);

namespace Akeneo\Test\Channel\Unit\Infrastructure\EventListener;

use Akeneo\Channel\Infrastructure\Component\Model\Channel;
use Akeneo\Channel\Infrastructure\Component\Model\Locale;
use Akeneo\Channel\Infrastructure\EventListener\RemoveLocalesFromChannelSubscriber;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\GenericEvent;

class RemoveLocalesFromChannelSubscriberTest extends TestCase
{
    private BulkSaverInterface|MockObject $localeSaver;
    private RemoveLocalesFromChannelSubscriber $sut;

    protected function setUp(): void
    {
        $this->localeSaver = $this->createMock(BulkSaverInterface::class);
        $this->sut = new RemoveLocalesFromChannelSubscriber($this->localeSaver);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(RemoveLocalesFromChannelSubscriber::class, $this->sut);
    }

    public function test_it_only_handles_channels(): void
    {
        $product = new Product();
        $this->localeSaver->expects($this->never())->method('saveAll')->with($this->anything());
        $this->sut->removeLocalesFromChannel(new GenericEvent($product));
        $this->sut->saveLocales(new GenericEvent($product));
    }

    public function test_it_removes_locales_from_deleted_channel_and_saves_them(): void
    {
        $enUS = new Locale();
        $frFR = new Locale();
        $channel = new Channel();
        $channel->setCode('ecommerce');
        $channel->addLocale($enUS);
        $channel->addLocale($frFR);
        $this->localeSaver->expects($this->once())->method('saveAll')->with([$enUS, $frFR]);
        $this->sut->removeLocalesFromChannel(new GenericEvent($channel));
        $this->sut->saveLocales(new GenericEvent($channel));
    }
}

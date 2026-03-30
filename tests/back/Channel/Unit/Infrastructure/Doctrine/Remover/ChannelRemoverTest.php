<?php

declare(strict_types=1);

namespace Akeneo\Test\Channel\Unit\Infrastructure\Doctrine\Remover;

use Akeneo\Channel\Infrastructure\Component\Model\Channel;
use Akeneo\Channel\Infrastructure\Component\Query\IsChannelUsedInProductExportJobInterface;
use Akeneo\Channel\Infrastructure\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Infrastructure\Doctrine\Remover\ChannelRemover;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ChannelRemoverTest extends TestCase
{
    private ChannelRemover $sut;

    protected function setUp(): void
    {
        $this->sut = new ChannelRemover();
    }

    public function test_it_throws_logic_exception_when_the_channel_is_used_in_an_export_profile(): void
    {
        $channel = $this->createMock(Channel::class);
        $channelRepository = $this->createMock(ChannelRepositoryInterface::class);
        $translator = $this->createMock(TranslatorInterface::class);
        $isChannelUsedInProductExportJob = $this->createMock(IsChannelUsedInProductExportJobInterface::class);

        $channel->method('getCode')->willReturn('mobile');
        $channelRepository->method('countAll')->willReturn(2);
        $isChannelUsedInProductExportJob->method('execute')->with('mobile')->willReturn(true);
        $translator->method('trans')->with('pim_enrich.channel.flash.delete.linked_to_export_profile')->willReturn('exception message');
        $logicException = new \LogicException('exception message');
        $this->expectException($logicException);
        $this->sut->remove($channel);
    }
}

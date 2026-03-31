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
    private ObjectManager|MockObject $objectManager;
    private EventDispatcherInterface|MockObject $eventDispatcher;
    private ChannelRepositoryInterface|MockObject $channelRepository;
    private TranslatorInterface|MockObject $translator;
    private IsChannelUsedInProductExportJobInterface|MockObject $isChannelUsedInProductExportJob;
    private ChannelRemover $sut;

    protected function setUp(): void
    {
        $this->objectManager = $this->createMock(ObjectManager::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->channelRepository = $this->createMock(ChannelRepositoryInterface::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->isChannelUsedInProductExportJob = $this->createMock(IsChannelUsedInProductExportJobInterface::class);
        $this->sut = new ChannelRemover(
            $this->objectManager,
            $this->eventDispatcher,
            $this->channelRepository,
            $this->translator,
            $this->isChannelUsedInProductExportJob,
            Channel::class
        );
    }

    public function test_it_is_a_remover(): void
    {
        $this->assertInstanceOf(RemoverInterface::class, $this->sut);
    }

    public function test_it_throws_logic_exception_when_the_channel_is_used_in_an_export_profile(): void
    {
        $channel = $this->createMock(Channel::class);

        $channel->method('getCode')->willReturn('mobile');
        $this->channelRepository->method('countAll')->willReturn(2);
        $this->isChannelUsedInProductExportJob->method('execute')->with('mobile')->willReturn(true);
        $this->translator->method('trans')->with('pim_enrich.channel.flash.delete.linked_to_export_profile')->willReturn('exception message');
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('exception message');
        $this->sut->remove($channel);
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\UseCase\Validator;

use Akeneo\Channel\Infrastructure\Component\Model\Channel;
use Akeneo\Channel\Infrastructure\Doctrine\Repository\ChannelRepository;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator\ValidateChannel;
use Akeneo\Tool\Component\Api\Exception\InvalidQueryException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ValidateChannelTest extends TestCase
{
    private ValidateChannel $sut;

    protected function setUp(): void
    {
        $this->sut = new ValidateChannel();
    }

    public function test_it_throws_an_exception_with_non_existing_channel(): void
    {
        $channelRepository = $this->createMock(ChannelRepository::class);

        $channelRepository->expects($this->once())->method('findOneByIdentifier')->with('foo')->willReturn(null);
        $this->expectException(InvalidQueryException::class);
        $this->sut->validate('foo');
    }

    public function test_it_does_not_throw_an_exception_with_an_existing_channel(): void
    {
        $channelRepository = $this->createMock(ChannelRepository::class);

        $channelRepository->expects($this->once())->method('findOneByIdentifier')->with('foo')->willReturn(new Channel());
        $this->sut->shouldNotThrow(InvalidQueryException::class)->during('validate', ['foo']);
    }

    public function test_it_does_not_throw_an_exception_when_there_is_no_channel_provided(): void
    {
        $channelRepository = $this->createMock(ChannelRepository::class);

        $channelRepository->expects($this->never())->method('findOneByIdentifier')->with($this->anything());
        $this->sut->shouldNotThrow(InvalidQueryException::class)->during('validate', [null]);
    }
}

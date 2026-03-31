<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\UseCase\Validator;

use Akeneo\Channel\Infrastructure\Component\Model\Channel;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator\ValidateChannel;
use Akeneo\Tool\Component\Api\Exception\InvalidQueryException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ValidateChannelTest extends TestCase
{
    private IdentifiableObjectRepositoryInterface|MockObject $channelRepository;
    private ValidateChannel $sut;

    protected function setUp(): void
    {
        $this->channelRepository = $this->createMock(IdentifiableObjectRepositoryInterface::class);
        $this->sut = new ValidateChannel($this->channelRepository);
    }

    public function test_it_throws_an_exception_with_non_existing_channel(): void
    {
        $this->channelRepository->expects($this->once())->method('findOneByIdentifier')->with('foo')->willReturn(null);
        $this->expectException(InvalidQueryException::class);
        $this->sut->validate('foo');
    }

    public function test_it_does_not_throw_an_exception_with_an_existing_channel(): void
    {
        $this->channelRepository->expects($this->once())->method('findOneByIdentifier')->with('foo')->willReturn(new Channel());
        $this->sut->validate('foo');
        $this->addToAssertionCount(1);
    }

    public function test_it_does_not_throw_an_exception_when_there_is_no_channel_provided(): void
    {
        $this->channelRepository->expects($this->never())->method('findOneByIdentifier');
        $this->sut->validate(null);
        $this->addToAssertionCount(1);
    }
}

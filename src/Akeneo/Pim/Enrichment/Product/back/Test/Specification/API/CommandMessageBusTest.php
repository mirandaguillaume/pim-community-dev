<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\API;

use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\CommandMessageBus;
use Akeneo\Pim\Enrichment\Product\API\UnknownCommandException;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier;
use Akeneo\Test\Pim\Enrichment\Product\Helper\DummyHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\MessageBusInterface;

class CommandMessageBusTest extends TestCase
{
    private DummyHandler|MockObject $handler1;
    private DummyHandler|MockObject $handler2;
    private CommandMessageBus $sut;

    protected function setUp(): void
    {
        $this->handler1 = $this->createMock(DummyHandler::class);
        $this->handler2 = $this->createMock(DummyHandler::class);
        $this->sut = new CommandMessageBus([
            'Other' => $this->handler1,
            UpsertProductCommand::class => $this->handler2,
        ]);
    }

    public function test_it_is_a_message_bus(): void
    {
        $this->assertInstanceOf(CommandMessageBus::class, $this->sut);
        $this->assertInstanceOf(MessageBusInterface::class, $this->sut);
    }

    public function test_it_executes_the_correct_handler(): void
    {
        $command = UpsertProductCommand::createWithIdentifier(userId: 1, productIdentifier: ProductIdentifier::fromIdentifier('foo'), userIntents: []);
        $this->handler1->expects($this->never())->method('__invoke')->with($this->anything());
        $this->handler2->expects($this->once())->method('__invoke')->with($command);
        $this->sut->dispatch($command);
    }

    public function test_it_throws_an_exception_when_the_command_cannot_be_handled(): void
    {
        $this->handler1->expects($this->never())->method('__invoke')->with($this->anything());
        $this->handler2->expects($this->never())->method('__invoke')->with($this->anything());
        $this->expectException(UnknownCommandException::class);
        $this->sut->dispatch(new \stdClass());
    }
}

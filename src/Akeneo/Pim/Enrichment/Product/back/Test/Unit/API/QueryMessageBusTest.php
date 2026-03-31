<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\API;

use Akeneo\Pim\Enrichment\Product\API\Query\GetProductUuidsQuery;
use Akeneo\Pim\Enrichment\Product\API\QueryMessageBus;
use Akeneo\Pim\Enrichment\Product\API\UnknownQueryException;
use Akeneo\Test\Pim\Enrichment\Product\Helper\DummyHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

class QueryMessageBusTest extends TestCase
{
    private DummyHandler|MockObject $handler1;
    private DummyHandler|MockObject $handler2;
    private QueryMessageBus $sut;

    protected function setUp(): void
    {
        $this->handler1 = $this->createMock(DummyHandler::class);
        $this->handler2 = $this->createMock(DummyHandler::class);
        $this->sut = new QueryMessageBus([
            'Other' => $this->handler1,
            GetProductUuidsQuery::class => $this->handler2,
        ]);
    }

    public function test_it_is_a_query_message_bus(): void
    {
        $this->assertInstanceOf(QueryMessageBus::class, $this->sut);
        $this->assertInstanceOf(MessageBusInterface::class, $this->sut);
    }

    public function test_it_executes_the_correct_handler(): void
    {
        $query = new GetProductUuidsQuery([], 1);
        $this->handler1->expects($this->never())->method('__invoke')->with($this->anything());
        $result = new \stdClass();
        $this->handler2->expects($this->once())->method('__invoke')->with($query)->willReturn($result);
        $envelope = $this->sut->dispatch($query);
        $this->assertInstanceOf(Envelope::class, $envelope);
        $handledStamp = $envelope->last(HandledStamp::class);
        $this->assertInstanceOf(HandledStamp::class, $handledStamp);
        $this->assertSame($result, $handledStamp->getResult());
    }

    public function test_it_throws_an_exception_when_the_query_cannot_be_handled(): void
    {
        $this->handler1->expects($this->never())->method('__invoke')->with($this->anything());
        $this->handler2->expects($this->never())->method('__invoke')->with($this->anything());
        $this->expectException(UnknownQueryException::class);
        $this->sut->dispatch(new \stdClass());
    }
}

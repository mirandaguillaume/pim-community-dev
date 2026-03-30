<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\MessengerBundle\Middleware;

use Akeneo\Tool\Bundle\MessengerBundle\Middleware\HandleProcessMessageMiddleware;
use Akeneo\Tool\Bundle\MessengerBundle\Process\RunMessageProcess;
use Akeneo\Tool\Bundle\MessengerBundle\Stamp\CorrelationIdStamp;
use Akeneo\Tool\Bundle\MessengerBundle\Stamp\ReceiverStamp;
use Akeneo\Tool\Bundle\MessengerBundle\Stamp\TenantIdStamp;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;
use Symfony\Component\Messenger\Transport\InMemoryTransport;
use Symfony\Component\Messenger\Transport\Receiver\ReceiverInterface;

class HandleProcessMessageMiddlewareTest extends TestCase
{
    private RunMessageProcess|MockObject $runUcsMessageProcess;
    private LoggerInterface|MockObject $logger;
    private HandleProcessMessageMiddleware $sut;

    protected function setUp(): void
    {
        $this->runUcsMessageProcess = $this->createMock(RunMessageProcess::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->sut = new HandleProcessMessageMiddleware($this->runUcsMessageProcess, $this->logger);
    }

    public function test_it_is_a_middleware(): void
    {
        $this->assertInstanceOf(MiddlewareInterface::class, $this->sut);
        $this->assertInstanceOf(HandleProcessMessageMiddleware::class, $this->sut);
    }

    public function test_it_handles_an_envelope_with_a_tenant_id(): void
    {
        $stack = $this->createMock(StackInterface::class);
        $stackMiddleware = $this->createMock(MiddlewareInterface::class);

        $receiver = new InMemoryTransport();
        $message = new \stdClass();
        $envelope = new Envelope($message, [
                    new TenantIdStamp('pim-test'),
                    new ReceivedStamp('consumer1'),
                    new ReceiverStamp($receiver),
                    new CorrelationIdStamp('123456'),
                ]);
        $this->runUcsMessageProcess->expects($this->once())->method('__invoke')->with($message, 'consumer1', 'pim-test', '123456', $this->isType('callable'));
        $stack->method('next')->willReturn($stackMiddleware);
        $stackMiddleware->method('handle')->with($envelope, $stack)->willReturn($envelope);
        $this->sut->handle($envelope, $stack);
    }

    public function test_it_handles_an_envelope_without_tenant_and_correlation_ids(): void
    {
        $stack = $this->createMock(StackInterface::class);
        $stackMiddleware = $this->createMock(MiddlewareInterface::class);

        $receiver = new InMemoryTransport();
        $message = new \stdClass();
        $envelope = new Envelope($message, [
                    new ReceivedStamp('consumer1'),
                    new ReceiverStamp($receiver),
                ]);
        $this->runUcsMessageProcess->expects($this->once())->method('__invoke')->with($message, 'consumer1', null, null, $this->isType('callable'));
        $stack->method('next')->willReturn($stackMiddleware);
        $stackMiddleware->method('handle')->with($envelope, $stack)->willReturn($envelope);
        $this->sut->handle($envelope, $stack);
    }

    public function test_it_throws_an_exception_if_there_is_no_consumer_name(): void
    {
        $stack = $this->createMock(StackInterface::class);

        $envelope = new Envelope(new \stdClass(), [
                    new TenantIdStamp('pim-test'),
                    new CorrelationIdStamp('123456'),
                ]);
        $this->runUcsMessageProcess->expects($this->never())->method('__invoke')->with($this->anything());
        $stack->expects($this->never())->method('next');
        $this->expectException(\LogicException::class);
        $this->sut->handle($envelope, $stack);
    }

    public function test_it_throws_an_exception_if_there_is_no_receiver(): void
    {
        $stack = $this->createMock(StackInterface::class);

        $envelope = new Envelope(new \stdClass(), [
                    new TenantIdStamp('pim-test'),
                    new CorrelationIdStamp('123456'),
                    new ReceivedStamp('consumer1'),
                ]);
        $this->runUcsMessageProcess->expects($this->never())->method('__invoke');
        $stack->expects($this->never())->method('next');
        $this->expectException(\LogicException::class);
        $this->sut->handle($envelope, $stack);
    }
}

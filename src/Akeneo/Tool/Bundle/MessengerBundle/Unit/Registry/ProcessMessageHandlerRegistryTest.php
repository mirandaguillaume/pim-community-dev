<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\MessengerBundle\Registry;

use Akeneo\Tool\Bundle\MessengerBundle\Registry\ProcessMessageHandlerRegistry;
use PHPUnit\Framework\TestCase;

class ProcessMessageHandlerRegistryTest extends TestCase
{
    private ProcessMessageHandlerRegistry $sut;

    protected function setUp(): void
    {
        $this->sut = new ProcessMessageHandlerRegistry();
    }

    public function test_it_returns_the_handler(): void
    {
        $handler1 = new class {
            public function __invoke(object $message)
            {
            }
        }
        ;
        $handler2 = new class {
            public function __invoke(object $message)
            {
            }
        }
        ;
        $this->sut->registerHandler($handler1, 'consumer1');
        $this->sut->registerHandler($handler2, 'consumer2');
        $this->assertSame($handler1, $this->sut->getHandler('consumer1'));
        $this->assertSame($handler2, $this->sut->getHandler('consumer2'));
    }

    public function test_it_throws_an_exception_when_no_handler_is_found(): void
    {
        $this->expectException(\LogicException::class);
        $this->sut->getHandler('unknown');
    }

    public function test_it_throws_an_exception_when_handler_is_registerer_twice_for_a_consumer(): void
    {
        $handler1 = new class {
            public function __invoke(object $message)
            {
            }
        }
        ;
        $handler2 = new class {
            public function __invoke(object $message)
            {
            }
        }
        ;
        $this->sut->registerHandler($handler1, 'consumer1');
        $this->expectException(\LogicException::class);
        $this->sut->registerHandler($handler2, 'consumer1');
    }

    public function test_it_throws_an_exception_when_handler_is_not_invokable(): void
    {
        $handler = new class {}
        ;
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->registerHandler($handler, 'not_invokable');
    }
}

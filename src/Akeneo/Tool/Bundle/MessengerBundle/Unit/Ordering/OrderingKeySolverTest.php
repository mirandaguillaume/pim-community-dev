<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\MessengerBundle\Ordering;

use Akeneo\Tool\Bundle\MessengerBundle\Ordering\OrderingKeyResolverInterface;
use Akeneo\Tool\Bundle\MessengerBundle\Ordering\OrderingKeySolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;

class OrderingKeySolverTest extends TestCase
{
    private OrderingKeyResolverInterface|MockObject $candidate1;
    private OrderingKeyResolverInterface|MockObject $candidate2;
    private OrderingKeySolver $sut;

    protected function setUp(): void
    {
        $this->candidate1 = $this->createMock(OrderingKeyResolverInterface::class);
        $this->candidate2 = $this->createMock(OrderingKeyResolverInterface::class);
        $this->sut = new OrderingKeySolver([$this->candidate1, $this->candidate2]);
    }

    public function test_it_can_be_instantiable(): void
    {
        $this->assertInstanceOf(OrderingKeySolver::class, $this->sut);
    }

    public function test_it_returns_null_when_no_candidates_support_the_envelope(): void
    {
        $envelope = new Envelope(new \stdClass());
        $this->candidate1->method('supports')->with($envelope)->willReturn(false);
        $this->candidate2->method('supports')->with($envelope)->willReturn(false);
        $this->assertNull($this->sut->solve($envelope));
    }

    public function test_it_returns_the_key_when_a_candidate_supports_the_envelope(): void
    {
        $envelope = new Envelope(new \stdClass());
        $this->candidate1->method('supports')->with($envelope)->willReturn(false);
        $this->candidate2->method('supports')->with($envelope)->willReturn(true);
        $this->candidate2->method('resolve')->with($envelope)->willReturn('the_key');
        $this->assertSame('the_key', $this->sut->solve($envelope));
    }
}

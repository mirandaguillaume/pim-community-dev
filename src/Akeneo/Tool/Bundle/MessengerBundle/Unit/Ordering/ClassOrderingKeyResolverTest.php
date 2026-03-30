<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\MessengerBundle\Ordering;

use Akeneo\Tool\Bundle\MessengerBundle\Ordering\ClassOrderingKeyResolver;
use Akeneo\Tool\Bundle\MessengerBundle\Ordering\OrderingKeyResolverInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;

class ClassOrderingKeyResolverTest extends TestCase
{
    private ClassOrderingKeyResolver $sut;

    protected function setUp(): void
    {
        $this->sut = new ClassOrderingKeyResolver(\stdClass::class, 'the_key');
    }

    public function test_it_is_a_ordering_key_candidate(): void
    {
        $this->assertInstanceOf(OrderingKeyResolverInterface::class, $this->sut);
        $this->assertInstanceOf(ClassOrderingKeyResolver::class, $this->sut);
    }

    public function test_it_supports_an_envelope_with_std_class_only(): void
    {
        $this->assertSame(true, $this->sut->supports(new Envelope(new \stdClass())));
        $this->assertSame(false, $this->sut->supports(new Envelope(new class {})));
    }

    public function test_it_returns_the_key(): void
    {
        $this->assertSame('the_key', $this->sut->resolve(new Envelope(new \stdClass())));
    }
}

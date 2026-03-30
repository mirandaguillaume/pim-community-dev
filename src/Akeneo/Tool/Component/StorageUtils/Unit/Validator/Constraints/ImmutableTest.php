<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\StorageUtils\Validator\Constraints;

use Akeneo\Tool\Component\StorageUtils\Validator\Constraints\Immutable;
use PHPUnit\Framework\TestCase;

class ImmutableTest extends TestCase
{
    private Immutable $sut;

    protected function setUp(): void
    {
        $this->sut = new Immutable();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(Immutable::class, $this->sut);
    }

    public function test_it_is_a_validator_constraint(): void
    {
        $this->assertInstanceOf(\Symfony\Component\Validator\Constraint::class, $this->sut);
    }

    public function test_it_has_message(): void
    {
        $this->assertSame('This property cannot be changed.', $this->sut->message);
    }

    public function test_it_can_get_targets(): void
    {
        $this->assertSame('class', $this->sut->getTargets());
    }
}

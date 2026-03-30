<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Application\Webhook\Validation;

use Akeneo\Connectivity\Connection\Application\Webhook\Validation\EventSubscriptionsLimit;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;

class EventSubscriptionsLimitTest extends TestCase
{
    private EventSubscriptionsLimit $sut;

    protected function setUp(): void
    {
        $this->sut = new EventSubscriptionsLimit();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(EventSubscriptionsLimit::class, $this->sut);
    }

    public function test_it_is_a_constraint(): void
    {
        $this->assertInstanceOf(Constraint::class, $this->sut);
    }

    public function test_it_provides_a_target(): void
    {
        $this->assertSame(Constraint::CLASS_CONSTRAINT, $this->sut->getTargets());
    }
}

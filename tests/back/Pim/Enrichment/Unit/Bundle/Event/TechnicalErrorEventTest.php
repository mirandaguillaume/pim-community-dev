<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\Event;

use Akeneo\Pim\Enrichment\Bundle\Event\TechnicalErrorEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TechnicalErrorEventTest extends TestCase
{
    private \Exception|MockObject $error;
    private TechnicalErrorEvent $sut;

    protected function setUp(): void
    {
        $this->error = $this->createMock(\Exception::class);
        $this->sut = new TechnicalErrorEvent($this->error);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(TechnicalErrorEvent::class, $this->sut);
    }

    public function test_it_returns_the_error(): void
    {
        $this->assertSame($this->error, $this->sut->getError());
    }
}

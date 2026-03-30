<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Batch\Event;

use Akeneo\Tool\Component\Batch\Item\InvalidItemInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Tool\Component\Batch\Event\InvalidItemEvent;

class InvalidItemEventTest extends TestCase
{
    private InvalidItemInterface|MockObject $invalidItem;
    private InvalidItemEvent $sut;

    protected function setUp(): void
    {
        $this->invalidItem = $this->createMock(InvalidItemInterface::class);
        $this->sut = new InvalidItemEvent(
            $this->invalidItem,
            'Foo\\Bar\\Baz',
            'No special reason %param%.',
            ['%param%' => 'Item1']
        );
    }

    public function test_it_provides_item_class(): void
    {
        $this->assertSame('Foo\\Bar\\Baz', $this->sut->getClass());
    }

    public function test_it_provides_invalidity_reason(): void
    {
        $this->assertSame('No special reason %param%.', $this->sut->getReason());
    }

    public function test_it_provides_invalidity_reason_params(): void
    {
        $this->assertSame(['%param%' => 'Item1'], $this->sut->getReasonParameters());
    }

    public function test_it_provides_invalid_item(): void
    {
        $this->assertSame($this->invalidItem, $this->sut->getItem());
    }
}

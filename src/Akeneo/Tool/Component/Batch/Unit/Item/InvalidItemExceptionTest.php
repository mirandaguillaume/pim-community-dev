<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Batch\Item;

use Akeneo\Tool\Component\Batch\Item\InvalidItemInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Tool\Component\Batch\Item\InvalidItemException;

class InvalidItemExceptionTest extends TestCase
{
    private InvalidItemInterface|MockObject $invalidItem;
    private InvalidItemException $sut;

    protected function setUp(): void
    {
        $this->invalidItem = $this->createMock(InvalidItemInterface::class);
        $this->sut = new InvalidItemException(
            'Tango is down, I repeat...',
            $this->invalidItem
        );
        $this->invalidItem->method('getInvalidData')->willReturn(['foo' => 'fighter']);
    }

    public function test_it_provides_the_message(): void
    {
        $this->assertSame('Tango is down, I repeat...', $this->sut->getMessage());
    }

    public function test_it_provides_the_invalid_item(): void
    {
        $this->assertSame($this->invalidItem, $this->sut->getItem());
    }
}

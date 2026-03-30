<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Batch\Item;

use PHPUnit\Framework\TestCase;
use spec\Akeneo\Tool\Component\Batch\Item\ExecutionContext;

class ExecutionContextTest extends TestCase
{
    private ExecutionContext $sut;

    protected function setUp(): void
    {
        $this->sut = new ExecutionContext();
    }

    public function test_it_is_dirty(): void
    {
        $this->assertSame(false, $this->sut->isDirty());
        $this->sut->put('test_key', 'test_value');
        $this->assertSame(true, $this->sut->isDirty());
    }

    public function test_it_allows_to_change_dirty_flag(): void
    {
        $this->assertSame(false, $this->sut->isDirty());
        $this->sut->put('test_key', 'test_value');
        $this->assertSame(true, $this->sut->isDirty());
        $this->sut->clearDirtyFlag();
        $this->assertSame(false, $this->sut->isDirty());
    }

    public function test_it_allows_to_add_value(): void
    {
        $this->sut->put('test_key', 'test_value');
        $this->assertSame(true, $this->sut->isDirty());
        $this->assertSame('test_value', $this->sut->get('test_key'));
    }

    public function test_it_allows_to_remove_value(): void
    {
        $this->sut->put('test_key', 'test_value');
        $this->assertSame('test_value', $this->sut->get('test_key'));
        $this->sut->remove('test_key');
        $this->assertNull($this->sut->get('test_key'));
    }

    public function test_it_provides_keys(): void
    {
        $this->sut->put('test_key', 'test_value');
        $this->sut->put('test_key2', 'test_value');
        $this->assertSame(['test_key', 'test_key2'], $this->sut->getKeys());
    }
}

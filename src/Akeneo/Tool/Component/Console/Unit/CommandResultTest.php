<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Console;

use Akeneo\Tool\Component\Console\CommandResult;
use Akeneo\Tool\Component\Console\CommandResultInterface;
use PHPUnit\Framework\TestCase;

class CommandResultTest extends TestCase
{
    private array $output;
    private CommandResult $sut;

    protected function setUp(): void
    {
        $this->output = ['line1', 'line2'];
        $this->sut = new CommandResult($this->output, 0);
    }

    public function test_it_can_be_initialized(): void
    {
        $this->assertInstanceOf(CommandResult::class, $this->sut);
        $this->assertInstanceOf(CommandResultInterface::class, $this->sut);
    }

    public function test_it_can_return_command_output(): void
    {
        $this->assertSame($this->output, $this->sut->getCommandOutput());
    }

    public function test_it_can_return_command_status(): void
    {
        $this->assertSame(0, $this->sut->getCommandStatus());
    }
}

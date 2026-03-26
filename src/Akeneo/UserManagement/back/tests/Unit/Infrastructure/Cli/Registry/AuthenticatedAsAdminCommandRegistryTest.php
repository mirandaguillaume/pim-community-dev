<?php

declare(strict_types=1);

namespace Akeneo\Test\UserManagement\Unit\Infrastructure\Cli\Registry;

use Akeneo\UserManagement\Infrastructure\Cli\Registry\AuthenticatedAsAdminCommandRegistry;
use PHPUnit\Framework\TestCase;

class AuthenticatedAsAdminCommandRegistryTest extends TestCase
{
    private AuthenticatedAsAdminCommandRegistry $sut;

    protected function setUp(): void
    {
        $this->sut = new AuthenticatedAsAdminCommandRegistry();
    }

    public function test_it_registers_authenticated_as_admin_commands(): void
    {
        $this->assertSame(false, $this->sut->isCommandRegistered('akeneo:batch:job'));
        $this->sut->registerCommand('akeneo:batch:job');
        $this->sut->registerCommand('pim:install');
        $this->assertSame(true, $this->sut->isCommandRegistered('akeneo:batch:job'));
        $this->assertSame(false, $this->sut->isCommandRegistered('debug:router'));
    }
}

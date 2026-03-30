<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Application\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\Command\DeleteAppCommand;
use PHPUnit\Framework\TestCase;

class DeleteAppCommandTest extends TestCase
{
    private DeleteAppCommand $sut;

    protected function setUp(): void
    {
        $this->sut = new DeleteAppCommand('test');
    }

    public function test_it_is_a_delete_app_command(): void
    {
        $this->assertInstanceOf(DeleteAppCommand::class, $this->sut);
    }

    public function test_it_gets_app_id(): void
    {
        $this->assertSame('test', $this->sut->getAppId());
    }
}

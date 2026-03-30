<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Application\CustomApps\Command;

use Akeneo\Connectivity\Connection\Application\CustomApps\Command\CreateCustomAppCommand;
use PHPUnit\Framework\TestCase;

class CreateCustomAppCommandTest extends TestCase
{
    private CreateCustomAppCommand $sut;

    protected function setUp(): void
    {
        $this->sut = new CreateCustomAppCommand(
            'UUID1234',
            'name of the app',
            'http://activate_url.test',
            'http://callback_url.test',
            42,
        );
    }

    public function test_it_is_a_create_custom_app_command(): void
    {
        $this->assertInstanceOf(CreateCustomAppCommand::class, $this->sut);
    }

    public function test_it_provides_a_client_id(): void
    {
        $this->assertSame('UUID1234', $this->sut->clientId);
    }

    public function test_it_provides_a_name(): void
    {
        $this->assertSame('name of the app', $this->sut->name);
    }

    public function test_it_provides_an_activate_url(): void
    {
        $this->assertSame('http://activate_url.test', $this->sut->activateUrl);
    }

    public function test_it_provides_a_callback_url(): void
    {
        $this->assertSame('http://callback_url.test', $this->sut->callbackUrl);
    }

    public function test_it_provides_a_user_id(): void
    {
        $this->assertSame(42, $this->sut->userId);
    }
}

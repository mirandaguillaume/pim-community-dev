<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Application\CustomApps\Command;

use Akeneo\Connectivity\Connection\Application\CustomApps\Command\CreateCustomAppCommand;
use Akeneo\Connectivity\Connection\Application\CustomApps\Command\CreateCustomAppCommandHandler;
use Akeneo\Connectivity\Connection\Application\RandomCodeGeneratorInterface;
use Akeneo\Connectivity\Connection\Domain\CustomApps\Persistence\CreateCustomAppQueryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CreateCustomAppCommandHandlerTest extends TestCase
{
    private RandomCodeGeneratorInterface|MockObject $randomCodeGenerator;
    private CreateCustomAppQueryInterface|MockObject $createCustomAppQuery;
    private CreateCustomAppCommandHandler $sut;

    protected function setUp(): void
    {
        $this->randomCodeGenerator = $this->createMock(RandomCodeGeneratorInterface::class);
        $this->createCustomAppQuery = $this->createMock(CreateCustomAppQueryInterface::class);
        $this->sut = new CreateCustomAppCommandHandler($this->randomCodeGenerator, $this->createCustomAppQuery);
    }

    public function test_it_is_a_create_custom_app_command_handler(): void
    {
        $this->assertInstanceOf(CreateCustomAppCommandHandler::class, $this->sut);
    }

    public function test_it_creates_a_custom_app(): void
    {
        $command = new CreateCustomAppCommand(
            'clientId1234',
            'Test app name',
            'http://activate-url.test',
            'http://callback-url.test',
            42,
        );
        $this->randomCodeGenerator->method('generate')->willReturn('abcd');
        $this->createCustomAppQuery->expects($this->once())->method('execute')->with(
            'clientId1234',
            'Test app name',
            'http://activate-url.test',
            'http://callback-url.test',
            'abcd',
            42
        );
        $this->sut->handle($command);
    }
}

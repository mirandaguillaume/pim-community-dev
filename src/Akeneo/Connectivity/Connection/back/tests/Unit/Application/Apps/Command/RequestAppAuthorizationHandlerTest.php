<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Application\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\AppAuthorizationSessionInterface;
use Akeneo\Connectivity\Connection\Application\Apps\Command\RequestAppAuthorizationCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\RequestAppAuthorizationHandler;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppAuthorization;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ScopeMapperInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ScopeMapperRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RequestAppAuthorizationHandlerTest extends TestCase
{
    private ValidatorInterface|MockObject $validator;
    private AppAuthorizationSessionInterface|MockObject $session;
    private ScopeMapperInterface|MockObject $scopeMapperChannel;
    private RequestAppAuthorizationHandler $sut;

    protected function setUp(): void
    {
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->session = $this->createMock(AppAuthorizationSessionInterface::class);
        $this->scopeMapperChannel = $this->createMock(ScopeMapperInterface::class);
        $this->sut = new RequestAppAuthorizationHandler(
            $this->validator,
            $this->session,
            $scopeMapperRegistry,
        );
        $this->scopeMapperChannel->method('getScopes')->willReturn(['read_channel_localization', 'read_channel_settings']);
        $scopeMapperRegistry = new ScopeMapperRegistry([$this->scopeMapperChannel]);
    }

    public function test_it_is_a_request_app_authorization_handler(): void
    {
        $this->assertInstanceOf(RequestAppAuthorizationHandler::class, $this->sut);
    }

    public function test_it_should_initialize_the_session(): void
    {
        $command = new RequestAppAuthorizationCommand(
            'client_id',
            'response_type',
            'read_channel_localization',
            'http://url.test',
        );
        $this->validator->method('validate')->with($command)->willReturn(new ConstraintViolationList());
        $this->session->expects($this->once())->method('initialize')->with($this->isInstanceOf(AppAuthorization::class));
        $this->sut->handle($command);
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth;

use Akeneo\Connectivity\Connection\Application\RandomCodeGeneratorInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppConfirmation;
use Akeneo\Connectivity\Connection\Domain\ClockInterface;
use Akeneo\Tool\Bundle\ApiBundle\OAuth\IOAuth2GrantCode;
use Akeneo\Tool\Bundle\ApiBundle\OAuth\Model\ClientInterface;
use Akeneo\Tool\Bundle\ApiBundle\OAuth\Model\ClientManagerInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth\AuthorizationCodeGenerator;

class AuthorizationCodeGeneratorTest extends TestCase
{
    private ClientManagerInterface|MockObject $clientManager;
    private UserRepositoryInterface|MockObject $userRepository;
    private IOAuth2GrantCode|MockObject $storage;
    private RandomCodeGeneratorInterface|MockObject $randomCodeGenerator;
    private ClockInterface|MockObject $clock;
    private AuthorizationCodeGenerator $sut;

    protected function setUp(): void
    {
        $this->clientManager = $this->createMock(ClientManagerInterface::class);
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->storage = $this->createMock(IOAuth2GrantCode::class);
        $this->randomCodeGenerator = $this->createMock(RandomCodeGeneratorInterface::class);
        $this->clock = $this->createMock(ClockInterface::class);
        $this->sut = new AuthorizationCodeGenerator(
            $this->clientManager,
            $this->userRepository,
            $this->storage,
            $this->randomCodeGenerator,
            $this->clock
        );
    }

    public function test_it_generates_an_authorization_code(): void
    {
        $now = $this->createMock(DateTimeImmutable::class);
        $client = $this->createMock(ClientInterface::class);
        $pimUser = $this->createMock(UserInterface::class);

        $code = 'MjE3NTE3YjQ0MzcwYTU1YjZlZjRhMzZiZGQwOWZmMDhlMmFkMzIxNmM5YjhiYjg2M2QwMjg4ZGIzZjE5ZjAzMg';
        $appId = '2ef7885a-4951-4d5a-bd28-1a8988b9476e';
        $appUserId = 3;
        $userGroup = 'my_user_group';
        $fosClientId = 2;
        $appConfirmation = AppConfirmation::create(
            $appId,
            $appUserId,
            $userGroup,
            $fosClientId,
        );
        $redirectUri = 'https://foo.example.com/oauth/callback';
        $timestamp = 1_634_572_000;
        $pimUserId = 1;
        $this->randomCodeGenerator->method('generate')->willReturn($code);
        $this->userRepository->method('find')->with($pimUserId)->willReturn($pimUser);
        $this->clientManager->method('findClientBy')->with(['id' => $fosClientId])->willReturn($client);
        $this->clock->method('now')->willReturn($now);
        $now->method('getTimestamp')->willReturn($timestamp);
        $this->storage->expects($this->once())->method('createAuthCode')->with(
            $code,
            $client,
            $pimUser,
            $redirectUri,
            $timestamp + 30
        );
        $this->assertSame($code, $this->sut->generate($appConfirmation, $pimUserId, $redirectUri));
    }
}

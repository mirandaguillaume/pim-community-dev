<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\ApiBundle\OAuth;

use Akeneo\Tool\Bundle\ApiBundle\Entity\AccessToken;
use Akeneo\Tool\Bundle\ApiBundle\Entity\AuthCode;
use Akeneo\Tool\Bundle\ApiBundle\Entity\Client;
use Akeneo\Tool\Bundle\ApiBundle\Entity\RefreshToken;
use Akeneo\Tool\Bundle\ApiBundle\OAuth\IOAuth2GrantCode;
use Akeneo\Tool\Bundle\ApiBundle\OAuth\Model\ClientInterface;
use Akeneo\Tool\Bundle\ApiBundle\OAuth\OAuthStorage;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\PasswordHasher\LegacyPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class OAuthStorageTest extends TestCase
{
    private EntityManagerInterface|MockObject $entityManager;
    private UserProviderInterface|MockObject $userProvider;
    private PasswordHasherFactoryInterface|MockObject $passwordHasherFactory;
    private EntityRepository|MockObject $repository;
    private OAuthStorage $sut;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->userProvider = $this->createMock(UserProviderInterface::class);
        $this->passwordHasherFactory = $this->createMock(PasswordHasherFactoryInterface::class);
        $this->repository = $this->createMock(EntityRepository::class);
        $this->sut = new OAuthStorage(
            $this->entityManager,
            $this->userProvider,
            $this->passwordHasherFactory,
        );
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(OAuthStorage::class, $this->sut);
        $this->assertInstanceOf(IOAuth2GrantCode::class, $this->sut);
    }

    public function test_it_gets_a_client_from_a_public_id(): void
    {
        $client = $this->createMock(Client::class);
        $this->entityManager->method('getRepository')->with(Client::class)->willReturn($this->repository);
        $this->repository->expects($this->once())
            ->method('findOneBy')
            ->with(['id' => 6, 'randomId' => '5ff5299118e2c'])
            ->willReturn($client);
        $this->repository->expects($this->never())->method('find')->with($this->anything());

        $this->assertSame($client, $this->sut->getClient('6_5ff5299118e2c'));
    }

    public function test_it_splits_the_public_id_on_the_first_underscore_only(): void
    {
        $this->entityManager->method('getRepository')->with(Client::class)->willReturn($this->repository);
        $this->repository->expects($this->once())
            ->method('findOneBy')
            ->with(['id' => 6, 'randomId' => 'random_id_with_underscores'])
            ->willReturn(null);

        $this->assertNull($this->sut->getClient('6_random_id_with_underscores'));
    }

    public function test_it_gets_a_client_from_a_raw_id_when_the_client_id_holds_no_underscore(): void
    {
        $client = $this->createMock(Client::class);
        $this->entityManager->method('getRepository')->with(Client::class)->willReturn($this->repository);
        $this->repository->expects($this->once())->method('find')->with('6')->willReturn($client);
        $this->repository->expects($this->never())->method('findOneBy')->with($this->anything());

        $this->assertSame($client, $this->sut->getClient('6'));
    }

    public function test_it_returns_no_client_when_the_public_id_matches_nothing(): void
    {
        $this->entityManager->method('getRepository')->with(Client::class)->willReturn($this->repository);
        $this->repository->method('findOneBy')->willReturn(null);

        $this->assertNull($this->sut->getClient('6_unknown_random_id'));
    }

    public function test_it_checks_the_client_credentials_against_the_client_secret(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('checkSecret')->with('the_secret')->willReturn(true);

        $this->assertTrue($this->sut->checkClientCredentials($client, 'the_secret'));
    }

    public function test_it_rejects_a_client_with_a_wrong_secret(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('checkSecret')->with('wrong_secret')->willReturn(false);

        $this->assertFalse($this->sut->checkClientCredentials($client, 'wrong_secret'));
    }

    public function test_it_gets_an_authorization_code_by_its_token(): void
    {
        $authCode = new AuthCode();
        $this->entityManager->method('getRepository')->with(AuthCode::class)->willReturn($this->repository);
        $this->repository->expects($this->once())
            ->method('findOneBy')
            ->with(['token' => 'an_authorization_code'])
            ->willReturn($authCode);

        $this->assertSame($authCode, $this->sut->getAuthCode('an_authorization_code'));
    }

    public function test_it_returns_no_authorization_code_when_the_token_is_unknown(): void
    {
        $this->entityManager->method('getRepository')->with(AuthCode::class)->willReturn($this->repository);
        $this->repository->method('findOneBy')->with(['token' => 'unknown_code'])->willReturn(null);

        $this->assertNull($this->sut->getAuthCode('unknown_code'));
    }

    public function test_it_creates_an_authorization_code(): void
    {
        $client = $this->createMock(Client::class);
        $user = $this->createMock(UserInterface::class);
        $persistedAuthCode = null;

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(AuthCode::class))
            ->willReturnCallback(function (AuthCode $authCode) use (&$persistedAuthCode): void {
                $persistedAuthCode = $authCode;
            });
        $this->entityManager->expects($this->once())->method('flush');

        $this->sut->createAuthCode(
            'an_authorization_code',
            $client,
            $user,
            'http://localhost/callback',
            1234567890,
            'read_products write_products',
        );

        $this->assertInstanceOf(AuthCode::class, $persistedAuthCode);
        $this->assertSame('an_authorization_code', $persistedAuthCode->getToken());
        $this->assertSame($client, $persistedAuthCode->getClient());
        $this->assertSame($user, $persistedAuthCode->getData());
        $this->assertSame('http://localhost/callback', $persistedAuthCode->getRedirectUri());
        $this->assertSame(1234567890, $persistedAuthCode->getExpiresAt());
        $this->assertSame('read_products write_products', $persistedAuthCode->getScope());
    }

    public function test_it_deletes_the_authorization_code_when_it_is_marked_as_used(): void
    {
        $authCode = new AuthCode();
        $this->entityManager->method('getRepository')->with(AuthCode::class)->willReturn($this->repository);
        $this->repository->expects($this->once())
            ->method('findOneBy')
            ->with(['token' => 'an_authorization_code'])
            ->willReturn($authCode);
        $this->entityManager->expects($this->once())->method('remove')->with($authCode);
        $this->entityManager->expects($this->once())->method('flush');

        $this->sut->markAuthCodeAsUsed('an_authorization_code');
    }

    public function test_it_does_not_delete_anything_when_marking_an_unknown_authorization_code_as_used(): void
    {
        $this->entityManager->method('getRepository')->with(AuthCode::class)->willReturn($this->repository);
        $this->repository->method('findOneBy')->with(['token' => 'unknown_code'])->willReturn(null);
        $this->entityManager->expects($this->never())->method('remove')->with($this->anything());
        $this->entityManager->expects($this->never())->method('flush');

        $this->sut->markAuthCodeAsUsed('unknown_code');
    }

    public function test_it_creates_and_persists_an_access_token(): void
    {
        $client = $this->createMock(Client::class);
        $user = $this->createMock(UserInterface::class);
        $persistedToken = null;

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(AccessToken::class))
            ->willReturnCallback(function (AccessToken $token) use (&$persistedToken): void {
                $persistedToken = $token;
            });
        $this->entityManager->expects($this->once())->method('flush');

        $token = $this->sut->createAccessToken(
            'an_access_token',
            $client,
            $user,
            null,
            'read_products write_products delete_products openid profile email',
        );

        $this->assertInstanceOf(AccessToken::class, $token);
        $this->assertSame($persistedToken, $token);
        $this->assertSame('an_access_token', $token->getToken());
        $this->assertSame($client, $token->getClient());
        $this->assertSame($user, $token->getData());
        $this->assertNull($token->getExpiresAt());
        $this->assertSame('read_products write_products delete_products openid profile email', $token->getScope());
    }

    public function test_it_gets_an_access_token_by_its_token(): void
    {
        $accessToken = new AccessToken();
        $this->entityManager->method('getRepository')->with(AccessToken::class)->willReturn($this->repository);
        $this->repository->expects($this->once())
            ->method('findOneBy')
            ->with(['token' => 'an_access_token'])
            ->willReturn($accessToken);

        $this->assertSame($accessToken, $this->sut->getAccessToken('an_access_token'));
    }

    public function test_it_returns_no_access_token_when_the_token_is_unknown(): void
    {
        $this->entityManager->method('getRepository')->with(AccessToken::class)->willReturn($this->repository);
        $this->repository->method('findOneBy')->willReturn(null);

        $this->assertNull($this->sut->getAccessToken('unknown_token'));
    }

    public function test_it_creates_and_persists_a_refresh_token(): void
    {
        $client = $this->createMock(Client::class);
        $user = $this->createMock(UserInterface::class);

        $this->entityManager->expects($this->once())->method('persist')->with($this->isInstanceOf(RefreshToken::class));
        $this->entityManager->expects($this->once())->method('flush');

        $token = $this->sut->createRefreshToken('a_refresh_token', $client, $user, 1234567890, 'read_products');

        $this->assertInstanceOf(RefreshToken::class, $token);
        $this->assertSame('a_refresh_token', $token->getToken());
        $this->assertSame($client, $token->getClient());
        $this->assertSame($user, $token->getData());
        $this->assertSame(1234567890, $token->getExpiresAt());
        $this->assertSame('read_products', $token->getScope());
    }

    public function test_it_gets_a_refresh_token_by_its_token(): void
    {
        $refreshToken = new RefreshToken();
        $this->entityManager->method('getRepository')->with(RefreshToken::class)->willReturn($this->repository);
        $this->repository->expects($this->once())
            ->method('findOneBy')
            ->with(['token' => 'a_refresh_token'])
            ->willReturn($refreshToken);

        $this->assertSame($refreshToken, $this->sut->getRefreshToken('a_refresh_token'));
    }

    public function test_it_deletes_a_refresh_token(): void
    {
        $refreshToken = new RefreshToken();
        $this->entityManager->method('getRepository')->with(RefreshToken::class)->willReturn($this->repository);
        $this->repository->method('findOneBy')->with(['token' => 'a_refresh_token'])->willReturn($refreshToken);
        $this->entityManager->expects($this->once())->method('remove')->with($refreshToken);
        $this->entityManager->expects($this->once())->method('flush');

        $this->sut->unsetRefreshToken('a_refresh_token');
    }

    public function test_it_does_not_delete_anything_when_unsetting_an_unknown_refresh_token(): void
    {
        $this->entityManager->method('getRepository')->with(RefreshToken::class)->willReturn($this->repository);
        $this->repository->method('findOneBy')->with(['token' => 'unknown_token'])->willReturn(null);
        $this->entityManager->expects($this->never())->method('remove')->with($this->anything());
        $this->entityManager->expects($this->never())->method('flush');

        $this->sut->unsetRefreshToken('unknown_token');
    }

    public function test_it_returns_the_user_when_the_credentials_are_valid(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $user = $this->createMock(UserInterface::class);
        $user->method('getPassword')->willReturn('a_hashed_password');
        $user->method('getSalt')->willReturn('a_salt');
        $this->userProvider->expects($this->once())
            ->method('loadUserByIdentifier')
            ->with('julia')
            ->willReturn($user);

        $hasher = $this->createMock(LegacyPasswordHasherInterface::class);
        $hasher->expects($this->once())
            ->method('verify')
            ->with('a_hashed_password', 'the_plain_password', 'a_salt')
            ->willReturn(true);
        $this->passwordHasherFactory->expects($this->once())
            ->method('getPasswordHasher')
            ->with($user)
            ->willReturn($hasher);

        $this->assertSame(['data' => $user], $this->sut->checkUserCredentials($client, 'julia', 'the_plain_password'));
    }

    public function test_it_rejects_the_credentials_when_the_password_does_not_match(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $user = $this->createMock(UserInterface::class);
        $user->method('getPassword')->willReturn('a_hashed_password');
        $user->method('getSalt')->willReturn('a_salt');
        $this->userProvider->method('loadUserByIdentifier')->with('julia')->willReturn($user);

        $hasher = $this->createMock(LegacyPasswordHasherInterface::class);
        $hasher->expects($this->once())
            ->method('verify')
            ->with('a_hashed_password', 'a_wrong_password', 'a_salt')
            ->willReturn(false);
        $this->passwordHasherFactory->method('getPasswordHasher')->with($user)->willReturn($hasher);

        $this->assertFalse($this->sut->checkUserCredentials($client, 'julia', 'a_wrong_password'));
    }

    public function test_it_rejects_the_credentials_of_an_unknown_user(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $this->userProvider->method('loadUserByIdentifier')
            ->with('unknown_user')
            ->willThrowException(new UserNotFoundException());
        $this->passwordHasherFactory->expects($this->never())
            ->method('getPasswordHasher')
            ->with($this->anything());

        $this->assertFalse($this->sut->checkUserCredentials($client, 'unknown_user', 'the_plain_password'));
    }
}

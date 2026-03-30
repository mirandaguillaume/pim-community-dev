<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth;

use Akeneo\Connectivity\Connection\Application\Apps\Service\CreateAccessTokenInterface;
use Akeneo\Connectivity\Connection\Application\RandomCodeGeneratorInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppConfirmation;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\DeleteAccessTokensQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\GetAccessTokenQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\GetAppConfirmationQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\GetConnectedAppScopesQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\GetUserConsentedAuthenticationScopesQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\GetUserConsentedAuthenticationUuidQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\ValueObject\ScopeList;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth\ClientProviderInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth\CreateAccessToken;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth\CreateJsonWebToken;
use Akeneo\Tool\Bundle\ApiBundle\Entity\Client;
use Akeneo\Tool\Bundle\ApiBundle\OAuth\IOAuth2AuthCode;
use Akeneo\Tool\Bundle\ApiBundle\OAuth\IOAuth2GrantCode;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateAccessTokenTest extends TestCase
{
    private IOAuth2GrantCode|MockObject $storage;
    private ClientProviderInterface|MockObject $clientProvider;
    private RandomCodeGeneratorInterface|MockObject $randomCodeGenerator;
    private GetAppConfirmationQueryInterface|MockObject $appConfirmationQuery;
    private UserRepositoryInterface|MockObject $userRepository;
    private CreateJsonWebToken|MockObject $createJsonWebToken;
    private GetConnectedAppScopesQueryInterface|MockObject $getConnectedAppScopesQuery;
    private GetUserConsentedAuthenticationUuidQueryInterface|MockObject $getUserConsentedAuthenticationUuidQuery;
    private GetUserConsentedAuthenticationScopesQueryInterface|MockObject $getUserConsentedAuthenticationScopesQuery;
    private GetAccessTokenQueryInterface|MockObject $getAccessTokenQuery;
    private DeleteAccessTokensQueryInterface|MockObject $deleteAccessTokensQuery;
    private CreateAccessToken $sut;

    protected function setUp(): void
    {
        $this->storage = $this->createMock(IOAuth2GrantCode::class);
        $this->clientProvider = $this->createMock(ClientProviderInterface::class);
        $this->randomCodeGenerator = $this->createMock(RandomCodeGeneratorInterface::class);
        $this->appConfirmationQuery = $this->createMock(GetAppConfirmationQueryInterface::class);
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->createJsonWebToken = $this->createMock(CreateJsonWebToken::class);
        $this->getConnectedAppScopesQuery = $this->createMock(GetConnectedAppScopesQueryInterface::class);
        $this->getUserConsentedAuthenticationUuidQuery = $this->createMock(GetUserConsentedAuthenticationUuidQueryInterface::class);
        $this->getUserConsentedAuthenticationScopesQuery = $this->createMock(GetUserConsentedAuthenticationScopesQueryInterface::class);
        $this->getAccessTokenQuery = $this->createMock(GetAccessTokenQueryInterface::class);
        $this->deleteAccessTokensQuery = $this->createMock(DeleteAccessTokensQueryInterface::class);
        $this->sut = new CreateAccessToken(
            $this->storage,
            $this->clientProvider,
            $this->randomCodeGenerator,
            $this->appConfirmationQuery,
            $this->userRepository,
            $this->createJsonWebToken,
            $this->getConnectedAppScopesQuery,
            $this->getUserConsentedAuthenticationUuidQuery,
            $this->getUserConsentedAuthenticationScopesQuery,
            $this->getAccessTokenQuery,
            $this->deleteAccessTokensQuery,
        );
    }

    public function test_it_is_a_create_access_token(): void
    {
        $this->assertInstanceOf(CreateAccessToken::class, $this->sut);
        $this->assertInstanceOf(CreateAccessTokenInterface::class, $this->sut);
    }

    public function test_it_creates_an_access_token(): void
    {
        $client = $this->createMock(Client::class);
        $authCode = $this->createMock(IOAuth2AuthCode::class);
        $appUser = $this->createMock(UserInterface::class);
        $pimUser = $this->createMock(UserInterface::class);

        $this->clientProvider->method('findClientByAppId')->with('client_id_1234')->willReturn($client);
        $this->storage->method('getAuthCode')->with('auth_code_1234')->willReturn($authCode);
        $this->randomCodeGenerator->method('generate')->willReturn('generated_token_123');
        $this->getConnectedAppScopesQuery->method('execute')->with('client_id_1234')->willReturn(['scope1', 'scope2']);
        $this->appConfirmationQuery->method('execute')->with('client_id_1234')->willReturn(AppConfirmation::create('client_id_1234', 1, 'some_user_group', 2));
        $this->userRepository->method('find')->with(1)->willReturn($appUser);
        $pimUser->method('getId')->willReturn(2);
        $this->getUserConsentedAuthenticationScopesQuery->method('execute')->with(2, 'client_id_1234')->willReturn([]);
        $authCode->method('getScope')->willReturn('delete_products');
        $authCode->method('getData')->willReturn($pimUser);
        $token = [
                    'access_token' => 'generated_token_123',
                    'token_type' => 'bearer',
                    'scope' => 'scope1 scope2',
                ];
        $this->getAccessTokenQuery->method('execute')->with('client_id_1234', 'scope1 scope2')->willReturn(null);
        $this->deleteAccessTokensQuery->expects($this->once())->method('execute')->with('client_id_1234')->willReturn(1);
        $this->storage->expects($this->once())->method('createAccessToken')->with(
            'generated_token_123',
            $client,
            $appUser,
            null,
            'scope1 scope2'
        );
        $this->storage->expects($this->once())->method('markAuthCodeAsUsed')->with('auth_code_1234');
        $this->assertSame($token, $this->sut->create('client_id_1234', 'auth_code_1234'));
    }

    public function test_it_returns_the_existing_access_token_if_it_exists_with_the_same_scopes(): void
    {
        $client = $this->createMock(Client::class);
        $authCode = $this->createMock(IOAuth2AuthCode::class);
        $pimUser = $this->createMock(UserInterface::class);

        $this->clientProvider->method('findClientByAppId')->with('client_id_1234')->willReturn($client);
        $this->storage->method('getAuthCode')->with('auth_code_1234')->willReturn($authCode);
        $this->getConnectedAppScopesQuery->method('execute')->with('client_id_1234')->willReturn(['scope1', 'scope2']);
        $this->getAccessTokenQuery->method('execute')->with('client_id_1234', 'scope1 scope2')->willReturn('generated_token_123');
        $pimUser->method('getId')->willReturn(2);
        $authCode->method('getData')->willReturn($pimUser);
        $this->getUserConsentedAuthenticationScopesQuery->method('execute')->with(2, 'client_id_1234')->willReturn([]);
        $this->deleteAccessTokensQuery->expects($this->never())->method('execute')->with($this->isType('string'))->willReturn(1);
        $this->storage->expects($this->never())->method('createAccessToken');
        $this->storage->expects($this->once())->method('markAuthCodeAsUsed')->with('auth_code_1234');
        $token = [
                    'access_token' => 'generated_token_123',
                    'token_type' => 'bearer',
                    'scope' => 'scope1 scope2',
                ];
        $this->assertSame($token, $this->sut->create('client_id_1234', 'auth_code_1234'));
    }

    public function test_it_adds_an_id_token_to_the_access_token_if_openid_is_requested(): void
    {
        $client = $this->createMock(Client::class);
        $authCode = $this->createMock(IOAuth2AuthCode::class);
        $appUser = $this->createMock(UserInterface::class);
        $pimUser = $this->createMock(UserInterface::class);

        $this->clientProvider->method('findClientByAppId')->with('a_client_id')->willReturn($client);
        $this->storage->method('getAuthCode')->with('an_auth_code')->willReturn($authCode);
        $this->randomCodeGenerator->method('generate')->willReturn('a_token');
        $this->getConnectedAppScopesQuery->method('execute')->with('a_client_id')->willReturn(['an_authorization_scope']);
        $this->appConfirmationQuery->method('execute')->with('a_client_id')->willReturn(AppConfirmation::create('a_client_id', 1, 'a_user_group', 2));
        $this->userRepository->method('find')->with(1)->willReturn($appUser);
        $authCode->method('getData')->willReturn($pimUser);
        $pimUser->method('getId')->willReturn(2);
        $pimUser->method('getFirstName')->willReturn('a_first_name');
        $pimUser->method('getLastName')->willReturn('a_last_name');
        $pimUser->method('getEmail')->willReturn('an_email');
        $this->getUserConsentedAuthenticationScopesQuery->method('execute')->with(2, 'a_client_id')->willReturn(['openid', 'an_authentication_scope']);
        $this->getUserConsentedAuthenticationUuidQuery->method('execute')->with(2, 'a_client_id')->willReturn('a_ppid');
        $this->deleteAccessTokensQuery->expects($this->once())->method('execute')->with('a_client_id')->willReturn(1);
        $this->storage->expects($this->once())->method('createAccessToken')->with(
            'a_token',
            $client,
            $appUser,
            null,
            'an_authorization_scope'
        );
        $this->storage->expects($this->once())->method('markAuthCodeAsUsed')->with('an_auth_code');
        $this->createJsonWebToken->expects($this->once())->method('create')->with(
            'a_client_id',
            'a_ppid',
            ScopeList::fromScopeString('openid an_authentication_scope'),
            'a_first_name',
            'a_last_name',
            'an_email'
        )->willReturn('an_id_token');
        $expectedAccessToken = [
                    'access_token' => 'a_token',
                    'token_type' => 'bearer',
                    'scope' => 'an_authentication_scope an_authorization_scope openid',
                    'id_token' => 'an_id_token',
                ];
        $this->assertSame($expectedAccessToken, $this->sut->create('a_client_id', 'an_auth_code'));
    }

    public function test_it_processes_only_valid_client(): void
    {
        $this->clientProvider->method('findClientByAppId')->with('client_id_1234')->willReturn(null);
        $this->expectException(new \InvalidArgumentException('No client found with the given client id.'));
        $this->sut->create('client_id_1234', 'auth_code_1234');
    }

    public function test_it_processes_only_valid_auth_code(): void
    {
        $client = $this->createMock(Client::class);

        $this->clientProvider->method('findClientByAppId')->with('client_id_1234')->willReturn($client);
        $this->storage->method('getAuthCode')->with('auth_code_1234')->willReturn(null);
        $this->expectException(new \InvalidArgumentException('Unknown authorization code.'));
        $this->sut->create('client_id_1234', 'auth_code_1234');
    }

    public function test_it_throw_an_exception_when_app_user_has_not_been_found(): void
    {
        $client = $this->createMock(Client::class);
        $authCode = $this->createMock(IOAuth2AuthCode::class);
        $pimUser = $this->createMock(UserInterface::class);

        $this->clientProvider->method('findClientByAppId')->with('client_id_1234')->willReturn($client);
        $this->storage->method('getAuthCode')->with('auth_code_1234')->willReturn($authCode);
        $this->randomCodeGenerator->method('generate')->willReturn('generated_token_123');
        $this->getConnectedAppScopesQuery->method('execute')->with('client_id_1234')->willReturn(['scope1', 'scope2']);
        $this->appConfirmationQuery->method('execute')->with('client_id_1234')->willReturn(AppConfirmation::create('client_id_1234', 1, 'some_user_group', 2));
        $this->userRepository->method('find')->with(1)->willReturn(null);
        $pimUser->method('getId')->willReturn(2);
        $this->getUserConsentedAuthenticationScopesQuery->method('execute')->with(2, 'client_id_1234')->willReturn([]);
        $authCode->method('getScope')->willReturn('delete_products');
        $authCode->method('getData')->willReturn($pimUser);
        $this->deleteAccessTokensQuery->expects($this->once())->method('execute')->with('client_id_1234')->willReturn(1);
        $this->getAccessTokenQuery->method('execute')->with('client_id_1234', 'scope1 scope2')->willReturn(null);
        $this->expectException(\LogicException::class);
        $this->sut->create('client_id_1234', 'auth_code_1234');
    }

    public function test_it_throw_an_exception_when_pim_user_has_not_been_found(): void
    {
        $client = $this->createMock(Client::class);
        $authCode = $this->createMock(IOAuth2AuthCode::class);
        $appUser = $this->createMock(UserInterface::class);
        $pimUser = $this->createMock(UserInterface::class);

        $this->clientProvider->method('findClientByAppId')->with('client_id_1234')->willReturn($client);
        $this->storage->method('getAuthCode')->with('auth_code_1234')->willReturn($authCode);
        $this->randomCodeGenerator->method('generate')->willReturn('generated_token_123');
        $this->getConnectedAppScopesQuery->method('execute')->with('client_id_1234')->willReturn(['scope1', 'scope2']);
        $this->appConfirmationQuery->method('execute')->with('client_id_1234')->willReturn(AppConfirmation::create('client_id_1234', 1, 'some_user_group', 2));
        $this->userRepository->method('find')->with(1)->willReturn($appUser);
        $pimUser->method('getId')->willReturn(2);
        $this->getUserConsentedAuthenticationScopesQuery->method('execute')->with(2, 'client_id_1234')->willReturn([]);
        $authCode->method('getScope')->willReturn('delete_products');
        $authCode->method('getData')->willReturn(false);
        $this->getAccessTokenQuery->method('execute')->with('client_id_1234', 'scope1 scope2')->willReturn(null);
        $this->deleteAccessTokensQuery->expects($this->once())->method('execute')->with('client_id_1234')->willReturn(1);
        $this->storage->expects($this->once())->method('createAccessToken')->with(
            'generated_token_123',
            $client,
            $appUser,
            null,
            'scope1 scope2'
        );
        $this->storage->expects($this->once())->method('markAuthCodeAsUsed')->with('auth_code_1234');
        $this->expectException(\LogicException::class);
        $this->sut->create('client_id_1234', 'auth_code_1234');
    }
}

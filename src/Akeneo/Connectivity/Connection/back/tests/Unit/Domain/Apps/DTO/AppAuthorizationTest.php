<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Domain\Apps\DTO;

use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppAuthorization;
use Akeneo\Connectivity\Connection\Domain\Apps\ValueObject\ScopeList;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AppAuthorizationTest extends TestCase
{
    private AppAuthorization $sut;

    protected function setUp(): void
    {
    }

    public function test_it_is_an_app_authorization(): void
    {
        $this->assertTrue(\is_a(AppAuthorization::class, AppAuthorization::class, true));
    }

    public function test_it_creates_from_request(): void
    {
        $this->sut = AppAuthorization::createFromRequest(
            'a_client_id',
            ScopeList::fromScopeString('an_authorization_scope'),
            ScopeList::fromScopeString('an_authentication_scope'),
            'a_redirect_uri',
            'a_state',
        );
        $this->assertTrue(\is_a(AppAuthorization::class, AppAuthorization::class, true));
    }

    public function test_it_creates_from_normalized(): void
    {
        $this->sut = AppAuthorization::createFromNormalized([
                            'client_id' => 'a_client_id',
                            'authorization_scope' => 'an_authorization_scope',
                            'authentication_scope' => 'an_authentication_scope',
                            'redirect_uri' => 'a_redirect_uri',
                            'state' => 'a_state',
                        ], );
        $this->assertTrue(\is_a(AppAuthorization::class, AppAuthorization::class, true));
    }

    public function test_it_normalizes_app_authorization(): void
    {
        $this->sut = AppAuthorization::createFromRequest(
            'a_client_id',
            ScopeList::fromScopeString('an_authorization_scope'),
            ScopeList::fromScopeString('an_authentication_scope'),
            'a_redirect_uri',
            'a_state',
        );
        $this->assertSame([
                    'client_id' => 'a_client_id',
                    'authorization_scope' => 'an_authorization_scope',
                    'authentication_scope' => 'an_authentication_scope',
                    'redirect_uri' => 'a_redirect_uri',
                    'state' => 'a_state',
                ], $this->sut->normalize());
    }

    public function test_it_gets_all_scopes(): void
    {
        $this->sut = AppAuthorization::createFromRequest(
            'a_client_id',
            ScopeList::fromScopeString('an_authorization_scope'),
            ScopeList::fromScopeString('an_authentication_scope'),
            'a_redirect_uri',
            'a_state',
        );
        $this->assertSame(['an_authentication_scope', 'an_authorization_scope'], $this->sut->getAllScopes()->getScopes());
    }

    public function test_it_gets_only_authorization_scopes(): void
    {
        $this->sut = AppAuthorization::createFromRequest(
            'a_client_id',
            ScopeList::fromScopeString('an_authorization_scope'),
            ScopeList::fromScopeString('an_authentication_scope'),
            'a_redirect_uri',
            'a_state',
        );
        $this->assertSame(['an_authorization_scope'], $this->sut->getAuthorizationScopes()->getScopes());
    }

    public function test_it_gets_only_authentication_scopes(): void
    {
        $this->sut = AppAuthorization::createFromRequest(
            'a_client_id',
            ScopeList::fromScopeString('an_authorization_scope'),
            ScopeList::fromScopeString('an_authentication_scope'),
            'a_redirect_uri',
            'a_state',
        );
        $this->assertSame(['an_authentication_scope'], $this->sut->getAuthenticationScopes()->getScopes());
    }

    public function test_it_gets_state(): void
    {
        $this->sut = AppAuthorization::createFromRequest(
            'a_client_id',
            ScopeList::fromScopeString('an_authorization_scope'),
            ScopeList::fromScopeString('an_authentication_scope'),
            'a_redirect_uri',
            'a_state',
        );
        $this->assertSame('a_state', $this->sut->getState());
    }

    public function test_it_gets_redirect_uri(): void
    {
        $this->sut = AppAuthorization::createFromRequest(
            'a_client_id',
            ScopeList::fromScopeString('an_authorization_scope'),
            ScopeList::fromScopeString('an_authentication_scope'),
            'a_redirect_uri',
            'a_state',
        );
        $this->assertSame('a_redirect_uri', $this->sut->getRedirectUri());
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Domain\Apps\Model;

use Akeneo\Connectivity\Connection\Domain\Apps\Model\AuthenticationScope;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AuthenticationScopeTest extends TestCase
{
    private AuthenticationScope $sut;

    protected function setUp(): void
    {
        $this->sut = new AuthenticationScope();
    }

    public function test_it_is_an_authentication_scope(): void
    {
        $this->assertInstanceOf(AuthenticationScope::class, $this->sut);
    }

    public function test_it_returns_all_the_scopes(): void
    {
        $this::getAllScopes()->shouldReturn(
            [AuthenticationScope::SCOPE_OPENID, AuthenticationScope::SCOPE_PROFILE, AuthenticationScope::SCOPE_EMAIL]
        );
    }
}

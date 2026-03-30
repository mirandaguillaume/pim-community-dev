<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Application\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\Command\RequestAppAuthenticationCommand;
use Akeneo\Connectivity\Connection\Domain\Apps\ValueObject\ScopeList;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RequestAppAuthenticationCommandTest extends TestCase
{
    private RequestAppAuthenticationCommand $sut;

    protected function setUp(): void
    {
        $this->sut = new RequestAppAuthenticationCommand(
            'an_app_id',
            1,
            ScopeList::fromScopes(['an_authentication_scope', 'another_authentication_scope'])
        );
    }

    public function test_it_is_a_request_app_authentication_command(): void
    {
        $this->assertInstanceOf(RequestAppAuthenticationCommand::class, $this->sut);
    }

    public function test_it_returns_app_id(): void
    {
        $this->assertSame('an_app_id', $this->sut->getAppId());
    }

    public function test_it_returns_pim_user_id(): void
    {
        $this->assertSame(1, $this->sut->getPimUserId());
    }

    public function test_it_returns_requested_authentication_scopes(): void
    {
        $this->assertSame(ScopeList::fromScopes(['an_authentication_scope', 'another_authentication_scope'])->toScopeString(), $this->sut->getRequestedAuthenticationScopes()->toScopeString());
    }
}

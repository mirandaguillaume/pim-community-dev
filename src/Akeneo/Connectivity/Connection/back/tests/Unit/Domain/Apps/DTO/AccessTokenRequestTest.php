<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Domain\Apps\DTO;

use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AccessTokenRequest;
use PHPUnit\Framework\TestCase;

class AccessTokenRequestTest extends TestCase
{
    private AccessTokenRequest $sut;

    protected function setUp(): void
    {
        $this->sut = new AccessTokenRequest(
            'client_id_1234',
            'BTC_123_ETH',
            'authorization_code',
            'code_identifier_123',
            'code_challenge_123'
        );
    }

    public function test_it_is_an_access_token_request(): void
    {
        $this->assertInstanceOf(AccessTokenRequest::class, $this->sut);
    }

    public function test_it_provides_a_client_id(): void
    {
        $this->assertSame('client_id_1234', $this->sut->getClientId());
    }

    public function test_it_provides_an_authorization_code(): void
    {
        $this->assertSame('BTC_123_ETH', $this->sut->getAuthorizationCode());
    }

    public function test_it_provides_a_grant_type(): void
    {
        $this->assertSame('authorization_code', $this->sut->getGrantType());
    }

    public function test_it_provides_a_code_identifier(): void
    {
        $this->assertSame('code_identifier_123', $this->sut->getCodeIdentifier());
    }

    public function test_it_provides_a_code_challenge(): void
    {
        $this->assertSame('code_challenge_123', $this->sut->getCodeChallenge());
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\ApiBundle\Entity;

use Akeneo\Tool\Bundle\ApiBundle\Entity\AuthCode;
use Akeneo\Tool\Bundle\ApiBundle\Entity\Client;
use Akeneo\Tool\Bundle\ApiBundle\OAuth\IOAuth2AuthCode;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PHPUnit\Framework\TestCase;

class AuthCodeTest extends TestCase
{
    private AuthCode $sut;

    protected function setUp(): void
    {
        $this->sut = new AuthCode();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(AuthCode::class, $this->sut);
        $this->assertInstanceOf(IOAuth2AuthCode::class, $this->sut);
    }

    public function test_it_exposes_the_public_id_of_its_client_as_client_id(): void
    {
        $client = $this->createMock(Client::class);
        $client->expects($this->once())->method('getPublicId')->willReturn('6_5ff5299118e2c');
        $this->sut->setClient($client);

        $this->assertSame('6_5ff5299118e2c', $this->sut->getClientId());
    }

    public function test_it_returns_an_empty_client_id_when_it_has_no_client(): void
    {
        $this->assertSame('', $this->sut->getClientId());
    }

    public function test_it_exposes_the_authenticated_user_as_data(): void
    {
        $user = $this->createMock(UserInterface::class);
        $this->sut->setUser($user);

        $this->assertSame($user, $this->sut->getData());
        $this->assertSame($user, $this->sut->getUser());
    }

    public function test_it_has_no_data_when_no_user_is_set(): void
    {
        $this->assertNull($this->sut->getData());
    }

    public function test_it_never_expires_when_no_expiration_date_is_set(): void
    {
        $this->sut->setExpiresAt(null);

        $this->assertNull($this->sut->getExpiresAt());
        $this->assertFalse($this->sut->hasExpired());
    }

    public function test_it_has_expired_when_the_expiration_date_is_in_the_past(): void
    {
        $this->sut->setExpiresAt(\time() - 1);

        $this->assertTrue($this->sut->hasExpired());
    }

    public function test_it_has_not_expired_when_the_expiration_date_is_in_the_future(): void
    {
        $this->sut->setExpiresAt(\time() + 3600);

        $this->assertFalse($this->sut->hasExpired());
    }

    public function test_it_has_not_expired_on_the_very_second_it_expires(): void
    {
        $this->sut->setExpiresAt(\time());

        $this->assertFalse($this->sut->hasExpired());
    }

    public function test_it_returns_an_empty_token_when_none_is_set(): void
    {
        $this->assertSame('', $this->sut->getToken());

        $this->sut->setToken('an_authorization_code');

        $this->assertSame('an_authorization_code', $this->sut->getToken());
    }
}

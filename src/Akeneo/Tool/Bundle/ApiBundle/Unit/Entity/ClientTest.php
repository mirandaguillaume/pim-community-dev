<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\ApiBundle\Entity;

use Akeneo\Tool\Bundle\ApiBundle\Entity\Client;
use Akeneo\Tool\Bundle\ApiBundle\OAuth\Model\ClientInterface;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ClientTest extends TestCase
{
    private Client $sut;

    protected function setUp(): void
    {
        $this->sut = new Client();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(Client::class, $this->sut);
        $this->assertInstanceOf(ClientInterface::class, $this->sut);
    }

    public function test_it_builds_its_public_id_from_its_id_and_its_random_id(): void
    {
        $this->forceId($this->sut, 7);
        $this->sut->setRandomId('e5f6a7b8');

        $this->assertSame('7_e5f6a7b8', $this->sut->getPublicId());
    }

    public function test_it_accepts_any_secret_when_it_has_none(): void
    {
        $this->sut->setSecret(null);

        // A public client (no secret) is what allows the token exchange to succeed without a client_secret.
        $this->assertTrue($this->sut->checkSecret(null));
        $this->assertTrue($this->sut->checkSecret(''));
        $this->assertTrue($this->sut->checkSecret('any-secret'));
    }

    public function test_it_accepts_only_the_exact_secret_when_it_has_one(): void
    {
        $this->sut->setSecret('the-secret');

        $this->assertTrue($this->sut->checkSecret('the-secret'));
        $this->assertFalse($this->sut->checkSecret('THE-SECRET'));
        $this->assertFalse($this->sut->checkSecret('the-secret '));
        $this->assertFalse($this->sut->checkSecret(''));
        $this->assertFalse($this->sut->checkSecret(null));
    }

    public function test_it_has_no_redirect_uri_nor_allowed_grant_type_before_being_configured(): void
    {
        $this->assertSame([], $this->sut->getRedirectUris());
        $this->assertSame([], $this->sut->getAllowedGrantTypes());
        $this->assertNull($this->sut->getMarketplacePublicAppId());
    }

    private function forceId(Client $client, int $id): void
    {
        $property = new \ReflectionProperty(Client::class, 'id');
        $property->setAccessible(true);
        $property->setValue($client, $id);
    }
}

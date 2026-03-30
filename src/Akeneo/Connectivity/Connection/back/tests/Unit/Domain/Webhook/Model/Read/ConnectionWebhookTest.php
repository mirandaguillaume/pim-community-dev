<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Domain\Webhook\Model\Read;

use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Read\ConnectionWebhook;
use PHPUnit\Framework\TestCase;

class ConnectionWebhookTest extends TestCase
{
    private ConnectionWebhook $sut;

    protected function setUp(): void
    {
        $this->sut = new ConnectionWebhook('magento', true, 'secret_magento', 'any-url.com', true);
    }

    public function test_it_is_a_connection_webhook(): void
    {
        $this->sut = new ConnectionWebhook('magento', false);
        $this->assertInstanceOf(ConnectionWebhook::class, $this->sut);
    }

    public function test_it_provides_a_code(): void
    {
        $this->sut = new ConnectionWebhook('magento', false);
        $this->assertSame('magento', $this->sut->connectionCode());
    }

    public function test_it_could_have_no_secret(): void
    {
        $this->sut = new ConnectionWebhook('magento', false);
        $this->assertNull($this->sut->secret());
    }

    public function test_it_provides_a_secret(): void
    {
        $this->sut = new ConnectionWebhook('magento', true, 'secret_magento', 'any-url.com');
        $this->assertSame('secret_magento', $this->sut->secret());
    }

    public function test_it_could_have_no_url(): void
    {
        $this->sut = new ConnectionWebhook('magento', false);
        $this->assertNull($this->sut->url());
    }

    public function test_it_provides_an_url(): void
    {
        $this->sut = new ConnectionWebhook('magento', true, 'secret_magento', 'any-url.com');
        $this->assertSame('any-url.com', $this->sut->url());
    }

    public function test_it_provides_the_enabled_status(): void
    {
        $this->sut = new ConnectionWebhook('magento', true);
        $this->assertSame(true, $this->sut->enabled());
    }

    public function test_it_provides_the_uuid_use_status(): void
    {
        $this->sut = new ConnectionWebhook('magento', true, 'secret_magento', 'any-url.com', true);
        $this->assertSame(true, $this->sut->isUsingUuid());
    }

    public function test_it_could_have_no_uuid_use_status(): void
    {
        $this->sut = new ConnectionWebhook('magento', true, 'secret_magento', 'any-url.com');
        $this->assertSame(false, $this->sut->isUsingUuid());
    }

    public function test_it_provides_a_normalized_format(): void
    {
        $this->sut = new ConnectionWebhook('magento', true, 'secret_magento', 'any-url.com', true);
        $this->assertSame([
                    'connectionCode' => 'magento',
                    'enabled' => true,
                    'secret' => 'secret_magento',
                    'url' => 'any-url.com',
                    'isUsingUuid' => true,
                ], $this->sut->normalize());
    }

    public function test_it_provides_a_normalized_format_with_no_arguments(): void
    {
        $this->sut = new ConnectionWebhook('magento', false);
        $this->assertSame([
                    'connectionCode' => 'magento',
                    'enabled' => false,
                    'secret' => null,
                    'url' => null,
                    'isUsingUuid' => false,
                ], $this->sut->normalize());
    }
}

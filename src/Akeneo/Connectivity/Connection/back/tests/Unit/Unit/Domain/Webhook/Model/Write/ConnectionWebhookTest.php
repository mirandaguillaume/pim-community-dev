<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Domain\Webhook\Model\Write;

use Akeneo\Connectivity\Connection\Domain\ValueObject\Url;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Write\ConnectionWebhook;
use PHPUnit\Framework\TestCase;

class ConnectionWebhookTest extends TestCase
{
    private ConnectionWebhook $sut;

    protected function setUp(): void
    {
    }

    public function test_it_is_a_connection_webhook_write_model(): void
    {
        $this->sut = new ConnectionWebhook('magento', false);
        $this->assertTrue(is_a(ConnectionWebhook::class, ConnectionWebhook::class, true));
    }

    public function test_it_provides_a_code(): void
    {
        $this->sut = new ConnectionWebhook('magento', false);
        $this->assertSame('magento', $this->sut->code());
    }

    public function test_it_provides_an_enabled_status(): void
    {
        $this->sut = new ConnectionWebhook('magento', false);
        $this->assertSame(false, $this->sut->enabled());
    }

    public function test_it_provides_a_url(): void
    {
        $this->sut = new ConnectionWebhook('magento', true, 'http://any-url.com');
        $url = $this->url();
        $url->shouldBeAnInstanceOf(Url::class);
        $url->__toString()->shouldReturn('http://any-url.com');
    }

    public function test_it_has_no_url_if_an_empty_one_is_provided(): void
    {
        $this->sut = new ConnectionWebhook('magento', true, '');
        $this->assertNull($this->sut->url());
    }

    public function test_it_could_have_no_url(): void
    {
        $this->sut = new ConnectionWebhook('magento', false);
        $this->assertNull($this->sut->url());
    }

    public function test_it_provides_the_uuid_use_status(): void
    {
        $this->sut = new ConnectionWebhook('magento', true, 'any-url.com', true);
        $this->assertSame(true, $this->sut->isUsingUuid());
    }

    public function test_it_could_have_no_uuid_use_status(): void
    {
        $this->sut = new ConnectionWebhook('magento', true, 'any-url.com');
        $this->assertSame(false, $this->sut->isUsingUuid());
    }
}

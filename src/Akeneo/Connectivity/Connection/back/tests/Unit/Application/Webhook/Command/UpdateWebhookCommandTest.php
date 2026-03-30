<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Application\Webhook\Command;

use Akeneo\Connectivity\Connection\Application\Webhook\Command\UpdateWebhookCommand;
use PHPUnit\Framework\TestCase;

class UpdateWebhookCommandTest extends TestCase
{
    private UpdateWebhookCommand $sut;

    protected function setUp(): void
    {
    }

    public function test_it_is_an_update_webhook_command(): void
    {
        $this->sut = new UpdateWebhookCommand('magento', false);
        $this->assertTrue(\is_a(UpdateWebhookCommand::class, UpdateWebhookCommand::class, true));
    }

    public function test_it_provides_a_code(): void
    {
        $this->sut = new UpdateWebhookCommand('magento', false);
        $this->assertSame('magento', $this->sut->code());
    }

    public function test_it_provides_an_enabled_status(): void
    {
        $this->sut = new UpdateWebhookCommand('magento', false);
        $this->assertSame(false, $this->sut->enabled());
    }

    public function test_it_provides_an_url(): void
    {
        $this->sut = new UpdateWebhookCommand('magento', true, 'http://my-url.com');
        $this->assertSame('http://my-url.com', $this->sut->url());
    }

    public function test_it_could_have_no_url(): void
    {
        $this->sut = new UpdateWebhookCommand('magento', false);
        $this->assertNull($this->sut->url());
    }

    public function test_it_provides_the_uuid_use_status(): void
    {
        $this->sut = new UpdateWebhookCommand('magento', true, 'any-url.com', true);
        $this->assertSame(true, $this->sut->isUsingUuid());
    }

    public function test_it_could_have_no_uuid_use_status(): void
    {
        $this->sut = new UpdateWebhookCommand('magento', true, 'any-url.com');
        $this->assertSame(false, $this->sut->isUsingUuid());
    }
}
